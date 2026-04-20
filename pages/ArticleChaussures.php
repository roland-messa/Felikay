<?php
include '../config/db.php';

$genre_filter = $_GET['genre'] ?? 'tous';

try {
  // 1. On récupère les chaussures des 3 catégories (4, 5, 6)
  $sql = "SELECT * FROM produits WHERE categorie_id IN (4, 5, 6)";

  // 2. Application des filtres de genre/type
  if ($genre_filter === 'homme') {
    // Cible la catégorie chaussures hommes ou les mixtes
    $sql .= " AND (categorie_id = 4 OR genre = 'homme' OR genre = 'mixte')";
  } elseif ($genre_filter === 'femme') {
    // Cible la catégorie chaussures femmes ou les mixtes
    $sql .= " AND (categorie_id = 5 OR genre = 'femme' OR genre = 'mixte')";
  } elseif ($genre_filter === 'enfant') {
    // Cible spécifiquement la catégorie chaussures enfants (ID 6)
    $sql .= " AND categorie_id = 6";
  }

  $sql .= " ORDER BY created_at DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $articles_chaussures = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $articles_chaussures = [];
  error_log($e->getMessage());
}

// 2. AFFICHAGE DE LA PAGE
if (basename($_SERVER['PHP_SELF']) == 'ArticleChaussures.php') :
  $pageTitle = "Felikay | Souliers & Sneakers";
  include '../includes/header.php';
  include '../includes/navbar.php';
?>

  <main class="bg-[#FDFDFD] min-h-screen pt-32 pb-24">
    <div class="max-w-[1400px] mx-auto px-6">

      <header class="text-center mb-16">
        <span class="text-[10px] uppercase tracking-[0.5em] text-stone-400 block mb-4">La Marche de l'Élégance</span>
        <h1 class="font-serif text-5xl md:text-7xl italic">Nos Souliers</h1>

        <div class="flex justify-center flex-wrap gap-4 mt-12 text-[10px] uppercase tracking-widest">
          <a href="?genre=tous" class="px-6 py-2 border <?php echo ($genre_filter == 'tous') ? 'bg-black text-white border-black' : 'border-stone-200 text-stone-500'; ?> transition-all">Tous</a>
          <a href="?genre=homme" class="px-6 py-2 border <?php echo ($genre_filter == 'homme') ? 'bg-black text-white border-black' : 'border-stone-200 text-stone-500'; ?> transition-all">Homme</a>
          <a href="?genre=femme" class="px-6 py-2 border <?php echo ($genre_filter == 'femme') ? 'bg-black text-white border-black' : 'border-stone-200 text-stone-500'; ?> transition-all">Femme</a>
          <a href="?genre=enfant" class="px-6 py-2 border <?php echo ($genre_filter == 'enfant') ? 'bg-black text-white border-black' : 'border-stone-200 text-stone-500'; ?> transition-all">Enfant</a>
        </div>
      </header>

      <section>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <?php foreach ($articles_chaussures as $soulier) :
            $img_path = str_replace('../', '', $soulier['image_principale']);
            $final_img = "../" . $img_path;
          ?>
            <div class="product-card group">
              <div class="relative aspect-square overflow-hidden bg-[#F3F3F3] mb-6 border border-stone-50 shadow-sm">
                <img src="<?php echo $final_img; ?>" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-all flex items-end p-4">
                  <button onclick="addToCart(<?php echo $soulier['id']; ?>, '<?php echo addslashes($soulier['nom']); ?>', <?php echo $soulier['prix']; ?>, '<?php echo $final_img; ?>')"
                    class="w-full bg-white py-3 text-[10px] uppercase font-bold tracking-widest shadow-xl hover:bg-black hover:text-white transition transform translate-y-4 group-hover:translate-y-0">
                    Ajouter au panier
                  </button>
                </div>
              </div>
              <div class="text-center">
                <span class="text-[8px] uppercase tracking-widest text-stone-400 block mb-2">
                  <?php
                  if ($soulier['categorie_id'] == 6) echo "Enfant";
                  elseif ($soulier['categorie_id'] == 4) echo "Homme";
                  else echo "Femme";
                  ?>
                </span>
                <h3 class="text-[11px] uppercase tracking-[0.2em] font-medium mb-1"><?php echo htmlspecialchars($soulier['nom']); ?></h3>
                <p class="font-serif italic text-stone-500 text-[14px]"><?php echo number_format($soulier['prix'], 2); ?> <?php echo $soulier['devise'] ?? 'USD'; ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (empty($articles_chaussures)): ?>
          <div class="text-center py-20 text-stone-400 italic">
            Aucune paire de chaussures trouvée pour cette sélection.
          </div>
        <?php endif; ?>
      </section>

    </div>
  </main>

<?php
  include '../includes/footer.php';
endif;
?>