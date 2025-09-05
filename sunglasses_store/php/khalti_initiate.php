<?php
session_start();
require_once __DIR__ . '/db.php';

$ENV        = 'test'; 
$SECRET_KEY = '9e08cc6804ed4eafaab7d8f47145dda0'; 
$BASE_URL   = 'http://localhost/sunglasses_store'; 

$INITIATE_URL = ($ENV === 'test')
  ? 'https://dev.khalti.com/api/v2/epayment/initiate/'
  : 'https://khalti.com/api/v2/epayment/initiate/';

$RETURN_URL = $BASE_URL . '/php/khalti_verify.php';
$WEBSITE_URL= $BASE_URL;

if (!isset($_SESSION['user_id'])) {
  header('Location: ../signup.php');
  exit;
}
$user_id = (int)$_SESSION['user_id'];

$st = $pdo->prepare('
  SELECT c.product_id, c.quantity, p.name, p.price, p.image_url
  FROM cart c
  JOIN products p ON p.id = c.product_id
  WHERE c.user_id = ?
');
$st->execute([$user_id]);
$items = $st->fetchAll();

if (!$items) {
  echo "<script>alert('Your cart is empty.'); window.location.href='../cart.php';</script>";
  exit;
}

$total_rs = 0.0;
foreach ($items as $row) {
  $total_rs += ((float)$row['price']) * ((int)$row['quantity']);
}
$amount_paisa = (int) round($total_rs * 100);
if ($amount_paisa <= 0) {
  echo "<script>alert('Invalid amount.'); window.location.href='../cart.php';</script>";
  exit;
}

$usr = $pdo->prepare('SELECT name, email, phone FROM users WHERE id = ? LIMIT 1');
$usr->execute([$user_id]);
$u = $usr->fetch();
$customer_name  = $u['name']  ?? 'Customer';
$customer_email = $u['email'] ?? 'customer@example.com';
$customer_phone = $u['phone'] ?? '9800000001';

$product_details = [];
foreach ($items as $row) {
  $qty = (int)$row['quantity'];
  $price = (float)$row['price'];
  $product_details[] = [
    "identity"     => (string)$row['product_id'],
    "name"         => $row['name'],
    "total_price"  => (int)round($price * $qty * 100), 
    "quantity"     => $qty,
    "unit_price"   => (int)round($price * 100),        
  ];
}

$amount_breakdown = [
  ["label" => "Total", "amount" => $amount_paisa]
];

try {
  $pdo->beginTransaction();
  $pdo->prepare('INSERT INTO orders (order_code, user_id, total_amount, payment_status) VALUES ("", ?, ?, "pending")')
      ->execute([$user_id, $total_rs]);
  $order_id = (int)$pdo->lastInsertId();
  $order_code = 'ORD-' . date('YmdHis') . '-' . $order_id;
  $pdo->prepare('UPDATE orders SET order_code=? WHERE id=?')->execute([$order_code, $order_id]);

  $insItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)');
  foreach ($items as $row) {
    $insItem->execute([$order_id, (int)$row['product_id'], (int)$row['quantity'], (float)$row['price']]);
  }
  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
  echo "<pre>Could not create order.\n</pre>";
  exit;
}

$payload = [
  "return_url"          => $RETURN_URL,
  "website_url"         => $WEBSITE_URL,
  "amount"              => $amount_paisa,          
  "purchase_order_id"   => $order_code,            
  "purchase_order_name" => "SunStore Order",
  "customer_info"       => [
    "name"  => $customer_name,
    "email" => $customer_email,
    "phone" => (string)$customer_phone
  ],
  "amount_breakdown"    => $amount_breakdown,
  "product_details"     => $product_details
];

$ch = curl_init($INITIATE_URL);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => [
    "Authorization: Key {$SECRET_KEY}",
    "Content-Type: application/json"
  ],
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => json_encode($payload),
  CURLOPT_TIMEOUT        => 30
]);
$response = curl_exec($ch);
$http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200) {
  echo "<pre>Initiate failed (HTTP {$http})\n" . htmlspecialchars($response) . "</pre>";
  exit;
}

$data = json_decode($response, true);
if (!empty($data["payment_url"])) {

  if (!empty($data["pidx"])) {
    $pdo->prepare('UPDATE orders SET payment_ref=? WHERE id=?')->execute([$data["pidx"], $order_id]);
  }
  header("Location: " . $data["payment_url"]);
  exit;
}

echo "<pre>Unexpected initiate response:\n" . htmlspecialchars($response) . "</pre>";
