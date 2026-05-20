<?php
session_start();
require_once '../config/db.php';

// 1. Récupération de l'ID de la commande (on accepte 'order' ou 'id')
$commande_id = $_GET['order'] ?? $_GET['id'] ?? null;

// Sécurité : On ne redirige que si l'ID de la commande est absent
// La vérification de $_SESSION['user_id'] est retirée pour permettre l'achat invité
if (!$commande_id) {
  header("Location: ../index.php");
  exit();
}

// 2. Récupération de la commande (On retire la condition AND c.user_id = ?)
$stmt = $pdo->prepare("
    SELECT c.id, p.mode_paiement, p.statut_paiement 
    FROM commandes c
    LEFT JOIN paiements p ON c.id = p.commande_id
    WHERE c.id = ? 
    LIMIT 1
");
$stmt->execute([$commande_id]);
$commande = $stmt->fetch();

// Si la commande n'existe pas en base de données, retour à l'accueil
if (!$commande) {
  header("Location: ../index.php");
  exit();
}

// Détermination du message selon le statut du paiement
$is_paid = (isset($commande['statut_paiement']) && in_array(strtolower($commande['statut_paiement']), ['reussi', 'completed', 'success', 'paye']));
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Felikay | Confirmation de commande</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Montserrat:wght@200;400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Montserrat', sans-serif;
      background-color: #FDFDFD;
      color: #1c1c1c;
    }

    .font-serif {
      font-family: 'Playfair Display', serif;
    }

    .reveal {
      animation: reveal 1.2s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes reveal {
      0% {
        opacity: 0;
        transform: translateY(30px);
      }

      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
</head>

<body class="min-h-screen flex flex-col">

  <header class="w-full py-12 flex flex-col items-center">
    <div class="w-20 h-20 rounded-full overflow-hidden mb-4 shadow-sm border border-stone-50">
      <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo Felikay" class="w-full h-full object-cover">
    </div>
    <span class="font-serif text-xl font-bold uppercase tracking-[0.3em] italic">Felykay</span>
  </header>

  <main class="flex-grow flex items-center justify-center px-6">
    <div class="max-w-xl w-full text-center reveal">
      <div class="mb-10">
        <h1 class="font-serif text-7xl italic mb-8 text-stone-800">Merci !</h1>
        <div class="w-12 h-[1px] bg-stone-300 mx-auto"></div>
      </div>

      <div class="space-y-6 mb-16 px-4">
        <?php if (!$is_paid): ?>
          <p class="text-stone-500 text-base md:text-lg leading-relaxed tracking-wide">
            Merci, votre <span class="text-black font-semibold">bon de commande</span> a été enregistré. <br>
            Le paiement se fera directement à la livraison.
          </p>
        <?php else: ?>
          <p class="text-stone-500 text-base md:text-lg leading-relaxed tracking-wide">
            Merci, votre commande est confirmée. <br>
            Veuillez consulter votre mail pour <span class="text-black font-semibold">télécharger votre facture</span>.
          </p>
        <?php endif; ?>
      </div>

      <div class="pt-6">
        <a href="../index.php"
          class="inline-block border border-black px-14 py-5 text-[10px] font-bold uppercase tracking-[0.4em] hover:bg-black hover:text-white transition-all duration-700 ease-in-out shadow-sm">
          Continuer mes achats
        </a>
      </div>
    </div>
  </main>

  <footer class="py-12 text-center">
    <p class="text-[9px] text-stone-400 uppercase tracking-[0.5em] font-light">
      Kinshasa &bull; Maison de Mode &bull; &copy; <?= date('Y') ?>
    </p>
  </footer>

  <script>
    // Nettoyage définitif du panier local
    localStorage.removeItem('felikay_cart');
  </script>
</body>

</html>