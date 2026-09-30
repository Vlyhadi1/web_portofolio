CREATE DATABASE IF NOT EXISTS vlyhady_store
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vlyhady_store;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(160) NOT NULL,
  category VARCHAR(60) NOT NULL,
  price INT UNSIGNED NOT NULL,
  rating DECIMAL(2,1) NULL DEFAULT NULL,
  reviews INT UNSIGNED NOT NULL DEFAULT 0,
  badge VARCHAR(60) NOT NULL DEFAULT '',
  image VARCHAR(120) NOT NULL DEFAULT '',
  alt VARCHAR(240) NOT NULL DEFAULT '',
  description TEXT NOT NULL,
  material VARCHAR(180) NOT NULL DEFAULT '',
  dimensions VARCHAR(120) NOT NULL DEFAULT '',
  weight_grams INT UNSIGNED NOT NULL DEFAULT 0,
  length_cm SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  width_cm SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  height_cm SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_demo TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_products_active_category (is_active, category)
) ENGINE=InnoDB;

ALTER TABLE products MODIFY image VARCHAR(512) NOT NULL DEFAULT '';

SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='weight_grams')=0, 'ALTER TABLE products ADD COLUMN weight_grams INT UNSIGNED NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='length_cm')=0, 'ALTER TABLE products ADD COLUMN length_cm SMALLINT UNSIGNED NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='width_cm')=0, 'ALTER TABLE products ADD COLUMN width_cm SMALLINT UNSIGNED NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='products' AND COLUMN_NAME='height_cm')=0, 'ALTER TABLE products ADD COLUMN height_cm SMALLINT UNSIGNED NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;

CREATE TABLE IF NOT EXISTS admin_users (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_username (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_code VARCHAR(32) NOT NULL,
  customer_name VARCHAR(160) NOT NULL,
  customer_phone VARCHAR(32) NOT NULL,
  customer_email VARCHAR(190) NOT NULL,
  shipping_address TEXT NOT NULL,
  city VARCHAR(120) NOT NULL,
  postal_code VARCHAR(16) NOT NULL,
  payment_method VARCHAR(80) NOT NULL,
  subtotal INT UNSIGNED NOT NULL,
  shipping_fee INT UNSIGNED NULL DEFAULT NULL,
  total_amount INT UNSIGNED NOT NULL DEFAULT 0,
  courier_code VARCHAR(50) NULL DEFAULT NULL,
  courier_service VARCHAR(120) NULL DEFAULT NULL,
  payment_status ENUM('manual', 'pending', 'paid', 'failed', 'expired') NOT NULL DEFAULT 'manual',
  midtrans_transaction_id VARCHAR(100) NULL DEFAULT NULL,
  midtrans_redirect_url VARCHAR(1000) NULL DEFAULT NULL,
  stock_released TINYINT(1) NOT NULL DEFAULT 0,
  sales_channel ENUM('web', 'pos') NOT NULL DEFAULT 'web',
  amount_received INT UNSIGNED NOT NULL DEFAULT 0,
  change_amount INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('demo', 'menunggu_pembayaran', 'menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan') NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_code (order_code),
  KEY idx_orders_created_at (created_at),
  KEY idx_orders_email (customer_email)
) ENGINE=InnoDB;

ALTER TABLE orders MODIFY status ENUM('demo', 'menunggu_pembayaran', 'menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan') NOT NULL;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='total_amount')=0, 'ALTER TABLE orders ADD COLUMN total_amount INT UNSIGNED NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='courier_code')=0, 'ALTER TABLE orders ADD COLUMN courier_code VARCHAR(50) NULL DEFAULT NULL', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='courier_service')=0, 'ALTER TABLE orders ADD COLUMN courier_service VARCHAR(120) NULL DEFAULT NULL', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='payment_status')=0, 'ALTER TABLE orders ADD COLUMN payment_status ENUM(''manual'', ''pending'', ''paid'', ''failed'', ''expired'') NOT NULL DEFAULT ''manual''', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='midtrans_transaction_id')=0, 'ALTER TABLE orders ADD COLUMN midtrans_transaction_id VARCHAR(100) NULL DEFAULT NULL', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='midtrans_redirect_url')=0, 'ALTER TABLE orders ADD COLUMN midtrans_redirect_url VARCHAR(1000) NULL DEFAULT NULL', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='stock_released')=0, 'ALTER TABLE orders ADD COLUMN stock_released TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='sales_channel')=0, 'ALTER TABLE orders ADD COLUMN sales_channel ENUM(''web'', ''pos'') NOT NULL DEFAULT ''web''', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='amount_received')=0, 'ALTER TABLE orders ADD COLUMN amount_received INT UNSIGNED NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;
SET @vlyhady_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders' AND COLUMN_NAME='change_amount')=0, 'ALTER TABLE orders ADD COLUMN change_amount INT UNSIGNED NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE vlyhady_stmt FROM @vlyhady_sql; EXECUTE vlyhady_stmt; DEALLOCATE PREPARE vlyhady_stmt;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  product_name VARCHAR(160) NOT NULL,
  quantity SMALLINT UNSIGNED NOT NULL,
  unit_price INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  KEY idx_order_items_order (order_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id),
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  status ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active',
  subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_newsletter_email (email)
) ENGINE=InnoDB;

INSERT INTO products (id, name, category, price, rating, reviews, badge, image, alt, description, material, dimensions, stock, is_active, is_demo)
VALUES
  (1, 'Lampu Meja Senja', 'Rumah', 685000, NULL, 0, 'Data contoh', 'photo-1507473885765-e6ed057f782c', 'Lampu meja bercahaya hangat di sudut ruang', 'Cahaya lembut untuk penghujung hari. Dibuat dari kayu solid dan kap kain linen dengan detail sederhana yang terasa akrab.', 'Kayu sungkai · linen', 'Tinggi 38 cm', 10, 1, 1),
  (2, 'Cangkir Pagi', 'Dapur', 185000, NULL, 0, 'Data contoh', 'photo-1514228742587-6b1558fcca3d', 'Cangkir keramik buatan tangan', 'Keramik stoneware dengan glasir yang setiap hasil bakarnya sedikit berbeda. Nyaman digenggam, pas untuk kopi pertama Anda.', 'Stoneware · glasir food-safe', 'Kapasitas 320 ml', 10, 1, 1),
  (3, 'Vas Bentuk Pagi', 'Rumah', 320000, NULL, 0, 'Data contoh', 'photo-1578500494198-246f612d3b3d', 'Vas keramik untuk bunga segar', 'Siluet organik yang tetap indah bahkan tanpa bunga. Dibentuk dan diglasir satu per satu oleh perajin studio kecil.', 'Keramik stoneware', 'Tinggi 21 cm', 10, 1, 1),
  (4, 'Sabun Pagi Hening', 'Ritual', 95000, NULL, 0, 'Data contoh', 'photo-1600857544200-b2f666a9a2ec', 'Sabun natural dengan kemasan minimal', 'Sabun batang dengan minyak nabati dan aroma serai yang segar. Dibuat dalam batch kecil dan dibungkus kertas daur ulang.', 'Minyak kelapa · minyak zaitun', 'Berat 100 g', 10, 1, 1),
  (5, 'Tote Kanvas Sehari', 'Aksesori', 245000, NULL, 0, 'Data contoh', 'photo-1590874103328-eac38a683ce7', 'Tas kanvas sederhana untuk kebutuhan harian', 'Kanvas katun tebal dengan saku dalam yang praktis. Dibuat di bengkel keluarga, cukup lapang untuk menemani rutinitas Anda.', 'Kanvas katun 14 oz', '38 × 34 cm', 10, 1, 1),
  (6, 'Nampan Sela', 'Dapur', 275000, NULL, 0, 'Data contoh', 'photo-1603199506016-b9a594b593c0', 'Nampan kayu untuk menyajikan minuman', 'Nampan kayu dengan tepi halus untuk menyajikan teh atau merapikan benda kecil di meja. Setiap serat kayu punya ceritanya sendiri.', 'Kayu mahoni', '30 × 20 cm', 10, 1, 1),
  (7, 'Linen Table Runner', 'Dapur', 390000, NULL, 0, 'Data contoh', 'photo-1600210492486-724fe5c67fb0', 'Kain linen natural di atas meja makan', 'Tenunan linen dengan tekstur yang makin lembut setelah dicuci. Membuat makan sederhana terasa sedikit lebih istimewa.', 'Linen', '180 × 40 cm', 10, 1, 1),
  (8, 'Minyak Esensial Teduh', 'Ritual', 165000, NULL, 0, 'Data contoh', 'photo-1608571423902-eed4a5ad8108', 'Botol minyak esensial dengan tanaman aromatik', 'Perpaduan lavender dan kayu cedar untuk menemani jeda di antara kesibukan. Diracik perlahan dari minyak esensial murni.', 'Lavender · cedarwood', '10 ml', 10, 1, 1)
ON DUPLICATE KEY UPDATE id = id;