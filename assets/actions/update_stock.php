<?php

ini_set('display_errors', 0);
error_reporting(0);

// 2. Initialiser la session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

header('Content-Type: application/json');

// 3. Inclusion de la base de données
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_role'])) {
  echo json_encode([
    'success' => false,
    'message' => 'Session expirée. Veuillez vous reconnecter.'
  ]);
  exit;
}

$_SESSION['last_activity'] = time();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? null;
  $stock = $_POST['stock'] ?? null;

  if ($id !== null && $stock !== null) {
    try {
      $stmt = $pdo->prepare("UPDATE produits SET stock_total = ? WHERE id = ?");
      $stmt->execute([(int)$stock, (int)$id]);

      echo json_encode(['success' => true]);
    } catch (Exception $e) {
      echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
  }
} else {
  echo json_encode(['success' => false, 'message' => 'Requête non autorisée']);
}
exit;
