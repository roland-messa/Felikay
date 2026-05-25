<?php
// C:\wamp64\www\ProjetFelykay\pages\admin\tabs\overview.php

// 1. Calcul des statistiques globales
$totalVentes = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE statut NOT IN ('annule', 'paye_annule')")->fetchColumn() ?: 0;
$countArticles = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$countCommandes = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();

// --- Récupérations des sous-détails pour les modales ---
$ventesJour = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE DATE(created_at) = CURDATE() AND statut NOT IN ('annule', 'paye_annule')")->fetchColumn() ?: 0;
$ventesMois = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND statut NOT IN ('annule', 'paye_annule')")->fetchColumn() ?: 0;

$cmdJour = $pdo->query("SELECT COUNT(*) FROM commandes WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
$cmdMois = $pdo->query("SELECT COUNT(*) FROM commandes WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn() ?: 0;

$artJour = $pdo->query("SELECT COUNT(*) FROM produits WHERE DATE(created_at) = CURDATE()")->fetchColumn() ?: 0;
$artMois = $pdo->query("SELECT COUNT(*) FROM produits WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn() ?: 0;

// 2. Récupération des données pour le graphique (7 derniers jours)
$salesQuery = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%d %b') as date, SUM(total_ttc) as total 
    FROM commandes 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    AND statut NOT IN ('annule', 'paye_annule')
    GROUP BY DATE(created_at)
    ORDER BY created_at ASC
");
$salesResults = $salesQuery->fetchAll(PDO::FETCH_ASSOC);

$labels = json_encode(array_column($salesResults, 'date'));
$totals = json_encode(array_column($salesResults, 'total'));
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

  <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center transition-all duration-300 hover:bg-slate-900 hover:border-slate-900 group">
    <div>
      <p class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1 group-hover:text-slate-400">Ventes Totales</p>
      <h3 class="text-3xl font-serif transition-all duration-300 group-hover:text-white group-hover:text-4xl"><?php echo number_format($totalVentes, 2); ?> $</h3>
      <button onclick="openOverviewModal('ventes', '<?= number_format($ventesJour, 2) ?> $', '<?= number_format($ventesMois, 2) ?> $', '<?= number_format($totalVentes, 2) ?> $')" class="text-[11px] font-bold text-blue-600 group-hover:text-blue-400 mt-3 flex items-center gap-1 transition-colors hover:underline ">
        Détails ➔
      </button>
    </div>
    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-xl transition-transform duration-300  group-hover:scale-110 group-hover:bg-slate-800">💰</div>
  </div>

  <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center transition-all duration-300 hover:bg-slate-900 hover:border-slate-900 group">
    <div>
      <p class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1 group-hover:text-slate-400">Articles</p>
      <h3 class="text-3xl font-serif transition-all duration-300 group-hover:text-white group-hover:text-4xl"><?php echo $countArticles; ?></h3>
      <button onclick="openOverviewModal('articles', '<?= $artJour ?>', '<?= $artMois ?>', '<?= $countArticles ?>')" class="text-[11px] font-bold text-purple-600 group-hover:text-purple-400 mt-3 flex items-center gap-1 transition-colors hover:underline ">
        Détails ➔
      </button>
    </div>
    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-xl transition-transform duration-300 group-hover:scale-110 group-hover:bg-slate-800">📦</div>
  </div>

  <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center transition-all duration-300 hover:bg-slate-900 hover:border-slate-900 group">
    <div>
      <p class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1 group-hover:text-slate-400">Commandes</p>
      <h3 class="text-3xl font-serif transition-all duration-300 group-hover:text-white group-hover:text-4xl"><?php echo $countCommandes; ?></h3>
      <button onclick="openOverviewModal('commandes', '<?= $cmdJour ?>', '<?= $cmdMois ?>', '<?= $countCommandes ?>')" class="text-[11px] font-bold text-orange-600 group-hover:text-orange-400 mt-3 flex items-center gap-1 transition-colors hover:underline">
        Détails ➔
      </button>
    </div>
    <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-xl transition-transform duration-300 group-hover:scale-110 group-hover:bg-slate-800">📜</div>
  </div>
</div>

<div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm mb-10">
  <h4 class="text-sm font-bold mb-6 italic font-serif">Activité récente</h4>
  <div class="h-64 w-full relative">
    <canvas id="salesChart"></canvas>
  </div>
</div>

<div id="overviewModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300">
  <div class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-2xl transform scale-95 transition-transform duration-300 border border-slate-100">
    <div class="flex justify-between items-center mb-6">
      <div id="modalBadge" class="px-3 py-1 text-[9px] font-black tracking-widest uppercase rounded-full"></div>
      <button onclick="closeOverviewModal()" class="w-7 h-7 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition">✕</button>
    </div>
    <h3 id="modalMainTitle" class="text-base font-bold text-slate-800 mb-4">Analyse Spécifique</h3>
    <div class="space-y-3 backend-data">
      <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
        <span class="text-xs font-medium text-slate-500">Aujourd'hui</span>
        <span id="detailJour" class="text-base font-bold text-slate-900"></span>
      </div>
      <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl">
        <span class="text-xs font-medium text-slate-500">Ce Mois-ci</span>
        <span id="detailMois" class="text-base font-bold text-slate-900"></span>
      </div>
      <div class="flex justify-between items-center p-3 bg-slate-950 text-white rounded-xl">
        <span class="text-xs font-medium opacity-80">Bilan Annuel / Total</span>
        <span id="detailAnnee" class="text-base font-bold"></span>
      </div>
    </div>
    <button onclick="closeOverviewModal()" class="w-full mt-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest transition">
      Fermer la vue
    </button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  function openOverviewModal(type, jour, mois, annee) {
    const modal = document.getElementById('overviewModal');
    const badge = document.getElementById('modalBadge');
    const title = document.getElementById('modalMainTitle');

    document.getElementById('detailJour').innerText = jour;
    document.getElementById('detailMois').innerText = mois;
    document.getElementById('detailAnnee').innerText = annee;

    if (type === 'ventes') {
      badge.className = "px-3 py-1 text-[9px] font-black tracking-widest uppercase rounded-full bg-blue-50 text-blue-600 border border-blue-200";
      badge.innerText = "Finances 💰";
      title.innerText = "Détails du Chiffre d'affaires";
    } else if (type === 'articles') {
      badge.className = "px-3 py-1 text-[9px] font-black tracking-widest uppercase rounded-full bg-purple-50 text-purple-600 border border-purple-200";
      badge.innerText = "Catalogue 📦";
      title.innerText = "Statistiques du Catalogue";
    } else {
      badge.className = "px-3 py-1 text-[9px] font-black tracking-widest uppercase rounded-full bg-orange-50 text-orange-600 border border-orange-200";
      badge.innerText = "Commandes 📜";
      title.innerText = "Volume des Commandes";
    }

    modal.classList.remove('opacity-0', 'pointer-events-none');
    modal.firstElementChild.classList.remove('scale-95');
  }

  function closeOverviewModal() {
    const modal = document.getElementById('overviewModal');
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.firstElementChild.classList.add('scale-95');
  }

  window.onclick = function(event) {
    const modal = document.getElementById('overviewModal');
    if (event.target === modal) {
      closeOverviewModal();
    }
  }

  // --- RENDU SÉCURISÉ DU GRAPHIQUE ---
  ;
  (function initSalesChart() {
    const canvas = document.getElementById('salesChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    const existingChart = Chart.getChart(canvas);
    if (existingChart) {
      existingChart.destroy();
    }

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?php echo $labels; ?>,
        datasets: [{
          label: 'Ventes ($)',
          data: <?php echo $totals; ?>,
          borderColor: '#000000', // Ligne noire pure
          backgroundColor: 'rgba(0, 0, 0, 0.05)', // Ombrage gris/noir très clair dessous
          fill: true,
          tension: 0.4, // Donne la courbure arrondie à la ligne
          borderWidth: 3, // Épaisseur de la ligne
          pointRadius: 5, // Taille des points d'ancrage
          pointHitRadius: 10,
          pointBackgroundColor: '#ffffff', // Fond intérieur des points en blanc
          pointBorderColor: '#000000', // Contour des points en noir
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: '#f1f5f9'
            },
            ticks: {
              font: {
                size: 10,
                family: 'sans-serif'
              },
              color: '#64748b'
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10,
                family: 'sans-serif'
              },
              color: '#64748b'
            }
          }
        }
      }
    });
  })();
</script>