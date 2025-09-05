<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sunglasses Store</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="home.php">SunStore</a>

    <nav class="site-nav" aria-label="Primary">
      <a href="home.php">Home</a>

      <div class="dropdown">
        <button class="dropbtn"
                type="button"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="nav-sg-menu">
          Sunglasses ▾
        </button>
        <div class="dropdown-menu" id="nav-sg-menu" role="menu">
          <a role="menuitem" href="male.php">Male Sunglasses</a>
          <a role="menuitem" href="female.php">Female Sunglasses</a>
          <a role="menuitem" href="unisex.php">Unisex Sunglasses</a>
        </div>
      </div>

      <?php if(isset($_SESSION['user_id'])): ?>
        <span class="nav-greeting">Hi, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Shopper'); ?></span>
        <a href="cart.php" class="cart-link" title="Cart">Cart (<span id="cartCount">0</span>)</a>
        <a href="php/logout.php" class="logout-link" title="Logout">Logout</a>
      <?php else: ?>
        <a href="cart.php" class="cart-link" title="Cart">Cart (<span id="cartCount">0</span>)</a>
        <a href="login.php">Login</a>
        <a href="signup.php">Signup</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="page-content">
