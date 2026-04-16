<?php

require_once '../../includes/db_connect.php';

session_start();
if (!isset($_SESSION['admin_id'])) {
  $check = $pdo->prepare("SELECT user_id FROM commandes WHERE id = ?");
  $check->execute([$_GET['id']]);
  $order_owner = $check->fetchColumn();

  if ($order_owner != $_SESSION['user_id']) {
    die("Accès refusé.");
  }
}

$id = $_GET['id'];
// 1. Récupérer la commande et le client
$cmd = $pdo->prepare("SELECT c.*, u.nom, u.email FROM commandes c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
$cmd->execute([$id]);
$order = $cmd->fetch();

// 2. Récupérer les produits de la commande
$details = $pdo->prepare("SELECT cd.*, p.nom FROM commande_details cd JOIN produits p ON cd.produit_id = p.id WHERE cd.commande_id = ?");
$details->execute([$id]);
$items = $details->fetchAll();
?>

<div class="max-w-3xl mx-auto p-10 bg-white border border-slate-200 shadow-lg mt-10 font-sans">
  <div class="flex justify-between items-start border-b pb-8">
    <div>
      <h1 class="text-3xl font-serif italic text-slate-900">FELIKAY</h1>
      <p class="text-xs text-slate-500 uppercase tracking-widest mt-1">Facture d'achat</p>
    </div>
    <div class="text-right text-xs text-slate-500">
      <p>Facture #ORD-<?= $order['id'] ?></p>
      <p>Date: <?= date('d/m/Y', strtotime($order['created_at'])) ?></p>
    </div>
  </div>

  <div class="grid grid-cols-2 gap-10 py-10">
    <div>
      <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Destinataire</p>
      <p class="font-bold"><?= $order['nom'] ?></p>
      <p class="text-sm text-slate-600"><?= $order['adresse_livraison'] ?></p>
      <p class="text-sm text-slate-600"><?= $order['ville'] ?>, RDC</p>
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
            <p class="font-bold"><?= $item['nom'] ?></p>
            <p class="text-[10px] text-slate-400 uppercase"><?= $item['taille_choisie'] ?> / <?= $item['couleur_choisie'] ?></p>
          </td>
          <td class="py-4"><?= number_format($item['prix_unitaire'], 2) ?> $</td>
          <td class="py-4"><?= $item['quantite'] ?></td>
          <td class="py-4 text-right font-bold"><?= number_format($item['prix_unitaire'] * $item['quantite'], 2) ?> $</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="border-t-2 border-slate-900 pt-4 flex justify-end">
    <div class="text-right">
      <p class="text-slate-500 text-xs">Total à payer</p>
      <p class="text-3xl font-serif italic"><?= number_format($order['total_ttc'], 2) ?> USD</p>
    </div>
  </div>

  <button onclick="window.print()" class="mt-10 px-6 py-2 bg-black text-white text-xs font-bold rounded-full no-print">
    Imprimer la facture
  </button>
</div>

<style>
  @media print {
    .no-print {
      display: none;
    }
  }
</style>