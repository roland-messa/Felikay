<?php
session_start();
$pageTitle = "Felikay | Collections";
$isSecondaryPage = true;

include '../includes/header.php';
include '../includes/navbar.php';
include '../config/db.php';

// 1. PARAMÈTRES DE FILTRAGE ET PAGINATION
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$articlesPerPage = 12;
$offset = ($currentPage - 1) * $articlesPerPage;

// Initialisation des variables pour éviter les erreurs "Undefined variable"
$all_articles = [];
$total_count = 0;
$totalPages = 1;

// Filtres récupérés de l'URL
$cat_filter   = $_GET['cat'] ?? 'all';
$color_filter = $_GET['color'] ?? 'all';
$size_filter  = $_GET['size'] ?? 'all';
$genre_filter = $_GET['genre'] ?? 'all';
$age_filter   = $_GET['age'] ?? 'all';
$type_filter  = $_GET['type'] ?? 'all';

/**
 * Fonction pour générer des URLs de filtrage cumulables
 */
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
  // 2. RÉCUPÉRATION DES CATÉGORIES POUR LE MENU
  $stmt_cats = $pdo->query("SELECT c.*, COUNT(p.id) as total 
                              FROM categories c 
                              LEFT JOIN produits p ON c.id = p.categorie_id 
                              GROUP BY c.id");
  $categories_data = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

  $count_total = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();

  // 3. CONSTRUCTION DE LA REQUÊTE PRINCIPALE
  $sql = "SELECT p.*, c.nom as cat_name FROM produits p 
            LEFT JOIN categories c ON p.categorie_id = c.id WHERE 1=1";

  $count_sql = "SELECT COUNT(*) FROM produits p WHERE 1=1";

  $conditions = "";
  $params = [];

  if ($cat_filter !== 'all') {
    $conditions .= " AND p.categorie_id = :cat_id";
    $params[':cat_id'] = $cat_filter;
  }

  // FILTRE COULEUR (Via table de liaison produit_couleurs)
  if ($color_filter !== 'all') {
    $conditions .= " AND p.id IN (SELECT produit_id FROM produit_couleurs WHERE couleur_id = :color_id)";
    $params[':color_id'] = $color_filter;
  }

  // FILTRE TAILLE (Via table de liaison produit_tailles)
  if ($size_filter !== 'all') {
    $conditions .= " AND p.id IN (SELECT produit_id FROM produit_tailles WHERE taille = :size)";
    $params[':size'] = $size_filter;
  }

  if ($genre_filter !== 'all') {
    $conditions .= " AND p.genre = :genre";
    $params[':genre'] = $genre_filter;
  }
  if ($age_filter !== 'all') {
    $conditions .= " AND p.tranche_age = :age";
    $params[':age'] = $age_filter;
  }
  if ($type_filter !== 'all') {
    $conditions .= " AND p.type_accessoire = :type";
    $params[':type'] = $type_filter;
  }

  $sql .= $conditions;
  $count_sql .= $conditions;

  // Calcul du total pour la pagination
  $total_stmt = $pdo->prepare($count_sql);
  $total_stmt->execute($params);
  $total_count = $total_stmt->fetchColumn();

  $totalPages = max(1, ceil($total_count / $articlesPerPage));

  // Récupération des articles paginés
  $sql .= " LIMIT :limit OFFSET :offset";
  $stmt = $pdo->prepare($sql);

  foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
  }
  $stmt->bindValue(':limit', $articlesPerPage, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $all_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 4. RÉCUPÉRATION DES COULEURS POUR LE FILTRE
  $all_colors = $pdo->query("SELECT * FROM couleurs")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  error_log($e->getMessage());
  if (!isset($all_colors)) $all_colors = [];
}
?>

<main class="pt-24 bg-[#FDFDFD]">
  <section class="py-12">
    <div class="max-w-[1400px] mx-auto px-6 flex flex-col md:flex-row gap-12">

      <aside class="w-full md:w-1/4 lg:w-1/5 space-y-8">
        <div class="relative border-b border-stone-200 pb-2">
          <input type="text" id="searchInput" onkeyup="searchFunction()" placeholder="Rechercher une pièce..."
            class="w-full text-[11px] uppercase tracking-widest focus:outline-none bg-transparent">
        </div>

        <div class="filter-section">
          <h4 class="text-[11px] uppercase font-bold tracking-[0.3em] mb-6">Garde-Robe</h4>
          <ul class="text-[12px] space-y-4 text-stone-500 tracking-wide">
            <li>
              <a href="?cat=all" class="hover:text-black transition flex justify-between <?php echo ($cat_filter === 'all') ? 'text-black font-bold' : ''; ?>">
                Tout l'univers <span><?php echo $count_total; ?></span>
              </a>
            </li>

            <?php foreach ($categories_data as $cat):
              if ($cat['nom'] == 'Perruques' || $cat['nom'] == 'Électronique') continue;
            ?>
              <li class="group">
                <a href="<?php echo filterUrl(['cat' => $cat['id'], 'genre' => 'all', 'age' => 'all']); ?>" class="hover:text-black transition flex justify-between <?php echo ($cat_filter == $cat['id']) ? 'text-black font-bold' : ''; ?>">
                  <?php echo htmlspecialchars($cat['nom']); ?>
                  <span><?php echo $cat['total']; ?></span>
                </a>

                <?php if (($cat['id'] == 3 || $cat['id'] == 6) && $cat_filter == $cat['id']): ?>
                  <div class="mt-4 ml-3 pl-4 border-l border-stone-100 space-y-4 animate-fadeIn">
                    <div class="space-y-2">
                      <p class="text-[9px] uppercase font-bold text-black tracking-tighter">Genre</p>
                      <div class="flex flex-col gap-1.5">
                        <a href="<?php echo filterUrl(['genre' => 'homme']); ?>" class="hover:text-black <?php echo ($genre_filter == 'homme') ? 'text-black font-bold' : ''; ?>">• Garçon</a>
                        <a href="<?php echo filterUrl(['genre' => 'femme']); ?>" class="hover:text-black <?php echo ($genre_filter == 'femme') ? 'text-black font-bold' : ''; ?>">• Fille</a>
                        <a href="<?php echo filterUrl(['genre' => 'unisexe']); ?>" class="hover:text-black <?php echo ($genre_filter == 'unisexe') ? 'text-black font-bold' : ''; ?>">• Mixte</a>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="filter-section">
          <h4 class="text-[11px] uppercase font-bold tracking-[0.3em] mb-4">Tailles</h4>
          <div class="grid grid-cols-4 gap-2">
            <?php foreach (['XS', 'S', 'M', 'L', 'XL'] as $t): ?>
              <a href="<?php echo filterUrl(['size' => $t]); ?>"
                class="border py-2 text-[10px] text-center transition-all <?php echo ($size_filter === $t) ? 'bg-black text-white border-black' : 'border-stone-200 hover:border-black'; ?>">
                <?php echo $t; ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="filter-section mt-8">
          <h4 class="text-[11px] uppercase font-bold tracking-[0.3em] mb-4">Couleurs</h4>
          <div class="flex flex-wrap gap-3">
            <?php foreach ($all_colors as $c): ?>
              <a href="<?php echo filterUrl(['color' => $c['id']]); ?>"
                title="<?php echo htmlspecialchars($c['nom']); ?>"
                class="w-6 h-6 rounded-full border border-stone-200 transition-all hover:scale-125 hover:shadow-md block <?php echo ($color_filter == $c['id']) ? 'ring-2 ring-black ring-offset-2' : ''; ?>"
                style="background-color: <?php echo $c['code_hex']; ?>;">
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if ($cat_filter !== 'all' || $color_filter !== 'all' || $size_filter !== 'all' || $genre_filter !== 'all'): ?>
          <div class="pt-4">
            <a href="collection.php" class="inline-flex items-center gap-2 text-[9px] uppercase tracking-widest bg-stone-900 text-white px-4 py-2 rounded-full hover:bg-stone-700 transition">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Effacer tout
            </a>
          </div>
        <?php endif; ?>

      </aside>

      <section class="flex-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-6 gap-y-12">
          <?php if (empty($all_articles)): ?>
            <div class="col-span-full py-20 text-center">
              <p class="text-stone-400 italic">Aucun article ne correspond à votre sélection.</p>
              <a href="collection.php" class="text-xs underline mt-4 block">Retourner à la collection complète</a>
            </div>
          <?php endif; ?>

          <?php foreach ($all_articles as $article) :
            $img = "../" . str_replace('../', '', $article['image_principale']);
            $detail_url = "ArticleSeul.php?id=" . $article['id'];
          ?>
            <div class="group">
              <a href="<?php echo $detail_url; ?>" class="relative block aspect-[3/4] overflow-hidden bg-[#F5F5F5] mb-4 border border-stone-100">
                <img src="<?php echo $img; ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                <div class="absolute inset-x-0 bottom-0 p-4 opacity-0 group-hover:opacity-100 transition-all translate-y-4 group-hover:translate-y-0 z-20">
                  <button onclick="event.preventDefault(); addToCart(<?php echo $article['id']; ?>, '<?php echo addslashes($article['nom']); ?>', <?php echo $article['prix']; ?>, '<?php echo $img; ?>')"
                    class="w-full bg-white text-black py-3 text-[9px] font-bold uppercase tracking-widest shadow-xl hover:bg-black hover:text-white transition-colors">
                    Ajouter au Panier
                  </button>
                </div>
              </a>

              <div class="text-center">
                <span class="text-[8px] uppercase tracking-[0.2em] text-stone-400 mb-1 block">
                  <?php echo htmlspecialchars($article['cat_name']); ?>
                </span>
                <a href="<?php echo $detail_url; ?>" class="hover:underline decoration-stone-300 underline-offset-4">
                  <h3 class="text-[11px] uppercase font-medium tracking-wider mb-2"><?php echo $article['nom']; ?></h3>
                </a>
                <p class="font-serif italic text-stone-600 text-[14px]"><?php echo number_format($article['prix'], 2); ?> $</p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
          <div class="mt-20 flex justify-center items-center gap-4">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <a href="<?php echo filterUrl(['page' => $i]); ?>"
                class="w-10 h-10 flex items-center justify-center border border-stone-200 rounded-full text-[12px] transition-all hover:border-black <?php echo ($i == $currentPage) ? 'bg-black text-white border-black' : 'text-stone-500'; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>
  </section>
</main>

<script>
  function searchFunction() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let cards = document.querySelectorAll('.group');
    cards.forEach(card => {
      let h3 = card.querySelector('h3');
      if (h3) {
        let name = h3.innerText.toLowerCase();
        card.style.display = name.includes(input) ? "block" : "none";
      }
    });
  }
</script>

<?php include '../includes/footer.php'; ?>