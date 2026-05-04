<?php
// C:\wamp64\www\ProjetFelykay\pages\admin\admin_dashboard.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/function.php';
isAdmin();

$pageTitle = "Felikay Admin | Dashboard";
include __DIR__ . '/../../includes/header.php';

// --- INITIALISATION DES COMPTEURS ---
$countArticles = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$totalVentes = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE statut = 'paye'")->fetchColumn() ?? 0;
$countCommandes = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();

// AJOUT ICI : Définition de la variable pour éviter l'erreur "Undefined variable"
$ruptures = get_low_stock_count($pdo, 0);

try {
  // 1. Nombre total de vues aujourd'hui
  $viewsToday = $pdo->query("SELECT COUNT(*) FROM visites WHERE DATE(date_visite) = CURDATE()")->fetchColumn() ?? 0;

  // 2. Nombre de visiteurs uniques (par IP)
  $uniqueVisitors = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM visites")->fetchColumn() ?? 0;

  // 3. Pages les plus visitées (Top 5)
  $topPages = $pdo->query("SELECT page_visitee, COUNT(*) as nb FROM visites GROUP BY page_visitee ORDER BY nb DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC) ?? [];
} catch (Exception $e) {
  $viewsToday = 0;
  $uniqueVisitors = 0;
  $topPages = [];
}

// --- REQUÊTES POUR LES LIVRAISONS (Nécessaires pour l'onglet Delivery) ---
$performances = $pdo->query("SELECT u.nom, COUNT(c.id) as total FROM users u INNER JOIN commandes c ON u.id = c.livreur_id WHERE c.statut = 'livre' GROUP BY u.nom")->fetchAll(PDO::FETCH_ASSOC);
$historiqueLivraisons = $pdo->query("SELECT c.*, u.nom as nom_livreur FROM commandes c JOIN users u ON c.livreur_id = u.id WHERE c.statut = 'livre' ORDER BY c.updated_at DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);

// --- REQUÊTE PRODUITS ---
$sql_produits = "SELECT p.*, c.nom as cat_nom, 
                GROUP_CONCAT(DISTINCT cl.code_hex) as les_couleurs,
                GROUP_CONCAT(DISTINCT t.nom) as les_tailles
                FROM produits p 
                LEFT JOIN categories c ON p.categorie_id = c.id 
                LEFT JOIN produit_couleurs pc ON p.id = pc.produit_id
                LEFT JOIN couleurs cl ON pc.couleur_id = cl.id
                LEFT JOIN produit_tailles pt ON p.id = pt.produit_id
                LEFT JOIN tailles t ON pt.taille_id = t.id
                GROUP BY p.id 
                ORDER BY p.created_at DESC";

$produits = $pdo->query($sql_produits)->fetchAll();

$allColors = $pdo->query("SELECT * FROM couleurs ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$allSizes = $pdo->query("SELECT * FROM tailles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Le reste de ton code HTML reste identique -->
<div id="successToast" class="fixed top-10 left-1/2 -translate-x-1/2 z-[100] transform transition-all duration-500 translate-y-[-150%] opacity-0">
  <!-- ... contenu du toast ... -->
</div>

<!-- Ligne 66 : Maintenant $ruptures existe bien ! -->
<?php if ($ruptures > 0): ?>
  <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl flex justify-between items-center mx-8 mt-4">
    <div class="flex items-center gap-3">
      <span class="text-2xl">⚠️</span>
      <div>
        <h4 class="text-sm font-bold text-red-800 uppercase tracking-tight">Alerte de Stock</h4>
        <p class="text-xs text-red-600">Il y a <strong><?= $ruptures ?></strong> produit(s) en rupture de stock.</p>
      </div>
    </div>
    <a href="#section-products" onclick="switchTab('products', 'Catalogue', event)" class="bg-red-600 text-white px-4 py-2 rounded-lg text-[10px] font-bold uppercase hover:bg-red-700 transition">
      Voir les articles
    </a>
  </div>
<?php endif; ?>

<div class="flex min-h-screen bg-gray-100 font-sans text-slate-700">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="flex-1 ml-64 p-8">
    <header class="flex justify-between items-center mb-10">
      <h2 id="current-title" class="text-2xl font-bold tracking-tight">Dashboard</h2>
      <div class="flex gap-3">
        <a href="../index.php" class="px-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-slate-50 transition">Site public</a>
        <button onclick="openModal()" class="px-4 py-2 bg-black text-white rounded-lg text-xs font-bold uppercase tracking-wider hover:bg-zinc-800 shadow-md transition">+ Nouveau</button>
      </div>
    </header>

    <div id="section-overview" class="tab-content block">
      <?php include 'tabs/overview.php'; ?>
    </div>

    <div id="section-products" class="tab-content hidden">
      <?php include 'tabs/products.php'; ?>
    </div>

    <div id="section-messages" class="tab-content hidden">
      <?php include 'tabs/messages.php'; ?>
    </div>

    <div id="section-orders" class="tab-content hidden">
      <?php include 'tabs/orders.php'; ?>
    </div>

    <div id="section-communes" class="tab-content hidden">
      <?php include __DIR__ . '/tabs/communes.php'; ?>
    </div>

    <div id="section-delivery" class="tab-content hidden">
      <?php include 'tabs/delivery.php'; ?>
    </div>

    <div id="section-analytics" class="tab-content hidden">
      <?php include 'tabs/analytics.php'; ?>
    </div>

    <div id="section-archives" class="tab-content hidden">
      <?php include 'tabs/archives.php'; ?>
    </div>

    <div id="section-settings" class="tab-content hidden">
      <?php include 'tabs/settings.php'; ?>
    </div>

  </main>
</div>

<?php include __DIR__ . '/tabs/modal_order.php'; ?>




<script src="/ProjetFelykay/assets/js/dashboard.js"></script>


<style>
  .nav-link {
    color: #94a3b8;
  }

  .nav-link:hover {
    background: #f8fafc;
    color: #0f172a;
  }

  .nav-link.active {
    background: #0f172a;
    color: white;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  }

  .tab-content.hidden {
    display: none;
  }

  .tab-content.block {
    display: block;
    animation: slideUp 0.4s ease;
  }

  @keyframes slideUp {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

</body>

</html>