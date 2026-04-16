<?php

include '../config/db.php';

$categorie_filter = $_GET['cat'] ?? 'tous';

try {

  $query = "SELECT * FROM produits WHERE categorie_id IN (2, 5)";

  if ($categorie_filter === 'vetements') {
    $query .= " AND categorie_id = 2";
  } elseif ($categorie_filter === 'chaussures') {
    $query .= " AND categorie_id = 5";
  }

  $query .= " ORDER BY created_at DESC";

  $stmt = $pdo->query($query);
  $articles_femmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $articles_femmes = [];
  error_log($e->getMessage());
}

// 2. AFFICHAGE DE LA PAGE
if (basename($_SERVER['PHP_SELF']) == 'ArticleFemme.php') :
  $pageTitle = "Felikay | Collection Femme";
  include '../includes/header.php';
  include '../includes/navbar.php';
?>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <style>
    .swiper-button-next,
    .swiper-button-prev {
      color: #000;
      background: rgba(255, 255, 255, 0.9);
      width: 45px;
      height: 45px;
      border-radius: 50%;
    }

    .swiper-pagination-bullet-active {
      background: #000 !important;
      width: 20px;
      border-radius: 5px;
    }

    .nav-filter.active {
      color: black;
      font-weight: bold;
      border-bottom: 2px solid black;
    }
  </style>

  <main class="bg-[#F9F9F9] min-h-screen">
    <section class="py-24 px-6">
      <div class="max-w-[1400px] mx-auto">

        <div class="text-center mb-12">
          <h2 class="font-serif text-4xl md:text-7xl mb-4 italic">Boutique Femmes</h2>
          <p class="text-[10px] uppercase tracking-[0.5em] text-gray-400">Élégance contemporaine & silhouettes fluides</p>
        </div>

        <div class="flex justify-center gap-10 mb-16 text-[11px] uppercase tracking-[0.2em] text-stone-400">
          <a href="?cat=tous" class="nav-filter <?php echo ($categorie_filter == 'tous') ? 'active' : ''; ?> pb-2 transition-all">Tout l'univers</a>
          <a href="?cat=vetements" class="nav-filter <?php echo ($categorie_filter == 'vetements') ? 'active' : ''; ?> pb-2 transition-all">Prêt-à-porter</a>
          <a href="?cat=chaussures" class="nav-filter <?php echo ($categorie_filter == 'chaussures') ? 'active' : ''; ?> pb-2 transition-all">chaussures</a>
        </div>

        <div class="swiper womenCarousel pb-12">
          <div class="swiper-wrapper">
            <?php foreach ($articles_femmes as $item) :

              $img_path = str_replace('../', '', $item['image_principale']);
              $final_img = "../" . $img_path;
            ?>
              <div class="swiper-slide product-item group">
                <div class="relative overflow-hidden mb-6 aspect-[3/4] bg-white border border-gray-100 shadow-sm">
                  <img src="<?php echo $final_img; ?>" class="product-img w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                  <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-all duration-500 flex items-center justify-center">
                    <button onclick="addToCart('<?php echo addslashes($item['nom']); ?>', <?php echo $item['prix']; ?>, '<?php echo $final_img; ?>')"
                      class="btn-add-to-cart bg-white p-4 rounded-full shadow-xl hover:bg-black hover:text-white transition transform translate-y-8 group-hover:translate-y-0">
                      <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </button>
                  </div>
                </div>
                <div class="text-center px-4">
                  <span class="text-[8px] uppercase tracking-[0.3em] text-stone-400 block mb-2">
                    <?php echo ($item['categorie_id'] == 5) ? 'Collection Souliers' : 'Prêt-à-porter'; ?>
                  </span>
                  <h3 class="product-name text-[11px] uppercase tracking-[0.2em] font-medium mb-2 text-stone-800"><?php echo $item['nom']; ?></h3>
                  <span class="product-price text-[14px] font-semibold italic text-stone-500">
                    <?php echo number_format($item['prix'], 2); ?> <?php echo $item['devise'] ?? 'USD'; ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-pagination"></div>
        </div>

        <?php if (empty($articles_femmes)): ?>
          <p class="text-center text-stone-400 italic py-20">La collection arrive bientôt.</p>
        <?php endif; ?>

      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    new Swiper('.womenCarousel', {
      slidesPerView: 1,
      spaceBetween: 30,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false
      },
      pagination: {
        el: ".swiper-pagination",
        clickable: true
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev"
      },
      breakpoints: {
        640: {
          slidesPerView: 2
        },
        1024: {
          slidesPerView: 3
        }
      }
    });
  </script>

<?php
  include '../includes/footer.php';
endif;
?>