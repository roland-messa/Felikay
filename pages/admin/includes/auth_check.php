<?php
// C:\wamp64\www\ProjetFelykay\pages\admin\includes\auth_check.php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// 1. Définition du timeout (Passage à 30 minutes pour laisser le temps de travailler)
$timeout = 1800; // 1800 secondes = 30 minutes

// 2. Vérification de l'activité
if (isset($_SESSION['last_activity'])) {
  $duration = time() - $_SESSION['last_activity'];
  if ($duration > $timeout) {

    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    unset($_SESSION['user_id']);
    unset($_SESSION['user_role']);

    header("Location: /ProjetFelykay/pages/admin_login.php?msg=timeout");
    exit();
  }
}

// 3. Mise à jour du timestamp d'activité
$_SESSION['last_activity'] = time();

// 4. Protection contre l'accès direct sans connexion
if (!isset($_SESSION['user_role'])) {
  // Si on essaie d'accéder à une page admin sans être connecté
  $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
  header("Location: /ProjetFelykay/pages/admin_login.php");
  exit();
}
