<?php
session_start();
require_once '../../config/db.php';

// Sécurité : Seul l'admin (Roland) peut assigner
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
  die("Accès réservé à l'administrateur.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $commande_id = intval($_POST['commande_id']);
  $livreur_id = intval($_POST['livreur_id']);

  if ($commande_id > 0 && $livreur_id > 0) {
    $stmt = $pdo->prepare("UPDATE commandes SET livreur_id = ?, statut = 'expedie' WHERE id = ?");
    if ($stmt->execute([$livreur_id, $commande_id])) {
      header("Location: ../../pages/admin/dashboard.php?assign_success=1");
    } else {
      header("Location: ../../pages/admin/dashboard.php?error=assign_failed");
    }
  }
}
exit();
