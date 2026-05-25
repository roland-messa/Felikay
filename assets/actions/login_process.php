<?php
// C:\wamp64\www\ProjetFelykay\assets\actions\login_process.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 1. Vérification de l'empreinte de sécurité
  if (
    !isset($_SESSION['admin_access_gate']) ||
    $_SESSION['admin_access_ip'] !== $_SERVER['REMOTE_ADDR'] ||
    $_SESSION['admin_user_agent'] !== $_SERVER['HTTP_USER_AGENT']
  ) {
    die("Session invalide ou expirée. Veuillez recharger la page de connexion.");
  }

  // 2. Vérification CSRF
  if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Erreur de sécurité (CSRF). Veuillez rafraîchir la page.");
  }

  // 3. Limitation des tentatives
  $attempts = $_SESSION['login_attempts'] ?? 0;
  if ($attempts >= 5) {
    header("Location: ../../pages/403_blocked.php");
    exit();
  }

  $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
  $password = trim($_POST['password']);

  try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $isValid = false;
    if ($user) {
      if (password_verify($password, $user['password'])) {
        $isValid = true;
      } elseif ($password === $user['password']) {
        $isValid = true;
      }
    }

    if ($isValid) {
      // Régénération de l'ID de session pour éviter la fixation de session
      session_regenerate_id(true);

      $_SESSION['login_attempts'] = 0;
      $_SESSION['last_activity'] = time(); // Initialisation immédiate de l'activité
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_nom'] = $user['nom'] ?? 'Utilisateur';

      $role = strtolower($user['role']);
      $_SESSION['user_role'] = $role;

      if (isset($_SESSION['redirect_after_login'])) {
        $destination = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        header("Location: " . $destination);
      } else {
        if ($role === 'admin') {
          header("Location: ../../pages/admin/admin_dashboard.php");
        } elseif ($role === 'livreur') {
          header("Location: ../../pages/admin/livreur_dashboard.php");
        } else {
          header("Location: ../../index.php");
        }
      }
      exit();
    } else {
      $_SESSION['login_attempts'] = $attempts + 1;
      header("Location: ../../pages/admin_login.php?msg=error_login");
      exit();
    }
  } catch (PDOException $e) {
    error_log($e->getMessage());
    die("Une erreur technique est survenue. Veuillez réessayer plus tard.");
  }
} else {
  header("Location: ../../pages/admin_login.php");
  exit();
}
