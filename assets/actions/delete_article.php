<?php

require_once __DIR__ . '/../../config/db.php';

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);

  try {
    // 1. Récupérer les infos du produit avant suppression
    $stmt = $pdo->prepare("SELECT image_principale FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $produit = $stmt->fetch();

    if ($produit) {
      // Nettoyage du chemin de l'image pour le serveur
      $imgName = str_replace(['../../', '../', 'assets/img/produits/'], '', $produit['image_principale']);
      $imagePath = __DIR__ . '/../../assets/img/produits/' . ltrim($imgName, '/');

      // 2. Supprimer l'image physiquement (si ce n'est pas l'image par défaut)
      if (!empty($imgName) && $imgName !== 'felikay.jpg' && file_exists($imagePath)) {
        unlink($imagePath);
      }

      // 3. Supprimer l'entrée en base de données
      $delete = $pdo->prepare("DELETE FROM produits WHERE id = ?");
      $delete->execute([$id]);
    }

    // Redirection vers le dashboard avec succès
    header("Location: ../../pages/admin/admin_dashboard.php?msg=deleted#section-products");
  } catch (Exception $e) {
    // Redirection en cas d'erreur
    header("Location: ../../pages/admin/admin_dashboard.php?msg=error");
  }
} else {
  header("Location: ../../pages/admin/admin_dashboard.php");
}
exit();
