<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Sécurité : Si pas connecté, direction login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les commandes du client connecté uniquement
$stmt = $pdo->prepare("SELECT * FROM commandes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$mes_commandes = $stmt->fetchAll();

include '../includes/header.php';
?>

<main class="pt-32 pb-24 bg-[#FDFDFD] min-h-screen">
  <div class="max-w-4xl mx-auto px-6">
    <div class="mb-12">
      <h1 class="font-serif text-3xl italic">Bonjour <?php echo htmlspecialchars($_SESSION['user_nom']); ?>,</h1>
      <p class="text-stone-400 text-sm mt-2">Retrouvez ici l'historique et les factures de vos commandes.</p>
    </div>

    <?php if (empty($mes_commandes)): ?>
      <div class="bg-white border border-dashed border-stone-200 p-12 text-center rounded-2xl">
        <p class="text-stone-400 italic">Vous n'avez pas encore passé de commande.</p>
        <a href="shop.php" class="inline-block mt-4 text-xs font-bold uppercase tracking-widest border-b border-black">Commencer le shopping</a>
      </div>
    <?php else: ?>
      <div class="space-y-4">
        <?php foreach ($mes_commandes as $com):
          // Gestion des couleurs de statut
          $statusClass = "bg-stone-100 text-stone-600";
          if ($com['statut'] == 'paye' || $com['statut'] == 'livre') $statusClass = "bg-green-50 text-green-700 border border-green-100";
          if ($com['statut'] == 'en_attente') $statusClass = "bg-orange-50 text-orange-700 border border-orange-100";
          if ($com['statut'] == 'annule') $statusClass = "bg-red-50 text-red-700 border border-red-100";
        ?>
          <div class="bg-white border border-stone-100 p-6 rounded-2xl flex flex-wrap md:flex-nowrap justify-between items-center shadow-sm hover:shadow-md transition-shadow">

            <div class="w-full md:w-1/4 mb-4 md:mb-0">
              <p class="text-[10px] uppercase font-bold text-stone-400 tracking-tighter">Commande #<?php echo $com['id']; ?></p>
              <p class="text-sm font-medium mt-1"><?php echo date('d/m/Y', strtotime($com['created_at'])); ?></p>
            </div>

            <div class="w-1/2 md:w-1/4 text-left md:text-center">
              <span class="px-3 py-1 rounded-full text-[9px] uppercase font-black <?php echo $statusClass; ?>">
                <?php echo str_replace('_', ' ', $com['statut']); ?>
              </span>
            </div>

            <div class="w-1/2 md:w-1/4 text-right md:text-center">
              <p class="font-serif italic text-lg text-slate-900"><?php echo formatPrice($com['total_ttc']); ?></p>
            </div>

            <div class="w-full md:w-1/4 text-right mt-4 md:mt-0">
              <a href="admin/generate_invoice.php?id=<?php echo $com['id']; ?>"
                target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 bg-black text-white text-[10px] font-bold uppercase rounded-xl hover:bg-stone-800 transition-colors shadow-lg">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Facture
              </a>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include '../includes/footer.php'; ?>