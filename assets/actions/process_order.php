<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../../pages/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$nom_complet = htmlspecialchars($_POST['nom_complet'] ?? '');
$adresse = htmlspecialchars($_POST['adresse'] ?? '');
$telephone = htmlspecialchars($_POST['phone'] ?? '');
$total_ttc = floatval($_POST['total_ttc'] ?? 0);
$ville = htmlspecialchars($_POST['ville'] ?? 'Kinshasa');
$methode_paiement = $_POST['payment_method'] ?? 'delivery';

$cart_items = json_decode($_POST['cart_data'], true);

if (empty($cart_items)) {
  header("Location: ../../index.php?msg=error_empty_cart");
  exit();
}

try {
  $pdo->beginTransaction();

  // 2. MISE À JOUR DES INFOS DE L'UTILISATEUR
  $updateUser = $pdo->prepare("UPDATE users SET nom = ?, telephone = ?, adresse = ?, ville = ? WHERE id = ?");
  $updateUser->execute([$nom_complet, $telephone, $adresse, $ville, $user_id]);

  // 3. INSERTION DE LA COMMANDE
  $sqlOrder = "INSERT INTO commandes (user_id, total_ttc, adresse_livraison, ville, statut, methode_paiement, created_at) 
                 VALUES (:user, :total, :adresse, :ville, 'en_attente', :methode, NOW())";

  $stmtOrder = $pdo->prepare($sqlOrder);
  $stmtOrder->execute([
    ':user'    => $user_id,
    ':total'   => $total_ttc,
    ':adresse' => $adresse,
    ':ville'   => $ville,
    ':methode' => $methode_paiement
  ]);

  $commande_id = $pdo->lastInsertId();

  // 4. INSERTION DES DÉTAILS DE LA COMMANDE
  $sqlDetail = "INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire, taille_id, couleur_id) 
                  VALUES (:cmd, :prod, :qte, :prix, :taille, :couleur)";
  $stmtDetail = $pdo->prepare($sqlDetail);

  $checkProdExist = $pdo->prepare("SELECT id FROM produits WHERE id = ?");
  $getTaille = $pdo->prepare("SELECT id FROM tailles WHERE nom = ? LIMIT 1");
  $getCouleur = $pdo->prepare("SELECT id FROM couleurs WHERE nom = ? LIMIT 1");

  foreach ($cart_items as $item) {

    $checkProdExist->execute([$item['id']]);
    if (!$checkProdExist->fetch()) {

      $pdo->rollBack();
      die("Erreur : Le produit '" . ($item['name'] ?? 'Inconnu') . "' n'est plus disponible. Veuillez vider votre panier et recommencer.");
    }

    // Recherche de l'ID de la taille
    $tId = null;
    if (!empty($item['size'])) {
      $getTaille->execute([$item['size']]);
      $tRow = $getTaille->fetch();
      $tId = $tRow ? $tRow['id'] : null;
    }

    // Recherche de l'ID de la couleur
    $cId = null;
    if (!empty($item['color'])) {
      $getCouleur->execute([$item['color']]);
      $cRow = $getCouleur->fetch();
      $cId = $cRow ? $cRow['id'] : null;
    }

    // Insertion du détail
    $stmtDetail->execute([
      ':cmd'     => $commande_id,
      ':prod'    => $item['id'],
      ':qte'     => $item['quantity'] ?? 1,
      ':prix'    => $item['price'],
      ':taille'  => $tId,
      ':couleur' => $cId
    ]);
  }

  $pdo->commit();

  // 5. REDIRECTION VERS LA PAGE DE CONFIRMATION
  header("Location: ../../pages/confirmation_succes.php?id=" . $commande_id);
  exit();
} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  die("Erreur critique lors de la commande : " . $e->getMessage());
}
