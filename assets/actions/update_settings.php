<?php

session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../../index.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $stmt = $pdo->prepare("INSERT INTO settings (cle, valeur) VALUES (:cle, :valeur) 
                           ON DUPLICATE KEY UPDATE valeur = :valeur, updated_at = NOW()");

  foreach ($_POST as $key => $value) {
    $stmt->execute([
      ':cle' => htmlspecialchars($key),
      ':valeur' => htmlspecialchars($value)
    ]);
  }

  header("Location: ../../pages/admin/admin_dashboard.php?msg=settings_updated");
  exit();
}
