<?php
// C:\wamp64\www\ProjetFelykay\assets\actions\update_article.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)$_POST['id'];
  $nom = htmlspecialchars($_POST['nom']);
  $prix = floatval($_POST['prix']);
  $stock = (int)$_POST['stock_total'];
  $categorie_id = $_POST['categorie_id'] ?? null;
  $genre = $_POST['genre'] ?? null;
  $tranche_age = $_POST['tranche_age'] ?? null;

  try {
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
        // On stocke le nom du fichier
        $sql_image = ", image_principale = :img";
        $params[':img'] = $new_image;
      }
    }

    // Ajout de "updated_at = NOW()" pour enregistrer la date de modification
    $sql = "UPDATE produits 
                SET nom = :nom, 
                    prix = :prix, 
                    stock_total = :stock, 
                    tranche_age = :age, 
                    genre = :genre, 
                    categorie_id = :cat,
                    updated_at = NOW()
                    $sql_image 
                WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: ../../pages/admin/admin_dashboard.php?msg=success#section-products");
  } catch (PDOException $e) {
    // En cas d'erreur SQL, on redirige avec un message d'erreur
    header("Location: ../../pages/admin/admin_dashboard.php?msg=error");
  }
  exit();
}
