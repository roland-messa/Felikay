<?php
require_once __DIR__ . '/../../config/db.php';

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);

  try {

    $stmt = $pdo->prepare("SELECT image_principale FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $produit = $stmt->fetch();

    if ($produit) {
      $imagePath = "../../" . ltrim($produit['image_principale'], './');
      if (file_exists($imagePath)) {
        unlink($imagePath);
      }

      $delete = $pdo->prepare("DELETE FROM produits WHERE id = ?");
      $delete->execute([$id]);
    }

    header("Location: ../../pages/admin/admin_dashboard.php?status=deleted");
  } catch (Exception $e) {
    header("Location: ../../pages/admin/admin_dashboard.php?status=error");
  }
}
