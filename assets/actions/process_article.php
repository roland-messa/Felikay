<?php
require_once '../../config/db.php';
require_once '../../includes/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 1. RÉCUPÉRATION ET NETTOYAGE
  $nom             = htmlspecialchars($_POST['nom'] ?? '');
  $prix            = floatval($_POST['prix'] ?? 0);
  $devise          = $_POST['devise'] ?? 'USD';
  $categorie_id    = intval($_POST['categorie_id'] ?? 0);
  $stock_total     = intval($_POST['stock_total'] ?? 0);
  $description     = htmlspecialchars($_POST['description'] ?? '');
  $colors_raw      = $_POST['colors'] ?? '';
  $type_accessoire = $_POST['type_accessoire'] ?? null;
  $tranche_age     = !empty($_POST['tranche_age']) ? $_POST['tranche_age'] : 'adulte';
  $genre           = !empty($_POST['genre']) ? $_POST['genre'] : 'unisexe';
  $is_new          = isset($_POST['is_new']) ? 1 : 0;
  $is_promo        = isset($_POST['is_promo']) ? 1 : 0;
  $actif_accueil   = isset($_POST['actif_accueil']) ? 1 : 0;
  $subtitle        = htmlspecialchars($_POST['subtitle'] ?? '');
  $promo_tag       = htmlspecialchars($_POST['promo_tag'] ?? '');
  $promo_duration  = $_POST['promo_duration'] ?? null;

  // 2. GESTION DES IMAGES
  // On ne stocke que le NOM du fichier. Si vide, on met un nom par défaut.
  $image_final_name = "default-product.jpg";

  if (!empty($_FILES['image_principale']['name'])) {
    $uploaded = uploadImage($_FILES['image_principale']);
    // uploadImage retourne maintenant juste "produit_xyz.jpg"
    if ($uploaded) $image_final_name = $uploaded;
  }

  // Pour les vues secondaires : soit le nom du fichier, soit NULL
  $img_dos    = !empty($_FILES['image_dos']['name'])    ? uploadImage($_FILES['image_dos'])    : null;
  $img_gauche = !empty($_FILES['image_gauche']['name']) ? uploadImage($_FILES['image_gauche']) : null;
  $img_droite = !empty($_FILES['image_droite']['name']) ? uploadImage($_FILES['image_droite']) : null;

  try {
    $pdo->beginTransaction();

    // 3. INSERTION DU PRODUIT
    $sql = "INSERT INTO produits 
                (nom, prix, devise, categorie_id, tranche_age, genre, type_accessoire, 
                stock_total, image_principale, image_dos, image_gauche, image_droite,
                description, is_new, is_promo, 
                actif_accueil, subtitle, promo_tag, promo_duration, created_at) 
                VALUES 
                (:nom, :prix, :devise, :cat, :age, :genre, :type_acc, 
                :stock, :img, :img_dos, :img_gauche, :img_droite,
                :descr, :is_new, :is_promo, 
                :accueil, :sub, :tag, :duration, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':nom'          => $nom,
      ':prix'         => $prix,
      ':devise'       => $devise,
      ':cat'          => $categorie_id,
      ':age'          => $tranche_age,
      ':genre'        => $genre,
      ':type_acc'     => $type_accessoire,
      ':stock'        => $stock_total,
      ':img'          => $image_final_name,
      ':img_dos'      => $img_dos,
      ':img_gauche'   => $img_gauche,
      ':img_droite'   => $img_droite,
      ':descr'        => $description,
      ':is_new'       => $is_new,
      ':is_promo'     => $is_promo,
      ':accueil'      => $actif_accueil,
      ':sub'          => $subtitle,
      ':tag'          => $promo_tag,
      ':duration'     => $promo_duration
    ]);

    $last_id = $pdo->lastInsertId();

    // 4. GESTION DE LA TAILLE
    if (!empty($_POST['taille_nom'])) {
      $taille_input = strtoupper(trim($_POST['taille_nom']));
      $type_taille = (in_array($categorie_id, [4, 5, 6])) ? 'pointure' : 'vetement';

      $stmtCheckT = $pdo->prepare("SELECT id FROM tailles WHERE nom = ?");
      $stmtCheckT->execute([$taille_input]);
      $taille_data = $stmtCheckT->fetch();
      $taille_id = $taille_data ? $taille_data['id'] : null;

      if (!$taille_id) {
        $stmtInsertT = $pdo->prepare("INSERT INTO tailles (nom, type) VALUES (?, ?)");
        $stmtInsertT->execute([$taille_input, $type_taille]);
        $taille_id = $pdo->lastInsertId();
      }

      $stmtLinkT = $pdo->prepare("INSERT INTO produit_tailles (produit_id, taille_id, stock_disponible) VALUES (?, ?, ?)");
      $stmtLinkT->execute([$last_id, $taille_id, $stock_total]);
    }

    // 5. GESTION DES COULEURS
    if (!empty($colors_raw)) {
      $couleurs_array = explode(',', $colors_raw);
      foreach ($couleurs_array as $hex) {
        $hex = strtoupper(trim($hex));
        if (empty($hex)) continue;

        $stmtC = $pdo->prepare("SELECT id FROM couleurs WHERE code_hex = ?");
        $stmtC->execute([$hex]);
        $couleur_data = $stmtC->fetch();
        $couleur_id = $couleur_data ? $couleur_data['id'] : null;

        if (!$couleur_id) {
          $stmtInsC = $pdo->prepare("INSERT INTO couleurs (nom, code_hex) VALUES (?, ?)");
          $stmtInsC->execute([$hex, $hex]);
          $couleur_id = $pdo->lastInsertId();
        }

        $stmtLinkC = $pdo->prepare("INSERT INTO produit_couleurs (produit_id, couleur_id) VALUES (?, ?)");
        $stmtLinkC->execute([$last_id, $couleur_id]);
      }
    }

    $pdo->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'L\'article a été ajouté avec succès !']);
    exit();
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();

    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
    exit();
  }
}
