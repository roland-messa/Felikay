<?php
session_start();
require_once '../config/db.php';
// require_once '../includes/session_check.php';  

$user_id = $_SESSION['user_id'] ?? null;
$nom = '';
$email = '';
$telephone = '';
$adresse = '';

if ($user_id) {
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch();
  if ($user) {
    $nom = $user['nom'] ?? '';
    $email = $user['email'] ?? '';
    $telephone = $user['telephone'] ?? '';
    $adresse = $user['adresse'] ?? '';
  }
}

$selected_method = $_GET['select'] ?? 'online';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Felikay | Paiement Sécurisé</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,700;1,400&display=swap" rel="stylesheet">
  <style>
    .font-serif {
      font-family: 'Playfair Display', serif;
    }

    body {
      font-family: 'Montserrat', sans-serif;
    }

    .payment-radio:checked+label {
      border-color: #000;
      background-color: #f9f9f9;
      box-shadow: 0 0 0 1px #000;
    }

    /* Toggle Switch Style */
    .switch {
      position: relative;
      display: inline-block;
      width: 40px;
      height: 20px;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 20px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 14px;
      width: 14px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }

    input:checked+.slider {
      background-color: #000;
    }

    input:checked+.slider:before {
      transform: translateX(20px);
    }
  </style>
</head>

<body class="bg-[#FDFDFD]">

  <nav class="py-6 px-8 border-b border-gray-100 bg-white flex flex-col items-center gap-2">
    <div class="w-12 h-12 rounded-full overflow-hidden border border-gray-100">
      <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo" class="w-full h-full object-cover">
    </div>
    <a href="../index.php" class="font-serif text-2xl font-bold uppercase tracking-tighter italic">Felikay</a>
  </nav>

  <main class="max-w-[1200px] mx-auto px-6 py-12">

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
      <section>
        <form id="checkout-form" action="" method="POST" class="space-y-6">
          <input type="hidden" name="payment_method" value="<?= $selected_method ?>">

          <h2 class="font-serif text-2xl italic mb-6">Vos informations</h2>
          <div class="space-y-4">
            <input type="text" name="nom_complet" placeholder="Nom complet" value="<?= htmlspecialchars($nom) ?>" required class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black">

            <input type="email" name="email" id="client_email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" <?= $user_id ? 'readonly' : 'required' ?> class="w-full border border-gray-200 p-3 text-sm outline-none <?= $user_id ? 'bg-gray-50' : '' ?>">
          </div>

          <!-- OPTION DE LIVRAISON -->
          <div class="pt-6 border-t border-gray-100">
            <div class="flex items-center justify-between bg-gray-50 p-4 rounded border border-gray-100">
              <div>
                <h3 class="text-sm font-bold uppercase tracking-widest">Se faire livrer ?</h3>
                <p class="text-[10px] text-gray-500">Cochez pour une livraison à domicile</p>
              </div>
              <label class="switch">
                <input type="checkbox" id="delivery_toggle" name="include_delivery" onchange="toggleDelivery()">
                <span class="slider"></span>
              </label>
            </div>
          </div>

          <!-- BLOC ADRESSE BOUTIQUE (Affiché par défaut) -->
          <div id="shop-address" class="p-4 border border-dashed border-gray-300 bg-white space-y-2">
            <div class="flex items-center gap-2 text-black">
              <i data-lucide="store" class="w-4 h-4"></i>
              <span class="text-[10px] font-bold uppercase tracking-widest">Retrait en boutique (Gratuit)</span>
            </div>
            <p class="text-sm text-gray-600">28 cadeco ; Commune de la gombe</p>
            <p class="text-[11px] text-stone-400 italic">Réf : Place des Évolués / Cuisine Parfaite</p>
          </div>

          <!-- FORMULAIRE LIVRAISON (Masqué par défaut) -->
          <div id="address-section" class="hidden space-y-4 animate-fadeIn">
            <h2 class="font-serif text-xl italic">Détails de livraison</h2>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="text-[9px] uppercase font-bold tracking-widest text-stone-400">Commune</label>
                <select name="commune" id="commune_select" class="w-full border border-gray-200 p-3 text-sm bg-white outline-none focus:border-black">
                  <option value="" disabled selected>Choisir...</option>
                  <option value="Gombe">Gombe</option>
                  <option value="Ngaliema">Ngaliema</option>
                  <option value="Limete">Limete</option>
                  <option value="Kintambo">Kintambo</option>
                  <option value="Bandalungwa">Bandalungwa</option>
                  <option value="Lingwala">Lingwala</option>
                  <option value="Barumbu">Barumbu</option>
                  <option value="Kinshasa">Kinshasa (Commune)</option>
                  <option value="Kalamu">Kalamu</option>
                  <option value="Lemba">Lemba</option>
                  <option value="Matete">Matete</option>
                  <option value="Masina">Masina</option>
                  <option value="Ndjili">N'djili</option>
                  <option value="Bumbu">Bumbu</option>
                  <option value="Kasa-Vubu">Kasa-Vubu</option>
                  <option value="Kimbanseke">Kimbanseke</option>
                  <option value="Kisenso">Kisenso</option>
                  <option value="Makala">Makala</option>
                  <option value="Maluku">Maluku</option>
                  <option value="Mont-Ngafula">Mont-Ngafula</option>
                  <option value="Ngaba">Ngaba</option>
                  <option value="Ngiri-Ngiri">Ngiri-Ngiri</option>
                  <option value="Nsele">Nsele</option>
                  <option value="Selembao">Selembao</option>
                </select>
              </div>
              <div class="space-y-1">
                <label class="text-[9px] uppercase font-bold tracking-widest text-stone-400">Téléphone contact</label>
                <input type="tel" name="phone" placeholder="082..." value="<?= htmlspecialchars($telephone) ?>" class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black">
              </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div class="col-span-2 space-y-1">
                <label class="text-[9px] uppercase font-bold tracking-widest text-stone-400">Avenue</label>
                <input type="text" name="avenue" placeholder="Ex: Avenue de l'Équateur" class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black">
              </div>
              <div class="space-y-1">
                <label class="text-[9px] uppercase font-bold tracking-widest text-stone-400">Numéro</label>
                <input type="text" name="numero" placeholder="Ex: 12" class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black">
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="text-[9px] uppercase font-bold tracking-widest text-stone-400">Quartier</label>

                <select name="quartier" id="quartier_select" class="w-full border border-gray-200 p-3 text-sm bg-white outline-none focus:border-black">
                  <option value="" disabled selected>Sélectionnez une commune d'abord</option>
                </select>


                <div id="new_quartier_container" class="hidden mt-2">
                  <input type="text" id="new_quartier_input" placeholder="Nom du nouveau quartier" class="w-full border border-orange-200 p-2 text-xs outline-none focus:border-orange-500 bg-orange-50">
                  <p class="text-[8px] text-orange-600 mt-1 uppercase italic">Ce quartier sera ajouté après validation.</p>
                </div>
              </div>
              <div class="space-y-1">
                <label class="text-[9px] uppercase font-bold tracking-widest text-stone-400">Référence</label>
                <input type="text" name="reference" placeholder="Ex: Près de..." class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black">
              </div>
            </div>
          </div>

          <input type="hidden" name="frais_livraison" id="frais_livraison_input" value="0">

          <?php if ($selected_method === 'online'): ?>
            <div id="gateways-container" class="space-y-4 pt-6 border-t border-gray-100">
              <h2 class="font-serif text-xl italic">Mode de paiement</h2>
              <div class="grid grid-cols-3 gap-3">
                <div>
                  <input type="radio" name="gateway" id="mpesa" value="MP" class="payment-radio hidden" checked>
                  <label for="mpesa" class="flex items-center justify-center border border-gray-200 p-2 cursor-pointer rounded h-14 bg-white"><img src="/ProjetFelykay/assets/img/Mpesa.jpg" class="h-6 object-contain"></label>
                </div>
                <div>
                  <input type="radio" name="gateway" id="airtel" value="AM" class="payment-radio hidden">
                  <label for="airtel" class="flex items-center justify-center border border-gray-200 p-2 cursor-pointer rounded h-14 bg-white"><img src="/ProjetFelykay/assets/img/airtel.jpg" class="h-6 object-contain"></label>
                </div>
                <div>
                  <input type="radio" name="gateway" id="orange" value="OM" class="payment-radio hidden">
                  <label for="orange" class="flex items-center justify-center border border-gray-200 p-2 cursor-pointer rounded h-14 bg-white"><img src="/ProjetFelykay/assets/img/orangeMoney.jpg" class="h-6 object-contain"></label>
                </div>
              </div>

              <div class="pt-3">
                <label class="text-[9px] uppercase font-bold tracking-widest text-black">Numéro Mobile Money (9 chiffres)</label>
                <div class="flex items-center border-b-2 border-black">
                  <span class="text-lg font-bold px-2 text-stone-500">+243</span>
                  <input type="tel" name="payment_phone" placeholder="829057677" pattern="[0-9]{9}" maxlength="9" required class="w-full p-3 text-lg font-bold outline-none bg-transparent">
                </div>
              </div>
            </div>
          <?php endif; ?>

          <input type="hidden" name="cart_data" id="cart_data_input">
          <input type="hidden" name="total_ttc" id="total_ttc_input">
        </form>
      </section>

      <!-- Résumé de la commande -->
      <section class="bg-gray-50 p-8 border border-gray-100 shadow-sm h-fit sticky top-6">
        <h2 class="font-serif text-2xl italic mb-8">Votre commande</h2>
        <div id="order-items" class="space-y-6 mb-8 border-b pb-8 max-h-[400px] overflow-y-auto"></div>

        <div class="space-y-4 text-sm">
          <div class="flex justify-between text-gray-500"><span>Articles</span><span id="order-subtotal">0.00 $</span></div>
          <div class="flex justify-between text-gray-500"><span>Livraison</span><span id="delivery-display">0.00 $</span></div>
          <div class="flex justify-between border-t border-gray-200 pt-6 font-bold text-xl"><span>Total</span><span id="order-total">0.00 $</span></div>
        </div>

        <button type="button" id="final-submit-btn" onclick="submitFinalForm()" class="w-full mt-8 py-4 bg-black text-white text-[10px] uppercase tracking-widest font-bold hover:bg-stone-800 transition flex items-center justify-center gap-3">
          <span id="btn-text"><?= $selected_method === 'online' ? 'Procéder au paiement' : 'Confirmer la commande' ?></span>
          <div id="btn-spinner" class="hidden w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></div>
        </button>
      </section>
    </div>
  </main>

  <script>
    lucide.createIcons();
    const cart = JSON.parse(localStorage.getItem('felikay_cart')) || [];
    const isOnline = "<?= $selected_method ?>" === "online";
    let deliveryFee = 0;

    function toggleDelivery() {
      const isDelivery = document.getElementById('delivery_toggle').checked;
      const addressSection = document.getElementById('address-section');
      const shopAddress = document.getElementById('shop-address');

      // CORRECTION : On cible les inputs normaux, pas le champ "nouveau quartier" masqué
      const standardInputs = addressSection.querySelectorAll('input:not(#new_quartier_input), select:not(#new_quartier_input)');
      const inputNewQuartier = document.getElementById('new_quartier_input');

      if (isDelivery) {
        addressSection.classList.remove('hidden');
        shopAddress.classList.add('hidden');
        standardInputs.forEach(input => input.setAttribute('required', ''));

        // On vérifie s'il faut remettre le required sur le nouveau quartier s'il est visible
        if (inputNewQuartier && !inputNewQuartier.parentElement.classList.contains('hidden')) {
          inputNewQuartier.setAttribute('required', '');
        }
      } else {
        addressSection.classList.add('hidden');
        shopAddress.classList.remove('hidden');

        // On retire le required de TOUS les champs sans exception
        addressSection.querySelectorAll('input, select').forEach(input => {
          input.removeAttribute('required');
          input.value = "";
        });

        deliveryFee = 0;
        document.getElementById('frais_livraison_input').value = 0;
        updateFinalDisplay();
      }
    }

    function updateFinalDisplay() {
      const subtotal = cart.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);
      const finalTotal = subtotal + deliveryFee;

      document.getElementById('order-subtotal').textContent = subtotal.toFixed(2) + " $";
      document.getElementById('delivery-display').textContent = deliveryFee.toFixed(2) + " $";
      document.getElementById('order-total').textContent = finalTotal.toFixed(2) + " $";
      document.getElementById('total_ttc_input').value = finalTotal.toFixed(2);
    }

    async function updateQuartiersList() {
      const commune = document.getElementById('commune_select').value;
      const quartierSelect = document.getElementById('quartier_select');
      if (!commune) return;

      try {
        const response = await fetch(`../assets/actions/get_neighborhoods.php?commune=${encodeURIComponent(commune)}`);
        const quartiers = await response.json();

        quartierSelect.innerHTML = '<option value="" disabled selected>Choisir le quartier...</option>';

        if (quartiers.length > 0) {
          quartiers.forEach(q => {
            const option = document.createElement('option');
            option.value = q.quartier;
            option.dataset.frais = q.frais_usd;
            option.textContent = q.quartier;
            quartierSelect.appendChild(option);
          });
        } else {
          quartierSelect.innerHTML = '<option value="" disabled selected>Aucun quartier disponible</option>';
        }

        // CORRECTION : On s'assure de cacher le champ de saisie libre au changement de commune
        const inputNewQuartier = document.getElementById('new_quartier_input');
        if (inputNewQuartier) {
          inputNewQuartier.removeAttribute('required');
          // Si tu as entouré ton input d'une div pour le masquer, cache-la ici
          if (inputNewQuartier.parentElement) {
            // inputNewQuartier.parentElement.classList.add('hidden'); // Optionnel selon ton HTML
          }
        }

      } catch (e) {
        console.error("Erreur chargement quartiers");
      }
    }

    function submitFinalForm() {
      const form = document.getElementById('checkout-form');
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const btn = document.getElementById('final-submit-btn');
      document.getElementById('btn-text').innerText = "Traitement...";
      document.getElementById('btn-spinner').classList.remove('hidden');
      btn.disabled = true;

      form.action = isOnline ? "../assets/actions/process_online.php" : "../assets/actions/process_cash.php";
      form.submit();
    }

    function displayOrder() {
      const container = document.getElementById('order-items');
      if (cart.length === 0) {
        window.location.href = "../index.php";
        return;
      }

      container.innerHTML = cart.map(item => `
            <div class="flex items-center space-x-4">
                <div class="w-16 h-20 bg-white border border-gray-100 overflow-hidden">
                    <img src="${item.img}" class="w-full h-full object-cover">
                </div>
                <div class="flex-1">
                    <h4 class="text-[10px] uppercase font-bold">${item.name}</h4>
                    <p class="text-[9px] text-gray-400">Qté: ${item.quantity || 1}</p>
                </div>
                <span class="text-xs font-semibold">${(item.price * (item.quantity || 1)).toFixed(2)} $</span>
            </div>
        `).join('');

      document.getElementById('cart_data_input').value = JSON.stringify(cart);
      updateFinalDisplay();
    }

    document.addEventListener('DOMContentLoaded', () => {
      displayOrder();

      document.getElementById('commune_select').addEventListener('change', () => {
        updateQuartiersList();
        deliveryFee = 0;
        updateFinalDisplay();
      });

      document.getElementById('quartier_select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];

        deliveryFee = parseFloat(selectedOption.dataset.frais || 0);

        document.getElementById('frais_livraison_input').value = deliveryFee;
        updateFinalDisplay();
      });
    });
  </script>


</body>

</html>