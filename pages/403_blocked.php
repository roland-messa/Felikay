<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accès Restreint - Felikay</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:italic,wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="bg-[#FDFDFD] min-h-screen flex items-center justify-center p-6 font-sans">
  <div class="max-w-2xl text-center">
    <div class="mb-12">
      <div class="w-32 h-32 bg-stone-50 rounded-full flex items-center justify-center border border-stone-100 mx-auto mb-8">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-stone-300">
          <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
          <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
      </div>
    </div>

    <h2 class="font-serif text-3xl md:text-4xl italic mb-4 text-slate-900">
      Accès temporairement suspendu.
    </h2>
    <p class="text-stone-400 text-sm mb-10 uppercase tracking-[0.2em] leading-relaxed">
      Suite à plusieurs tentatives infructueuses, l'accès à votre compte est bloqué par mesure de sécurité. <br>
      Veuillez réessayer dans quelques minutes ou contacter notre service AfreeData pour vous aider !!.
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="/ProjetFelykay/index.php"
        class="inline-block bg-black text-white px-10 py-5 text-[10px] uppercase tracking-[0.4em] font-bold hover:bg-zinc-800 transition-all shadow-xl">
        Retour à l'accueil
      </a>
      <a href="mailto:contact@felikay.com"
        class="inline-block border border-stone-200 text-stone-500 px-10 py-5 text-[10px] uppercase tracking-[0.4em] font-bold hover:border-black hover:text-black transition-all">
        Contacter le support contact@felikay.com
      </a>
    </div>
  </div>
</body>

</html>