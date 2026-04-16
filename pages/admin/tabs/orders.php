<div class="bg-white rounded-3xl border border-slate-300 shadow-sm overflow-hidden">
  <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-800">Historique des commandes</h4>
    <div class="flex gap-2">
      <span class="px-3 py-1 bg-green-50 text-green-600 rounded-full text-[9px] font-bold uppercase border border-green-200 shadow-sm">Livré / Payé</span>
      <span class="px-3 py-1 bg-orange-50 text-orange-600 rounded-full text-[9px] font-bold uppercase border border-orange-100 shadow-sm">En cours</span>
    </div>
  </div>

  <table class="w-full text-left text-sm">
    <thead class="bg-white border-b border-slate-200">
      <tr class="text-[10px] uppercase font-bold text-slate-400">
        <th class="p-6">ID / Client</th>
        <th class="p-6">Expédition (Ville & Adresse)</th>
        <th class="p-6">Date</th>
        <th class="p-6">Montant</th>
        <th class="p-6 text-center">Statut</th>
        <th class="p-6 text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      // Requête SQL avec JOIN pour récupérer le nom du client depuis la table 'users'
      $stmt = $pdo->query("
        SELECT c.*, u.nom as client_nom 
        FROM commandes c 
        LEFT JOIN users u ON c.user_id = u.id 
        ORDER BY c.created_at DESC
      ");

      while ($c = $stmt->fetch()):
        // Logique de couleur dynamique pour le badge de statut
        $statusLabel = strtoupper(str_replace('_', ' ', $c['statut'] ?? 'ATTENTE'));
        switch ($c['statut']) {
          case 'paye':
          case 'livre':
            $statusColor = 'text-green-600 bg-green-50 border-green-200';
            break;
          case 'annule':
            $statusColor = 'text-red-600 bg-red-50 border-red-200';
            break;
          default:
            $statusColor = 'text-orange-600 bg-orange-50 border-orange-200';
        }
      ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
          <td class="p-6">
            <p class="font-bold text-slate-900">#ORD-<?php echo $c['id']; ?></p>
            <p class="text-[10px] text-blue-600 uppercase font-bold tracking-tight">
              👤 <?php echo htmlspecialchars($c['client_nom'] ?? 'Client Inconnu'); ?>
            </p>
          </td>

          <td class="p-6">
            <div class="flex flex-col">
              <span class="text-slate-900 font-bold text-xs">📍 <?php echo htmlspecialchars($c['ville'] ?? 'N/A'); ?></span>
              <span class="text-[10px] text-slate-500 italic truncate w-48" title="<?php echo htmlspecialchars($c['adresse_livraison'] ?? ''); ?>">
                <?php echo htmlspecialchars($c['adresse_livraison'] ?? 'Pas d\'adresse'); ?>
              </span>
            </div>
          </td>

          <td class="p-6 text-slate-500 text-[11px] font-medium">
            <?php echo date('d/m/Y', strtotime($c['created_at'])); ?><br>
            <span class="text-[9px] text-slate-300"><?php echo date('H:i', strtotime($c['created_at'])); ?></span>
          </td>

          <td class="p-6">
            <span class="font-serif font-bold text-slate-900 text-base">
              <?php echo number_format($c['total_ttc'] ?? 0, 2, '.', ' '); ?>
            </span>
            <span class="text-[10px] font-bold text-slate-400">USD</span>
          </td>

          <td class="p-6 text-center">
            <span class="px-3 py-1 rounded-full text-[9px] font-black border <?php echo $statusColor; ?> shadow-sm">
              <?php echo $statusLabel; ?>
            </span>
          </td>

          <td class="p-6 text-right">
            <div class="flex justify-end gap-2 items-center">
              <a href="generate_invoice.php?id=<?php echo $c['id']; ?>" target="_blank"
                class="p-2 bg-slate-50 text-slate-600 rounded-xl hover:bg-black hover:text-white transition-all border border-slate-100 shadow-sm"
                title="Voir la facture">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </a>

              <select onchange="updateOrderStatus(<?php echo $c['id']; ?>, this.value)"
                class="p-1.5 text-[10px] font-bold border border-slate-200 rounded-xl bg-white outline-none focus:ring-2 focus:ring-slate-100 transition-all cursor-pointer hover:border-slate-400">
                <option value="en_attente" <?php echo ($c['statut'] == 'en_attente') ? 'selected' : ''; ?>>🕒 Attente</option>
                <option value="paye" <?php echo ($c['statut'] == 'paye') ? 'selected' : ''; ?>>✅ Payé</option>
                <option value="livre" <?php echo ($c['statut'] == 'livre') ? 'selected' : ''; ?>>📦 Livré</option>
                <option value="annule" <?php echo ($c['statut'] == 'annule') ? 'selected' : ''; ?>>❌ Annulé</option>
              </select>

              <button onclick='viewOrderDetails(<?php echo json_encode($c); ?>)'
                class="p-2 bg-slate-900 text-white rounded-xl hover:bg-blue-600 transition-all shadow-md active:scale-90">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>