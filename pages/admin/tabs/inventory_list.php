<?php
// C:\wamp64\www\ProjetFelykay\pages\admin\tabs\inventory_list.php

require_once __DIR__ . '/../../../config/db.php';

$root = "/ProjetFelykay/";

// ===============================
// 1. FILTRES ET TRI
// ===============================

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_date = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'desc';

// ===============================
// 2. CONDITIONS SQL
// ===============================

$conditions = [];
$params = [];

if ($search !== '') {

  $conditions[] = "(p.nom LIKE :search OR p.id = :id_exact)";

  $params['search'] = '%' . $search . '%';
  $params['id_exact'] = (int)$search;
}

if ($filter_date !== '') {

  $conditions[] = "DATE(p.created_at) = :filter_date";

  $params['filter_date'] = $filter_date;
}

// WHERE FINAL
$where_clause = '';

if (!empty($conditions)) {

  $where_clause = "WHERE " . implode(" AND ", $conditions);
}

// ===============================
// 3. TRI
// ===============================

$order_by = ($sort === 'asc') ? 'ASC' : 'DESC';

// ===============================
// 4. TOTAL ARTICLES
// ===============================

$count_sql = "SELECT COUNT(*) FROM produits p $where_clause";

$count_stmt = $pdo->prepare($count_sql);

$count_stmt->execute($params);

$total_articles = $count_stmt->fetchColumn();
?>

<!-- ========================= -->
<!-- MODAL STOCK -->
<!-- ========================= -->

<div id="stockModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[150] hidden flex items-center justify-center p-4 transition-all duration-300">

  <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-100">

    <div class="p-8">

      <div class="w-12 h-12 bg-slate-100 text-black rounded-xl flex items-center justify-center mb-5 border border-slate-200">
        <i data-lucide="package-plus" class="w-6 h-6"></i>
      </div>

      <h3 class="font-serif italic text-2xl text-slate-950 mb-1" id="modalTitle">
        Article
      </h3>

      <p class="text-sm text-slate-500 mb-8 pb-4 border-b border-slate-100" id="modalDesc">
        Stock actuel : 0 unités
      </p>

      <div>
        <label class="text-[10px] uppercase font-bold text-slate-400 tracking-[0.2em] mb-3 block">
          Nouvelle Quantité Totale
        </label>

        <input
          type="number"
          id="newStockInput"
          placeholder="0"
          class="w-full px-5 py-4 bg-white border border-slate-200 rounded-lg focus:ring-1 focus:ring-black focus:border-black outline-none transition-all font-mono text-lg text-slate-900">
      </div>

    </div>

    <div class="p-4 bg-slate-50 border-t border-slate-100 flex gap-3">

      <button
        onclick="closeStockModal()"
        class="flex-1 px-4 py-3 text-[11px] uppercase tracking-widest font-bold text-slate-500 hover:bg-slate-100 rounded-lg transition">

        Annuler

      </button>

      <button
        onclick="confirmStockUpdate()"
        class="flex-1 px-4 py-3 text-[11px] uppercase tracking-widest font-bold text-white bg-black hover:bg-zinc-800 rounded-lg shadow-md transition">

        Confirmer

      </button>

    </div>

  </div>

</div>

<!-- ========================= -->
<!-- FILTRES -->
<!-- ========================= -->

<div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm mb-6">

  <form
    method="GET"
    action=""
    id="inventoryFilterForm"
    class="flex flex-col lg:flex-row items-end gap-4">

    <input type="hidden" name="tab" value="inventory">

    <!-- RECHERCHE -->
    <div class="flex-1 w-full">

      <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2 block">
        Rechercher un article
      </label>

      <div class="relative">

        <input
          type="text"
          name="search"
          value="<?php echo htmlspecialchars($search); ?>"
          placeholder="Par nom d'article ou ID (ex: 97)..."
          class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-1 focus:ring-black focus:border-black outline-none transition-all text-xs text-slate-800">

        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>

      </div>

    </div>

    <!-- DATE -->
    <div class="w-full lg:w-56">

      <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2 block">
        Filtrer par date
      </label>

      <div class="relative">

        <input
          type="date"
          name="filter_date"
          value="<?php echo htmlspecialchars($filter_date); ?>"
          class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-1 focus:ring-black focus:border-black outline-none transition-all text-xs text-slate-800">

        <i data-lucide="calendar" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>

      </div>

    </div>

    <!-- TRI -->
    <div class="w-full lg:w-56">

      <label class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-2 block">
        Trier
      </label>

      <select
        name="sort"
        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-1 focus:ring-black focus:border-black outline-none transition-all text-xs text-slate-800">

        <option value="desc" <?php echo $sort === 'desc' ? 'selected' : ''; ?>>
          Plus récent ➜ Plus ancien
        </option>

        <option value="asc" <?php echo $sort === 'asc' ? 'selected' : ''; ?>>
          Plus ancien ➜ Plus récent
        </option>

      </select>

    </div>

    <!-- BOUTONS -->
    <div class="flex gap-2 w-full lg:w-auto">

      <?php if ($search !== '' || $filter_date !== '' || $sort !== 'desc'): ?>

        <a
          href="?tab=inventory"
          class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold uppercase tracking-wider transition text-center flex-1 lg:flex-none">

          Effacer

        </a>

      <?php endif; ?>

      <button
        type="submit"
        class="px-6 py-3 bg-black hover:bg-slate-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider transition shadow-sm flex-1 lg:flex-none">

        Filtrer

      </button>

    </div>

  </form>

</div>

<!-- ========================= -->
<!-- TABLE -->
<!-- ========================= -->

<div id="inventory-content">

  <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

    <div class="p-6 border-b border-slate-100 flex justify-between items-center">

      <h4 class="font-serif italic text-xl text-slate-800">
        Articles en inventaire
      </h4>

      <span class="text-[10px] bg-slate-100 px-3 py-1 rounded-full uppercase font-bold tracking-widest text-slate-500">
        <?php echo $total_articles; ?> trouvé(s)
      </span>

    </div>

    <div class="overflow-x-auto">

      <table class="w-full text-left border-collapse">

        <thead>

          <tr class="bg-slate-50">

            <th class="p-4 text-[10px] uppercase font-bold text-slate-400 border-b">
              Article
            </th>

            <th class="p-4 text-[10px] uppercase font-bold text-slate-400 border-b">
              Catégorie
            </th>

            <th class="p-4 text-[10px] uppercase font-bold text-slate-400 border-b">
              Date d'ajout
            </th>

            <th class="p-4 text-[10px] uppercase font-bold text-slate-400 border-b">
              Prix
            </th>

            <th class="p-4 text-[10px] uppercase font-bold text-slate-400 border-b">
              Couleurs
            </th>

            <th class="p-4 text-[10px] uppercase font-bold text-slate-400 border-b">
              Stock
            </th>

            <th class="p-4 text-[10px] uppercase font-bold text-slate-400 border-b text-right">
              Actions
            </th>

          </tr>

        </thead>

        <tbody class="divide-y divide-slate-100">

          <?php

          $sql = "
                  SELECT 
                      p.*,
                      c.nom AS cat_nom,

                      DATE_FORMAT(
                          p.created_at,
                          '%d/%m/%Y %H:%i'
                      ) AS date_complete,

                      (
                          SELECT GROUP_CONCAT(cl.code_hex)
                          FROM produit_couleurs pc
                          JOIN couleurs cl
                          ON pc.couleur_id = cl.id
                          WHERE pc.produit_id = p.id
                      ) AS les_couleurs

                  FROM produits p

                  LEFT JOIN categories c
                  ON p.categorie_id = c.id

                  $where_clause

                  ORDER BY p.created_at $order_by
                  ";

          $stmt = $pdo->prepare($sql);

          $stmt->execute($params);

          $has_rows = false;

          while ($row = $stmt->fetch()):

            $has_rows = true;

            $raw_img = $row['image_principale'] ?? '';

            $clean_filename = str_replace(
              ['../../', '../', 'assets/img/produits/'],
              '',
              $raw_img
            );

            $full_img_url =
              $root .
              "assets/img/produits/" .
              ltrim($clean_filename, '/');

            $stockVal = (int)$row['stock_total'];

            $stockClass = "bg-slate-100 text-slate-900";

            if ($stockVal <= 0) {

              $stockClass = "bg-rose-50 text-rose-600";
            } elseif ($stockVal <= 5) {

              $stockClass = "bg-amber-50 text-amber-600";
            }

          ?>

            <tr class="hover:bg-slate-50/50 transition-colors">

              <td class="p-4">

                <div class="flex items-center gap-3">

                  <div class="w-12 h-12 rounded-xl overflow-hidden border border-slate-100 bg-slate-50">

                    <img
                      src="<?php echo $full_img_url; ?>"
                      onerror="this.onerror=null; this.src='<?php echo $root; ?>assets/img/felikay.jpg';"
                      class="w-full h-full object-cover">

                  </div>

                  <div>

                    <p class="text-sm font-bold text-slate-800 leading-tight">
                      <?php echo htmlspecialchars($row['nom']); ?>
                    </p>

                    <p class="text-[9px] text-slate-400 uppercase tracking-tighter">
                      ID: #<?php echo $row['id']; ?>
                    </p>

                  </div>

                </div>

              </td>

              <td class="p-4">

                <span class="text-[10px] font-medium px-2 py-1 bg-slate-50 text-slate-500 rounded-md border border-slate-100">
                  <?php echo htmlspecialchars($row['cat_nom'] ?? 'Général'); ?>
                </span>

              </td>

              <td class="p-4 text-[11px] text-slate-400 font-medium italic">
                <?php echo $row['date_complete']; ?>
              </td>

              <td class="p-4 text-sm font-serif italic text-slate-900 font-bold">
                <?php echo number_format($row['prix'], 2); ?> $
              </td>

              <td class="p-4">

                <div class="flex -space-x-1.5">

                  <?php

                  if (!empty($row['les_couleurs'])) {

                    $clean_colors = str_replace(
                      ['[', ']', '"', "'"],
                      '',
                      $row['les_couleurs']
                    );

                    $colors = explode(',', $clean_colors);

                    foreach ($colors as $hex) {

                      $hex = trim($hex);

                      if (empty($hex)) {
                        continue;
                      }

                      $valid_hex =
                        (strpos($hex, '#') === 0)
                        ? $hex
                        : '#' . $hex;

                      echo '
                      <span
                          class="w-5 h-5 rounded-full border-2 border-white shadow-sm ring-1 ring-slate-200"
                          style="background-color:' . $valid_hex . '; display:inline-block;"
                          title="' . htmlspecialchars($hex) . '">
                      </span>';
                    }
                  } else {

                    echo '<span class="text-[9px] text-slate-300 italic">Aucune</span>';
                  }

                  ?>

                </div>

              </td>

              <td class="p-4">

                <span
                  data-stock-id="<?php echo $row['id']; ?>"
                  class="px-2 py-1 rounded-full text-[10px] font-bold <?php echo $stockClass; ?>">

                  <?php echo $stockVal; ?> unités

                </span>

              </td>

              <td class="p-4 text-right">

                <div class="flex gap-1 justify-end">

                  <button
                    onclick="openStockModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nom']); ?>', <?php echo $stockVal; ?>)"
                    class="p-2 hover:bg-slate-100 text-slate-400 hover:text-black rounded-lg"
                    title="Mettre à jour le stock">

                    <i data-lucide="package-plus" class="w-4 h-4"></i>

                  </button>

                </div>

              </td>

            </tr>

          <?php endwhile; ?>

          <?php if (!$has_rows): ?>

            <tr>

              <td colspan="7" class="p-10 text-center text-xs uppercase tracking-widest text-slate-400 bg-slate-50/50 italic">

                Aucun produit ne correspond à vos critères de filtrage.

              </td>

            </tr>

          <?php endif; ?>

        </tbody>

      </table>

    </div>

  </div>

</div>

<script>
  let currentUpdateId = null;

  // ==========================
  // FILTRAGE AJAX
  // ==========================

  document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('inventoryFilterForm');

    form.addEventListener('submit', function(e) {

      e.preventDefault();

      const formData = new FormData(form);

      const params = new URLSearchParams(formData).toString();

      fetch(`/ProjetFelykay/pages/admin/tabs/inventory_list.php?${params}`)
        .then(response => response.text())
        .then(html => {

          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');

          const newContent = doc.querySelector('#inventory-content');

          document.querySelector('#inventory-content').innerHTML =
            newContent.innerHTML;

          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }

        })
        .catch(error => {

          console.error(error);

          Swal.fire(
            'Erreur',
            'Impossible de filtrer les données.',
            'error'
          );

        });

    });

  });

  // ==========================
  // MODAL STOCK
  // ==========================

  function openStockModal(id, nom, currentStock) {

    currentUpdateId = id;

    document.getElementById('modalTitle').innerText = nom;

    document.getElementById('modalDesc').innerText =
      `Stock actuel : ${currentStock} unités`;

    document.getElementById('newStockInput').value = currentStock;

    document.getElementById('stockModal').classList.remove('hidden');

    document.getElementById('newStockInput').focus();
  }

  function closeStockModal() {

    document.getElementById('stockModal').classList.add('hidden');

  }

  // ==========================
  // MISE À JOUR STOCK
  // ==========================

  function confirmStockUpdate() {

    const newStock =
      document.getElementById('newStockInput').value;

    if (newStock !== "" && !isNaN(newStock)) {

      fetch('/ProjetFelykay/assets/actions/update_stock.php', {

          method: 'POST',

          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },

          body: `id=${currentUpdateId}&stock=${newStock}`

        })

        .then(response => response.json())

        .then(data => {

          if (data.success) {

            closeStockModal();

            Swal.fire({
              icon: 'success',
              title: 'Mis à jour !',
              text: 'Le stock a été actualisé.',
              timer: 1500,
              showConfirmButton: false
            });

            const stockElements =
              document.querySelectorAll(
                `[data-stock-id="${currentUpdateId}"]`
              );

            stockElements.forEach(el => {

              el.innerHTML = `${newStock} unités`;

              el.className =
                'px-2 py-1 rounded-full text-[10px] font-bold';

              if (parseInt(newStock) <= 0) {

                el.classList.add(
                  'bg-rose-50',
                  'text-rose-600'
                );

              } else if (parseInt(newStock) <= 5) {

                el.classList.add(
                  'bg-amber-50',
                  'text-amber-600'
                );

              } else {

                el.classList.add(
                  'bg-slate-100',
                  'text-slate-900'
                );
              }

            });

          } else {

            Swal.fire(
              'Erreur',
              data.message,
              'error'
            );
          }

        })

        .catch(error => {

          console.error('Erreur:', error);

          Swal.fire(
            'Erreur',
            'Impossible de contacter le serveur.',
            'error'
          );

        });

    }

  }

  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
</script>