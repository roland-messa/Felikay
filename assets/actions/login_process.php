<?php
// C:\wamp64\www\ProjetFelykay\assets\actions\login_process.php
require_once '../../config/db.php'; // session_start() est géré ici

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 1. Vérification de l'empreinte de sécurité
  if (
    !isset($_SESSION['admin_access_gate']) ||
    $_SESSION['admin_access_ip'] !== $_SERVER['REMOTE_ADDR'] ||
    $_SESSION['admin_user_agent'] !== $_SERVER['HTTP_USER_AGENT']
  ) {
    die("Session invalide ou expirée. Veuillez recharger la page de connexion.");
  }

  // 2. Vérification CSRF (Anti-piratage de formulaire)
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

    // 4. VÉRIFICATION SIMPLIFIÉE (Texte brut autorisé)
    $isValid = false;
    if ($user) {
      // On vérifie d'abord le hachage (pour jaden) OU le texte brut (pour Roland)
      if (password_verify($password, $user['password'])) {
        $isValid = true;
      } elseif ($password === $user['password']) {
        $isValid = true;
      }
    }

    if ($isValid) {
      // SUCCÈS : Connexion établie
      session_regenerate_id(true);

      $_SESSION['login_attempts'] = 0;
      $_SESSION['last_activity'] = time();
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_nom'] = $user['nom'] ?? 'Admin';
      $_SESSION['user_role'] = strtolower($user['role']);

      header("Location: ../../pages/admin/admin_dashboard.php");
      exit();
    } else {
      // ÉCHEC : Identifiants incorrects
      $_SESSION['login_attempts'] = $attempts + 1;
      header("Location: ../../pages/admin_login.php?msg=error_login");
      exit();
    }
  } catch (PDOException $e) {
    die("Erreur technique de base de données.");
  }
} else {
  header("Location: ../../pages/admin_login.php");
  exit();
}
