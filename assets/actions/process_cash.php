<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $user_id = $_SESSION['user_id'] ?? null;

  if (!$user_id) {
    header("Location: ../../pages/connexion.php?error=session_expired");
    exit();
  }

  // ====== RÉCUPÉRATION DONNÉES ======
  $nom       = htmlspecialchars($_POST['nom_complet'] ?? '');
  $phone_raw = $_POST['phone'] ?? '';
  $commune   = htmlspecialchars($_POST['commune'] ?? '');
  $quartier  = htmlspecialchars($_POST['quartier'] ?? '');
  $avenue    = htmlspecialchars($_POST['avenue'] ?? '');
  $numero    = htmlspecialchars($_POST['numero'] ?? '');
  $adresse_complete = "Av. $avenue, N° $numero";

  // ====== NORMALISATION TELEPHONE ======
  $clean_phone = preg_replace('/[^0-9]/', '', $phone_raw);
  $phone = '243' . substr($clean_phone, -9);

  // ====== MONTANT ======
  // Utilisation de floatval pour la précision multi-devise
  $total = floatval($_POST['total_ttc'] ?? 0);
  $frais_livraison = floatval($_POST['frais_livraison'] ?? 0);

  if ($total <= 0) {
    header("Location: ../../pages/paiement.php?error=invalid_amount");
    exit();
  }

  // Génération d'un code de confirmation pour le suivi
  $code_confirmation = rand(1000, 9999);

  try {
    $pdo->beginTransaction();

    // ====== 1. INSERT COMMANDE ======
    $stmtCmd = $pdo->prepare("
            INSERT INTO commandes (
                user_id, nom_complet, telephone, total_ttc, frais_livraison,
                statut, commune, quartier, adresse_livraison, ville, 
                code_confirmation, created_at
            ) VALUES (?, ?, ?, ?, ?, 'en_attente', ?, ?, ?, 'Kinshasa', ?, NOW())
        ");

    $stmtCmd->execute([
      $user_id,
      $nom,
      $phone,
      $total,
      $frais_livraison,
      $commune,
      $quartier,
      $adresse_complete,
      $code_confirmation
    ]);

    $commande_id = $pdo->lastInsertId();

    // ====== 2. INSERTION ARTICLES ======
    $cart_json = $_POST['cart_data'] ?? '[]';
    $cart_items = json_decode($cart_json, true);

    if (!empty($cart_items)) {
      $stmtItem = $pdo->prepare("
                INSERT INTO commande_details (
                    commande_id, produit_id, quantite, prix_unitaire, taille_id, couleur_id
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");

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

        $stmtItem->execute([
          $commande_id,
          $item['id'],
          $item['quantity'] ?? 1,
          $item['price'],
          $tId,
          $cId
        ]);
      }
    } else {
      throw new Exception("Le panier est vide.");
    }

    // ====== 3. INSERT PAIEMENT ======
    $stmtPay = $pdo->prepare("
            INSERT INTO paiements (
                commande_id, mode_paiement, telephone_paiement, montant,
                statut_paiement, created_at
            ) VALUES (?, 'CASH', ?, ?, 'en_attente', NOW())
        ");

    $stmtPay->execute([$commande_id, $phone, $total]);

    $pdo->commit();

    header("Location: ../../pages/confirmation_succes.php?method=cash&order=" . $commande_id);
    exit();
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Erreur Cash : " . $e->getMessage());
    header("Location: ../../pages/paiement.php?error=db_error&msg=" . urlencode($e->getMessage()));
    exit();
  }
}
