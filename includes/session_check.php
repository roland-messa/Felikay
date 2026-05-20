<?php
// Session déjà démarrée dans les pages parentes
$timeout_duration = 3600; // 1 heure en secondes

if (isset($_SESSION['user_id'])) {
  if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
    // Trop tard, on détruit la session
    session_unset();
    session_destroy();
    header("Location: ../pages/login.php?msg=session_expired");
    exit();
  }

  $_SESSION['last_activity'] = time();
}
