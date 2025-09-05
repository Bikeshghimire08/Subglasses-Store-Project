<?php
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/payments_config.php';
include __DIR__ . '/header.php';

if (!isset($_SESSION['user_id'])) {
  echo '<section class="container section"><p>Please <a href="signup.php">sign up</a> (or log in) to checkout.</p></section>';
  include __DIR__ . '/footer.php'; exit;
}
$user_id = (int)$_SESSION['user_id'];

$st = $pdo->prepare('
  SELECT c.product_id, c.quantity, p.name, p.price, p.image_url
  FROM cart c JOIN products p ON p.id = c.product_id
  WHERE c.user_id = ?
');
$st->execute([$user_id]);
$items = $st->fetchAll();
if (!$items) { echo '<section class="container section"><p>Your cart is empty. <a href="home.php">Continue shopping</a>.</p></section>'; include __DIR__ . '/footer.php'; exit; }

$total = 0.0; foreach($items as $it){ $total += $it['price'] * $it['quantity']; }

try {
  $pdo->beginTransaction();
  $pdo->prepare('INSERT INTO orders (order_code, user_id, total_amount, payment_status) VALUES ("", ?, ?, "pending")')
      ->execute([$user_id, $total]);
  $order_id = (int)$pdo->lastInsertId();
  $order_code = 'ORD-' . date('YmdHis') . '-' . $order_id;
  $pdo->prepare('UPDATE orders SET order_code=? WHERE id=?')->execute([$order_code, $order_id]);

  $insItem = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)');
  foreach($items as $it){ $insItem->execute([$order_id, (int)$it['product_id'], (int)$it['quantity'], (float)$it['price']]); }
  $pdo->commit();
} catch (Throwable $e) { $pdo->rollBack(); echo '<section class="container section"><p>Could not create order. Please try again.</p></section>'; include __DIR__ . '/footer.php'; exit; }

$amount_nrs   = (float)$total;
$amount_paisa = (int)round($amount_nrs * 100);
?>
<section class="page-banner" style="background-image:url('assets/other_background.jpg');">
  <h1>Checkout</h1>
</section>

<section class="container section">
  <div class="grid">
    <div class="product-card" style="grid-column: span 8;">
      <div class="product-body">
        <div class="product-title">Order #<?php echo $order_id; ?> Summary</div>
        <table class="table">
          <thead><tr><th>Product</th><th width="100">Qty</th><th width="140">Price (रु)</th><th width="160">Subtotal (रु)</th></tr></thead>
          <tbody>
            <?php foreach($items as $it): ?>
              <tr>
                <td><?php echo htmlspecialchars($it['name']); ?></td>
                <td><?php echo (int)$it['quantity']; ?></td>
                <td>रु <?php echo number_format((float)$it['price'], 0); ?></td>
                <td>रु <?php echo number_format((float)$it['price'] * (int)$it['quantity'], 0); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot><tr><td colspan="3" style="text-align:right;">Total</td><td><strong>रु <?php echo number_format($amount_nrs, 0); ?></strong></td></tr></tfoot>
        </table>
      </div>
    </div>

    <div class="form-card" style="grid-column: span 4;">
      <h3>Pay with Khalti</h3>
      <button id="btnKhalti" class="add-btn btn-wide">Pay रु <?php echo number_format($amount_nrs, 0); ?></button>
      <p class="tiny muted mt-2">You’ll be redirected to an order confirmation after payment.</p>
    </div>
  </div>
</section>

<script src="https://khalti.com/static/khalti-checkout.js"></script>
<script>
  (function(){
    const orderId     = <?php echo json_encode($order_id); ?>;
    const amountPaisa = <?php echo json_encode($amount_paisa); ?>;

    const checkout = new KhaltiCheckout({
      publicKey: <?php echo json_encode(KHALTI_PUBLIC_KEY); ?>,
      productIdentity: String(orderId),
      productName: 'SunStore Order #' + orderId,
      productUrl: <?php echo json_encode(SITE_BASE . '/checkout.php?order=' . $order_id); ?>,
      paymentPreference: ['KHALTI','EBANKING','MOBILE_BANKING','CONNECT_IPS','SCT'],
      eventHandler: {
        onSuccess: async function(payload){
          try{
            const res = await fetch('php/khalti_verify.php', {
              method:'POST',
              headers:{'Content-Type':'application/x-www-form-urlencoded'},
              body: new URLSearchParams({
                order_id: orderId,
                token: payload.token,
                amount_paisa: amountPaisa
              }).toString()
            });
            const data = await res.json();
            if(data.ok){ window.location.href = data.redirect; }
            else{ alert(data.message || 'Payment verification failed.'); }
          }catch(e){ alert('Network error verifying payment.'); }
        },
        onError: function(err){ console.error(err); alert('Payment error. Try again.'); },
        onClose: function(){ /* closed */ }
      }
    });

    document.getElementById('btnKhalti').addEventListener('click', function(){
      checkout.show({ amount: amountPaisa });
    });
  })();
</script>
<?php include __DIR__ . '/footer.php'; ?>
