<?php
// assets/actions/update_password.php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_pass = $_POST['new_pass'];
  $confirm_pass = $_POST['confirm_pass'];
  $user_id = $_SESSION['user_id'];

  if ($new_pass === $confirm_pass && !empty($new_pass)) {
    $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_pass, $user_id]);

    // Déconnexion automatique après changement
    session_destroy();
    header("Location: ../../pages/login.php?msg=password_changed");
    exit();
  } else {
    header("Location: ../../pages/admin/admin_dashboard.php?msg=error_password");
    exit();
  }
}
