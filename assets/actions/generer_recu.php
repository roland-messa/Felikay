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
$email_force = $_GET['email'] ?? null; // Email passé par process_cash ou process_online

if (!$commande_id && !$payment_ref) die("Identifiant de commande manquant.");

// 1. RÉCUPÉRATION DES INFOS
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

// Détermination de l'email destinataire (Priorité : Email forcé > Email User > Email Invite)
$client_email = $email_force ?: ($commande['user_email'] ?: $commande['email_invite']);

// 2. RÉCUPÉRATION DES DÉTAILS
$stmtDetails = $pdo->prepare("
    SELECT cd.*, p.nom as produit_nom, t.nom as taille_nom, cl.nom as couleur_nom 
    FROM commande_details cd
    LEFT JOIN produits p ON cd.produit_id = p.id
    LEFT JOIN tailles t ON cd.taille_id = t.id
    LEFT JOIN couleurs cl ON cd.couleur_id = cl.id
    WHERE cd.commande_id = ?
");
$stmtDetails->execute([$real_id]);
$items = $stmtDetails->fetchAll();

// 3. CALCULS ET LOGIQUE DE PAIEMENT
$total_ttc = floatval($commande['total_ttc']);
$frais_livraison = floatval($commande['frais_livraison'] ?? 0);
$sous_total = $total_ttc - $frais_livraison;
$is_livraison = ($frais_livraison > 0);

$brut_mode = strtoupper($commande['mode_paiement'] ?? 'CASH');
$affichage_mode = $brut_mode;
if (strpos($brut_mode, 'MPESA') !== false) $affichage_mode = "M-PESA";
elseif (strpos($brut_mode, 'ORANGE') !== false) $affichage_mode = "ORANGE MONEY";
elseif (strpos($brut_mode, 'AIRTEL') !== false) $affichage_mode = "AIRTEL MONEY";
elseif ($brut_mode === 'CASH') $affichage_mode = "CASH (À LA LIVRAISON)";

$is_paid = in_array(strtolower($commande['statut_paiement'] ?? ''), ['reussi', 'completed', 'success', 'paye']);
$type_document = $is_paid ? "FACTURE DE PAIEMENT" : "BON DE COMMANDE";

// ENCODAGE DU LOGO EN BASE64 POUR DOMPDF
$logo_path = dirname(__DIR__, 2) . '/assets/img/felikay.jpg';
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_data = file_get_contents($logo_path);
    $logo_base64 = 'data:image/jpeg;base64,' . base64_encode($logo_data);
}

// 4. CONSTRUCTION DU HTML
$html = '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Helvetica", sans-serif; color: #1a1a1a; margin: 0; padding: 0; line-height: 1.4; }
        .page { padding: 40px; }
        .watermark { position: fixed; top: 35%; left: 10%; transform: rotate(-45deg); font-size: 100px; color: rgba(0, 0, 0, 0.03); font-weight: bold; z-index: -1000; text-transform: uppercase; letter-spacing: 20px; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 0.5px solid #eee; padding-bottom: 20px; }
        
        /* Conteneur et style du logo en cercle */
        .logo-container { margin-bottom: 15px; text-align: center; }
        .logo-img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 1px solid #eee; }
        
        .brand { font-size: 28px; font-weight: bold; letter-spacing: 8px; text-transform: uppercase; }
        .subtitle { font-size: 9px; letter-spacing: 3px; color: #888; text-transform: uppercase; margin-bottom: 10px;}
        .doc-title { text-align: center; margin-bottom: 30px; font-size: 11px; font-weight: bold; letter-spacing: 4px; border: 1px solid #000; padding: 8px; text-transform: uppercase; }
        .status-badge { margin: 20px 0; padding: 15px; text-align: center; font-weight: bold; font-size: 13px; text-transform: uppercase; letter-spacing: 2px; }
        .status-paid { border: 2px solid #27ae60; color: #27ae60; background: #f4fdf8; }
        .status-unpaid { border: 2px solid #e74c3c; color: #e74c3c; background: #fdf5f4; }
        .info-section { width: 100%; margin-bottom: 30px; }
        .info-box { width: 50%; vertical-align: top; font-size: 11px; }
        .label { font-size: 8px; color: #aaa; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; display: block; }
        table.items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items-table th { background: #f9f9f9; text-align: left; padding: 10px; font-size: 9px; text-transform: uppercase; border-bottom: 1px solid #000; }
        table.items-table td { padding: 12px 10px; border-bottom: 0.5px solid #eee; font-size: 11px; }
        .summary-table { width: 250px; margin-left: auto; margin-top: 20px; }
        .summary-table td { padding: 5px 0; font-size: 11px; }
        .total-row { font-size: 15px; font-weight: bold; border-top: 1px solid #000; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 8px; color: #bbb; letter-spacing: 1px; }
    </style>
</head>
<body>
<div class="watermark">FELIKAY</div>
<div class="page">
    <div class="header">';

if (!empty($logo_base64)) {
    $html .= '
        <div class="logo-container">
            <img src="' . $logo_base64 . '" class="logo-img" alt="Logo">
        </div>';
}

$html .= '
        <div class="brand">FELIKAY</div>
        <div class="subtitle">Maison de Mode • Kinshasa</div>
        <div class="doc-title">' . $type_document . '</div>
    </div>';

if ($is_paid) {
    $html .= '<div class="status-badge status-paid">Payé par ' . $affichage_mode . '</div>';
} else {
    $html .= '<div class="status-badge status-unpaid">Solde à payer : ' . number_format($total_ttc, 2) . ' $</div>';
}

$html .= '
    <table class="info-section">
        <tr>
            <td class="info-box">
                <span class="label">Client & Destination</span>
                <strong style="font-size: 13px;">' . htmlspecialchars($commande['nom_complet'] ?? $commande['user_nom']) . '</strong><br>
                ' . ($is_livraison ? htmlspecialchars($commande['adresse_livraison']) . '<br>' . htmlspecialchars($commande['quartier']) . ', ' . htmlspecialchars($commande['commune']) : '<strong style="color:#e67e22;">À RETIRER EN BOUTIQUE</strong>') . '<br>
                Tél : ' . htmlspecialchars($commande['telephone'] ?? $commande['user_tel']) . '
            </td>
            <td class="info-box" style="text-align: right;">
                <span class="label">Détails</span>
                <strong>Référence :</strong> #' . $real_id . '<br>
                <strong>Date :</strong> ' . date("d/m/Y", strtotime($commande['created_at'])) . '<br>
                <strong>Mode :</strong> ' . $affichage_mode . '
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr><th>Désignation</th><th style="text-align: center;">Qté</th><th style="text-align: right;">Total</th></tr>
        </thead>
        <tbody>';
foreach ($items as $item) {
    $html .= '<tr>
        <td><strong>' . htmlspecialchars($item['produit_nom']) . '</strong><br><small>Taille: ' . ($item['taille_nom'] ?? 'STD') . ' | Couleur: ' . ($item['couleur_nom'] ?? 'Unique') . '</small></td>
        <td style="text-align: center;">' . $item['quantite'] . '</td>
        <td style="text-align: right;">' . number_format($item['prix_unitaire'] * $item['quantite'], 2) . ' $</td>
    </tr>';
}
$html .= '</tbody></table>

    <table class="summary-table">
        <tr><td>Sous-total</td><td style="text-align: right;">' . number_format($sous_total, 2) . ' $</td></tr>
        <tr><td>Livraison</td><td style="text-align: right;">' . number_format($frais_livraison, 2) . ' $</td></tr>
        <tr class="total-row"><td style="padding-top:10px;">TOTAL TTC</td><td style="text-align: right; padding-top:10px;">' . number_format($total_ttc, 2) . ' $</td></tr>
    </table>
    <div class="footer">FELIKAY • 28 CADECO, GOMBE • +243 829 045 003</div>
</div>
</body>
</html>';

// 5. GÉNÉRATION PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$pdfOutput = $dompdf->output();

// 6. ENVOI PAR EMAIL
if (!empty($client_email)) {
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
        $mail->addAddress($client_email);
        $mail->addStringAttachment($pdfOutput, "Felikay_Commande_" . $real_id . ".pdf");

        $mail->isHTML(true);
        $mail->Subject = "Votre $type_document Felikay - #$real_id";
        $mail->Body    = "Merci de votre achat chez Felikay. Veuillez trouver ci-joint votre $type_document au format PDF.";

        $mail->send();
    } catch (Exception $e) {
        error_log("Erreur mail commande #$real_id : " . $mail->ErrorInfo);
    }
}

// 7. SORTIE NAVIGATEUR (Affichage du PDF)
$dompdf->stream("Felikay_" . $real_id . ".pdf", ["Attachment" => false]);
