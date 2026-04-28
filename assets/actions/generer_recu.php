<?php
// C:\wamp64\www\ProjetFelykay\assets\actions\generer_recu.php
require_once '../../vendor/autoload.php';
require_once '../../config/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// On accepte soit l'ID soit la REF
$commande_id = $_GET['id'] ?? null;
$payment_ref = $_GET['ref'] ?? null;

if (!$commande_id && !$payment_ref) die("Identifiant de commande manquant.");

/**
 * 1. RÉCUPÉRATION DES DONNÉES
 */
$query = "
    SELECT c.*, 
           u.nom as user_nom, 
           u.email as user_email, 
           u.telephone as user_tel
    FROM commandes c 
    LEFT JOIN users u ON c.user_id = u.id
    WHERE " . ($payment_ref ? "c.payment_ref = ?" : "c.id = ?");

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

// --- Logique d'affichage (Variables) ---
$nom_client = !empty($commande['nom_complet']) ? $commande['nom_complet'] : ($commande['user_nom'] ?? 'Client');
$telephone_client = !empty($commande['telephone']) ? $commande['telephone'] : ($commande['user_tel'] ?? 'N/A');

// On vérifie si c'est payé
$is_paid = ($commande['statut'] === 'paye' || $commande['statut'] === 'paid');
$type_document = $is_paid ? "FACTURE DE PAIEMENT" : "BON DE COMMANDE";
$couleur_statut = $is_paid ? "#000000" : "#ea580c";

/**
 * 4. CONFIGURATION DOMPDF ET HTML
 */
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = '
<style>
    body { font-family: "Helvetica", sans-serif; color: #333; font-size: 11px; line-height: 1.6; }
    .header { text-align: center; margin-bottom: 40px; }
    .brand { font-size: 24px; font-weight: bold; letter-spacing: 5px; margin-bottom: 5px; }
    .doc-type { font-size: 10px; color: ' . $couleur_statut . '; letter-spacing: 2px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; display: inline-block; padding: 5px 20px; }
    
    .info-table { width: 100%; margin-bottom: 40px; margin-top: 30px; }
    .info-box { width: 50%; vertical-align: top; }
    
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    table.items th { background: #000; color: #fff; padding: 12px 10px; text-align: left; text-transform: uppercase; font-size: 8px; letter-spacing: 1px; }
    table.items td { padding: 12px 10px; border-bottom: 1px solid #eee; }
    
    .total-section { text-align: right; font-size: 16px; font-weight: bold; margin-top: 20px; padding-top: 10px; }
    .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; font-size: 8px; color: #999; text-transform: uppercase; letter-spacing: 1px; }
</style>

<div class="header">
    <div class="brand">FELIKAY</div>
    <div class="doc-type">' . $type_document . '</div>
</div>

<table class="info-table">
    <tr>
        <td class="info-box">
            <strong style="color:#999; font-size:8px; tracking:1px;">CLIENT</strong><br>
            <span style="font-size:12px; font-weight:bold;">' . htmlspecialchars($nom_client) . '</span><br>
            ' . htmlspecialchars($commande['adresse_livraison']) . '<br>
            ' . htmlspecialchars($commande['commune'] ?? 'Kinshasa') . ', RDC<br>
            Tél: ' . htmlspecialchars($telephone_client) . '
        </td>
        <td class="info-box" style="text-align: right;">
            <strong style="color:#999; font-size:8px; tracking:1px;">REFERENCE</strong><br>
            <strong>N° :</strong> #' . $real_id . '<br>
            <strong>ID Paiement :</strong> ' . ($commande['payment_ref'] ?? 'N/A') . '<br>
            <strong>Date :</strong> ' . date("d/m/Y", strtotime($commande['created_at'])) . '
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
            <td>' . ($item['taille_nom'] ?? 'Standard') . '</td>
            <td>' . $item['quantite'] . '</td>
            <td style="text-align:right;">' . number_format($sub, 2) . ' $</td>
        </tr>';
}

$html .= '
    </tbody>
</table>

<div class="total-section">
    MONTANT TOTAL : ' . number_format($commande['total_ttc'], 2) . ' $
</div>';

if ($is_paid) {
    $html .= '<div style="margin-top: 40px; background: #f0fdf4; border: 1px solid #16a34a; padding: 15px; color: #16a34a; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 10px; letter-spacing: 2px;">
                Paiement Confirmé via ' . strtoupper($commande['methode_paiement']) . '
              </div>';
}

$html .= '
<div class="footer">
    Maison Felikay Luxury - Kinshasa, RDC - www.felikay.com
</div>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Felikay_Recu_" . $real_id . ".pdf", ["Attachment" => false]);
