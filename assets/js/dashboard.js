// --- 1. GESTION DES FORMULAIRES PRODUITS ---

function toggleEnfantFields() {
  const mainCat = document.getElementById('main_cat').value;
  const enfantDiv = document.getElementById('fields_enfant');
  const accessoireDiv = document.getElementById('fields_accessoire');
  const labelTaille = document.getElementById('label_taille');
  const inputTaille = document.getElementById('input_taille');
  const labelDetail = document.getElementById('label_detail_dynamique');

  if (!enfantDiv || !accessoireDiv || !labelTaille || !inputTaille) return;

  enfantDiv.classList.remove('hidden');
  accessoireDiv.classList.add('hidden');

  if (mainCat == "3") {

    if (labelDetail) labelDetail.innerText = "Détails Enfant (Genre & Âge)";
    labelTaille.innerText = "Taille de l'habit";
    inputTaille.placeholder = "Ex: 2-4 ans, M, 12m...";
  }
  else if (mainCat == "5") {

    if (labelDetail) labelDetail.innerText = "Genre pour la chaussure";
    labelTaille.innerText = "Pointure de la chaussure";
    inputTaille.placeholder = "Ex: 38, 42, 44...";
  }
  else if (mainCat == "4") {
    enfantDiv.classList.add('hidden');
    accessoireDiv.classList.remove('hidden');
    labelTaille.innerText = "Taille (optionnel)";
  }
  else {

    if (labelDetail) labelDetail.innerText = "Détails de l'article";
    labelTaille.innerText = "Taille de l'article";
    inputTaille.placeholder = "Ex: S, M, XL, L...";
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

  const loader = document.getElementById('loader');
  if (loader) loader.style.display = 'none';

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
      if (iconContainer) {
        iconContainer.classList.remove('bg-red-500');
        iconContainer.classList.add('bg-green-500');
      }
    }
    else if (status === 'deleted') {
      if (title) title.innerText = "Supprimé";
      if (message) message.innerText = "L'article a été retiré avec succès.";
      if (iconContainer) {
        iconContainer.classList.remove('bg-red-500');
        iconContainer.classList.add('bg-green-500');
      }
    }
    else if (status === 'error') {
      if (title) title.innerText = "Erreur";
      if (message) message.innerText = "L'opération a échoué. Veuillez réessayer.";
      if (iconContainer) {
        iconContainer.classList.remove('bg-green-500');
        iconContainer.classList.add('bg-red-500');
      }
    }

    // 1. Affichage du Toast
    toast.classList.remove('translate-y-[-150%]', 'opacity-0', 'invisible');
    toast.classList.add('translate-y-0', 'opacity-100');

    const newUrl = window.location.pathname + window.location.hash;
    window.history.replaceState({}, document.title, newUrl);

    setTimeout(() => {

      toast.classList.replace('translate-y-0', 'translate-y-[-150%]');
      toast.classList.replace('opacity-100', 'opacity-0');

      setTimeout(() => {
        toast.classList.add('invisible');
      }, 500);
    }, 3000);
  }
});