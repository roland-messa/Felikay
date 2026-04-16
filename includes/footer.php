<?php include 'Ajouter_pagnier.php'; ?>

<footer class="py-20 bg-gray-100 border-t border-gray-100">
  <div class="max-w-[1400px] mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">

      <div class="col-span-1">
        <div class="w-16 h-16 rounded-full overflow-hidden border border-stone-100 shadow-sm mb-6">
          <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo Felikay" class="w-full h-full object-cover">
        </div>

        <h1 class="font-serif text-2xl tracking-[0.2em] uppercase mb-6">Felikay</h1>
        <p class="text-[11px] text-gray-400 leading-relaxed uppercase tracking-widest mb-6">
          Maison d'habillement dédiée à l'élégance contemporaine et au luxe intemporel.
        </p>
        <div class="flex space-x-4 text-gray-400">
          <a href="#" class="hover:text-black transition"><i data-lucide="instagram" class="w-4 h-4"></i></a>
          <a href="#" class="hover:text-black transition"><i data-lucide="facebook" class="w-4 h-4"></i></a>
          <a href="#" class="hover:text-black transition"><i data-lucide="twitter" class="w-4 h-4"></i></a>
        </div>
      </div>

      <div>
        <h4 class="text-[10px] uppercase tracking-[0.3em] font-bold mb-8">Liens Rapides</h4>
        <ul class="text-[11px] uppercase tracking-widest text-gray-500 space-y-4">
          <li><a href="/ProjetFelykay/index.php" class="hover:text-black transition">Accueil</a></li>
          <li><a href="/ProjetFelykay/pages/about.php" class="hover:text-black transition">À Propos</a></li>
          <li><a href="#" class="hover:text-black transition">Services</a></li>
          <li><a href="/ProjetFelykay/pages/contact.php" class="hover:text-black transition">Contact</a></li>
        </ul>
      </div>

      <div>
        <h4 class="text-[10px] uppercase tracking-[0.3em] font-bold mb-8">Aide & Informations</h4>
        <ul class="text-[11px] uppercase tracking-widest text-gray-500 space-y-4">
          <li><a href="#" class="hover:text-black transition">Suivi de commande</a></li>
          <li><a href="#" class="hover:text-black transition">Retours & Échanges</a></li>
          <li><a href="#" class="hover:text-black transition">Expédition & Livraison</a></li>
          <li><a href="#" class="hover:text-black transition">FAQ</a></li>
        </ul>
      </div>

      <div>
        <h4 class="text-[10px] uppercase tracking-[0.3em] font-bold mb-8">Contactez-nous</h4>
        <p class="text-[11px] text-gray-500 tracking-widest mb-4 uppercase">
          Des questions ? <br>
          <span class="text-black font-medium lowercase">contact@felikay.com</span>
        </p>
        <p class="text-[11px] text-gray-500 tracking-widest uppercase">
          Besoin d'aide ? Appelez-nous au <br>
          <span class="text-black font-medium">+243 00 000 00</span>
        </p>
      </div>
    </div>

    <div class="pt-8 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center text-[9px] uppercase tracking-[0.2em] text-gray-400">
      <p>
        © 2026 Felikay. Tous droits réservés.
        <span class="mx-2">|</span>
        <a href="/ProjetFelykay/pages/admin_login.php" class="hover:text-stone-600 transition-colors duration-300">Espace Privé</a>
      </p>
      <div class="flex space-x-6 mt-4 md:mt-0">
        <span>Expédition : Kinshasa</span>
        <span>Paiements sécurisés : Mpesa, AirtelMoney, Afrimoney</span>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://unpkg.com/lucide@0.473.0/dist/umd/lucide.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    // 1. AOS
    if (typeof AOS !== 'undefined') {
      AOS.init({
        duration: 1000,
        once: true
      });
    }

    // 2. Lucide
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }

    // 3. Swiper (Carrousel des ventes)
    if (typeof Swiper !== 'undefined') {
      new Swiper('.saleSwiper', {
        speed: 1000,
        loop: true,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false
        },
        pagination: {
          el: '.swiper-pagination-sale',
          clickable: true
        },
        on: {
          slideChangeTransitionEnd: function() {
            AOS.refresh();
          },
        },
      });
    }
  });
</script>

<script src="/ProjetFelykay/assets/js/index.js?v=<?php echo time(); ?>"></script>
</body>

</html>