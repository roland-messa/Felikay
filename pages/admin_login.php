<?php
// C:\wamp64\www\ProjetFelykay\pages\admin_login.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/function.php';

// 1. Création de l'empreinte si inexistante
if (!isset($_SESSION['admin_access_gate'])) {
  $_SESSION['admin_access_gate'] = true;
  $_SESSION['admin_access_ip'] = $_SERVER['REMOTE_ADDR'];
  $_SESSION['admin_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}

// 2. Génération du Token CSRF (une seule fois par session)
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Vérification du blocage
if (isset($_SESSION['blocked_until']) && $_SESSION['blocked_until'] > time()) {
  $minutes = ceil(($_SESSION['blocked_until'] - time()) / 60);
  die("Sécurité : Trop d'échecs. Réessayez dans $minutes minute(s).");
}

// 4. Redirection automatique si déjà connecté (Correction ici pour inclure le livreur)
if (isset($_SESSION['user_role'])) {
  if ($_SESSION['user_role'] === 'admin') {
    header("Location: admin/admin_dashboard.php");
    exit();
  } elseif ($_SESSION['user_role'] === 'livreur') {
    header("Location: livreur/dashboard.php");
    exit();
  }
}

$pageTitle = "Felikay | Connexion Staff";
include __DIR__ . '/../includes/header.php';
?>

<style>
  #admin-content {
    opacity: 0;
    transition: opacity 0.8s ease;
  }

  .loaded #admin-content {
    opacity: 1;
  }

  .logo-container {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    overflow: hidden;
    border: 1px solid #f3f2ee;
    margin: 0 auto 20px auto;
    background: white;
  }

  .logo-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .admin-input {
    width: 100%;
    border-bottom: 1px solid #e5e7eb;
    padding: 10px 0;
    font-size: 12px;
    outline: none;
    transition: border-color 0.3s;
    background: transparent;
  }

  .admin-input:focus {
    border-bottom: 1px solid #000;
  }
</style>

<main id="admin-content" class="bg-[#fcfbfb] min-h-screen flex items-center justify-center px-6">
  <div class="w-full max-w-[400px]">

    <?php if (function_exists('display_alert')) display_alert(); ?>

    <div class="bg-white p-10 shadow-2xl border border-gray-50">

      <div class="text-center mb-10">
        <div class="logo-container">
          <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo Felikay">
        </div>
        <h1 class="font-serif text-2xl italic">Espace Personnel</h1>
        <p class="text-[9px] uppercase tracking-[0.3em] text-gray-400 mt-2">Administration & Logistique</p>
      </div>

      <form action="../assets/actions/login_process.php" method="POST" class="space-y-6">

        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

        <div class="space-y-2">
          <label class="text-[9px] uppercase tracking-widest font-bold text-black">Identifiant</label>
          <input type="email" name="email" required class="admin-input" autocomplete="off">
        </div>

        <div class="space-y-2">
          <label class="text-[9px] uppercase tracking-widest font-bold text-black">Mot de passe</label>
          <input type="password" name="password" required class="admin-input" autocomplete="off">
        </div>

        <button type="submit" class="w-full bg-black text-white py-4 text-[10px] uppercase tracking-[0.4em] hover:bg-stone-800 transition">
          Connexion
        </button>
      </form>

      <div class="mt-8 text-center">
        <a href="../index.php" class="text-[9px] uppercase tracking-widest text-gray-300 hover:text-black transition">
          ← Retour au site
        </a>
      </div>
    </div>
  </div>
</main>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
  window.addEventListener('load', () => {
    const loader = document.getElementById('loader');
    setTimeout(() => {
      if (loader) {
        loader.style.transition = "opacity 0.6s ease";
        loader.style.opacity = "0";
        document.getElementById('admin-content').parentElement.classList.add('loaded');
        setTimeout(() => {
          loader.style.display = 'none';
        }, 600);
      }
    }, 1200);
    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
</script>
</body>

</html>