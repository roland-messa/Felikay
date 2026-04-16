<aside class="w-64 bg-gray-100 border-r border-slate-100 flex flex-col fixed h-full z-50">
  <div class="p-8 flex flex-col items-center justify-center">
    <img src="/ProjetFelykay/assets/img/felikay.jpg" class="w-16 h-16 rounded-xl mb-4 shadow-md">
    <h2 class="font-serif italic text-xl text-center">Felikay Admin</h2>
  </div>

  <nav class="flex-1 px-4 space-y-2">
    <button onclick="switchTab('overview', 'Dashboard', event)" class="nav-link active w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition font-medium">
      <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
      <span>Dashboard</span>
    </button>

    <button onclick="switchTab('products', 'Catalogue & Stocks', event)" class="nav-link w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition font-medium">
      <i data-lucide="shirt" class="w-4 h-4"></i>
      <span>Catalogue</span>
    </button>

    <button onclick="switchTab('orders', 'Commandes Clients', event)" class="nav-link w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition font-medium">
      <i data-lucide="shopping-bag" class="w-4 h-4"></i>
      <span>Commandes</span>
    </button>

    <button onclick="switchTab('messages', 'Messages & Newsletter', event)" class="nav-link w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition font-medium">
      <i data-lucide="mail" class="w-4 h-4"></i>
      <span>Messages</span>
    </button>

    <button onclick="switchTab('analytics', 'Vues & Audience', event)" class="nav-link w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition font-medium">
      <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
      <span>Analyses</span>
    </button>

    <div class="pt-4 mt-4 border-t border-slate-200">
      <button onclick="switchTab('settings', 'Paramètres Système', event)" class="nav-link w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition font-medium">
        <i data-lucide="settings" class="w-4 h-4"></i>
        <span>Paramètres</span>
      </button>
    </div>
  </nav>

  <div class="p-6 border-t border-slate-200">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-8 h-8 rounded-full bg-black text-white flex items-center justify-center text-[10px] font-bold">
        <?php
        $name = $_SESSION['user_nom'] ?? 'Admin';
        // Affiche les initiales dynamiquement (ex: Roland Messa -> RM)
        echo strtoupper(substr($name, 0, 1) . (strpos($name, ' ') ? substr($name, strpos($name, ' ') + 1, 1) : ''));
        ?>
      </div>
      <div class="text-[11px]">
        <p class="font-bold text-slate-800 line-clamp-1"><?php echo htmlspecialchars($name); ?></p>
        <button onclick="confirmLogout()" class="text-red-500 hover:text-red-700 font-semibold transition flex items-center gap-1">
          <i data-lucide="log-out" class="w-3 h-3"></i> Déconnexion
        </button>
      </div>
    </div>
  </div>
</aside>

<script>
  // Initialisation immédiate des icônes après le chargement du menu
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
</script>