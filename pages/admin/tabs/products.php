<div class="bg-gray-100 rounded-3xl border border-slate-300 shadow-sm p-8 mb-10">
  <h4 class="font-serif italic text-xl mb-6">Ajouter un nouvel article</h4>


  <div id="notification" class="hidden fixed top-5 right-5 z-[200] p-4 rounded-xl shadow-2xl border transition-all duration-500 transform translate-x-20 opacity-0">
    <div class="flex items-center gap-3">
      <div id="notif-icon" class="w-8 h-8 rounded-full flex items-center justify-center"></div>
      <p id="notif-text" class="text-sm font-bold uppercase tracking-wide"></p>
    </div>
  </div>




  <form id="productForm" action="../../assets/actions/process_article.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-8">

    <div class="space-y-4">
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Nom du produit</label>
        <input type="text" name="nom" required class="w-full p-3 bg-white rounded-xl border border-black text-sm focus:ring-2 focus:ring-slate-200 outline-none transition-all" placeholder="Ex: Robe de soirée">
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Catalogue Principal</label>
        <select name="categorie_id" id="main_cat" onchange="updateSubFields()" class="w-full p-3 bg-white rounded-xl border border-black text-sm cursor-pointer font-medium">
          <option value="1">Homme</option>
          <option value="2">Femme</option>
          <option value="3">Enfants (Vêtements)</option>
          <option value="6">Enfants (Chaussures)</option>
          <option value="8">Déco (Maison)</option>
          <option value="9">Gadgets (Cuisine/Élec)</option>
          <option value="7">Accessoires Mode</option>
        </select>
      </div>

      <div class="p-4 bg-white rounded-2xl border border-black shadow-sm">
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-3 text-center">Couleurs disponibles</label>
        <div class="flex justify-center items-center gap-3 mb-3">
          <input type="color" id="colorPicker" value="#000000" class="w-12 h-10 border border-black rounded-lg cursor-pointer">
          <button type="button" onclick="addColor()" class="px-4 py-2 bg-black text-white text-[10px] uppercase rounded-lg hover:bg-zinc-800 transition-colors">+ Ajouter</button>
        </div>
        <div id="colorsList" class="flex flex-wrap gap-2 justify-center min-h-[30px]"></div>
        <input type="hidden" name="colors" id="colorsInput">
      </div>
    </div>

    <div class="space-y-4">
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Taille / Dimension</label>
        <input type="text" name="taille_nom" id="input_taille" placeholder="Ex: XL, 42, 120x60..." class="w-full p-3 bg-white rounded-xl border border-black text-sm uppercase font-bold outline-none" required>
      </div>

      <div id="dynamic_fields" class="p-4 bg-slate-50 rounded-2xl border border-black space-y-3">
        <label class="text-[10px] uppercase font-bold text-black block">Classification Automatique</label>

        <select name="genre" id="select_genre" class="w-full p-2.5 bg-white rounded-lg border border-slate-300 text-xs outline-none">
          <option value="femme">Fille / Femme</option>
          <option value="homme">Garçon / Homme</option>
          <option value="unisexe">Mixte / Autre</option>
        </select>

        <select name="tranche_age" id="select_age" class="w-full p-2.5 bg-white rounded-lg border border-slate-300 text-xs outline-none">
        </select>

        <select name="type_accessoire" id="select_sub_type" class="w-full p-2.5 bg-white rounded-lg border border-slate-300 text-xs outline-none">
        </select>
      </div>

      <div>
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Prix & Stock</label>
        <div class="flex gap-2">
          <input type="number" step="0.01" name="prix" required class="flex-1 p-3 bg-white rounded-xl border border-black text-sm" placeholder="Prix">
          <select name="devise" class="w-24 p-3 bg-black text-white rounded-xl text-xs font-bold">
            <option value="USD">$ USD</option>
            <option value="CDF">FC</option>
          </select>
        </div>
        <input type="number" name="stock_total" value="1" min="1" class="w-full mt-3 p-3 bg-white rounded-xl border border-black text-sm" placeholder="Quantité en stock">
      </div>
    </div>

    <div class="space-y-4">
      <div>
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Image Principale (Face) *</label>
        <div class="relative group">
          <input type="file" name="image_principale" required class="text-[10px] w-full p-4 border-2 border-dashed border-slate-300 rounded-xl bg-white cursor-pointer hover:border-black transition-colors">
        </div>
      </div>

      <div class="p-4 bg-white rounded-2xl border border-black shadow-sm space-y-3">
        <label class="text-[10px] uppercase font-bold text-slate-800 block mb-1">Vues complémentaires (Optionnel)</label>

        <div class="flex flex-col gap-2">
          <div class="flex items-center justify-between">
            <span class="text-[8px] uppercase font-bold text-slate-400">Dos</span>
            <input type="file" name="image_dos" class="text-[9px] w-2/3 p-1 border border-slate-200 rounded-lg bg-slate-50 cursor-pointer">
          </div>

          <div class="flex items-center justify-between">
            <span class="text-[8px] uppercase font-bold text-slate-400">Gauche</span>
            <input type="file" name="image_gauche" class="text-[9px] w-2/3 p-1 border border-slate-200 rounded-lg bg-slate-50 cursor-pointer">
          </div>

          <div class="flex items-center justify-between">
            <span class="text-[8px] uppercase font-bold text-slate-400">Droite</span>
            <input type="file" name="image_droite" class="text-[9px] w-2/3 p-1 border border-slate-200 rounded-lg bg-slate-50 cursor-pointer">
          </div>
        </div>
      </div>

      <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
        <h5 class="text-[9px] uppercase font-black mb-3 tracking-widest text-slate-500">Options d'affichage</h5>
        <div class="grid grid-cols-2 gap-2">
          <label class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-100 text-[9px] font-bold uppercase cursor-pointer hover:bg-slate-50">
            <input type="checkbox" name="is_new" value="1" class="accent-black"> Nouveauté
          </label>
          <label class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-100 text-[9px] font-bold uppercase cursor-pointer hover:bg-slate-50">
            <input type="checkbox" name="actif_accueil" value="1" class="accent-black"> Accueil
          </label>
        </div>
        <input type="text" name="promo_tag" placeholder="Badge (ex: -20% ou SOLDE)" class="w-full mt-3 p-2 text-xs rounded-lg border border-slate-300 outline-none focus:border-black">
      </div>

      <button type="submit" class="w-full bg-black text-white p-5 rounded-2xl font-bold text-[11px] uppercase tracking-[0.2em] shadow-lg hover:bg-zinc-800 hover:-translate-y-1 transition-all duration-300">
        Enregistrer le produit
      </button>
    </div>
  </form>
</div>

<script>
  // GESTION DES COULEURS
  let colors = [];

  function addColor() {
    const cp = document.getElementById('colorPicker');
    const colorValue = cp.value.toUpperCase();
    if (!colors.includes(colorValue)) {
      colors.push(colorValue);
      renderColors();
    }
  }

  function renderColors() {
    const list = document.getElementById('colorsList');
    const input = document.getElementById('colorsInput');
    list.innerHTML = '';
    colors.forEach((c, index) => {
      const colDiv = document.createElement('div');
      colDiv.className = "w-7 h-7 rounded-full border border-slate-300 relative group animate-popIn";
      colDiv.style.backgroundColor = c;
      colDiv.innerHTML = `<button type="button" onclick="removeColor(${index})" class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 text-[8px] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">x</button>`;
      list.appendChild(colDiv);
    });
    input.value = colors.join(',');
  }

  function removeColor(i) {
    colors.splice(i, 1);
    renderColors();
  }

  function updateSubFields() {

    const cat = document.getElementById('main_cat').value;

    const selectGenre = document.getElementById('select_genre');
    const selectAge = document.getElementById('select_age');
    const selectSubType = document.getElementById('select_sub_type');

    // RESET
    selectGenre.innerHTML = '';
    selectAge.innerHTML = '';
    selectSubType.innerHTML = '';

    // =========================
    // HOMME
    // =========================
    if (cat == "1") {

      selectGenre.innerHTML = `
      <option value="homme">Homme</option>
      <option value="unisexe">Mixte / Autre</option>
    `;

      selectAge.innerHTML = `
      <option value="adulte">Adulte</option>
    `;

      selectSubType.innerHTML = `
      <option value="quotidien">Quotidien</option>
      <option value="evenement">Évènements</option>
      <option value="soir">Soir</option>
      <option value="sport">Sport</option>
    `;

    }

    // =========================
    // FEMME
    // =========================
    else if (cat == "2") {

      selectGenre.innerHTML = `
      <option value="femme">Femme</option>
      <option value="unisexe">Mixte / Autre</option>
    `;

      selectAge.innerHTML = `
      <option value="adulte">Adulte</option>
    `;

      selectSubType.innerHTML = `
      <option value="quotidien">Quotidien</option>
      <option value="evenement">Évènements</option>
      <option value="soir">Soir</option>
      <option value="luxe">Luxe</option>
    `;

    }

    // =========================
    // ENFANTS
    // =========================
    else if (cat == "3" || cat == "6") {

      selectGenre.innerHTML = `
      <option value="fille">Fille</option>
      <option value="garcon">Garçon</option>
      <option value="unisexe">Mixte</option>
    `;

      selectAge.innerHTML = `
      <option value="0-5 ans">Nourrissons (0-5 ans)</option>
      <option value="6-14 ans">Enfants (6-14 ans)</option>
      <option value="14-18 ans">Ados (14-18 ans)</option>
    `;

      selectSubType.innerHTML = `
      <option value="quotidien">Quotidien</option>
      <option value="ecole">École</option>
      <option value="evenement">Évènements</option>
      <option value="sport">Sport</option>
    `;

    }

    // =========================
    // DÉCO
    // =========================
    else if (cat == "8") {

      selectGenre.innerHTML = `
      <option value="maison">Maison</option>
    `;

      selectAge.innerHTML = `
      <option value="maison">Maison / Déco intérieure</option>
    `;

      selectSubType.innerHTML = `
      <option value="salon">Salon</option>
      <option value="chambre">Chambre</option>
      <option value="decoration">Décoration</option>
    `;

    }

    // =========================
    // GADGETS
    // =========================
    else if (cat == "9") {

      selectGenre.innerHTML = `
      <option value="mixte">Mixte</option>
    `;

      selectAge.innerHTML = `
      <option value="divers">Gadgets divers</option>
    `;

      selectSubType.innerHTML = `
      <option value="cuisine">Cuisine</option>
      <option value="electroniques">Électroniques</option>
      <option value="maison">Maison</option>
    `;

    }

    // =========================
    // AUTRES
    // =========================
    else {

      selectGenre.innerHTML = `
      <option value="unisexe">Mixte / Autre</option>
    `;

      selectAge.innerHTML = `
      <option value="adulte">Adulte</option>
    `;

      selectSubType.innerHTML = `
      <option value="standard">Standard</option>
    `;
    }
  }

  // FONCTION DE NOTIFICATION (Thème Noir & Blanc)
  function showNotification(message, type) {
    const notif = document.getElementById('notification');
    const text = document.getElementById('notif-text');
    const icon = document.getElementById('notif-icon');

    text.innerText = message;

    // Reset classes de base
    notif.className = "fixed top-5 right-5 z-[200] p-4 rounded-xl shadow-2xl border flex items-center gap-3 transform transition-all duration-500 opacity-100 translate-x-0";

    if (type === 'success') {
      // Style Noir pour le succès
      notif.classList.add('bg-black', 'text-white', 'border-zinc-800');
      icon.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5"></i>';
      icon.className = "w-8 h-8 bg-zinc-800 text-white rounded-full flex items-center justify-center";
    } else {
      // Style Blanc/Gris pour l'erreur
      notif.classList.add('bg-white', 'text-zinc-900', 'border-zinc-200');
      icon.innerHTML = '<i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>';
      icon.className = "w-8 h-8 bg-zinc-100 rounded-full flex items-center justify-center";
    }

    if (typeof lucide !== 'undefined') lucide.createIcons();

    setTimeout(() => {
      notif.classList.add('opacity-0', 'translate-x-20');
      setTimeout(() => notif.classList.add('hidden'), 500);
    }, 4000);
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateSubFields();

    const form = document.getElementById('productForm');
    const inputs = form.querySelectorAll('input, select');

    // Restoration du brouillon
    inputs.forEach(input => {
      if (!input.name) return;
      const saved = localStorage.getItem('draft_' + input.name);
      if (saved !== null) {
        if (input.type === 'checkbox') input.checked = saved === 'true';
        else input.value = saved;
      }
      input.addEventListener('input', () => {
        const valueToStore = input.type === 'checkbox' ? input.checked : input.value;
        localStorage.setItem('draft_' + input.name, valueToStore);
      });
    });

    const catSelect = document.getElementById('main_cat');
    catSelect.addEventListener('change', updateSubFields);

    // ENVOI DU FORMULAIRE VIA AJAX
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerText;

      submitBtn.disabled = true;
      submitBtn.innerText = "ENCOURS...";

      fetch(this.action, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotification(data.message, 'success');
            form.reset();
            colors = [];
            renderColors();
            inputs.forEach(input => localStorage.removeItem('draft_' + input.name));
            updateSubFields();
          } else {
            showNotification(data.message, 'error');
          }
        })
        .catch(error => {
          console.error('Erreur:', error);
          showNotification("Problème de connexion au serveur", 'error');
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerText = originalText;
        });
    });
  });
</script>

<style>
  @keyframes popIn {
    0% {
      transform: scale(0.5);
      opacity: 0;
    }

    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  .animate-popIn {
    animation: popIn 0.2s ease-out forwards;
  }
</style>