<?php
// C:\wamp64\www\ProjetFelykay\assets\actions\generer_recu.php
require_once '../../vendor/autoload.php';
require_once '../../config/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$commande_id = $_GET['id'] ?? null;
$payment_ref = $_GET['ref'] ?? null;

if (!$commande_id && !$payment_ref) die("Identifiant de commande manquant.");

/**
 * 1. RÉCUPÉRATION DES DONNÉES (Version avec JOINTURE table paiements)
 */
$query = "
    SELECT c.*, 
           u.nom as user_nom, 
           u.email as user_email, 
           u.telephone as user_tel,
           p.mode_paiement,
           p.statut_paiement,
           p.reference_interne
    FROM commandes c 
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN paiements p ON c.id = p.commande_id
    WHERE " . ($payment_ref ? "p.reference_interne = ?" : "c.id = ?");

$stmt = $pdo->prepare($query);
$stmt->execute([$payment_ref ?? $commande_id]);
$commande = $stmt->fetch();

if (!$commande) die("Commande introuvable.");

$real_id = $commande['id'];

/**
 * 2. RÉCUPÉRATION DES ARTICLES
 */
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

/**
 * 3. PRÉPARATION DU LOGO ET VARIABLES
 */
$logoPath = '../../assets/img/felikay.jpg';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $type = pathinfo($logoPath, PATHINFO_EXTENSION);
    $data = file_get_contents($logoPath);
    $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

// Variables d'affichage
$nom_client = !empty($commande['nom_complet']) ? $commande['nom_complet'] : ($commande['user_nom'] ?? 'Client');
$telephone_client = !empty($commande['telephone']) ? $commande['telephone'] : ($commande['user_tel'] ?? 'N/A');
$frais_livraison = floatval($commande['frais_livraison'] ?? 0);
$total_ttc = floatval($commande['total_ttc']);
$sous_total = $total_ttc - $frais_livraison;

// Détermination du statut (Paiement réussi ou Commande payée)
$is_paid = ($commande['statut_paiement'] === 'reussi' || $commande['statut'] === 'paye');
$type_document = $is_paid ? "FACTURE DE PAIEMENT" : "BON DE COMMANDE";
$couleur_statut = $is_paid ? "#000000" : "#ea580c";

/**
 * 4. CONFIGURATION DOMPDF ET HTML
 */
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: "Helvetica", sans-serif; color: #333; font-size: 11px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { width: 80px; height: 80px; margin-bottom: 10px; }
        .brand { font-size: 22px; font-weight: bold; letter-spacing: 5px; text-transform: uppercase; margin-bottom: 5px; }
        .doc-type { font-size: 10px; color: ' . $couleur_statut . '; letter-spacing: 2px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; display: inline-block; padding: 5px 20px; margin-top:10px; }
        .info-table { width: 100%; margin-bottom: 30px; margin-top: 20px; }
        .info-box { width: 50%; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #000; color: #fff; padding: 10px; text-align: left; text-transform: uppercase; font-size: 8px; letter-spacing: 1px; }
        table.items td { padding: 10px; border-bottom: 1px solid #eee; }
        .summary-table { width: 100%; margin-top: 10px; }
        .summary-td { text-align: right; padding: 5px 10px; }
        .total-final { font-size: 16px; font-weight: bold; background: #f9f9f9; padding: 10px; border-top: 2px solid #000; }
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>

<div class="header">';
if ($logoBase64) {
    $html .= '<img src="' . $logoBase64 . '" class="logo"><br>';
}
$html .= '
    <div class="brand">FELIKAY</div>
    <div class="doc-type">' . $type_document . '</div>
</div>

<table class="info-table">
    <tr>
        <td class="info-box">
            <strong style="color:#999; font-size:8px;">ADRESSE DE LIVRAISON</strong><br>
            <span style="font-size:12px; font-weight:bold;">' . htmlspecialchars($nom_client) . '</span><br>';

// Gestion de l'adresse (Utilise les nouveaux champs commune/quartier)
if (empty($commande['adresse_livraison']) && empty($commande['commune'])) {
    $html .= '<span style="color:red;">Adresse non renseignée</span><br>';
} else {
    $html .= htmlspecialchars($commande['adresse_livraison']) . '<br>';
    $html .= 'Q/ ' . htmlspecialchars($commande['quartier'] ?? 'N/A') . '<br>';
    $html .= '<strong>' . htmlspecialchars($commande['commune'] ?? 'Kinshasa') . ', Kinshasa</strong><br>';
}

$html .= '
            Tél: ' . htmlspecialchars($telephone_client) . '
        </td>
        <td class="info-box" style="text-align: right;">
            <strong style="color:#999; font-size:8px;">DETAILS COMMANDE</strong><br>
            <strong>Référence :</strong> #' . $real_id . '<br>
            <strong>Paiement :</strong> ' . strtoupper($commande['mode_paiement'] ?? 'CASH') . '<br>
            <strong>Date :</strong> ' . date("d/m/Y H:i", strtotime($commande['created_at'])) . '
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th width="50%">Désignation</th>
            <th>Taille</th>
            <th>Qté</th>
            <th style="text-align:right;">Total</th>
        </tr>
    </thead>
    <tbody>';

foreach ($items as $item) {
    $sub = $item['prix_unitaire'] * $item['quantite'];
    $html .= '
        <tr>
            <td>
                <strong style="text-transform:uppercase;">' . htmlspecialchars($item['produit_nom'] ?? "Article") . '</strong>' .
        (!empty($item['couleur_nom']) ? '<br><span style="color:#666;">Couleur: ' . htmlspecialchars($item['couleur_nom']) . '</span>' : '') . '
            </td>
            <td>' . htmlspecialchars($item['taille_nom'] ?? 'Standard') . '</td>
            <td>' . $item['quantite'] . '</td>
            <td style="text-align:right;">' . number_format($sub, 2) . ' $</td>
        </tr>';
}

$html .= '
    </tbody>
</table>

<table class="summary-table">
    <tr>
        <td width="70%"></td>
        <td class="summary-td">Sous-total :</td>
        <td class="summary-td" style="width:100px;">' . number_format($sous_total, 2) . ' $</td>
    </tr>
    <tr>
        <td></td>
        <td class="summary-td">Frais de livraison :</td>
        <td class="summary-td">' . number_format($frais_livraison, 2) . ' $</td>
    </tr>
    <tr>
        <td></td>
        <td class="summary-td total-final">TOTAL TTC :</td>
        <td class="summary-td total-final">' . number_format($total_ttc, 2) . ' $</td>
    </tr>
</table>';

// Ajout du bloc du code de confirmation pour le livreur
$html .= '
<div style="margin-top: 25px; border: 2px dashed #000; padding: 12px; text-align: center; background-color: #fafafa;">
    <div style="font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">
        Code de confirmation livraison
    </div>
    <div style="font-size: 20px; font-weight: bold; letter-spacing: 6px; color: #000;">
        ' . ($commande['code_confirmation'] ?? '----') . '
    </div>
    <div style="font-size: 7px; color: #888; margin-top: 4px; text-transform: uppercase;">
        Veuillez présenter ce code au livreur uniquement après réception de votre colis.
    </div>
</div>';

if ($is_paid) {
    $html .= '<div style="margin-top: 25px; background: #f0fdf4; border: 1px solid #16a34a; padding: 10px; color: #16a34a; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9px;">
                Transaction terminée - Merci pour votre confiance
              </div>';
}

$html .= '
<div class="footer">
    Felikay Luxury - Mode & Élégance - Kinshasa, RDC
</div>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Felikay_Facture_" . $real_id . ".pdf", ["Attachment" => false]);
