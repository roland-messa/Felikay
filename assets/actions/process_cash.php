<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_id = $_SESSION['user_id'];
  $nom = $_POST['nom_complet'];
  $adresse = $_POST['adresse'];
  $ville = $_POST['ville'];
  $total = $_POST['total_ttc'];
  $cart_data = $_POST['cart_data'];

  try {
    $stmt = $pdo->prepare("INSERT INTO commandes (user_id, nom_client, adresse, ville, total, items, statut_paiement, methode) VALUES (?, ?, ?, ?, ?, ?, 'En attente', 'Cash')");
    $stmt->execute([$user_id, $nom, $adresse, $ville, $total, $cart_data]);

    // Redirection vers succès
    header("Location: ../../pages/confirmation_succes.php?method=cash");
    exit();
  } catch (PDOException $e) {
    die("Erreur lors de l'enregistrement : " . $e->getMessage());
  }
}
