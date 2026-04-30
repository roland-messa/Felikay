<?php

function initierPaiementMobile($phone, $amount, $currency, $telecom, $description)
{
  $url = "https://afreemosi.com/api/payment/initiate/InitiateFelikayPayment.ashx";

  try {

    // ====== NORMALISATION TELEPHONE ======
    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (strpos($phone, '2430') === 0) {
      $phone = '243' . substr($phone, 4);
    } elseif (strpos($phone, '0') === 0) {
      $phone = '243' . substr($phone, 1);
    } elseif (strlen($phone) === 9) {
      $phone = '243' . $phone;
    }

    // ====== REFERENCE UNIQUE ======
    $ref = "FEL" . date("YmdHis") . rand(100, 999);

    // ====== PAYLOAD ======
    $payload = json_encode([
      "phone"       => $phone,
      "amount"      => (string)$amount,
      "currency"    => $currency,
      "telecom"     => $telecom,
      "referenceNo" => $ref,
      "description" => $description
    ]);

    // ====== CURL ======
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Accept: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
      throw new Exception("Erreur CURL: " . curl_error($ch));
    }

    curl_close($ch);

    // ====== LOG BRUT ======
    file_put_contents("api_raw.log", date('Y-m-d H:i:s') . " - " . $response . PHP_EOL, FILE_APPEND);

    $data = json_decode($response, true);

    // ====== SUCCÈS ======
    if ($httpCode == 200 && isset($data['success']) && $data['success'] === true) {

      $inner = isset($data['response']) ? json_decode($data['response'], true) : [];

      return [
        "success"     => true,
        "referenceNo" => $ref,
        "message"     => $inner['message'] ?? "Veuillez valider sur votre téléphone",
        "sessionId"   => $data['externalSessionId'] ?? null
      ];
    }

    // ====== ERREUR ======
    $error = "Échec de l'initialisation";

    if (isset($data['response'])) {
      $inner = json_decode($data['response'], true);
      $error = $inner['message'] ?? $error;
    }

    return [
      "success" => false,
      "message" => $error,
      "raw"     => $data
    ];
  } catch (Exception $e) {
    return [
      "success" => false,
      "message" => $e->getMessage()
    ];
  }
}
