<?php

require_once '../../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../../vendor/autoload.php';

$commande_id = $_GET['id'] ?? null;

if (!$commande_id) die("ID de commande manquant.");

// Récupération des infos de la commande et du client
$stmt = $pdo->prepare("
    SELECT c.*, u.email as user_email 
    FROM commandes c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.id = ?
");
$stmt->execute([$commande_id]);
$commande = $stmt->fetch();

if (!$commande) die("Commande introuvable.");

$email_client = !empty($commande['email_invite']) ? $commande['email_invite'] : $commande['user_email'];

// Traitement de l'envoi de la planification
$message_statut = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $date_livraison = $_POST['date_livraison'];
  $heure_livraison = $_POST['heure_livraison'];

  $date_formatee = date("d/m/Y", strtotime($date_livraison));

  // 1. Mise à jour du statut distinct pour la planification
  $stmtUpdate = $pdo->prepare("UPDATE commandes SET statut = 'planifie', date_livraison_prevue = ?, creneau_livraison = ? WHERE id = ?");
  $stmtUpdate->execute([$date_livraison, $heure_livraison, $commande_id]);

  // 2. Envoi du mail au client et à l'administrateur
  if (!empty($email_client)) {
    try {
      $mail = new PHPMailer(true);
      $mail->isSMTP();
      $mail->Host       = 'mail5017.site4now.net';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'noreply@felikayboutique.com';
      $mail->Password   = 'Felikay@2026';
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      $mail->Port       = 465;
      $mail->CharSet    = 'UTF-8';

      $mail->setFrom('noreply@felikayboutique.com', 'Felikay Maison de Mode');
      $mail->addAddress($email_client);
      $mail->addAddress('contact@felikayboutique.com', 'Felikay Administration');
      $mail->addReplyTo('contact@felikayboutique.com', 'Felikay Maison de Mode');

      $mail->isHTML(true);
      $mail->Subject = "🚚 [Suivi Livraison] Planification de la commande #$commande_id";

      $mail->Body = "
                <div style='font-family: sans-serif; color: #1a1a1a; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;'>
                    <h2 style='text-align: center; color: #000; letter-spacing: 2px;'>FELIKAY</h2>
                    <p>Bonjour <strong>" . htmlspecialchars($commande['nom_complet']) . "</strong>,</p>
                    <p>Bonne nouvelle ! Votre commande <strong>#$commande_id</strong> a été planifiée par notre service logistique.</p>
                    
                    <div style='background: #f9f9f9; padding: 15px; margin: 20px 0; border-left: 3px solid #000;'>
                        <p style='margin: 0 0 10px 0;'><strong>🗓️ Date de livraison prévue :</strong> $date_formatee</p>
                        <p style='margin: 0;'><strong>⏰ Tranche horaire estimée :</strong> $heure_livraison</p>
                    </div>

                    <p>Notre livreur vous contactera sur votre numéro (<strong>" . htmlspecialchars($commande['telephone']) . "</strong>) dès qu'il sera en route.</p>
                    <p>Merci pour votre confiance.</p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 11px; color: #888; text-align: center;'>Maison de Mode • Kinshasa</p>
                </div>
            ";

      $mail->send();
      $message_statut = "<div style='color: green; font-weight: bold; margin-bottom: 20px;'>✅ Planification validée et enregistrée ! Le client et l'administration ont été notifiés.</div>";
    } catch (Exception $e) {
      $message_statut = "<div style='color: red; margin-bottom: 20px;'>❌ Erreur lors de l'envoi du mail : " . $mail->ErrorInfo . "</div>";
    }
  } else {
    $message_statut = "<div style='color: orange; margin-bottom: 20px;'>⚠️ Aucun email trouvé pour ce client (Planification sauvegardée, mais notification impossible).</div>";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Planifier la Livraison</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 p-8">
  <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-sm border border-gray-100">

    <div class="flex flex-col items-center justify-center mb-6">
      <div class="w-20 h-20 rounded-full border border-gray-200 overflow-hidden bg-white flex items-center justify-center p-1 shadow-sm">
        <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo Felikay" class="w-full h-full object-contain rounded-full">
      </div>
      <h1 class="text-xl font-bold uppercase tracking-wider text-center mt-3">Planification Livraison</h1>
    </div>

    <?= $message_statut ?>

    <div class="mb-4 text-sm text-gray-600 bg-gray-50 p-3 rounded">
      <p><strong>Commande :</strong> #<?= $commande['id'] ?></p>
      <p><strong>Client :</strong> <?= htmlspecialchars($commande['nom_complet']) ?></p>
      <p><strong>Adresse :</strong> <?= htmlspecialchars($commande['adresse_livraison']) ?>, <?= htmlspecialchars($commande['quartier']) ?> (<?= htmlspecialchars($commande['commune']) ?>)</p>
      <p><strong>Téléphone :</strong> <?= htmlspecialchars($commande['telephone']) ?></p>
    </div>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-xs uppercase font-bold tracking-wide text-gray-500 mb-1">Date de Livraison</label>
        <input type="date" name="date_livraison" required min="<?= date('Y-m-d') ?>" class="w-full border p-2 rounded focus:outline-none focus:border-black">
      </div>

      <div>
        <label class="block text-xs uppercase font-bold tracking-wide text-gray-500 mb-1">Créneau horaire de Livraison</label>
        <select name="heure_livraison" required class="w-full border p-2 rounded focus:outline-none focus:border-black bg-white text-sm">
          <option value="" disabled selected>Choisir une tranche horaire...</option>
          <option value="Matinée (Entre 08h00 et 12h00)">Matinée (08h00 - 12h00)</option>
          <option value="Début d'après-midi (Entre 12h00 et 15h00)">Début d'après-midi (12h00 - 15h00)</option>
          <option value="Fin d'après-midi (Entre 15h00 et 18h00)">Fin d'après-midi (15h00 - 18h00)</option>
          <option value="Soirée (Entre 18h00 et 20h00)">Soirée (18h00 - 20h00)</option>
        </select>
      </div>

      <button type="submit" class="w-full bg-black text-white p-3 uppercase text-xs font-bold tracking-widest hover:bg-gray-900 transition">
        Confirmer & Envoyer au Client
      </button>
    </form>

    <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center text-xs">
      <a href="javascript:history.back()" class="text-slate-500 hover:text-black font-medium flex items-center gap-1">← Retour aux commandes</a>
      <a href="../../index.php" class="text-gray-400 hover:underline">Retour à la boutique</a>
    </div>
  </div>
</body>

</html>