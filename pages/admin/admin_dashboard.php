<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/function.php';
isAdmin();

$pageTitle = "Felikay Admin | Dashboard";
include __DIR__ . '/../../includes/header.php';

$countArticles = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$totalVentes = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE statut = 'paye'")->fetchColumn() ?? 0;
$countCommandes = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();

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


<div id="successToast" class="fixed top-10 left-1/2 -translate-x-1/2 z-[100] transform transition-all duration-500 translate-y-[-150%] opacity-0">
  <div class="bg-black text-white px-8 py-4 rounded-2xl shadow-2xl flex items-center gap-4 border border-white/20">
    <div id="toastIcon" class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
      </svg>
    </div>
    <div class="flex flex-col">
      <span id="toastTitle" class="text-[11px] uppercase tracking-[0.2em] font-bold">Succès</span>
      <span id="toastMessage" class="text-[10px] text-slate-300">Opération effectuée.</span>
    </div>
  </div>
</div>





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

    <div id="section-analytics" class="tab-content hidden">
      <?php include 'tabs/analytics.php'; ?>
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