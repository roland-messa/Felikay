<?php
// assets/actions/callback_serdipay.php
require_once '../../config/db.php';

// SerdiPay envoie les données en JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (isset($data['referenceNo']) && $data['status'] === 'SUCCESS') {
  // On met à jour la commande dans VOTRE base de données
  $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE payment_ref = ?");
  $stmt->execute([$data['referenceNo']]);
}
