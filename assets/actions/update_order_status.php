<?php
require_once '../../config/db.php';
require_once '../../includes/function.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Accès refusé']);
  exit;
}

if (isset($_POST['id']) && isset($_POST['statut'])) {
  $id = intval($_POST['id']);
  $statut = $_POST['statut'];

  $query = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
  if ($query->execute([$statut, $id])) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Erreur SQL']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Données manquantes']);
}
