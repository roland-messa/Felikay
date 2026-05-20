<?php
// C:\wamp64\www\ProjetFelykay\pages\ArticleSeul.php
include '../config/db.php';

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
  echo "<script>window.location.href='collection.php';</script>";
  exit;
}

try {
  // 1. RÉCUPÉRATION DU PRODUIT AVEC SA CATÉGORIE
  $stmt = $pdo->prepare("SELECT p.*, c.nom as cat_name, c.id as cat_id FROM produits p 
                           LEFT JOIN categories c ON p.categorie_id = c.id 
                           WHERE p.id = :id");
  $stmt->execute(['id' => $product_id]);
  $produit = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$produit) {
    include '../includes/header.php';
    include '../includes/navbar.php';
    echo "<div class='pt-40 text-center font-serif italic'>Cette pièce est actuellement indisponible.</div>";
    include '../includes/footer.php';
    exit;
  }

  // 2. DÉTERMINER LE LIEN DE RETOUR DYNAMIQUE
  $back_link = "collection.php";
  if (in_array($produit['cat_id'], [3, 6])) {
    $back_link = "ArticleEnfants.php?age=" . urlencode($produit['tranche_age']);
  } elseif (in_array($produit['cat_id'], [1, 2, 4, 5])) {
    $back_link = "ArticleHomme.php?genre=" . urlencode($produit['genre']);
  } elseif ($produit['cat_id'] == 8) {
    $back_link = "ArticleGadgets.php";
  }

  // 3. RÉCUPÉRATION DES TAILLES & COULEURS
  $stmt_sizes = $pdo->prepare("SELECT t.nom FROM tailles t INNER JOIN produit_tailles pt ON t.id = pt.taille_id WHERE pt.produit_id = :id");
  $stmt_sizes->execute(['id' => $product_id]);
  $tailles = $stmt_sizes->fetchAll(PDO::FETCH_COLUMN);

  $stmt_colors = $pdo->prepare("SELECT c.code_hex, c.nom FROM couleurs c INNER JOIN produit_couleurs pc ON c.id = pc.couleur_id WHERE pc.produit_id = :id");
  $stmt_colors->execute(['id' => $product_id]);
  $couleurs = $stmt_colors->fetchAll(PDO::FETCH_ASSOC);

  // 4. PRÉPARATION DE LA GALERIE D'IMAGES (LOGIQUE INTELLIGENTE)
  $galerie = [];
  $images_colonnes = ['image_principale', 'image_dos', 'image_gauche', 'image_droite'];

  foreach ($images_colonnes as $col) {
    if (!empty($produit[$col])) {
      $path = trim($produit[$col]);

      // Nettoyage des préfixes
      $path = str_replace('../', '', $path);
      $path = ltrim($path, '/');

      // Vérification du dossier assets/img
      if (strpos($path, 'assets/img/') === false) {
        $path = 'assets/img/produits/' . $path;
      }

      // Construction de l'URL finale pour l'affichage
      $galerie[] = "/ProjetFelykay/" . $path;
    }
  }
} catch (PDOException $e) {
  die("Erreur base de données : " . $e->getMessage());
}

$pageTitle = "Felikay | " . $produit['nom'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>
  .size-btn.active {
    border-color: black;
    background: black;
    color: white;
  }

  .color-ring.active {
    outline: 2px solid black;
    outline-offset: 3px;
  }

  .thumb-active {
    border-color: black !important;
    opacity: 1 !important;
  }

  #mainView {
    transition: opacity 0.3s ease-in-out;
  }

  .scrollbar-hide::-webkit-scrollbar {
    display: none;
  }

  #toast-container {
    position: fixed;
    top: 100px;
    right: 24px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    pointer-events: none;
  }

  .toast {
    pointer-events: auto;
    background: black;
    color: white;
    padding: 16px 24px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-weight: 600;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 280px;
    transform: translateX(120%);
    transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  }

  .toast.show {
    transform: translateX(0);
  }

  .toast-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fee2e2;
  }
</style>

<div id="toast-container"></div>

<main class="pt-32 pb-24 bg-white">
  <div class="max-w-[1400px] mx-auto px-6">
    <nav class="text-[10px] uppercase tracking-[0.2em] text-stone-400 mb-12 flex gap-2">
      <a href="../index.php" class="hover:text-black">Accueil</a> <span>/</span>
      <a href="<?php echo $back_link; ?>" class="hover:text-black"><?php echo htmlspecialchars($produit['cat_name'] ?? 'Collection'); ?></a> <span>/</span>
      <span class="text-black font-bold"><?php echo htmlspecialchars($produit['nom']); ?></span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-16">
      <div class="w-full lg:w-3/5 flex flex-col-reverse md:flex-row gap-4">
        <div class="flex md:flex-col gap-3 overflow-x-auto md:max-h-[600px] scrollbar-hide">
          <?php foreach ($galerie as $i => $img_url): ?>
            <div onclick="updateGallery('<?php echo $img_url; ?>', this)"
              class="thumb-img cursor-pointer w-20 h-24 md:w-24 md:h-32 flex-shrink-0 border <?php echo $i == 0 ? 'thumb-active' : 'opacity-50'; ?> transition-all">
              <img src="<?php echo $img_url; ?>" class="w-full h-full object-cover" onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">
            </div>
          <?php endforeach; ?>
        </div>
        <div class="flex-1 bg-stone-50 aspect-[4/5] overflow-hidden border border-stone-100">
          <img id="mainView" src="<?php echo $galerie[0]; ?>" class="w-full h-full object-cover" onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">
        </div>
      </div>

      <div class="w-full lg:w-2/5 space-y-10">
        <header>
          <h1 class="font-serif text-5xl italic mb-4 leading-tight"><?php echo htmlspecialchars($produit['nom']); ?></h1>
          <p class="text-2xl font-light tracking-widest text-stone-900"><?php echo number_format($produit['prix'], 2); ?> $</p>
        </header>

        <div class="text-stone-500 text-sm leading-relaxed font-light">
          <h4 class="text-[11px] uppercase font-bold text-black tracking-widest mb-3">L'histoire de la pièce</h4>
          <p><?php echo nl2br(htmlspecialchars($produit['description'])); ?></p>
        </div>

        <?php if (!empty($couleurs)): ?>
          <div>
            <h4 class="text-[11px] uppercase font-bold tracking-widest mb-4 text-black">Palette disponible</h4>
            <div class="flex gap-4">
              <?php foreach ($couleurs as $c): ?>
                <button onclick="selectVariant(this, 'color')" data-val="<?php echo $c['nom']; ?>"
                  class="color-ring w-8 h-8 rounded-full border border-stone-200 transition-all"
                  style="background-color: <?php echo $c['code_hex']; ?>;" title="<?php echo $c['nom']; ?>">
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($tailles)): ?>
          <div>
            <div class="flex justify-between mb-4">
              <h4 class="text-[11px] uppercase font-bold tracking-widest text-black">Taille</h4>
              <button class="text-[10px] underline text-stone-400 uppercase tracking-tighter">Guide des tailles</button>
            </div>
            <div class="flex flex-wrap gap-2">
              <?php foreach ($tailles as $t): ?>
                <button onclick="selectVariant(this, 'size')" data-val="<?php echo $t; ?>"
                  class="size-btn min-w-[3.5rem] h-12 border border-stone-200 text-[10px] tracking-widest uppercase transition-all">
                  <?php echo $t; ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="pt-6">
          <button onclick="addToCartComplete()" class="w-full bg-black text-white py-6 text-[11px] uppercase font-bold tracking-[0.4em] hover:bg-stone-800 transition-all">
            Ajouter à la sélection
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  function updateGallery(src, el) {
    const main = document.getElementById('mainView');
    main.style.opacity = '0';
    setTimeout(() => {
      main.src = src;
      main.style.opacity = '1';
    }, 250);
    document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('thumb-active'));
    el.classList.add('thumb-active');
  }

  function showNotification(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const icon = type === 'success' ? '✓' : '✕';
    const errorClass = type === 'error' ? 'toast-error' : '';
    toast.className = `toast ${errorClass}`;
    toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 500);
    }, 3000);
  }

  function selectVariant(el, type) {
    const selector = type === 'size' ? '.size-btn' : '.color-ring';
    document.querySelectorAll(selector).forEach(b => b.classList.remove('active'));
    el.classList.add('active');
  }

  function addToCartComplete() {
    const sizesAvailable = document.querySelectorAll('.size-btn').length > 0;
    const selectedSize = document.querySelector('.size-btn.active')?.dataset.val;

    if (sizesAvailable && !selectedSize) {
      showNotification('Veuillez sélectionner une taille', 'error');
      return;
    }

    const size = selectedSize || 'Unique';
    const color = document.querySelector('.color-ring.active')?.dataset.val || 'Standard';

    if (typeof addToCart === "function") {
      addToCart(
        <?php echo $produit['id']; ?>,
        '<?php echo addslashes($produit['nom']); ?>',
        <?php echo $produit['prix']; ?>,
        '<?php echo $galerie[0]; ?>',
        size,
        color
      );
      showNotification('Article ajouté à votre sélection');
    } else {
      showNotification('Erreur : Panier non chargé', 'error');
    }
  }
</script>

<?php include '../includes/footer.php'; ?>