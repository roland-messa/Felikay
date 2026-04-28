// --- 1. GESTION DES FORMULAIRES PRODUITS ---

function toggleEnfantFields() {
  const mainCat = document.getElementById('main_cat').value;
  const enfantDiv = document.getElementById('fields_enfant');
  const accessoireDiv = document.getElementById('fields_accessoire');
  const labelTaille = document.getElementById('label_taille');
  const inputTaille = document.getElementById('input_taille');
  const labelDetail = document.getElementById('label_detail_dynamique');
  const containerTaille = inputTaille ? inputTaille.parentElement : null;

  if (!enfantDiv || !accessoireDiv || !labelTaille || !inputTaille) return;

  // --- RÉINITIALISATION PAR DÉFAUT (HABITS) ---
  enfantDiv.classList.remove('hidden');
  accessoireDiv.classList.add('hidden');
  if (containerTaille) containerTaille.classList.remove('hidden');

  inputTaille.required = true;
  labelTaille.innerText = "Taille de l'article";
  inputTaille.placeholder = "Ex: S, M, XL, L...";
  if (labelDetail) labelDetail.innerText = "Détails de l'article";

  // --- LOGIQUE SPÉCIFIQUE SELON LA DB ---

  // CHAUSSURES (IDs 4, 5, 6 selon votre table categories)
  if (mainCat == "4" || mainCat == "5" || mainCat == "6") {
    if (labelDetail) labelDetail.innerText = "Genre pour la chaussure";
    labelTaille.innerText = "Pointure de la chaussure";
    inputTaille.placeholder = "Ex: 38, 42, 44...";
  }

  // ACCESSOIRES (ID 7 selon votre table categories)
  else if (mainCat == "7") {
    enfantDiv.classList.add('hidden');
    accessoireDiv.classList.remove('hidden');

    if (containerTaille) {
      containerTaille.classList.add('hidden');
      inputTaille.required = false;
    }
    if (labelDetail) labelDetail.innerText = "Type d'accessoire";
  }

  // ENFANTS (ID 3)
  else if (mainCat == "3") {
    if (labelDetail) labelDetail.innerText = "Détails Enfant (Genre & Âge)";
    labelTaille.innerText = "Taille de l'habit";
    inputTaille.placeholder = "Ex: 2-4 ans, 12m...";
  }
}


// --- 2. NAVIGATION & TABS ---

function openModal() {
  switchTab('products');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function switchTab(id) {
  const titles = {
    'overview': 'Dashboard',
    'products': 'Catalogue Articles',
    'orders': 'Gestion Commandes',
    'analytics': 'Vues & Audience',
    'settings': 'Paramètres'
  };

  const titleEl = document.getElementById('current-title');
  if (titleEl) titleEl.innerText = titles[id] || 'Admin';

  document.querySelectorAll('.tab-content').forEach(el => {
    el.classList.add('hidden');
    el.classList.remove('block');
  });

  const target = document.getElementById('section-' + id);
  if (target) {
    target.classList.remove('hidden');
    target.classList.add('block');
  }

  document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
  if (window.event && window.event.currentTarget) {
    window.event.currentTarget.classList.add('active');
  }

}

// --- 3. GESTION DES COMMANDES (AJAX) ---

function updateOrderStatus(orderId, newStatus) {
  if (!confirm(`Changer le statut de la commande #${orderId} ?`)) return;

  fetch('../assets/actions/update_order_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${orderId}&statut=${newStatus}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert("Statut mis à jour !");
        location.reload();
      } else {
        alert("Erreur : " + data.message);
      }
    })
    .catch(err => console.error('Erreur:', err));
}

function viewOrderDetails(order) {
  const modal = document.getElementById('orderModal');
  if (!modal) return;

  document.getElementById('modalOrderId').innerText = `Commande #ORD-${order.id}`;
  document.getElementById('modalOrderCustomer').innerText = `Client : ${order.client_nom}`;
  document.getElementById('modalOrderAddress').innerText = `${order.adresse_livraison} (${order.ville})`;
  document.getElementById('modalOrderTotal').innerText = `${order.total_ttc} $`;

  const itemsList = document.getElementById('orderItemsList');
  itemsList.innerHTML = '<p class="text-center italic animate-pulse">Chargement...</p>';

  fetch(`../assets/actions/get_order_items.php?id=${order.id}`)
    .then(res => res.json())
    .then(items => {
      itemsList.innerHTML = '';
      items.forEach(item => {
        itemsList.innerHTML += `
          <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
            <img src="${item.image_principale}" class="w-12 h-16 object-cover rounded-lg border border-black shadow-sm">
            <div class="flex-1">
              <p class="font-bold text-sm">${item.nom}</p>
              <p class="text-[10px] text-slate-400 uppercase">Quantité : ${item.quantite}</p>
            </div>
            <p class="font-serif font-bold text-black">${item.prix_unitaire} $</p>
          </div>`;
      });
    });

  modal.classList.remove('hidden');
}

function closeOrderModal() {
  document.getElementById('orderModal').classList.add('hidden');
}

// --- 4. GESTION DES COULEURS ---

let selectedColors = [];

function addColor() {
  const hex = document.getElementById('colorPicker').value;
  if (selectedColors.includes(hex)) return alert("Couleur déjà ajoutée");

  selectedColors.push(hex);
  renderColors();
}

function removeColor(color) {
  selectedColors = selectedColors.filter(c => c !== color);
  renderColors();
}

function renderColors() {
  const container = document.getElementById('colorsList');
  const inputHidden = document.getElementById('colorsInput');
  if (!container) return;

  container.innerHTML = selectedColors.map(color => `
    <div class="flex items-center gap-1 px-2 py-1 bg-white rounded-lg border text-xs">
      <div class="w-4 h-4 rounded-full border" style="background:${color}"></div>
      <span>${color}</span>
      <button type="button" onclick="removeColor('${color}')" class="text-red-500">✕</button>
    </div>
  `).join('');

  if (inputHidden) inputHidden.value = JSON.stringify(selectedColors);
}
// --- 5. INITIALISATION & NOTIFICATIONS ---

function confirmLogout() {
  if (confirm("Voulez-vous vraiment vous déconnecter du dashboard ?")) {
    window.location.href = "../assets/actions/logout.php";
  }
}



window.addEventListener('load', () => {

  // 1. Gestion du Loader
  const loader = document.getElementById('loader');
  if (loader) loader.style.display = 'none';

  // 2. Initialisation des états dynamiques (Prix Promo)
  // Masque l'input si la checkbox est décochée au chargement
  if (typeof togglePromoPrice === "function") {
    togglePromoPrice();
  }

  // 3. Gestion des Notifications (Toast)
  const urlParams = new URLSearchParams(window.location.search);
  const toast = document.getElementById('successToast');
  const status = urlParams.get('msg') || urlParams.get('status');

  if (toast && status) {
    const title = toast.querySelector('.font-bold');
    const message = toast.querySelector('.text-slate-300');
    const iconContainer = document.getElementById('toastIcon');

    if (status === 'success') {
      if (title) title.innerText = "Succès";
      if (message) message.innerText = "L'opération a été effectuée avec succès.";
      if (iconContainer) { iconContainer.className = "p-2 bg-green-500 rounded-lg"; }
    }
    else if (status === 'deleted') {
      if (title) title.innerText = "Supprimé";
      if (message) message.innerText = "L'article a été retiré avec succès.";
      if (iconContainer) { iconContainer.className = "p-2 bg-green-500 rounded-lg"; }
    }
    else if (status === 'error') {
      if (title) title.innerText = "Erreur";
      if (message) message.innerText = "L'opération a échoué. Veuillez réessayer.";
      if (iconContainer) { iconContainer.className = "p-2 bg-red-500 rounded-lg"; }
    }

    toast.classList.remove('translate-y-[-150%]', 'opacity-0', 'invisible');
    toast.classList.add('translate-y-0', 'opacity-100');

    const newUrl = window.location.pathname + window.location.hash;
    window.history.replaceState({}, document.title, newUrl);

    setTimeout(() => {
      toast.classList.replace('translate-y-0', 'translate-y-[-150%]');
      toast.classList.replace('opacity-100', 'opacity-0');
      setTimeout(() => { toast.classList.add('invisible'); }, 500);
    }, 3000);
  }
});

/**
 * Gère l'affichage de l'input du prix promotionnel
 */
function togglePromoPrice() {
  const checkbox = document.getElementById('promo_checkbox');
  const container = document.getElementById('promo_price_container');

  if (!checkbox || !container) return;

  if (checkbox.checked) {
    container.classList.remove('hidden');
    const input = container.querySelector('input');
    if (input) input.focus();
  } else {
    container.classList.add('hidden');
    const input = container.querySelector('input');
    if (input) input.value = '';
  }
}


/**
 * Gère l'affichage d'image dos, gauche, droite
 */
window.toggleExtraViews = function (event) {
  const button = event.currentTarget || event.target;
  const extraViews = document.getElementById('extra_views');

  if (!extraViews) return;

  if (extraViews.classList.contains('hidden')) {
    extraViews.classList.remove('hidden');
    extraViews.classList.add('grid');
    button.innerText = "- Masquer les vues supplémentaires";
  } else {
    extraViews.classList.add('hidden');
    extraViews.classList.remove('grid');
    button.innerText = "+ Ajouter des vues (Dos, Côtés)";
  }
};