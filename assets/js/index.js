console.log('JS chargé avec succès : Felikay Engine');

// --- 1. GESTION DU PANIER (ÉTAT GLOBAL) ---
// On utilise 'var' ou on vérifie l'existence pour éviter l'erreur "already declared"
if (typeof cart === 'undefined') {
  var cart = JSON.parse(localStorage.getItem('felikay_cart')) || [];
}

document.addEventListener('DOMContentLoaded', () => {
  // Initialisation des icônes
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  // Gestion du Drawer Panier
  const overlay = document.getElementById('cart-overlay');
  const openCartBtn = document.getElementById('open-cart');

  if (openCartBtn) {
    openCartBtn.addEventListener('click', (e) => {
      e.preventDefault();
      toggleCart();
    });
  }
  if (overlay) {
    overlay.addEventListener('click', toggleCart);
  }

  // Premier rendu du panier
  renderCart();

  // Gestion du bouton "COMMANDER" dans le drawer
  const checkoutBtn = document.getElementById('checkout-button');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      if (cart.length === 0) {
        alert("Votre panier est vide.");
      } else {
        window.location.href = '/ProjetFelykay/pages/paiement.php';
      }
    });
  }

  if (document.getElementById('days')) startCountdown(30);
});

// --- 2. FONCTIONS DE RECHERCHE ---
function searchFunction() {
  const input = document.getElementById('searchInput');
  if (!input) return;

  const term = input.value.toLowerCase();
  const cards = document.querySelectorAll('.group'); // Sélecteur mis à jour pour collection.php

  cards.forEach(card => {
    const title = card.querySelector('h3') ? card.querySelector('h3').innerText.toLowerCase() : "";
    card.style.display = title.includes(term) ? "block" : "none";
  });
}

// --- 3. FONCTIONS GLOBALES DU PANIER ---
function toggleCart() {
  const drawer = document.getElementById('cart-drawer');
  if (drawer) {
    const isOpen = drawer.classList.toggle('open');
    document.body.style.overflow = isOpen ? 'hidden' : '';
  }
}

function addToCart(name, price, img) {
  const product = {
    name: name,
    price: parseFloat(price),
    img: img,
    id: Date.now()
  };
  cart.push(product);
  localStorage.setItem('felikay_cart', JSON.stringify(cart));
  renderCart();

  // Ouvrir le panier pour confirmation
  const drawer = document.getElementById('cart-drawer');
  if (drawer && !drawer.classList.contains('open')) {
    toggleCart();
  }
}

function removeFromCart(id) {
  cart = cart.filter(item => item.id !== id);
  localStorage.setItem('felikay_cart', JSON.stringify(cart));
  renderCart();
}

function renderCart() {
  const container = document.getElementById('cart-items-container');
  const countLabels = document.querySelectorAll('#cart-count'); // On cible tous les badges
  const totalLabel = document.getElementById('cart-total-price');
  const emptyMsg = document.getElementById('empty-cart-msg');

  if (!container) return;

  container.innerHTML = "";
  let total = 0;

  if (cart.length === 0) {
    if (emptyMsg) emptyMsg.style.display = "block";
    if (totalLabel) totalLabel.innerText = "0.00 $";
  } else {
    if (emptyMsg) emptyMsg.style.display = "none";
    cart.forEach((item) => {
      total += item.price;

      // Correction cruciale du chemin d'image pour éviter le 404
      const cleanImgPath = item.img.startsWith('http')
        ? item.img
        : '/ProjetFelykay/' + item.img.replace(/^(\.\.\/|\.\/)/, '');

      container.innerHTML += `
                <div class="flex gap-4 items-center border-b border-gray-50 pb-4">
                    <img src="${cleanImgPath}" 
                         class="w-16 h-20 object-cover bg-gray-100 shadow-sm"
                         onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">
                    <div class="flex-1">
                        <h4 class="text-[10px] font-bold uppercase tracking-tight text-black">${item.name}</h4>
                        <p class="text-stone-500 text-[11px] italic mt-1">${item.price.toFixed(2)} $</p>
                        <button onclick="removeFromCart(${item.id})" 
                                class="text-[9px] text-red-400 underline mt-1 uppercase hover:text-red-600 transition">
                            Supprimer
                        </button>
                    </div>
                </div>`;
    });
  }

  // Mise à jour de tous les compteurs (badge icône + titre panier)
  countLabels.forEach(label => {
    label.innerText = cart.length;
    label.classList.toggle('hidden', cart.length === 0);
  });

  if (totalLabel) totalLabel.innerText = `${total.toFixed(2)} $`;

  if (window.lucide) lucide.createIcons();
}

// --- 4. LOADER ---
const hideLoader = () => {
  const loader = document.getElementById('loader');
  if (loader) {
    loader.style.opacity = "0";
    loader.style.visibility = "hidden";
    setTimeout(() => loader.remove(), 800);
  }
};

window.addEventListener('load', () => setTimeout(hideLoader, 600));