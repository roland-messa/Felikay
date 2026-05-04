<?php
session_start();
require_once '../config/db.php';

$commande_id = $_GET['order'] ?? $_GET['id'] ?? null;

if (!$commande_id) {
  header("Location: ../index.php");
  exit();
}

// On vérifie le mode de paiement dans la table 'paiements'
$stmt = $pdo->prepare("
    SELECT mode_paiement 
    FROM paiements 
    WHERE commande_id = ? 
    LIMIT 1
");
$stmt->execute([$commande_id]);
$mode = $stmt->fetchColumn();

// CASH est la valeur utilisée dans process_cash.php
$is_delivery = (strtoupper($mode) === 'CASH');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Felikay | Merci pour votre commande</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Montserrat:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
    }

    .font-serif {
      font-family: 'Playfair Display', serif;
    }
  </style>
</head>

<body class="bg-[#FDFDFD] min-h-screen flex flex-col">

  <!-- Header / Logo en haut -->
  <header class="w-full py-8 flex flex-col items-center border-b border-stone-50 bg-white">
    <div class="w-16 h-16 rounded-full overflow-hidden border border-stone-100 mb-2 shadow-sm">
      <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo Felikay" class="w-full h-full object-cover">
    </div>
    <span class="font-serif text-xl font-bold uppercase tracking-[0.2em] italic">Felikay</span>
    <p class="text-[9px] text-stone-400 uppercase tracking-widest mt-1">Kinshasa</p>
  </header>

  <!-- Contenu Central -->
  <main class="flex-grow flex items-center justify-center px-6">
    <div class="max-w-md w-full text-center">

      <div class="mb-6 flex justify-center">
        <div class="w-12 h-[1px] bg-stone-200"></div>
      </div>

      <h1 class="font-serif text-5xl italic mb-6 text-stone-800">Merci !</h1>

      <p class="text-stone-500 text-sm mb-12 leading-relaxed">
        <?php if ($is_delivery): ?>
          Votre commande a bien été enregistrée.<br>Le paiement se fera directement à la livraison.
        <?php else: ?>
          Votre paiement a été confirmé avec succès.<br>Nous préparons désormais votre colis avec soin.
        <?php endif; ?>
      </p>

      <div class="flex flex-col gap-4">
        <a href="admin/generate_invoice.php?id=<?= htmlspecialchars($commande_id) ?>" target="_blank"
          class="bg-black text-white px-8 py-5 uppercase text-[10px] font-bold tracking-widest hover:bg-stone-800 transition-all duration-300 shadow-lg shadow-black/5">
          Télécharger la facture
        </a>

        <a href="../index.php" class="text-stone-400 text-[10px] uppercase font-bold tracking-widest hover:text-black transition-colors duration-300 py-2">
          Continuer mes achats
        </a>
      </div>
    </div>
  </main>

  <!-- Petit footer discret -->
  <footer class="py-8 text-center">
    <p class="text-[10px] text-stone-300 uppercase tracking-widest">&copy; <?= date('Y') ?> Felikay Maison de Mode</p>
  </footer>

  <script>
    // Nettoyage du panier après succès
    localStorage.removeItem('felikay_cart');
  </script>
</body>

</html>