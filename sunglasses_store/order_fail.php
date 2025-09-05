<?php include __DIR__ . '/header.php'; ?>
<section class="page-banner" style="background-image:url('assets/other_background.jpg');">
  <h1>Payment Failed</h1>
</section>
<section class="container section">
  <div class="form-card">
    <p>We couldn’t confirm your payment<?php if(isset($_GET['reason'])) echo ' ('.htmlspecialchars($_GET['reason']).')'; ?>.</p>
    <p><a class="btn-outline" href="cart.php">Back to cart</a></p>
  </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
