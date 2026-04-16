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

$cat_filter = $_GET['cat'] ?? 'all';
$color_filter = $_GET['color'] ?? 'all';

try {

  $stmt_cats = $pdo->query("SELECT c.*, COUNT(p.id) as total 
                             FROM categories c 
                             LEFT JOIN produits p ON c.id = p.categorie_id 
                             GROUP BY c.id");
  $categories_data = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

  $count_total = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();

  // 3. CONSTRUCTION DE LA REQUÊTE PRINCIPALE
  $sql = "SELECT p.*, c.nom as cat_name FROM produits p 
            LEFT JOIN categories c ON p.categorie_id = c.id WHERE 1=1";

  if ($cat_filter !== 'all') {
    $sql .= " AND p.categorie_id = :cat_id";
  }

  if ($color_filter !== 'all') {
    $sql .= " AND p.couleur_id = :color_id";
  }

  $count_sql = "SELECT COUNT(*) FROM produits WHERE 1=1";
  if ($cat_filter !== 'all') $count_sql .= " AND categorie_id = :cat_id";
  if ($color_filter !== 'all') $count_sql .= " AND couleur_id = :color_id";

  $total_stmt = $pdo->prepare($count_sql);
  if ($cat_filter !== 'all') $total_stmt->bindValue(':cat_id', $cat_filter);
  if ($color_filter !== 'all') $total_stmt->bindValue(':color_id', $color_filter);
  $total_stmt->execute();
  $total_count = $total_stmt->fetchColumn();
  $totalPages = ceil($total_count / $articlesPerPage);

  // Récupération des articles
  $sql .= " LIMIT :limit OFFSET :offset";
  $stmt = $pdo->prepare($sql);
  if ($cat_filter !== 'all') $stmt->bindValue(':cat_id', $cat_filter, PDO::PARAM_INT);
  if ($color_filter !== 'all') $stmt->bindValue(':color_id', $color_filter, PDO::PARAM_INT);
  $stmt->bindValue(':limit', $articlesPerPage, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $all_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 4. RÉCUPÉRATION DES COULEURS
  $stmt_colors = $pdo->query("SELECT * FROM couleurs");
  $all_colors = $stmt_colors->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $all_articles = [];
  $total_count = 0;
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

            <?php foreach ($categories_data as $cat): ?>
              <li>
                <a href="?cat=<?php echo $cat['id']; ?>" class="hover:text-black transition flex justify-between <?php echo ($cat_filter == $cat['id']) ? 'text-black font-bold' : ''; ?>">
                  <?php echo htmlspecialchars($cat['nom']); ?>
                  <span><?php echo $cat['total']; ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="filter-section">
          <h4 class="text-[11px] uppercase font-bold tracking-[0.3em] mb-4">Tailles</h4>
          <div class="grid grid-cols-4 gap-2">
            <?php foreach (['XS', 'S', 'M', 'L', 'XL'] as $t): ?>
              <button class="border border-stone-200 py-2 text-[10px] hover:border-black transition"><?php echo $t; ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="filter-section mt-8">
          <h4 class="text-[11px] uppercase font-bold tracking-[0.3em] mb-4">Couleurs</h4>
          <div class="flex flex-wrap gap-3">
            <?php foreach ($all_colors as $c): ?>
              <a href="?cat=<?php echo $cat_filter; ?>&color=<?php echo $c['id']; ?>"
                title="<?php echo htmlspecialchars($c['nom']); ?>"
                class="w-6 h-6 rounded-full border border-stone-200 transition-all hover:scale-125 hover:shadow-md block <?php echo ($color_filter == $c['id']) ? 'ring-2 ring-black ring-offset-2' : ''; ?>"
                style="background-color: <?php echo $c['code_hex']; ?>; <?php echo (strtoupper($c['code_hex']) == '#FFFFFF' || strtoupper($c['code_hex']) == '#F8F7F7') ? 'border: 1px solid #e5e7eb;' : ''; ?>">
              </a>
            <?php endforeach; ?>
          </div>

          <?php if ($color_filter !== 'all'): ?>
            <a href="?cat=<?php echo $cat_filter; ?>" class="text-[9px] uppercase tracking-widest mt-4 block text-stone-400 underline">
              Effacer le filtre couleur
            </a>
          <?php endif; ?>
        </div>
      </aside>

      <section class="flex-1">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-6 gap-y-12">
          <?php foreach ($all_articles as $article) :

            $img = "../" . str_replace('../', '', $article['image_principale']);
            $detail_url = "ArticleSeul.php?id=" . $article['id'];
          ?>
            <div class="group">
              <a href="<?php echo $detail_url; ?>" class="relative block aspect-[3/4] overflow-hidden bg-[#F5F5F5] mb-4 border border-stone-100">
                <img src="<?php echo $img; ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">

                <div class="absolute inset-x-0 bottom-0 p-4 opacity-0 group-hover:opacity-100 transition-all translate-y-4 group-hover:translate-y-0 z-20">
                  <button onclick="event.preventDefault(); addToCart('<?php echo addslashes($article['nom']); ?>', <?php echo $article['prix']; ?>, '<?php echo $img; ?>')"
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
              <a href="?page=<?php echo $i; ?>&cat=<?php echo $cat_filter; ?>&color=<?php echo $color_filter; ?>"
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
      let name = card.querySelector('h3').innerText.toLowerCase();
      card.style.display = name.includes(input) ? "block" : "none";
    });
  }
</script>

<?php include '../includes/footer.php'; ?>