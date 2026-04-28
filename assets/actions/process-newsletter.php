<?php

include '../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name  = htmlspecialchars(trim($_POST['client_name']));
  $email = !empty($_POST['client_email']) ? htmlspecialchars(trim($_POST['client_email'])) : null;
  $raw_phone = htmlspecialchars(trim($_POST['client_phone']));
  $phone = "+243" . $raw_phone;

  // Le téléphone reste la condition sine qua non
  if (!empty($name) && strlen($raw_phone) === 9) {
    try {
      // Ajout de la colonne email dans la requête
      $sql = "INSERT INTO newsletter_felykay (nom_complet, email, telephone, created_at) VALUES (?, ?, ?, NOW())";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$name, $email, $phone]);

      $referer = $_SERVER['HTTP_REFERER'] ?? '../../index.php';
      header("Location: " . $referer . (strpos($referer, '?') !== false ? '&' : '?') . "status=success#newsletter");
      exit();
    } catch (Exception $e) {
      // Si l'erreur est un doublon sur le téléphone ou l'email
      header("Location: ../../index.php?status=error#newsletter");
      exit();
    }
  } else {
    header("Location: ../../index.php?status=invalid#newsletter");
    exit();
  }
}
