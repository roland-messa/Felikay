<?php
session_start();
ini_set('display_errors', 0);
header('Content-Type: application/json');
ob_start();

require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Accès refusé']);
  exit;
}

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Données manquantes']);
  exit;
}

$id = intval($_POST['order_id']);
$new_status = trim($_POST['status']);
$livreur_id = !empty($_POST['livreur_id']) ? intval($_POST['livreur_id']) : null;
$admin_id = $_SESSION['user_id'] ?? 0;

try {
  $pdo->beginTransaction();

  $check = $pdo->prepare("SELECT id, statut FROM commandes WHERE id = ? LIMIT 1");
  $check->execute([$id]);
  $commande = $check->fetch(PDO::FETCH_ASSOC);

  if (!$commande) throw new Exception("Commande introuvable.");

  $old_status = $commande['statut'];

  // MATRICE DES TRANSITIONS CORRIGÉE (Autorise l'archivage depuis n'importe quel état final)
  $allowedTransitions = [
    'en_attente'         => ['expedie', 'livre_payer', 'annule', 'archive'],
    'expedie'            => ['annule', 'livre', 'livre_payer', 'archive'],
    'paiement_confirmer' => ['expedie', 'annule', 'archive'],
    'paye'               => ['paye_annule', 'archive'],
    'annule'             => ['archive'],
    'paye_annule'        => ['archive'],
    'livre_payer'        => ['archive'],
    'livre'              => ['archive']
  ];

  if (isset($allowedTransitions[$old_status]) && !in_array($new_status, $allowedTransitions[$old_status])) {
    throw new Exception("Transition de $old_status vers $new_status interdite.");
  }

  if ($new_status === 'expedie' && !$livreur_id) {
    throw new Exception("Veuillez sélectionner un livreur pour l'expédition.");
  }

  // MISE À JOUR
  $sql = ($new_status === 'expedie')
    ? "UPDATE commandes SET statut = ?, livreur_id = ?, updated_at = NOW() WHERE id = ?"
    : "UPDATE commandes SET statut = ?, updated_at = NOW() WHERE id = ?";

  $params = ($new_status === 'expedie') ? [$new_status, $livreur_id, $id] : [$new_status, $id];
  $pdo->prepare($sql)->execute($params);

  // LOGS
  $details = ($new_status === 'expedie') ? "Expédition (Livreur #$livreur_id)" : "Changement de statut : $new_status";
  $pdo->prepare("INSERT INTO historique_commandes (order_id, user_id, ancien_statut, nouveau_statut, action_details, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
    ->execute([$id, $admin_id, $old_status, $new_status, $details]);

  $pdo->commit();
  ob_clean();
  echo json_encode(['success' => true]);
} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  ob_clean();
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
ob_end_flush();
