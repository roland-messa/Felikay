<?php
require_once '../../config/db.php';

// Correction de l'erreur : On ne lance la session que si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// 1. Récupération et validation de l'ID
$id = $_GET['id'] ?? $_GET['order'] ?? null;

if (!$id || !is_numeric($id)) {
  die("ID de commande invalide.");
}

// 2. Vérification du propriétaire de la commande
$check = $pdo->prepare("SELECT user_id FROM commandes WHERE id = ?");
$check->execute([$id]);
$order_owner = $check->fetchColumn();

if (!$order_owner) {
  die("Commande introuvable.");
}

// Sécurité : admin ou propriétaire uniquement
if (!isset($_SESSION['admin_id'])) {
  $current_user_id = $_SESSION['user_id'] ?? null;

  if (!$current_user_id || $order_owner != $current_user_id) {
    die("Accès refusé. Vous n'êtes pas autorisé à voir cette facture.");
  }
}

// 3. Récupération de la commande
$cmd = $pdo->prepare("
    SELECT c.*, u.nom as user_nom, u.email as user_email, u.telephone as user_tel 
    FROM commandes c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.id = ?
");
$cmd->execute([$id]);
$order = $cmd->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  die("Erreur chargement commande.");
}

// 4. Récupération des produits
$details = $pdo->prepare("
    SELECT cd.*, p.nom 
    FROM commande_details cd 
    JOIN produits p ON cd.produit_id = p.id 
    WHERE cd.commande_id = ?
");
$details->execute([$id]);
$items = $details->fetchAll(PDO::FETCH_ASSOC);

// Préparation affichage
$nom_affichage = !empty($order['nom_complet']) ? $order['nom_complet'] : ($order['user_nom'] ?? 'Client');
$tel_affichage = !empty($order['telephone']) ? $order['telephone'] : ($order['user_tel'] ?? 'N/A');

$frais_livraison = (float)($order['frais_livraison'] ?? 0);
$sous_total = 0;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Facture #ORD-<?= htmlspecialchars($order['id']) ?> | Felikay</title>
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

      body {
        background: white;
        padding: 0;
      }

      .shadow-lg {
        border: none;
        box-shadow: none;
      }
    }
  </style>
</head>

<body class="bg-gray-100 py-10">

  <div class="max-w-4xl mx-auto p-12 bg-white border border-slate-200 shadow-lg relative">

    <!-- Filigrane -->
    <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] pointer-events-none">
      <h1 class="text-9xl font-serif italic -rotate-12">Felikay</h1>
    </div>

    <div class="relative z-10">

      <!-- Header avec Logo ajouté à côté du nom -->
      <div class="flex justify-between items-start border-b border-black pb-8">
        <div class="flex items-center gap-4">
          <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo" class="w-16 h-16 object-cover rounded-full border border-gray-100 shadow-sm">
          <div>
            <h1 class="text-4xl font-serif italic font-bold">FELIKAY</h1>
            <p class="text-[10px] text-slate-500 uppercase tracking-widest mt-1">
              Maison de Mode • Kinshasa
            </p>
          </div>
        </div>

        <div class="text-right text-xs">
          <p class="font-bold text-lg">Facture #ORD-<?= htmlspecialchars($order['id']) ?></p>
          <p>Date: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
          <p class="bg-slate-100 px-2 py-1 text-[9px] uppercase mt-1">
            Statut: <?= htmlspecialchars($order['statut']) ?>
          </p>
        </div>
      </div>

      <!-- Client -->
      <div class="grid grid-cols-2 gap-16 py-10">
        <div>
          <p class="text-[10px] uppercase font-bold text-gray-400 mb-2">Client</p>
          <p class="font-bold text-lg"><?= htmlspecialchars($nom_affichage) ?></p>
          <p><?= htmlspecialchars($order['adresse_livraison']) ?></p>
          <p>Q/ <?= htmlspecialchars($order['quartier']) ?> - <?= htmlspecialchars($order['commune']) ?></p>
          <p>Tél: <?= htmlspecialchars($tel_affichage) ?></p>
        </div>

        <div class="text-right">
          <p class="text-[10px] uppercase font-bold text-gray-400 mb-2">Boutique</p>
          <p class="font-bold">FELIKAY</p>
          <p>Kinshasa, RDC</p>
        </div>
      </div>

      <!-- Produits -->
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b font-bold text-[10px] uppercase">
            <th class="text-left py-2">Produit</th>
            <th class="text-center py-2">Prix</th>
            <th class="text-center py-2">Qté</th>
            <th class="text-right py-2">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item):
            $total_ligne = $item['prix_unitaire'] * $item['quantite'];
            $sous_total += $total_ligne;
          ?>
            <tr class="border-b">
              <td class="py-3"><?= htmlspecialchars($item['nom']) ?></td>
              <td class="text-center py-3"><?= number_format($item['prix_unitaire'], 2) ?> $</td>
              <td class="text-center py-3"><?= $item['quantite'] ?></td>
              <td class="text-right py-3"><?= number_format($total_ligne, 2) ?> $</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Total -->
      <div class="mt-8 text-right">
        <p class="text-sm text-gray-600">Sous-total : <?= number_format($sous_total, 2) ?> $</p>
        <p class="text-sm text-gray-600">Livraison : <?= number_format($frais_livraison, 2) ?> $</p>
        <div class="mt-2 border-t border-black pt-2 inline-block min-w-[200px]">
          <p class="font-bold text-xl">
            Total : <?= number_format($order['total_ttc'], 2) ?> USD
          </p>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-10 no-print flex gap-4">
        <button onclick="window.print()" class="bg-black text-white px-8 py-3 text-[10px] font-bold uppercase tracking-widest hover:bg-stone-800 transition">
          Imprimer la facture
        </button>
        <a href="http://localhost/ProjetFelykay/index.php" class="border border-black px-8 py-3 text-[10px] font-bold uppercase tracking-widest hover:bg-black hover:text-white transition">
          Retour boutique
        </a>
      </div>

    </div>
  </div>

</body>

</html>