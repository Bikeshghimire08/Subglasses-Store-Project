<?php
session_start();
if (empty($_SESSION['is_admin'])) { header('Location: ../admin.php'); exit; }

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/admin_config.php';

$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = (int)($_POST['price'] ?? 0);
$category = $_POST['category'] ?? '';

if($id <= 0 || !$name || !$description || $price < 0 || !in_array($category, ['male','female','unisex'], true)){
    header('Location: ../admin.php');
    exit;
}

$st = $pdo->prepare('SELECT image_url FROM products WHERE id=?');
$st->execute([$id]);
$row = $st->fetch();
if(!$row){ header('Location: ../admin.php'); exit; }
$image_url = $row['image_url'];

if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['image']['size'] <= ADMIN_MAX_UPLOAD) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        if (in_array($mime, ADMIN_MIMES, true)) {
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
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                
                if (strpos($image_url, 'assets/products/') === 0) {
                    $oldPath = dirname(__DIR__) . '/' . $image_url;
                    if (is_file($oldPath)) @unlink($oldPath);
                }
                $image_url = 'assets/products/' . $filename;
            }
        }
    }
}

try{
    $up = $pdo->prepare('UPDATE products SET name=?, description=?, price=?, category=?, image_url=? WHERE id=?');
    $up->execute([$name, $description, $price, $category, $image_url, $id]);
} catch(Throwable $e){}
header('Location: ../admin.php');
