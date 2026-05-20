<?php
// C:\wamp64\www\ProjetFelykay\pages\admin\carte_globale.php
session_start();

require_once __DIR__ . '/../../config/db.php';
// Charge les fonctions globales avant d'inclure la sidebar
require_once __DIR__ . '/../../includes/function.php';
require_once __DIR__ . '/includes/auth_check.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'livreur'])) {
  header("Location: ../../pages/admin_login.php");
  exit();
}

// 1. Récupérer toutes les commandes en cours de livraison
$stmt = $pdo->query("SELECT id, nom_complet, commune, quartier, adresse_livraison, total_ttc FROM commandes WHERE statut = 'expedie'");
$commandes_encours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Coordonnées de base des communes pour le placement des marqueurs
$coordonnees_communes = [
  'gombe'       => [-4.3032, 15.3015],
  'limete'      => [-4.3512, 15.3340],
  'ngaliema'    => [-4.3630, 15.2492],
  'bandalungwa' => [-4.3411, 15.2861],
  'kasa-vubu'   => [-4.3435, 15.3135],
  'lingwala'    => [-4.3180, 15.3040],
  'barumbu'     => [-4.3140, 15.3210],
  'kinshasa'    => [-4.3224, 15.3070],
  'kitambo'     => [-4.3315, 15.2680],
  'lemba'       => [-4.3790, 15.3285],
  'matete'      => [-4.3880, 15.3470],
  'masina'      => [-4.3850, 15.4180]
];

if (!function_exists('lowercase_clean')) {
  function lowercase_clean($str)
  {
    return trim(str_replace([' ', '-', '_', '/'], '', mb_strtolower($str, 'UTF-8')));
  }
}

// 2. Préparer les données JSON pour JavaScript
$marqueurs = [];
foreach ($commandes_encours as $c) {
  $cle = lowercase_clean($c['commune']);
  if (array_key_exists($cle, $coordonnees_communes)) {
    $marqueurs[] = [
      'id' => $c['id'],
      'client' => htmlspecialchars($c['nom_complet']),
      'secteur' => htmlspecialchars($c['commune'] . ' - ' . $c['quartier']),
      'lat' => $coordonnees_communes[$cle][0] + (rand(-5, 5) / 1000), // Légère variation pour éviter la superposition totale
      'lng' => $coordonnees_communes[$cle][1] + (rand(-5, 5) / 1000)
    ];
  }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Felikay | Carte Globale des Livraisons</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>

<body class="bg-gray-100 flex min-h-screen text-slate-800">

  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="flex-1 ml-64 p-8 flex flex-col gap-6">
    <header class="flex justify-between items-center bg-white p-6 rounded-2xl border shadow-sm">
      <div>
        <h2 class="text-xl font-bold tracking-tight">Carte Globale des Livraisons</h2>
        <p class="text-xs text-gray-400">Visualisation de toutes les expéditions actives sur Kinshasa</p>
      </div>
      <a href="admin_dashboard.php" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase transition hover:bg-slate-800">Dashboard</a>
    </header>

    <div class="bg-white p-4 rounded-2xl border shadow-sm flex-1">
      <div id="map-globale" class="w-full h-[650px] rounded-xl border"></div>
    </div>
  </main>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    const map = L.map('map-globale', {
      attributionControl: false
    }).setView([-4.34, 15.31], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);


    const livraisons = <?= json_encode($marqueurs) ?>;

    // Ajouter un marqueur pour chaque commande en cours
    livraisons.forEach(l => {
      L.marker([l.lat, l.lng])
        .addTo(map)
        .bindPopup(`
          <div style="font-family: sans-serif; padding: 2px;">
              <b style="color:#2563eb;">Commande #ORD-${l.id}</b><br>
              <b>Client :</b> ${l.client}<br>
              <b>Zone :</b> ${l.secteur}<br><br>
              <a href="voir_itineraire.php?id=${l.id}" target="_blank" style="display:inline-block; background:#000; color:#fff; padding:5px 10px; text-decoration:none; border-radius:5px; font-size:10px; font-weight:bold; text-transform:uppercase;">Tracer l'itinéraire détaillé 🗺️</a>
          </div>
        `);
    });
  </script>
</body>

</html>