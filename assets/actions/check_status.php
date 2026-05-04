<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

$ref = $_GET['ref'] ?? '';

if (empty($ref)) {
  echo json_encode(['status' => 'error', 'message' => 'Référence manquante']);
  exit;
}

try {
  // 1. On vérifie d'abord en base de données locale
  $stmt = $pdo->prepare("SELECT statut_paiement, commande_id FROM paiements WHERE reference_interne = ?");
  $stmt->execute([$ref]);
  $paiement = $stmt->fetch();

  if (!$paiement) {
    echo json_encode(['status' => 'not_found']);
    exit;
  }

  // 2. Si c'est déjà payé ou échoué en local, on répond tout de suite
  if ($paiement['statut_paiement'] !== 'en_attente') {
    echo json_encode([
      'status' => $paiement['statut_paiement'],
      'commande_id' => $paiement['commande_id']
    ]);
    exit;
  }

  // 3. Sinon, on interroge l'API AfreeMosi pour vérifier s'il y a du nouveau
  $apiUrl = "https://www.afreemosi.com/api/payment/CheckFelikayPaymentStatus.ashx?ref=" . urlencode($ref);
  $ch = curl_init($apiUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  $response = curl_exec($ch);
  curl_close($ch);

  $data = json_decode($response, true);

  if ($data && isset($data['status'])) {
    $apiStatus = strtolower($data['status']);
    $newStatus = 'en_attente';

    if (in_array($apiStatus, ['success', 'reussi', 'paye'])) $newStatus = 'paye';
    if (in_array($apiStatus, ['failed', 'echoue', 'error'])) $newStatus = 'echoue';

    // Mises à jour si le statut a changé
    if ($newStatus !== 'en_attente') {
      $pdo->prepare("UPDATE paiements SET statut_paiement = ?, updated_at = NOW() WHERE reference_interne = ?")
        ->execute([$newStatus, $ref]);

      if ($newStatus === 'paye') {
        $pdo->prepare("UPDATE commandes SET statut = 'paye' WHERE id = ?")
          ->execute([$paiement['commande_id']]);
      }
    }

    echo json_encode([
      'status' => $newStatus,
      'commande_id' => $paiement['commande_id']
    ]);
  } else {
    echo json_encode(['status' => 'en_attente']);
  }
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
