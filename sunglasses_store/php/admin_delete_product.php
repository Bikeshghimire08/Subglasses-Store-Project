<?php

session_start();
if (empty($_SESSION['is_admin'])) { header('Location: ../admin.php'); exit; }

require_once __DIR__ . '/db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { header('Location: ../admin.php'); exit; }

$st = $pdo->prepare('SELECT image_url FROM products WHERE id=?');
$st->execute([$id]);
$row = $st->fetch();

try{
    $del = $pdo->prepare('DELETE FROM products WHERE id=?');
    $del->execute([$id]);

    if($row && isset($row['image_url']) && strpos($row['image_url'], 'assets/products/') === 0){
        $file = dirname(__DIR__) . '/' . $row['image_url'];
        if(is_file($file)) @unlink($file);
    }
} catch(Throwable $e){}

header('Location: ../admin.php');
