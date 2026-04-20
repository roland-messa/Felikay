<div class="bg-gray-100 rounded-3xl border border-slate-300 shadow-sm p-8 mb-10">
  <h4 class="font-serif italic text-xl mb-6">Ajouter un nouvel article</h4>

  <form action="../../assets/actions/process_article.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="space-y-4">
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Nom du produit</label>
        <input type="text" name="nom" required class="w-full p-3 bg-white rounded-xl border border-black text-sm focus:ring-2 focus:ring-slate-200 outline-none transition-all" placeholder="Ex: Robe de soirée">
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Catégorie</label>
        <select name="categorie_id" id="main_cat" onchange="toggleEnfantFields()" class="w-full p-3 bg-white rounded-xl border border-black text-sm cursor-pointer">
          <option value="1">Homme</option>
          <option value="2">Femme</option>
          <option value="3">Enfants</option>
          <option value="4">Chaussures Hommes</option>
          <option value="5">Chaussures Femmes</option>
          <option value="6">Chaussures Enfants</option>
          <option value="7">Accessoires</option>
        </select>
      </div>

      <div class="p-4 bg-white rounded-2xl border border-black shadow-sm">
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-3 text-center">Choisir les couleurs</label>
        <div class="flex justify-center mb-3">
          <input type="color" id="colorPicker" value="#000000" class="w-16 h-10 border border-black rounded-lg cursor-pointer">
        </div>
        <div class="text-center mb-3">
          <button type="button" onclick="addColor()" class="px-4 py-2 bg-black text-white text-[10px] uppercase rounded-lg">+ Ajouter</button>
        </div>
        <div id="colorsList" class="flex flex-wrap gap-2 justify-center"></div>
        <input type="hidden" name="colors" id="colorsInput">
      </div>
    </div>

    <div class="space-y-4">
      <div>
        <label id="label_taille" class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Taille de l'article</label>
        <input type="text" name="taille_nom" id="input_taille" placeholder="Ex: XL, 42, L..." class="w-full p-3 bg-white rounded-xl border border-black text-sm uppercase font-bold outline-none focus:bg-slate-50 transition-all" required>
      </div>

      <div id="fields_enfant" class="p-4 bg-blue-50/50 rounded-2xl border border-blue-200 space-y-3">
        <label class="text-[10px] uppercase font-bold text-blue-600 block">Détails de l'article</label>
        <div class="flex gap-2">
          <select name="genre" class="flex-1 p-2.5 bg-white rounded-lg border border-blue-300 text-xs outline-none">
            <option value="homme">Homme / Garçon</option>
            <option value="femme">Femme / Fille</option>
            <option value="unisexe">Mixte</option>
          </select>
          <select name="tranche_age" id="select_age" class="flex-1 p-2.5 bg-white rounded-lg border border-blue-300 text-xs outline-none">
            <option value="adulte" selected>Adulte</option>
            <option value="Enfant (1-18 ans)">Enfant (1-18 ans)</option>
            <option value="Bébé (0-12 mois)">Bébé (0-12 mois)</option>
          </select>
        </div>
      </div>

      <div id="fields_accessoire" class="hidden p-4 bg-orange-50/50 rounded-2xl border border-orange-200">
        <label class="text-[10px] uppercase font-bold text-orange-600 block mb-2">Type Accessoire</label>
        <select name="type_accessoire" class="w-full p-2.5 bg-white rounded-lg border border-orange-300 text-sm outline-none">
          <option value="electronique">Électronique</option>
          <option value="perruque">Perruque</option>
          <option value="parfum">Parfum</option>
          <option value="autre">Autre (préciser dans la description)</option>
        </select>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Prix et Devise</label>
        <div class="flex gap-2">
          <input type="number" step="0.01" name="prix" required class="flex-1 p-3 bg-white rounded-xl border border-black text-sm outline-none" placeholder="0.00">
          <select name="devise" class="w-28 p-3 bg-black text-white rounded-xl border border-black text-xs font-bold cursor-pointer">
            <option value="USD">USD ($)</option>
            <option value="CDF">CDF (FC)</option>
          </select>
        </div>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Stock Initial</label>
        <input type="number" name="stock_total" value="1" class="w-full p-3 bg-white rounded-xl border border-black text-sm outline-none">
      </div>
    </div>

    <div class="space-y-4 flex flex-col">
      <div class="flex-1">
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Description</label>
        <textarea name="description" class="w-full p-3 bg-white rounded-xl border border-black text-sm h-32 resize-none outline-none focus:bg-slate-50 transition-all" placeholder="Détails du produit..."></textarea>
      </div>

      <div class="mt-4">
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Image Principale</label>
        <input type="file" name="image_principale" required class="text-[10px] w-full p-4 border-2 border-dashed border-slate-300 rounded-xl hover:border-black transition-colors cursor-pointer bg-white">
      </div>

      <button type="submit" class="w-full mt-6 bg-black text-white p-4 rounded-2xl font-bold text-[11px] uppercase tracking-[0.2em] shadow-lg hover:bg-zinc-800 hover:scale-[1.02] active:scale-95 transition-all">
        Enregistrer le produit
      </button>
    </div>
  </form>
</div>


<div class="bg-white rounded-3xl border border-slate-300 shadow-sm overflow-hidden">
  <table class="w-full text-left text-sm">
    <thead class="bg-slate-50/50 border-b border-slate-100">
      <tr class="text-[10px] uppercase font-bold text-slate-800">
        <th class="p-6">Produit</th>
        <th class="p-6 text-center">Stock</th>
        <th class="p-6">Prix</th>
        <th class="p-6">Couleurs</th>
        <th class="p-6 text-right">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($produits as $p): ?>
        <tr class="border-b border-slate-50 hover:bg-slate-50/30 transition">
          <td class="p-6 flex items-center gap-4">
            <img src="/ProjetFelykay/<?php echo trim($p['image_principale'], './'); ?>"
              class="w-12 h-14 rounded-lg object-cover shadow-sm border border-slate-100"
              onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">

            <div>
              <p class="font-bold text-slate-900"><?php echo htmlspecialchars($p['nom']); ?></p>
              <div class="flex flex-wrap items-center gap-2 mt-0.5">

                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">
                  <?php
                  $cat_id  = (int)$p['categorie_id'];
                  $cat_nom = $p['cat_nom'] ?? 'Article';
                  $genre   = !empty($p['genre']) ? $p['genre'] : '';
                  $age     = !empty($p['tranche_age']) ? $p['tranche_age'] : '';
                  $type_acc = !empty($p['type_accessoire']) ? $p['type_accessoire'] : '';

                  // Nettoyage du nom de la catégorie (ex: "Habits Hommes" devient "Habits")
                  $display_cat = str_ireplace([' Hommes', ' Femmes', ' Enfants', ' Homme', ' Femme', ' Enfant'], '', $cat_nom);
                  echo htmlspecialchars($display_cat);

                  // LOGIQUE ACCESSOIRES : ID 7 selon la DB
                  if ($cat_id === 7 && !empty($type_acc)) {
                    echo " • " . htmlspecialchars(str_replace('_', ' ', $type_acc));
                  } else {
                    // Sinon affichage classique Genre / Âge
                    if (!empty($age) && strtolower($age) !== 'adulte') {
                      echo " " . htmlspecialchars($age);
                    }
                    if (!empty($genre)) {
                      echo " • " . htmlspecialchars($genre);
                    }
                  }
                  ?>
                </p>

                <?php if (!empty($p['les_tailles'])): ?>
                  <span class="text-[9px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-bold uppercase">
                    <?php
                    // On affiche PT (Pointure) pour les Chaussures (IDs 4, 5, 6)
                    echo (in_array($cat_id, [4, 5, 6])) ? 'PT: ' : 'T: ';
                    echo htmlspecialchars(str_replace(',', ' | ', $p['les_tailles']));
                    ?>
                  </span>
                <?php endif; ?>

              </div>
            </div>
          </td>

          <td class="p-6 text-center">
            <?php $stock = (int)($p['stock_total'] ?? 0); ?>
            <span class="px-3 py-1 rounded-lg <?php echo ($stock < 5) ? 'text-red-600 font-bold bg-red-50' : 'text-slate-600 bg-slate-100'; ?>">
              <?php echo $stock; ?>
            </span>
          </td>

          <td class="p-6 font-serif">
            <span class="font-bold text-slate-900"><?php echo number_format($p['prix'], 2, '.', ' '); ?></span>
            <span class="text-[10px] font-bold ml-1 text-slate-500"><?php echo htmlspecialchars($p['devise'] ?? 'USD'); ?></span>
          </td>

          <td class="p-6">
            <div class="flex gap-1.5 flex-wrap">
              <?php
              if (!empty($p['les_couleurs'])) {
                $colors = explode(',', $p['les_couleurs']);
                foreach ($colors as $hex) {
                  echo '<div class="w-4 h-4 rounded-full border border-slate-200 shadow-sm" style="background-color: ' . htmlspecialchars(trim($hex)) . '"></div>';
                }
              } else {
                echo '<span class="text-[10px] text-slate-300 italic">Aucune</span>';
              }
              ?>
            </div>
          </td>

          <td class="p-6 text-right">
            <div class="flex justify-end gap-2">
              <a href="../edit_article.php?id=<?php echo $p['id']; ?>" class="p-2 hover:bg-slate-100 rounded-lg text-slate-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
              </a>
              <a href="../../assets/actions/delete_article.php?id=<?php echo $p['id']; ?>" onclick="return confirm('Supprimer cet article ?')" class="p-2 hover:bg-red-50 rounded-lg text-slate-400 hover:text-red-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<script>
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (typeof toggleEnfantFields === 'function') {
      toggleEnfantFields();
    }
  });
</script>