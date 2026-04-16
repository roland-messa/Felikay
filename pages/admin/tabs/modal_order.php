<div id="orderModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
  <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-black animate-slideUp">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
      <div>
        <h3 class="font-serif italic text-2xl" id="modalOrderId">Détails Commande</h3>
        <p class="text-[10px] uppercase font-bold text-slate-400" id="modalOrderCustomer"></p>
      </div>
      <button onclick="closeOrderModal()" class="text-2xl hover:rotate-90 transition-transform">✕</button>
    </div>

    <div class="p-8">
      <div id="orderItemsList" class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
      </div>

      <div class="mt-8 pt-6 border-t border-black flex justify-between items-end">
        <div>
          <p class="text-[10px] uppercase font-bold text-slate-400">Adresse de livraison</p>
          <p id="modalOrderAddress" class="text-sm italic text-slate-700"></p>
        </div>
        <div class="text-right">
          <p class="text-[10px] uppercase font-bold text-slate-400">Total payé</p>
          <p id="modalOrderTotal" class="text-3xl font-serif font-bold text-black"></p>
        </div>
      </div>
    </div>
  </div>
</div>