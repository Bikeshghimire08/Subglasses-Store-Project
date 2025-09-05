<?php
require_once __DIR__ . '/php/db.php';
include __DIR__ . '/header.php';

$cat = 'female';
$st = $pdo->prepare('SELECT id, name, description, price, image_url FROM products WHERE category = ? ORDER BY created_at DESC');
$st->execute([$cat]);
$products = $st->fetchAll();
?>
<section class="page-banner" style="background-image:url('assets/other_background.jpg');">
  <h1>Female Sunglasses</h1>
</section>

<section class="section container">
  <h2>Sunny Styles for Her</h2>
  <div class="grid">
    <?php foreach($products as $p): ?>
      <article class="product-card">
        <div class="product-media">
          <img src="<?php echo htmlspecialchars($p['image_url']); ?>"
               alt="<?php echo htmlspecialchars($p['name']); ?>"
               onerror="this.src='assets/placeholder.svg'">
        </div>
        <div class="product-body">
          <div class="product-title"><?php echo htmlspecialchars($p['name']); ?></div>
          <div class="product-desc"><?php echo htmlspecialchars($p['description']); ?></div>
          <div class="product-foot">
            <div class="price">रु <?php echo number_format((float)$p['price'], 0); ?></div>
            <button class="add-btn" data-product-id="<?php echo (int)$p['id']; ?>">Add to Cart</button>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
    <?php if(empty($products)): ?>
      <p>No products yet. Add some rows to <code>products</code> with category <strong>female</strong>.</p>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
