<?php
require_once '../../config/db.php';

// 1. Récupération des données envoyées par la passerelle
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// 2. Journalisation pour le débogage (indispensable en production)
file_put_contents('callback_debug.log', date('Y-m-d H:i:s') . " - Data: " . $input . PHP_EOL, FILE_APPEND);

if (isset($data['referenceNo']) && isset($data['status'])) {
  $ref = $data['referenceNo'];
  $status = strtoupper($data['status']); // Normalisation en majuscules

  if ($status === 'SUCCESS' || $status === 'COMPLETED') {
    // Mise à jour de la table 'commandes'
    $stmt = $pdo->prepare("UPDATE commandes SET statut = 'paye' WHERE payment_ref = ?");
    $stmt->execute([$ref]);

    echo json_encode(["status" => "success", "message" => "Commande mise à jour"]);
  } else {
    // Si le statut est ECHEC ou autre
    $stmt = $pdo->prepare("UPDATE commandes SET statut = 'echoue' WHERE payment_ref = ? AND statut = 'en_attente'");
    $stmt->execute([$ref]);

    echo json_encode(["status" => "error", "message" => "Statut de paiement non valide : " . $status]);
  }
} else {
  echo json_encode(["status" => "error", "message" => "Données reçues incomplètes"]);
}
