<?php
// C:\wamp64\www\ProjetFelykay\pages\admin\tournee_livraison.php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/function.php';
require_once __DIR__ . '/includes/auth_check.php';

if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'livreur'])) {
  header("Location: ../../pages/admin_login.php");
  exit();
}

// 1. Point de départ fixe : La Cuisine Parfaite
$depart = [
  'nom' => 'La Cuisine Parfaite (Départ)',
  'lat' => -4.304943879425345,
  'lng' => 15.28531152883573
];

// Coordonnées de référence des communes de Kinshasa
$coordonnees_communes = [
  'gombe'        => [-4.3032, 15.3015],
  'limete'       => [-4.3512, 15.3340],
  'ngaliema'     => [-4.3630, 15.2492],
  'bandalungwa'  => [-4.3411, 15.2861],
  'kasa-vubu'    => [-4.3435, 15.3135],
  'lingwala'     => [-4.3180, 15.3040],
  'barumbu'      => [-4.3140, 15.3210],
  'kinshasa'     => [-4.3224, 15.3070],
  'kitambo'      => [-4.3315, 15.2680],
  'lemba'        => [-4.3790, 15.3285],
  'matete'       => [-4.3880, 15.3470],
  'masina'       => [-4.3850, 15.4180]
];

if (!function_exists('lowercase_clean')) {
  function lowercase_clean($str)
  {
    return trim(str_replace([' ', '-', '_', '/'], '', mb_strtolower($str, 'UTF-8')));
  }
}

// 2. Récupérer les commandes à livrer
$stmt = $pdo->query("SELECT id, nom_complet, commune, quartier, adresse_livraison FROM commandes WHERE statut = 'expedie'");
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Associer chaque commande à ses coordonnées géographiques
$points_livraison = [];
foreach ($commandes as $c) {
  $cle = lowercase_clean($c['commune']);
  if (array_key_exists($cle, $coordonnees_communes)) {
    $points_livraison[] = [
      'id' => $c['id'],
      'client' => $c['nom_complet'],
      'secteur' => $c['commune'] . ' - ' . $c['quartier'],
      'lat' => $coordonnees_communes[$cle][0] + (rand(-3, 3) / 1000), // Légère variation pour éviter les superpositions exactes
      'lng' => $coordonnees_communes[$cle][1] + (rand(-3, 3) / 1000)
    ];
  }
}

// 3. Algorithme du Plus Proche Voisin (Tri de la trajectoire optimale)
$tournee_ordonnee = [];
$position_actuelle = ['lat' => $depart['lat'], 'lng' => $depart['lng']];
$points_restants = $points_livraison;

// Fonction simple de distance de Manhattan (suffisante pour trier à l'échelle d'une ville)
function calculer_distance_points($p1, $p2)
{
  return abs($p1['lat'] - $p2['lat']) + abs($p1['lng'] - $p2['lng']);
}

while (count($points_restants) > 0) {
  $index_proche = null;
  $distance_min = INF;

  foreach ($points_restants as $index => $point) {
    $dist = calculer_distance_points($position_actuelle, $point);
    if ($dist < $distance_min) {
      $distance_min = $dist;
      $index_proche = $index;
    }
  }

  // Ajouter le point le plus proche à la feuille de route
  $tournee_ordonnee[] = $points_restants[$index_proche];
  // Mettre à jour la position actuelle pour la prochaine recherche
  $position_actuelle = $points_restants[$index_proche];
  // Retirer le point traité de la liste des points restants
  unset($points_restants[$index_proche]);
  $points_restants = array_values($points_restants); // Réindexer le tableau
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Felikay | Optimisation de Tournée</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
  <style>
    .hidden-itinerary {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gray-100 flex min-h-screen text-slate-800">

  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="flex-1 ml-64 p-8 flex flex-col gap-6">
    <header class="flex justify-between items-center bg-white p-6 rounded-2xl border shadow-sm">
      <div>
        <h2 class="text-xl font-bold tracking-tight">Feuille de Route Intelligente 🧠</h2>
        <p class="text-xs text-gray-400">Algorithme de calcul de trajectoire optimisée pour le livreur</p>
      </div>
      <a href="admin_dashboard.php" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold uppercase transition hover:bg-slate-800">Dashboard</a>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1">

      <div class="bg-white p-6 rounded-2xl border shadow-sm space-y-4 h-fit">
        <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 border-b pb-2">Ordre de Passage Conseillé</h3>

        <div class="space-y-3 relative before:absolute before:top-2 before:bottom-2 before:left-3.5 before:w-0.5 before:bg-gray-200">
          <div class="flex items-start gap-3 relative z-10">
            <span class="w-7 h-7 rounded-full bg-black text-white text-xs font-bold flex items-center justify-center shadow-sm">🏁</span>
            <div>
              <h4 class="text-xs font-bold uppercase">La Cuisine Parfaite</h4>
              <p class="text-[10px] text-gray-400">Point de chargement des colis</p>
            </div>
          </div>

          <?php if (empty($tournee_ordonnee)): ?>
            <p class="text-xs text-gray-400 italic pt-2">Aucun colis en cours d'expédition aujourd'hui.</p>
          <?php else: ?>
            <?php foreach ($tournee_ordonnee as $index => $etape): ?>
              <div class="flex items-start gap-3 relative z-10 pt-2">
                <span class="w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-black flex items-center justify-center shadow-sm">
                  <?= $index + 1 ?>
                </span>
                <div>
                  <h4 class="text-xs font-bold uppercase text-slate-800">#ORD-<?= $etape['id'] ?> — <?= htmlspecialchars($etape['client']) ?></h4>
                  <p class="text-[11px] text-blue-600 font-bold uppercase"><?= htmlspecialchars($etape['secteur']) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="lg:col-span-2 bg-white p-4 rounded-2xl border shadow-sm min-h-[600px] flex">
        <div id="map-tournee" class="w-full h-full rounded-xl border"></div>
      </div>

    </div>
  </main>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
  <script>
    // Initialisation centrée sur Kinshasa
    const map = L.map('map-tournee', {
      attributionControl: false
    }).setView([-4.34, 15.31], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    // Injection des données PHP ordonnées
    const baseDepart = <?= json_encode($depart) ?>;
    const etapesOrdonnees = <?= json_encode($tournee_ordonnee) ?>;

    // Construction du tableau de points pour la feuille de route (Routing Machine)
    const waypoints = [
      L.latLng(baseDepart.lat, baseDepart.lng) // Le point de départ (A)
    ];

    // On ajoute toutes les destinations triées par ordre de proximité
    etapesOrdonnees.forEach(e => {
      waypoints.push(L.latLng(e.lat, e.lng));
    });

    if (etapesOrdonnees.length > 0) {
      // Calcul et affichage de la trajectoire complète unifiée
      L.Routing.control({
        waypoints: waypoints,
        lineOptions: {
          styles: [{
              color: '#10b981',
              weight: 6,
              opacity: 0.8
            }, // Tracé Vert Émeraude pour la tournée complète
            {
              color: '#000000',
              weight: 2,
              opacity: 1
            }
          ]
        },
        router: L.Routing.osrmv1({
          serviceUrl: 'https://router.project-osrm.org/route/v1',
          language: 'fr'
        }),
        createMarker: function(i, waypoint, n) {
          if (i === 0) {
            return L.marker(waypoint.latLng).bindPopup("🏁 <b>Point de Départ :</b><br>La Cuisine Parfaite").openPopup();
          } else {
            const dataEtape = etapesOrdonnees[i - 1];
            return L.marker(waypoint.latLng).bindPopup(`
              <div style="font-family: sans-serif; padding:2px;">
                <b style="color:#10b981;">Étape ${i} : Commande #ORD-${dataEtape.id}</b><br>
                <b>Client :</b> ${dataEtape.client}<br>
                <b>Zone :</b> ${dataEtape.secteur}
              </div>
            `);
          }
        },
        show: false,
        itineraryClassName: 'hidden-itinerary'
      }).addTo(map);
    }
  </script>
</body>

</html>