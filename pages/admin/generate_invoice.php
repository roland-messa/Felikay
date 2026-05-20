<?php
// C:\wamp64\www\ProjetFelykay\assets\actions\generer_recu.php
require_once '../../vendor/autoload.php';
require_once '../../config/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$commande_id = $_GET['id'] ?? null;
$payment_ref = $_GET['ref'] ?? null;

if (!$commande_id && !$payment_ref) die("Identifiant de commande manquant.");

// 1. RÉCUPÉRATION DES INFOS (Base de données)
$query = "
    SELECT c.*, u.nom as user_nom, u.email as user_email, u.telephone as user_tel,
           p.mode_paiement, p.statut_paiement, p.reference_interne
    FROM commandes c 
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN paiements p ON c.id = p.commande_id
    WHERE " . ($payment_ref ? "p.reference_interne = ?" : "c.id = ?");

$stmt = $pdo->prepare($query);
$stmt->execute([$payment_ref ?? $commande_id]);
$commande = $stmt->fetch();

if (!$commande) die("Commande introuvable.");

$real_id = $commande['id'];
$client_email = $commande['user_email'] ?? $commande['email_contact']; // Ajustez selon votre table

// 2. RÉCUPÉRATION DES DÉTAILS (Produit, Taille, Couleur)
$stmtDetails = $pdo->prepare("
    SELECT cd.*, p.nom as produit_nom, t.nom as taille_nom, col.nom as couleur_nom 
    FROM commande_details cd
    LEFT JOIN produits p ON cd.produit_id = p.id
    LEFT JOIN tailles t ON cd.taille_id = t.id
    LEFT JOIN couleurs col ON cd.couleur_id = col.id
    WHERE cd.commande_id = ?
");
$stmtDetails->execute([$real_id]);
$items = $stmtDetails->fetchAll();

// 3. LOGIQUE DE NOMMAGE & COULEURS
$is_paid = ($commande['statut_paiement'] === 'reussi' || $commande['statut'] === 'paye');
$type_document = $is_paid ? "REÇU DE PAIEMENT" : "BON DE COMMANDE";
$accent_color = $is_paid ? "#2c7a7b" : "#000000"; // Vert sombre si payé, Noir sinon

// Logo en Base64 pour le PDF
$logoPath = '../../assets/img/felikay.jpg';
$logoBase64 = '';
if (file_exists($logoPath)) {
  $data = file_get_contents($logoPath);
  $logoBase64 = 'data:image/jpg;base64,' . base64_encode($data);
}

// 4. DESIGN ÉLÉGANT (HTML/CSS)
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Helvetica", sans-serif; color: #333; margin: 0; padding: 0; font-size: 11px; }
        .watermark { position: fixed; top: 30%; left: 10%; transform: rotate(-45deg); font-size: 100px; color: rgba(0, 0, 0, 0.03); font-weight: bold; z-index: -1000; text-transform: uppercase; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo-container { width: 70px; height: 70px; margin: 0 auto 10px; border: 1.5px solid #000; border-radius: 50%; overflow: hidden; }
        .brand { font-size: 24px; font-weight: bold; letter-spacing: 6px; margin-bottom: 5px; text-transform: uppercase; }
        .doc-type { font-size: 10px; letter-spacing: 3px; font-weight: bold; border: 1px solid ' . $accent_color . '; color: ' . $accent_color . '; padding: 6px 20px; display: inline-block; margin-top: 10px; }
        
        .info-table { width: 100%; margin: 30px 0; border-collapse: collapse; }
        .info-td { vertical-align: top; width: 50%; }
        .label { font-size: 8px; color: #999; text-transform: uppercase; margin-bottom: 3px; }

        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { background: #000; color: #fff; padding: 10px; text-transform: uppercase; font-size: 9px; text-align: left; }
        .items-table td { padding: 12px 10px; border-bottom: 1px solid #eee; }
        .detail-text { font-size: 9px; color: #666; text-transform: uppercase; margin-top: 2px; }

        .summary-table { width: 250px; margin-left: auto; margin-top: 20px; }
        .summary-td { padding: 5px; text-align: right; }
        .total-final { font-size: 15px; font-weight: bold; border-top: 1.5px solid #000; padding-top: 10px; }
        
        .footer { position: fixed; bottom: 20px; width: 100%; text-align: center; font-size: 8px; color: #aaa; }
    </style>
</head>
<body>
    <div class="watermark">FELIKAY</div>
    <div class="header">
        <div class="logo-container"><img src="' . $logoBase64 . '" style="width:100%;"></div>
        <div class="brand">FELIKAY</div>
        <div style="font-size: 9px; color: #666;">Maison de Mode • Gombe, Kinshasa</div>
        <div class="doc-type">' . $type_document . '</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-td">
                <div class="label">Client / Destination</div>
                <strong style="font-size:12px;">' . htmlspecialchars($commande['nom_complet'] ?? $commande['user_nom']) . '</strong><br>
                ' . ($commande['frais_livraison'] > 0 ? htmlspecialchars($commande['adresse_livraison']) : "Retrait en Boutique") . '<br>
                Tél : ' . htmlspecialchars($commande['telephone'] ?? $commande['user_tel']) . '
            </td>
            <td class="info-td" style="text-align: right;">
                <div class="label">Détails</div>
                <strong>Référence :</strong> #' . $real_id . '<br>
                <strong>Date :</strong> ' . date("d/m/Y", strtotime($commande['created_at'])) . '<br>
                <strong>Paiement :</strong> ' . strtoupper($commande['mode_paiement'] ?? 'Non défini') . '
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Article & Détails</th>
                <th style="text-align: center;">Qté</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>';
foreach ($items as $item) {
  $html .= '<tr>
                <td>
                    <strong>' . htmlspecialchars($item['produit_nom']) . '</strong><br>
                    <div class="detail-text">Taille: ' . ($item['taille_nom'] ?? 'STD') . ' | Couleur: ' . ($item['couleur_nom'] ?? 'Unique') . '</div>
                </td>
                <td style="text-align: center;">' . $item['quantite'] . '</td>
                <td style="text-align: right;">' . number_format($item['prix_unitaire'] * $item['quantite'], 2) . ' $</td>
            </tr>';
}
$html .= '</tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="summary-td">Sous-total :</td>
            <td class="summary-td" style="width:80px;">' . number_format($commande['total_ttc'] - ($commande['frais_livraison'] ?? 0), 2) . ' $</td>
        </tr>
        <tr>
            <td class="summary-td">Livraison :</td>
            <td class="summary-td">' . number_format($commande['frais_livraison'] ?? 0, 2) . ' $</td>
        </tr>
        <tr>
            <td class="summary-td total-final">TOTAL TTC :</td>
            <td class="summary-td total-final">' . number_format($commande['total_ttc'], 2) . ' $</td>
        </tr>
    </table>

    <div class="footer">FELIKAY • +243 829 045 003 • www.felikay.com</div>
</body>
</html>';

// 5. GÉNÉRATION PDF & ENVOI MAIL
try {
  $options = new Options();
  $options->set('isRemoteEnabled', true);
  $dompdf = new Dompdf($options);
  $dompdf->loadHtml($html);
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  $pdfOutput = $dompdf->output();

  // Configuration PHPMailer pour le serveur Felikay
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
  $mail->addAddress($client_email);
  $mail->addStringAttachment($pdfOutput, "Felikay_" . $type_document . "_" . $real_id . ".pdf");

  $mail->isHTML(true);
  $mail->Subject = "Votre $type_document Felikay - #$real_id";
  $mail->Body    = "Merci de votre achat chez Felikay. Veuillez trouver ci-joint votre $type_document.";

  $mail->send();
  $mail_sent = true;
} catch (Exception $e) {
  $mail_sent = false;
  $error_msg = $mail->ErrorInfo;
  // Optionnel : Enregistrer l'erreur dans les logs pour le débogage
  error_log("Erreur d'envoi de mail : " . $error_msg);
}

// 6. AFFICHAGE DU MESSAGE FINAL AU CLIENT
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Confirmation - Felikay</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital@1&display=swap" rel="stylesheet">
</head>

<body class="bg-stone-50 flex items-center justify-center h-screen">
  <div class="bg-white p-12 shadow-sm border border-stone-200 text-center max-w-lg">
    <h1 style="font-family: 'Playfair Display', serif;" class="text-4xl italic mb-8">Felikay</h1>

    <?php if ($mail_sent): ?>
      <div class="text-stone-800">
        <p class="text-lg mb-2">Merci pour votre confiance.</p>
        <p class="text-sm text-stone-500 mb-8 italic">Un reçu de paiement vous a été envoyé par mail à : <br><strong><?= $client_email ?></strong></p>
      </div>
    <?php else: ?>
      <p class="text-red-500 mb-8 font-bold">Désolé, l'email n'a pas pu partir. Mais votre commande est bien enregistrée.</p>
    <?php endif; ?>

    <a href="../../index.php" class="inline-block border border-black px-10 py-3 text-[10px] font-bold uppercase tracking-[3px] hover:bg-black hover:text-white transition-all">
      Retour à la boutique
    </a>
  </div>
</body>

</html>