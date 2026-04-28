<?php
session_start();
require_once '../../config/db.php';

// On vérifie seulement que la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../../index.php");
  exit();
}

// Récupération des données (User connecté ou Invité)
$user_id = $_SESSION['user_id'] ?? null;
$email_client = htmlspecialchars($_POST['email'] ?? '');
$nom_complet = htmlspecialchars($_POST['nom_complet'] ?? '');
$adresse = htmlspecialchars($_POST['adresse'] ?? '');
$telephone = htmlspecialchars($_POST['phone'] ?? '');
$total_ttc = floatval($_POST['total_ttc'] ?? 0);
$ville = htmlspecialchars($_POST['ville'] ?? 'Kinshasa');
$methode_paiement = $_POST['payment_method'] ?? 'delivery';

// Panier JSON
$cart_items = json_decode($_POST['cart_data'], true);

if (empty($cart_items)) {
  header("Location: ../../index.php?msg=error_empty_cart");
  exit();
}

try {
  $pdo->beginTransaction();

  // 1. SI L'UTILISATEUR EST CONNECTÉ : On met à jour son profil automatiquement
  if ($user_id) {
    $updateUser = $pdo->prepare("UPDATE users SET nom = ?, telephone = ?, adresse = ?, ville = ? WHERE id = ?");
    $updateUser->execute([$nom_complet, $telephone, $adresse, $ville, $user_id]);
  }

  // 2. INSERTION DE LA COMMANDE
  // Note : email_client permet de garder le contact même si user_id est NULL (invité)
  $sqlOrder = "INSERT INTO commandes (user_id, email_invite, total_ttc, adresse_livraison, ville, statut, methode_paiement, created_at) 
                 VALUES (:user, :email, :total, :adresse, :ville, 'en_attente', :methode, NOW())";

  $stmtOrder = $pdo->prepare($sqlOrder);
  $stmtOrder->execute([
    ':user'    => $user_id, // Sera NULL si non connecté
    ':email'   => $email_client,
    ':total'   => $total_ttc,
    ':adresse' => $adresse,
    ':ville'   => $ville,
    ':methode' => $methode_paiement
  ]);

  $commande_id = $pdo->lastInsertId();

  // 3. INSERTION DES DÉTAILS DE LA COMMANDE
  $sqlDetail = "INSERT INTO commande_details (commande_id, produit_id, quantite, prix_unitaire, taille_id, couleur_id) 
                  VALUES (:cmd, :prod, :qte, :prix, :taille, :couleur)";
  $stmtDetail = $pdo->prepare($sqlDetail);

  $getTaille = $pdo->prepare("SELECT id FROM tailles WHERE nom = ? LIMIT 1");
  $getCouleur = $pdo->prepare("SELECT id FROM couleurs WHERE nom = ? LIMIT 1");

  foreach ($cart_items as $item) {
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

  // 4. REDIRECTION
  if ($methode_paiement === 'online') {
    header("Location: ../../pages/attente_paiement.php?id=" . $commande_id);
  } else {
    header("Location: ../../pages/confirmation_succes.php?id=" . $commande_id);
  }
  exit();
} catch (Exception $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }
  die("Erreur critique lors de la commande : " . $e->getMessage());
}
