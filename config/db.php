<?php

$host = 'localhost';
$dbname = 'felikay_db';
$db_user = 'root';
$db_pass = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
  $page = $_SERVER['REQUEST_URI'] ?? '/';
  $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu';

  $stmtVisit = $pdo->prepare("INSERT INTO visites (ip_address, page_visitee, user_agent, date_visite) VALUES (?, ?, ?, NOW())");
  $stmtVisit->execute([$ip, $page, $ua]);
} catch (PDOException $e) {

  if (strpos($e->getMessage(), 'Connect') !== false) {
    die("Erreur de connexion : " . $e->getMessage());
  }
}
