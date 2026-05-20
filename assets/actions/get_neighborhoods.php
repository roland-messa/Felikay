<?php
require_once '../../config/db.php';

$commune = $_GET['commune'] ?? '';

if (!$commune) {
  echo json_encode([]);
  exit;
}

$stmt = $pdo->prepare("SELECT DISTINCT quartier, frais_usd FROM zones_livraison WHERE commune = ? ORDER BY quartier ASC");
$stmt->execute([$commune]);
$quartiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($quartiers);
