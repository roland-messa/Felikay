<?php
include '../config/db.php';
$pageTitle = "Felikay | Nos Promotions";
include '../includes/header.php';
include '../includes/navbar.php';

// On récupère TOUTES les promotions
try {
  $stmt = $pdo->query("SELECT * FROM produits WHERE is_promo = 1 ORDER BY created_at DESC");
  $all_promos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $all_promos = [];
  error_log($e->getMessage());
}
?>

<main class="bg-[#F9F9F9] min-h-screen pt-24">
  <div class="text-center py-20 bg-white">
    <p class="text-[10px] uppercase tracking-[0.5em] text-red-500 mb-4 font-bold">Prix d'Exception</p>
    <h1 class="font-serif text-5xl italic">Toutes les Promotions</h1>
  </div>

  <section class="max-w-[1400px] mx-auto px-6 py-16">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12">
      <?php foreach ($all_promos as $produit) :
        // Nettoyage du chemin d'image pour assurer la compatibilité
        $img_path = str_replace('../', '', $produit['image_principale']);
        $final_img = "../" . $img_path;
        $detail_url = "ArticleSeul.php?id=" . $produit['id'];
      ?>
        <div class="group">
          <a href="<?= $detail_url ?>" class="relative block aspect-[3/4] overflow-hidden bg-white mb-6 border border-gray-100 shadow-sm">
            <img src="<?= $final_img ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">

            <?php if (!empty($produit['promo_tag'])): ?>
              <span class="absolute top-4 right-4 bg-black text-white text-[9px] px-3 py-2 rounded-full uppercase font-bold z-10">
                <?= htmlspecialchars($produit['promo_tag']) ?>
              </span>
            <?php endif; ?>

            <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
              <button onclick="event.preventDefault(); event.stopPropagation(); addToCart(<?= $produit['id']; ?>, '<?= addslashes($produit['nom']); ?>', <?= $produit['prix']; ?>, '<?= $final_img; ?>')"
                class="bg-white p-4 rounded-full shadow-xl translate-y-4 group-hover:translate-y-0 transition-all hover:bg-black hover:text-white">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
              </button>
            </div>
          </a>

          <div class="text-center">
            <a href="<?= $detail_url ?>" class="hover:text-stone-600 transition-colors">
              <h3 class="text-[12px] uppercase tracking-wider font-medium"><?= htmlspecialchars($produit['nom']) ?></h3>
            </a>
            <p class="mt-2 font-serif text-[15px] italic text-red-600 font-semibold">
              <?= number_format($produit['prix'], 2) ?> <?= htmlspecialchars($produit['devise'] ?? 'USD') ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (empty($all_promos)): ?>
      <div class="text-center py-20">
        <p class="text-stone-400 italic">Aucune promotion disponible pour le moment. Revenez bientôt !</p>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include '../includes/footer.php'; ?>