<?php

session_start();
if (empty($_SESSION['is_admin'])) { header('Location: ../admin.php'); exit; }

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/admin_config.php';


$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = (int)($_POST['price'] ?? 0);
$category = $_POST['category'] ?? '';

if(!$name || !$description || $price < 0 || !in_array($category, ['male','female','unisex'], true)){
    header('Location: ../admin.php?added=0');
    exit;
}

$image_url = 'assets/placeholder.svg';
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['image']['size'] > ADMIN_MAX_UPLOAD) {
        header('Location: ../admin.php?added=0');
        exit;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ADMIN_MIMES, true)) {
        header('Location: ../admin.php?added=0');
        exit;
    }
    $ext = match($mime){
        'image/jpeg' => '.jpg',
        'image/png'  => '.png',
        'image/webp' => '.webp',
        default => ''
    };
    $safeBase = preg_replace('/[^a-z0-9\-]/i', '-', strtolower($name));
    $filename = $safeBase . '-' . bin2hex(random_bytes(4)) . $ext;
    $targetDir = dirname(__DIR__) . '/assets/products/';
    if (!is_dir($targetDir)) { mkdir($targetDir, 0775, true); }
    $targetPath = $targetDir . $filename;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        header('Location: ../admin.php?added=0');
        exit;
    }
    $image_url = 'assets/products/' . $filename;
}

try{
    $st = $pdo->prepare('INSERT INTO products (name, description, price, category, image_url) VALUES (?,?,?,?,?)');
    $st->execute([$name, $description, $price, $category, $image_url]);
    header('Location: ../admin.php?added=1');
} catch(Throwable $e){
    header('Location: ../admin.php?added=0');
}
