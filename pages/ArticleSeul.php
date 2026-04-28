<?php
$pageTitle = "Felikay | Détail Produit";
include '../includes/header.php';
include '../includes/navbar.php';
include '../config/db.php';

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
  header('Location: collection.php');
  exit;
}

try {
  // 1. RÉCUPÉRATION DES INFOS DU PRODUIT (On inclut les colonnes d'images)
  $stmt = $pdo->prepare("SELECT p.*, c.nom as cat_name FROM produits p 
                           LEFT JOIN categories c ON p.categorie_id = c.id 
                           WHERE p.id = :id");
  $stmt->execute(['id' => $product_id]);
  $produit = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$produit) {
    echo "Produit introuvable.";
    exit;
  }

  // 2. RÉCUPÉRATION DYNAMIQUE DES TAILLES
  $stmt_sizes = $pdo->prepare("SELECT t.nom FROM tailles t 
                                 INNER JOIN produit_tailles pt ON t.id = pt.taille_id 
                                 WHERE pt.produit_id = :id");
  $stmt_sizes->execute(['id' => $product_id]);
  $tailles = $stmt_sizes->fetchAll(PDO::FETCH_COLUMN);

  // 3. RÉCUPÉRATION DYNAMIQUE DES COULEURS
  $stmt_colors = $pdo->prepare("SELECT c.code_hex, c.nom FROM couleurs c 
                                  INNER JOIN produit_couleurs pc ON c.id = pc.couleur_id 
                                  WHERE pc.produit_id = :id");
  $stmt_colors->execute(['id' => $product_id]);
  $couleurs = $stmt_colors->fetchAll(PDO::FETCH_ASSOC);

  // 4. GESTION DES IMAGES (CORRECTION ICI)
  // On crée un tableau avec l'image principale
  $toutes_les_vues = [$produit['image_principale']];

  // On ajoute les autres vues seulement si elles ne sont pas vides
  if (!empty($produit['image_dos'])) $toutes_les_vues[] = $produit['image_dos'];
  if (!empty($produit['image_gauche'])) $toutes_les_vues[] = $produit['image_gauche'];
  if (!empty($produit['image_droite'])) $toutes_les_vues[] = $produit['image_droite'];

  $final_img = "../" . str_replace('../', '', $produit['image_principale']);
} catch (PDOException $e) {
  die("Erreur : " . $e->getMessage());
}
?>

<style>
  .size-btn.active {
    border-color: black;
    background-color: black;
    color: white;
  }

  .color-ring.active {
    outline: 2px solid black;
    outline-offset: 2px;
    transform: scale(1.1);
  }

  .thumb-card.active {
    border-color: black;
    opacity: 1 !important;
  }

  .scrollbar-hide::-webkit-scrollbar {
    display: none;
  }
</style>

<main class="pt-32 pb-24 bg-white">
  <div class="max-w-[1400px] mx-auto px-6">
    <nav class="text-[10px] uppercase tracking-widest text-stone-400 mb-12">
      <a href="/ProjetFelykay/index.php" class="hover:text-black">Accueil</a> /
      <a href="collection.php" class="hover:text-black">Collections</a> /
      <span class="text-black font-bold"><?php echo htmlspecialchars($produit['nom']); ?></span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-16">
      <div class="w-full lg:w-3/5 flex flex-col-reverse md:flex-row gap-4">
        <div class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto max-h-[600px] scrollbar-hide">
          <?php foreach ($toutes_les_vues as $index => $vue):
            $path = "../" . str_replace('../', '', $vue); ?>
            <div onclick="changeView('<?php echo $path; ?>', this)"
              class="thumb-card cursor-pointer w-20 h-24 md:w-24 md:h-32 flex-shrink-0 border-2 <?php echo $index === 0 ? 'active border-black' : 'border-transparent opacity-60'; ?> transition-all overflow-hidden bg-gray-50">
              <img src="<?php echo $path; ?>" class="w-full h-full object-cover">
            </div>
          <?php endforeach; ?>
        </div>
        <div class="flex-1 bg-[#F5F5F5] aspect-[4/5] overflow-hidden border border-stone-100 shadow-sm">
          <img id="mainView" src="<?php echo $final_img; ?>" class="w-full h-full object-cover transition-opacity duration-300">
        </div>
      </div>

      <div class="w-full lg:w-2/5 space-y-8">
        <div>
          <span class="text-[10px] uppercase tracking-[0.4em] text-stone-400 block mb-2"><?php echo htmlspecialchars($produit['cat_name']); ?></span>
          <h1 class="font-serif text-4xl italic mb-4"><?php echo htmlspecialchars($produit['nom']); ?></h1>
          <p class="text-2xl font-light tracking-widest"><?php echo number_format($produit['prix'], 2); ?> $</p>
        </div>

        <div class="border-t border-stone-100 pt-8">
          <h4 class="text-[11px] uppercase font-bold tracking-widest mb-4">Description</h4>
          <p class="text-stone-500 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($produit['description'])); ?></p>
        </div>

        <?php if (!empty($couleurs)): ?>
          <div>
            <h4 class="text-[11px] uppercase font-bold tracking-widest mb-4">Couleurs</h4>
            <div class="flex gap-3">
              <?php foreach ($couleurs as $c): ?>
                <button onclick="selectColor(this, '<?php echo htmlspecialchars($c['nom']); ?>')"
                  class="color-ring w-8 h-8 rounded-full border border-stone-200 transition-all"
                  style="background-color: <?php echo $c['code_hex']; ?>;"
                  title="<?php echo htmlspecialchars($c['nom']); ?>">
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div>
          <h4 class="text-[11px] uppercase font-bold tracking-widest mb-4">Tailles</h4>
          <div class="flex flex-wrap gap-3">
            <?php if (!empty($tailles)): ?>
              <?php foreach ($tailles as $t): ?>
                <button onclick="selectSize(this)" class="size-btn min-w-[3rem] h-12 border border-stone-200 text-[10px] flex items-center justify-center px-4 hover:border-black transition-all">
                  <?php echo htmlspecialchars($t); ?>
                </button>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-[10px] text-stone-400 italic">Taille unique</p>
            <?php endif; ?>
          </div>
        </div>

        <div class="pt-6">
          <button onclick="handleProductAddToCart()"
            class="w-full bg-black text-white py-5 text-[11px] uppercase font-bold tracking-[0.3em] hover:bg-stone-800 transition-all flex items-center justify-center gap-4">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Ajouter au panier
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  function changeView(src, thumb) {
    const main = document.getElementById('mainView');
    main.style.opacity = '0';
    setTimeout(() => {
      main.src = src;
      main.style.opacity = '1';
    }, 200);
    document.querySelectorAll('.thumb-card').forEach(t => t.classList.remove('active', 'border-black'));
    thumb.classList.add('active', 'border-black');
  }

  function selectSize(btn) {
    btn.classList.toggle('active');
  }

  function selectColor(btn, name) {
    btn.classList.toggle('active');
    btn.dataset.name = name;
  }

  function handleProductAddToCart() {
    const selectedSizes = document.querySelectorAll('.size-btn.active');
    const selectedColors = document.querySelectorAll('.color-ring.active');

    let sizes = selectedSizes.length > 0 ? Array.from(selectedSizes).map(b => b.innerText.trim()) : ['Unique'];
    let colors = selectedColors.length > 0 ? Array.from(selectedColors).map(b => b.dataset.name) : ['Standard'];

    sizes.forEach(s => {
      colors.forEach(c => {
        addToCart(
          <?php echo $produit['id']; ?>,
          '<?php echo addslashes($produit['nom']); ?>',
          <?php echo $produit['prix']; ?>,
          '<?php echo $final_img; ?>',
          s,
          c
        );
      });
    });
    showToast("Ajouté au panier !");
  }
</script>
<?php include '../includes/footer.php'; ?>