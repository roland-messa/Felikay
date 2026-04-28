<?php
require_once '../../config/db.php';

$ref = $_GET['ref'] ?? '';

if (!empty($ref)) {
  $stmt = $pdo->prepare("SELECT status FROM orders WHERE payment_ref = ?");
  $stmt->execute([$ref]);
  $order = $stmt->fetch();

  if ($order) {
    echo json_encode(['status' => $order['status']]);
    exit;
  }
}

echo json_encode(['status' => 'not_found']);
