<?php
session_start();
require_once '../../config/db.php';
require_once '../functions/payment_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_id = $_SESSION['user_id'] ?? null;
  $gateway = $_POST['gateway'] ?? '';
  $nom_complet = htmlspecialchars($_POST['nom_complet'] ?? 'Client');

  // CORRECTION : On récupère "email" qui correspond au name du formulaire
  $email_client = htmlspecialchars($_POST['email'] ?? '');
  $currency = 'USD';

  $allowed_gateways = ['MP', 'AM', 'OM'];
  if (!in_array($gateway, $allowed_gateways)) {
    header("Location: ../../pages/paiement.php?error=payment_failed");
    exit();
  }

  // DONNÉES LIVRAISON
  $commune = htmlspecialchars($_POST['commune'] ?? '');
  $quartier = htmlspecialchars($_POST['quartier'] ?? '');
  $avenue = htmlspecialchars($_POST['avenue'] ?? '');
  $numero = htmlspecialchars($_POST['numero'] ?? '');
  $adresse_complete = (!empty($avenue)) ? "Av. $avenue, N° $numero" : "Retrait en boutique";

  // TÉLÉPHONE
  $raw_phone = $_POST['payment_phone'] ?? '';
  $phone_to_pay = '243' . substr(preg_replace('/[^0-9]/', '', $raw_phone), -9);

  $total_final = floatval($_POST['total_ttc'] ?? 0);
  $frais_livraison = floatval($_POST['frais_livraison'] ?? 0);

  try {
    $paiement = initierPaiementMobile($phone_to_pay, $total_final, $currency, $gateway, "Commande Felykay - " . $nom_complet);

    if ($paiement['success']) {
      $ref_paiement = $paiement['referenceNo'];
      $pdo->beginTransaction();

      // 1. INSERT COMMANDE
      $stmtCmd = $pdo->prepare("
                INSERT INTO commandes (
                    user_id, nom_complet, email_invite, telephone, total_ttc, frais_livraison,
                    statut, commune, quartier, adresse_livraison, ville,
                    code_confirmation, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, 'en_attente', ?, ?, ?, 'Kinshasa', ?, NOW())
            ");
      $stmtCmd->execute([
        $user_id,
        $nom_complet,
        $email_client, // Utilisation de la variable corrigée
        $phone_to_pay,
        $total_final,
        $frais_livraison,
        $commune,
        $quartier,
        $adresse_complete,
        rand(1000, 9999)
      ]);

      $commande_id = $pdo->lastInsertId();

      // 2. INSERTION ARTICLES
      $cart_items = json_decode($_POST['cart_data'] ?? '[]', true);
      if (!empty($cart_items)) {
        $stmtItem = $pdo->prepare("INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire, taille_id, couleur_id) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($cart_items as $item) {
          $stmtItem->execute([$commande_id, $item['id'], $item['quantity'] ?? 1, $item['price'], null, null]);
        }
      }

      // 3. INSERT PAIEMENT
      $stmtPay = $pdo->prepare("INSERT INTO paiements (commande_id, mode_paiement, telephone_paiement, montant, reference_interne, statut_paiement, created_at) VALUES (?, ?, ?, ?, ?, 'en_attente', NOW())");
      $stmtPay->execute([$commande_id, $gateway, $phone_to_pay, $total_final, $ref_paiement]);

      $pdo->commit();

      // 4. DÉCLENCHEMENT DU MAIL (Asynchrone via generer_recu.php)
      $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
      $url_envoi = "$protocol://$_SERVER[HTTP_HOST]/ProjetFelykay/assets/actions/generer_recu.php?id=$commande_id&email=" . urlencode($email_client);

      $ctx = stream_context_create(['http' => ['timeout' => 2]]);
      @file_get_contents($url_envoi, false, $ctx);

      // AJOUT : Notification Admin
      require_once '../functions/notification_helper.php';
      notifierNouvelleCommande($commande_id, $nom_complet, $gateway, $total_final, $phone_to_pay);

      header("Location: ../../pages/attente_paiement.php?ref=" . urlencode($ref_paiement) . "&phone=" . urlencode($phone_to_pay));
      exit();
    } else {
      header("Location: ../../pages/paiement.php?error=payment_failed&msg=" . urlencode($paiement['message']));
      exit();
    }
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("ERREUR ONLINE: " . $e->getMessage());
    header("Location: ../../pages/paiement.php?error=system");
    exit();
  }
}
