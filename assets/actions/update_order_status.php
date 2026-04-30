<?php
// On désactive l'affichage des erreurs pour éviter de corrompre le JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// On capture tout affichage parasite (warnings, notices)
ob_start();

header('Content-Type: application/json');

// Inclusion de la base de données (qui gère maintenant la session)
$db_path = __DIR__ . '/../../config/db.php';
if (!file_exists($db_path)) {
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Fichier db.php introuvable']);
  exit;
}
require_once $db_path;

// Vérification de sécurité Admin
// Note : Le session_start() est déjà exécuté par db.php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Session invalide ou non admin']);
  exit;
}

if (isset($_POST['id']) && isset($_POST['statut'])) {
  $id = intval($_POST['id']);
  $statut = $_POST['statut'];

  try {
    $query = $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?");
    $success = $query->execute([$statut, $id]);

    // Nettoyage final du tampon avant l'envoi du JSON
    ob_clean();
    echo json_encode(['success' => $success]);
  } catch (PDOException $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Erreur SQL lors de la mise à jour']);
  }
} else {
  ob_clean();
  echo json_encode(['success' => false, 'message' => 'Données POST manquantes (id ou statut)']);
}

ob_end_flush();
exit;
