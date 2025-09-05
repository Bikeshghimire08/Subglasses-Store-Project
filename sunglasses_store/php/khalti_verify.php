<?php
session_start();
require_once __DIR__ . '/db.php';

$ENV        = 'test';                           
$SECRET_KEY = '9e08cc6804ed4eafaab7d8f47145dda0';      
$BASE_URL   = 'http://localhost/sunglasses-store';

$LOOKUP_URL = ($ENV === 'test')
  ? 'https://dev.khalti.com/api/v2/epayment/lookup/'
  : 'https://khalti.com/api/v2/epayment/lookup/';

$pidx = $_GET['pidx'] ?? '';
if (!$pidx) { header('Location: ../order_fail.php?reason=no_pidx'); exit; }

$ch = curl_init($LOOKUP_URL);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER     => [
    "Authorization: Key {$SECRET_KEY}",
    "Content-Type: application/json"
  ],
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => json_encode(["pidx" => $pidx]),
  CURLOPT_TIMEOUT        => 30
]);
$response = curl_exec($ch);
$http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

@file_put_contents(__DIR__.'/khalti.log', sprintf("[%s] HTTP:%s pidx:%s resp:%s\n", date('c'), $http, $pidx, $response), FILE_APPEND);

if ($http !== 200) { header('Location: ../order_fail.php?reason=lookup_http_'.$http); exit; }

$data   = json_decode($response, true) ?: [];
$status = $data['status'] ?? null;

if (!$status && (isset($data['detail']) || isset($data['error_key']))) {
  $reason = isset($data['detail']) ? $data['detail'] : $data['error_key'];
  header('Location: ../order_fail.php?reason='.urlencode($reason)); exit;
}
if ($status !== 'Completed') {
  header('Location: ../order_fail.php?reason=status_'.urlencode((string)$status)); exit;
}

$amt_paisa = null;
if (isset($data['total_amount'])) $amt_paisa = (int)$data['total_amount'];
elseif (isset($data['amount']))    $amt_paisa = (int)$data['amount'];

$poid = $data['purchase_order_id'] ?? ($data['order_id'] ?? ($data['purchase_order'] ?? ''));

$order = null;
if ($poid) {
  $st = $pdo->prepare('SELECT * FROM orders WHERE order_code = ? LIMIT 1');
  $st->execute([$poid]);
  $order = $st->fetch();
}

if (!$order) {
  $st = $pdo->prepare('SELECT * FROM orders WHERE payment_ref = ? LIMIT 1');
  $st->execute([$pidx]);
  $order = $st->fetch();
}

if (!$order) {
  header('Location: ../order_fail.php?reason=order_not_found'); exit;
}
if ($order['payment_status'] !== 'pending') {
  header('Location: ../order_success.php?order_id='.(int)$order['id']); exit;
}

$expected_paisa = (int)round(((float)$order['total_amount']) * 100);

if ($amt_paisa !== null && $expected_paisa !== $amt_paisa) {
  header('Location: ../order_fail.php?reason=amount_mismatch'); exit;
}

try {
  $pdo->beginTransaction();
  $pdo->prepare('UPDATE orders SET payment_method="khalti", payment_status="paid", payment_ref=?, payment_meta=? WHERE id=?')
      ->execute([$pidx, $response, (int)$order['id']]);
  $pdo->prepare('DELETE FROM cart WHERE user_id=?')->execute([(int)$order['user_id']]);
  $pdo->commit();
  header('Location: ../order_success.php?order_id='.(int)$order['id']); exit;
} catch (Throwable $e) {
  $pdo->rollBack();
  header('Location: ../order_fail.php?reason=save_fail'); exit;
}
