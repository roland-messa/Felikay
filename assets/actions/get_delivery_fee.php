<?php

header('Content-Type: application/json');
require_once '../../config/db.php';

// Récupération et nettoyage des paramètres
$commune = isset($_GET['commune']) ? trim($_GET['commune']) : '';
$quartier = isset($_GET['quartier']) ? trim($_GET['quartier']) : '';

if (!empty($commune) && !empty($quartier)) {
  try {

    $stmt = $pdo->prepare("
            SELECT frais_usd 
            FROM zones_livraison 
            WHERE LOWER(commune) = LOWER(?) 
            AND LOWER(quartier) = LOWER(?) 
            LIMIT 1
        ");
    $stmt->execute([$commune, $quartier]);
    $zone = $stmt->fetch();

    if ($zone) {
      echo json_encode([
        'success' => true,
        'frais' => (float)$zone['frais_usd']
      ]);
      exit;
    }
  } catch (PDOException $e) {
    echo json_encode([
      'success' => false,
      'message' => 'Erreur base de données',
      'frais' => 0
    ]);
    exit;
  }
}

// Réponse par défaut si rien n'est trouvé
echo json_encode([
  'success' => false,
  'message' => 'Zone non répertoriée',
  'frais' => 0
]);
exit;
