<?php
// C:\wamp64\www\ProjetFelykay\pages\ArticleHomme.php
include '../config/db.php';

// 1. RÉCUPÉRATION DES FILTRES DEPUIS L'URL
$genre_filter = $_GET['genre'] ?? 'homme';
$style_filter = $_GET['style'] ?? 'tous';
$categorie_filter = $_GET['cat'] ?? 'tous';

try {
  // 2. CONSTRUCTION DE LA REQUÊTE DYNAMIQUE
  $sql = "SELECT * FROM produits WHERE genre = :genre";

  if ($style_filter !== 'tous') {
    $sql .= " AND (description LIKE :style OR nom LIKE :style)";
  }

  if ($categorie_filter === 'vetements') {
    $sql .= " AND categorie_id = 1";
  } elseif ($categorie_filter === 'chaussures') {
    $sql .= " AND categorie_id = 4";
  }

  $sql .= " ORDER BY created_at DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':genre', $genre_filter);

  if ($style_filter !== 'tous') {
    $stmt->bindValue(':style', '%' . $style_filter . '%');
  }

  $stmt->execute();
  $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $articles = [];
  error_log($e->getMessage());
}

$pageTitle = "Felikay | Collection " . ucfirst($genre_filter);
include '../includes/header.php';
include '../includes/navbar.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
  /* Correction de l'espacement pour la pagination (dots) */
  .menCarousel {
    padding-bottom: 70px !important;
    /* Crée l'espace sous les prix */
  }

  .swiper-pagination {
    bottom: 15px !important;
    /* Descend les points tout en bas */
  }

  .swiper-pagination-bullet {
    background: #d1d1d1;
    opacity: 1;
  }

  .swiper-pagination-bullet-active {
    background: #000;
    /* Noir pour le style Felykay */
  }

  /* Styles boutons navigation */
  .swiper-button-next,
  .swiper-button-prev {
    color: #000;
    background: white;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  }

  .nav-filter.active {
    color: black;
    font-weight: bold;
    border-bottom: 2px solid black;
  }
</style>

<main class="bg-[#F9F9F9] min-h-screen pt-20">
  <section class="py-24 px-6 overflow-hidden">
    <div class="max-w-[1400px] mx-auto">

      <!-- TITRE -->
      <div class="text-center mb-12">
        <h2 class="font-serif text-4xl md:text-6xl mb-4 italic">
          <?php echo ($genre_filter === 'femme') ? 'Boutique Femmes' : 'Boutique Hommes'; ?>
        </h2>
        <p class="text-[10px] uppercase tracking-[0.4em] text-gray-400">
          <?php
          echo ($style_filter !== 'tous')
            ? "Style : " . htmlspecialchars($style_filter)
            : "Coupes impeccables & matières premium";
          ?>
        </p>
      </div>

      <!-- FILTRES -->
      <div class="flex justify-center gap-8 mb-16 text-[11px] uppercase tracking-widest text-stone-400">
        <a href="?genre=<?php echo $genre_filter; ?>&cat=tous"
          class="nav-filter <?php echo ($categorie_filter == 'tous') ? 'active' : ''; ?> pb-2">Tout voir</a>
        <a href="?genre=<?php echo $genre_filter; ?>&cat=vetements"
          class="nav-filter <?php echo ($categorie_filter == 'vetements') ? 'active' : ''; ?> pb-2">Vêtements</a>
        <a href="?genre=<?php echo $genre_filter; ?>&cat=chaussures"
          class="nav-filter <?php echo ($categorie_filter == 'chaussures') ? 'active' : ''; ?> pb-2">Chaussures</a>
      </div>

      <!-- CAROUSEL -->
      <div class="swiper menCarousel">
        <div class="swiper-wrapper">
          <?php foreach ($articles as $item) :
            // Traitement robuste de l'image pour ProjetFelykay
            $image_raw = trim($item['image_principale']);
            $image_clean = ltrim(str_replace('../', '', $image_raw), '/');

            if (strpos($image_clean, 'assets/') === false) {
              $image_clean = 'assets/img/produits/' . $image_clean;
            }
            $final_img = '/ProjetFelykay/' . $image_clean;
          ?>
            <div class="swiper-slide group">
              <a href="ArticleSeul.php?id=<?php echo $item['id']; ?>"
                class="relative block overflow-hidden mb-6 aspect-[3/4] bg-white border border-gray-100 shadow-sm">
                <img src="<?php echo htmlspecialchars($final_img); ?>"
                  alt="<?php echo htmlspecialchars($item['nom']); ?>"
                  class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                  onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg';">
              </a>

              <div class="text-left px-2">
                <span class="text-[9px] uppercase tracking-widest text-stone-400 block mb-1">
                  <?php echo ($item['categorie_id'] == 4) ? 'Souliers' : 'Prêt-à-porter'; ?>
                </span>
                <h3 class="text-[11px] uppercase tracking-[0.2em] font-medium mb-2">
                  <?php echo htmlspecialchars($item['nom']); ?>
                </h3>
                <span class="text-[14px] font-light italic text-stone-500">
                  <?php echo number_format($item['prix'], 2); ?> $
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (empty($articles)): ?>
          <p class="text-center text-stone-400 italic py-20">Aucun article trouvé pour cette sélection.</p>
        <?php endif; ?>

        <!-- NAVIGATION ET PAGINATION -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  new Swiper('.menCarousel', {
    slidesPerView: 1,
    spaceBetween: 30,
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev"
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true
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

<?php include '../includes/footer.php'; ?>