<div id="cart-drawer" class="fixed inset-0 z-[100] invisible">
  <div id="cart-overlay" class="absolute inset-0 bg-black/40 opacity-0 transition-opacity duration-300"></div>

  <div id="cart-content" class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl translate-x-full transition-transform duration-300 flex flex-col">
    <div class="p-6 border-b flex justify-between items-center">
      <h2 class="text-lg font-bold uppercase tracking-widest">
        Mon Panier (<span id="cart-count-drawer" class="js-cart-count">0</span>)
      </h2>
      <button onclick="toggleCart()" class="p-2 hover:bg-gray-100 rounded-full transition">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>

    <div id="cart-items-container" class="flex-1 overflow-y-auto p-6 space-y-6">
      <p id="empty-cart-msg" class="text-center text-gray-400 mt-10">Votre panier est vide.</p>
    </div>

    <div class="p-6 border-t bg-gray-50">
      <div class="flex justify-between items-center mb-6">
        <span class="text-gray-500 uppercase text-xs tracking-widest">Total estimé</span>
        <span id="cart-total-price" class="text-xl font-bold">$0.00</span>
      </div>

      <button id="checkout-button" class="w-full bg-black text-white py-4 uppercase text-[12px] tracking-[0.2em] font-bold hover:bg-stone-800 transition">
        Commander
      </button>
    </div>
  </div>
</div>