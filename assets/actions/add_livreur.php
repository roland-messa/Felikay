<?php
// C:\wamp64\www\ProjetFelykay\assets\actions\add_livreur.php
session_start();
require_once '../../config/db.php';

// On précise que la réponse est du JSON
header('Content-Type: application/json');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
  echo json_encode(['success' => false, 'message' => 'Non autorisé']);
  exit();
}

$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$telephone = trim($_POST['telephone'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($nom) || empty($email) || empty($password)) {
  echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs']);
  exit();
}

try {
  $pdo->beginTransaction();

  $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $check->execute([$email]);
  if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
    exit();
  }

  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  // Insertion Table USERS
  $stmtUser = $pdo->prepare("INSERT INTO users (nom, email, password, role, telephone) VALUES (?, ?, ?, 'livreur', ?)");
  $stmtUser->execute([$nom, $email, $hashedPassword, $telephone]);

  // Insertion Table LIVREURS
  $stmtLivreur = $pdo->prepare("INSERT INTO livreurs (nom, telephone, email, statut) VALUES (?, ?, ?, 'actif')");
  $stmtLivreur->execute([$nom, $telephone, $email]);

  $pdo->commit();
  echo json_encode(['success' => true, 'message' => 'Livreur ajouté avec succès !']);
} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['success' => false, 'message' => 'Erreur base de données : ' . $e->getMessage()]);
}
exit();
