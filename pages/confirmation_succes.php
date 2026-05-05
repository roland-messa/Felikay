<?php
session_start();
require_once '../config/db.php';

// 1. Récupération de l'ID de la commande
$commande_id = $_GET['order'] ?? $_GET['id'] ?? null;

// Sécurité : Si pas d'ID ou si l'utilisateur n'est pas connecté, retour à l'accueil
if (!$commande_id || !isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// 2. On vérifie les détails de la commande et du paiement
$stmt = $pdo->prepare("
    SELECT c.id, p.mode_paiement 
    FROM commandes c
    LEFT JOIN paiements p ON c.id = p.commande_id
    WHERE c.id = ? AND c.user_id = ?
    LIMIT 1
");
$stmt->execute([$commande_id, $_SESSION['user_id']]);
$commande = $stmt->fetch();

if (!$commande) {
  header("Location: ../index.php");
  exit();
}

// CASH est la valeur utilisée pour le paiement à la livraison
$is_delivery = (isset($commande['mode_paiement']) && strtoupper($commande['mode_paiement']) === 'CASH');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Felykay | Merci pour votre commande</title>
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

  <!-- Header / Logo -->
  <header class="w-full py-8 flex flex-col items-center border-b border-stone-50 bg-white">
    <div class="w-16 h-16 rounded-full overflow-hidden border border-stone-100 mb-2 shadow-sm">
      <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo Felykay" class="w-full h-full object-cover">
    </div>
    <span class="font-serif text-xl font-bold uppercase tracking-[0.2em] italic">Felykay</span>
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
        <!-- Lien vers le générateur de PDF (generer_recu.php) -->
        <a href="../assets/actions/generer_recu.php?id=<?= htmlspecialchars($commande_id) ?>"
          target="_blank"
          class="bg-black text-white px-8 py-5 uppercase text-[10px] font-bold tracking-widest hover:bg-stone-800 transition-all duration-300 shadow-lg shadow-black/5">
          Télécharger la facture (PDF)
        </a>

        <a href="../index.php" class="text-stone-400 text-[10px] uppercase font-bold tracking-widest hover:text-black transition-colors duration-300 py-2">
          Continuer mes achats
        </a>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="py-8 text-center">
    <p class="text-[10px] text-stone-300 uppercase tracking-widest">&copy; <?= date('Y') ?> Felykay Maison de Mode</p>
  </footer>

  <script>
    // Nettoyage définitif du panier après la réussite du paiement
    localStorage.removeItem('felikay_cart');
  </script>
</body>

</html>