<?php

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

require_once __DIR__ . '/db.php'; 

function back($code){
  header('Location: ../signup.php?error=' . urlencode($code));
  exit;
}

try {
  if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    back('csrf');
  }

  $fields = ['name','email','phone','address','password','confirm_password'];
  foreach ($fields as $f) {
    if (!isset($_POST[$f]) || trim($_POST[$f]) === '') { back('missing'); }
  }

  $name  = trim($_POST['name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $addr  = trim($_POST['address']);
  $pass  = $_POST['password'];
  $conf  = $_POST['confirm_password'];

  
  if (mb_strlen($name) < 2) back('name');

  
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) back('email');

  
  $digits = preg_replace('/\D+/', '', $phone);     // keep digits
  
  if (strpos($digits, '977') === 0) { $digits = substr($digits, 3); }
  if (!preg_match('/^(98|97)\d{8}$/', $digits)) back('phone');

 
  if (mb_strlen($addr) < 3) back('address');

  
  if (strlen($pass) < 6) back('weak');
  if ($pass !== $conf)   back('mismatch');

  
  $st = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
  $st->execute([$email]);
  if ($st->fetch()) back('exists');

  
  $hash = password_hash($pass, PASSWORD_DEFAULT);

  $ins = $pdo->prepare('
    INSERT INTO users (name, email, phone, address, password_hash, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
  ');
  $ins->execute([$name, $email, $digits, $addr, $hash]);

  
  $uid = (int)$pdo->lastInsertId();
  $_SESSION['user_id'] = $uid;
  $_SESSION['user_name'] = $name;

 
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

  
  header('Location: ../home.php');
  exit;

} catch (Throwable $e) {
  
  back('unknown');
}
