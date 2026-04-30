<?php
// C:\wamp64\www\ProjetFelykay\config\db.php

if (session_status() === PHP_SESSION_NONE) {
  // Configuration sécurisée avant le démarrage
  ini_set('session.cookie_httponly', 1);
  ini_set('session.use_only_cookies', 1);

  session_set_cookie_params([
    'path' => '/ProjetFelykay/',
    'samesite' => 'Lax',
    'httponly' => true
  ]);
  session_start();
}

$host = 'localhost';
$dbname = 'felikay_db';
$db_user = 'root';
$db_pass = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  if (!isset($_GET['ajax'])) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $page = $_SERVER['REQUEST_URI'] ?? '/';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu';
    $stmtVisit = $pdo->prepare("INSERT INTO visites (ip_address, page_visitee, user_agent, date_visite) VALUES (?, ?, ?, NOW())");
    $stmtVisit->execute([$ip, $page, $ua]);
  }
} catch (PDOException $e) {
  die("Erreur de connexion : " . $e->getMessage());
}
