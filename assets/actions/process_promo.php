<?php
require_once '../config/db.php';
require_once '../includes/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom = $_POST['nom'];
  $prix = $_POST['prix'];
  $promo_tag = $_POST['promo_tag'];
  $subtitle = $_POST['subtitle']; // La petite description en rouge
  $is_promo = $_POST['is_promo'];
  $actif_accueil = isset($_POST['actif_accueil']) ? 1 : 0;

  // Gestion de l'image
  $image_path = '';
  if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $img_name = time() . '_' . $_FILES['image']['name'];
    $target = '../assets/img/produits/' . $img_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
      $image_path = 'assets/img/produits/' . $img_name;
    }
  }

  try {
    // Si l'administrateur veut afficher ce produit sur l'accueil
    if ($actif_accueil == 1) {
      // On vérifie combien de produits sont déjà actifs sur l'accueil
      $count = $pdo->query("SELECT COUNT(*) FROM produits WHERE actif_accueil = 1")->fetchColumn();

      if ($count >= 4) {
        // Si on a déjà 4 produits, on désactive le plus ancien pour laisser place au nouveau
        $oldest = $pdo->query("SELECT id FROM produits WHERE actif_accueil = 1 ORDER BY created_at ASC LIMIT 1")->fetchColumn();
        $pdo->prepare("UPDATE produits SET actif_accueil = 0 WHERE id = ?")->execute([$oldest]);
      }
    }

    // Insertion du nouveau produit de promotion
    $sql = "INSERT INTO produits (nom, prix, promo_tag, subtitle, is_promo, image_principale, actif_accueil, created_at, devise) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'USD')";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $prix, $promo_tag, $subtitle, $is_promo, $image_path, $actif_accueil]);

    header('Location: ../admin_dashboard.php?page=promotions&status=success');
  } catch (PDOException $e) {
    header('Location: ../admin_dashboard.php?page=promotions&status=error&msg=' . urlencode($e->getMessage()));
  }
  exit();
}
