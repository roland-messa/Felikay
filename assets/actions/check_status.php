<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

$ref = $_GET['ref'] ?? '';

if (empty($ref)) {
  echo json_encode(['status' => 'error', 'message' => 'Paramètres manquants']);
  exit;
}

try {
  // 🔍 1. Récupération paiement
  $stmt = $pdo->prepare("SELECT * FROM paiements WHERE reference_interne = ?");
  $stmt->execute([$ref]);
  $paiement = $stmt->fetch();

  if (!$paiement) {
    echo json_encode(['status' => 'not_found']);
    exit;
  }

  // 🔒 2. VERROU GLOBAL (Source de vérité BDD)
  // On utilise 'reussi' pour correspondre à ton ENUM BDD
  if ($paiement['statut_paiement'] === 'reussi' || $paiement['statut_paiement'] === 'paye') {
    echo json_encode([
      'status' => 'reussi',
      'commande_id' => $paiement['commande_id']
    ]);
    exit;
  }

  if ($paiement['statut_paiement'] === 'echoue') {
    echo json_encode([
      'status' => 'echoue',
      'commande_id' => $paiement['commande_id']
    ]);
    exit;
  }

  // 📡 3. Préparation API
  $phone = preg_replace('/[^0-9]/', '', $paiement['telephone_paiement']);
  $amount = number_format(floatval($paiement['montant']), 2, '.', '');

  $apiUrl = "https://www.afreemosi.com/api/payment/CheckFelikayPaymentStatus.ashx?"
    . http_build_query([
      "clientphone" => $phone,
      "amount" => $amount
    ]);

  $ch = curl_init($apiUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  $response = curl_exec($ch);
  curl_close($ch);

  file_put_contents("check_log.txt", date('Y-m-d H:i:s') . " | $apiUrl => $response\n", FILE_APPEND);

  $data = json_decode($response, true);

  // 🧠 4. Analyse réponse API
  $newStatus = 'en_attente';
  if ($data && isset($data['status'])) {
    $apiStatus = strtolower($data['status']);

    if (in_array($apiStatus, ['success', 'reussi', 'paye', 'already_processed'])) {
      $newStatus = 'reussi';
    } elseif (in_array($apiStatus, ['failed', 'echoue', 'error'])) {
      $newStatus = 'echoue';
    }

    // ⚠️ 5. UPDATE SÉCURISÉ (Correction updated_at)
    if ($newStatus !== 'en_attente') {

      $update = $pdo->prepare("
                UPDATE paiements 
                SET statut_paiement = ?, updated_at = NOW() 
                WHERE reference_interne = ? AND statut_paiement = 'en_attente'
            ");
      $update->execute([$newStatus, $ref]);

      if ($update->rowCount() > 0) {
        if ($newStatus === 'reussi') {
          $pdo->prepare("UPDATE commandes SET statut = 'paye' WHERE id = ?")
            ->execute([$paiement['commande_id']]);
        }
      } else {
        // Quelqu’un a déjà modifié -> on récupère l'état actuel
        $stmt = $pdo->prepare("SELECT statut_paiement FROM paiements WHERE reference_interne = ?");
        $stmt->execute([$ref]);
        $current = $stmt->fetchColumn();
        $newStatus = $current ?: 'en_attente';
      }
    }
  }

  echo json_encode([
    'status' => $newStatus,
    'commande_id' => $paiement['commande_id']
  ]);
} catch (Exception $e) {
  echo json_encode([
    'status' => 'error',
    'message' => $e->getMessage()
  ]);
}
