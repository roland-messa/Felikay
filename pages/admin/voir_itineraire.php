<?php
session_start();
require_once '../../config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

// Sécurité d'accès (Admin ou Livreur uniquement)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'livreur'])) {
  header("Location: ../../pages/admin_login.php");
  exit();
}

if (!function_exists('lowercase_clean')) {
  function lowercase_clean($str)
  {
    $str = mb_strtolower($str, 'UTF-8');
    $str = str_replace([' ', '-', '_', '/'], '', $str);
    return trim($str);
  }
}

// CONFIGURATION DU POINT DE DEPART : La Cuisine Parfaite
$latitude_depart  = -4.304943879425345;
$longitude_depart = 15.28531152883573;

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
  'nadjili'      => [-4.4285, 15.3810],
  'masina'       => [-4.3850, 15.4180],
  'lemba'        => [-4.3790, 15.3285],
  'matete'       => [-4.3880, 15.3470],
  'ngaba'        => [-4.3895, 15.3190],
  'makala'       => [-4.3750, 15.3020],
  'bumbu'        => [-4.3640, 15.2920],
  'selembao'     => [-4.3820, 15.2740],
  'kala'         => [-4.3910, 15.2510],
  'kalamu'       => [-4.3520, 15.3140],
  'mont-ngafula' => [-4.4450, 15.2580],
  'ndjili'       => [-4.4285, 15.3810],
  'kimbanseke'   => [-4.4320, 15.4450],
  'maluku'       => [-4.2400, 15.8000],
  'nsele'        => [-4.2500, 15.5400]
];

$commandes = [];
$est_groupe = false;

try {
  // 1. CAS ITINÉRAIRE GROUPÉ (via ?ids=117,116,115)
  if (!empty($_GET['ids'])) {
    $est_groupe = true;
    $idsArray = explode(',', $_GET['ids']);
    $inQuery = implode(',', array_fill(0, count($idsArray), '?'));

    $stmt = $pdo->prepare("
        SELECT c.id, c.nom_complet, c.telephone, c.commune, c.quartier, c.adresse_livraison, c.statut, c.type_livraison
        FROM commandes c
        WHERE c.id IN ($inQuery)
    ");
    $stmt->execute($idsArray);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
  // 2. CAS ITINÉRAIRE UNIQUE (via ?id=97)
  elseif (!empty($_GET['id'])) {
    $stmt = $pdo->prepare("
        SELECT c.id, c.nom_complet, c.telephone, c.commune, c.quartier, c.adresse_livraison, c.statut, c.type_livraison
        FROM commandes c
        WHERE c.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) {
      $commandes[] = $res;
    }
  }

  // Si aucune commande n'a été interceptée
  if (empty($commandes)) {
    die("Aucune commande spécifiée ou introuvable.");
  }

  // 3. Traitement des adresses et détection des retraits en boutique
  foreach ($commandes as &$commande) {
    $commune_cle = lowercase_clean($commande['commune'] ?? '');
    $type_livr = lowercase_clean($commande['type_livraison'] ?? '');

    // Détection si c'est un retrait sur place / en boutique (selon ton champ type_livraison ou le nom de la commune)
    if ($type_livr === 'boutique' || $type_livr === 'surplace' || $commune_cle === 'boutique' || $commune_cle === 'atelier') {
      $commande['est_retrait_boutique'] = true;
      $commande['lat'] = $latitude_depart; // Coordonnées de l'atelier
      $commande['lng'] = $longitude_depart;
    } else {
      $commande['est_retrait_boutique'] = false;
      if (array_key_exists($commune_cle, $coordonnees_communes)) {
        $commande['lat'] = $coordonnees_communes[$commune_cle][0];
        $commande['lng'] = $coordonnees_communes[$commune_cle][1];
      } else {
        $commande['lat'] = -4.3224; // Par défaut Kinshasa Centre
        $commande['lng'] = 15.3070;
      }
    }
  }
  unset($commande);
} catch (PDOException $e) {
  die("Erreur database : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $est_groupe ? "Itinéraire Groupé" : "Itinéraire de Livraison #" . $commandes[0]['id'] ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
  <style>
    .leaflet-routing-container,
    .hidden-itinerary {
      display: none !important;
    }
  </style>
</head>

<body class="bg-gray-50 min-h-screen text-black p-4 md:p-8">

  <div class="max-w-5xl mx-auto space-y-6">

    <div class="flex flex-col md:flex-row md:justify-between md:items-center bg-white p-6 rounded-2xl border border-gray-100 shadow-sm gap-4">
      <div>
        <span class="text-[9px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Calculateur d'itinéraire</span>
        <h1 class="text-2xl font-black uppercase tracking-tight mt-2">
          <?= $est_groupe ? "Tournée Groupée (" . count($commandes) . " Commandes)" : "Commande #" . $commandes[0]['id'] ?>
        </h1>
        <p class="text-xs text-gray-400 mt-1">
          <?= $est_groupe ? "Traitement de plusieurs destinations simultanées" : "Statut actuel de la commande : <span class='font-bold text-gray-700 uppercase'>" . htmlspecialchars($commandes[0]['statut']) . "</span>" ?>
        </p>
      </div>
      <div class="flex gap-3">
        <a href="carte_globale.php" class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold uppercase tracking-wider px-5 py-3 rounded-xl transition">
          Retour
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-6 h-fit max-h-[550px] overflow-y-auto">
        <h2 class="text-xs font-black uppercase tracking-wider text-gray-400 border-b pb-2">Détails des points de livraison</h2>

        <?php foreach ($commandes as $index => $cmd): ?>
          <div class="p-3 <?= $cmd['est_retrait_boutique'] ? 'border-l-4 border-amber-500 bg-amber-50/50' : ($est_groupe ? 'border-l-4 border-blue-500 bg-slate-50/50' : '') ?> rounded-r-xl space-y-2">

            <div class="flex justify-between items-center">
              <span class="text-[10px] font-bold text-blue-600 uppercase">📍 Destination #<?= $cmd['id'] ?></span>

              <?php if ($cmd['est_retrait_boutique']): ?>
                <span class="px-2 py-0.5 text-[8px] font-black tracking-wide uppercase bg-amber-600 text-white rounded">🏠 RETRAIT ATELIER</span>
              <?php endif; ?>
            </div>

            <?php if ($cmd['est_retrait_boutique']): ?>
              <div class="p-2 border border-amber-200 bg-white rounded-lg text-[10px] text-amber-700 leading-tight">
                ⚠️ <strong>Attention admin :</strong>
                <?php if (in_array($cmd['statut'], ['paye', 'paiement_confirmer'])): ?>
                  Cette commande est <strong>déjà payée en ligne</strong>. Le client viendra la récupérer lui-même à l'atelier. Pas de livraison requise.
                <?php else: ?>
                  Le client a choisi de <strong>payer et retirer sur place</strong> directement en boutique.
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <div>
              <label class="block text-[9px] font-bold uppercase text-gray-400">Destinataire</label>
              <p class="text-sm font-bold uppercase text-black"><?= htmlspecialchars($cmd['nom_complet']) ?></p>
              <p class="text-xs text-gray-600">📞 <?= htmlspecialchars($cmd['telephone']) ?></p>
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase text-gray-400">Secteur ciblé</label>
              <p class="text-xs font-bold text-slate-700 uppercase"><?= htmlspecialchars($cmd['commune']) ?> <span class="text-gray-300 font-normal">|</span> <?= htmlspecialchars($cmd['quartier']) ?></p>
            </div>
            <div>
              <label class="block text-[9px] font-bold uppercase text-gray-400">Adresse Complète</label>
              <p class="text-xs text-gray-600 italic bg-white p-2 rounded-lg border mt-1"><?= htmlspecialchars($cmd['adresse_livraison']) ?></p>
            </div>
          </div>
          <?php if ($index < count($commandes) - 1): ?>
            <hr class="border-gray-100"><?php endif; ?>
        <?php endforeach; ?>

        <div class="pt-4 border-t border-dashed">
          <p class="text-[11px] text-gray-400 leading-relaxed">
            💡 <strong>Note logistique :</strong> Les commandes en "Retrait Atelier" restent localisées au point de départ et ne génèrent pas de déplacement sur la carte.
          </p>
        </div>
      </div>

      <div class="lg:col-span-2 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm relative">
        <div id="map" class="w-full h-[500px] rounded-xl border border-gray-100 z-10"></div>
      </div>

    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

  <script>
    const map = L.map('map').setView([-4.3224, 15.3070], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const startLat = <?= $latitude_depart ?>;
    const startLng = <?= $longitude_depart ?>;
    const listeCommandes = <?= json_encode($commandes) ?>;

    let routeWaypoints = [];
    routeWaypoints.push(L.latLng(startLat, startLng)); // Point A : Cuisine Parfaite

    // Ajouter uniquement les commandes qui NE sont PAS des retraits en boutique pour tracer la route
    listeCommandes.forEach(cmd => {
      if (!cmd.est_retrait_boutique) {
        routeWaypoints.push(L.latLng(cmd.lat, cmd.lng));
      }
    });

    // Si on a des vraies livraisons à faire, on trace l'itinéraire carrossable
    if (routeWaypoints.length > 1) {
      L.Routing.control({
        waypoints: routeWaypoints,
        lineOptions: {
          styles: [{
              color: '#2563eb',
              weight: 6,
              opacity: 0.7
            },
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
            return L.marker(waypoint.latLng).bindPopup("<b>📍 Départ : La Cuisine Parfaite</b>").openPopup();
          } else {
            // Filtrer la liste pour trouver la bonne commande correspondante excluant les retraits sur carte
            const livraisonsReelles = listeCommandes.filter(c => !c.est_retrait_boutique);
            const cmdCorrespondante = livraisonsReelles[i - 1];
            return L.marker(waypoint.latLng).bindPopup(`
              <div style="font-family: sans-serif; min-width:140px;">
                <b style="color:#2563eb;">📦 Commande #ORD-${cmdCorrespondante.id}</b><br>
                <b>Client :</b> ${cmdCorrespondante.nom_complet}<br>
                <b>Zone :</b> ${cmdCorrespondante.commune}
              </div>
            `);
          }
        },
        show: false,
        itineraryClassName: 'hidden-itinerary'
      }).addTo(map);
    } else {
      // S'il n'y a que des retraits en boutique sélectionnés, on place juste un marqueur spécial sur l'atelier
      L.marker([startLat, startLng]).addTo(map).bindPopup(`
        <div style="font-family: sans-serif; text-align:center; padding:5px;">
          <b style="color:#d97706;">🏠 Point de Retrait Client</b><br>
          Toutes les commandes sélectionnées<br>sont à récupérer directement ici !
        </div>
      `).openPopup();
    }
  </script>
</body>

</html>