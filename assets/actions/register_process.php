<?php
// C:\wamp64\www\ProjetFelykay\actions\register_process.php
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom = htmlspecialchars($_POST['nom']);
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Sécurité max
  $telephone = htmlspecialchars($_POST['telephone'] ?? '');

  $role = 'client';

  try {
    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
      header("Location: ../pages/register.php?msg=error_email_exists");
      exit();
    }

    // Insertion du nouvel utilisateur
    $sql = "INSERT INTO users (nom, email, password, telephone, role, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $email, $password, $telephone, $role]);

    // Une fois inscrit, on l'envoie vers le login
    header("Location: ../pages/login.php?msg=success_registration");
    exit();
  } catch (PDOException $e) {
    die("Erreur lors de l'inscription : " . $e->getMessage());
  }
}
