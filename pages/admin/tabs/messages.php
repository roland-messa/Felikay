<?php
// Récupération des messages de contact
$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();

// Récupération des deux types de newsletters
$newsletters_simple = $pdo->query("SELECT * FROM newsletter ORDER BY created_at DESC")->fetchAll();
$newsletters_felykay = $pdo->query("SELECT * FROM newsletter_felykay ORDER BY date_inscription DESC")->fetchAll();
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
                <p class="truncate" title="<?= htmlspecialchars($m['message']) ?>">"<?= htmlspecialchars($m['message']) ?>"</p>
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
        <p class="text-[10px] text-slate-400 mt-1">Collecte d'emails et contacts Felykay</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div>
        <h4 class="text-[10px] uppercase font-bold text-slate-400 mb-4 tracking-widest">Emails (Simple)</h4>
        <div class="flex flex-wrap gap-3">
          <?php foreach ($newsletters_simple as $ns): ?>
            <div class="group flex items-center gap-2 px-4 py-2 bg-slate-50 rounded-2xl border border-slate-100">
              <span class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-black transition-colors"></span>
              <span class="text-[11px] font-medium text-slate-600"><?= htmlspecialchars($ns['email']) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <h4 class="text-[10px] uppercase font-bold text-slate-400 mb-4 tracking-widest">Contacts (Felykay)</h4>
        <div class="grid grid-cols-1 gap-3">
          <?php foreach ($newsletters_felykay as $nf): ?>
            <?php
            $wa_number = str_replace('+', '', $nf['telephone']); // Nettoyage pour wa.me
            $wa_message = urlencode("Bonjour " . $nf['nom_complet'] . ", l'équipe Felikay a bien reçu votre inscription !");
            ?>
            <div class="flex justify-between items-center p-4 bg-stone-50 rounded-2xl border border-stone-100 hover:border-green-500/50 transition-all group">
              <div>
                <span class="text-[11px] font-bold text-slate-900 block"><?= htmlspecialchars($nf['nom_complet']) ?></span>
                <span class="text-[10px] text-slate-500 font-mono italic"><?= htmlspecialchars($nf['telephone']) ?></span>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-[9px] text-slate-300 font-medium"><?= date('d/m', strtotime($nf['date_inscription'])) ?></span>
                <a href="https://wa.me/<?= $wa_number ?>?text=<?= $wa_message ?>" target="_blank"
                  class="p-2 bg-white text-green-600 border border-green-100 rounded-xl hover:bg-green-600 hover:text-white transition-all shadow-sm">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                  </svg>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>