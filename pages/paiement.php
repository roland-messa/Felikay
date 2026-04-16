<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php?msg=auth_required");
  exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$nom = $user['nom'] ?? '';
$email = $user['email'] ?? '';
$telephone = $user['telephone'] ?? '';
$adresse = $user['adresse'] ?? '';
$ville_actuelle = $user['ville'] ?? 'Kinshasa';
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

    .modal-active {
      overflow: hidden;
    }

    #confirm-modal {
      display: none;
    }
  </style>
</head>

<body class="bg-[#FDFDFD]">

  <div id="confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white max-w-md w-full p-8 shadow-2xl">
      <h3 class="font-serif text-2xl italic mb-4">Confirmer votre commande</h3>
      <p class="text-sm text-gray-500 mb-8" id="modal-message"></p>
      <div class="flex gap-4">
        <button onclick="closeModal()" class="flex-1 py-4 border border-gray-200 text-[10px] uppercase tracking-widest font-bold hover:bg-gray-50 transition">Annuler</button>
        <button onclick="submitFinalForm()" class="flex-1 py-4 bg-black text-white text-[10px] uppercase tracking-widest font-bold hover:bg-stone-800 transition">Oui, commander</button>
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
        <h2 class="font-serif text-2xl italic mb-8">Informations de livraison</h2>

        <form id="checkout-form" action="../assets/actions/process_order.php" method="POST" class="space-y-4">
          <input type="text" name="nom_complet" placeholder="Nom complet" value="<?= htmlspecialchars($nom) ?>" required class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black transition-all">
          <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" readonly class="w-full border border-gray-200 p-3 text-sm bg-gray-50 outline-none">

          <div class="grid grid-cols-2 gap-4">
            <input type="tel" name="phone" placeholder="Téléphone" value="<?= htmlspecialchars($telephone) ?>" required class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black transition-all">
            <select name="ville" required class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black transition-all bg-white">
              <option value="Kinshasa" <?= $ville_actuelle == 'Kinshasa' ? 'selected' : '' ?>>Kinshasa</option>
              <option value="Lubumbashi" <?= $ville_actuelle == 'Lubumbashi' ? 'selected' : '' ?>>Lubumbashi</option>
              <option value="Goma" <?= $ville_actuelle == 'Goma' ? 'selected' : '' ?>>Goma</option>
              <option value="Autre" <?= $ville_actuelle == 'Autre' ? 'selected' : '' ?>>Autre ville</option>
            </select>
          </div>

          <input type="text" name="adresse" placeholder="Adresse complète (Quartier, Avenue, Numéro)" value="<?= htmlspecialchars($adresse) ?>" required class="w-full border border-gray-200 p-3 text-sm outline-none focus:border-black transition-all">

          <h2 class="font-serif text-2xl italic mt-12 mb-6">Option de service</h2>
          <div class="space-y-3">
            <div class="relative">
              <input type="radio" name="payment_method" id="pay_online" value="online" class="payment-radio hidden" checked onclick="toggleGateways(true)">
              <label for="pay_online" class="flex items-center p-4 border border-gray-200 cursor-pointer rounded hover:border-stone-400 transition-all">
                <div class="flex-1"><span class="block text-[11px] uppercase font-bold tracking-widest">Payer directement (Mobile Money)</span></div>
                <i data-lucide="credit-card" class="w-4 h-4 text-gray-400"></i>
              </label>
            </div>

            <div class="relative">
              <input type="radio" name="payment_method" id="pay_delivery" value="delivery" class="payment-radio hidden" onclick="toggleGateways(false)">
              <label for="pay_delivery" class="flex items-center p-4 border border-gray-200 cursor-pointer rounded hover:border-stone-400 transition-all">
                <div class="flex-1"><span class="block text-[11px] uppercase font-bold tracking-widest">Payer à la livraison</span></div>
                <i data-lucide="truck" class="w-4 h-4 text-gray-400"></i>
              </label>
            </div>
          </div>

          <div id="gateways-container" class="mt-8 space-y-4">
            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold text-center">Choisissez votre opérateur</p>
            <div class="grid grid-cols-2 gap-3">
              <div class="relative">
                <input type="radio" name="gateway" id="mpesa" value="mpesa" class="payment-radio hidden" checked>
                <label for="mpesa" class="flex flex-col items-center justify-center border border-gray-200 p-4 cursor-pointer rounded hover:border-black transition-all h-20">
                  <img src="/assets/img/Mpesa.jpg" class="h-5 object-contain" alt="M-pesa">
                </label>
              </div>
              <div class="relative">
                <input type="radio" name="gateway" id="orange" value="orange" class="payment-radio hidden">
                <label for="orange" class="flex flex-col items-center justify-center border border-gray-200 p-4 cursor-pointer rounded hover:border-black transition-all h-20">
                  <img src="/ProjetFelykay/assets/img/orangeMoney.jpg" class="h-5 object-contain" alt="Orange">
                </label>
              </div>
            </div>
          </div>

          <input type="hidden" name="cart_data" id="cart_data_input">
          <input type="hidden" name="total_ttc" id="total_ttc_input">
        </form>
      </section>

      <section class="bg-gray-50 p-8 h-fit border border-gray-100 shadow-sm">
        <h2 class="font-serif text-2xl italic mb-8">Votre commande</h2>
        <div id="order-items" class="space-y-6 mb-8 border-b border-gray-200 pb-8 max-h-[400px] overflow-y-auto pr-2"></div>

        <div class="space-y-4 text-sm">
          <div class="flex justify-between text-gray-500"><span>Sous-total</span><span id="order-subtotal" class="font-medium text-black">0.00 $</span></div>
          <div class="flex justify-between border-t border-gray-200 pt-6 font-bold text-xl"><span>Total</span><span id="order-total">0.00 $</span></div>
        </div>

        <button id="confirm-button" class="w-full bg-black text-white py-5 mt-10 text-[11px] uppercase tracking-[0.3em] font-semibold hover:bg-gray-800 transition-all shadow-lg active:scale-95">
          Confirmer la commande
        </button>
      </section>
    </div>
  </main>

  <script>
    lucide.createIcons();
    const cart = JSON.parse(localStorage.getItem('felikay_cart')) || [];
    const modal = document.getElementById('confirm-modal');
    const modalMessage = document.getElementById('modal-message');

    function toggleGateways(show) {
      document.getElementById('gateways-container').style.display = show ? 'block' : 'none';
    }

    function closeModal() {
      modal.style.display = 'none';
      document.body.classList.remove('modal-active');
    }

    document.getElementById('confirm-button').addEventListener('click', function() {
      const form = document.getElementById('checkout-form');
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }

      const isOnline = document.getElementById('pay_online').checked;
      modalMessage.innerText = isOnline ?
        "Vous allez être redirigé vers le paiement Mobile Money. Confirmer ?" :
        "Vous allez payer en espèces lors de la livraison. Confirmer la commande ?";

      modal.style.display = 'flex';
      document.body.classList.add('modal-active');
    });

    function submitFinalForm() {
      const btn = document.getElementById('confirm-button');
      btn.disabled = true;
      btn.innerHTML = "Traitement en cours...";
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
                            <img src="${cleanImg}" class="w-full h-full object-cover">
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

    document.addEventListener('DOMContentLoaded', displayOrder);
  </script>
</body>

</html>