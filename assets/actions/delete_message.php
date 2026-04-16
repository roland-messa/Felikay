<?php

require_once '../../config/db.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
  $id = intval($_GET['id']);

  try {

    $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
    $execute = $stmt->execute([$id]);

    if ($execute) {

      header("Location: ../../pages/admin/admin_dashboard.php?tab=messages&status=deleted");
      exit();
    }
  } catch (PDOException $e) {

    header("Location: ../../pages/admin/admin_dashboard.php?tab=messages&status=error");
    exit();
  }
} else {

  header("Location: ../../pages/admin/admin_dashboard.php?tab=messages");
  exit();
}
