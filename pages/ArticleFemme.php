<?php
// C:\wamp64\www\ProjetFelykay\pages\ArticleFemme.php
include '../config/db.php';

// RÉCUPÉRATION DES FILTRES
$categorie_filter = $_GET['cat'] ?? 'tous';
$type_filter = $_GET['type'] ?? 'tous';

try {
  // Base : Catégorie 2 (Vêtements Femme) et 5 (Chaussures Femme)
  $sql = "SELECT * FROM produits WHERE categorie_id IN (2, 5) AND genre = 'femme'";
  $params = [];

  // Filtre par catégorie
  if ($categorie_filter === 'vetements') {
    $sql .= " AND categorie_id = 2";
  } elseif ($categorie_filter === 'chaussures') {
    $sql .= " AND categorie_id = 5";
  }

  // Filtre par type
  if ($type_filter !== 'tous') {
    $sql .= " AND (type_accessoire = :type OR description LIKE :type_like OR nom LIKE :type_like)";
    $params[':type'] = $type_filter;
    $params[':type_like'] = '%' . $type_filter . '%';
  }

  $sql .= " ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $articles_femmes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $articles_femmes = [];
}

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

  .nav-filter.active {
    color: black;
    font-weight: bold;
    border-bottom: 2px solid black;
  }
</style>

<main class="bg-[#F9F9F9] min-h-screen pt-20">
  <section class="py-24 px-6">
    <div class="max-w-[1400px] mx-auto text-center">
      <h2 class="font-serif text-4xl md:text-7xl mb-4 italic">Boutique Femmes</h2>
      <p class="text-[10px] uppercase tracking-[0.5em] text-gray-400 mb-12">Élégance contemporaine & silhouettes fluides</p>

      <div class="flex justify-center gap-10 mb-16 text-[11px] uppercase tracking-[0.2em] text-stone-400">
        <a href="?cat=tous" class="nav-filter <?php echo ($categorie_filter == 'tous') ? 'active' : ''; ?> pb-2">Tout l'univers</a>
        <a href="?cat=vetements" class="nav-filter <?php echo ($categorie_filter == 'vetements') ? 'active' : ''; ?> pb-2">Prêt-à-porter</a>
        <a href="?cat=chaussures" class="nav-filter <?php echo ($categorie_filter == 'chaussures') ? 'active' : ''; ?> pb-2">Chaussures</a>
      </div>

      <div class="swiper womenCarousel pb-12">
        <div class="swiper-wrapper">
          <?php foreach ($articles_femmes as $item) :
            // --- LOGIQUE INTELLIGENTE DES IMAGES ---
            $image_path = trim($item['image_principale']);
            $image_path = str_replace('../', '', $image_path);
            $image_path = ltrim($image_path, '/');

            if (strpos($image_path, 'assets/img/') === false) {
              $image_path = 'assets/img/produits/' . $image_path;
            }
            $final_img = '/ProjetFelykay/' . $image_path;
          ?>
            <div class="swiper-slide group">
              <a href="ArticleSeul.php?id=<?php echo $item['id']; ?>" class="relative block overflow-hidden mb-6 aspect-[3/4] bg-white border border-gray-100 shadow-sm">
                <img src="<?php echo htmlspecialchars($final_img); ?>"
                  class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                  onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg';">
              </a>
              <div class="text-center px-4">
                <span class="text-[8px] uppercase tracking-[0.3em] text-stone-400 block mb-2">
                  <?php echo ($item['categorie_id'] == 5) ? 'Collection Souliers' : 'Prêt-à-porter'; ?>
                </span>
                <h3 class="text-[11px] uppercase tracking-[0.2em] font-medium mb-2 text-stone-800"><?php echo htmlspecialchars($item['nom']); ?></h3>
                <span class="text-[14px] font-semibold italic text-stone-500"><?php echo number_format($item['prix'], 2); ?> $</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  new Swiper('.womenCarousel', {
    slidesPerView: 1,
    spaceBetween: 30,
    autoplay: {
      delay: 4000
    },
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
        slidesPerView: 3
      }
    }
  });
</script>
<?php include '../includes/footer.php'; ?>