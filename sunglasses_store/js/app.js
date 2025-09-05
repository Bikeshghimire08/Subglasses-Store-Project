
async function refreshCartCount(){
  try{
    const res = await fetch('php/cart_count.php');
    if(!res.ok) return;
    const data = await res.json();
    const el = document.getElementById('cartCount');
    if(el) el.textContent = data.count ?? 0;
  }catch(e){}
}

document.addEventListener('click', async (e) => {
  const btn = e.target.closest('.add-btn[data-product-id]');
  if(!btn) return;

  const productId = btn.getAttribute('data-product-id');

  try{
    const res = await fetch('php/add_to_cart.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: 'product_id=' + encodeURIComponent(productId)
    });

    if(res.status === 401){
      alert('Please sign up first to add items to your cart.');
      window.location.href = 'signup.php';
      return;
    }
    if(!res.ok){
      const t = await res.text();
      alert('Could not add to cart: ' + t);
      return;
    }
    await refreshCartCount();
    btn.textContent = 'Added ✅';
    setTimeout(() => btn.textContent = 'Add to Cart', 1200);
  }catch(err){
    alert('Network error. Try again.');
  }
});

document.addEventListener('submit', (e) => {
  const form = e.target.closest('#signupForm');
  if(!form) return;

  const name = form.querySelector('input[name="name"]').value.trim();
  const email = form.querySelector('input[name="email"]').value.trim();
  const phone = form.querySelector('input[name="phone"]').value.trim();
  const address = form.querySelector('input[name="address"]').value.trim();
  const pass = form.querySelector('input[name="password"]').value;
  const confirm = form.querySelector('input[name="confirm_password"]').value;

  if(name.length < 2){ e.preventDefault(); alert('Please enter your full name.'); return; }
  if(!/^[^@]+@[^@]+\.[^@]+$/.test(email)){ e.preventDefault(); alert('Please enter a valid email.'); return; }
  if(!/^\+?[0-9\s-]{7,16}$/.test(phone)){ e.preventDefault(); alert('Please enter a valid phone number.'); return; }
  if(address.length < 5){ e.preventDefault(); alert('Please enter your address.'); return; }
  if(pass.length < 6){ e.preventDefault(); alert('Password must be at least 6 characters.'); return; }
  if(pass !== confirm){ e.preventDefault(); alert('Passwords do not match.'); return; }
});

(function(){
  function closeAll(){
    document.querySelectorAll('.dropdown.open').forEach(d => {
      d.classList.remove('open');
      const b = d.querySelector('.dropbtn');
      if(b) b.setAttribute('aria-expanded','false');
    });
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.dropbtn');
    const insideDropdown = e.target.closest('.dropdown');

    if(btn){
      const container = btn.closest('.dropdown');
      const willOpen = !container.classList.contains('open');

      document.querySelectorAll('.dropdown.open').forEach(d => {
        if(d !== container){
          d.classList.remove('open');
          const b = d.querySelector('.dropbtn');
          if(b) b.setAttribute('aria-expanded','false');
        }
      });

      container.classList.toggle('open', willOpen);
      btn.setAttribute('aria-expanded', String(willOpen));
      return;
    }

    if(!insideDropdown){
      closeAll();
    }
  });

  document.addEventListener('keydown', (e) => {
    if(e.key === 'Escape'){
      closeAll();
    }
  });

  document.addEventListener('click', (e) => {
    if(e.target.matches('.dropdown .dropdown-menu a')){
      closeAll();
    }
  });
})();

refreshCartCount();
