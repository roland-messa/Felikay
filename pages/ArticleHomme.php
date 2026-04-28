<?php
include '../config/db.php';

// 1. RÉCUPÉRATION DES FILTRES
$categorie_filter = $_GET['cat'] ?? 'tous';

try {
  // CORRECTION : categorie_id 1 (Habits) et 4 (Chaussures Hommes)
  $base_sql = "SELECT * FROM produits WHERE categorie_id IN (1, 4)";

  if ($categorie_filter === 'vetements') {
    $base_sql .= " AND categorie_id = 1";
  } elseif ($categorie_filter === 'chaussures') {
    $base_sql .= " AND categorie_id = 4";
  }

  $base_sql .= " ORDER BY created_at DESC";

  $stmt = $pdo->prepare($base_sql);
  $stmt->execute();
  $articles_hommes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $articles_hommes = [];
  error_log($e->getMessage());
}

// 2. AFFICHAGE DE LA PAGE
if (basename($_SERVER['PHP_SELF']) == 'ArticleHomme.php') :
  $pageTitle = "Felikay | Collection Homme";
  include '../includes/header.php';
  include '../includes/navbar.php';
?>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <style>
    .swiper-button-next,
    .swiper-button-prev {
      color: #000;
      background: white;
      width: 45px;
      height: 45px;
      border-radius: 50%;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .swiper-pagination-bullet-active {
      background: #000 !important;
      width: 24px;
      border-radius: 4px;
    }

    .nav-filter.active {
      color: black;
      font-weight: bold;
      border-bottom: 2px solid black;
    }
  </style>

  <main class="bg-[#F9F9F9] min-h-screen">
    <section class="py-24 px-6 overflow-hidden">
      <div class="max-w-[1400px] mx-auto">

        <div class="text-center mb-12">
          <h2 class="font-serif text-4xl md:text-6xl mb-4 italic">Boutique Hommes</h2>
          <p class="text-[10px] uppercase tracking-[0.4em] text-gray-400">Coupes impeccables & matières premium</p>
        </div>

        <div class="flex justify-center gap-8 mb-16 text-[11px] uppercase tracking-widest text-stone-400">
          <a href="?cat=tous" class="nav-filter <?php echo ($categorie_filter == 'tous') ? 'active' : ''; ?> pb-2 transition">Tout voir</a>
          <a href="?cat=vetements" class="nav-filter <?php echo ($categorie_filter == 'vetements') ? 'active' : ''; ?> pb-2 transition">Vêtements</a>
          <a href="?cat=chaussures" class="nav-filter <?php echo ($categorie_filter == 'chaussures') ? 'active' : ''; ?> pb-2 transition">Chaussures</a>
        </div>

        <div class="swiper menCarousel pb-12">
          <div class="swiper-wrapper">
            <?php foreach ($articles_hommes as $item) :
              $img_path = str_replace('../', '', $item['image_principale']);
              $final_img = "../" . $img_path;
              $detail_url = "ArticleSeul.php?id=" . $item['id'];
            ?>
              <div class="swiper-slide product-item group">
                <a href="<?php echo $detail_url; ?>" class="relative block overflow-hidden mb-6 aspect-[3/4] bg-white border border-gray-100 shadow-sm">
                  <img src="<?php echo $final_img; ?>" class="product-img w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                  <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-all duration-500 flex items-center justify-center">
                    <button onclick="event.preventDefault(); event.stopPropagation(); addToCart(<?php echo $item['id']; ?>, '<?php echo addslashes($item['nom']); ?>', <?php echo $item['prix']; ?>, '<?php echo $final_img; ?>')"
                      class="bg-white p-4 rounded-full shadow-lg hover:bg-black hover:text-white transition transform translate-y-8 group-hover:translate-y-0">
                      <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                    </button>
                  </div>
                </a>
                <div class="text-left px-2">
                  <span class="text-[9px] uppercase tracking-widest text-stone-400 block mb-1">
                    <?php echo ($item['categorie_id'] == 4) ? 'Souliers' : 'Prêt-à-porter'; ?>
                  </span>
                  <a href="<?php echo $detail_url; ?>" class="hover:text-stone-600 transition-colors">
                    <h3 class="product-name text-[11px] uppercase tracking-[0.2em] font-medium mb-2"><?php echo htmlspecialchars($item['nom']); ?></h3>
                  </a>
                  <span class="product-price text-[14px] font-light italic text-stone-500">
                    <?php echo number_format($item['prix'], 2); ?> <?php echo htmlspecialchars($item['devise'] ?? 'USD'); ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="swiper-button-next"></div>
          <div class="swiper-button-prev"></div>
          <div class="swiper-pagination"></div>
        </div>

        <?php if (empty($articles_hommes)): ?>
          <p class="text-center text-stone-400 italic py-20">Aucun article disponible pour le moment.</p>
        <?php endif; ?>

      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    new Swiper('.menCarousel', {
      slidesPerView: 1,
      spaceBetween: 30,
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
          slidesPerView: 4
        }
      }
    });
  </script>

<?php
  include '../includes/footer.php';
endif;
?>