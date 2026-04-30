<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

$ref = $_GET['ref'] ?? '';

if (!empty($ref)) {
  // On cherche dans la table commandes de Felikay
  $stmt = $pdo->prepare("SELECT statut FROM commandes WHERE payment_ref = ?");
  $stmt->execute([$ref]);
  $commande = $stmt->fetch();

  if ($commande) {
    echo json_encode(['status' => $commande['statut']]);
    exit;
  }
}

echo json_encode(['status' => 'not_found']);
