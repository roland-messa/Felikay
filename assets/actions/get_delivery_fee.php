<?php
require_once '../../config/db.php';

$commune = $_GET['commune'] ?? '';
$quartier = $_GET['quartier'] ?? '';

if (!empty($commune) && !empty($quartier)) {

  $stmt = $pdo->prepare("SELECT frais_usd FROM zones_livraison WHERE LOWER(TRIM(commune)) = LOWER(TRIM(?)) AND LOWER(TRIM(quartier)) = LOWER(TRIM(?)) LIMIT 1");
  $stmt->execute([$commune, $quartier]);
  $zone = $stmt->fetch();

  if ($zone) {
    echo json_encode(['success' => true, 'frais' => (float)$zone['frais_usd']]);
    exit;
  }
}
echo json_encode(['success' => false, 'frais' => 0]);
