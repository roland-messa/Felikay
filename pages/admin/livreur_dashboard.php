<?php
// C:\wamp64\www\ProjetFelykay\pages\admin\livreur_dashboard.php
session_start();
require_once '../../config/db.php';

// 1. SÉCURITÉ : Vérification stricte
$roles_autorises = ['livreur', 'admin'];

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $roles_autorises)) {
  header("Location: ../../pages/admin/admin_login.php");
  exit();
}

// 2. RÉCUPÉRATION
try {
  $stmt = $pdo->prepare("SELECT * FROM commandes WHERE statut = 'expedie' ORDER BY created_at DESC");
  $stmt->execute();
  $commandes = $stmt->fetchAll();
} catch (PDOException $e) {
  die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Espace Livreur | Felikay</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@1,700;1,900&family=Inter:wght@300;400;700&display=swap');

    .font-serif-felikay {
      font-family: 'Playfair Display', serif;
    }

    body {
      font-family: 'Inter', sans-serif;
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
  </style>
</head>

<body class="bg-gray-100 min-h-screen text-black">

  <!-- En-tête avec Logo (Style Paiement) -->
  <header class="py-10 px-8 flex flex-col items-center gap-4 bg-gray-50">
    <div class="w-16 h-16 rounded-full overflow-hidden border border-gray-50 shadow-sm">
      <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo" class="w-full h-full object-cover">
    </div>
    <h1 class="font-serif-felikay text-3xl uppercase tracking-widest italic border-b-2 border-black pb-2">
      Felikay
    </h1>
    <div class="flex justify-between items-center w-full max-w-4xl mt-4">
      <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Espace Livreur</span>
      <a href="../../assets/actions/logout.php" class="text-[10px] uppercase tracking-widest font-bold hover:underline">Déconnexion</a>
    </div>
  </header>

  <main class="max-w-4xl mx-auto p-6">

    <div class="mb-12 flex justify-between items-end">
      <div>
        <h2 class="text-4xl font-black uppercase tracking-tighter">Mes Courses</h2>
        <p class="text-[10px] text-gray-400 uppercase tracking-[0.3em] font-medium mt-2"><?= date('d F Y') ?></p>
      </div>
      <div class="flex flex-col items-end">
        <span class="text-3xl font-light"><?= count($commandes) ?></span>
        <span class="text-[9px] uppercase tracking-widest font-bold opacity-40">En attente</span>
      </div>
    </div>

    <!-- Alertes -->
    <?php if (isset($_GET['success'])): ?>
      <div class="bg-black text-white p-5 mb-8 flex items-center justify-between">
        <span class="text-xs uppercase tracking-widest font-bold text-center w-full">Livraison confirmée</span>
      </div>
    <?php endif; ?>

    <!-- Liste des commandes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <?php if (empty($commandes)): ?>
        <div class="col-span-full text-center py-32 border border-dashed border-gray-300">
          <p class="text-gray-300 uppercase tracking-widest text-xs font-bold">Aucune course disponible</p>
        </div>
      <?php endif; ?>

      <?php foreach ($commandes as $cmd): ?>
        <div class="border border-gray-200 p-8 hover:shadow-2xl transition-all duration-500">
          <div class="flex justify-between items-start mb-10">
            <span class="text-[10px] font-bold border border-black px-3 py-1 uppercase">#<?= $cmd['id'] ?></span>
            <span class="text-2xl font-serif-felikay italic"><?= number_format($cmd['total_ttc'], 2) ?> $</span>
          </div>

          <div class="space-y-8">
            <div>
              <h3 class="text-[9px] uppercase tracking-[0.2em] text-gray-400 font-bold mb-3">Client</h3>
              <p class="text-xl font-bold uppercase tracking-tight"><?= htmlspecialchars($cmd['nom_complet']) ?></p>
            </div>

            <div class="flex items-start gap-4">
              <span class="text-sm">📍</span>
              <div>
                <p class="text-sm font-bold uppercase"><?= htmlspecialchars($cmd['commune']) ?></p>
                <p class="text-xs text-gray-500 leading-relaxed"><?= htmlspecialchars($cmd['quartier']) ?><br><?= htmlspecialchars($cmd['adresse_livraison']) ?></p>
              </div>
            </div>


            <div class="mb-4 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-between">
              <span class="text-[10px] font-bold text-blue-600 uppercase">Contact Client</span>
              <a href="tel:<?= $cmd['telephone'] ?>" class="text-sm font-bold text-blue-700 underline">
                <?= htmlspecialchars($cmd['telephone']) ?>
              </a>
            </div>

            <!-- Formulaire -->
            <form action="../../assets/actions/valider_livraison.php" method="POST" class="pt-8 mt-4 border-t border-gray-50">
              <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
              <div class="flex flex-col gap-5">
                <input type="number" name="code_saisi"
                  placeholder="CODE DE CONFIRMATION"
                  class="w-full bg-gray-50 border-none py-4 text-center text-xl font-light tracking-[0.5em] focus:ring-1 focus:ring-black outline-none transition-all placeholder:text-[9px] placeholder:tracking-widest"
                  required>
                <button type="submit" class="w-full bg-black text-white py-5 text-[10px] font-bold uppercase tracking-[0.4em] hover:bg-gray-800 transition-all shadow-lg">
                  Confirmer la livraison
                </button>
              </div>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <footer class="text-center py-20 text-gray-500 text-[8px] uppercase tracking-[0.5em] font-bold">
    Felikay &copy; <?= date('Y') ?> &bull; Kinshasa Service
  </footer>

</body>

</html>