-- Create database and switch to it
DROP DATABASE IF EXISTS sunglasses_store;
CREATE DATABASE sunglasses_store
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sunglasses_store;

-- USERS TABLE
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PRODUCTS TABLE
CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  category ENUM('male','female','unisex') NOT NULL,
  image_url VARCHAR(400) DEFAULT 'assets/placeholder.svg',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CART TABLE
CREATE TABLE cart (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_product (user_id, product_id),
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SAMPLE DATA
INSERT INTO products (name, description, price, category, image_url) VALUES
('SunBlaze Classic', 'Polarized UV400 lenses, lightweight frame.', 39.99, 'male', 'assets/placeholder.svg'),
('Coastal Cruise', 'Matte black frame, ocean-tint lenses.', 49.99, 'male', 'assets/placeholder.svg'),
('Palm Bloom', 'Retro round, floral vibe for summer.', 34.50, 'female', 'assets/placeholder.svg'),
('SunKiss Cat-Eye', 'Gloss finish, UV protection.', 44.00, 'female', 'assets/placeholder.svg'),
('WaveRider', 'All-day comfort, anti-glare.', 41.75, 'unisex', 'assets/placeholder.svg'),
('Citrus Pop', 'Bold color accents, UV400.', 29.99, 'unisex', 'assets/placeholder.svg');
