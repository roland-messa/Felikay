<?php

require_once '../../vendor/autoload.php';
require_once '../../config/db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$commande_id = $_GET['id'] ?? null;
if (!$commande_id) die("ID de commande manquant.");

// 1. Récupération des données (Commande + User en direct via user_id)
$stmt = $pdo->prepare("
    SELECT c.*, u.nom, u.email, u.telephone as user_tel
    FROM commandes c 
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
");
$stmt->execute([$commande_id]);
$commande = $stmt->fetch();

if (!$commande) die("Commande introuvable.");

// 2. Récupération des articles avec jointures pour les noms de Taille et Couleur
$stmtDetails = $pdo->prepare("
    SELECT cd.*, p.nom as produit_nom, t.nom as taille_nom, col.nom as couleur_nom 
    FROM commande_details cd
    LEFT JOIN produits p ON cd.produit_id = p.id
    LEFT JOIN tailles t ON cd.taille_id = t.id
    LEFT JOIN couleurs col ON cd.couleur_id = col.id
    WHERE cd.commande_id = ?
");
$stmtDetails->execute([$commande_id]);
$items = $stmtDetails->fetchAll();

$is_paid = (($commande['statut_paiement'] ?? '') === 'paye' || ($commande['methode_paiement'] ?? '') === 'online');
$type_document = $is_paid ? "FACTURE DE PAIEMENT" : "BON DE COMMANDE";
$couleur_statut = $is_paid ? "#16a34a" : "#ea580c";

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = '
<style>
    body { font-family: "Helvetica", sans-serif; color: #333; font-size: 12px; line-height: 1.4; }
    .header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
    .logo { width: 70px; height: 70px; margin-bottom: 10px; }
    .brand { font-size: 24px; font-weight: bold; letter-spacing: 2px; }
    .doc-type { font-size: 14px; color: ' . $couleur_statut . '; margin-top: 5px; font-weight: bold; }
    
    .info-table { width: 100%; margin-bottom: 40px; }
    .info-box { width: 50%; vertical-align: top; }
    
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
    table.items th { background: #f8f8f8; padding: 12px; text-align: left; text-transform: uppercase; font-size: 10px; border-bottom: 2px solid #000; }
    table.items td { padding: 12px; border-bottom: 1px solid #eee; }
    
    .total-section { text-align: right; font-size: 16px; font-weight: bold; margin-top: 20px; border-top: 2px solid #eee; padding-top: 10px; }
    .note-livraison { margin-top: 30px; padding: 15px; background: #fffcf0; border: 1px solid #f5e9b3; color: #856404; font-style: italic; }
    .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; padding-bottom: 20px; }
</style>

<div class="header">
    <img src="http://localhost/ProjetFelykay/assets/img/felikay.jpg" class="logo">
    <div class="brand">FELIKAY</div>
    <div class="doc-type">' . $type_document . '</div>
</div>

<table class="info-table">
    <tr>
        <td class="info-box">
            <strong>DESTINATAIRE</strong><br>
            ' . htmlspecialchars($commande['nom']) . '<br>
            ' . htmlspecialchars($commande['adresse_livraison']) . '<br>
            Tél: ' . htmlspecialchars($commande['user_tel']) . '
        </td>
        <td class="info-box" style="text-align: right;">
            <strong>DÉTAILS</strong><br>
            N° : #' . $commande_id . '<br>
            Date : ' . date("d/m/Y", strtotime($commande['created_at'])) . '<br>
            Méthode : ' . strtoupper($commande['methode_paiement'] ?? 'LIVRAISON') . '
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th>Description</th>
            <th>Taille</th>
            <th>Qté</th>
            <th>Prix Unit.</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>';

foreach ($items as $item) {
    $sub = $item['prix_unitaire'] * $item['quantite'];
    $nom_produit = $item['produit_nom'] ?? "Produit #" . $item['produit_id'];
    $couleur = !empty($item['couleur_nom']) ? " (" . $item['couleur_nom'] . ")" : "";

    $html .= '
        <tr>
            <td>' . htmlspecialchars($nom_produit) . $couleur . '</td>
            <td>' . ($item['taille_nom'] ?? 'Standard') . '</td>
            <td>' . $item['quantite'] . '</td>
            <td>' . number_format($item['prix_unitaire'], 2) . ' $</td>
            <td>' . number_format($sub, 2) . ' $</td>
        </tr>';
}

$html .= '
    </tbody>
</table>

<div class="total-section">
    TOTAL : ' . number_format($commande['total_ttc'], 2) . ' $
</div>';

if (!$is_paid) {
    $html .= '
    <div class="note-livraison">
        <strong>ATTENTION :</strong> Cette commande est à régler en espèces auprès du livreur. 
        Veuillez prévoir le montant total ainsi que les frais de livraison applicables à Kinshasa.
    </div>';
} else {
    $html .= '
    <div style="margin-top: 30px; color: #16a34a; font-weight: bold; text-align: center;">
        PAIEMENT CONFIRMÉ. Merci pour votre confiance.
    </div>';
}

$html .= '
<div class="footer">
    Felikay Luxury Experience - Kinshasa, RDC - ' . date("Y") . '<br>
    Document généré automatiquement par le système de gestion Felikay.
</div>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Felikay_Recu_" . $commande_id . ".pdf", ["Attachment" => false]);
