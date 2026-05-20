<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'livreur') {
  die("Accès refusé");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $commande_id = intval($_POST['commande_id']);
  $code_saisi = trim($_POST['code_saisi']);
  $user_id = $_SESSION['user_id'];

  try {
    $stmt = $pdo->prepare("SELECT code_confirmation FROM commandes WHERE id = ? AND livreur_id = ? AND statut = 'expedie'");
    $stmt->execute([$commande_id, $user_id]);
    $cmd = $stmt->fetch();

    if ($cmd && $cmd['code_confirmation'] == $code_saisi) {
      $pdo->beginTransaction();

      // Passage en statut livré & payé
      $update = $pdo->prepare("UPDATE commandes SET statut = 'livre_payer', updated_at = NOW() WHERE id = ?");
      $update->execute([$commande_id]);

      // Log dans l'historique pour la traçabilité
      $log = $pdo->prepare("INSERT INTO historique_commandes (order_id, user_id, ancien_statut, nouveau_statut, action_details, created_at) VALUES (?, ?, 'expedie', 'livre_payer', 'Livraison validée par code', NOW())");
      $log->execute([$commande_id, $user_id]);

      $pdo->commit();
      header("Location: ../../pages/admin/livreur_dashboard.php?success=1");
    } else {
      header("Location: ../../pages/admin/livreur_dashboard.php?error=bad_code");
    }
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header("Location: ../../pages/admin/livreur_dashboard.php?error=db_error");
  }
}
