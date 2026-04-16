<?php
// C:\wamp64\www\ProjetFelykay\pages\view_order.php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
  header("Location: admin_dashboard.php");
  exit();
}

$order_id = (int)$_GET['id'];

// 1. Récupérer les infos de la commande et du client
$stmtOrder = $pdo->prepare("
    SELECT c.*, u.nom as client_nom, u.email as client_email, u.telephone
    FROM commandes c
    JOIN users u ON c.user_id = u.id
    WHERE c.id = ?
");
$stmtOrder->execute([$order_id]);
$order = $stmtOrder->fetch();

if (!$order) {
  die("Commande introuvable.");
}

// 2. Récupérer les articles de cette commande
$stmtItems = $pdo->prepare("
    SELECT d.*, p.nom as produit_nom, p.image_principale
    FROM commande_details d
    JOIN produits p ON d.produit_id = p.id
    WHERE d.commande_id = ?
");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll();

include '../includes/header.php';
?>

<main class="pt-32 pb-24 bg-[#F4F4F4] min-h-screen">
  <div class="max-w-4xl mx-auto px-6">

    <a href="admin_dashboard.php" class="text-[10px] uppercase tracking-widest text-stone-400 hover:text-black mb-8 inline-block">← Retour au tableau de bord</a>

    <div class="bg-white p-10 shadow-sm border border-stone-100">
      <header class="flex justify-between items-start border-b border-stone-100 pb-8 mb-8">
        <div>
          <h1 class="font-serif text-3xl italic">Commande #<?php echo $order['id']; ?></h1>
          <p class="text-[10px] uppercase text-stone-400 mt-2">Passée le <?php echo date('d/m/Y à H:i', strtotime($order['created_at'])); ?></p>
        </div>
        <div class="text-right">
          <span class="px-4 py-2 bg-stone-100 text-[10px] uppercase font-bold"><?php echo $order['statut']; ?></span>
        </div>
      </header>

      <div class="grid grid-cols-2 gap-12 mb-12">
        <div>
          <h3 class="text-[10px] uppercase font-bold mb-4">Informations Client</h3>
          <p class="text-sm"><strong><?php echo htmlspecialchars($order['client_nom']); ?></strong></p>
          <p class="text-sm text-stone-500"><?php echo htmlspecialchars($order['client_email']); ?></p>
          <p class="text-sm text-stone-500"><?php echo htmlspecialchars($order['telephone']); ?></p>
        </div>
        <div>
          <h3 class="text-[10px] uppercase font-bold mb-4">Adresse de Livraison</h3>
          <p class="text-sm text-stone-500 italic"><?php echo nl2br(htmlspecialchars($order['adresse_livraison'])); ?></p>
          <p class="text-sm font-bold mt-1"><?php echo htmlspecialchars($order['ville']); ?></p>
        </div>
      </div>

      <h3 class="text-[10px] uppercase font-bold mb-6 italic">Articles commandés</h3>
      <div class="space-y-4">
        <?php foreach ($items as $item): ?>
          <div class="flex items-center justify-between border-b border-stone-50 pb-4">
            <div class="flex items-center gap-4">
              <img src="<?php echo $item['image_principale']; ?>" class="w-12 h-16 object-cover">
              <div>
                <p class="text-sm font-medium"><?php echo htmlspecialchars($item['produit_nom']); ?></p>
                <p class="text-[10px] text-stone-400 uppercase">Taille: <?php echo $item['taille_choisie']; ?> | Couleur: <?php echo $item['couleur_choisie']; ?></p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-serif italic"><?php echo number_format($item['prix_unitaire'], 2); ?> $ x <?php echo $item['quantite']; ?></p>
              <p class="text-xs font-bold"><?php echo number_format($item['prix_unitaire'] * $item['quantite'], 2); ?> $</p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-10 pt-6 border-t-2 border-black flex justify-between items-center">
        <span class="font-serif text-2xl italic">Total TTC</span>
        <span class="font-serif text-2xl italic"><?php echo number_format($order['total_ttc'], 2); ?> $</span>
      </div>
    </div>

    <div class="mt-12 p-8 bg-stone-50 border border-stone-100">
      <h3 class="text-[10px] uppercase font-bold mb-6 tracking-widest">Mettre à jour l'état de la commande</h3>

      <form action="../actions/update_order_status.php" method="POST" class="flex items-end gap-4">
        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">

        <div class="flex-1 space-y-2">
          <label class="text-[9px] uppercase text-stone-400 font-bold">Sélectionner le nouveau statut</label>
          <select name="new_status" class="w-full border border-stone-200 p-3 text-xs uppercase tracking-widest outline-none focus:border-black transition">
            <option value="en_attente" <?php if ($order['statut'] == 'en_attente') echo 'selected'; ?>>En attente</option>
            <option value="paye" <?php if ($order['statut'] == 'paye') echo 'selected'; ?>>Payé / En préparation</option>
            <option value="expedie" <?php if ($order['statut'] == 'expedie') echo 'selected'; ?>>Expédié</option>
            <option value="livre" <?php if ($order['statut'] == 'livre') echo 'selected'; ?>>Livré</option>
            <option value="annule" <?php if ($order['statut'] == 'annule') echo 'selected'; ?>>Annulé</option>
          </select>
        </div>

        <button type="submit" class="bg-black text-white px-8 py-3.5 text-[10px] uppercase tracking-[0.2em] hover:bg-stone-800 transition shadow-md">
          Actualiser le statut
        </button>
      </form>
    </div>

  </div>
</main>

<?php include '../includes/footer.php'; ?>