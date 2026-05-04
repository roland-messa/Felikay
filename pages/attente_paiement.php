<?php
require_once __DIR__ . '/../config/db.php';
$pageTitle = "Felykay | Attente de Paiement";
include __DIR__ . '/../includes/header.php';

$ref = htmlspecialchars($_GET['ref'] ?? '');
$phone = htmlspecialchars($_GET['phone'] ?? 'N/A');
?>

<div class="min-h-[70vh] flex items-center justify-center bg-white">
  <div class="max-w-md w-full text-center px-6 py-12 border border-stone-100 shadow-sm rounded-2xl bg-white">
    <div id="status-container">

      <!-- Animation de chargement -->
      <div class="relative w-16 h-16 mx-auto mb-8">
        <div class="absolute inset-0 border-4 border-stone-100 rounded-full"></div>
        <div class="absolute inset-0 border-4 border-t-black rounded-full animate-spin"></div>
      </div>

      <h2 class="font-serif text-2xl italic mb-4 text-stone-800">Validation en cours...</h2>

      <p class="text-sm text-stone-500 mb-8">
        Veuillez confirmer la transaction sur votre téléphone.<br>
        Mobile Money : <span class="font-bold text-black"><?= $phone ?></span>
      </p>

      <div class="p-5 bg-stone-50 border border-stone-100 rounded-xl text-[10px] text-stone-400 uppercase tracking-widest leading-relaxed">
        Ne fermez pas cette page. Elle s'actualisera dès que vous aurez saisi votre code PIN.
      </div>

    </div>
  </div>
</div>

<script>
  const paymentRef = "<?= $ref ?>";
  let tries = 0;
  const maxTries = 18; // 18 tentatives x 5 secondes = 1 min 30

  function checkPaymentStatus() {
    tries++;

    if (!paymentRef) return;

    // Appel vers ton script local qui interroge la DB puis l'API
    fetch(`../assets/actions/check_status.php?ref=${encodeURIComponent(paymentRef)}`)
      .then(res => res.json())
      .then(data => {

        if (data.status === 'paye') {
          clearInterval(interval);
          const orderId = data.commande_id;

          document.getElementById('status-container').innerHTML = `
            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="check" class="text-green-600 w-8 h-8"></i>
            </div>
            <h2 class="text-2xl font-serif mb-2 italic">Paiement reçu</h2>
            <p class="text-sm text-stone-500 mb-10">Merci pour votre confiance. Votre commande est validée.</p>
            
            <div class="flex flex-col gap-3">
                <a href="admin/generate_invoice.php?id=${orderId}" target="_blank"
                   class="bg-black text-white px-8 py-4 uppercase text-[10px] font-bold tracking-widest hover:bg-stone-800 transition flex items-center justify-center gap-2 shadow-lg shadow-black/5">
                   <i data-lucide="file-text" class="w-4 h-4"></i> Télécharger ma facture
                </a>
                <a href="../index.php" class="text-stone-400 text-[10px] uppercase font-bold tracking-widest hover:text-black py-4 transition">
                   Retour à la boutique
                </a>
            </div>
          `;

          localStorage.removeItem('felikay_cart');
          if (window.lucide) lucide.createIcons();

        } else if (data.status === 'echoue') {
          clearInterval(interval);

          document.getElementById('status-container').innerHTML = `
            <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="x" class="text-red-600 w-8 h-8"></i>
            </div>
            <h2 class="text-xl font-serif mb-4 italic">Paiement échoué</h2>
            <p class="text-sm text-stone-500 mb-8">${data.message || 'La transaction n\'a pas pu être finalisée.'}</p>
            <a href="paiement.php" class="bg-black text-white px-8 py-4 uppercase text-[10px] font-bold tracking-widest hover:bg-stone-800 transition">
                Réessayer le paiement
            </a>
          `;
          if (window.lucide) lucide.createIcons();

        } else if (tries >= maxTries) {
          // Arrêt après 1 minute 30 (18 tentatives)
          clearInterval(interval);

          document.getElementById('status-container').innerHTML = `
            <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="clock" class="text-amber-600 w-8 h-8"></i>
            </div>
            <h2 class="text-xl font-serif mb-4 italic">Délai d'attente dépassé</h2>
            <p class="text-sm text-stone-500 mb-8">Nous n'avons pas reçu la confirmation après 1 min 30. Veuillez vérifier votre solde ou réessayer.</p>
            <a href="paiement.php" class="bg-black text-white px-8 py-4 uppercase text-[10px] font-bold tracking-widest hover:bg-stone-800 transition">
                Réessayer
            </a>
          `;
          if (window.lucide) lucide.createIcons();
        }
      })
      .catch(err => console.error("Erreur réseau :", err));
  }

  // Lancement du polling : toutes les 5 secondes (5000ms)
  const interval = setInterval(checkPaymentStatus, 5000);
</script>

<style>
  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }

  .animate-spin {
    animation: spin 1s linear infinite;
  }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>