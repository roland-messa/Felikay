<div class="max-w-md mx-auto text-center py-20 px-6">
  <div id="status-container">
    <!-- Loader animé -->
    <div class="animate-spin inline-block w-12 h-12 border-4 border-stone-200 border-t-black rounded-full mb-6"></div>

    <h2 class="font-serif text-2xl italic mb-4">Attente de confirmation...</h2>

    <p class="text-sm text-gray-500 mb-8 leading-relaxed">
      Veuillez valider le paiement sur votre téléphone.<br>
      Numéro utilisé : <strong><?= htmlspecialchars($_GET['phone'] ?? 'N/A') ?></strong>
    </p>

    <div class="p-4 bg-stone-50 border border-stone-100 rounded text-[11px] text-stone-400 uppercase tracking-widest">
      Dès que vous aurez saisi votre code PIN, cette page se mettra à jour automatiquement.
    </div>
  </div>
</div>

<script>
  // Récupération sécurisée des paramètres de l'URL
  const urlParams = new URLSearchParams(window.location.search);
  const paymentRef = urlParams.get('ref') || "<?= htmlspecialchars($_GET['ref'] ?? '') ?>";
  let checkInterval;

  function checkPaymentStatus() {
    if (!paymentRef) {
      console.error("Référence de paiement manquante.");
      return;
    }

    // Appel vers ton script de vérification
    fetch(`../assets/actions/check_status.php?ref=${encodeURIComponent(paymentRef)}`)
      .then(response => response.json())
      .then(data => {
        // Vérification du statut mis à jour par le callback (paye)
        if (data.status === 'paye' || data.status === 'paid' || data.status === 'success') {
          clearInterval(checkInterval); // Arrêt des requêtes

          document.getElementById('status-container').innerHTML = `
                        <div class="bg-stone-50 p-8 border border-stone-200 animate-fadeIn">
                            <div class="mb-6">
                                <svg class="w-16 h-16 text-green-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h2 class="font-serif text-3xl italic mb-2">Paiement Confirmé</h2>
                            <p class="text-sm text-gray-600 mb-8">Merci pour votre confiance. Votre commande est validée et votre reçu est disponible.</p>
                            
                            <div class="flex flex-col gap-4">
                                <a href="../assets/actions/generer_recu.php?ref=${paymentRef}" target="_blank" class="bg-black text-white px-8 py-4 text-[10px] uppercase font-bold tracking-[0.2em] hover:bg-stone-800 transition">
                                    Télécharger ma facture PDF
                                </a>
                                <a href="../index.php" class="text-[10px] uppercase underline tracking-widest text-stone-500 hover:text-black">
                                    Retour à la boutique
                                </a>
                            </div>
                        </div>
                    `;

          // Nettoyage du panier local après succès
          localStorage.removeItem('felikay_cart');

          // Si tu utilises Lucide Icons
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        } else if (data.status === 'failed' || data.status === 'error' || data.status === 'cancelled') {
          clearInterval(checkInterval);
          document.getElementById('status-container').innerHTML = `
                        <div class="p-8 border border-red-100 bg-red-50">
                            <h2 class="text-red-600 font-bold mb-2 uppercase tracking-tighter">Échec de la transaction</h2>
                            <p class="text-sm text-gray-600 mb-6">Le paiement a été refusé ou annulé sur votre mobile.</p>
                            <a href="paiement.php" class="inline-block bg-black text-white px-8 py-4 text-[10px] uppercase font-bold tracking-widest">
                                Réessayer le paiement
                            </a>
                        </div>
                    `;
        }
      })
      .catch(error => {
        console.error('Erreur lors de la vérification du statut:', error);
      });
  }

  // Lancement de la vérification toutes les 3 secondes (3000ms)
  if (paymentRef) {
    checkInterval = setInterval(checkPaymentStatus, 3000);
  }
</script>