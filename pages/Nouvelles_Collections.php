<?php
// 1. CONNEXION ET RÉCUPÉRATION DES NOUVEAUTÉS
include '../config/db.php';

$univers_filter = $_GET['univers'] ?? 'tous';

try {
  // On récupère les 12 derniers produits ajoutés à la base de données
  $sql = "SELECT p.*, c.nom as cat_name 
            FROM produits p 
            LEFT JOIN categories c ON p.categorie_id = c.id";

  $conditions = [];

  // Filtrage par univers (basé sur les IDs de ta table categories)
  if ($univers_filter === 'femme') {
    $conditions[] = "p.categorie_id = 2";
  } elseif ($univers_filter === 'homme') {
    $conditions[] = "p.categorie_id = 1";
  } elseif ($univers_filter === 'enfant') {
    $conditions[] = "p.categorie_id = 3";
  }

  if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
  }

  $sql .= " ORDER BY p.created_at DESC LIMIT 12";

  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $nouveautes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $nouveautes = [];
  error_log($e->getMessage());
}

// 2. AFFICHAGE DE LA PAGE
if (basename($_SERVER['PHP_SELF']) == 'Nouvelles_Collections.php') :
  $pageTitle = "Felikay | Les Nouveautés";
  include '../includes/header.php';
  include '../includes/navbar.php';
?>

  <main class="bg-[#F9F9F9] min-h-screen pt-12">
    <header class="py-20 text-center bg-white border-b border-gray-50">
      <p class="text-[10px] uppercase tracking-[0.5em] text-gray-400 mb-4">Saison 2026</p>
      <h1 class="font-serif text-5xl md:text-7xl italic mb-10">Les Nouveautés</h1>

      <div class="flex justify-center space-x-8 text-[11px] uppercase tracking-widest font-medium">
        <a href="?univers=tous" class="<?php echo ($univers_filter == 'tous') ? 'border-b border-black text-black' : 'text-gray-400'; ?> pb-1 hover:text-black transition-colors">Tout voir</a>
        <a href="?univers=femme" class="<?php echo ($univers_filter == 'femme') ? 'border-b border-black text-black' : 'text-gray-400'; ?> pb-1 hover:text-black transition-colors">Femme</a>
        <a href="?univers=homme" class="<?php echo ($univers_filter == 'homme') ? 'border-b border-black text-black' : 'text-gray-400'; ?> pb-1 hover:text-black transition-colors">Homme</a>
        <a href="?univers=enfant" class="<?php echo ($univers_filter == 'enfant') ? 'border-b border-black text-black' : 'text-gray-400'; ?> pb-1 hover:text-black transition-colors">Enfant</a>
      </div>
    </header>

    <section class="max-w-[1400px] mx-auto px-6 py-16">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-8 gap-y-16">

        <?php foreach ($nouveautes as $produit) :
          // Nettoyage et sécurisation du chemin d'image
          $img_path = str_replace('../', '', $produit['image_principale']);
          $final_img = "../" . $img_path;
        ?>
          <div class="product-item group cursor-pointer" data-aos="fade-up">
            <div class="relative aspect-[3/4] overflow-hidden bg-white mb-6 border border-gray-100 shadow-sm">
              <img src="<?php echo $final_img; ?>" class="product-img w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110">
              <span class="absolute top-4 left-4 bg-black text-white text-[9px] px-3 py-1 uppercase tracking-widest z-10 font-bold">Nouveau</span>

              <div class="absolute inset-x-4 bottom-6 space-y-2 opacity-0 transform translate-y-4 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-500 z-20">
                <button onclick="addToCart(<?php echo $produit['id']; ?>, '<?php echo addslashes($produit['nom']); ?>', <?php echo $produit['prix']; ?>, '<?php echo $final_img; ?>')"
                  class="w-full bg-white text-black text-[10px] uppercase tracking-widest py-4 hover:bg-black hover:text-white transition-colors flex items-center justify-center gap-2 shadow-xl">
                  <i data-lucide="shopping-bag" class="w-4 h-4"></i> Ajouter au panier
                </button>
              </div>
            </div>
            <div class="text-center">
              <p class="text-[9px] text-gray-400 uppercase tracking-widest mb-2">Collection <?php echo $produit['cat_name']; ?></p>
              <h3 class="product-name text-[12px] uppercase tracking-wider font-medium"><?php echo $produit['nom']; ?></h3>
              <p class="product-price mt-2 font-serif text-[15px] italic text-stone-600">
                <?php echo number_format($produit['prix'], 2); ?> <?php echo $produit['devise'] ?? '$'; ?>
              </p>
            </div>
          </div>
        <?php endforeach; ?>

      </div>

      <?php if (empty($nouveautes)): ?>
        <p class="text-center py-20 text-stone-400 italic">Aucune nouveauté n'est disponible pour le moment.</p>
      <?php endif; ?>
    </section>
  </main>

<?php
  include '../includes/footer.php';
endif;
?>