<?php
// C:\wamp64\www\ProjetFelykay\index.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Maison Felikay | Accueil";

require_once 'config/db.php';
require_once 'includes/function.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Vérification du mode maintenance
$maintenance = $pdo->query("SELECT valeur FROM settings WHERE cle = 'maintenance_mode'")->fetchColumn();

if ($maintenance === '1' && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    include __DIR__ . '/pages/maintenance.php';
    exit();
}

/** 
 * Fonction utilitaire pour nettoyer les chemins d'images en base de données
 * et garantir qu'ils s'affichent correctement sur Wamp
 */
function getProductImage($path)
{
    $cleanPath = ltrim(trim($path), './');
    // Si le chemin ne contient pas déjà le dossier assets, on l'ajoute
    if (strpos($cleanPath, 'assets/') === false) {
        $cleanPath = 'assets/img/produits/' . $cleanPath;
    }
    return '/ProjetFelykay/' . $cleanPath;
}

// RECUPERATION DES 4 PROMOS ACTIVES
$promos_accueil = $pdo->query("SELECT * FROM produits WHERE is_promo = 1 AND actif_accueil = 1 ORDER BY created_at DESC LIMIT 4")->fetchAll();

// RECUPERATION DES 4 DERNIÈRES NOUVEAUTÉS
$nouveautes_accueil = $pdo->query("SELECT * FROM produits ORDER BY created_at DESC LIMIT 4")->fetchAll();
?>

<!-- Alertes système -->
<div class="absolute top-32 left-0 w-full z-[60] flex justify-center pointer-events-none">
    <div class="pointer-events-auto">
        <?php display_alert(); ?>
    </div>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
    <div class="bg-green-100 text-green-800 p-4 text-center text-[10px] uppercase tracking-widest mb-6">
        Merci ! Votre inscription au Club Felikay Pis est confirmée.
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section id="hero-home" class="relative h-screen w-full flex items-center justify-center overflow-hidden">
    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=1000"
        alt="Hero Felikay" class="absolute inset-0 w-full h-full object-cover brightness-[0.85]">

    <div class="relative z-10 text-center text-white px-4" data-aos="fade-up" data-aos-duration="1500">
        <h2 class="font-serif text-5xl md:text-8xl mb-10 leading-tight italic">
            L'Élégance <br>
            <span class="not-italic uppercase tracking-tighter">Signature</span>
        </h2>
        <p class="max-w-lg mx-auto mb-12 text-[11px] uppercase tracking-[0.3em] opacity-80 leading-relaxed hidden md:block">
            Une maison dédiée à la création d'essentiels intemporels.
        </p>
        <a href="pages/ArticleAdultes.php"
            class="inline-block bg-white text-black px-12 py-5 text-[10px] uppercase tracking-[0.4em] hover:bg-black hover:text-white transition-all duration-700 shadow-2xl">
            Découvrir la collection
        </a>
    </div>
</section>

<!-- Collections par Catégories -->
<section id="collections" class="py-28 px-6 md:px-12 bg-white">
    <div class="max-w-[1400px] mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-end mb-20" data-aos="fade-up">
            <div>
                <span class="text-[9px] uppercase tracking-[0.4em] text-stone-400 block mb-3 font-bold">Incontournables</span>
                <h2 class="font-serif text-4xl italic">Le vestiaire Felikay</h2>
            </div>
            <a href="pages/ArticleAdultes.php" class="text-[10px] uppercase tracking-widest border-b border-black pb-1 hover:opacity-50 transition mt-6 md:mt-0">
                Tout découvrir
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            <!-- Enfant -->
            <a href="pages/ArticleEnfants.php" class="group block" data-aos="fade-up" data-aos-delay="100">
                <div class="overflow-hidden mb-6 aspect-[3/4] bg-stone-100 relative shadow-sm">
                    <img src="assets/img/enfant_background.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" onerror="this.src='assets/img/felikay.jpg'">
                </div>
                <h3 class="font-serif text-xl mb-1 italic">Collection Enfant</h3>
                <p class="text-[10px] uppercase tracking-widest text-stone-400">À partir de 45.00 $</p>
            </a>
            <!-- Femme -->
            <a href="pages/ArticleAdultes.php?genre=femme" class="group block" data-aos="fade-up" data-aos-delay="200">
                <div class="overflow-hidden mb-6 aspect-[3/4] bg-stone-100 relative shadow-sm">
                    <img src="assets/img/femmebacground.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" onerror="this.src='assets/img/felikay.jpg'">
                </div>
                <h3 class="font-serif text-xl mb-1 italic">Collection Femme</h3>
                <p class="text-[10px] uppercase tracking-widest text-stone-400">À partir de 120.00 $</p>
            </a>
            <!-- Homme -->
            <a href="pages/ArticleAdultes.php?genre=homme" class="group block" data-aos="fade-up" data-aos-delay="300">
                <div class="overflow-hidden mb-6 aspect-[3/4] bg-stone-100 relative shadow-sm">
                    <img src="assets/img/hommeBackground.jpg" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" onerror="this.src='assets/img/felikay.jpg'">
                </div>
                <h3 class="font-serif text-xl mb-1 italic">Collection Homme</h3>
                <p class="text-[10px] uppercase tracking-widest text-stone-400">À partir de 85.00 $</p>
            </a>
        </div>
    </div>
</section>

<!-- Promotions -->
<?php if (!empty($promos_accueil)): ?>
    <section class="py-24 bg-[#F6F5F2] overflow-hidden">
        <div class="max-w-[1400px] mx-auto px-6">
            <div class="flex justify-between items-end mb-12" data-aos="fade-up">
                <div>
                    <span class="text-[9px] uppercase tracking-[0.4em] text-stone-400 block mb-3 font-bold">Sélection Spéciale</span>
                    <h2 class="font-serif text-4xl italic">Offres de la Maison</h2>
                </div>
            </div>

            <div class="swiper saleSwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($promos_accueil as $item): ?>
                        <div class="swiper-slide">
                            <div class="flex flex-col md:flex-row items-center justify-between gap-16 py-10">
                                <div class="relative group">
                                    <div class="absolute -top-4 -right-4 bg-black text-white w-24 h-24 rounded-full flex flex-col items-center justify-center z-10 text-center shadow-2xl">
                                        <span class="text-[8px] uppercase tracking-tighter opacity-70 mb-1"><?= htmlspecialchars($item['promo_tag']) ?></span>
                                        <span class="text-lg font-bold leading-none"><?= number_format($item['prix'], 2) ?> $</span>
                                    </div>
                                    <img src="<?= getProductImage($item['image_principale']) ?>"
                                        alt="<?= htmlspecialchars($item['nom']) ?>"
                                        class="w-full max-w-md mix-blend-multiply transition-all duration-1000 group-hover:scale-105"
                                        onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">
                                </div>

                                <div class="max-w-md text-center md:text-left">
                                    <span class="text-red-500 text-[10px] uppercase tracking-[0.4em] font-bold mb-6 block">
                                        <?= htmlspecialchars($item['subtitle']) ?> <?= !empty($item['promo_duration']) ? " — " . htmlspecialchars($item['promo_duration']) : "" ?>
                                    </span>
                                    <h2 class="font-serif text-4xl md:text-5xl mb-10 leading-tight italic">
                                        <?= str_replace(',', '<br>', htmlspecialchars($item['nom'])); ?>
                                    </h2>

                                    <?php if ($item['stock_total'] > 0): ?>
                                        <button onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['nom']) ?>', <?= $item['prix'] ?>, '<?= $item['image_principale'] ?>')"
                                            class="bg-black text-white px-12 py-5 text-[10px] uppercase tracking-[0.4em] font-bold hover:bg-stone-800 transition-all shadow-lg">
                                            Ajouter au panier
                                        </button>
                                    <?php else: ?>
                                        <button class="bg-gray-400 text-white px-12 py-5 text-[10px] uppercase tracking-[0.4em] font-bold cursor-not-allowed" disabled>
                                            Rupture de stock
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Dernières Arrivées -->
<section class="py-24 bg-white border-t border-stone-100">
    <div class="max-w-[1400px] mx-auto px-6">
        <div class="flex justify-between items-end mb-16" data-aos="fade-up">
            <h2 class="font-serif text-4xl italic">Dernières Arrivées</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <?php foreach ($nouveautes_accueil as $new): ?>
                <div class="group" data-aos="fade-up">
                    <a href="pages/ArticleSeul.php?id=<?= $new['id'] ?>" class="block">
                        <div class="relative overflow-hidden aspect-[3/4] mb-4 bg-stone-50">
                            <img src="<?= getProductImage($new['image_principale']) ?>"
                                alt="<?= htmlspecialchars($new['nom']) ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000"
                                onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">
                            <span class="absolute top-4 left-4 bg-black text-white text-[8px] px-3 py-1 uppercase tracking-widest font-bold">
                                <?= !empty($new['promo_tag']) ? htmlspecialchars($new['promo_tag']) : 'New' ?>
                            </span>
                        </div>
                        <h3 class="font-serif text-lg leading-tight mb-1 text-black"><?= htmlspecialchars($new['nom']) ?></h3>
                        <p class="text-[10px] uppercase tracking-widest text-stone-400"><?= number_format($new['prix'], 2) ?> $</p>
                    </a>
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