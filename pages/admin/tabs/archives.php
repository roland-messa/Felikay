<div class="space-y-6">
  <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
    <form id="filter-form" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-400 mb-2 block">N° Commande</label>
        <input type="text" id="search-id" placeholder="Ex: 125"
          class="w-full p-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-black">
      </div>
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-400 mb-2 block">Nom du Client</label>
        <input type="text" id="search-name" placeholder="Rechercher..."
          class="w-full p-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-black">
      </div>
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-400 mb-2 block">Date</label>
        <input type="date" id="search-date"
          class="w-full p-3 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-black">
      </div>

      <button type="button" onclick="searchArchives()"
        class="py-3 bg-black text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-zinc-800 transition shadow-lg">
        Rechercher
      </button>

      <button type="button" onclick="resetArchives()"
        class="py-3 bg-slate-100 text-slate-700 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-200 transition">
        Voir Tout 🔄
      </button>
    </form>
  </div>

  <div class="bg-white rounded-3xl border border-slate-300 shadow-sm overflow-hidden">
    <table class="w-full text-left text-sm" id="archive-table">
      <thead class="bg-slate-50 border-b border-slate-200">
        <tr class="text-[10px] uppercase font-bold text-slate-400">
          <th class="p-6">ID / Client</th>
          <th class="p-6">Destination</th>
          <th class="p-6">Date Archive</th>
          <th class="p-6">Montant Final</th>
          <th class="p-6 text-right">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $archives = $pdo->query("
                    SELECT c.*, u.nom as client_nom 
                    FROM commandes c 
                    LEFT JOIN users u ON c.user_id = u.id 
                    WHERE c.statut = 'archive' 
                    ORDER BY c.created_at DESC
                ")->fetchAll();

        foreach ($archives as $a):
          // Sécurité pour parer aux valeurs nulles ou clients supprimés
          $nom_client = $a['client_nom'] ?? $a['nom_complet'] ?? 'Client Inconnu';
        ?>
          <tr class="archive-row border-b border-slate-50 hover:bg-slate-50/50 transition-colors"
            data-id="<?= $a['id'] ?>"
            data-name="<?= htmlspecialchars(strtolower($nom_client)) ?>"
            data-date="<?= date('Y-m-d', strtotime($a['created_at'])) ?>">

            <td class="p-6">
              <p class="font-bold text-slate-900">#ORD-<?= $a['id'] ?></p>
              <p class="text-[10px] text-slate-500 uppercase font-bold">👤 <?= htmlspecialchars($nom_client) ?></p>
            </td>
            <td class="p-6">
              <span class="text-xs font-medium"><?= htmlspecialchars($a['commune'] ?? 'Non spécifiée') ?></span>
            </td>
            <td class="p-6 text-slate-500 text-[11px]">
              <?= date('d/m/Y', strtotime($a['created_at'])) ?>
            </td>
            <td class="p-6 font-bold text-slate-900">
              <?= number_format($a['total_ttc'], 2) ?> USD
            </td>
            <td class="p-6 text-right">
              <button onclick='viewOrderDetails(<?= json_encode($a) ?>)'
                class="p-2 bg-slate-100 text-slate-600 rounded-xl hover:bg-black hover:text-white transition-all">
                <i data-lucide="eye" class="w-4 h-4"></i>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  // Exécute un filtrage croisé en temps réel sur la table sans rechargement de page
  function searchArchives() {
    const idVal = document.getElementById('search-id').value.trim().toLowerCase();
    const nameVal = document.getElementById('search-name').value.trim().toLowerCase();
    const dateVal = document.getElementById('search-date').value; // Format natif : YYYY-MM-DD

    const rows = document.querySelectorAll('.archive-row');

    rows.forEach(row => {
      const id = row.getAttribute('data-id').toLowerCase();
      const name = row.getAttribute('data-name').toLowerCase();
      const date = row.getAttribute('data-date');

      // Si le filtre est vide, la condition renvoie true d'office
      const matchesId = idVal === "" || id.includes(idVal);
      const matchesName = nameVal === "" || name.includes(nameVal);
      const matchesDate = dateVal === "" || date === dateVal;

      // La ligne s'affiche uniquement si elle valide simultanément les 3 critères
      if (matchesId && matchesName && matchesDate) {
        row.style.display = "";
      } else {
        row.style.display = "none";
      }
    });
  }

  // Nettoie tous les champs et réaffiche l'intégralité du tableau
  function resetArchives() {
    document.getElementById('search-id').value = "";
    document.getElementById('search-name').value = "";
    document.getElementById('search-date').value = "";

    const rows = document.querySelectorAll('.archive-row');
    rows.forEach(row => {
      row.style.display = "";
    });
  }
</script>