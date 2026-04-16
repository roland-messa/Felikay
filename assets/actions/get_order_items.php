<?php
require_once '../../config/db.php';

$orderId = intval($_GET['id']);

$stmt = $pdo->prepare("
    SELECT dc.*, p.nom, p.image_principale 
    FROM details_commandes dc 
    JOIN produits p ON dc.produit_id = p.id 
    WHERE dc.commande_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($items);
