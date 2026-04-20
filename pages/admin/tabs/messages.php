<?php
// On récupère les messages de contact et les emails de la newsletter
$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
$newsletters = $pdo->query("SELECT * FROM newsletter ORDER BY created_at DESC")->fetchAll();
?>

<div class="grid grid-cols-1 gap-8">
  <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
      <div>
        <h3 class="text-sm font-bold uppercase tracking-widest text-slate-800">Messages Reçus</h3>
        <p class="text-[10px] text-slate-400 mt-1">Gérez les demandes de vos clients</p>
      </div>
      <span class="px-3 py-1 bg-black text-white text-[10px] font-bold rounded-full"><?= count($messages) ?></span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-100">
            <th class="p-4">Date</th>
            <th class="p-4">Client / Contact</th>
            <th class="p-4">Sujet</th>
            <th class="p-4">Message</th>
            <th class="p-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($messages)): ?>
            <tr>
              <td colspan="5" class="p-10 text-center text-slate-400 italic">Aucun message pour le moment.</td>
            </tr>
          <?php endif; ?>

          <?php foreach ($messages as $m): ?>
            <tr class="hover:bg-slate-50/50 transition">
              <td class="p-4 text-slate-400 font-medium">
                <?= date('d/m/Y', strtotime($m['created_at'])) ?><br>
                <span class="text-[10px] text-slate-300"><?= date('H:i', strtotime($m['created_at'])) ?></span>
              </td>
              <td class="p-4">
                <div class="font-bold text-slate-900"><?= htmlspecialchars($m['nom']) ?></div>
                <a href="mailto:<?= $m['email'] ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($m['email']) ?></a>
              </td>
              <td class="p-4">
                <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded text-[9px] uppercase font-bold border border-slate-200">
                  <?= htmlspecialchars($m['sujet']) ?>
                </span>
              </td>
              <td class="p-4 text-slate-600 leading-relaxed max-w-xs">
                <p class="truncate" title="<?= htmlspecialchars($m['message']) ?>">
                  "<?= htmlspecialchars($m['message']) ?>"
                </p>
              </td>
              <td class="p-4 text-right">
                <a href="../../assets/actions/delete_message.php?id=<?= $m['id'] ?>"
                  onclick="return confirm('Supprimer ce message ?');"
                  class="inline-flex p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition border border-transparent hover:border-red-100">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
    <div class="flex justify-between items-center mb-8">
      <div>
        <h3 class="text-sm font-bold uppercase tracking-widest text-slate-800">Inscriptions Newsletter</h3>
        <p class="text-[10px] text-slate-400 mt-1">Liste des emails collectés pour le marketing</p>
      </div>
      <button class="px-4 py-2 bg-slate-100 hover:bg-black hover:text-white text-slate-600 text-[10px] font-bold rounded-xl transition uppercase tracking-tighter">
        Exporter la liste (CSV)
      </button>
    </div>

    <div class="flex flex-wrap gap-3">
      <?php if (empty($newsletters)): ?>
        <p class="text-slate-400 italic text-xs">Aucune inscription enregistrée.</p>
      <?php endif; ?>

      <?php foreach ($newsletters as $n): ?>
        <div class="group flex items-center gap-3 px-4 py-3 bg-slate-50 hover:bg-white rounded-2xl border border-slate-100 hover:border-black hover:shadow-md transition-all duration-300">
          <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-500 group-hover:bg-black group-hover:text-white transition-colors">
            @
          </div>
          <div>
            <span class="text-[11px] font-bold text-slate-800 block"><?= htmlspecialchars($n['email']) ?></span>
            <span class="text-[9px] text-slate-400 uppercase font-medium italic">Inscrit le <?= date('d/m/Y', strtotime($n['created_at'])) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>