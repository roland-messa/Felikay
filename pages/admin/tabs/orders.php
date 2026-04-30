<div class="bg-white rounded-3xl border border-slate-300 shadow-sm overflow-hidden">
  <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
    <div>
      <h4 class="text-xs font-bold uppercase tracking-widest text-slate-800">Commandes Actives</h4>
      <p class="text-[10px] text-slate-400 mt-1">Archivez uniquement les commandes traitées.</p>
    </div>

    <div class="flex gap-3">
      <button id="btn-bulk-archive" onclick="bulkArchive()" class="hidden px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-blue-600 transition shadow-lg flex items-center gap-2">
        📁 Archiver la sélection (<span id="selected-count">0</span>)
      </button>

      <div class="flex gap-2">
        <span class="px-3 py-1 bg-green-50 text-green-600 rounded-full text-[9px] font-bold uppercase border border-green-200">Traité</span>
        <span class="px-3 py-1 bg-orange-50 text-orange-600 rounded-full text-[9px] font-bold uppercase border border-orange-100">En cours</span>
      </div>
    </div>
  </div>

  <table class="w-full text-left text-sm">
    <thead class="bg-white border-b border-slate-200">
      <tr class="text-[10px] uppercase font-bold text-slate-400">
        <th class="p-6 w-10">
          <input type="checkbox" id="select-all" onclick="toggleSelectAll()" class="rounded border-slate-300 text-black focus:ring-black">
        </th>
        <th class="p-6">ID / Client</th>
        <th class="p-6">Détails Livraison</th>
        <th class="p-6 text-center">Frais Livr.</th>
        <th class="p-6">Total Final</th>
        <th class="p-6 text-center">Statut</th>
        <th class="p-6 text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $stmt = $pdo->query("
        SELECT c.*, u.nom as client_nom 
        FROM commandes c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.statut != 'archive' OR c.statut IS NULL
        ORDER BY c.created_at DESC
      ");

      while ($c = $stmt->fetch()):
        $isArchivable = in_array($c['statut'], ['paye', 'livre', 'annule']);
        $clientNom = htmlspecialchars($c['client_nom'] ?? 'Client Inconnu');

        $statusColor = match ($c['statut']) {
          'paye', 'livre' => 'text-green-600 bg-green-50 border-green-200',
          'annule' => 'text-red-600 bg-red-50 border-red-200',
          default => 'text-orange-600 bg-orange-50 border-orange-200',
        };
      ?>
        <tr class="order-row border-b border-slate-50 hover:bg-slate-50/50 transition-colors" data-client-name="<?= $clientNom ?>">
          <td class="p-6">
            <?php if ($isArchivable): ?>
              <input type="checkbox" name="order_ids[]" value="<?= $c['id'] ?>" onclick="updateBulkButton()" class="order-checkbox rounded border-slate-300 text-black focus:ring-black">
            <?php else: ?>
              <div title="Valider avant d'archiver" class="w-4 h-4 border border-slate-100 rounded bg-slate-50 opacity-30 cursor-not-allowed"></div>
            <?php endif; ?>
          </td>

          <td class="p-6">
            <p class="font-bold text-slate-900">#ORD-<?= $c['id'] ?></p>
            <p class="text-[10px] text-blue-600 uppercase font-bold"><?= $clientNom ?></p>
          </td>

          <td class="p-6">
            <span class="text-slate-700 font-bold text-[11px]"><?= htmlspecialchars($c['commune'] ?? 'N/A') ?></span>
          </td>

          <td class="p-6 text-center">
            <input type="number" step="0.01" value="<?= $c['frais_livraison'] ?? 0 ?>"
              onchange="updateFraisCommande(<?= $c['id'] ?>, this.value)"
              class="w-14 p-1 text-center text-xs font-bold border border-slate-200 rounded-lg outline-none">
          </td>

          <td class="p-6 font-bold text-slate-900">
            <?= number_format($c['total_ttc'] ?? 0, 2) ?> <span class="text-[9px]">USD</span>
          </td>

          <td class="p-6 text-center">
            <span class="px-3 py-1 rounded-full text-[9px] font-black border <?= $statusColor ?>">
              <?= strtoupper(str_replace('_', ' ', $c['statut'] ?? 'ATTENTE')) ?>
            </span>
          </td>

          <td class="p-6 text-right">
            <select onchange="updateOrderStatus(<?= $c['id'] ?>, this.value)"
              class="p-1.5 text-[10px] font-bold border border-slate-200 rounded-xl bg-white outline-none cursor-pointer">
              <option value="en_attente" <?= ($c['statut'] == 'en_attente') ? 'selected' : '' ?>>🕒 Attente</option>
              <option value="paye" <?= ($c['statut'] == 'paye') ? 'selected' : '' ?>>✅ Payé</option>
              <option value="livre" <?= ($c['statut'] == 'livre') ? 'selected' : '' ?>>📦 Livré</option>
              <option value="annule" <?= ($c['statut'] == 'annule') ? 'selected' : '' ?>>❌ Annulé</option>
              <?php if ($isArchivable): ?>
                <option value="archive">📁 Archiver</option>
              <?php endif; ?>
            </select>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
  // Correction des chemins : On utilise "../../assets/actions/..." 
  // pour remonter de "pages/admin/tabs/" vers la racine "ProjetFelykay/"

  function updateOrderStatus(orderId, newStatus) {
    fetch('../../assets/actions/update_order_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `order_id=${orderId}&status=${newStatus}`
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) location.reload();
        else alert("Erreur lors de la mise à jour du statut");
      }).catch(err => console.error("Erreur:", err));
  }

  function updateBulkButton() {
    const checked = document.querySelectorAll('.order-checkbox:checked');
    const btn = document.getElementById('btn-bulk-archive');
    const count = document.getElementById('selected-count');
    if (checked.length > 0) {
      btn.classList.remove('hidden');
      count.innerText = checked.length;
    } else {
      btn.classList.add('hidden');
    }
  }

  function toggleSelectAll() {
    const mainCb = document.getElementById('select-all');
    document.querySelectorAll('.order-checkbox').forEach(cb => {
      cb.checked = mainCb.checked;
    });
    updateBulkButton();
  }

  function bulkArchive() {
    const checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
    const ids = [];
    const names = [];
    checkedBoxes.forEach(cb => {
      ids.push(cb.value);
      const row = cb.closest('.order-row');
      names.push(row.getAttribute('data-client-name'));
    });

    if (ids.length === 0) return;
    const message = "Êtes-vous sûr de vouloir archiver ces commandes ?\n\nClients concernés :\n- " + names.join("\n- ");
    if (!confirm(message)) return;

    fetch('../../assets/actions/bulk_archive.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          order_ids: ids
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) location.reload();
        else alert("Erreur lors de l'archivage groupé.");
      });
  }

  function updateFraisCommande(orderId, nouveauTarif) {
    fetch('../../assets/actions/update_order_frais.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `order_id=${orderId}&frais=${nouveauTarif}`
    }).then(() => location.reload());
  }
</script>