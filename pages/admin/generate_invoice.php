<?php
require_once '../../includes/db_connect.php';

session_start();

// Vérification de sécurité
if (!isset($_SESSION['admin_id'])) {
  $check = $pdo->prepare("SELECT user_id FROM commandes WHERE id = ?");
  $check->execute([$_GET['id']]);
  $order_owner = $check->fetchColumn();

  if ($order_owner != ($_SESSION['user_id'] ?? null)) {
    die("Accès refusé.");
  }
}

$id = $_GET['id'] ?? null;
if (!$id) die("ID de commande manquant.");

// 1. Récupérer la commande avec LEFT JOIN pour inclure les infos de secours du profil user
$cmd = $pdo->prepare("
    SELECT c.*, u.nom as user_nom, u.email as user_email, u.telephone as user_tel 
    FROM commandes c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.id = ?
");
$cmd->execute([$id]);
$order = $cmd->fetch();

if (!$order) die("Commande introuvable.");

// Déterminer les informations à afficher (Priorité aux données saisies lors de l'achat)
$nom_affichage = !empty($order['nom_complet']) ? $order['nom_complet'] : ($order['user_nom'] ?? 'Client');
$tel_affichage = !empty($order['telephone']) ? $order['telephone'] : ($order['user_tel'] ?? 'N/A');

// 2. Récupérer les produits de la commande
$details = $pdo->prepare("SELECT cd.*, p.nom FROM commande_details cd JOIN produits p ON cd.produit_id = p.id WHERE cd.commande_id = ?");
$details->execute([$id]);
$items = $details->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
    }

    .font-serif {
      font-family: 'Playfair Display', serif;
    }

    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
</head>

<body class="bg-gray-50 py-10">

  <div class="max-w-3xl mx-auto p-10 bg-white border border-slate-200 shadow-lg font-sans">
    <div class="flex justify-between items-start border-b pb-8">
      <div>
        <h1 class="text-3xl font-serif italic text-slate-900">FELIKAY</h1>
        <p class="text-xs text-slate-500 uppercase tracking-widest mt-1">Facture d'achat</p>
      </div>
      <div class="text-right text-xs text-slate-500">
        <p class="font-bold text-slate-900">Facture #ORD-<?= $order['id'] ?></p>
        <p>Date: <?= date('d/m/Y', strtotime($order['created_at'])) ?></p>
        <p>Paiement: <?= strtoupper($order['methode_paiement'] ?? 'Livraison') ?></p>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-10 py-10">
      <div>
        <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Destinataire</p>
        <p class="font-bold text-lg"><?= htmlspecialchars($nom_affichage) ?></p>
        <p class="text-sm text-slate-600"><?= htmlspecialchars($order['adresse_livraison']) ?></p>
        <p class="text-sm text-slate-600">
          Q/ <?= htmlspecialchars($order['quartier'] ?? 'N/A') ?>,
          C/ <?= htmlspecialchars($order['commune'] ?? 'N/A') ?>
        </p>
        <p class="text-sm text-slate-600"><?= htmlspecialchars($order['ville'] ?? 'Kinshasa') ?>, RDC</p>
        <p class="text-sm font-semibold mt-2">Tél: <?= htmlspecialchars($tel_affichage) ?></p>
      </div>
      <div class="text-right">
        <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Expéditeur</p>
        <p class="font-bold">FELIKAY LUXURY</p>
        <p class="text-sm text-slate-600">Gombe, Kinshasa</p>
        <p class="text-sm text-slate-600">contact@felikay.com</p>
      </div>
    </div>

    <table class="w-full text-left text-sm mb-10">
      <thead class="border-b-2 border-slate-900">
        <tr>
          <th class="py-3">Article</th>
          <th class="py-3">Prix Unitaire</th>
          <th class="py-3">Qté</th>
          <th class="py-3 text-right">Total</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        <?php foreach ($items as $item): ?>
          <tr>
            <td class="py-4">
              <p class="font-bold"><?= htmlspecialchars($item['nom']) ?></p>
              <p class="text-[10px] text-slate-400 uppercase">
                <?= htmlspecialchars($item['taille_choisie'] ?? 'Standard') ?> /
                <?= htmlspecialchars($item['couleur_choisie'] ?? 'Unique') ?>
              </p>
            </td>
            <td class="py-4"><?= number_format($item['prix_unitaire'], 2) ?> $</td>
            <td class="py-4"><?= $item['quantite'] ?></td>
            <td class="py-4 text-right font-bold"><?= number_format($item['prix_unitaire'] * $item['quantite'], 2) ?> $</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="border-t-2 border-slate-900 pt-4 flex justify-between items-start">
      <div class="text-xs text-slate-400 italic">
        <?php if (($order['methode_paiement'] ?? '') !== 'online'): ?>
          * À régler en espèces lors de la livraison.
        <?php else: ?>
          * Paiement effectué en ligne.
        <?php endif; ?>
      </div>
      <div class="text-right">
        <p class="text-slate-500 text-xs">Total TTC</p>
        <p class="text-3xl font-serif italic"><?= number_format($order['total_ttc'], 2) ?> USD</p>
      </div>
    </div>

    <div class="mt-12 flex gap-4 no-print">
      <button onclick="window.print()" class="px-8 py-3 bg-black text-white text-[10px] uppercase tracking-widest font-bold hover:bg-slate-800 transition-colors">
        Imprimer la facture
      </button>
      <a href="javascript:history.back()" class="px-8 py-3 border border-slate-200 text-[10px] uppercase tracking-widest font-bold hover:bg-gray-50 transition-colors">
        Retour
      </a>
    </div>
  </div>

</body>

</html>