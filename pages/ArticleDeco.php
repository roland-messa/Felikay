<?php
// C:\wamp64\www\ProjetFelykay\pages\ArticleDeco.php
include '../config/db.php';

$type_filter = $_GET['type'] ?? 'tous'; // Maison, Décoration intérieure, Evenements

try {
  // Catégorie 7 : Maison & Déco
  $sql = "SELECT * FROM produits WHERE categorie_id = 7";
  $params = [];

  if ($type_filter !== 'tous') {
    // Utilisation d'un LIKE pour plus de souplesse sur les termes comme "décoration intérieure"
    $sql .= " AND (type_accessoire LIKE :type OR nom LIKE :type_like)";
    $params[':type'] = $type_filter;
    $params[':type_like'] = '%' . $type_filter . '%';
  }

  $sql .= " ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $articles = [];
}

$pageTitle = "Felikay | Maison & Déco";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="bg-[#FDFDFD] min-h-screen pt-32">
  <section class="max-w-[1400px] mx-auto px-6">
    <!-- En-tête et Filtres -->
    <div class="flex flex-col items-center mb-16">
      <h2 class="font-serif text-4xl italic mb-4">Maison & Déco</h2>
      <div class="w-20 h-[1px] bg-stone-300 mb-8"></div>
      <div class="flex gap-8 text-[10px] uppercase tracking-widest text-stone-500">
        <a href="?type=tous" class="<?php echo ($type_filter === 'tous') ? 'text-black font-bold border-b border-black' : ''; ?> pb-1 hover:text-black transition-all">Tout</a>
        <a href="?type=maison" class="<?php echo ($type_filter === 'maison') ? 'text-black font-bold border-b border-black' : ''; ?> pb-1 hover:text-black transition-all">Maison</a>
        <a href="?type=decoration intérieure" class="<?php echo ($type_filter === 'decoration intérieure') ? 'text-black font-bold border-b border-black' : ''; ?> pb-1 hover:text-black transition-all">Décoration</a>
        <a href="?type=evenements" class="<?php echo ($type_filter === 'evenements') ? 'text-black font-bold border-b border-black' : ''; ?> pb-1 hover:text-black transition-all">Événements</a>
      </div>
    </div>

    <!-- Grille de Produits -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12">
      <?php if (!empty($articles)): ?>
        <?php foreach ($articles as $item):
          // --- LOGIQUE INTELLIGENTE DES IMAGES ---
          $image_path = trim($item['image_principale']);

          // Nettoyage des préfixes relatifs
          $image_path = str_replace('../', '', $image_path);
          $image_path = ltrim($image_path, '/');

          // Reconstruction du chemin vers le dossier produits
          if (strpos($image_path, 'assets/img/') === false) {
            $image_path = 'assets/img/produits/' . $image_path;
          }

          // URL absolue pour le projet local
          $final_img = '/ProjetFelykay/' . $image_path;
        ?>
          <div class="group">
            <a href="ArticleSeul.php?id=<?php echo $item['id']; ?>" class="block aspect-square overflow-hidden bg-stone-50 mb-6 border border-stone-100">
              <img src="<?php echo htmlspecialchars($final_img); ?>"
                alt="<?php echo htmlspecialchars($item['nom']); ?>"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-1000"
                onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg';">
            </a>
            <div class="flex justify-between items-center border-b border-stone-100 pb-4">
              <div>
                <h3 class="text-[11px] font-bold uppercase tracking-wider"><?php echo htmlspecialchars($item['nom']); ?></h3>
                <p class="text-[9px] text-stone-400 uppercase mt-1"><?php echo htmlspecialchars($item['type_accessoire'] ?? 'Objet de soin'); ?></p>
              </div>
              <p class="font-serif italic text-lg"><?php echo number_format($item['prix'], 2); ?> $</p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-span-full py-24 text-center">
          <p class="font-serif italic text-stone-400 text-xl">Aucune pièce de décoration disponible dans cette catégorie.</p>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>