<div class="space-y-10">
  <!-- STATS PERFORMANCES -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php foreach ($performances as $perf): ?>
      <div class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm">
        <div class="flex justify-between items-start mb-4">
          <span class="text-[10px] uppercase tracking-widest font-bold text-slate-400">Livreur</span>
          <span class="bg-green-100 text-green-700 text-[9px] px-2 py-1 rounded-full font-bold">ACTIF</span>
        </div>
        <h4 class="text-xl font-bold"><?= htmlspecialchars($perf['nom']) ?></h4>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-3xl font-black text-black"><?= $perf['total'] ?></span>
          <span class="text-[10px] text-slate-500 uppercase tracking-tighter">courses terminées</span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- TABLEAU HISTORIQUE -->
  <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
      <h3 class="font-bold text-slate-800 uppercase text-xs tracking-widest">Journal des Livraisons</h3>
    </div>
    <table class="w-full text-left border-collapse">
      <thead class="bg-slate-50">
        <tr>
          <th class="p-4 text-[10px] uppercase font-bold text-slate-500">ID</th>
          <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Client</th>
          <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Livreur</th>
          <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Montant</th>
          <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Date/Heure</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($historiqueLivraisons as $h): ?>
          <tr class="hover:bg-slate-50 transition-colors">
            <td class="p-4 text-xs font-bold">#<?= $h['id'] ?></td>
            <td class="p-4 text-xs uppercase font-medium"><?= htmlspecialchars($h['nom_complet']) ?></td>
            <td class="p-4">
              <span class="text-[10px] bg-slate-900 text-white px-2 py-1 rounded italic">
                @<?= htmlspecialchars($h['nom_livreur']) ?>
              </span>
            </td>
            <td class="p-4 text-xs font-serif font-bold italic"><?= number_format($h['total_ttc'], 2) ?> $</td>
            <td class="p-4 text-[10px] text-slate-400">
              <?= date('d/m/Y H:i', strtotime($h['updated_at'])) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>