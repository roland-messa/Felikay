<?php

require_once __DIR__ . '/../../config/db.php';

// On vide le tableau de session
$_SESSION = array();

// On détruit le cookie de session dans le navigateur
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
  );
}

// On détruit la session sur le serveur
session_destroy();

// Redirection vers la page de login admin
header("Location: /ProjetFelykay/pages/admin_login.php");
exit;
