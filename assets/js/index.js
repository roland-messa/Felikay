/**
 * index.js - Felikay Engine
 */
console.log('JS chargé avec succès : Felikay Engine');

// --- 1. INITIALISATION GLOBALE ---
var cart = JSON.parse(localStorage.getItem('felikay_cart')) || [];

document.addEventListener('DOMContentLoaded', () => {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

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

  renderCart();

  const checkoutBtn = document.getElementById('checkout-button');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      if (cart.length === 0) {
        alert("Votre panier est vide.");
      } else {
        openPaymentSelection();
      }
    });
  }

  if (document.getElementById('days')) {
    if (typeof startCountdown === 'function') startCountdown(30);
  }
});

// --- 2. FONCTIONS DE PAIEMENT ---

function openPaymentSelection() {
  const modal = document.createElement('div');
  modal.id = "payment-selector-modal";
  modal.className = "fixed inset-0 z-[999] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4 animate-in fade-in duration-300";

  modal.innerHTML = `
    <div class="bg-white w-full max-w-sm p-8 shadow-2xl transform transition-all animate-in zoom-in duration-300">
      <h3 class="font-serif text-2xl italic text-center mb-6">Mode de paiement</h3>
      <p class="text-[10px] uppercase tracking-widest text-gray-400 text-center mb-8 font-bold">Comment souhaitez-vous régler ?</p>
      
      <div class="space-y-4">
        <button onclick="confirmMethod('online')" class="w-full flex items-center justify-between p-4 border border-gray-100 hover:border-black transition-all group text-left">
          <div class="flex items-center gap-4">
            <div class="p-2 bg-gray-50 group-hover:bg-black group-hover:text-white transition-colors">
              <i data-lucide="smartphone" class="w-5 h-5"></i>
            </div>
            <div>
              <span class="block text-[11px] font-bold uppercase tracking-widest">Payer en ligne</span>
              <span class="block text-[9px] text-gray-400 font-normal">M-Pesa, Airtel, Orange</span>
            </div>
          </div>
          <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300"></i>
        </button>

        <button onclick="confirmMethod('delivery')" class="w-full flex items-center justify-between p-4 border border-gray-100 hover:border-black transition-all group text-left">
          <div class="flex items-center gap-4">
            <div class="p-2 bg-gray-50 group-hover:bg-black group-hover:text-white transition-colors">
              <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
            <div>
              <span class="block text-[11px] font-bold uppercase tracking-widest">Payer par Cash</span>
              <span class="block text-[9px] text-gray-400 font-normal">À la livraison</span>
            </div>
          </div>
          <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300"></i>
        </button>
      </div>

      <button onclick="document.getElementById('payment-selector-modal').remove()" class="w-full mt-8 text-[9px] uppercase tracking-[0.3em] text-gray-400 hover:text-black transition-colors">
        Retour au panier
      </button>
    </div>
  `;

  document.body.appendChild(modal);
  if (window.lucide) lucide.createIcons();
}

function confirmMethod(method) {
  window.location.href = `/ProjetFelykay/pages/paiement.php?select=${method}`;
}

// --- 3. FONCTIONS GLOBALES DU PANIER ---

function toggleCart() {
  const drawer = document.getElementById('cart-drawer');
  if (drawer) {
    const isOpen = drawer.classList.toggle('open');
    document.body.style.overflow = isOpen ? 'hidden' : '';
  }
}

function saveAndRender() {
  localStorage.setItem('felikay_cart', JSON.stringify(cart));
  renderCart();
}

function addToCart(id, name, price, img, size = 'Unique', color = 'Standard') {
  const cartKey = `${id}-${size}-${color}`;
  const existingProduct = cart.find(item => item.cartKey === cartKey);

  if (existingProduct) {
    existingProduct.quantity += 1;
  } else {
    cart.push({
      cartKey: cartKey,
      id: id,
      name: name,
      price: parseFloat(price),
      img: img,
      size: size,
      color: color,
      quantity: 1
    });
  }

  saveAndRender();
  const drawer = document.getElementById('cart-drawer');
  if (drawer && !drawer.classList.contains('open')) toggleCart();
}

function removeFromCartByKey(key) {
  cart = cart.filter(item => item.cartKey !== key);
  saveAndRender();
}

function updateQuantityByKey(key, change) {
  const product = cart.find(item => item.cartKey === key);
  if (product) {
    product.quantity += change;
    if (product.quantity <= 0) {
      removeFromCartByKey(key);
    } else {
      saveAndRender();
    }
  }
}

function renderCart() {
  const container = document.getElementById('cart-items-container');
  const countLabels = document.querySelectorAll('#cart-count');
  const totalLabel = document.getElementById('cart-total-price');
  const emptyMsg = document.getElementById('empty-cart-msg');

  if (!container) return;

  container.innerHTML = "";
  let totalPrice = 0;
  let totalItems = 0;

  if (cart.length === 0) {
    if (emptyMsg) emptyMsg.style.display = "block";
    if (totalLabel) totalLabel.innerText = "0.00 $";
  } else {
    if (emptyMsg) emptyMsg.style.display = "none";

    cart.forEach((item) => {
      const itemTotal = item.price * item.quantity;
      totalPrice += itemTotal;
      totalItems += item.quantity;

      const cleanImgPath = item.img.startsWith('http')
        ? item.img
        : '/ProjetFelykay/' + item.img.replace(/^(\.\.\/|\.\/)/, '');

      container.innerHTML += `
        <div class="flex gap-4 items-center border-b border-gray-50 py-4">
            <img src="${cleanImgPath}" class="w-16 h-20 object-cover bg-gray-100" onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">
            <div class="flex-1 text-left">
                <h4 class="text-[10px] font-bold uppercase tracking-tight text-black">${item.name}</h4>
                <p class="text-stone-400 text-[9px] mt-1 uppercase">Taille: ${item.size} | Couleur: ${item.color}</p>
                <p class="text-stone-600 text-[10px]">${item.price.toFixed(2)} $</p>
                <div class="flex items-center gap-3 mt-2">
                    <button onclick="updateQuantityByKey('${item.cartKey}', -1)" class="w-5 h-5 border border-stone-200 flex items-center justify-center text-xs hover:bg-black hover:text-white transition">-</button>
                    <span class="text-[11px] font-bold">${item.quantity}</span>
                    <button onclick="updateQuantityByKey('${item.cartKey}', 1)" class="w-5 h-5 border border-stone-200 flex items-center justify-center text-xs hover:bg-black hover:text-white transition">+</button>
                </div>
                <button onclick="removeFromCartByKey('${item.cartKey}')" class="text-[9px] text-red-400 underline mt-2 uppercase hover:text-red-600 transition">Supprimer</button>
            </div>
            <div class="text-[11px] font-bold text-black">${itemTotal.toFixed(2)} $</div>
        </div>`;
    });
  }

  countLabels.forEach(label => {
    label.innerText = totalItems;
    label.classList.toggle('hidden', totalItems === 0);
  });

  if (totalLabel) totalLabel.innerText = `${totalPrice.toFixed(2)} $`;
  if (window.lucide) lucide.createIcons();
}

// --- 4. LOADER & RECHERCHE ---

function searchFunction() {
  const input = document.getElementById('searchInput');
  if (!input) return;
  const term = input.value.toLowerCase();
  const cards = document.querySelectorAll('.group');
  cards.forEach(card => {
    const title = card.querySelector('h3') ? card.querySelector('h3').innerText.toLowerCase() : "";
    card.style.display = title.includes(term) ? "block" : "none";
  });
}

const hideLoader = () => {
  const loader = document.getElementById('loader');
  if (loader) {
    loader.style.opacity = "0";
    loader.style.visibility = "hidden";
    setTimeout(() => loader.remove(), 800);
  }
};
window.addEventListener('load', () => setTimeout(hideLoader, 600));

// --- 5. NOTIFICATIONS ---

function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  const bgColor = type === 'success' ? 'bg-[#1A1A1A]' : 'bg-red-900';
  toast.className = `${bgColor} text-white px-8 py-4 shadow-2xl flex items-center gap-4 border border-white/10 mb-2`;
  toast.innerHTML = `
        <span class="text-[9px] uppercase tracking-[0.3em] font-bold">${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white/40 hover:text-white transition-colors"><i data-lucide="x" class="w-3 h-3"></i></button>`;
  container.appendChild(toast);
  if (window.lucide) lucide.createIcons();
  setTimeout(() => toast.remove(), 5000);
}


// Nettoyage URL et Notifications au chargement
document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  if (params.has('status')) showToast(params.get('status') === 'success' ? 'Bienvenue au Club Felikay' : 'Erreur', params.get('status'));

  const banner = document.getElementById('notification-banner');
  if (banner) {
    setTimeout(() => {
      banner.style.opacity = '0';
      setTimeout(() => banner.remove(), 500);
    }, 5000);
  }
});
