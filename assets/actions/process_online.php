<?php
session_start();
require_once '../../config/db.php';
require_once '../functions/payment_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $user_id = $_SESSION['user_id'] ?? null;
  $gateway = $_POST['gateway'] ?? '';
  $nom_complet = $_POST['nom_complet'] ?? 'Client';

  if (!$user_id) {
    header("Location: ../../pages/connexion.php?error=session_expired");
    exit();
  }

  // ====== SÉCURITÉ GATEWAY ======
  $allowed_gateways = ['AM', 'OM'];
  if (!in_array($gateway, $allowed_gateways)) {
    header("Location: ../../pages/paiement.php?error=payment_failed&msg=" . urlencode("Mode de paiement non supporté"));
    exit();
  }

  // ====== TÉLÉPHONE ======
  $raw_phone = $_POST['payment_phone'] ?? '';
  $clean_phone = preg_replace('/[^0-9]/', '', $raw_phone);

  if (substr($clean_phone, 0, 3) === '243') {
    $clean_phone = substr($clean_phone, 3);
  }
  if (substr($clean_phone, 0, 1) === '0') {
    $clean_phone = substr($clean_phone, 1);
  }

  $phone_to_pay = '243' . $clean_phone;

  if (strlen($phone_to_pay) !== 12) {
    header("Location: ../../pages/paiement.php?error=invalid_phone");
    exit();
  }

  // ====== MONTANTS ======
  $total_final = floatval($_POST['total_ttc'] ?? 0);
  $frais_livraison = floatval($_POST['frais_livraison'] ?? 0);

  if ($total_final <= 0) {
    header("Location: ../../pages/paiement.php?error=payment_failed&msg=" . urlencode("Montant invalide"));
    exit();
  }

  // ====== DEBUG LOG ======
  error_log("PAYMENT INIT -> PHONE: $phone_to_pay | AMOUNT: $total_final | GATEWAY: $gateway");

  try {

    // ====== APPEL API ======
    $paiement = initierPaiementMobile(
      $phone_to_pay,
      $total_final,
      'USD',
      $gateway,
      "Commande Felykay - " . $nom_complet
    );

    // LOG COMPLET
    file_put_contents("debug_payment.log", date('Y-m-d H:i:s') . " - " . json_encode($paiement) . PHP_EOL, FILE_APPEND);

    if ($paiement['success']) {

      $ref_paiement = $paiement['referenceNo'];

      // ====== INSERT DB ======
      $stmt = $pdo->prepare("
        INSERT INTO commandes (
          user_id, nom_complet, total_ttc, frais_livraison, statut,
          payment_ref, methode_paiement, telephone, created_at
        ) VALUES (?, ?, ?, ?, 'en_attente', ?, ?, ?, NOW())
      ");

      $stmt->execute([
        $user_id,
        $nom_complet,
        $total_final,
        $frais_livraison,
        $ref_paiement,
        $gateway,
        $phone_to_pay
      ]);

      // ====== REDIRECTION ======
      header("Location: ../../pages/attente_paiement.php?ref=" . urlencode($ref_paiement) . "&phone=" . urlencode($phone_to_pay));
      exit();
    } else {

      $message = $paiement['message'] ?? "Échec de l'initialisation";

      header("Location: ../../pages/paiement.php?error=payment_failed&msg=" . urlencode($message));
      exit();
    }
  } catch (Exception $e) {

    error_log("ERREUR SYSTEME PAIEMENT: " . $e->getMessage());

    header("Location: ../../pages/paiement.php?error=system&msg=" . urlencode($e->getMessage()));
    exit();
  }
} else {
  header("Location: ../../pages/paiement.php");
  exit();
}
