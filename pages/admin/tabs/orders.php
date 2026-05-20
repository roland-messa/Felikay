<?php
$livreurs = $pdo->query("SELECT id, nom FROM users WHERE role = 'livreur' ORDER BY nom ASC")->fetchAll();

$stmt = $pdo->query("
    SELECT c.*, u.nom as client_nom, u.adresse as client_adresse, u.telephone as client_tel 
    FROM commandes c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.statut != 'archive' OR c.statut IS NULL
    ORDER BY c.created_at DESC
");
?>

<div class="bg-white rounded-3xl border border-slate-300 shadow-sm overflow-hidden">
  <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
    <div>
      <h4 class="text-xs font-bold uppercase tracking-widest text-slate-800">Gestion des Commandes</h4>
      <p class="text-[10px] text-slate-400 mt-1">Les commandes payées sont verrouillées pour garantir la traçabilité.</p>
    </div>

    <button onclick="switchTab('archives')" class="px-3 py-2 bg-slate-100 text-slate-700 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-200 transition flex items-center gap-1.5 border border-slate-200">
      Voir les Archives 📂
    </button>

    <div class="flex gap-3">
      <button id="btn-bulk-route" onclick="bulkRoute()" class="hidden px-4 py-2 bg-emerald-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-emerald-700 transition shadow-lg flex items-center gap-2">
        🗺️ Itinéraire groupé (<span id="route-count">0</span>)
      </button>
      <button id="btn-bulk-ship" onclick="bulkShip()" class="hidden px-4 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-blue-700 transition shadow-lg flex items-center gap-2">
        🚚 Expédier la sélection (<span id="ship-count">0</span>)
      </button>
      <button id="btn-bulk-archive" onclick="bulkArchive()" class="hidden px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-700 transition shadow-lg flex items-center gap-2">
        📁 Archiver (<span id="archive-count">0</span>)
      </button>
    </div>
  </div>

  <table class="w-full text-left text-sm">
    <thead class="bg-white border-b border-slate-200">
      <tr class="text-[10px] uppercase font-bold text-slate-400">
        <th class="p-6 w-10"><input type="checkbox" id="select-all" onclick="toggleSelectAll()" class="rounded"></th>
        <th class="p-6">ID / Client</th>
        <th class="p-6">Détails Livraison</th>
        <th class="p-6 text-center">Frais Livr.</th>
        <th class="p-6">Total Final</th>
        <th class="p-6 text-center">Statut</th>
        <th class="p-6 text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($c = $stmt->fetch()):
        $clientNom = htmlspecialchars($c['client_nom'] ?? 'Client Inconnu');
        $statut = $c['statut'];
        $statusColor = match ($statut) {
          'paye', 'paiement_confirmer' => 'text-green-600 bg-green-50 border-green-200',
          'expedie' => 'text-blue-600 bg-blue-50 border-blue-200',
          'annule', 'paye_annule' => 'text-red-600 bg-red-50 border-red-200',
          'livre', 'livre_payer' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
          default => 'text-orange-600 bg-orange-50 border-orange-200',
        };
      ?>
        <tr id="order-row-<?= $c['id'] ?>" class="order-row border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
          <td class="p-6">
            <input type="checkbox" name="order_ids[]" value="<?= $c['id'] ?>" onclick="updateBulkUI()" class="order-checkbox rounded border-slate-300">
          </td>
          <td class="p-6">
            <p class="font-bold text-slate-900">#ORD-<?= $c['id'] ?></p>
            <p class="text-[10px] text-blue-600 uppercase font-bold"><?= $clientNom ?></p>
          </td>
          <td class="p-6 text-[11px] font-medium text-slate-600">
            <div class="font-bold text-slate-900"><?= htmlspecialchars($c['commune'] ?? 'N/A') ?></div>
          </td>
          <td class="p-6 text-center">
            <input type="number" step="0.01" value="<?= $c['frais_livraison'] ?? 0 ?>" onchange="updateFraisCommande(<?= $c['id'] ?>, this.value)" class="w-14 p-1 text-center text-xs border rounded-lg">
          </td>
          <td class="p-6 font-bold text-slate-900"><?= number_format($c['total_ttc'], 2) ?> USD</td>
          <td class="p-6 text-center">
            <span class="px-3 py-1 rounded-full text-[9px] font-black border <?= $statusColor ?>">
              <?= strtoupper(str_replace('_', ' ', $statut ?? 'ATTENTE')) ?>
            </span>
          </td>
          <td class="p-6 text-right">
            <div class="flex flex-col gap-2 items-end">
              <select onchange="handleStatusSelection(<?= $c['id'] ?>, this.value, '<?= addslashes($clientNom) ?>')" class="status-select text-[10px] font-bold uppercase border-slate-200 rounded-lg">
                <option value="" selected disabled>Changer Statut</option>
                <option value="expedie">🚚 Expédier</option>
                <option value="annule">❌ Annuler</option>
                <option value="archive">📁 Archiver</option>
              </select>
              <select id="select-livreur-<?= $c['id'] ?>" class="hidden text-[10px] border-blue-200 bg-blue-50 rounded-lg font-bold">
                <option value="">-- Choisir Livreur --</option>
                <?php foreach ($livreurs as $l): ?>
                  <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nom']) ?></option>
                <?php endforeach; ?>
              </select>

              <a href="voir_itineraire.php?id=<?= $c['id'] ?>" target="_blank" class="mt-1 text-[10px] font-bold uppercase text-blue-600 hover:text-blue-800 bg-blue-50 px-2.5 py-1 rounded-md transition border border-blue-100 flex items-center gap-1">
                🗺️ Itinéraire
              </a>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script>
  function handleStatusSelection(orderId, newStatus, clientName) {
    const livreurSelect = document.getElementById(`select-livreur-${orderId}`);

    if (newStatus === 'expedie') {
      livreurSelect.classList.remove('hidden');
      livreurSelect.onchange = function() {
        if (this.value) {
          confirmAction(orderId, 'expedie', clientName, this.value);
        }
      };
    } else {
      livreurSelect.classList.add('hidden');
      confirmAction(orderId, newStatus, clientName);
    }
  }

  function confirmAction(orderId, status, client, livreurId = null) {
    let title = "Confirmer l'action ?";
    let text = `Voulez-vous passer la commande #ORD-${orderId} de ${client} en statut "${status.toUpperCase()}" ?`;
    let icon = 'question';

    if (status === 'annule') {
      icon = 'warning';
      title = "Annuler la commande ?";
    }
    if (status === 'archive') {
      icon = 'info';
      title = "Archiver la commande ?";
    }

    // UNIQUE ALERTE : SweetAlert2 prend le contrôle total
    Swal.fire({
      title: title,
      text: text,
      icon: icon,
      showCancelButton: true,
      confirmButtonColor: '#000000',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Confirmer',
      cancelButtonText: 'Retour',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        updateOrderStatus(orderId, status, livreurId);
      } else {
        // Remet simplement le select à zéro sans rien perturber ni recharger
        const row = document.getElementById(`order-row-${orderId}`);
        if (row) {
          const select = row.querySelector('.status-select');
          if (select) select.selectedIndex = 0;
        }
      }
    });
  }

  function updateOrderStatus(orderId, newStatus, livreurId = null) {
    let payload = `order_id=${orderId}&status=${newStatus}`;
    if (livreurId) payload += `&livreur_id=${livreurId}`;

    fetch('../../assets/actions/update_order_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: payload
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
              icon: 'success',
              title: 'Mis à jour avec succès !',
              timer: 1000,
              showConfirmButton: false
            })
            .then(() => {
              // Au lieu de recharger ou rediriger, on supprime proprement la ligne du tableau
              const row = document.getElementById(`order-row-${orderId}`);
              if (row) {
                row.style.transition = 'all 0.5s ease';
                row.style.opacity = '0';
                setTimeout(() => {
                  row.remove();
                  updateBulkUI();
                }, 500);
              }
            });
        } else {
          Swal.fire('Erreur de validation', data.message, 'error');
          // Remise à zéro du select en cas d'erreur
          const row = document.getElementById(`order-row-${orderId}`);
          if (row) {
            const select = row.querySelector('.status-select');
            if (select) select.selectedIndex = 0;
          }
        }
      })
      .catch(err => {
        Swal.fire('Erreur Critique', 'Impossible de joindre le serveur.', 'error');
      });
  }

  function bulkRoute() {
    const ids = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);
    if (ids.length > 0) {
      const idsParam = ids.join(',');
      window.open(`voir_itineraire.php?ids=${idsParam}`, '_blank');
    }
  }

  async function bulkArchive() {
    const ids = Array.from(document.querySelectorAll('.order-checkbox:checked')).map(cb => cb.value);

    const result = await Swal.fire({
      title: 'Archiver la sélection ?',
      text: `Vous allez archiver ${ids.length} commande(s).`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Oui, archiver',
      confirmButtonColor: '#0f172a'
    });

    if (result.isConfirmed) {
      fetch('../../assets/actions/bulk_archive.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          order_ids: ids
        })
      }).then(() => {
        // Supprime les lignes archivées en groupe de l'interface directement
        ids.forEach(id => {
          const row = document.getElementById(`order-row-${id}`);
          if (row) row.remove();
        });
        updateBulkUI();
        document.getElementById('select-all').checked = false;
      });
    }
  }

  function updateBulkUI() {
    const checked = document.querySelectorAll('.order-checkbox:checked').length;

    document.getElementById('btn-bulk-route').classList.toggle('hidden', checked === 0);
    document.getElementById('btn-bulk-ship').classList.toggle('hidden', checked === 0);
    document.getElementById('btn-bulk-archive').classList.toggle('hidden', checked === 0);

    document.getElementById('route-count').innerText = checked;
    document.getElementById('ship-count').innerText = checked;
    document.getElementById('archive-count').innerText = checked;
  }

  function toggleSelectAll() {
    const mainCb = document.getElementById('select-all');
    document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = mainCb.checked);
    updateBulkUI();
  }
</script>