<?php
session_start();
require_once '../config/db.php';

$user_id = $_SESSION['user_id'] ?? null;
$nom = '';
$email = '';
$telephone = '';
$adresse = '';
$ville_actuelle = 'Kinshasa';

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

    #confirm-modal {
      display: none;
    }

    .modal-active {
      overflow: hidden;
    }
  </style>
</head>

<body class="bg-[#FDFDFD]">

  <div id="confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white max-w-md w-full p-8 shadow-2xl">
      <h3 class="font-serif text-2xl italic mb-4">Confirmer votre commande</h3>
      <p class="text-sm text-gray-500 mb-8" id="modal-message"></p>
      <div class="flex gap-4">
        <button onclick="closeModal()" class="flex-1 py-4 border border-gray-200 text-[10px] uppercase tracking-widest font-bold hover:bg-gray-50">Annuler</button>
        <button onclick="submitFinalForm()" class="flex-1 py-4 bg-black text-white text-[10px] uppercase tracking-widest font-bold hover:bg-stone-800">Confirmer</button>
      </div>
    </div>
  </div>

  <nav class="py-6 px-8 border-b border-gray-100 bg-white flex flex-col items-center gap-2">
    <div class="w-12 h-12 rounded-full overflow-hidden border border-gray-100">
      <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo" class="w-full h-full object-cover">
    </div>
    <a href="../index.php" class="font-serif text-2xl font-bold uppercase tracking-tighter italic">Felikay</a>
  </nav>

  <main class="max-w-[1200px] mx-auto px-6 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">

      <section>
        <form id="checkout-form" action="../assets/actions/process_order.php" method="POST" class="space-y-6">
          <input type="hidden" name="payment_method" value="<?= $selected_method ?>">

          <h2 class="font-serif text-2xl italic mb-6">Vos informations</h2>
          <div class="space-y-4">
            <input type="text" name="nom_complet" placeholder="Nom complet" value="<?= htmlspecialchars($nom) ?>" required class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black">
            <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" <?= $user_id ? 'readonly' : 'required' ?> class="w-full border border-gray-200 p-3 text-sm outline-none <?= $user_id ? 'bg-gray-50' : '' ?>">
          </div>

          <div class="bg-gray-50 p-4 border border-gray-200">
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="checkbox" id="include_delivery" name="include_delivery" class="w-4 h-4 accent-black"
                <?= ($selected_method === 'delivery') ? 'checked' : '' ?> onchange="toggleAddressFields(this.checked)">
              <span class="text-xs font-bold uppercase tracking-widest">Je souhaite une livraison à domicile</span>
            </label>
          </div>

          <div id="address-section" class="<?= ($selected_method === 'online') ? 'hidden' : '' ?> space-y-4 pt-4 border-t border-gray-100">
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
                <label class="text-[9px] uppercase font-bold tracking-widest text-stone-400">Téléphone de contact</label>
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
                <input type="text" name="numero" placeholder="Ex: 12 bis" class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black">
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <label class="text-[9px] uppercase font-bold tracking-widest text-stone-400">Quartier</label>
                <input type="text" name="quartier" placeholder="Nom du quartier" class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black">
              </div>
              <div class="space-y-1">
                <label class="text-[9px] uppercase font-bold tracking-widest text-stone-400">Référence précise</label>
                <input type="text" name="reference" placeholder="Ex: Près de l'arrêt bus" class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black">
              </div>
            </div>

            <p class="text-[9px] text-stone-400 italic italic">Note : Plus l'adresse est précise, plus vite vous serez livré.</p>
          </div>

          <?php if ($selected_method === 'online'): ?>
            <div id="gateways-container" class="space-y-4 pt-6">
              <h2 class="font-serif text-xl italic">Mode de paiement</h2>
              <div class="pt-2"></div>
              <div class="grid grid-cols-3 gap-3">
                <div class="relative group">
                  <p class="text-[8px] uppercase font-bold tracking-tighter text-center mb-1 text-stone-400 group-hover:text-black transition-colors">M-Pesa</p>
                  <input type="radio" name="gateway" id="mpesa" value="mpesa" class="payment-radio hidden" checked>
                  <label for="mpesa" class="flex items-center justify-center border border-gray-200 p-2 cursor-pointer rounded h-14 bg-white hover:border-stone-400 transition-all">
                    <img src="/ProjetFelykay/assets/img/Mpesa.jpg" class="h-5 object-contain" alt="M-pesa">
                  </label>
                </div>

                <div class="relative group">
                  <p class="text-[8px] uppercase font-bold tracking-tighter text-center mb-1 text-stone-400 group-hover:text-black transition-colors">Orange Money</p>
                  <input type="radio" name="gateway" id="orange" value="orange" class="payment-radio hidden">
                  <label for="orange" class="flex items-center justify-center border border-gray-200 p-2 cursor-pointer rounded h-14 bg-white hover:border-stone-400 transition-all">
                    <img src="/ProjetFelykay/assets/img/orangeMoney.jpg" class="h-5 object-contain" alt="Orange">
                  </label>
                </div>

                <div class="relative group">
                  <p class="text-[8px] uppercase font-bold tracking-tighter text-center mb-1 text-stone-400 group-hover:text-black transition-colors">Airtel Money</p>
                  <input type="radio" name="gateway" id="airtel" value="airtel" class="payment-radio hidden">
                  <label for="airtel" class="flex items-center justify-center border border-gray-200 p-2 cursor-pointer rounded h-14 bg-white hover:border-stone-400 transition-all">
                    <img src="/ProjetFelykay/assets/img/airtelMoney.png" class="h-7 object-contain" alt="Airtel">
                  </label>
                </div>

              </div>
              <div class="pt-3">
                <label class="text-[9px] uppercase font-bold tracking-widest text-black">Numéro Mobile Money</label>
                <input type="tel" name="payment_phone" placeholder="Ex: 081..." required class="w-full border-b-2 border-black p-3 text-lg font-bold outline-none bg-transparent">
              </div>
            </div>
          <?php endif; ?>



          <input type="hidden" name="cart_data" id="cart_data_input">
          <input type="hidden" name="total_ttc" id="total_ttc_input">
        </form>
      </section>










      <section class="bg-gray-50 p-8 border border-gray-100 shadow-sm h-fit">
        <h2 class="font-serif text-2xl italic mb-8">Votre commande</h2>
        <div id="order-items" class="space-y-6 mb-8 border-b pb-8 max-h-[400px] overflow-y-auto"></div>
        <div class="space-y-4 text-sm">
          <div class="flex justify-between text-gray-500"><span>Articles</span><span id="order-subtotal">0.00 $</span></div>
          <div class="flex justify-between border-t border-gray-200 pt-6 font-bold text-xl"><span>Total</span><span id="order-total">0.00 $</span></div>
        </div>
        <button id="confirm-button" class="w-full bg-black text-white py-5 mt-10 text-[11px] uppercase tracking-[0.3em] font-semibold hover:bg-gray-800 transition-all">
          Confirmer la commande
        </button>
      </section>
    </div>
  </main>

  <script>
    lucide.createIcons();
    const cart = JSON.parse(localStorage.getItem('felikay_cart')) || [];
    const isOnline = "<?= $selected_method ?>" === "online";

    function toggleAddressFields(checked) {
      const section = document.getElementById('address-section');
      const fields = section.querySelectorAll('input, select');

      section.classList.toggle('hidden', !checked);

      fields.forEach(field => {
        field.required = checked;
        if (!checked) {
          field.value = "";
        }
      });
    }

    function closeModal() {
      document.getElementById('confirm-modal').style.display = 'none';
      document.body.classList.remove('modal-active');
    }

    document.getElementById('confirm-button').addEventListener('click', function() {
      const form = document.getElementById('checkout-form');
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const wantsDelivery = document.getElementById('include_delivery').checked;
      let msg = isOnline ? "Passer au paiement Mobile Money" : "Confirmer la commande";
      msg += wantsDelivery ? " avec livraison ?" : " (Retrait en point de vente) ?";

      document.getElementById('modal-message').innerText = msg;
      document.getElementById('confirm-modal').style.display = 'flex';
      document.body.classList.add('modal-active');
    });

    function submitFinalForm() {
      document.getElementById('confirm-button').disabled = true;
      document.getElementById('checkout-form').submit();
    }

    function displayOrder() {
      const container = document.getElementById('order-items');
      if (cart.length === 0) {
        window.location.href = "../index.php";
        return;
      }
      let total = 0;
      container.innerHTML = cart.map(item => {
        const subtotal = item.price * (item.quantity || 1);
        total += subtotal;
        const cleanImg = item.img.startsWith('http') ? item.img : '/ProjetFelykay/' + item.img.replace(/^(\.\.\/|\.\/)/, '');
        return `
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-20 bg-white border border-gray-100 overflow-hidden">
                            <img src="${cleanImg}" class="w-full h-full object-cover" onerror="this.src='/ProjetFelykay/assets/img/felikay.jpg'">
                        </div>
                        <div class="flex-1">
                            <h4 class="text-[10px] uppercase font-bold">${item.name}</h4>
                            <p class="text-[9px] text-gray-400">Qté: ${item.quantity || 1}</p>
                        </div>
                        <span class="text-xs font-semibold">${subtotal.toFixed(2)} $</span>
                    </div>`;
      }).join('');
      document.getElementById('order-total').textContent = total.toFixed(2) + " $";
      document.getElementById('order-subtotal').textContent = total.toFixed(2) + " $";
      document.getElementById('cart_data_input').value = JSON.stringify(cart);
      document.getElementById('total_ttc_input').value = total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', () => {
      displayOrder();
      // Initialise l'état des champs requis au chargement
      toggleAddressFields(document.getElementById('include_delivery').checked);
    });
  </script>
</body>

</html>