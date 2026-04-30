<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

  <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
    <h3 class="text-xs font-bold uppercase tracking-widest mb-6">Configurer une Zone</h3>
    <form action="../../assets/actions/process_zone.php" method="POST" class="space-y-4">

      <div>
        <label class="text-[10px] uppercase text-slate-400 font-bold mb-2 block">Commune</label>
        <select name="commune" required
          class="w-full p-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-black outline-none appearance-none">
          <option value="" disabled selected>Choisir une commune...</option>
          <option value="Bandalungwa">Bandalungwa</option>
          <option value="Barumbu">Barumbu</option>
          <option value="Bumbu">Bumbu</option>
          <option value="Gombe">Gombe</option>
          <option value="Kalamu">Kalamu</option>
          <option value="Kasa-Vubu">Kasa-Vubu</option>
          <option value="Kimbanseke">Kimbanseke</option>
          <option value="Kinshasa">Kinshasa (Commune)</option>
          <option value="Kintambo">Kintambo</option>
          <option value="Kisenso">Kisenso</option>
          <option value="Lemba">Lemba</option>
          <option value="Limete">Limete</option>
          <option value="Lingwala">Lingwala</option>
          <option value="Makala">Makala</option>
          <option value="Maluku">Maluku</option>
          <option value="Masina">Masina</option>
          <option value="Matete">Matete</option>
          <option value="Mont-Ngafula">Mont-Ngafula</option>
          <option value="Ndjili">N'djili</option>
          <option value="Ngaba">Ngaba</option>
          <option value="Ngaliema">Ngaliema</option>
          <option value="Ngiri-Ngiri">Ngiri-Ngiri</option>
          <option value="Nsele">Nsele</option>
          <option value="Selembao">Selembao</option>
        </select>
      </div>

      <div>
        <label class="text-[10px] uppercase text-slate-400 font-bold mb-2 block">Quartier</label>
        <input type="text" name="quartier" placeholder="Ex: SOCIMAT" required
          class="w-full p-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-black">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="text-[10px] uppercase text-slate-400 font-bold mb-2 block">Frais (FC)</label>
          <input type="number" name="frais_fc" placeholder="Ex: 8000" required
            class="w-full p-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-black outline-none">
        </div>
        <div>
          <label class="text-[10px] uppercase text-slate-400 font-bold mb-2 block">Frais ($)</label>
          <input type="number" step="0.01" name="frais_usd" placeholder="Ex: 3.50" required
            class="w-full p-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-black outline-none">
        </div>
      </div>

      <button type="submit" class="w-full py-3 bg-black text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-zinc-800 transition shadow-lg">
        Ajouter au Tarifaire
      </button>
    </form>
  </div>

  <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-left">
      <thead>
        <tr class="border-b border-slate-50">
          <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400">Commune</th>
          <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400">Quartier</th>
          <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-slate-400">Prix (FC / $)</th>
          <th class="px-6 py-4 text-right"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        <?php
        try {
          $zones = $pdo->query("SELECT * FROM zones_livraison ORDER BY commune, quartier ASC")->fetchAll();
          if (empty($zones)) {
            echo '<tr><td colspan="4" class="px-6 py-10 text-center text-slate-400 text-xs italic">Aucune zone configurée pour le moment.</td></tr>';
          }
          foreach ($zones as $z):
        ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="px-6 py-4 text-sm font-medium"><?= htmlspecialchars($z['commune']) ?></td>
              <td class="px-6 py-4 text-sm text-slate-500"><?= htmlspecialchars($z['quartier']) ?></td>
              <td class="px-6 py-4 text-sm">
                <div class="font-bold text-black"><?= number_format($z['frais_fc'], 0, '.', ' ') ?> FC</div>
                <div class="text-[10px] text-slate-400 font-medium"><?= number_format($z['frais_usd'], 2) ?> $</div>
              </td>
              <td class="px-6 py-4 text-right">
                <button onclick="deleteZone(<?= $z['id'] ?>)" class="text-slate-300 hover:text-red-500 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </td>
            </tr>
        <?php
          endforeach;
        } catch (Exception $e) {
          echo '<tr><td colspan="4" class="px-6 py-4 text-red-500 text-xs">Erreur : ' . $e->getMessage() . '</td></tr>';
        }
        ?>
      </tbody>
    </table>
  </div>
</div>