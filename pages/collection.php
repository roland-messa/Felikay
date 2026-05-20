<?php
session_start();
$pageTitle = "Felikay | Collections";
$isSecondaryPage = true;

include '../includes/header.php';
include '../includes/navbar.php';
include '../config/db.php';

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$articlesPerPage = 12;
$offset = ($currentPage - 1) * $articlesPerPage;

$cat_filter    = $_GET['cat'] ?? 'all';
$color_filter  = $_GET['color'] ?? 'all';
$genre_filter  = $_GET['genre'] ?? 'all';
$age_filter    = $_GET['age'] ?? 'all';
$type_filter   = $_GET['type'] ?? 'all';

function filterUrl($newParams)
{
  $params = $_GET;
  foreach ($newParams as $key => $value) {
    if ($value === 'all') unset($params[$key]);
    else $params[$key] = $value;
  }
  return "?" . http_build_query($params);
}

try {
  $sql = "SELECT DISTINCT p.*, c.nom as cat_name FROM produits p 
          LEFT JOIN categories c ON p.categorie_id = c.id WHERE 1=1";
  $params = [];

  if ($cat_filter !== 'all') {
    $sql .= " AND p.categorie_id = :cat";
    $params[':cat'] = $cat_filter;
  }
  if ($genre_filter !== 'all') {
    $sql .= " AND p.genre = :genre";
    $params[':genre'] = $genre_filter;
  }
  // CORRECTION ICI AUSSI
  if ($age_filter !== 'all') {
    $sql .= " AND p.tranche_age LIKE :age";
    $params[':age'] = '%' . $age_filter . '%';
  }
  if ($type_filter !== 'all') {
    $sql .= " AND (p.type_accessoire = :type OR p.nom LIKE :type_like)";
    $params[':type'] = $type_filter;
    $params[':type_like'] = '%' . $type_filter . '%';
  }
  if ($color_filter !== 'all') {
    $sql .= " AND p.id IN (SELECT produit_id FROM produit_couleurs WHERE couleur_id = :col)";
    $params[':col'] = $color_filter;
  }

  // Exécution avec pagination
  $sql .= " ORDER BY p.created_at DESC LIMIT $articlesPerPage OFFSET $offset";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $all_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $all_colors = $pdo->query("SELECT * FROM couleurs LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die($e->getMessage());
}
?>

<style>
  .modal-catalog {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(255, 255, 255, 0.98);
    z-index: 1000;
    overflow-y: auto;
  }

  .modal-catalog.active {
    display: block;
    animation: fadeIn 0.4s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<main class="pt-24 bg-[#FDFDFD]">
  <section class="py-12">
    <div class="max-w-[1400px] mx-auto px-6 flex flex-col md:flex-row gap-12">

      <aside class="w-full md:w-1/4 lg:w-1/5 space-y-10">
        <div>
          <h4 class="text-[11px] uppercase font-bold tracking-[0.3em] mb-6 border-b pb-2">Catalogues</h4>
          <ul class="text-[12px] space-y-5 uppercase tracking-widest font-medium">
            <li><a href="collection.php" class="hover:text-stone-400">Tout Voir</a></li>
            <li><button onclick="openModal('modal-enfants')" class="hover:text-stone-400 text-left w-full">Enfants</button></li>
            <li><button onclick="openModal('modal-adultes')" class="hover:text-stone-400 text-left w-full">Adultes</button></li>
            <li><button onclick="openModal('modal-deco')" class="hover:text-stone-400 text-left w-full">Déco</button></li>
            <li><button onclick="openModal('modal-gadgets')" class="hover:text-stone-400 text-left w-full">Gadgets</button></li>
          </ul>
        </div>

        <div>
          <h4 class="text-[11px] uppercase font-bold tracking-[0.3em] mb-4">Nuances</h4>
          <div class="flex flex-wrap gap-2">
            <?php foreach ($all_colors as $c): ?>
              <a href="<?php echo filterUrl(['color' => $c['id']]); ?>" class="w-5 h-5 rounded-full border border-stone-200" style="background:<?php echo $c['code_hex']; ?>"></a>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>

      <section class="flex-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <?php foreach ($all_articles as $article):

            // Image venant de la base
            $image_path = trim($article['image_principale']);

            // Nettoyage du chemin
            $image_path = str_replace('../', '', $image_path);
            $image_path = ltrim($image_path, '/');

            // Vérifie si assets/img existe déjà
            if (strpos($image_path, 'assets/img/') === false) {

              // Ajout automatique du bon dossier
              $image_path = 'assets/img/produits/' . $image_path;
            }

            // URL finale correcte
            $img_path = '/ProjetFelykay/' . $image_path;

          ?>
            <div class="group">
              <a href="ArticleSeul.php?id=<?php echo $article['id']; ?>" class="block aspect-[3/4] overflow-hidden bg-stone-100 mb-3 border border-stone-50">
                <img src="<?php echo $img_path; ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-700" alt="<?php echo htmlspecialchars($article['nom']); ?>">
              </a>
              <div class="text-center">
                <h3 class="text-[10px] uppercase tracking-wider"><?php echo htmlspecialchars($article['nom']); ?></h3>
                <p class="text-[12px] font-serif italic text-stone-500"><?php echo number_format($article['prix'], 2); ?> $</p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (empty($all_articles)): ?>
          <div class="py-20 text-center">
            <p class="font-serif italic text-stone-400">Aucune pièce ne correspond à votre sélection.</p>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </section>
</main>

<!-- MODALS -->

<!-- MODALS (Remplacer la section modal-enfants existante) -->
<div id="modal-enfants" class="modal-catalog">
  <div class="max-w-7xl mx-auto p-10">
    <button onclick="closeModal()" class="float-right text-4xl font-light hover:text-stone-400">&times;</button>
    <h2 class="text-3xl font-serif mb-16 text-center uppercase tracking-[0.5em]">Univers Enfants</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-16">

      <!-- GROUPE 1: NOURRISSONS -->
      <div>
        <h3 class="font-bold border-b-2 border-black pb-2 mb-6 uppercase text-sm tracking-widest">Nourrissons (0-5 ans)</h3>
        <div class="grid grid-cols-2 gap-4 text-[11px] text-stone-500 uppercase tracking-widest">
          <ul class="space-y-3">
            <li class="font-black text-black mb-2">Fille</li>
            <li><a href="ArticleEnfants.php?age=0-5&genre=femme&type=nuit" class="hover:underline">Nuit</a></li>
            <li><a href="ArticleEnfants.php?age=0-5&genre=femme&type=quotidien" class="hover:underline">Quotidien</a></li>
            <li><a href="ArticleEnfants.php?age=0-5&genre=femme&type=evenement" class="hover:underline">Événements</a></li>
            <li><a href="ArticleEnfants.php?age=0-5&genre=femme&type=accessoires" class="hover:underline">Accessoires</a></li>
          </ul>
          <ul class="space-y-3">
            <li class="font-black text-black mb-2">Garçon</li>
            <li><a href="ArticleEnfants.php?age=0-5&genre=homme&type=nuit" class="hover:underline">Nuit</a></li>
            <li><a href="ArticleEnfants.php?age=0-5&genre=homme&type=quotidien" class="hover:underline">Quotidien</a></li>
            <li><a href="ArticleEnfants.php?age=0-5&genre=homme&type=evenement" class="hover:underline">Événements</a></li>
            <li><a href="ArticleEnfants.php?age=0-5&genre=homme&type=accessoires" class="hover:underline">Accessoires</a></li>
          </ul>
        </div>
      </div>

      <!-- GROUPE 2: 6-14 ANS -->
      <div>
        <h3 class="font-bold border-b-2 border-black pb-2 mb-6 uppercase text-sm tracking-widest">Enfants (6-14 ans)</h3>
        <div class="grid grid-cols-2 gap-4 text-[11px] text-stone-500 uppercase tracking-widest">
          <ul class="space-y-3">
            <li class="font-black text-black mb-2">Fille</li>
            <li><a href="ArticleEnfants.php?age=6-14&genre=femme&type=nuit" class="hover:underline">Nuit</a></li>
            <li><a href="ArticleEnfants.php?age=6-14&genre=femme&type=quotidien" class="hover:underline">Quotidien</a></li>
            <li><a href="ArticleEnfants.php?age=6-14&genre=femme&type=evenement" class="hover:underline">Événements</a></li>
            <li><a href="ArticleEnfants.php?age=6-14&genre=femme&type=accessoires" class="hover:underline">Accessoires</a></li>
          </ul>
          <ul class="space-y-3">
            <li class="font-black text-black mb-2">Garçon</li>
            <li><a href="ArticleEnfants.php?age=6-14&genre=homme&type=nuit" class="hover:underline">Nuit</a></li>
            <li><a href="ArticleEnfants.php?age=6-14&genre=homme&type=quotidien" class="hover:underline">Quotidien</a></li>
            <li><a href="ArticleEnfants.php?age=6-14&genre=homme&type=evenement" class="hover:underline">Événements</a></li>
            <li><a href="ArticleEnfants.php?age=6-14&genre=homme&type=accessoires" class="hover:underline">Accessoires</a></li>
          </ul>
        </div>
      </div>

      <!-- GROUPE 3: 14-18 ANS -->
      <div>
        <h3 class="font-bold border-b-2 border-black pb-2 mb-6 uppercase text-sm tracking-widest">Ados (14-18 ans)</h3>
        <div class="grid grid-cols-2 gap-4 text-[11px] text-stone-500 uppercase tracking-widest">
          <ul class="space-y-3">
            <li class="font-black text-black mb-2">Fille</li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=femme&type=nuit" class="hover:underline">Nuit</a></li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=femme&type=quotidien" class="hover:underline">Quotidien</a></li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=femme&type=evenement" class="hover:underline">Événements</a></li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=femme&type=soir" class="hover:underline">Soir</a></li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=femme&type=accessoires" class="hover:underline">Accessoires</a></li>
          </ul>
          <ul class="space-y-3">
            <li class="font-black text-black mb-2">Garçon</li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=homme&type=nuit" class="hover:underline">Nuit</a></li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=homme&type=quotidien" class="hover:underline">Quotidien</a></li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=homme&type=evenement" class="hover:underline">Événements</a></li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=homme&type=soir" class="hover:underline">Soir</a></li>
            <li><a href="ArticleEnfants.php?age=14-18&genre=homme&type=accessoires" class="hover:underline">Accessoires</a></li>
          </ul>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modal Adultes -->
<div id="modal-adultes" class="modal-catalog">
  <div class="max-w-4xl mx-auto p-10">
    <button onclick="closeModal()" class="float-right text-3xl font-light">&times;</button>
    <h2 class="text-2xl font-serif mb-12 text-center uppercase tracking-widest">Univers Adultes</h2>
    <div class="grid grid-cols-2 gap-20">
      <div>
        <h3 class="font-bold border-b pb-2 mb-4 uppercase text-xs">Femme</h3>
        <ul class="text-[11px] space-y-3 uppercase tracking-widest">
          <li><a href="<?php echo filterUrl(['age' => 'adulte', 'genre' => 'femme', 'type' => 'quotidien']); ?>">Quotidien</a></li>
          <li><a href="<?php echo filterUrl(['age' => 'adulte', 'genre' => 'femme', 'type' => 'evenement']); ?>">Événements</a></li>
          <li><a href="<?php echo filterUrl(['age' => 'adulte', 'genre' => 'femme', 'type' => 'soir']); ?>">Soir</a></li>
        </ul>
      </div>
      <div>
        <h3 class="font-bold border-b pb-2 mb-4 uppercase text-xs">Homme</h3>
        <ul class="text-[11px] space-y-3 uppercase tracking-widest">
          <li><a href="<?php echo filterUrl(['age' => 'adulte', 'genre' => 'homme', 'type' => 'quotidien']); ?>">Quotidien</a></li>
          <li><a href="<?php echo filterUrl(['age' => 'adulte', 'genre' => 'homme', 'type' => 'soir']); ?>">Soir</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Modal Déco -->
<div id="modal-deco" class="modal-catalog">
  <div class="max-w-3xl mx-auto p-10 text-center">
    <button onclick="closeModal()" class="float-right text-3xl font-light">&times;</button>
    <h2 class="text-2xl font-serif mb-10 uppercase tracking-widest">Décoration & Maison</h2>
    <ul class="text-[12px] space-y-6 uppercase tracking-[0.3em]">
      <li><a href="<?php echo filterUrl(['cat' => '7', 'type' => 'maison']); ?>">Maison</a></li>
      <li><a href="<?php echo filterUrl(['cat' => '7', 'type' => 'decoration']); ?>">Décoration Intérieure</a></li>
    </ul>
  </div>
</div>

<!-- Modal Gadgets -->
<div id="modal-gadgets" class="modal-catalog">
  <div class="max-w-3xl mx-auto p-10 text-center">
    <button onclick="closeModal()" class="float-right text-3xl font-light">&times;</button>
    <h2 class="text-2xl font-serif mb-10 uppercase tracking-widest">Gadgets & Utilitaires</h2>
    <ul class="text-[12px] space-y-6 uppercase tracking-[0.3em]">
      <li><a href="<?php echo filterUrl(['cat' => '8', 'type' => 'electroniques']); ?>">Électroniques</a></li>
      <li><a href="<?php echo filterUrl(['cat' => '8', 'type' => 'cuisine']); ?>">Cuisine</a></li>
    </ul>
  </div>
</div>

<script>
  function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
      document.querySelectorAll('.modal-catalog').forEach(m => m.classList.remove('active'));
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeModal() {
    document.querySelectorAll('.modal-catalog').forEach(m => m.classList.remove('active'));
    document.body.style.overflow = 'auto';
  }

  window.onclick = function(event) {
    if (event.target.classList.contains('modal-catalog')) {
      closeModal();
    }
  }
</script>

<?php include '../includes/footer.php'; ?>