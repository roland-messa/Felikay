<?php

session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../../index.php?error=unauthorized");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $commande_id = isset($_POST['commande_id']) ? intval($_POST['commande_id']) : 0;
  $livreur_id = isset($_POST['livreur_id']) ? intval($_POST['livreur_id']) : 0;

  if ($commande_id > 0 && $livreur_id > 0) {
    try {

      $stmt = $pdo->prepare("UPDATE commandes SET livreur_id = ?, statut = 'expedie', updated_at = NOW() WHERE id = ?");
      if ($stmt->execute([$livreur_id, $commande_id])) {
        header("Location: ../../pages/admin/dashboard.php?tab=orders&assign_success=1");
      } else {
        header("Location: ../../pages/admin/dashboard.php?tab=orders&error=assign_failed");
      }
    } catch (PDOException $e) {
      header("Location: ../../pages/admin/dashboard.php?tab=orders&error=" . urlencode($e->getMessage()));
    }
  } else {
    header("Location: ../../pages/admin/dashboard.php?tab=orders&error=missing_data");
  }
}
exit();
