<?php

session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo 'Not signed in';
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    http_response_code(400);
    echo 'Invalid product';
    exit;
}

try {
    
    $st = $pdo->prepare('SELECT id FROM products WHERE id = ?');
    $st->execute([$product_id]);
    if(!$st->fetch()){
        http_response_code(404);
        echo 'Product not found';
        exit;
    }

    
    try{
        $ins = $pdo->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (?,?,1)');
        $ins->execute([$user_id, $product_id]);
    }catch(PDOException $e){
        
        $upd = $pdo->prepare('UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?');
        $upd->execute([$user_id, $product_id]);
    }

    echo 'OK';
} catch (Exception $e) {
    http_response_code(500);
    echo 'Server error';
}
