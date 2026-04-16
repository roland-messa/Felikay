<?php
$pageTitle = "À Propos | Maison Felikay";
$isSecondaryPage = true;
include '../includes/header.php';
include '../includes/navbar.php';
?>

<main class="pt-32 pb-20 bg-white">
  <div class="max-w-[1200px] mx-auto px-6">
    <div class="text-center mb-20" data-aos="fade-up">
      <span class="text-[10px] uppercase tracking-[0.5em] text-stone-400 mb-4 block">Notre Héritage</span>
      <h1 class="font-serif text-5xl md:text-7xl italic">L'Essence Felikay</h1>
    </div>

    <div class="grid md:grid-cols-2 gap-20 items-center mb-32">
      <div class="overflow-hidden" data-aos="reveal-left">
        <img src="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?q=80&w=1000" alt="Atelier" class="w-full h-[600px] object-cover hover:scale-105 transition-transform duration-1000">
      </div>
      <div data-aos="fade-left">
        <h2 class="font-serif text-3xl mb-8 italic">Un savoir-faire d'exception</h2>
        <p class="text-sm leading-relaxed text-stone-600 mb-6 uppercase tracking-widest text-[11px]">
          Fondée sur l'amour des matières nobles, la Maison Felikay s'efforce de redéfinir l'élégance contemporaine. Chaque pièce est pensée comme une œuvre intemporelle.
        </p>
        <p class="text-sm leading-relaxed text-stone-500 mb-10">
          Notre processus créatif puise son inspiration dans le minimalisme et la pureté des lignes. Nous croyons en une mode durable, où la qualité l'emporte sur la quantité.
        </p>
        <div class="border-l-2 border-stone-100 pl-8 py-2">
          <span class="block font-serif italic text-xl">"L'élégance n'est pas de se faire remarquer, mais de se faire retenir."</span>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>