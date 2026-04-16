<?php
$pageTitle = "Felikay | Détail Produit";
include '../includes/header.php';
include '../includes/navbar.php';
include '../config/db.php';

// 1. RÉCUPÉRATION DU PRODUIT VIA L'ID
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
  header('Location: collection.php'); // Redirection si pas d'ID
  exit;
}

try {
  // On récupère le produit et le nom de sa catégorie
  $stmt = $pdo->prepare("SELECT p.*, c.nom as cat_name FROM produits p 
                           LEFT JOIN categories c ON p.categorie_id = c.id 
                           WHERE p.id = :id");
  $stmt->execute(['id' => $product_id]);
  $produit = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$produit) {
    echo "Produit introuvable.";
    exit;
  }

  // On récupère aussi des produits similaires (même catégorie) pour la suggestion
  $simstmt = $pdo->prepare("SELECT * FROM produits WHERE categorie_id = :cid AND id != :id LIMIT 4");
  $simstmt->execute(['cid' => $produit['categorie_id'], 'id' => $product_id]);
  $suggestions = $simstmt->fetchAll(PDO::FETCH_ASSOC);

  $final_img = "../" . str_replace('../', '', $produit['image_principale']);
} catch (PDOException $e) {
  die("Erreur : " . $e->getMessage());
}
?>

<main class="pt-32 pb-24 bg-white">
  <div class="max-w-[1400px] mx-auto px-6">

    <nav class="text-[10px] uppercase tracking-widest text-stone-400 mb-12">
      <a href="index.php" class="hover:text-black">Accueil</a> /
      <a href="collection.php" class="hover:text-black">Collections</a> /
      <span class="text-black font-bold"><?php echo $produit['nom']; ?></span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-16">

      <div class="w-full lg:w-3/5">
        <div class="bg-[#F5F5F5] aspect-[4/5] overflow-hidden border border-stone-100 shadow-sm">
          <img src="<?php echo $final_img; ?>" class="w-full h-full object-cover">
        </div>
        <div class="grid grid-cols-4 gap-4 mt-4">
          <div class="aspect-square bg-[#F5F5F5] opacity-50 hover:opacity-100 cursor-pointer border border-stone-100">
            <img src="<?php echo $final_img; ?>" class="w-full h-full object-cover">
          </div>
        </div>
      </div>

      <div class="w-full lg:w-2/5 space-y-8">
        <div>
          <span class="text-[10px] uppercase tracking-[0.4em] text-stone-400 block mb-2"><?php echo $produit['cat_name']; ?></span>
          <h1 class="font-serif text-4xl italic mb-4"><?php echo $produit['nom']; ?></h1>
          <p class="text-2xl font-light tracking-widest"><?php echo number_format($produit['prix'], 2); ?> <?php echo $produit['devise']; ?></p>
        </div>

        <div class="border-t border-b border-stone-100 py-8">
          <h4 class="text-[11px] uppercase font-bold tracking-widest mb-4">Description</h4>
          <p class="text-stone-500 text-sm leading-relaxed">
            <?php echo nl2br($produit['description']); ?>
          </p>
        </div>

        <div>
          <h4 class="text-[11px] uppercase font-bold tracking-widest mb-4">Sélectionner la Taille</h4>
          <div class="flex gap-3">
            <?php foreach (['XS', 'S', 'M', 'L', 'XL'] as $taille): ?>
              <button class="w-12 h-12 border border-stone-200 text-[10px] flex items-center justify-center hover:border-black hover:bg-black hover:text-white transition-all">
                <?php echo $taille; ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="pt-6">
          <button onclick="addToCart('<?php echo addslashes($produit['nom']); ?>', <?php echo $produit['prix']; ?>, '<?php echo $final_img; ?>')"
            class="w-full bg-black text-white py-5 text-[11px] uppercase font-bold tracking-[0.3em] hover:bg-stone-800 transition-all flex items-center justify-center gap-4 shadow-2xl">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Ajouter au panier
          </button>
          <p class="text-[9px] text-center text-stone-400 mt-4 uppercase tracking-widest italic">
            Livraison offerte pour toute commande Maison Felikay
          </p>
        </div>
      </div>
    </div>

    <section class="mt-32">
      <h3 class="font-serif text-3xl italic text-center mb-16">Vous aimerez aussi</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php foreach ($suggestions as $suggest):
          $simg = "../" . str_replace('../', '', $suggest['image_principale']);
        ?>
          <div class="group cursor-pointer" onclick="window.location.href='ArticleSeul.php?id=<?php echo $suggest['id']; ?>'">
            <div class="aspect-[3/4] bg-[#F5F5F5] mb-4 overflow-hidden border border-stone-50">
              <img src="<?php echo $simg; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            </div>
            <h4 class="text-[11px] uppercase tracking-wider text-center"><?php echo $suggest['nom']; ?></h4>
            <p class="text-center font-serif italic text-stone-400 text-sm mt-1"><?php echo number_format($suggest['prix'], 2); ?> $</p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

  </div>
</main>

<?php include '../includes/footer.php'; ?>