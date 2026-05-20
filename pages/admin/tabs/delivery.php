<div class="space-y-10">

  <div class="flex justify-between items-center">
    <div>
      <h2 class="text-xl font-serif italic font-bold text-slate-800">Gestion des Livreurs</h2>
      <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Effectifs et nouveaux enregistrements</p>
    </div>
    <button onclick="document.getElementById('modal-livreur').classList.remove('hidden')" class="bg-black text-white px-5 py-2.5 rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-slate-800 transition shadow-lg flex items-center gap-2">
      <span class="text-sm">+</span> Nouveau Livreur
    </button>
  </div>

  <div id="modal-livreur" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md border border-slate-100">
      <div class="mb-6">
        <h3 class="font-bold text-slate-800 uppercase text-xs tracking-widest">Enregistrer un nouveau coursier</h3>
        <div class="h-1 w-10 bg-black mt-2"></div>
      </div>

      <div id="ajax-msg" class="hidden mb-4 p-3 rounded-xl text-[10px] font-bold uppercase tracking-wider"></div>

      <form id="form-add-livreur" class="space-y-4">
        <div>
          <label class="text-[10px] uppercase font-bold text-slate-400 ml-1">Nom Complet</label>
          <input type="text" name="nom" placeholder="Ex: Jean Livreur" class="w-full mt-1 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm outline-none focus:bg-white focus:ring-2 focus:ring-black/5 transition-all" required>
        </div>

        <div>
          <label class="text-[10px] uppercase font-bold text-slate-400 ml-1">Téléphone</label>
          <input type="text" name="telephone" placeholder="+243..." class="w-full mt-1 p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm outline-none focus:bg-white focus:ring-2 focus:ring-black/5 transition-all">
        </div>

        <hr class="border-slate-100 my-2">

        <div>
          <label class="text-[10px] uppercase font-bold text-blue-600 ml-1">Email de connexion</label>
          <input type="email" name="email" placeholder="livreur@felykay.com" class="w-full mt-1 p-3.5 bg-blue-50/30 border border-blue-100 rounded-2xl text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all" required>
        </div>

        <div>
          <label class="text-[10px] uppercase font-bold text-blue-600 ml-1">Mot de passe provisoire</label>
          <input type="password" name="password" placeholder="••••••••" class="w-full mt-1 p-3.5 bg-blue-50/30 border border-blue-100 rounded-2xl text-sm outline-none focus:bg-white focus:ring-2 focus:ring-blue-500/20 transition-all" required>
        </div>

        <div class="flex gap-3 pt-4">
          <button type="button" onclick="closeLivreurModal()" class="flex-1 px-4 py-3 border border-slate-200 rounded-2xl text-[10px] font-bold uppercase hover:bg-slate-50 transition">
            Annuler
          </button>
          <button type="button" onclick="submitLivreurForm()" class="flex-1 px-4 py-3 bg-black text-white rounded-2xl text-[10px] font-bold uppercase hover:shadow-xl transition">
            Confirmer
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="p-6 border-b border-slate-100 bg-slate-50/30 flex justify-between items-center">
      <h3 class="font-bold text-slate-800 uppercase text-xs tracking-widest">Livreurs Enregistrés</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-slate-50">
          <tr>
            <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Nom & Contact</th>
            <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Compte Email</th>
            <th class="p-4 text-[10px] uppercase font-bold text-slate-500 text-center">Accès</th>
            <th class="p-4 text-[10px] uppercase font-bold text-slate-500 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php
          // CORRECTION : On récupère les livreurs depuis 'users' car 'livreurs' est vide
          $allLivreurs = $pdo->query("SELECT id, nom, email, telephone, 'actif' as statut FROM users WHERE role = 'livreur' ORDER BY nom ASC")->fetchAll();

          foreach ($allLivreurs as $l):
          ?>
            <tr class="hover:bg-slate-50/50 transition-colors">
              <td class="p-4">
                <div class="font-bold text-slate-900 text-xs"><?= htmlspecialchars($l['nom']) ?></div>
                <div class="text-[9px] text-slate-400 font-medium"><?= htmlspecialchars($l['telephone'] ?? 'SANS CONTACT') ?></div>
              </td>
              <td class="p-4 text-xs text-blue-600 font-medium italic"><?= htmlspecialchars($l['email']) ?></td>
              <td class="p-4 text-center">
                <span class="px-2 py-0.5 rounded-full text-[8px] font-black bg-green-100 text-green-700">
                  ACTIF
                </span>
              </td>
              <td class="p-4 text-right">
                <div class="flex justify-end gap-2">
                  <a href="../../assets/actions/toggle_livreur.php?id=<?= $l['id'] ?>" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg transition" title="Bloquer">
                    🚫
                  </a>
                  <a href="../../assets/actions/delete_livreur.php?id=<?= $l['id'] ?>" onclick="return confirm('Supprimer définitivement ce livreur ?')" class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition">
                    🗑️
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>

          <?php if (empty($allLivreurs)): ?>
            <tr>
              <td colspan="4" class="p-10 text-center text-xs text-slate-400 uppercase tracking-widest">Aucun livreur trouvé</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
      <h3 class="font-bold text-slate-800 uppercase text-xs tracking-widest">Journal des Livraisons</h3>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead class="bg-slate-50/50">
          <tr>
            <th class="p-4 text-[10px] uppercase font-bold text-slate-500">ID</th>
            <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Client</th>
            <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Livreur</th>
            <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Montant</th>
            <th class="p-4 text-[10px] uppercase font-bold text-slate-500">Date/Heure</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (isset($historiqueLivraisons) && !empty($historiqueLivraisons)): ?>
            <?php foreach ($historiqueLivraisons as $h): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="p-4 text-xs font-bold text-slate-400">#<?= $h['id'] ?></td>
                <td class="p-4 text-xs uppercase font-semibold text-slate-700">
                  <div class="flex items-center gap-2">
                    <span><?= htmlspecialchars($h['nom_complet']) ?></span>
                    <a href="voir_itineraire.php?id=<?= $h['id'] ?>" target="_blank" title="Revoir l'itinéraire" class="text-xs hover:scale-110 transition">
                      🗺️
                    </a>
                  </div>
                </td>
                <td class="p-4">
                  <span class="text-[10px] bg-slate-900 text-white px-2.5 py-1 rounded-lg italic font-medium">
                    @<?= htmlspecialchars($h['nom_livreur']) ?>
                  </span>
                </td>
                <td class="p-4 text-xs font-serif font-black italic text-slate-900"><?= number_format($h['total_ttc'], 2) ?> $</td>
                <td class="p-4 text-[10px] text-slate-400"><?= date('d/m/Y H:i', strtotime($h['updated_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="p-10 text-center text-xs text-slate-400 uppercase tracking-widest">Aucune livraison enregistrée</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
  function closeLivreurModal() {
    document.getElementById('modal-livreur').classList.add('hidden');
    document.getElementById('ajax-msg').classList.add('hidden');
    document.getElementById('form-add-livreur').reset();
  }

  function submitLivreurForm() {
    const form = document.getElementById('form-add-livreur');
    const msgDiv = document.getElementById('ajax-msg');
    const formData = new FormData(form);

    fetch('../../assets/actions/add_livreur.php', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        msgDiv.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
        msgDiv.innerText = data.message;
        msgDiv.classList.remove('hidden');

        if (data.success) {
          msgDiv.classList.add('bg-green-100', 'text-green-700');
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          msgDiv.classList.add('bg-red-100', 'text-red-700');
        }
      })
      .catch(e => {
        alert("Erreur de connexion au serveur.");
      });
  }
</script>