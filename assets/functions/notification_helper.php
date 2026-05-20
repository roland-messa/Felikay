<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function notifierNouvelleCommande($commande_id, $nom_client, $mode_paiement, $total, $telephone)
{
  try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'mail5017.site4now.net';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'noreply@felikayboutique.com'; // Compte technique d'envoi
    $mail->Password   = 'Felikay@2026';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    // Configuration de l'Expéditeur & du Destinataire
    $mail->setFrom('noreply@felikayboutique.com', 'Felikay Système');


    $mail->addAddress('contact@felikayboutique.com', 'Felikay Administration');

    // Permet à l'admin de cliquer sur "Répondre" pour écrire directement au client si besoin (optionnel mais pratique)
    $mail->addReplyTo('contact@felikayboutique.com', 'Maison Felikay');

    $mail->isHTML(true);
    $mail->Subject = "🚨 Nouvelle commande Felykay à planifier - #$commande_id";

    // 💡 CORRECTION : Ajustement du chemin vers le dossier /pages/admin/
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $lien_planification = "$protocol://$_SERVER[HTTP_HOST]/ProjetFelykay/pages/admin/planifier_livraison.php?id=$commande_id";

    $mail->Body = "
            <div style='font-family: sans-serif; color: #1a1a1a; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;'>
                <h2 style='color: #000; font-weight: normal; letter-spacing: 1px;'>Une nouvelle commande a été enregistrée !</h2>
                <hr style='border:none; border-top:1px solid #eee; margin:20px 0;'>
                
                <p><strong>Commande :</strong> #$commande_id</p>
                <p><strong>Client :</strong> " . htmlspecialchars($nom_client) . "</p>
                <p><strong>Téléphone :</strong> " . htmlspecialchars($telephone) . "</p>
                <p><strong>Mode de paiement :</strong> " . htmlspecialchars($mode_paiement) . "</p>
                <p><strong>Montant total :</strong> $total $</p>
                
                <br>
                <p style='color: #555;'>👉 Pour définir le jour ainsi que la tranche horaire de livraison et envoyer la notification automatique au client, cliquez ci-dessous :</p>
                <p style='text-align: center; margin-top: 25px;'>
                    <a href='$lien_planification' style='display:inline-block; padding:12px 25px; background:#000; color:#fff; text-decoration:none; font-weight:bold; text-transform:uppercase; font-size:11px; letter-spacing:2px;'>
                        Planifier la livraison
                    </a>
                </p>
            </div>
        ";

    $mail->send();
  } catch (Exception $e) {
    error_log("Erreur notification Admin : " . $mail->ErrorInfo);
  }
}
