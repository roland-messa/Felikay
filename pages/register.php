<?php
session_start();
$pageTitle = "Felikay | Créer un compte";

$isSecondaryPage = false; // Pour avoir la Navbar blanche
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/function.php';
?>

<main class="min-h-screen bg-[#FDFDFD] flex items-center justify-center pt-32 pb-20 px-6">
  <div class="max-w-md w-full bg-white border border-stone-100 p-10 shadow-sm" data-aos="fade-up">

    <div class="text-center mb-10">
      <h1 class="font-serif text-3xl italic mb-3">Rejoindre la Maison</h1>
      <p class="text-[10px] uppercase tracking-[0.3em] text-stone-400">Inscrivez-vous pour suivre vos commandes</p>
    </div>

    <?php display_alert(); ?>

    <form action="../assets/actions/register_process.php" method="POST" class="space-y-6">

      <div class="space-y-2">
        <label class="text-[9px] uppercase tracking-widest font-bold text-stone-500">Nom Complet</label>
        <input type="text" name="nom" required
          placeholder="ex: Roland Messa"
          class="w-full border-b border-stone-200 py-3 text-sm outline-none focus:border-black transition-all">
      </div>

      <div class="space-y-2">
        <label class="text-[9px] uppercase tracking-widest font-bold text-stone-500">Adresse Email</label>
        <input type="email" name="email" required
          placeholder="votre@email.com"
          class="w-full border-b border-stone-200 py-3 text-sm outline-none focus:border-black transition-all">
      </div>

      <div class="space-y-2">
        <label class="text-[9px] uppercase tracking-widest font-bold text-stone-500">Téléphone</label>
        <input type="tel" name="telephone" required
          placeholder="+243..."
          class="w-full border-b border-stone-200 py-3 text-sm outline-none focus:border-black transition-all">
      </div>

      <div class="space-y-2">
        <label class="text-[9px] uppercase tracking-widest font-bold text-stone-500">Mot de passe</label>
        <input type="password" name="password" required
          placeholder="••••••••"
          class="w-full border-b border-stone-200 py-3 text-sm outline-none focus:border-black transition-all">
      </div>

      <button type="submit"
        class="w-full bg-black text-white py-5 mt-4 text-[10px] uppercase tracking-[0.4em] font-bold hover:bg-stone-800 transition-all shadow-lg active:scale-95">
        Créer mon compte
      </button>

    </form>

    <div class="mt-8 text-center">
      <p class="text-[10px] text-stone-500 uppercase tracking-widest">
        Déjà un compte ? <a href="admin_login.php" class="font-bold text-black underline">Se connecter</a>
      </p>
    </div>

    <p class="mt-12 text-[8px] text-center text-stone-300 uppercase tracking-[0.2em] leading-relaxed">
      En créant un compte, vous acceptez nos <br> conditions générales et notre politique de confidentialité.
    </p>
  </div>
</main>

<?php include '../includes/footer.php'; ?>