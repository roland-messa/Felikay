<?php
require_once '../../config/security.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 1. Vérification de l'empreinte (Invisible Gate)
  if (
    !isset($_SESSION['admin_access_gate']) ||
    $_SESSION['admin_access_ip'] !== $_SERVER['REMOTE_ADDR'] ||
    $_SESSION['admin_user_agent'] !== $_SERVER['HTTP_USER_AGENT']
  ) {
    die("Accès suspect (Empreinte invalide).");
  }

  // 2. Vérification du Token CSRF
  // Correction : On vérifie l'existence dans POST et SESSION avant la comparaison
  if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Erreur de validation du formulaire.");
  }

  // 3. Gestion des tentatives (Blocage à 5 tentatives)
  $attempts = $_SESSION['login_attempts'] ?? 0;

  if ($attempts >= 5) {
    // Redirection vers la page "Accès Restreint"
    header("Location: ../../pages/403_blocked.php");
    exit();
  }

  $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
  $password = trim($_POST['password']);

  try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérification du mot de passe
    if ($user && $password === $user['password']) {

      // SUCCÈS : On peut maintenant nettoyer le token CSRF
      unset($_SESSION['csrf_token']);

      // Réinitialisation du compteur et sécurisation de la session
      $_SESSION['login_attempts'] = 0;
      $_SESSION['last_activity'] = time();
      session_regenerate_id(true);

      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_role'] = strtolower($user['role']);

      header("Location: ../../pages/admin/admin_dashboard.php");
      exit();
    } else {
      // ÉCHEC : On incrémente les tentatives
      $new_attempts = $attempts + 1;
      $_SESSION['login_attempts'] = $new_attempts;

      if ($new_attempts >= 5) {
        // Définir un temps de blocage (optionnel)
        $_SESSION['blocked_until'] = time() + 1800; // 30 minutes
        header("Location: ../../pages/403_blocked.php");
      } else {
        header("Location: ../../pages/admin_login.php?msg=error_login");
      }
      exit();
    }
  } catch (PDOException $e) {
    die("Erreur base de données.");
  }
} else {
  // Si on tente d'accéder au script sans POST
  header("Location: ../../pages/admin_login.php");
  exit();
}
