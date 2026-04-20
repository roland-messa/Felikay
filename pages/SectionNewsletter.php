<section class="py-24 bg-[#F3F3F3] relative overflow-hidden">
  <div class="absolute inset-0 flex flex-col justify-center opacity-[0.03] select-none pointer-events-none">
    <span class="font-serif text-[120px] leading-none whitespace-nowrap uppercase">Felikay Club Felikay Club Felikay Club</span>
    <span class="font-serif text-[120px] leading-none whitespace-nowrap uppercase">Felikay Club Felikay Club Felikay Club</span>
    <span class="font-serif text-[120px] leading-none whitespace-nowrap uppercase">Felikay Club Felikay Club Felikay Club</span>
  </div>

  <div class="max-w-[800px] mx-auto px-6 relative z-10 text-center">
    <h2 class="font-serif text-3xl md:text-4xl tracking-widest uppercase mb-4">
      Rejoignez le Club Felikay
    </h2>
    <p class="text-[10px] uppercase tracking-[0.2em] text-stone-500 mb-12">
      Recevez nos offres exclusives directement sur votre téléphone
    </p>

    <form action="../assets/actions/process-newsletter.php" method="POST" class="max-w-[500px] mx-auto">
      <div class="flex flex-col gap-4">

        <input
          type="text"
          name="client_name"
          placeholder="Votre nom complet"
          required
          class="w-full bg-white border border-gray-200 px-6 py-4 text-[11px] uppercase tracking-widest focus:outline-none focus:border-black transition-colors">

        <div class="flex gap-2">
          <span class="bg-white border border-gray-200 px-4 py-4 text-[11px] text-gray-400 flex items-center shrink-0">
            +243
          </span>
          <input
            type="tel"
            name="client_phone"
            placeholder="Numéro de téléphone"
            pattern="[0-9]{9}"
            title="Veuillez entrer les 9 chiffres après l'indicatif (ex: 812345678)"
            required
            class="w-full bg-white border border-gray-200 px-6 py-4 text-[11px] uppercase tracking-widest focus:outline-none focus:border-black transition-colors">
        </div>

        <button
          type="submit"
          class="w-full bg-[#1A1A1A] text-white py-5 text-[10px] uppercase tracking-[0.3em] font-bold hover:bg-black transition-all mt-2">
          S'abonner
        </button>
      </div>

      <p class="mt-6 text-[9px] text-stone-400 uppercase tracking-widest">
        Pas de spam. Uniquement l'élégance à votre portée.
      </p>
    </form>
  </div>
</section>