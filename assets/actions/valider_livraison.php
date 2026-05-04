<?php
session_start();
require_once '../../config/db.php';

// SÉCURITÉ : On vérifie juste que c'est un livreur (ou admin) qui valide
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['livreur', 'admin'])) {
  die("Accès non autorisé.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $commande_id = intval($_POST['commande_id']);
  $code_saisi = trim($_POST['code_saisi']);

  // MODIFICATION : On vérifie le code uniquement pour les commandes "expédiées"
  $stmt = $pdo->prepare("SELECT code_confirmation FROM commandes WHERE id = ? AND statut = 'expedie'");
  $stmt->execute([$commande_id]);
  $cmd = $stmt->fetch();

  if ($cmd && $cmd['code_confirmation'] == $code_saisi) {
    $pdo->beginTransaction();
    try {
      // Mise à jour de la commande
      $updateCmd = $pdo->prepare("UPDATE commandes SET statut = 'livre', updated_at = NOW() WHERE id = ?");
      $updateCmd->execute([$commande_id]);

      // Mise à jour du paiement
      $updatePay = $pdo->prepare("UPDATE paiements SET statut_paiement = 'reussi' WHERE commande_id = ?");
      $updatePay->execute([$commande_id]);

      // Gestion des stocks (Conservation de ton code actuel)
      $stmtItems = $pdo->prepare("SELECT produit_id, quantite FROM commande_items WHERE commande_id = ?");
      $stmtItems->execute([$commande_id]);
      $items = $stmtItems->fetchAll();

      foreach ($items as $item) {
        $updateStock = $pdo->prepare("UPDATE produits SET stock_total = stock_total - ? WHERE id = ?");
        $updateStock->execute([$item['quantite'], $item['produit_id']]);
      }

      $pdo->commit();
      header("Location: ../../pages/admin/livreur_dashboard.php?success=livre");
      exit();
    } catch (Exception $e) {
      $pdo->rollBack();
      header("Location: ../../pages/admin/livreur_dashboard.php?error=db_error");
      exit();
    }
  } else {
    header("Location: ../../pages/admin/livreur_dashboard.php?error=invalid_code&id=$commande_id");
    exit();
  }
}
