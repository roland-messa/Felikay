<?php
// C:\wamp64\www\ProjetFelykay\pages\edit_article.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/function.php';

if (!isset($_GET['id'])) {
  header("Location: /ProjetFelykay/pages/admin/admin_dashboard.php");
  exit();
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
  die("Article introuvable.");
}

// Logique pour déterminer si on affiche la tranche d'âge (ex: ID 3 pour catégorie Enfants)
// On vérifie soit l'ID, soit si le mot "enfant" est dans le nom/genre
$isEnfant = (isset($p['categorie_id']) && $p['categorie_id'] == 3) || (stripos($p['genre'], 'enfant') !== false);

$pageTitle = "Modifier " . htmlspecialchars($p['nom']);
include __DIR__ . '/../includes/header.php';
?>

<main class="pt-32 pb-24 bg-[#F4F4F4] min-h-screen">
  <div class="max-w-2xl mx-auto bg-white p-10 shadow-sm rounded-3xl border border-slate-200">
    <h2 class="font-serif text-3xl italic mb-10">Modifier l'article</h2>

    <form action="../assets/actions/update_article.php" method="POST" enctype="multipart/form-data" class="space-y-6">
      <!-- IDs et données persistantes -->
      <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
      <input type="hidden" name="categorie_id" value="<?php echo $p['categorie_id']; ?>">
      <input type="hidden" name="genre" value="<?php echo $p['genre']; ?>">

      <div class="space-y-2">
        <label class="text-[10px] uppercase font-bold text-slate-500">Nom du produit</label>
        <input type="text" name="nom" value="<?php echo htmlspecialchars($p['nom']); ?>" required
          class="w-full border-b border-slate-300 p-3 text-sm outline-none focus:border-black transition">
      </div>

      <div class="grid grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="text-[10px] uppercase font-bold text-slate-500">Prix ($)</label>
          <input type="number" step="0.01" name="prix" value="<?php echo number_format($p['prix'], 2, '.', ''); ?>" required
            class="w-full border-b border-slate-300 p-3 text-sm outline-none focus:border-black transition">
        </div>
        <div class="space-y-2">
          <label class="text-[10px] uppercase font-bold text-slate-500">Stock actuel</label>
          <input type="number" name="stock_total" value="<?php echo $p['stock_total']; ?>" required
            class="w-full border-b border-slate-300 p-3 text-sm outline-none focus:border-black transition">
        </div>
      </div>

      <!-- Affichage conditionnel de la tranche d'âge -->
      <?php if ($isEnfant): ?>
        <div class="space-y-2">
          <label class="text-[10px] uppercase font-bold text-slate-500">Tranche d'âge (Enfants)</label>
          <select name="tranche_age" class="w-full border-b border-slate-300 p-3 text-sm outline-none bg-transparent focus:border-black transition">
            <option value="" <?php echo (empty($p['tranche_age']) || $p['tranche_age'] == 'adulte') ? 'selected' : ''; ?>>N/A</option>
            <option value="0-12m" <?php echo ($p['tranche_age'] == '0-12m') ? 'selected' : ''; ?>>0-12 mois</option>
            <option value="1-18j" <?php echo ($p['tranche_age'] == '1-18j') ? 'selected' : ''; ?>>1-18 ans</option>
          </select>
        </div>
      <?php else: ?>
        <input type="hidden" name="tranche_age" value="adulte">
      <?php endif; ?>

      <div class="space-y-4 pt-4">
        <p class="text-[10px] uppercase font-bold text-slate-500">Image actuelle</p>
        <div class="flex items-center gap-6">
          <?php
          $img_name = str_replace(['../../', '../', 'assets/img/produits/'], '', $p['image_principale']);
          $img_path = "/ProjetFelykay/assets/img/produits/" . ltrim($img_name, '/');
          ?>

          <img src="<?php echo $img_path; ?>"
            class="w-24 h-32 object-cover rounded-xl shadow-md border border-slate-100"
            onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">

          <div class="flex-1">
            <label class="text-[10px] uppercase font-bold text-slate-500 block mb-2">Remplacer l'image</label>
            <input type="file" name="image_principale" class="w-full text-xs">
          </div>
        </div>
      </div>

      <!-- Note de modification (optionnel visuellement) -->
      <p class="text-[9px] text-slate-400 italic">Dernière mise à jour : <?php echo $p['updated_at'] ?? 'Jamais'; ?></p>

      <div class="flex gap-4 pt-8">
        <button type="submit" class="flex-1 bg-black text-white py-4 rounded-xl text-[11px] uppercase tracking-widest font-bold hover:bg-zinc-800 transition">
          Enregistrer les modifications
        </button>
        <a href="/ProjetFelykay/pages/admin/admin_dashboard.php" class="px-8 py-4 border border-slate-200 rounded-xl text-[11px] uppercase font-bold flex items-center hover:bg-slate-50 transition">
          Annuler
        </a>
      </div>
    </form>
  </div>
</main>

<script>
  window.addEventListener('load', function() {
    const loader = document.getElementById('loader');
    if (loader) {
      setTimeout(() => {
        loader.classList.add('fade-out');
        setTimeout(() => {
          loader.style.display = 'none';
        }, 800);
      }, 1000);
    }
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>