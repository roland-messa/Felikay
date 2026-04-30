<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $commune = trim($_POST['commune']);
  $quartier = trim($_POST['quartier']);
  $frais_fc = intval($_POST['frais_fc']);
  $frais_usd = floatval($_POST['frais_usd']);

  try {
    // Requête intelligente : Insère ou met à jour si la zone existe déjà
    $sql = "INSERT INTO zones_livraison (commune, quartier, frais_fc, frais_usd) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                frais_fc = VALUES(frais_fc), 
                frais_usd = VALUES(frais_usd)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$commune, $quartier, $frais_fc, $frais_usd]);

    header("Location: ../../pages/admin/admin_dashboard.php?tab=communes&status=success");
  } catch (Exception $e) {
    // Optionnel : tu peux logger l'erreur $e->getMessage() pour le debug
    header("Location: ../../pages/admin/admin_dashboard.php?tab=communes&status=error");
  }
  exit;
}
