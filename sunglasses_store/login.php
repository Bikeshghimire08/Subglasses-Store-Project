<?php include __DIR__ . '/header.php'; ?>
<section class="page-banner" style="background-image:url('assets/other_background.jpg');">
  <h1>Sign in</h1>
</section>

<section class="container">
  <div class="form-card">
    <?php if(isset($_GET['error'])): ?>
      <p class="text-center" style="color:#B91C1C;">
        <?php
          switch($_GET['error']){
            case 'missing': echo 'Please enter both email and password.'; break;
            case 'invalid': echo 'Invalid email or password.'; break;
            default: echo 'Something went wrong. Try again.';
          }
        ?>
      </p>
    <?php endif; ?>

    <form id="loginForm" action="php/login_handler.php" method="post" novalidate>
      <label for="email">Email address</label>
      <input type="email" id="email" name="email" placeholder="you@example.com" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Your password" required>

      <div class="form-actions">
        <button class="add-btn btn-wide" type="submit">Login</button>
      </div>
    </form>

    <p class="text-center mt-2">No account? <a href="signup.php"><strong>Signup</strong></a></p>
  </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
