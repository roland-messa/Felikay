<?php
// C:\wamp64\www\ProjetFelykay\pages\profile.php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// 1. Protection de la page : Si l'utilisateur n'est pas connecté, retour au login
if (!isset($_SESSION['user_id'])) {
  header("Location: /ProjetFelykay/pages/admin_login.php");
  exit();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/function.php';

$pageTitle = "Maison Felikay | Mon Profil";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

// 2. Récupération des informations fraîches de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT nom, email, role, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si par hasard l'utilisateur n'existe plus en BDD
if (!$user) {
  header("Location: /ProjetFelykay/assets/actions/logout.php");
  exit();
}

// Formatage de la date d'inscription
$date_inscription = date("d/m/Y", strtotime($user['created_at']));
?>

<div class="min-h-screen bg-neutral-50 pt-36 pb-12 font-sans text-neutral-800">
  <div class="max-w-xl mx-auto bg-white border border-neutral-200 p-8 shadow-sm rounded-sm">

    <div class="text-center mb-8">
      <div class="w-20 h-20 bg-neutral-900 text-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
        <span class="text-2xl font-serif tracking-wider">
          <?= strtoupper(substr(trim($user['nom']), 0, 1)) ?>
        </span>
      </div>
      <h1 class="font-serif text-2xl tracking-wide uppercase"><?= htmlspecialchars($user['nom']) ?></h1>
      <p class="text-[10px] uppercase tracking-[0.2em] text-neutral-400 mt-1">Statut : <?= htmlspecialchars($user['role']) ?></p>
    </div>

    <hr class="border-neutral-100 mb-6">

    <div class="space-y-4 text-xs tracking-wide">
      <div class="flex justify-between py-2 border-b border-neutral-100">
        <span class="text-neutral-400 uppercase tracking-wider">Adresse E-mail</span>
        <span class="font-medium text-neutral-900"><?= htmlspecialchars($user['email']) ?></span>
      </div>

      <div class="flex justify-between py-2 border-b border-neutral-100">
        <span class="text-neutral-400 uppercase tracking-wider">Membre depuis le</span>
        <span class="font-medium text-neutral-900"><?= $date_inscription ?></span>
      </div>
    </div>

    <div class="mt-10 space-y-3">

      <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="/ProjetFelykay/pages/admin/admin_dashboard.php"
          class="w-full inline-flex justify-center items-center bg-neutral-900 text-white py-3.5 text-[10px] uppercase tracking-[0.3em] hover:bg-neutral-800 transition duration-500">
          ⚙️ Accéder au Dashboard Admin
        </a>
      <?php elseif (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'livreur'): ?>
        <a href="/ProjetFelykay/pages/admin/livreur_dashboard.php"
          class="w-full inline-flex justify-center items-center bg-neutral-900 text-white py-3.5 text-[10px] uppercase tracking-[0.3em] hover:bg-neutral-800 transition duration-500">
          🚚 Mon Espace Livreur
        </a>
      <?php else: ?>
        <a href="/ProjetFelykay/index.php"
          class="w-full inline-flex justify-center items-center bg-neutral-900 text-white py-3.5 text-[10px] uppercase tracking-[0.3em] hover:bg-neutral-800 transition duration-500">
          Retourner à la boutique
        </a>
      <?php endif; ?>

      <a href="/ProjetFelykay/assets/actions/logout.php"
        class="w-full inline-flex justify-center items-center bg-white text-red-600 border border-red-200 py-3.5 text-[10px] uppercase tracking-[0.3em] hover:bg-red-50 hover:border-red-300 transition duration-500 font-bold">
        🚪 Se déconnecter de la Maison
      </a>

    </div>

  </div>
</div>

<?php
// Inclusion optionnelle du pied de page si disponible
if (file_exists(__DIR__ . '/../includes/footer.php')) {
  include __DIR__ . '/../includes/footer.php';
}
?>