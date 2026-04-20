<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Maison Felikay | Accueil";

require_once 'config/db.php';
require_once 'includes/function.php';
include 'includes/header.php';
include 'includes/navbar.php';


$maintenance = $pdo->query("SELECT valeur FROM settings WHERE cle = 'maintenance_mode'")->fetchColumn();

if ($maintenance === '1' && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    include __DIR__ . '/pages/maintenance.php';
    exit();
}


?>

<div class="absolute top-32 left-0 w-full z-[60] flex justify-center pointer-events-none">
    <div class="pointer-events-auto">
        <?php display_alert(); ?>
    </div>
</div>


<?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="bg-green-100 text-green-800 p-4 text-center text-[10px] uppercase tracking-widest mb-6">
        Merci ! Votre inscription au Club Felikay est confirmée.
    </div>
<?php endif; ?>


<section id="hero-home" class="relative h-screen w-full flex items-center justify-center overflow-hidden">
    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=1000"
        alt="Hero Felikay" class="absolute inset-0 w-full h-full object-cover brightness-[0.85]">

    <div class="relative z-10 text-center text-white px-4" data-aos="fade-up" data-aos-duration="1500">

        <h2 class="font-serif text-5xl md:text-8xl mb-10 leading-tight italic">
            L'Élégance <br>
            <span class="not-italic uppercase tracking-tighter">Signature</span>
        </h2>

        <p class="max-w-lg mx-auto mb-12 text-[11px] uppercase tracking-[0.3em] opacity-80 leading-relaxed hidden md:block">
            Une maison dédiée à la création d'essentiels intemporels. <br>
            Savoir-faire éthique et matières d'exception.
        </p>

        <a href="pages/collection.php"
            class="inline-block bg-white text-black px-12 py-5 text-[10px] uppercase tracking-[0.4em] hover:bg-black hover:text-white transition-all duration-700 shadow-2xl">
            Découvrir la collection
        </a>
    </div>

    <div class="absolute bottom-10 left-10 flex items-center space-x-6 opacity-50 text-white">
        <span class="text-[10px] tracking-widest border-b border-white pb-1">01</span>
        <span class="text-[10px] tracking-widest hover:opacity-100 cursor-pointer transition">02</span>
        <span class="text-[10px] tracking-widest hover:opacity-100 cursor-pointer transition">03</span>
    </div>
</section>

<section id="collections" class="py-28 px-6 md:px-12 bg-white">
    <div class="max-w-[1400px] mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-end mb-20" data-aos="fade-up">
            <div>
                <span class="text-[9px] uppercase tracking-[0.4em] text-stone-400 block mb-3 font-bold">Incontournables</span>
                <h2 class="font-serif text-4xl italic">Le vestiaire Felikay</h2>
            </div>
            <a href="pages/collection.php" class="text-[10px] uppercase tracking-widest border-b border-black pb-1 hover:opacity-50 transition mt-6 md:mt-0">
                Tout découvrir
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <a href="pages/ArticleEnfants.php" class="group block" data-aos="fade-up" data-aos-delay="100">
                <div class="overflow-hidden mb-6 aspect-[3/4] bg-stone-100 relative shadow-sm">
                    <img src="assets/img/enfant_background.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                </div>
                <h3 class="font-serif text-xl mb-1 italic">Collection Enfant</h3>
                <p class="text-[10px] uppercase tracking-widest text-stone-400">À partir de 45.00 $</p>
            </a>

            <a href="pages/ArticleFemme.php" class="group block" data-aos="fade-up" data-aos-delay="200">
                <div class="overflow-hidden mb-6 aspect-[3/4] bg-stone-100 relative shadow-sm">
                    <img src="assets/img/femmebacground.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                </div>
                <h3 class="font-serif text-xl mb-1 italic">Collection Femme</h3>
                <p class="text-[10px] uppercase tracking-widest text-stone-400">À partir de 120.00 $</p>
            </a>

            <a href="pages/ArticleHomme.php" class="group block" data-aos="fade-up" data-aos-delay="300">
                <div class="overflow-hidden mb-6 aspect-[3/4] bg-stone-100 relative shadow-sm">
                    <img src="assets/img/hommeBackground.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                </div>
                <h3 class="font-serif text-xl mb-1 italic">Collection Homme</h3>
                <p class="text-[10px] uppercase tracking-widest text-stone-400">À partir de 85.00 $</p>
            </a>
        </div>
    </div>
</section>

<section class="py-24 bg-[#F6F5F2] overflow-hidden">
    <div class="max-w-[1400px] mx-auto px-6">

        <div class="swiper saleSwiper">
            <div class="swiper-wrapper">

                <div class="swiper-slide">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-16 py-10">
                        <div class="relative group">
                            <div class="absolute -top-4 -right-4 bg-black text-white w-24 h-24 rounded-full flex flex-col items-center justify-center z-10 text-center shadow-2xl">
                                <span class="text-[8px] uppercase tracking-tighter opacity-70">Exclusif</span>
                                <span class="text-lg font-bold">29,99 $</span>
                            </div>
                            <img src="assets/img/sac2.jpg" alt="Sacoche" class="w-full max-w-md mix-blend-multiply transition-transform duration-1000 group-hover:rotate-3 group-hover:scale-105">
                        </div>
                        <div class="max-w-md text-center md:text-left">
                            <span class="text-red-500 text-[10px] uppercase tracking-[0.4em] font-bold mb-6 block">Offre de la semaine</span>
                            <h2 class="font-serif text-4xl md:text-5xl mb-10 leading-tight">Sacoche de poitrine <br> Édition Limitée</h2>
                            <button onclick="addToCart(999, 'Sacoche de poitrine Edition Limitée', 29.99, 'assets/img/sac2.jpg')"
                                class="bg-black text-white px-12 py-5 text-[10px] uppercase tracking-[0.4em] font-bold hover:bg-stone-800 transition-all shadow-lg">
                                Ajouter au panier
                            </button>
                        </div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-16 py-10">
                        <div class="relative group">
                            <div class="absolute -top-4 -right-4 bg-black text-white w-24 h-24 rounded-full flex flex-col items-center justify-center z-10 text-center shadow-2xl">
                                <span class="text-[8px] uppercase tracking-tighter opacity-70">Promotion</span>
                                <span class="text-lg font-bold">85,00 $</span>
                            </div>
                            <img src="https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=500" alt="Souliers" class="w-full max-w-md mix-blend-multiply transition-transform duration-1000 group-hover:-rotate-3 group-hover:scale-105">
                        </div>
                        <div class="max-w-md text-center md:text-left">
                            <span class="text-red-500 text-[10px] uppercase tracking-[0.4em] font-bold mb-6 block">Incontournable</span>
                            <h2 class="font-serif text-4xl md:text-5xl mb-10 leading-tight">Mocassins Cuir <br> Glacé Noir</h2>
                            <button onclick="addToCart(998, 'Mocassins Cuir Glacé', 85.00, 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=500')"
                                class="bg-black text-white px-12 py-5 text-[10px] uppercase tracking-[0.4em] font-bold hover:bg-stone-800 transition-all shadow-lg">
                                Ajouter au panier
                            </button>
                        </div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-16 py-10">
                        <div class="relative group">
                            <div class="absolute -top-4 -right-4 bg-black text-white w-24 h-24 rounded-full flex flex-col items-center justify-center z-10 text-center shadow-2xl">
                                <span class="text-[8px] uppercase tracking-tighter opacity-70">Nouveau prix</span>
                                <span class="text-lg font-bold">45,00 $</span>
                            </div>
                            <img src="assets/img/parfum.jpg" alt="Parfum" class="w-full max-w-md mix-blend-multiply transition-transform duration-1000 group-hover:scale-110">
                        </div>
                        <div class="max-w-md text-center md:text-left">
                            <span class="text-red-500 text-[10px] uppercase tracking-[0.4em] font-bold mb-6 block">Essence Pure</span>
                            <h2 class="font-serif text-4xl md:text-5xl mb-10 leading-tight">Signature d'Orient <br> Eau de Parfum</h2>
                            <button onclick="addToCart(997, 'Parfum Signature Orient', 45.00, 'assets/img/parfum.jpg')"
                                class="bg-black text-white px-12 py-5 text-[10px] uppercase tracking-[0.4em] font-bold hover:bg-stone-800 transition-all shadow-lg">
                                Ajouter au panier
                            </button>
                        </div>
                    </div>
                </div>

                <div class="swiper-slide">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-16 py-10">
                        <div class="relative group">
                            <div class="absolute -top-4 -right-4 bg-black text-white w-24 h-24 rounded-full flex flex-col items-center justify-center z-10 text-center shadow-2xl">
                                <span class="text-[8px] uppercase tracking-tighter opacity-70">Offre</span>
                                <span class="text-lg font-bold">19,99 $</span>
                            </div>
                            <img src="assets/img/ceinture.jpg" alt="Accessoire" class="w-full max-w-md mix-blend-multiply transition-transform duration-1000 group-hover:rotate-6">
                        </div>
                        <div class="max-w-md text-center md:text-left">
                            <span class="text-red-500 text-[10px] uppercase tracking-[0.4em] font-bold mb-6 block">Accessoires</span>
                            <h2 class="font-serif text-4xl md:text-5xl mb-10 leading-tight">Ceinture Classique <br> Cuir de Toscane</h2>
                            <button onclick="addToCart(996, 'Ceinture Cuir Toscane', 19.99, 'assets/img/ceinture.jpg')"
                                class="bg-black text-white px-12 py-5 text-[10px] uppercase tracking-[0.4em] font-bold hover:bg-stone-800 transition-all shadow-lg">
                                Ajouter au panier
                            </button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="swiper-pagination-sale mt-10 flex justify-center"></div>
        </div>

    </div>


</section>

<?php
include 'pages/temoignage.php';
include 'pages/SectionNewsletter.php';
include 'includes/footer.php';
?>