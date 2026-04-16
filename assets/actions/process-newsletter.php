<?php

include 'includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $name = htmlspecialchars(trim($_POST['client_name']));

  $raw_phone = htmlspecialchars(trim($_POST['client_phone']));
  $phone = "+243" . $raw_phone;

  if (!empty($name) && strlen($raw_phone) === 9) {

    try {

      $stmt = $pdo->prepare("INSERT INTO newsletter_felykay (nom_complet, telephone) VALUES (?, ?)");
      $stmt->execute([$name, $phone]);

      header("Location: ../../index.php?status=success#newsletter");
      exit();
    } catch (Exception $e) {

      header("Location: ../../index.php?status=error#newsletter");
      exit();
    }
  } else {

    header("Location: ../../index.php?status=invalid#newsletter");
    exit();
  }
}
