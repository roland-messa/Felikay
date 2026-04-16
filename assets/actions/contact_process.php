<?php
// C:\wamp64\www\ProjetFelykay\actions\contact_process.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $nom     = htmlspecialchars($_POST['nom']);
  $email   = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $sujet   = htmlspecialchars($_POST['sujet']);
  $message = htmlspecialchars($_POST['message']);

  if (!empty($nom) && !empty($email) && !empty($message)) {


    header("Location: ../pages/contact.php?msg=success_contact");
    exit();
  } else {
    header("Location: ../pages/contact.php?msg=error");
    exit();
  }
} else {
  header("Location: ../index.php");
  exit();
}
