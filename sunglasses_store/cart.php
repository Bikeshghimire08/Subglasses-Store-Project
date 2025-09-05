<?php
require_once __DIR__ . '/php/db.php';
include __DIR__ . '/header.php';

if (!isset($_SESSION['user_id'])) {
  echo '<section class="container section"><p>Please <a href="signup.php">sign up</a> to view your cart.</p></section>';
  include __DIR__ . '/footer.php';
  exit;
}

$user_id = (int)$_SESSION['user_id'];

if (isset($_GET['remove'])) {
    $pid = (int)$_GET['remove'];
    $del = $pdo->prepare('DELETE FROM cart WHERE user_id = ? AND product_id = ?');
    $del->execute([$user_id, $pid]);
    header('Location: cart.php');
    exit;
}

$st = $pdo->prepare('
  SELECT c.product_id, c.quantity, p.name, p.price, p.image_url
  FROM cart c
  JOIN products p ON p.id = c.product_id
  WHERE c.user_id = ?
');
$st->execute([$user_id]);
$items = $st->fetchAll();

$total = 0.0;
foreach($items as $it){
  $total += $it['price'] * $it['quantity'];
}
?>
<section class="page-banner" style="background-image:url('assets/other_background.jpg');">
  <h1>Your Cart</h1>
</section>

<section class="container section">
  <?php if(empty($items)): ?>
    <p>Your cart is empty. <a href="home.php">Browse products</a>.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="table">
        <thead>
          <tr>
            <th>Product</th>
            <th width="100">Qty</th>
            <th width="140">Price (रु)</th>
            <th width="160">Subtotal (रु)</th>
            <th width="100">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($items as $it): ?>
          <tr>
            <td>
              <div style="display:flex;gap:.7rem;align-items:center;">
                <img src="<?php echo htmlspecialchars($it['image_url']); ?>" alt="" width="68" height="48" style="object-fit:cover;border-radius:10px" onerror="this.src='assets/placeholder.svg'">
                <div>
                  <strong><?php echo htmlspecialchars($it['name']); ?></strong>
                </div>
              </div>
            </td>
            <td><?php echo (int)$it['quantity']; ?></td>
            <td>रु <?php echo number_format((float)$it['price'], 0); ?></td>
            <td>रु <?php echo number_format((float)$it['price'] * (int)$it['quantity'], 0); ?></td>
            <td>
              <a class="btn-outline" href="cart.php?remove=<?php echo (int)$it['product_id']; ?>"
                 onclick="return confirm('Remove this item?');">Remove</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" style="text-align:right;">Total</td>
            <td><strong>रु <?php echo number_format($total, 0); ?></strong></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="form-actions" style="text-align:right;margin-top:1rem;">
      <a class="add-btn" href="php/khalti_initiate.php">Pay with Khalti</a>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/footer.php'; ?>
