<?php
session_start();
require_once '../../config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'livreur') {
  if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../../pages/admin_login.php");
    exit();
  }
}

$livreur_id = $_SESSION['user_id'];

try {
  // 1. COURSES ACTIVES
  $stmt = $pdo->prepare("
        SELECT c.*, u.nom as client_nom, u.telephone as client_tel, u.email as user_email 
        FROM commandes c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.livreur_id = ? AND c.statut IN ('expedie', 'en_cours_de_livraison', 'en_route') 
        ORDER BY c.created_at DESC
    ");
  $stmt->execute([$livreur_id]);
  $commandes = $stmt->fetchAll();

  // 2. HISTORIQUE
  $stmtHist = $pdo->prepare("
        SELECT c.*, u.nom as client_nom 
        FROM commandes c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.livreur_id = ? AND c.statut IN ('livre', 'livre_payer') 
        ORDER BY c.updated_at DESC LIMIT 10
    ");
  $stmtHist->execute([$livreur_id]);
  $historique = $stmtHist->fetchAll();
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
  </style>
</head>

<body class="bg-gray-100 min-h-screen text-black">

  <header class="py-10 px-8 flex flex-col items-center gap-4 bg-gray-50 border-b border-gray-200">
    <div class="w-16 h-16 rounded-full overflow-hidden shadow-sm">
      <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo" class="w-full h-full object-cover">
    </div>
    <h1 class="font-serif-felikay text-3xl uppercase tracking-widest italic border-b-2 border-black pb-2">Felikay</h1>
    <div class="flex justify-between items-center w-full max-w-4xl mt-4">
      <div class="flex flex-col">
        <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">Espace Livreur</span>
        <span class="text-[11px] font-medium text-black">Coursier : <?= htmlspecialchars($_SESSION['user_nom']) ?></span>
      </div>
      <a href="../../assets/actions/logout.php" class="text-[10px] uppercase tracking-widest font-bold text-red-500">Déconnexion</a>
    </div>
  </header>

  <main class="max-w-4xl mx-auto p-6">

    <div class="mb-12 flex justify-between items-end">
      <div>
        <h2 class="text-4xl font-black uppercase tracking-tighter text-blue-600">En cours</h2>
        <p class="text-[10px] text-gray-400 uppercase tracking-[0.3em] font-medium mt-2"><?= date('d F Y') ?></p>
      </div>
      <div class="flex flex-col items-end">
        <span class="text-3xl font-light"><?= count($commandes) ?></span>
        <span class="text-[9px] uppercase tracking-widest font-bold opacity-40">À livrer</span>
      </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
      <div class="bg-green-600 text-white p-4 mb-8 rounded-2xl shadow-lg text-center text-xs font-bold uppercase tracking-widest animate-bounce">✅ Livraison validée et archivée</div>
    <?php endif; ?>

    <?php if (isset($_GET['en_route']) && $_GET['en_route'] === 'success'): ?>
      <div class="bg-blue-600 text-white p-4 mb-8 rounded-2xl shadow-lg text-center text-xs font-bold uppercase tracking-widest">🚀 Course démarrée ! Le client a été notifié par e-mail.</div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-20">
      <?php if (empty($commandes)): ?>
        <div class="col-span-full text-center py-20 border border-dashed border-gray-300 bg-white rounded-3xl">
          <p class="text-gray-300 uppercase tracking-widest text-xs font-bold">Aucune course active</p>
        </div>
      <?php endif; ?>

      <?php
      foreach ($commandes as $cmd):
        // --- CALCUL DE LA RAPIDITÉ ET DES COULEURS DE DÉLAIS ---
        $heure_creation = strtotime($cmd['created_at']);
        $heure_actuelle = time();
        $minutes_ecoulees = round(($heure_actuelle - $heure_creation) / 60);

        if ($minutes_ecoulees < 120) { // Moins de 2h
          $color_bg = 'bg-green-500';
          $color_text = 'text-green-700';
          $color_light_bg = 'bg-green-50';
          $delai_status = "Dans les délais";
        } elseif ($minutes_ecoulees >= 120 && $minutes_ecoulees < 240) { // Entre 2h et 4h
          $color_bg = 'bg-amber-500';
          $color_text = 'text-amber-700';
          $color_light_bg = 'bg-amber-50';
          $delai_status = "Délai presque atteint";
        } else { // Plus de 4h
          $color_bg = 'bg-red-500';
          $color_text = 'text-red-700';
          $color_light_bg = 'bg-red-50';
          $delai_status = "En retard";
        }

        // Formatage lisible du temps écoulé
        if ($minutes_ecoulees < 60) {
          $temps_affichage = "depuis " . $minutes_ecoulees . " min";
        } else {
          $heures = floor($minutes_ecoulees / 60);
          $restant_minutes = $minutes_ecoulees % 60;
          $temps_affichage = "depuis " . $heures . "h " . ($restant_minutes > 0 ? $restant_minutes . "m" : "");
        }
      ?><div class="bg-white border-l-4 p-8 rounded-3xl shadow-sm hover:shadow-md transition-all relative border border-gray-100" style="border-left-color: <?php if ($minutes_ecoulees < 120) echo '#22c55e';
                                                                                                                                                            elseif ($minutes_ecoulees < 240) echo '#f59e0b';
                                                                                                                                                            else echo '#ef4444'; ?>;">

          <div class="absolute top-8 left-24 flex items-center gap-1.5 px-2.5 py-1 rounded-full <?= $color_light_bg ?> <?= $color_text ?> text-[8px] font-black uppercase tracking-wider">
            <span class="w-2 h-2 rounded-full <?= $color_bg ?>"></span>
            <?= $delai_status ?> (<?= $temps_affichage ?>)
          </div>

          <div class="absolute top-8 right-8">
            <?php if ($cmd['statut'] === 'en_route'): ?>
              <span class="bg-amber-100 text-amber-800 text-[9px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider animate-pulse">En route</span>
            <?php else: ?>
              <span class="bg-blue-100 text-blue-800 text-[9px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">Au dépôt</span>
            <?php endif; ?>
          </div>

          <div class="flex justify-between items-start mb-10 mt-4">
            <span class="text-[10px] font-bold border border-black px-3 py-1 uppercase">#<?= $cmd['id'] ?></span>
            <span class="text-2xl font-serif-felikay italic font-bold mr-16"><?= number_format($cmd['total_ttc'], 2) ?> $</span>
          </div>

          <div class="space-y-6">
            <div>
              <h3 class="text-[9px] uppercase tracking-[0.2em] text-gray-400 font-bold mb-1">Client</h3>
              <p class="text-lg font-bold uppercase"><?= htmlspecialchars($cmd['client_nom'] ?? 'Client') ?></p>
            </div>

            <div class="flex items-start gap-3">
              <span class="text-sm">📍</span>
              <div class="text-xs">
                <p class="font-bold uppercase text-blue-600">
                  <?= htmlspecialchars($cmd['commune'] ?? 'Commune non précisée') ?>
                  <span class="text-gray-400 font-normal">|</span>
                  <?= htmlspecialchars($cmd['quartier'] ?? 'Quartier non précisé') ?>
                </p>
                <p class="text-gray-500 italic mt-1">
                  <?= htmlspecialchars($cmd['adresse_livraison'] ?? 'Pas d\'adresse précise') ?>
                </p>
              </div>
            </div>

            <a href="tel:<?= $cmd['client_tel'] ?? '' ?>" class="block w-full text-center py-3 bg-blue-50 text-blue-700 rounded-xl text-[10px] font-bold uppercase tracking-widest">
              Appeler : <?= htmlspecialchars($cmd['client_tel'] ?? 'N/A') ?>
            </a>

            <?php if ($cmd['statut'] !== 'en_route'): ?>
              <form action="../../assets/actions/depart_livreur.php" method="POST" class="pt-2">
                <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-xl text-[10px] font-bold uppercase tracking-[0.2em] transition">
                  🚀 Se mettre en route
                </button>
              </form>
            <?php endif; ?>

            <form action="../../assets/actions/valider_livraison.php" method="POST" class="pt-6 border-t border-gray-100">
              <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
              <input type="number" name="code_saisi" placeholder="CODE CLIENT" class="w-full bg-gray-50 border border-gray-200 py-4 rounded-2xl text-center text-xl font-bold tracking-[0.5em] focus:ring-2 focus:ring-black outline-none mb-3" required>
              <button type="submit" class="w-full bg-black text-white py-4 rounded-2xl text-[10px] font-bold uppercase tracking-[0.3em]">Confirmer la remise</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="pt-10 border-t border-gray-200">
      <h2 class="text-2xl font-black uppercase tracking-tighter mb-8">Historique Récent</h2>
      <div class="bg-white rounded-3xl shadow-sm overflow-hidden border border-gray-100">
        <table class="w-full text-left">
          <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-400">
            <tr>
              <th class="p-5">ID</th>
              <th class="p-5">Client</th>
              <th class="p-5">Montant</th>
              <th class="p-5 text-right">Date</th>
            </tr>
          </thead>
          <tbody class="text-xs">
            <?php foreach ($historique as $h): ?>
              <tr class="border-t border-gray-50">
                <td class="p-5 font-bold">#<?= $h['id'] ?></td>
                <td class="p-5"><?= htmlspecialchars($h['client_nom']) ?></td>
                <td class="p-5 font-bold text-green-600"><?= number_format($h['total_ttc'], 2) ?> $</td>
                <td class="p-5 text-right text-gray-400 italic">Le <?= date('d/m à H:i', strtotime($h['updated_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($historique)): ?>
              <tr>
                <td colspan="4" class="p-10 text-center text-gray-300 italic uppercase tracking-widest">Aucun historique</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <footer class="text-center py-20 text-gray-400 text-[8px] uppercase tracking-[0.5em] font-bold">
    Felikay &copy; <?= date('Y') ?> &bull; Logistique & Performance
  </footer>

</body>

</html>