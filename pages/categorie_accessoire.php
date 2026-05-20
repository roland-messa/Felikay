<?php

include '../config/db.php';

$type_filter = $_GET['type'] ?? 'tous';
// ID des accessoires dans la table categories
$cat_id = 4;

try {
  // 1. RÉCUPÉRATION DES ARTICLES
  $sql = "SELECT * FROM produits WHERE categorie_id = :cat_id";
  if ($type_filter !== 'tous') {
    // Recherche plus large dans le type, la description ou le nom
    $sql .= " AND (type_accessoire = :type OR description LIKE :type_like OR nom LIKE :type_like)";
  }
  $sql .= " ORDER BY created_at DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':cat_id', $cat_id, PDO::PARAM_INT);
  if ($type_filter !== 'tous') {
    $stmt->bindValue(':type', $type_filter);
    $stmt->bindValue(':type_like', '%' . $type_filter . '%');
  }
  $stmt->execute();
  $articles_accessoires = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 2. COMPTAGES DYNAMIQUES POUR LA SIDEBAR
  $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE categorie_id = :cat");
  $stmt_count->execute(['cat' => $cat_id]);
  $count_all = $stmt_count->fetchColumn();

  // Fonction helper pour compter par mot-clé
  function countByKeyword($pdo, $cat_id, $keyword)
  {
    $st = $pdo->prepare("SELECT COUNT(*) FROM produits WHERE categorie_id = :cat 
                             AND (type_accessoire = :k OR description LIKE :kl OR nom LIKE :kl)");
    $st->execute([
      'cat' => $cat_id,
      'k' => $keyword,
      'kl' => '%' . $keyword . '%'
    ]);
    return $st->fetchColumn();
  }

  $count_elec = countByKeyword($pdo, $cat_id, 'electronique');
  $count_perruque = countByKeyword($pdo, $cat_id, 'perruque');
  $count_parfum = countByKeyword($pdo, $cat_id, 'parfum');
} catch (PDOException $e) {
  $articles_accessoires = [];
  error_log($e->getMessage());
}

$pageTitle = "Felikay | Accessoires & Prestige";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="bg-[#FDFDFD] min-h-screen pt-32 pb-24">
  <div class="max-w-[1400px] mx-auto px-6">

    <header class="text-center mb-20">
      <span class="text-[10px] uppercase tracking-[0.5em] text-stone-400 block mb-4">Lifestyle & Accessoires</span>
      <h1 class="font-serif text-5xl md:text-7xl italic">L'Art du Détail</h1>
    </header>

    <div class="flex flex-col lg:flex-row gap-16">

      <!-- SIDEBAR -->
      <aside class="w-full lg:w-72">
        <div class="sticky top-40">
          <h3 class="text-[11px] font-bold uppercase tracking-[0.3em] mb-8 pb-4 border-b border-stone-100 text-black">
            Catégories
          </h3>
          <ul class="space-y-6 text-[12px] uppercase tracking-widest">
            <li>
              <a href="?type=tous" class="flex justify-between items-center group <?php echo ($type_filter == 'tous') ? 'text-black font-bold' : 'text-stone-400 hover:text-black'; ?> transition-colors">
                <span>Tout l'univers</span>
                <span class="text-[10px] bg-stone-100 px-2 py-1 rounded-full"><?php echo $count_all; ?></span>
              </a>
            </li>
            <li>
              <a href="?type=electronique" class="flex justify-between items-center group <?php echo ($type_filter == 'electronique') ? 'text-black font-bold' : 'text-stone-400 hover:text-black'; ?> transition-colors">
                <span>Électronique</span>
                <span class="text-[10px] bg-stone-100 px-2 py-1 rounded-full"><?php echo $count_elec; ?></span>
              </a>
            </li>
            <li>
              <a href="?type=perruque" class="flex justify-between items-center group <?php echo ($type_filter == 'perruque') ? 'text-black font-bold' : 'text-stone-400 hover:text-black'; ?> transition-colors">
                <span>Perruques</span>
                <span class="text-[10px] bg-stone-100 px-2 py-1 rounded-full"><?php echo $count_perruque; ?></span>
              </a>
            </li>
            <li>
              <a href="?type=parfum" class="flex justify-between items-center group <?php echo ($type_filter == 'parfum') ? 'text-black font-bold' : 'text-stone-400 hover:text-black'; ?> transition-colors">
                <span>Parfums</span>
                <span class="text-[10px] bg-stone-100 px-2 py-1 rounded-full"><?php echo $count_parfum; ?></span>
              </a>
            </li>
          </ul>
        </div>
      </aside>

      <!-- GRILLE PRODUITS -->
      <section class="flex-1">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-8 gap-y-16">
          <?php foreach ($articles_accessoires as $article) :

            // --- TRAITEMENT ROBUSTE DE L'IMAGE ---
            $image_raw = trim($article['image_principale']);
            $image_clean = ltrim($image_raw, './');

            // Vérification et reconstruction du chemin pour Wamp
            if (strpos($image_clean, 'assets/') === false) {
              $image_clean = 'assets/img/produits/' . $image_clean;
            }

            $final_img = '/ProjetFelykay/' . $image_clean;
            $detail_url = "ArticleSeul.php?id=" . $article['id'];
          ?>
            <div class="product-card group" data-aos="fade-up">
              <a href="<?php echo $detail_url; ?>" class="relative block aspect-square overflow-hidden bg-[#F3F3F3] mb-6 border border-stone-50">
                <img src="<?php echo htmlspecialchars($final_img); ?>"
                  class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110"
                  onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg';">

                <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                  <button onclick="event.preventDefault(); addToCart(<?php echo $article['id']; ?>, '<?php echo addslashes($article['nom']); ?>', <?php echo $article['prix']; ?>, '<?php echo $final_img; ?>', 'Unique', 'Standard')"
                    class="bg-white px-8 py-4 text-[10px] uppercase font-bold tracking-[0.2em] shadow-2xl hover:bg-black hover:text-white transition transform translate-y-4 group-hover:translate-y-0">
                    Ajouter au panier
                  </button>
                </div>
              </a>
              <div class="text-center">
                <a href="<?php echo $detail_url; ?>" class="hover:text-stone-600 transition-colors">
                  <h3 class="text-[11px] uppercase tracking-[0.2em] font-medium mb-2"><?php echo htmlspecialchars($article['nom']); ?></h3>
                </a>
                <p class="font-serif italic text-stone-500 text-[14px]">
                  <?php echo number_format($article['prix'], 2); ?> $
                </p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (empty($articles_accessoires)): ?>
          <div class="text-center py-40">
            <p class="text-stone-400 italic font-serif">Aucune pièce ne correspond à cette sélection.</p>
            <a href="?type=tous" class="text-[10px] uppercase tracking-widest underline mt-4 inline-block">Voir tout l'univers</a>
          </div>
        <?php endif; ?>
      </section>

    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>