<?php
// C:\wamp64\www\ProjetFelykay\assets\functions\payment_helper.php

function initierPaiementMobile($phone, $amount, $currency, $telecom, $description)
{
  global $pdo;

  $apiId = "VOTRE_API_ID";
  $apiPassword = "VOTRE_API_PASSWORD";
  $merchantCode = "VOTRE_MERCHANT_CODE";
  $tokenUrl = "https://api.serdipay.com/v1/token";
  $paymentUrl = "https://api.serdipay.com/v1/c2b";

  try {
    // 1. OBTENIR LE TOKEN
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
      "api_id" => $apiId,
      "api_password" => $apiPassword
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

    $tokenRes = json_decode(curl_exec($ch), true);
    $token = $tokenRes['access_token'] ?? null;
    curl_close($ch);

    if (!$token) return ["success" => false, "message" => "Erreur d'authentification API"];

    // 2. GÉNÉRER LA RÉFÉRENCE
    $ref = "FLK-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -5));

    // 3. ENVOI DE LA REQUÊTE
    $payload = json_encode([
      "merchantCode" => $merchantCode,
      "clientPhone"  => $phone,
      "amount"       => $amount,
      "currency"     => $currency,
      "telecom"      => $telecom,
      "referenceNo"  => $ref,
      "description"  => $description
    ]);

    $ch = curl_init($paymentUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true); // Ajouté pour être sûr
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer " . $token
    ]);

    $response = json_decode(curl_exec($ch), true);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
      "success" => ($httpCode < 400),
      "referenceNo" => $ref,
      "data" => $response
    ];
  } catch (Exception $e) {
    return ["success" => false, "message" => $e->getMessage()];
  }
}
