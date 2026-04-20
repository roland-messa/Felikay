<?php
session_start();
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nom     = htmlspecialchars(trim($_POST['nom']));
  $email   = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $sujet   = htmlspecialchars(trim($_POST['sujet']));
  $message = htmlspecialchars(trim($_POST['message']));

  if (!empty($nom) && !empty($email) && !empty($message)) {
    try {

      $stmt = $pdo->prepare("INSERT INTO contact_messages (nom, email, sujet, message, created_at) VALUES (?, ?, ?, ?, NOW())");
      $stmt->execute([$nom, $email, $sujet, $message]);

      header("Location: ../../pages/contact.php?msg=success_contact");
      exit();
    } catch (Exception $e) {
      header("Location: ../../pages/contact.php?msg=error");
      exit();
    }
  } else {
    header("Location: ../../pages/contact.php?msg=error_fields");
    exit();
  }
} else {
  header("Location: ../../index.php");
  exit();
}
