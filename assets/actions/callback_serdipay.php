<?php
require_once '../../config/db.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Log pour débogage
file_put_contents('callback_debug.log', date('Y-m-d H:i:s') . " - Data: " . $input . PHP_EOL, FILE_APPEND);

if (isset($data['referenceNo']) && isset($data['status'])) {
  $ref = $data['referenceNo'];
  $status = strtoupper($data['status']);
  $transId = $data['transactionId'] ?? null; // ID transaction opérateur si dispo

  try {
    $pdo->beginTransaction();

    if ($status === 'SUCCESS' || $status === 'COMPLETED') {
      // 1. Mise à jour du paiement
      $stmtPay = $pdo->prepare("UPDATE paiements SET statut_paiement = 'reussi', reference_operateur = ? WHERE reference_interne = ?");
      $stmtPay->execute([$transId, $ref]);

      // 2. Mise à jour de la commande associée
      $stmtCmd = $pdo->prepare("
                UPDATE commandes 
                SET statut = 'paye' 
                WHERE id = (SELECT commande_id FROM paiements WHERE reference_interne = ?)
            ");
      $stmtCmd->execute([$ref]);

      $pdo->commit();
      echo json_encode(["status" => "success", "message" => "Paiement et commande validés"]);
    } else {
      // Échec du paiement
      $stmtPay = $pdo->prepare("UPDATE paiements SET statut_paiement = 'echoue' WHERE reference_interne = ?");
      $stmtPay->execute([$ref]);

      $pdo->commit();
      echo json_encode(["status" => "error", "message" => "Paiement échoué"]);
    }
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("CALLBACK ERROR: " . $e->getMessage());
    echo json_encode(["status" => "error", "message" => "Erreur interne"]);
  }
} else {
  echo json_encode(["status" => "error", "message" => "Données incomplètes"]);
}
