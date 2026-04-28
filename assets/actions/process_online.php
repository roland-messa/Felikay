<?php
// C:\wamp64\www\ProjetFelykay\assets\actions\process_online.php
session_start();
require_once '../../config/db.php';
require_once '../functions/payment_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // 1. RÉCUPÉRATION DES DONNÉES
  $user_id = $_SESSION['user_id'] ?? null;
  $gateway = $_POST['gateway'] ?? '';
  $total   = $_POST['total_ttc'] ?? 0;
  $phone   = $_POST['payment_phone'] ?? '';

  // Données de livraison
  $commune   = $_POST['commune'] ?? '';
  $avenue    = $_POST['avenue'] ?? '';
  $numero    = $_POST['numero'] ?? '';
  $reference = $_POST['reference'] ?? '';
  $ville     = "Kinshasa";

  try {
    // 2. INITIATION DU PAIEMENT SERDIPAY
    $paiement = initierPaiementMobile(
      $phone,
      $total,
      'USD',
      $gateway,
      "Commande Felykay - " . ($_POST['nom_complet'] ?? 'Client')
    );

    if ($paiement['success']) {
      $ref_paiement = $paiement['referenceNo'];

      // 3. ENREGISTREMENT DANS LA TABLE 'commandes'
      // J'ai utilisé vos noms de colonnes : total_ttc, statut, adresse_livraison, etc.
      $stmt = $pdo->prepare("INSERT INTO commandes (user_id, total_ttc, statut, payment_ref, methode_paiement, telephone, adresse_livraison, commune, ville, created_at) VALUES (?, ?, 'en_attente', ?, ?, ?, ?, ?, ?, NOW())");

      $adresse_complete = "Ave: $avenue, No: $numero, Ref: $reference";

      $stmt->execute([
        $user_id,
        $total,
        $ref_paiement,
        $gateway,
        $_POST['phone'] ?? $phone, // Téléphone de contact
        $adresse_complete,
        $commune,
        $ville
      ]);

      // 4. REDIRECTION
      header("Location: ../../pages/attente_paiement.php?ref=$ref_paiement&operator=$gateway&phone=$phone");
      exit();
    } else {
      header("Location: ../../pages/paiement.php?error=payment_failed&msg=" . urlencode($paiement['message']));
      exit();
    }
  } catch (Exception $e) {
    die("Erreur critique : " . $e->getMessage());
  }
}
