<?php
require_once '../../config/security.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 1. Vérification de l'empreinte
  if (
    !isset($_SESSION['admin_access_gate']) ||
    $_SESSION['admin_access_ip'] !== $_SERVER['REMOTE_ADDR'] ||
    $_SESSION['admin_user_agent'] !== $_SERVER['HTTP_USER_AGENT']
  ) {
    die("Accès suspect (Empreinte invalide).");
  }

  // 2. Token CSRF
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Erreur de validation du formulaire.");
  }
  unset($_SESSION['csrf_token']);

  // 3. Gestion des tentatives
  $attempts = $_SESSION['login_attempts'] ?? 0;
  if ($attempts >= 5 && isset($_SESSION['blocked_until']) && $_SESSION['blocked_until'] > time()) {
    header("Location: ../../pages/admin_login.php?msg=too_many_attempts");
    exit();
  }

  $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
  $password = trim($_POST['password']);

  try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $password === $user['password']) {

      $_SESSION['login_attempts'] = 0;
      $_SESSION['last_activity'] = time();
      session_regenerate_id(true);

      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_role'] = strtolower($user['role']);

      // Redirection vers le dossier admin
      header("Location: ../../pages/admin/admin_dashboard.php");
      exit();
    } else {
      $_SESSION['login_attempts'] = ($attempts + 1);
      header("Location: ../../pages/admin_login.php?msg=error_login");
      exit();
    }
  } catch (PDOException $e) {
    die("Erreur base de données.");
  }
}
