<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'] ?? null;

  // ====== RÉCUPÉRATION DONNÉES ======
  $nom = htmlspecialchars($_POST['nom_complet'] ?? '');

  // CORRECTION : On récupère "email" du formulaire
  $email_client = htmlspecialchars($_POST['email'] ?? '');

  $phone_raw = $_POST['phone'] ?? '';
  $commune = htmlspecialchars($_POST['commune'] ?? '');
  $quartier = htmlspecialchars($_POST['quartier'] ?? '');
  $avenue = htmlspecialchars($_POST['avenue'] ?? '');
  $numero = htmlspecialchars($_POST['numero'] ?? '');
  $adresse_complete = (!empty($avenue)) ? "Av. $avenue, N° $numero" : "Retrait en boutique";

  // ====== NORMALISATION TELEPHONE ======
  $clean_phone = preg_replace('/[^0-9]/', '', $phone_raw);
  $phone = '243' . substr($clean_phone, -9);

  // ====== MONTANT ======
  $total = floatval($_POST['total_ttc'] ?? 0);
  $frais_livraison = floatval($_POST['frais_livraison'] ?? 0);

  if ($total <= 0) {
    header("Location: ../../pages/paiement.php?error=invalid_amount");
    exit();
  }

  $code_confirmation = rand(1000, 9999);
  $ref_interne = "CASH" . date('YmdHis') . rand(100, 999);

  try {
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
      $nom,
      $email_client, // Variable corrigée
      $phone,
      $total,
      $frais_livraison,
      $commune,
      $quartier,
      $adresse_complete,
      $code_confirmation
    ]);

    $commande_id = $pdo->lastInsertId();

    // 2. INSERTION ARTICLES
    $cart_items = json_decode($_POST['cart_data'] ?? '[]', true);
    if (!empty($cart_items)) {
      $stmtItem = $pdo->prepare("INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire, taille_id, couleur_id) VALUES (?, ?, ?, ?, ?, ?)");
      $getTaille = $pdo->prepare("SELECT id FROM tailles WHERE nom = ? LIMIT 1");
      $getCouleur = $pdo->prepare("SELECT id FROM couleurs WHERE nom = ? LIMIT 1");

      foreach ($cart_items as $item) {
        $tId = null;
        if (!empty($item['size'])) {
          $getTaille->execute([$item['size']]);
          $tId = $getTaille->fetchColumn() ?: null;
        }
        $cId = null;
        if (!empty($item['color'])) {
          $getCouleur->execute([$item['color']]);
          $cId = $getCouleur->fetchColumn() ?: null;
        }
        $stmtItem->execute([$commande_id, $item['id'], $item['quantity'] ?? 1, $item['price'], $tId, $cId]);
      }
    } else {
      throw new Exception("Le panier est vide.");
    }

    // 3. INSERT PAIEMENT
    $stmtPay = $pdo->prepare("INSERT INTO paiements (commande_id, mode_paiement, telephone_paiement, montant, reference_interne, statut_paiement, created_at) VALUES (?, 'CASH', ?, ?, ?, 'en_attente', NOW())");
    $stmtPay->execute([$commande_id, $phone, $total, $ref_interne]);

    $pdo->commit();

    // 4. DÉCLENCHEMENT DU MAIL
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $url_envoi = "$protocol://$_SERVER[HTTP_HOST]/ProjetFelykay/assets/actions/generer_recu.php?id=$commande_id&email=" . urlencode($email_client);

    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    @file_get_contents($url_envoi, false, $ctx);

    // AJOUT : Notification Admin
    require_once '../functions/notification_helper.php';
    notifierNouvelleCommande($commande_id, $nom, 'CASH', $total, $phone);

    header("Location: ../../pages/confirmation_succes.php?method=cash&order=" . $commande_id);
    exit();
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Erreur Cash : " . $e->getMessage());
    header("Location: ../../pages/paiement.php?error=db_error&msg=" . urlencode($e->getMessage()));
    exit();
  }
}
