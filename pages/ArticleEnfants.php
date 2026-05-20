<?php
// C:\wamp64\www\ProjetFelykay\pages\ArticleEnfants.php
require_once '../config/db.php';

$age_filter   = $_GET['age'] ?? 'tous';
$genre_filter = $_GET['genre'] ?? 'tous';
$type_filter  = $_GET['type'] ?? 'tous';

try {
  $sql = "SELECT * FROM produits WHERE (categorie_id = 3 OR categorie_id = 6)";
  $params = [];

  if ($age_filter !== 'tous') {
    $sql .= " AND tranche_age LIKE :age";
    $params[':age'] = '%' . $age_filter . '%';
  }

  if ($genre_filter !== 'tous') {
    $sql .= " AND (genre = :genre OR genre = 'mixte' OR genre = 'unisexe')";
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
  $articles_enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Erreur : " . $e->getMessage());
}

$pageTitle = "Felikay | Univers Enfants";
include '../includes/header.php';
include '../includes/navbar.php';

$labelAge = "Univers Enfants";
if (strpos($age_filter, "0-5") !== false) $labelAge = "Nourrissons (0-5 ans)";
if (strpos($age_filter, "6-14") !== false) $labelAge = "Enfants (6-14 ans)";
if (strpos($age_filter, "14-18") !== false) $labelAge = "Ados (14-18 ans)";
?>

<main class="bg-[#FDFDFD] pt-32 min-h-screen">
  <div class="max-w-[1400px] mx-auto px-6">

    <div class="text-center mb-16">
      <h1 class="font-serif text-5xl italic mb-4"><?php echo $labelAge; ?></h1>
      <p class="text-[10px] uppercase tracking-[0.4em] text-stone-400">
        Filtre actuel : <?php echo ($genre_filter == 'femme' ? 'Fille' : ($genre_filter == 'homme' ? 'Garçon' : 'Tous')); ?>
        | <?php echo ucfirst($type_filter); ?>
      </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
      <?php if (!empty($articles_enfants)): ?>
        <?php foreach ($articles_enfants as $item):

          // --- LOGIQUE INTELLIGENTE DES IMAGES ---
          $image_path = trim($item['image_principale']);

          // Nettoyage (enlève ../ et / au début)
          $image_path = str_replace('../', '', $image_path);
          $image_path = ltrim($image_path, '/');

          // Vérifie si le chemin contient déjà le dossier assets
          if (strpos($image_path, 'assets/img/') === false) {
            $image_path = 'assets/img/produits/' . $image_path;
          }

          // URL finale absolue pour WAMP
          $final_img = '/ProjetFelykay/' . $image_path;
        ?>
          <div class="group">
            <!-- Conteneur Image avec Icône au survol -->
            <div class="relative aspect-[3/4] overflow-hidden bg-stone-100 mb-4 border border-stone-50">
              <a href="ArticleSeul.php?id=<?php echo $item['id']; ?>">
                <img src="<?php echo htmlspecialchars($final_img); ?>"
                  class="w-full h-full object-cover group-hover:scale-110 transition duration-700"
                  onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg';">

                <!-- Overlay avec Icône -->
                <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                  <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                    <i class="fa-solid fa-magnifying-glass text-black text-sm"></i>
                  </div>
                </div>
              </a>
            </div>

            <!-- Infos Produit -->
            <div class="text-center">
              <h3 class="text-[11px] uppercase tracking-widest font-bold mb-1"><?php echo htmlspecialchars($item['nom']); ?></h3>

              <!-- Affichage de la description -->
              <p class="text-[10px] text-stone-400 mb-2 italic px-4 line-clamp-2">
                <?php echo !empty($item['description']) ? htmlspecialchars($item['description']) : "Aucune description disponible"; ?>
              </p>

              <p class="text-sm font-serif italic text-stone-600"><?php echo number_format($item['prix'], 2); ?> $</p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-span-full py-20 text-center">
          <p class="font-serif italic text-stone-400 text-xl">Désolé, aucune pièce ne correspond.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>