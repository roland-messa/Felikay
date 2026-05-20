<?php

$viewsToday = $pdo->query("SELECT COUNT(*) FROM visites WHERE DATE(date_visite) = CURDATE()")->fetchColumn() ?: 0;

$uniqueVisitors = $pdo->query("SELECT COUNT(DISTINCT ip_address) FROM visites")->fetchColumn() ?: 0;

$topPages = $pdo->query("SELECT page_visitee, COUNT(*) as nb FROM visites GROUP BY page_visitee ORDER BY nb DESC LIMIT 5")->fetchAll();
?>



<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
  <div class="bg-white p-8 rounded-3xl border border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
    <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Vues aujourd'hui</p>
    <h3 class="text-4xl font-serif font-bold"><?php echo $viewsToday; ?></h3>
    <p class="text-[10px] text-green-500 mt-2">▲ Temps réel activé</p>
  </div>
  <div class="bg-white p-8 rounded-3xl border border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
    <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Visiteurs Uniques</p>
    <h3 class="text-4xl font-serif font-bold"><?php echo $uniqueVisitors; ?></h3>
    <p class="text-[10px] text-slate-400 mt-2">Total cumulé</p>
  </div>
</div>

<div class="bg-white rounded-3xl border border-slate-300 shadow-sm overflow-hidden">
  <div class="p-6 bg-slate-50/50 border-b border-slate-100">
    <h4 class="font-serif italic text-lg">Pages les plus consultées</h4>
  </div>
  <table class="w-full text-left text-sm">
    <thead class="bg-white border-b border-black">
      <tr class="text-[10px] uppercase font-bold text-black">
        <th class="p-6">URL de la page</th>
        <th class="p-6 text-right">Nombre de vues</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($topPages as $page): ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
          <td class="p-6 font-mono text-xs text-blue-600">
            <?php echo htmlspecialchars($page['page_visitee']); ?>
          </td>
          <td class="p-6 text-right font-bold">
            <span class="bg-black text-white px-3 py-1 rounded-full text-xs">
              <?php echo $page['nb']; ?> vues
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($topPages)): ?>
        <tr>
          <td colspan="2" class="p-10 text-center italic text-slate-400">Aucune donnée de visite pour le moment.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>