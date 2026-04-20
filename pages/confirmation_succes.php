<?php
session_start();
require_once '../config/db.php';

$commande_id = $_GET['id'] ?? null;

if (!$commande_id) {
  header("Location: ../index.php");
  exit();
}

$stmt = $pdo->prepare("SELECT methode_paiement FROM commandes WHERE id = ?");
$stmt->execute([$commande_id]);
$commande = $stmt->fetch();

$is_delivery = ($commande && $commande['methode_paiement'] === 'delivery');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Felikay | Confirmation</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
  <style>
    .font-serif {
      font-family: 'Playfair Display', serif;
    }
  </style>
</head>

<body class="bg-[#FDFDFD] flex items-center justify-center min-h-screen">

  <div class="max-w-md w-full text-center px-6">
    <div class="mb-8 flex justify-center">
      <div class="w-20 h-20 bg-stone-50 rounded-full flex items-center justify-center border border-stone-100">
        <i data-lucide="check" class="w-10 h-10 text-black"></i>
      </div>
    </div>

    <h1 class="font-serif text-4xl italic mb-4">Merci !</h1>

    <?php if ($is_delivery): ?>
      <p class="text-gray-600 text-sm leading-relaxed mb-2">
        Votre commande a été enregistrée avec succès.
      </p>
      <p class="text-orange-600 text-xs font-medium uppercase tracking-widest mb-10">
        Paiement à prévoir lors de la livraison
      </p>
    <?php else: ?>
      <p class="text-gray-600 text-sm leading-relaxed mb-10">
        Votre paiement a été traité avec succès. Nous préparons votre colis.
      </p>
    <?php endif; ?>

    <div class="flex flex-col gap-4">
      <a href="../assets/actions/generer_recu.php?id=<?php echo $commande_id; ?>" target="_blank"
        class="bg-black text-white px-8 py-4 uppercase text-[10px] font-bold tracking-[0.2em] flex items-center justify-center gap-3 hover:bg-stone-800 transition shadow-xl">
        <i data-lucide="file-text" class="w-4 h-4"></i>
        <?php echo $is_delivery ? 'Télécharger mon bon de commande' : 'Télécharger mon reçu de paiement'; ?>
      </a>

      <a href="../index.php" class="border border-stone-200 text-stone-400 px-8 py-4 uppercase text-[10px] font-bold tracking-[0.2em] hover:text-black hover:border-black transition">
        Continuer mes achats
      </a>
    </div>

    <?php if ($is_delivery): ?>
      <p class="mt-8 text-[10px] text-gray-400 italic">
        * N'oubliez pas de prévoir les frais de livraison pour le coursier.
      </p>
    <?php endif; ?>
  </div>

  <script>
    lucide.createIcons();

    localStorage.removeItem('felikay_cart');
  </script>
</body>

</html>