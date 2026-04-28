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

// RECUPERATION DES 4 PROMOS ACTIVES POUR L'ACCUEIL
$promos_accueil = $pdo->query("SELECT * FROM produits WHERE is_promo = 1 AND actif_accueil = 1 ORDER BY created_at DESC LIMIT 4")->fetchAll();

// RECUPERATION DES 4 DERNIÈRES NOUVEAUTÉS
$nouveautes_accueil = $pdo->query("SELECT * FROM produits ORDER BY created_at DESC LIMIT 4")->fetchAll();


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





<?php
// On vérifie si le tableau des promos n'est pas vide avant d'afficher la section
if (!empty($promos_accueil)):
?>
    <section class="py-24 bg-[#F6F5F2] overflow-hidden">
        <div class="max-w-[1400px] mx-auto px-6">

            <div class="flex justify-between items-end mb-12" data-aos="fade-up">
                <div>
                    <span class="text-[9px] uppercase tracking-[0.4em] text-stone-400 block mb-3 font-bold">Sélection Spéciale</span>
                    <h2 class="font-serif text-4xl italic">Offres de la Maison</h2>
                </div>
                <a href="pages/Toutes_Promotions.php" class="text-[10px] uppercase tracking-widest border-b border-black pb-1 hover:opacity-50 transition">
                    Voir toutes les promotions
                </a>
            </div>

            <div class="swiper saleSwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($promos_accueil as $item): ?>
                        <div class="swiper-slide">
                            <div class="flex flex-col md:flex-row items-center justify-between gap-16 py-10">
                                <div class="relative group">
                                    <div class="absolute -top-4 -right-4 bg-black text-white w-24 h-24 rounded-full flex flex-col items-center justify-center z-10 text-center shadow-2xl transition-transform duration-700 group-hover:scale-110">
                                        <span class="text-[8px] uppercase tracking-tighter opacity-70 mb-1">
                                            <?= htmlspecialchars($item['promo_tag']) ?>
                                        </span>
                                        <span class="text-lg font-bold leading-none">
                                            <?= number_format($item['prix'], 2) ?> $
                                        </span>
                                    </div>
                                    <img src="<?= htmlspecialchars($item['image_principale']) ?>"
                                        alt="<?= htmlspecialchars($item['nom']) ?>"
                                        class="w-full max-w-md mix-blend-multiply transition-all duration-1000 group-hover:scale-105">
                                </div>

                                <div class="max-w-md text-center md:text-left content-animate">
                                    <span class="text-red-500 text-[10px] uppercase tracking-[0.4em] font-bold mb-6 block">
                                        <?php
                                        $display_subtitle = htmlspecialchars($item['subtitle']);
                                        if (!empty($item['promo_duration'])) {
                                            $display_subtitle .= " — " . htmlspecialchars($item['promo_duration']);
                                        }
                                        echo $display_subtitle;
                                        ?>
                                    </span>

                                    <h2 class="font-serif text-4xl md:text-5xl mb-10 leading-tight italic">
                                        <?= str_replace(',', '<br>', htmlspecialchars($item['nom'])); ?>
                                    </h2>

                                    <button onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['nom']) ?>', <?= $item['prix'] ?>, '<?= $item['image_principale'] ?>')"
                                        class="bg-black text-white px-12 py-5 text-[10px] uppercase tracking-[0.4em] font-bold hover:bg-stone-800 transition-all shadow-lg active:scale-95">
                                        Ajouter au panier
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination-sale mt-10 flex justify-center"></div>
            </div>
        </div>
    </section>
<?php endif;
?>











<section class="py-24 bg-white border-t border-stone-100">
    <div class="max-w-[1400px] mx-auto px-6">
        <div class="flex justify-between items-end mb-16" data-aos="fade-up">
            <h2 class="font-serif text-4xl italic">Dernières Arrivées</h2>
            <a href="pages/Nouvelles_Collections.php" class="text-[10px] uppercase tracking-widest border-b border-black pb-1 hover:opacity-50 transition">
                Voir toutes les nouveautés
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <?php foreach ($nouveautes_accueil as $new): ?>
                <div class="group" data-aos="fade-up">
                    <div class="relative overflow-hidden aspect-[3/4] mb-4 bg-stone-50">
                        <img src="<?= htmlspecialchars($new['image_principale']) ?>"
                            alt="<?= htmlspecialchars($new['nom']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">

                        <span class="absolute top-4 left-4 bg-black text-white text-[8px] px-3 py-1 uppercase tracking-widest font-bold">
                            <?= !empty($new['promo_tag']) ? htmlspecialchars($new['promo_tag']) : 'New' ?>
                        </span>
                    </div>

                    <?php if (!empty($new['subtitle'])): ?>
                        <span class="text-[8px] text-red-500 uppercase tracking-widest font-bold block mb-1">
                            <?= htmlspecialchars($new['subtitle']) ?>
                        </span>
                    <?php endif; ?>

                    <h3 class="font-serif text-lg leading-tight mb-1"><?= htmlspecialchars($new['nom']) ?></h3>

                    <div class="flex items-center gap-2">
                        <p class="text-[10px] uppercase tracking-widest text-stone-400"><?= number_format($new['prix'], 2) ?> $</p>

                        <?php if (!empty($new['is_promo']) && !empty($new['promo_duration'])): ?>
                            <span class="text-[8px] text-stone-300 italic">(<?= htmlspecialchars($new['promo_duration']) ?>)</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<?php
include 'pages/temoignage.php';
include 'pages/SectionNewsletter.php';
include 'includes/footer.php';
?>