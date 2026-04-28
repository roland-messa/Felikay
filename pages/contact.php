<?php
$pageTitle = "Contact | Maison Felikay";
$isSecondaryPage = true;
include '../includes/header.php';
include '../includes/function.php';
include '../includes/navbar.php';
?>

<main class="pt-32 pb-20 bg-[#F9F8F6]">
  <div class="max-w-[1200px] mx-auto px-6">


    <?php
    display_alert();
    ?>

    <div class="grid md:grid-cols-3 gap-16">

      <div class="space-y-12" data-aos="fade-right">
        <div>
          <h1 class="font-serif text-4xl mb-6 italic">Nous contacter</h1>
          <p class="text-[11px] uppercase tracking-widest text-stone-400 leading-relaxed">
            Notre service client vous accompagne du lundi au samedi, de 9h à 19h.
          </p>
        </div>

        <div class="space-y-6">
          <div>
            <span class="text-[9px] uppercase tracking-[0.3em] font-bold text-stone-400 block mb-2">ADRESSE COMPLET</span>
            <p class="text-sm uppercase tracking-wider">Kinshasa, Gombe<br>République Démocratique du Congo</p>
          </div>
          <div>
            <span class="text-[9px] uppercase tracking-[0.3em] font-bold text-stone-400 block mb-2">Téléphone</span>
            <p class="text-sm tracking-widest">+243 820 000 000</p>
          </div>
          <div>
            <span class="text-[9px] uppercase tracking-[0.3em] font-bold text-stone-400 block mb-2">Email</span>
            <p class="text-sm tracking-widest">contact@felikay.com</p>
          </div>
        </div>
      </div>

      <div class="md:col-span-2 bg-white p-10 md:p-16 shadow-sm border border-stone-100" data-aos="fade-up">
        <form action="../assets/actions/contact_process.php" method="POST" class="space-y-8">

          <div class="grid md:grid-cols-3 gap-8">
            <div class="space-y-2">
              <label class="text-[9px] uppercase tracking-widest font-bold">Nom Complet</label>
              <input type="text" name="nom" required class="w-full border-b border-stone-200 py-3 text-sm outline-none focus:border-black transition bg-transparent">
            </div>

            <div class="space-y-2">
              <label class="text-[9px] uppercase tracking-widest font-bold">Téléphone (Obligatoire)</label>
              <div class="flex gap-0 border-b border-stone-200 focus-within:border-black transition">
                <span class="py-3 text-sm text-stone-400 shrink-0 select-none">
                  +243
                </span>
                <input
                  type="tel"
                  name="telephone"
                  placeholder="820000000"
                  pattern="[0-9]{9}"
                  title="Veuillez entrer les 9 chiffres après l'indicatif"
                  required
                  class="w-full py-3 px-2 text-sm outline-none transition bg-transparent">
              </div>
            </div>

            <div class="space-y-2">
              <label class="text-[9px] uppercase tracking-widest font-bold">Votre Email (Optionnel)</label>
              <input type="email" name="email" class="w-full border-b border-stone-200 py-3 text-sm outline-none focus:border-black transition bg-transparent">
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-[9px] uppercase tracking-widest font-bold">Sujet</label>
            <select name="sujet" class="w-full border-b border-stone-200 py-3 text-sm outline-none focus:border-black transition bg-transparent uppercase tracking-widest text-[10px]">
              <option value="Service Client">Service Client</option>
              <option value="Autre">Autre demande</option>
            </select>
          </div>

          <div class="space-y-2">
            <label class="text-[9px] uppercase tracking-widest font-bold">Message</label>
            <textarea name="message" rows="4" required class="w-full border-b border-stone-200 py-3 text-sm outline-none focus:border-black transition resize-none bg-transparent"></textarea>
          </div>

          <button type="submit" class="bg-black text-white px-12 py-5 text-[10px] uppercase tracking-[0.4em] hover:bg-stone-800 transition shadow-lg active:scale-95">
            Envoyer le message
          </button>
        </form>
      </div>

    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>