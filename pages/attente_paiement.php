<?php
require_once __DIR__ . '/../config/db.php';
$pageTitle = "Felykay | Attente de Paiement";
include __DIR__ . '/../includes/header.php';

// 1. On récupère uniquement la référence de l'URL
$ref = htmlspecialchars($_GET['ref'] ?? '');

// Initialisation des variables par défaut
$phone = "N/A";
$amount = "0.00";

// 2. INTERROGATION DE LA BDD (La source de vérité)
if (!empty($ref)) {
  try {
    // On récupère le téléphone et le montant directement en BDD
    $stmt = $pdo->prepare("SELECT telephone_paiement, montant FROM paiements WHERE reference_interne = ?");
    $stmt->execute([$ref]);
    $res = $stmt->fetch();

    if ($res) {
      $phone = $res['telephone_paiement'];
      $amount = $res['montant'];
    } else {
      echo "<script>alert('Référence de paiement invalide'); window.location.href='paiement.php';</script>";
      exit;
    }
  } catch (Exception $e) {
    $amount = "Erreur BDD";
  }
} else {
  header('Location: paiement.php');
  exit;
}
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

      <div class="mb-8">
        <p class="text-sm text-stone-500">Veuillez confirmer le paiement de :</p>
        <p class="text-2xl font-bold text-black"><?= $amount ?> $</p>
        <p class="text-[11px] text-stone-400 mt-2 uppercase tracking-widest">
          Mobile Money : <span class="font-bold text-black"><?= $phone ?></span>
        </p>
      </div>

      <div class="p-5 bg-stone-50 border border-stone-100 rounded-xl text-[10px] text-stone-400 uppercase tracking-widest leading-relaxed">
        Ne fermez pas cette page. Elle vous redirigera automatiquement dès que vous aurez saisi votre code PIN sur votre téléphone.
      </div>

    </div>
  </div>
</div>

<!-- Footer Personnalisé AfreePay -->
<footer class="mt-auto py-10 border-t border-stone-100 bg-[#FAFAFA] text-center">
  <div class="max-w-xs mx-auto">
    <img src="https://www.afreepay.com/img/afreepaylogo.png"
      alt="AfreePay"
      class="h-16 w-16 object-contain mx-auto mb-4 opacity-80">
    <div class="text-[11px] text-stone-600 uppercase tracking-wider font-medium">
      Paiement sécurisé par <strong>AfreePay</strong>
    </div>
    <div class="text-[9px] text-stone-400 mt-2 uppercase tracking-tight">
      Mobile Money & Online Payments
    </div>
    <div class="text-[9px] text-stone-300 mt-6 font-mono">
      © 2026 AFREEPAY. ALL RIGHTS RESERVED.
    </div>
  </div>
</footer>

<script>
  console.log("JS chargé avec succès : Felikay Engine");

  document.addEventListener("DOMContentLoaded", function() {
    const paymentRef = "<?= $ref ?>";
    let interval = null;
    let attempts = 0;
    const maxAttempts = 18; // 1min30 de polling
    let isFinished = false;

    function stopPolling() {
      if (interval) clearInterval(interval);
      isFinished = true;
    }

    function checkPaymentStatus() {
      if (isFinished || !paymentRef) return;

      console.log("Tentative :", attempts + 1);

      fetch("/ProjetFelykay/assets/actions/check_status.php?ref=" + encodeURIComponent(paymentRef) + "&t=" + Date.now())
        .then(res => res.json())
        .then(data => {
          console.log("STATUS :", data);

          // SUCCESS : Arrêt immédiat et redirection
          if (data.status === 'reussi' || data.status === 'paye' || data.status === 'success') {
            stopPolling();
            window.location.href = "confirmation_succes.php?id=" + data.commande_id;
            return;
          }

          // ÉCHEC : On affiche l'erreur mais on ne bloque pas forcément si l'API est instable
          if (data.status === 'echoue') {
            console.log("L'API indique un échec, on continue de vérifier au cas où...");
          }

          attempts++;

          // TIMEOUT FINAL
          if (attempts >= maxAttempts) {
            stopPolling();
            document.getElementById('status-container').innerHTML = `
              <h2 class="text-red-600 text-xl font-bold mb-4">Temps expiré</h2>
              <p class="text-stone-500 mb-6">Nous n'avons pas reçu la confirmation. Vérifiez votre téléphone.</p>
              <a href="paiement.php" class="bg-black text-white px-6 py-2 rounded-full uppercase text-xs tracking-widest">Réessayer</a>
            `;
          }
        })
        .catch(err => {
          console.error("Erreur réseau :", err);
        });
    }

    // Premier appel immédiat puis toutes les 5s
    checkPaymentStatus();
    interval = setInterval(checkPaymentStatus, 5000);
  });
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