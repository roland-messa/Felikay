<?php
session_start();
require_once '../../config/db.php';
require_once '../functions/payment_helper.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  $user_id = $_SESSION['user_id'] ?? null;
  $gateway = $_POST['gateway'] ?? '';
  $nom_complet = htmlspecialchars($_POST['nom_complet'] ?? 'Client');

  // --- CONFIGURATION DE LA DEVISE ---
  $currency = 'USD';

  // Vérification session
  if (!$user_id) {
    header("Location: ../../pages/connexion.php?error=session_expired");
    exit();
  }

  // Validation Gateway
  $allowed_gateways = ['MP', 'AM', 'OM'];
  if (!in_array($gateway, $allowed_gateways)) {
    header("Location: ../../pages/paiement.php?error=payment_failed&msg=" . urlencode("Mode de paiement non supporté"));
    exit();
  }

  // ====== DONNÉES LIVRAISON ======
  $commune = htmlspecialchars($_POST['commune'] ?? '');
  $quartier = htmlspecialchars($_POST['quartier'] ?? '');
  $avenue = htmlspecialchars($_POST['avenue'] ?? '');
  $numero = htmlspecialchars($_POST['numero'] ?? '');
  $adresse_complete = "Av. $avenue, N° $numero";

  // ====== TÉLÉPHONE ======
  $raw_phone = $_POST['payment_phone'] ?? '';
  $clean_phone = preg_replace('/[^0-9]/', '', $raw_phone);
  $lastNine = substr($clean_phone, -9);
  $phone_to_pay = '243' . $lastNine;

  if (strlen($lastNine) !== 9) {
    header("Location: ../../pages/paiement.php?error=invalid_phone");
    exit();
  }

  // ====== MONTANT ======
  $total_final = floatval($_POST['total_ttc'] ?? 0);
  $frais_livraison = floatval($_POST['frais_livraison'] ?? 0);

  if ($total_final <= 0) {
    header("Location: ../../pages/paiement.php?error=invalid_amount");
    exit();
  }

  $code_confirmation = rand(1000, 9999);

  try {
    // ✅ APPEL API (Multi-devise)
    $paiement = initierPaiementMobile(
      $phone_to_pay,
      $total_final,
      $currency,
      $gateway,
      "Commande Felykay - " . $nom_complet
    );

    if ($paiement['success']) {
      $ref_paiement = $paiement['referenceNo'];

      $pdo->beginTransaction();

      // ====== 1. INSERTION COMMANDE ======
      $stmtCmd = $pdo->prepare("
                INSERT INTO commandes (
                    user_id, nom_complet, telephone, total_ttc, frais_livraison,
                    statut, commune, quartier, adresse_livraison, ville,
                    code_confirmation, created_at
                ) VALUES (?, ?, ?, ?, ?, 'en_attente', ?, ?, ?, 'Kinshasa', ?, NOW())
            ");

      $stmtCmd->execute([
        $user_id,
        $nom_complet,
        $phone_to_pay,
        $total_final,
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

      // ====== 3. INSERTION PAIEMENT ======
      $stmtPay = $pdo->prepare("
                INSERT INTO paiements (
                    commande_id, mode_paiement, telephone_paiement, montant,
                    reference_interne, statut_paiement, created_at
                ) VALUES (?, ?, ?, ?, ?, 'en_attente', NOW())
            ");

      $stmtPay->execute([
        $commande_id,
        $gateway,
        $phone_to_pay,
        $total_final,
        $ref_paiement
      ]);

      $pdo->commit();

      header("Location: ../../pages/attente_paiement.php?ref=" . urlencode($ref_paiement) . "&phone=" . urlencode($phone_to_pay));
      exit();
    } else {
      header("Location: ../../pages/paiement.php?error=payment_failed&msg=" . urlencode($paiement['message']));
      exit();
    }
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("ERREUR ONLINE: " . $e->getMessage());
    header("Location: ../../pages/paiement.php?error=system&msg=" . urlencode($e->getMessage()));
    exit();
  }
}
