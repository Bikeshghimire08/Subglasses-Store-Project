<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$st = $pdo->prepare('SELECT COALESCE(SUM(quantity),0) AS c FROM cart WHERE user_id = ?');
$st->execute([$user_id]);
$row = $st->fetch();

echo json_encode(['count' => (int)($row['c'] ?? 0)]);
