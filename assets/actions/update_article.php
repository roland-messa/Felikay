<?php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1. Récupération sécurisée des données
  $id = (int)$_POST['id'];
  $nom = htmlspecialchars($_POST['nom']);
  $prix = $_POST['prix'];
  $stock = (int)$_POST['stock_total'];

  $categorie_id = $_POST['categorie_id'] ?? null;
  $genre = $_POST['genre'] ?? null;
  $tranche_age = $_POST['tranche_age'] ?? null;

  try {
    // 2. Préparation des paramètres de base
    $params = [
      ':nom'   => $nom,
      ':prix'  => $prix,
      ':stock' => $stock,
      ':age'   => $tranche_age,
      ':genre' => $genre,
      ':cat'   => $categorie_id,
      ':id'    => $id
    ];

    $sql_image = "";
    if (!empty($_FILES['image_principale']['name'])) {
      $new_image = uploadImage($_FILES['image_principale']);
      if ($new_image) {
        $sql_image = ", image_principale = :img";
        $params[':img'] = $new_image;
      }
    }

    $sql = "UPDATE produits 
                SET nom = :nom, 
                    prix = :prix, 
                    stock_total = :stock, 
                    tranche_age = :age, 
                    genre = :genre, 
                    categorie_id = :cat 
                    $sql_image 
                WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: ../../pages/admin/admin_dashboard.php?msg=success");
  } catch (PDOException $e) {

    header("Location: ../../pages/admin/admin_dashboard.php?msg=error");
  }
  exit();
}
