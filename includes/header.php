<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!function_exists('isLoggedIn')) {
  function isLoggedIn()
  {
    return isset($_SESSION['user_id']);
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle ?? 'Felikay | Maison d\'habillement'; ?></title>
  <link rel="stylesheet" href="/ProjetFelykay/assets/css/style.css">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />


  <style>
    body {
      font-family: 'Montserrat', sans-serif;
      scroll-behavior: smooth;
    }

    .font-serif {
      font-family: 'Playfair Display', serif;
    }

    .progress-bar {
      animation: progress 2s ease-in-out forwards;
    }

    @keyframes progress {
      0% {
        width: 0%;
      }

      100% {
        width: 100%;
      }
    }

    .nav-transition {
      transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .fade-out {
      opacity: 0;
      visibility: hidden;
      transition: all 0.8s ease-in-out;
    }

    .swiper-pagination-fraction {
      font-family: 'Playfair Display', serif;
      font-size: 1.2rem;
      bottom: 0 !important;
    }

    .product-card img {
      transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .product-card:hover img {
      transform: scale(1.05);
    }
  </style>
</head>

<body class="bg-[#f2f2f2] text-[#1A1A1A]" id="body-content">

  <div id="loader" class="fixed inset-0 z-[100] bg-white flex flex-col items-center justify-center">
    <div class="relative mb-6">
      <div class="w-24 h-24 md:w-32 md:h-32 rounded-full overflow-hidden border border-gray-100 shadow-sm">
        <img src="/ProjetFelykay/assets/img/felikay.jpg" alt="Logo Felikay" class="w-full h-full object-cover">
      </div>
      <div class="absolute inset-0 border-t-2 border-black rounded-full animate-spin"></div>
    </div>

    <h1 class="font-serif text-4xl md:text-6xl tracking-[0.3em] uppercase mb-6">Felikay</h1>

    <div class="w-48 h-[1px] bg-gray-100 relative">
      <div class="progress-bar absolute top-0 left-0 h-full bg-black"></div>
    </div>

    <p class="mt-4 text-[9px] uppercase tracking-[0.5em] text-gray-400 font-light text-center">Maison d'habillement</p>
  </div>