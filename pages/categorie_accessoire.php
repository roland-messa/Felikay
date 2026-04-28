<?php

include '../config/db.php';

$type_filter = $_GET['type'] ?? 'tous';

try {
  // La catégorie ID pour les accessoires est 4
  $sql = "SELECT * FROM produits WHERE categorie_id = 4";

  if ($type_filter !== 'tous') {
    $sql .= " AND description LIKE :type";
  }

  $sql .= " ORDER BY created_at DESC";

  $stmt = $pdo->prepare($sql);
  if ($type_filter !== 'tous') {
    $stmt->bindValue(':type', '%' . $type_filter . '%');
  }

  $stmt->execute();
  $articles_accessoires = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Comptages pour la barre latérale
  $count_all = $pdo->query("SELECT COUNT(*) FROM produits WHERE categorie_id = 4")->fetchColumn();
  $count_elec = $pdo->query("SELECT COUNT(*) FROM produits WHERE categorie_id = 4 AND description LIKE '%electronique%'")->fetchColumn();
  $count_perruque = $pdo->query("SELECT COUNT(*) FROM produits WHERE categorie_id = 4 AND description LIKE '%perruque%'")->fetchColumn();
  $count_parfum = $pdo->query("SELECT COUNT(*) FROM produits WHERE categorie_id = 4 AND description LIKE '%parfum%'")->fetchColumn();
} catch (PDOException $e) {
  $articles_accessoires = [];
}

// 2. AFFICHAGE DE LA PAGE
if (basename($_SERVER['PHP_SELF']) == 'categorie_accessoire.php') :
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

        <aside class="w-full lg:w-72">
          <div class="sticky top-40">
            <h3 class="text-[11px] font-bold uppercase tracking-[0.3em] mb-8 pb-4 border-b border-stone-100">
              Catégories Accessoires
            </h3>
            <ul class="space-y-6 text-[12px] uppercase tracking-widest">
              <li>
                <a href="?type=tous" class="flex justify-between items-center group <?php echo ($type_filter == 'tous') ? 'text-black font-bold' : 'text-stone-400'; ?>">
                  <span>Tout l'univers</span>
                  <span class="text-[10px] bg-stone-100 px-2 py-1 rounded-full"><?php echo $count_all; ?></span>
                </a>
              </li>
              <li>
                <a href="?type=electronique" class="flex justify-between items-center group <?php echo ($type_filter == 'electronique') ? 'text-black font-bold' : 'text-stone-400'; ?>">
                  <span>Électronique</span>
                  <span class="text-[10px] bg-stone-100 px-2 py-1 rounded-full"><?php echo $count_elec; ?></span>
                </a>
              </li>
              <li>
                <a href="?type=perruque" class="flex justify-between items-center group <?php echo ($type_filter == 'perruque') ? 'text-black font-bold' : 'text-stone-400'; ?>">
                  <span>Perruques</span>
                  <span class="text-[10px] bg-stone-100 px-2 py-1 rounded-full"><?php echo $count_perruque; ?></span>
                </a>
              </li>
              <li>
                <a href="?type=parfum" class="flex justify-between items-center group <?php echo ($type_filter == 'parfum') ? 'text-black font-bold' : 'text-stone-400'; ?>">
                  <span>Parfums</span>
                  <span class="text-[10px] bg-stone-100 px-2 py-1 rounded-full"><?php echo $count_parfum; ?></span>
                </a>
              </li>
            </ul>
          </div>
        </aside>

        <section class="flex-1">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-8 gap-y-16">
            <?php foreach ($articles_accessoires as $article) :
              $img_path = str_replace('../', '', $article['image_principale']);
              $final_img = "../" . $img_path;
              $detail_url = "ArticleSeul.php?id=" . $article['id'];
            ?>
              <div class="product-card group">
                <a href="<?php echo $detail_url; ?>" class="relative block aspect-square overflow-hidden bg-[#F3F3F3] mb-6 border border-stone-50">
                  <img src="<?php echo $final_img; ?>" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
                  <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center">
                    <button onclick="event.preventDefault(); event.stopPropagation(); addToCart(<?php echo $article['id']; ?>, '<?php echo addslashes($article['nom']); ?>', <?php echo $article['prix']; ?>, '<?php echo $final_img; ?>')"
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
                    <?php echo number_format($article['prix'], 2); ?> <?php echo htmlspecialchars($article['devise'] ?? 'USD'); ?>
                  </p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if (empty($articles_accessoires)): ?>
            <p class="text-center py-20 text-stone-400 italic">Aucun accessoire trouvé dans cette catégorie.</p>
          <?php endif; ?>
        </section>

      </div>
    </div>
  </main>

<?php
  include '../includes/footer.php';
endif;
?>