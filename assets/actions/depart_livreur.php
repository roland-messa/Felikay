<?php
session_start();
require_once '../../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $commande_id = $_POST['commande_id'] ?? null;

  if (!$commande_id) {
    die("ID de commande introuvable.");
  }

  try {
    // 1. Récupération des détails de la commande et du client avant mise à jour
    $stmt = $pdo->prepare("
        SELECT c.*, u.nom as client_nom, u.telephone as client_tel, u.email as user_email 
        FROM commandes c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$commande_id]);
    $commande = $stmt->fetch();

    if (!$commande) {
      die("Commande introuvable.");
    }

    // Détermination de l'e-mail du client (invité ou enregistré)
    $email_client = !empty($commande['email_invite']) ? $commande['email_invite'] : $commande['user_email'];

    // 2. Passage du statut de la commande à 'en_route'
    $stmtUpdate = $pdo->prepare("UPDATE commandes SET statut = 'en_route', updated_at = NOW() WHERE id = ?");
    $stmtUpdate->execute([$commande_id]);

    // 3. Envoi du mail de notification au client si un e-mail est renseigné
    if (!empty($email_client)) {
      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host       = 'mail5017.site4now.net';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'noreply@felikayboutique.com';
      $mail->Password   = 'Felikay@2026';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port       = 465;
      $mail->CharSet    = 'UTF-8';

      // Expéditeur et contacts
      $mail->setFrom('noreply@felikayboutique.com', 'Felikay Maison de Mode');
      $mail->addAddress($email_client, $commande['nom_complet']);
      $mail->addReplyTo('contact@felikayboutique.com', 'Felikay Service Client');

      $mail->isHTML(true);
      $mail->Subject = "🚀 [Felikay] Votre livreur est en route !";

      $mail->Body = "
                <div style='font-family: sans-serif; color: #1a1a1a; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;'>
                    <h2 style='text-align: center; color: #000; letter-spacing: 2px;'>FELIKAY</h2>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    
                    <p>Bonjour <strong>" . htmlspecialchars($commande['nom_complet']) . "</strong>,</p>
                    <p>Bonne nouvelle ! Votre colis correspondant à la commande <strong>#$commande_id</strong> a quitté nos locaux.</p>
                    <p><strong>Notre coursier est actuellement en route pour votre livraison.</strong></p>
                    
                    <div style='background: #fff9e6; padding: 15px; margin: 20px 0; border-left: 3px solid #ffcc00; font-size: 13px;'>
                        <p style='margin: 0;'>⚠️ <strong>Consigne importante :</strong> S'il vous plaît, restez joignable sur votre numéro de téléphone (<strong>" . htmlspecialchars($commande['telephone']) . "</strong>) afin que le livreur puisse finaliser la remise sans encombre.</p>
                    </div>

                    <p>À tout de suite !</p>
                    <p>L'équipe Felikay Maison de Mode</p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 11px; color: #888; text-align: center;'>Maison de Mode • Kinshasa</p>
                </div>
            ";

      $mail->send();
    }

    // Redirection vers le dashboard avec message de confirmation
    header("Location: ../../pages/admin/livreur_dashboard.php?en_route=success");
    exit();
  } catch (Exception $e) {
    error_log("Erreur lors de la mise en route de la livraison : " . $e->getMessage());
    die("Une erreur est survenue lors du traitement. Veuillez réessayer.");
  }
} else {
  header("Location: ../../pages/admin/livreur_dashboard.php");
  exit();
}
