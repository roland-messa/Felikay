<?php
// C:\wamp64\www\ProjetFelykay\pages\ArticleAdultes.php
include '../config/db.php';

$genre_filter = $_GET['genre'] ?? 'tous';
$type_filter  = $_GET['type'] ?? 'tous';

try {
  // Sélection des catégories : 1 (Vêtements Homme), 2 (Vêtements Femme), 4 (Chaussures Homme), 5 (Chaussures Femme)
  $sql = "SELECT * FROM produits WHERE categorie_id IN (1, 2, 4, 5)";
  $params = [];

  if ($genre_filter !== 'tous') {
    $sql .= " AND genre = :genre";
    $params[':genre'] = $genre_filter;
  }

  if ($type_filter !== 'tous') {
    $sql .= " AND (type_accessoire = :type OR description LIKE :type_like OR nom LIKE :type_like)";
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

$pageTitle = "Felikay | Collection Adultes";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="bg-[#FDFDFD] min-h-screen pt-32">
  <section class="max-w-[1400px] mx-auto px-6">
    <div class="flex flex-col items-center mb-16">
      <span class="text-[9px] uppercase tracking-[0.4em] text-stone-400 mb-2">Prêt-à-porter</span>
      <h2 class="font-serif text-4xl italic text-center uppercase tracking-widest">
        <?php echo ($genre_filter === 'femme') ? 'Collection Femme' : (($genre_filter === 'homme') ? 'Collection Homme' : 'Univers Adultes'); ?>
      </h2>

      <!-- Filtres de navigation -->
      <div class="flex gap-6 mt-6 text-[10px] uppercase tracking-widest text-stone-400">
        <a href="?genre=<?php echo $genre_filter; ?>&type=tous" class="<?php echo ($type_filter === 'tous') ? 'text-black font-bold border-b border-black' : ''; ?> pb-1 hover:text-black transition-all">Tout</a>
        <a href="?genre=<?php echo $genre_filter; ?>&type=quotidien" class="<?php echo ($type_filter === 'quotidien') ? 'text-black font-bold border-b border-black' : ''; ?> pb-1 hover:text-black transition-all">Quotidien</a>
        <a href="?genre=<?php echo $genre_filter; ?>&type=soir" class="<?php echo ($type_filter === 'soir') ? 'text-black font-bold border-b border-black' : ''; ?> pb-1 hover:text-black transition-all">Soir</a>
        <a href="?genre=<?php echo $genre_filter; ?>&type=evenement" class="<?php echo ($type_filter === 'evenement') ? 'text-black font-bold border-b border-black' : ''; ?> pb-1 hover:text-black transition-all">Événements</a>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
      <?php if (!empty($articles)): ?>
        <?php foreach ($articles as $item):
          // --- LOGIQUE INTELLIGENTE DES IMAGES ---
          $image_path = trim($item['image_principale']);

          // Nettoyage des préfixes habituels (../ ou ./)
          $image_path = str_replace(['../', './'], '', $image_path);
          $image_path = ltrim($image_path, '/');

          // Si le chemin ne contient pas déjà "assets/img/", on l'ajoute
          if (strpos($image_path, 'assets/img/') === false) {
            $image_path = 'assets/img/produits/' . $image_path;
          }

          // Construction du chemin absolu pour Wamp64
          $final_img = '/ProjetFelykay/' . $image_path;
        ?>
          <div class="group text-center">
            <a href="ArticleSeul.php?id=<?php echo $item['id']; ?>" class="block aspect-[3/4] overflow-hidden bg-stone-50 mb-4 border border-stone-100 shadow-sm group-hover:shadow-md transition-all">
              <img src="<?php echo htmlspecialchars($final_img); ?>"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
                onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg';">
            </a>
            <h3 class="text-[10px] font-bold uppercase tracking-widest text-stone-800"><?php echo htmlspecialchars($item['nom']); ?></h3>
            <p class="text-stone-500 italic text-[13px] mt-1"><?php echo number_format($item['prix'], 2); ?> $</p>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-span-full py-24 text-center">
          <p class="font-serif italic text-stone-400 text-xl">Aucune pièce disponible pour cette sélection actuellement.</p>
          <a href="?genre=tous&type=tous" class="inline-block mt-4 text-[10px] uppercase tracking-widest underline">Voir toute la collection</a>
        </div>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>