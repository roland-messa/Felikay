<?php
// C:\wamp64\www\ProjetFelykay\pages\ArticleGadgets.php
include '../config/db.php';

$type_filter = $_GET['type'] ?? 'tous';

try {
  // On cible la catégorie 8 (Gadgets)
  $sql = "SELECT * FROM produits WHERE categorie_id = 8";
  $params = [];

  if ($type_filter !== 'tous') {
    // Recherche souple : dans la colonne type_accessoire OU la description OU le nom
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

$pageTitle = "Felikay | Gadgets & Lifestyle";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="bg-[#F5F5F5] min-h-screen pt-32">
  <section class="max-w-[1200px] mx-auto px-6">
    <!-- En-tête et Filtres -->
    <div class="bg-white p-12 mb-12 text-center shadow-sm">
      <h2 class="text-3xl font-light uppercase tracking-[0.5em] mb-6">Gadgets</h2>
      <div class="flex justify-center gap-10 text-[10px] font-bold tracking-widest text-stone-400">
        <a href="?type=tous" class="<?php echo ($type_filter === 'tous') ? 'text-black border-b-2 border-black' : ''; ?> pb-2 hover:text-black transition-all">Tout</a>
        <a href="?type=cuisine" class="<?php echo ($type_filter === 'cuisine') ? 'text-black border-b-2 border-black' : ''; ?> pb-2 hover:text-black transition-all">Cuisine</a>
        <a href="?type=electronique" class="<?php echo ($type_filter === 'electronique') ? 'text-black border-b-2 border-black' : ''; ?> pb-2 hover:text-black transition-all">Électronique</a>
        <a href="?type=divers" class="<?php echo ($type_filter === 'divers') ? 'text-black border-b-2 border-black' : ''; ?> pb-2 hover:text-black transition-all">Divers</a>
      </div>
    </div>

    <!-- Grille de Produits -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($articles as $item):
        // --- LOGIQUE INTELLIGENTE DES IMAGES ---
        $image_path = trim($item['image_principale']);

        // Nettoyage des préfixes habituels
        $image_path = str_replace('../', '', $image_path);
        $image_path = ltrim($image_path, '/');

        // Vérification et reconstruction du chemin pour WAMP
        if (strpos($image_path, 'assets/img/') === false) {
          $image_path = 'assets/img/produits/' . $image_path;
        }

        // URL absolue pour éviter les erreurs de dossier parent
        $final_img = '/ProjetFelykay/' . $image_path;
      ?>
        <div class="bg-white p-4 group shadow-sm hover:shadow-md transition-all duration-300 border border-transparent hover:border-stone-100">
          <a href="ArticleSeul.php?id=<?php echo $item['id']; ?>" class="block aspect-square overflow-hidden mb-4 bg-stone-50">
            <img src="<?php echo htmlspecialchars($final_img); ?>"
              alt="<?php echo htmlspecialchars($item['nom']); ?>"
              class="w-full h-full object-contain mix-blend-multiply group-hover:scale-110 transition duration-500"
              onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg';">
          </a>

          <div class="text-center md:text-left">
            <h3 class="text-[10px] uppercase font-medium text-stone-500 tracking-wider mb-2">
              <?php echo htmlspecialchars($item['nom']); ?>
            </h3>
            <p class="text-sm font-bold text-stone-900">
              <?php echo number_format($item['prix'], 2); ?> $
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (empty($articles)): ?>
      <div class="text-center py-24 bg-white shadow-sm border border-dashed border-stone-200">
        <p class="text-stone-400 italic font-serif text-lg">
          Aucun gadget ne correspond à cette sélection pour le moment.
        </p>
        <a href="?type=tous" class="inline-block mt-4 text-[10px] uppercase tracking-widest underline">Retour à la collection</a>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include '../includes/footer.php'; ?>