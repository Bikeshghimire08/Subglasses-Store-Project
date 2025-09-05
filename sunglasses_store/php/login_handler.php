<?php
session_start();
require_once __DIR__ . '/db.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if(!$email || !$password){
    header('Location: ../login.php?error=missing');
    exit;
}

try {
    $st = $pdo->prepare('SELECT id, name, password_hash FROM users WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    $user = $st->fetch();

    if(!$user || !password_verify($password, $user['password_hash'])){
        header('Location: ../login.php?error=invalid');
        exit;
    }

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];

    header('Location: ../home.php?login=1');
    exit;
} catch (Exception $e) {
    header('Location: ../login.php?error=server');
    exit;
}
