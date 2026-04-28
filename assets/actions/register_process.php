<?php
session_start();
require_once '../../config/db.php'; // Vérifie que le chemin vers db.php est correct

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom = htmlspecialchars($_POST['nom']);
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $telephone = htmlspecialchars($_POST['telephone'] ?? '');
  $role = 'client';

  try {
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
      header("Location: ../../pages/register.php?msg=error_email_exists");
      exit();
    }

    $sql = "INSERT INTO users (nom, email, password, telephone, role, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $email, $password, $telephone, $role]);

    // RECUPERATION DE L'ID ET CONNEXION AUTOMATIQUE
    $user_id = $pdo->lastInsertId();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_role'] = $role;
    $_SESSION['user_nom'] = $nom;

    // REDIRECTION DIRECTE VERS LE PAIEMENT
    header("Location: ../../pages/paiement.php?msg=welcome");
    exit();
  } catch (PDOException $e) {
    die("Erreur lors de l'inscription : " . $e->getMessage());
  }
}
