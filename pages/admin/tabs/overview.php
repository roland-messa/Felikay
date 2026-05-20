<?php
// C:\wamp64\www\ProjetFelykay\pages\admin\tabs\overview.php

// 1. Calcul des statistiques globales
$totalVentes = $pdo->query("SELECT SUM(total_ttc) FROM commandes WHERE statut NOT IN ('annule', 'paye_annule')")->fetchColumn() ?: 0;
$countArticles = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$countCommandes = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();

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

<!-- Section des indicateurs (Cards) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
  <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
    <div>
      <p class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1">Ventes Totales</p>
      <h3 class="text-3xl font-serif"><?php echo number_format($totalVentes, 2); ?> $</h3>
    </div>
    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-xl">💰</div>
  </div>

  <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
    <div>
      <p class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1">Articles</p>
      <h3 class="text-3xl font-serif"><?php echo $countArticles; ?></h3>
    </div>
    <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center text-xl">📦</div>
  </div>

  <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 flex justify-between items-center">
    <div>
      <p class="text-slate-400 text-xs uppercase font-bold tracking-widest mb-1">Commandes</p>
      <h3 class="text-3xl font-serif"><?php echo $countCommandes; ?></h3>
    </div>
    <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center text-xl">📜</div>
  </div>
</div>

<!-- Section du Graphique -->
<div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-sm mb-10">
  <h4 class="text-sm font-bold mb-6 italic font-serif">Activité récente</h4>
  <div class="h-64 w-full">
    <canvas id="salesChart"></canvas>
  </div>
</div>

<!-- Scripts pour le graphique -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('salesChart').getContext('2d');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?php echo $labels; ?>,
        datasets: [{
          label: 'Ventes ($)',
          data: <?php echo $totals; ?>,
          borderColor: '#000000',
          backgroundColor: 'rgba(0, 0, 0, 0.05)',
          fill: true,
          tension: 0.4,
          borderWidth: 2,
          pointRadius: 4,
          pointBackgroundColor: '#fff'
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
                size: 10
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 10
              }
            }
          }
        }
      }
    });
  });
</script>