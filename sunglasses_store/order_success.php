<?php
require_once __DIR__ . '/php/db.php';
include __DIR__ . '/header.php';

$order_id = (int)($_GET['order_id'] ?? 0);
$st = $pdo->prepare('SELECT order_code,total_amount,payment_method,payment_status,created_at FROM orders WHERE id=?');
$st->execute([$order_id]);
$o = $st->fetch();
?>
<section class="page-banner" style="background-image:url('assets/other_background.jpg');">
  <h1>Order Confirmation</h1>
</section>
<section class="container section">
  <?php if(!$o || $o['payment_status'] !== 'paid'): ?>
    <p>Order not found or payment incomplete. <a href="home.php">Go home</a></p>
  <?php else: ?>
    <div class="form-card">
      <h3>Thank you! 🎉</h3>
      <ul>
        <li><strong>Order code:</strong> <?php echo htmlspecialchars($o['order_code']); ?></li>
        <li><strong>Amount:</strong> रु <?php echo number_format((float)$o['total_amount'], 0); ?></li>
        <li><strong>Method:</strong> <?php echo htmlspecialchars(strtoupper($o['payment_method'])); ?></li>
        <li><strong>Date:</strong> <?php echo htmlspecialchars($o['created_at']); ?></li>
      </ul>
      <p><a class="btn-outline" href="home.php">Continue shopping</a></p>
    </div>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/footer.php'; ?>
