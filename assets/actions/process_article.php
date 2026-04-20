<?php
require_once '../../config/db.php';
require_once '../../includes/function.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // 1. RÉCUPÉRATION ET NETTOYAGE DES DONNÉES
  $nom           = htmlspecialchars($_POST['nom'] ?? '');
  $prix          = floatval($_POST['prix'] ?? 0);
  $devise        = $_POST['devise'] ?? 'USD';
  $categorie_id  = intval($_POST['categorie_id'] ?? 0);

  // Valeurs par défaut pour éviter les colonnes vides
  $tranche_age   = !empty($_POST['tranche_age']) ? $_POST['tranche_age'] : 'adulte';
  $genre         = !empty($_POST['genre']) ? $_POST['genre'] : 'mixte';

  // NOUVEAU : Récupération du type d'accessoire envoyé par le formulaire
  $type_accessoire = $_POST['type_accessoire'] ?? null;

  $stock_total   = intval($_POST['stock_total'] ?? 0);
  $description   = htmlspecialchars($_POST['description'] ?? '');
  $colors_raw    = $_POST['colors'] ?? null;

  // 2. GESTION DE L'IMAGE
  $image_final_path = "https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=600";
  if (!empty($_FILES['image_principale']['name'])) {
    $uploaded_path = uploadImage($_FILES['image_principale']);
    if ($uploaded_path) {
      $image_final_path = $uploaded_path;
    }
  }

  try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO produits 
        (nom, prix, devise, categorie_id, tranche_age, genre, type_accessoire, stock_total, image_principale, description, created_at) 
        VALUES (:nom, :prix, :devise, :cat, :age, :genre, :type_acc, :stock, :img, :descr, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':nom'      => $nom,
      ':prix'     => $prix,
      ':devise'   => $devise,
      ':cat'      => $categorie_id,
      ':age'      => $tranche_age,
      ':genre'    => $genre,
      ':type_acc' => $type_accessoire,
      ':stock'    => $stock_total,
      ':img'      => $image_final_path,
      ':descr'    => $description
    ]);

    $last_id = $pdo->lastInsertId();

    // 4. GESTION DE LA TAILLE 
    // On n'insère de taille que si le champ n'est pas vide (les accessoires n'en ont pas)
    if (!empty($_POST['taille_nom']) && $categorie_id != 4) {
      $taille_input = strtoupper(trim($_POST['taille_nom']));
      $stmtCheckT = $pdo->prepare("SELECT id FROM tailles WHERE nom = ?");
      $stmtCheckT->execute([$taille_input]);
      $taille_data = $stmtCheckT->fetch();
      $taille_id = $taille_data ? $taille_data['id'] : null;

      if (!$taille_id) {
        $stmtInsertT = $pdo->prepare("INSERT INTO tailles (nom, type) VALUES (?, ?)");
        $stmtInsertT->execute([$taille_input, 'vetement']);
        $taille_id = $pdo->lastInsertId();
      }
      $stmtLinkT = $pdo->prepare("INSERT INTO produit_tailles (produit_id, taille_id, stock_disponible) VALUES (?, ?, ?)");
      $stmtLinkT->execute([$last_id, $taille_id, $stock_total]);
    }

    // 5. GESTION DES COULEURS
    if (!empty($colors_raw)) {
      $couleurs_json = json_decode($colors_raw, true);
      if (is_array($couleurs_json)) {
        foreach ($couleurs_json as $hex) {
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
    }

    $pdo->commit();

    $referer = $_SERVER['HTTP_REFERER'] ?? '../../pages/admin/admin_dashboard.php';
    $clean_referer = explode('?', $referer)[0];
    header("Location: " . $clean_referer . "?msg=success#section-products");
    exit();
  } catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Erreur SQL : " . $e->getMessage());
  }
}
