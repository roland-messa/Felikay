<?php
include '../config/db.php';

// 1. RÉCUPÉRATION DES FILTRES
$age_filter = $_GET['age'] ?? 'tous';
$genre_filter = $_GET['genre'] ?? 'tous';

try {
  // On cible uniquement la catégorie 3 (Habits Enfants)
  $sql = "SELECT * FROM produits WHERE categorie_id = 3";

  // Filtre par tranche d'âge
  if ($age_filter !== 'tous') {
    $sql .= " AND tranche_age = :age";
  }

  // Filtre par genre (CORRIGÉ : inclut 'mixte' pour éviter les pages vides)
  if ($genre_filter !== 'tous') {
    $sql .= " AND (genre = :genre OR genre = 'mixte' OR genre = 'unisexe' OR genre = 'tous')";
  }

  $sql .= " ORDER BY created_at DESC";

  $stmt = $pdo->prepare($sql);

  if ($age_filter !== 'tous') {
    $stmt->bindValue(':age', $age_filter);
  }
  if ($genre_filter !== 'tous') {
    $stmt->bindValue(':genre', $genre_filter);
  }

  $stmt->execute();
  $articles_enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $articles_enfants = [];
  error_log($e->getMessage());
}

// 2. AFFICHAGE DE LA PAGE
if (basename($_SERVER['PHP_SELF']) == 'ArticleEnfants.php') :
  $pageTitle = "Felikay | Collection Enfants";
  include '../includes/header.php';
  include '../includes/navbar.php';

  $displayTitle = "Collection Enfant";
  if ($age_filter === 'Bébé (0-12 mois)') $displayTitle = "Univers Nouveau-né";
  if ($age_filter === 'Enfant (1-18 ans)') $displayTitle = "Collection Junior";
?>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <style>
    .swiper-button-next,
    .swiper-button-prev {
      color: #000;
      background: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .filter-link.active {
      color: black;
      font-weight: 700;
      border-bottom: 2px solid black;
    }
  </style>

  <main class="bg-[#F9F9F9] min-h-screen pt-28">
    <section class="max-w-[1400px] mx-auto px-6">

      <div class="flex flex-col items-center mb-16">
        <h2 class="font-serif text-4xl md:text-5xl mb-6 italic text-center"><?php echo $displayTitle; ?></h2>

        <div class="flex gap-8 text-[11px] uppercase tracking-[0.3em] text-stone-400">
          <a href="?age=<?php echo urlencode($age_filter); ?>&genre=tous"
            class="filter-link <?php echo ($genre_filter === 'tous') ? 'active' : ''; ?> pb-2 transition">Tous</a>

          <a href="?age=<?php echo urlencode($age_filter); ?>&genre=homme"
            class="filter-link <?php echo ($genre_filter === 'homme') ? 'active' : ''; ?> pb-2 transition">Garçons</a>

          <a href="?age=<?php echo urlencode($age_filter); ?>&genre=femme"
            class="filter-link <?php echo ($genre_filter === 'femme') ? 'active' : ''; ?> pb-2 transition">Filles</a>
        </div>
      </div>

      <div class="swiper childCarousel pb-20">
        <div class="swiper-wrapper">
          <?php foreach ($articles_enfants as $item) :
            $img_path = str_replace('../', '', $item['image_principale']);
            $final_img = "../" . $img_path;
          ?>
            <div class="swiper-slide group">
              <div class="relative aspect-[3/4] overflow-hidden bg-white border border-stone-100 mb-4">
                <img src="<?php echo $final_img; ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                  <button onclick="addToCart(<?php echo $item['id']; ?>, '<?php echo addslashes($item['nom']); ?>', <?php echo $item['prix']; ?>, '<?php echo $final_img; ?>')"
                    class="bg-white p-4 rounded-full shadow-xl translate-y-4 group-hover:translate-y-0 transition-all hover:bg-black hover:text-white">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                  </button>
                </div>
              </div>
              <div class="text-left px-2">
                <h3 class="text-[10px] uppercase tracking-widest font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($item['nom']); ?></h3>
                <p class="text-[13px] text-stone-500 italic font-semibold"><?php echo number_format($item['prix'], 2); ?> <?php echo $item['devise'] ?? 'USD'; ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="swiper-pagination"></div>
        <div class="swiper-button-next !hidden md:!flex"></div>
        <div class="swiper-button-prev !hidden md:!flex"></div>
      </div>

      <?php if (empty($articles_enfants)): ?>
        <div class="text-center py-20">
          <p class="text-stone-400 italic">Désolé, aucun vêtement ne correspond à cette sélection pour le moment.</p>
          <a href="?age=tous&genre=tous" class="text-[10px] uppercase underline tracking-widest mt-4 inline-block text-black">Voir toute la collection</a>
        </div>
      <?php endif; ?>

    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script>
    new Swiper('.childCarousel', {
      slidesPerView: 1,
      spaceBetween: 25,
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