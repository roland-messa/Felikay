<?php
require_once __DIR__ . '/../../config/db.php';
$id = $_GET['id'] ?? null;

if ($id) {
  // On inverse le statut : si actif -> inactif, si inactif -> actif
  $pdo->query("UPDATE livreurs SET statut = IF(statut = 'actif', 'inactif', 'actif') WHERE id = " . intval($id));
  header("Location: ../../pages/admin/dashboard.php?tab=delivery&success=status_updated");
} else {
  header("Location: ../../pages/admin/dashboard.php?tab=delivery&error=id_missing");
}
exit;
