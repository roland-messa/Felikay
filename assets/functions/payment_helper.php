<?php

/**
 * Initialise un paiement mobile via l'API AfreeMosi
 * Version corrigée pour support multi-devise (USD/CDF)
 */
function initierPaiementMobile($phone, $amount, $currency, $telecom, $description)
{
  $url = "https://afreemosi.com/api/payment/initiate/InitiateFelikayPayment.ashx";

  try {
    // ====== NORMALISATION DU TÉLÉPHONE ======
    // On ne garde que les chiffres et on force le préfixe 243 sur les 9 derniers chiffres
    $clean = preg_replace('/[^0-9]/', '', $phone);
    $lastNine = substr($clean, -9);
    $phone = '243' . $lastNine;

    if (strlen($lastNine) !== 9) {
      throw new Exception("Format de numéro incorrect. 9 chiffres attendus.");
    }

    // ====== NORMALISATION TÉLÉCOM ======
    $telecom = strtoupper(trim($telecom));
    $allowedTelecoms = ['MP', 'AM', 'OM'];

    if (!in_array($telecom, $allowedTelecoms)) {
      throw new Exception("Opérateur non supporté : " . $telecom);
    }

    // ====== MONTANT ET RÉFÉRENCE ======
    // Correction : On utilise 2 décimales pour le USD, sans arrondir à l'entier supérieur
    $amount_formatted = number_format(floatval($amount), 2, '.', '');

    if (floatval($amount_formatted) <= 0) {
      throw new Exception("Le montant doit être supérieur à zéro.");
    }

    $ref = "FEL" . date("YmdHis") . rand(100, 999);

    // ====== PRÉPARATION DU PAYLOAD ======
    $payloadArray = [
      "phone"       => $phone,
      "amount"      => $amount_formatted, // Format string avec 2 décimales
      "currency"    => strtoupper(trim($currency)), // Accepte USD ou CDF
      "telecom"     => $telecom,
      "referenceNo" => $ref,
      "description" => $description
    ];

    $payload = json_encode($payloadArray);

    // ====== EXÉCUTION CURL ======
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $payload,
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Accept: application/json"
      ],
      CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
      throw new Exception("Erreur de connexion (CURL) : " . curl_error($ch));
    }
    curl_close($ch);

    // ====== LOGGING DE DEBUG ======
    file_put_contents(
      "api_debug.log",
      date('Y-m-d H:i:s') . " | HTTP: $httpCode | REQ: $payload | RES: $response\n",
      FILE_APPEND
    );

    $data = json_decode($response, true);
    if ($data === null) {
      throw new Exception("Réponse API corrompue (JSON invalide).");
    }

    // ====== RÉPONSE DE L'API ======
    if ($httpCode == 200 && isset($data['success']) && $data['success'] === true) {
      return [
        "success"     => true,
        "referenceNo" => $ref,
        "message"     => "Veuillez valider l'opération sur votre téléphone",
        "sessionId"   => $data['externalSessionId'] ?? null
      ];
    }

    // Gestion des erreurs renvoyées par l'API
    $errorMsg = "Échec de l'initialisation du paiement.";
    if (isset($data['response'])) {
      $inner = json_decode($data['response'], true);
      $errorMsg = $inner['message'] ?? $errorMsg;
    }

    return [
      "success" => false,
      "message" => $errorMsg
    ];
  } catch (Exception $e) {
    // Log de l'erreur fatale
    file_put_contents(
      "api_error.log",
      date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n",
      FILE_APPEND
    );

    return [
      "success" => false,
      "message" => $e->getMessage()
    ];
  }
}
