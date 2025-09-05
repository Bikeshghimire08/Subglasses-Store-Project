<?php
session_start();
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/admin_config.php';

function is_admin(){ return !empty($_SESSION['is_admin']); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin • SunStore</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    .admin-head{ display:flex; justify-content:space-between; align-items:center; gap:1rem; }
    .muted{ color:#6b7280; font-size:.95rem; }
    .row{ display:grid; grid-template-columns: 1fr 1fr; gap:1rem; }
    @media (max-width: 920px){ .row{ grid-template-columns: 1fr; } }
    .thumb{ width:80px; height:56px; object-fit:cover; border-radius:10px; border:1px solid rgba(0,0,0,.08); }

    .inline-form{ display:inline-flex; flex-wrap:wrap; gap:.5rem; align-items:center; }
    .inline-form input[type="text"], .inline-form input[type="number"], .inline-form select{
      padding:.5rem .6rem; border-radius:10px; border:1px solid rgba(0,0,0,.18);
    }


    .actions-col{ min-width: 360px; }
    .actions-cell{ display:flex; align-items:center; gap:.6rem; white-space: nowrap; }
    .actions-cell input[type="file"]{ max-width: 230px; }

    @media (max-width: 900px){
      .actions-col{ min-width: 300px; }
      .actions-cell{ flex-wrap: wrap; }
      .actions-cell input[type="file"]{ max-width: 100%; }
    }

    .danger{ background:#ef4444; color:#fff; border:none; padding:.5rem .7rem; border-radius:10px; cursor:pointer; }
    .secondary{ background:#111; color:#fff; border:none; padding:.5rem .8rem; border-radius:10px; cursor:pointer; }
    .tiny{ font-size:.85rem; }
    .note{ background:#fff8e1; border:1px solid #f59e0b40; padding:.6rem .8rem; border-radius:10px; }
  </style>
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="brand" href="home.php">SunStore</a>
    <nav class="site-nav" aria-label="Primary">
      <a href="home.php">Store</a>
      <?php if(is_admin()): ?>
        <a href="php/admin_logout.php" class="logout-link">Logout</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="page-content">
  <section class="container section">
    <?php if(!is_admin()): ?>
      <div class="form-card" style="max-width:460px;">
        <h2>Admin sign-in</h2>
        <?php if(isset($_GET['error']) && $_GET['error']==='no'): ?>
          <p style="color:#B91C1C;">Invalid password.</p>
        <?php endif; ?>
        <form action="php/admin_login.php" method="post">
          <label for="pass">Password</label>
          <input id="pass" name="pass" type="password" placeholder="Enter admin password" required>
          <div class="form-actions">
            <button class="add-btn btn-wide" type="submit">Enter</button>
          </div>
        </form>
      </div>

    <?php else: ?>
      <div class="admin-head">
        <h2>Admin Dashboard</h2>
        <div class="muted">Logged in • <a href="php/admin_logout.php">Logout</a></div>
      </div>

      <div class="row">
        <div class="form-card">
          <h3>Add new product</h3>
          <?php if(isset($_GET['added']) && $_GET['added']==='1'): ?>
            <p style="color:#065f46;">✅ Product added.</p>
          <?php elseif(isset($_GET['added']) && $_GET['added']==='0'): ?>
            <p style="color:#B91C1C;">❌ Failed to add product.</p>
          <?php endif; ?>
          <form action="php/admin_add_product.php" method="post" enctype="multipart/form-data">
            <label>Name</label>
            <input type="text" name="name" placeholder="e.g. Aviator Pro" required>

            <label>Description</label>
            <input type="text" name="description" placeholder="Short product description" required>

            <label>Price (रु)</label>
            <input type="number" name="price" min="0" step="1" placeholder="e.g. 4999" required>

            <label>Category</label>
            <select name="category" required>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="unisex">Unisex</option>
            </select>

            <label>Image</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">

            <div class="form-actions">
              <button class="add-btn" type="submit">Add product</button>
            </div>
          </form>
        </div>

        <div>
          <div class="form-card" style="overflow:auto;">
            <h3>Products</h3>
            <?php
              $st = $pdo->query('SELECT * FROM products ORDER BY created_at DESC');
              $rows = $st->fetchAll();
              if(!$rows){ echo '<p class="muted">No products yet.</p>'; }
            ?>
            <?php if($rows): ?>
              <table class="table">
                <thead>
                  <tr>
                    <th>Image</th>
                    <th>Name / Description</th>
                    <th width="110">Price (रु)</th>
                    <th width="120">Category</th>
                    <th class="actions-col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach($rows as $p): ?>
                  <tr>
                    <td>
                      <img class="thumb" src="<?php echo htmlspecialchars($p['image_url']); ?>" onerror="this.src='assets/placeholder.svg'">
                    </td>
                    <td>
                      <form class="inline-form" action="php/admin_update_product.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                        <input type="text" name="name" value="<?php echo htmlspecialchars($p['name']); ?>" placeholder="Name" required style="min-width:220px;">
                        <input type="text" name="description" value="<?php echo htmlspecialchars($p['description']); ?>" placeholder="Description" required style="min-width:260px;">
                    </td>
                    <td>
                        <input type="number" name="price" min="0" step="1" value="<?php echo (int)$p['price']; ?>" required style="width:100px;">
                    </td>
                    <td>
                        <select name="category">
                          <option value="male"   <?php if($p['category']==='male')   echo 'selected'; ?>>Male</option>
                          <option value="female" <?php if($p['category']==='female') echo 'selected'; ?>>Female</option>
                          <option value="unisex" <?php if($p['category']==='unisex') echo 'selected'; ?>>Unisex</option>
                        </select>
                    </td>
                    <td class="actions-cell">
                        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" class="tiny">
                        <button class="secondary tiny" type="submit">Save</button>
                      </form>
                      <form style="display:inline-block" action="php/admin_delete_product.php" method="post" onsubmit="return confirm('Delete this product?');">
                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                        <button class="danger tiny" type="submit">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include __DIR__ . '/footer.php'; ?>
</body>
</html>
