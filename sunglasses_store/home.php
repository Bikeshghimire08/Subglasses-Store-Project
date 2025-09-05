<?php
require_once __DIR__ . '/php/db.php';
include __DIR__ . '/header.php';

$st = $pdo->query('SELECT id, name, description, price, image_url, category FROM products ORDER BY created_at DESC LIMIT 6');
$products = $st->fetchAll();
?>
<section class="page-hero" style="background-image: url('assets/home_background.jpg');">
  <div class="hero-card container">
    <span class="badge">Summer 2025</span>
    <h1>Bright, Bold & UV-Safe 😎</h1>
    <p>Find your perfect summer pair — polarized, comfy, and wallet-friendly.</p>
    <div class="hero-actions">
      <a class="btn-primary" href="unisex.php">Shop Unisex</a>
      <a class="btn-outline" href="male.php">Shop Male</a>
      <a class="btn-outline" href="female.php">Shop Female</a>
    </div>
    <?php if(isset($_GET['signup']) && $_GET['signup']==='success'): ?>
      <p class="mt-2"><strong>Welcome!</strong> You’re signed in. Happy shopping 🌞</p>
    <?php endif; ?>
    <?php if(isset($_GET['login'])): ?>
      <p class="mt-2"><strong>Welcome back!</strong> You’re signed in ✅</p>
    <?php endif; ?>
    <?php if(isset($_GET['logout'])): ?>
      <p class="mt-2">You’ve logged out. See you soon! 👋</p>
    <?php endif; ?>
  </div>
</section>

<section class="section container">
  <h2>New Arrivals</h2>
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
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
