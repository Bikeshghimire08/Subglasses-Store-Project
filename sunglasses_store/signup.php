<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<?php include __DIR__ . '/header.php'; ?>


<style>
  
  header nav,
  .navbar, .site-nav, .main-nav, .topbar, .header-nav, .menu, .nav,
  [role="navigation"], #nav, #navbar {
    display: none !important;
  }
  
  header { margin-bottom: 0 !important; }
</style>

<section class="page-banner" style="background-image:url('assets/other_background.jpg');">
  <h1>Create your account</h1>
</section>

<section class="container">
  <div class="form-card">

    <?php if(isset($_GET['error'])): ?>
      <p class="text-center" style="color:#B91C1C;margin-bottom:1rem;">
        <?php
          switch($_GET['error']){
            case 'missing':  echo 'Please fill out all fields.'; break;
            case 'name':     echo 'Please enter your full name (min 2 letters).'; break;
            case 'email':    echo 'Invalid email format.'; break;
            case 'phone':    echo 'Invalid phone number. Use e.g. +977 98XXXXXXXX.'; break;
            case 'address':  echo 'Please provide a valid address.'; break;
            case 'weak':     echo 'Password must be at least 6 characters.'; break;
            case 'mismatch': echo 'Passwords do not match.'; break;
            case 'exists':   echo 'This email is already registered.'; break;
            case 'csrf':     echo 'Security token invalid. Please try again.'; break;
            default:         echo 'Something went wrong. Try again.';
          }
        ?>
      </p>
    <?php endif; ?>

    <form id="signupForm" action="php/signup_handler.php" method="post">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

      <label for="name">Full name</label>
      <input
        type="text"
        id="name"
        name="name"
        placeholder="e.g. Bikesh Ghimire"
        minlength="2"
        required
      >
      <small class="field-error" id="err_name"></small>

      <label for="email">Email address</label>
      <input
        type="email"
        id="email"
        name="email"
        placeholder="you@example.com"
        required
      >
      <small class="field-error" id="err_email"></small>

      <label for="phone">Phone number</label>
      <input
        type="tel"
        id="phone"
        name="phone"
        placeholder="e.g. +977 98XXXXXXXX"
        pattern="^(\+?977[- ]?)?(98|97)\d{8}$"
        title="Use +977 98XXXXXXXX or 98XXXXXXXX"
        required
      >
      <small class="field-error" id="err_phone"></small>

      <label for="address">Address</label>
      <input
        type="text"
        id="address"
        name="address"
        placeholder="e.g. Tokha, Kathmandu"
        minlength="3"
        required
      >
      <small class="field-error" id="err_address"></small>

      <label for="password">Password</label>
      <input
        type="password"
        id="password"
        name="password"
        placeholder="Min 6 characters"
        minlength="6"
        required
      >
      <small class="field-error" id="err_password"></small>

      <label for="confirm_password">Confirm password</label>
      <input
        type="password"
        id="confirm_password"
        name="confirm_password"
        placeholder="Re-enter password"
        minlength="6"
        required
      >
      <small class="field-error" id="err_confirm"></small>

      <div class="form-actions">
        <button class="add-btn btn-wide" type="submit">Create Account</button>
      </div>
    </form>
  </div>
</section>

<style>
  /* inline error helpers */
  .field-error { color:#B91C1C; display:block; min-height:1.1rem; margin:.25rem 0 .75rem; }
  input.is-invalid { border-color:#B91C1C; outline-color:#B91C1C; }
</style>

<script>
  (function(){
    const form  = document.getElementById('signupForm');
    const name  = document.getElementById('name');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const addr  = document.getElementById('address');
    const pass  = document.getElementById('password');
    const conf  = document.getElementById('confirm_password');

    const err = {
      name:  document.getElementById('err_name'),
      email: document.getElementById('err_email'),
      phone: document.getElementById('err_phone'),
      addr:  document.getElementById('err_address'),
      pass:  document.getElementById('err_password'),
      conf:  document.getElementById('err_confirm')
    };

    const phoneRe = /^(\+?977[- ]?)?(98|97)\d{8}$/;
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function invalid(el, msgEl, message){
      el.classList.add('is-invalid');
      msgEl.textContent = message;
      return false;
    }
    function clearInvalid(el, msgEl){
      el.classList.remove('is-invalid');
      msgEl.textContent = '';
    }

    form.addEventListener('submit', function(e){
      let ok = true;

      if (!name.value.trim() || name.value.trim().length < 2){
        ok = invalid(name, err.name, 'Please enter your full name (min 2 letters).');
      } else { clearInvalid(name, err.name); }

      if (!emailRe.test(email.value.trim())){
        ok = invalid(email, err.email, 'Please enter a valid email.');
      } else { clearInvalid(email, err.email); }

      if (!phoneRe.test(phone.value.trim())){
        ok = invalid(phone, err.phone, 'Use +977 98XXXXXXXX or 98XXXXXXXX.');
      } else { clearInvalid(phone, err.phone); }

      if (!addr.value.trim() || addr.value.trim().length < 3){
        ok = invalid(addr, err.addr, 'Please provide a valid address.');
      } else { clearInvalid(addr, err.addr); }

      if (pass.value.length < 6){
        ok = invalid(pass, err.pass, 'Password must be at least 6 characters.');
      } else { clearInvalid(pass, err.pass); }

      if (conf.value !== pass.value){
        ok = invalid(conf, err.conf, 'Passwords do not match.');
      } else { clearInvalid(conf, err.conf); }

      if (!ok) e.preventDefault();
    });
  })();
</script>

<?php include __DIR__ . '/footer.php'; ?>
