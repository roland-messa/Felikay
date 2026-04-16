<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$isHome = !isset($isSecondaryPage) || !$isSecondaryPage;

$navClasses = $isHome
  ? 'bg-transparent text-white py-10 opacity-0 -translate-y-full pointer-events-none'
  : 'bg-white shadow-md text-black py-4 border-b border-stone-100 opacity-100 translate-y-0 pointer-events-auto';
?>

<nav id="navbar" class="fixed top-0 w-full z-50 px-6 md:px-12 transition-all duration-700 ease-in-out transform <?php echo $navClasses; ?>">
  <div class="max-w-[1400px] mx-auto grid grid-cols-3 items-center">

    <div class="hidden lg:flex space-x-10 text-[10px] uppercase tracking-[0.2em] font-medium justify-start items-center text-black">
      <a href="/ProjetFelykay/index.php" class="hover:text-gray-400 transition">Boutique</a>
      <a href="/ProjetFelykay/pages/collection.php" class="hover:text-gray-400 transition">Collections</a>
      <a href="/ProjetFelykay/pages/about.php" class="hover:text-gray-400 transition">À propos</a>
      <a href="/ProjetFelykay/pages/contact.php" class="hover:text-gray-400 transition">Contact</a>
    </div>

    <div class="flex flex-col items-center justify-center">
      <div class="w-12 h-12 md:w-16 md:h-16 rounded-full overflow-hidden border border-gray-100 shadow-sm bg-white">
        <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo Felikay" class="w-full h-full object-cover">
      </div>
      <span class="mt-2 font-serif text-[10px] tracking-[0.3em] uppercase font-bold text-center text-black">Felikay</span>
    </div>

    <div class="flex items-center space-x-6 justify-end text-black">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="/ProjetFelykay/pages/register.php" class="bg-black text-white px-5 py-2 text-[9px] uppercase tracking-[0.2em] font-bold hover:bg-stone-800 transition shadow-lg">
          Inscription
        </a>
      <?php endif; ?>
    </div>

  </div>
</nav>

<script>
  {
    const navbar = document.getElementById('navbar');
    const handleNavbar = () => {
      if (window.scrollY < 20) {
        navbar.classList.add('opacity-0', '-translate-y-full', 'pointer-events-none');
        navbar.classList.remove('bg-white', 'text-black', 'py-4', 'shadow-md', 'opacity-100', 'translate-y-0', 'pointer-events-auto');
      } else {
        navbar.classList.remove('opacity-0', '-translate-y-full', 'text-white', 'py-10', 'pointer-events-none');
        navbar.classList.add('bg-white', 'text-black', 'py-4', 'shadow-md', 'opacity-100', 'translate-y-0', 'pointer-events-auto');
      }
    };
    window.addEventListener('scroll', handleNavbar);
    handleNavbar();
  }
</script>