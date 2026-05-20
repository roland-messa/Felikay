<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$isHome = !isset($isSecondaryPage) || !$isSecondaryPage;

$navClasses = $isHome
  ? 'text-white py-10 opacity-0 -translate-y-full pointer-events-none'
  : 'bg-white shadow-md text-black py-4 border-b border-stone-100 opacity-100 translate-y-0 pointer-events-auto';
?>

<script src="https://unpkg.com/lucide@latest"></script>

<nav id="navbar" class="fixed top-0 w-full z-50 px-6 md:px-12 transition-all duration-700 ease-in-out transform <?php echo $navClasses; ?>">
  <div class="max-w-[1400px] mx-auto grid grid-cols-3 items-center">

    <div class="hidden lg:flex space-x-8 text-[10px] uppercase tracking-[0.2em] font-medium justify-start items-center text-black">
      <button onclick="toggleSideNav()" class="hover:text-gray-400 transition uppercase tracking-[0.2em]">Boutique</button>
      <a href="/ProjetFelykay/pages/collection.php" class="hover:text-gray-400 transition">Collection</a>
      <a href="/ProjetFelykay/pages/about.php" class="hover:text-gray-400 transition">À propos</a>
      <a href="/ProjetFelykay/pages/contact.php" class="hover:text-gray-400 transition">Contact</a>
    </div>

    <a href="/ProjetFelykay/index.php" class="flex flex-col items-center justify-center group transition-transform hover:scale-105">
      <div class="w-12 h-12 md:w-16 md:h-16 rounded-full overflow-hidden border border-gray-100 shadow-sm bg-white">
        <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo Felikay" class="w-full h-full object-cover">
      </div>
      <span class="mt-2 font-serif text-[10px] tracking-[0.3em] uppercase font-bold text-center text-black group-hover:text-stone-500 transition-colors">
        Felikay
      </span>
    </a>

    <div class="flex items-center space-x-6 justify-end text-black">
      <button onclick="toggleCart()" class="relative group p-2 transition-colors">
        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
        <span id="cart-count-nav" class="js-cart-count absolute -top-1 -right-1 bg-black text-white text-[8px] font-bold w-4 h-4 rounded-full flex items-center justify-center border border-white">
          0
        </span>
      </button>

      <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="flex items-center space-x-3">
          <a href="/ProjetFelykay/pages/admin_login.php" class="text-[9px] uppercase tracking-[0.2em] font-medium hover:text-stone-400 transition">
            Connexion
          </a>
          <span class="text-stone-300 text-xs">|</span>
          <a href="/ProjetFelykay/pages/register.php" class="bg-black text-white px-4 py-2 text-[9px] uppercase tracking-[0.2em] font-bold hover:bg-stone-800 transition shadow-md">
            Inscription
          </a>
        </div>
      <?php else: ?>
        <a href="/ProjetFelykay/pages/profile.php" class="hover:text-gray-400 transition p-2">
          <i data-lucide="user" class="w-5 h-5"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div id="sideNav" class="fixed inset-0 z-[60] invisible overflow-hidden transition-all duration-500">
  <div onclick="toggleSideNav()" class="absolute inset-0 bg-black/30 backdrop-blur-sm opacity-0 transition-opacity duration-500" id="sideNavOverlay"></div>

  <div class="absolute inset-y-0 left-0 w-full max-w-xs bg-white shadow-2xl -translate-x-full transition-transform duration-500 ease-out flex flex-col" id="sideNavContent">
    <div class="p-8 border-b border-stone-100 flex justify-between items-center">
      <h3 class="font-serif italic text-xl">Exploration</h3>
      <button onclick="toggleSideNav()" class="p-2 hover:rotate-90 transition-transform duration-300">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto py-10 px-8 space-y-12 text-black">
      <div>
        <a href="/ProjetFelykay/pages/ArticleHomme.php?genre=homme" class="group flex items-center justify-between">
          <span class="text-xs uppercase tracking-[0.3em] font-bold group-hover:pl-2 transition-all">Homme</span>
          <i data-lucide="chevron-right" class="w-3 h-3 text-stone-300"></i>
        </a>
      </div>

      <div>
        <a href="/ProjetFelykay/pages/ArticleHomme.php?genre=femme" class="group flex items-center justify-between">
          <span class="text-xs uppercase tracking-[0.3em] font-bold group-hover:pl-2 transition-all">Femme</span>
          <i data-lucide="chevron-right" class="w-3 h-3 text-stone-300"></i>
        </a>
      </div>

      <div class="space-y-6">
        <p class="text-[9px] uppercase tracking-[0.4em] text-stone-400 font-black mb-4 border-b border-stone-50 pb-2">Univers Enfant</p>
        <div class="pl-4 space-y-4">
          <a href="/ProjetFelykay/pages/ArticleEnfants.php?age=0-5 ans" class="block text-[10px] uppercase tracking-widest hover:text-stone-400 transition">Nourrissons (0-5 ans)</a>
          <a href="/ProjetFelykay/pages/ArticleEnfants.php?age=6-14 ans" class="block text-[10px] uppercase tracking-widest hover:text-stone-400 transition">Enfants (6-14 ans)</a>
          <a href="/ProjetFelykay/pages/ArticleEnfants.php?age=14-18 ans" class="block text-[10px] uppercase tracking-widest hover:text-stone-400 transition">Adolescents</a>
        </div>
      </div>

      <div>
        <a href="/ProjetFelykay/pages/ArticleGadgets.php" class="group flex items-center justify-between">
          <span class="text-xs uppercase tracking-[0.3em] font-bold group-hover:pl-2 transition-all">Maison & Gadgets</span>
          <i data-lucide="chevron-right" class="w-3 h-3 text-stone-300"></i>
        </a>
      </div>
    </div>

    <div class="p-8 bg-stone-50 text-[8px] uppercase tracking-[0.2em] text-stone-400">
      Felikay Maison de Mode — Kinshasa
    </div>
  </div>
</div>

<script>
  {
    const navbar = document.getElementById('navbar');
    if (window.lucide) {
      lucide.createIcons();
    }

    const handleNavbar = () => {
      if (window.scrollY < 20) {
        navbar.classList.add('opacity-0', '-translate-y-full', 'pointer-events-none');
        navbar.classList.remove('bg-white', 'text-black', 'py-4', 'shadow-md', 'opacity-100', 'translate-y-0', 'pointer-events-auto');
      } else {
        navbar.classList.remove('opacity-0', '-translate-y-full', 'text-white', 'py-10', 'pointer-events-none');
        navbar.classList.add('bg-white', 'text-black', 'py-4', 'shadow-md', 'opacity-100', 'translate-y-0', 'pointer-events-auto');
      }
    };

    window.toggleSideNav = function() {
      const modal = document.getElementById('sideNav');
      const content = document.getElementById('sideNavContent');
      const overlay = document.getElementById('sideNavOverlay');

      if (modal.classList.contains('invisible')) {
        modal.classList.remove('invisible');
        setTimeout(() => {
          overlay.classList.add('opacity-100');
          content.classList.remove('-translate-x-full');
        }, 10);
      } else {
        content.classList.add('-translate-x-full');
        overlay.classList.remove('opacity-100');
        setTimeout(() => {
          modal.classList.add('invisible');
        }, 500);
      }
    };

    window.addEventListener('scroll', handleNavbar);
    handleNavbar();
  }
</script>