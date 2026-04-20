console.log('JS chargé avec succès : Felikay Engine');

// --- 1. GESTION DU PANIER (ÉTAT GLOBAL) ---
if (typeof cart === 'undefined') {
  var cart = JSON.parse(localStorage.getItem('felikay_cart')) || [];
}

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
  const cards = document.querySelectorAll('.group');

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

/**
 * AJOUTÉE : Sauvegarde dans le localStorage et rafraîchit l'affichage
 */
function saveAndRender() {
  localStorage.setItem('felikay_cart', JSON.stringify(cart));
  renderCart();
}

function addToCart(id, name, price, img) {
  const existingProduct = cart.find(item => item.id === id);

  if (existingProduct) {
    existingProduct.quantity += 1;
  } else {
    const product = {
      id: id,
      name: name,
      price: parseFloat(price),
      img: img,
      quantity: 1
    };
    cart.push(product);
  }

  saveAndRender();
  const drawer = document.getElementById('cart-drawer');
  if (drawer && !drawer.classList.contains('open')) {
    toggleCart();
  }
}

function removeFromCart(id) {
  cart = cart.filter(item => item.id !== id);
  saveAndRender();
}

function updateQuantity(id, change) {
  const product = cart.find(item => item.id === id);
  if (product) {
    product.quantity += change;
    if (product.quantity <= 0) {
      removeFromCart(id);
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
                    <img src="${cleanImgPath}" 
                         class="w-16 h-20 object-cover bg-gray-100"
                         onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">
                    <div class="flex-1">
                        <h4 class="text-[10px] font-bold uppercase tracking-tight text-black">${item.name}</h4>
                        <p class="text-stone-500 text-[11px] mt-1">${item.price.toFixed(2)} $</p>
                        
                        <div class="flex items-center gap-3 mt-2">
                            <button onclick="updateQuantity(${item.id}, -1)" class="w-5 h-5 border border-stone-200 flex items-center justify-center text-xs hover:bg-black hover:text-white transition">-</button>
                            <span class="text-[11px] font-bold">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, 1)" class="w-5 h-5 border border-stone-200 flex items-center justify-center text-xs hover:bg-black hover:text-white transition">+</button>
                        </div>

                        <button onclick="removeFromCart(${item.id})" 
                                class="text-[9px] text-red-400 underline mt-2 uppercase hover:text-red-600 transition">
                            Supprimer
                        </button>
                    </div>
                    <div class="text-[11px] font-bold text-black">
                        ${itemTotal.toFixed(2)} $
                    </div>
                </div>`;
    });
  }

  countLabels.forEach(label => {
    label.innerText = totalItems;
    label.classList.toggle('hidden', totalItems === 0);
  });

  const drawerTitle = document.querySelector('#cart-drawer h2, #cart-drawer h3');
  if (drawerTitle) {
    drawerTitle.innerText = `MON PANIER (${totalItems})`;
  }

  if (totalLabel) totalLabel.innerText = `${totalPrice.toFixed(2)} $`;
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

// --- 5. NOTIFICATIONS (TOASTS) ---
function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  const bgColor = type === 'success' ? 'bg-[#1A1A1A]' : 'bg-red-900';

  toast.className = `${bgColor} text-white px-8 py-4 shadow-2xl flex items-center gap-4 transform translate-y-10 opacity-0 transition-all duration-500 ease-out border border-white/10 pointer-events-auto`;
  toast.innerHTML = `
        <span class="text-[9px] uppercase tracking-[0.3em] font-bold">${message}</span>
        <button onclick="this.parentElement.remove()" class="text-white/40 hover:text-white transition-colors">
            <i data-lucide="x" class="w-3 h-3"></i>
        </button>
    `;
  container.appendChild(toast);
  if (window.lucide) lucide.createIcons();
  setTimeout(() => toast.classList.remove('translate-y-10', 'opacity-0'), 100);
  setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-2');
    setTimeout(() => toast.remove(), 500);
  }, 5000);
}

document.addEventListener('submit', (e) => {
  const btn = e.target.querySelector('button[type="submit"]');
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse italic">Envoi en cours...</span>';
  }
});

window.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(window.location.search);
  if (params.has('status')) {
    const s = params.get('status');
    if (s === 'success') showToast('Bienvenue au Club Felikay');
    if (s === 'error') showToast('Erreur lors de l\'inscription', 'error');
  }
  if (params.has('msg')) {
    const m = params.get('msg');
    if (m === 'success_contact') showToast('Message envoyé avec succès');
    if (m === 'error') showToast('Erreur : veuillez réessayer', 'error');
  }
});