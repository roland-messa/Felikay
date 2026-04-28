<div class="max-w-md mx-auto text-center py-20 px-6">
  <div id="status-container">
    <div class="animate-spin inline-block w-12 h-12 border-4 border-current border-t-transparent text-black rounded-full mb-6"></div>
    <h2 class="font-serif text-2xl italic mb-4">Attente de confirmation...</h2>
    <p class="text-sm text-gray-500 mb-8">
      Veuillez valider le paiement sur votre téléphone (numéro : <strong><?= htmlspecialchars($_GET['phone'] ?? '') ?></strong>).
      Dès que vous aurez saisi votre code PIN, cette page se mettra à jour.
    </p>
  </div>
</div>

<script>
  const paymentRef = "<?= htmlspecialchars($_GET['ref'] ?? '') ?>";
  let checkInterval;

  function checkPaymentStatus() {
    if (!paymentRef) return;

    fetch(`../assets/actions/check_status.php?ref=${paymentRef}`)
      .then(response => response.json())
      .then(data => {
        // On accepte 'paye' car c'est ce qu'on enregistre dans la table commandes
        if (data.status === 'paye' || data.status === 'paid' || data.status === 'success') {
          clearInterval(checkInterval); // On arrête la vérification

          document.getElementById('status-container').innerHTML = `
                <div class="bg-green-50 p-8 border border-green-100 animate-fadeIn">
                    <i data-lucide="check-circle" class="w-16 h-16 text-green-600 mx-auto mb-4"></i>
                    <h2 class="font-serif text-3xl italic mb-2">Merci Roland !</h2>
                    <p class="text-sm text-gray-600 mb-6">Votre paiement a été confirmé. Votre reçu est prêt.</p>
                    
                    <div class="flex flex-col gap-4">
                        <a href="../assets/actions/generer_recu.php?ref=${paymentRef}" target="_blank" class="bg-black text-white px-8 py-4 text-[10px] uppercase font-bold tracking-[0.2em] hover:bg-stone-800 transition">
                            Télécharger mon reçu PDF
                        </a>
                        <a href="../index.php" class="text-[10px] uppercase underline tracking-widest text-stone-500 hover:text-black">
                            Retour à la boutique
                        </a>
                    </div>
                </div>
            `;
          lucide.createIcons();
          localStorage.removeItem('felikay_cart');
        } else if (data.status === 'failed') {
          clearInterval(checkInterval);
          document.getElementById('status-container').innerHTML = `
                <div class="p-8 border border-red-100 bg-red-50">
                    <h2 class="text-red-600 font-bold mb-2">Échec du paiement</h2>
                    <p class="text-sm text-gray-600 mb-6">La transaction a été annulée ou a échoué.</p>
                    <a href="paiement.php" class="inline-block bg-black text-white px-6 py-3 text-[10px] uppercase font-bold">Réessayer</a>
                </div>
            `;
        }
      })
      .catch(error => console.error('Erreur:', error));
  }

  checkInterval = setInterval(checkPaymentStatus, 3000);
</script>