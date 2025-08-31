
CREATE DATABASE IF NOT EXISTS pizza_shop;
USE pizza_shop;

-- Bảng users: Quản lý tài khoản người dùng và admin
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng categories: Danh mục sản phẩm
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng coupons: Mã giảm giá
CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_percentage DECIMAL(5, 2),
    discount_amount DECIMAL(10, 2),
    expiry_date DATE,
    min_order_amount DECIMAL(10, 2),
    max_discount_amount DECIMAL(10, 2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHECK (
        (discount_percentage IS NOT NULL AND discount_amount IS NULL)
        OR (discount_percentage IS NULL AND discount_amount IS NOT NULL)
    )
);

-- Bảng products: Sản phẩm pizza
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng sizes: Kích cỡ pizza
CREATE TABLE sizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    diameter DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng crusts: Loại đế pizza
CREATE TABLE crusts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng product_variants: Biến thể sản phẩm
CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size_id INT,
    crust_id INT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (size_id) REFERENCES sizes(id) ON DELETE RESTRICT,
    FOREIGN KEY (crust_id) REFERENCES crusts(id) ON DELETE RESTRICT
);

-- Trigger để kiểm tra size_id và crust_id trong product_variants
DELIMITER //
CREATE TRIGGER before_product_variants_insert
BEFORE INSERT ON product_variants
FOR EACH ROW
BEGIN
    DECLARE product_category INT;
    SELECT category_id INTO product_category FROM products WHERE id = NEW.product_id;
    
    IF product_category IN (1, 2, 3, 4, 5, 6) THEN
        IF NEW.size_id IS NULL OR NEW.crust_id IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Pizza products must have size_id and crust_id';
        END IF;
    ELSEIF product_category IN (7, 8, 9, 10) THEN
        IF NEW.size_id IS NOT NULL OR NEW.crust_id IS NOT NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Non-pizza products must not have size_id or crust_id';
        END IF;
    END IF;
END//

CREATE TRIGGER before_product_variants_update
BEFORE UPDATE ON product_variants
FOR EACH ROW
BEGIN
    DECLARE product_category INT;
    SELECT category_id INTO product_category FROM products WHERE id = NEW.product_id;
    
    IF product_category IN (1, 2, 3, 4, 5, 6) THEN
        IF NEW.size_id IS NULL OR NEW.crust_id IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Pizza products must have size_id and crust_id';
        END IF;
    ELSEIF product_category IN (7, 8, 9, 10) THEN
        IF NEW.size_id IS NOT NULL OR NEW.crust_id IS NOT NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Non-pizza products must not have size_id or crust_id';
        END IF;
    END IF;
END//
DELIMITER ;

-- Bảng combos: Combo khuyến mãi
CREATE TABLE combos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255),
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng combo_items: Chi tiết sản phẩm trong combo
CREATE TABLE combo_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    combo_id INT NOT NULL,
    product_variant_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (combo_id) REFERENCES combos(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
);

-- Bảng orders: Đơn hàng
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) DEFAULT 0,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    coupon_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE SET NULL
);

-- Bảng order_items: Chi tiết sản phẩm trong đơn hàng
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_variant_id INT,
    combo_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (combo_id) REFERENCES combos(id) ON DELETE SET NULL
);

-- Trigger để kiểm tra product_variant_id và combo_id trong order_items
DELIMITER //
CREATE TRIGGER before_order_items_insert
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
    IF (NEW.product_variant_id IS NOT NULL AND NEW.combo_id IS NOT NULL) OR
       (NEW.product_variant_id IS NULL AND NEW.combo_id IS NULL) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'order_items must have either product_variant_id or combo_id, but not both or none';
    END IF;
END//

CREATE TRIGGER before_order_items_update
BEFORE UPDATE ON order_items
FOR EACH ROW
BEGIN
    IF (NEW.product_variant_id IS NOT NULL AND NEW.combo_id IS NOT NULL) OR
       (NEW.product_variant_id IS NULL AND NEW.combo_id IS NULL) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'order_items must have either product_variant_id or combo_id, but not both or none';
    END IF;
END//
DELIMITER ;

-- Trigger để cập nhật total_amount trong orders
DELIMITER //
CREATE TRIGGER update_order_total
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders
    SET total_amount = (
        SELECT COALESCE(SUM(quantity * price), 0)
        FROM order_items
        WHERE order_id = NEW.order_id
    )
    WHERE id = NEW.order_id;
END//

CREATE TRIGGER update_order_total_after_update
AFTER UPDATE ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders
    SET total_amount = (
        SELECT COALESCE(SUM(quantity * price), 0)
        FROM order_items
        WHERE order_id = NEW.order_id
    )
    WHERE id = NEW.order_id;
END//

CREATE TRIGGER update_order_total_after_delete
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
    UPDATE orders
    SET total_amount = (
        SELECT COALESCE(SUM(quantity * price), 0)
        FROM order_items
        WHERE order_id = OLD.order_id
    )
    WHERE id = OLD.order_id;
END//
DELIMITER ;

-- Bảng carts: Giỏ hàng
CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng cart_items: Chi tiết sản phẩm trong giỏ hàng
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_variant_id INT,
    combo_id INT,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    FOREIGN KEY (combo_id) REFERENCES combos(id) ON DELETE SET NULL
);

-- Trigger để kiểm tra product_variant_id và combo_id trong cart_items
DELIMITER //
CREATE TRIGGER before_cart_items_insert
BEFORE INSERT ON cart_items
FOR EACH ROW
BEGIN
    IF (NEW.product_variant_id IS NOT NULL AND NEW.combo_id IS NOT NULL) OR
       (NEW.product_variant_id IS NULL AND NEW.combo_id IS NULL) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'cart_items must have either product_variant_id or combo_id, but not both or none';
    END IF;
END//

CREATE TRIGGER before_cart_items_update
BEFORE UPDATE ON cart_items
FOR EACH ROW
BEGIN
    IF (NEW.product_variant_id IS NOT NULL AND NEW.combo_id IS NOT NULL) OR
       (NEW.product_variant_id IS NULL AND NEW.combo_id IS NULL) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'cart_items must have either product_variant_id or combo_id, but not both or none';
    END IF;
END//
DELIMITER ;

-- Bảng payments: Thanh toán
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    method ENUM('cash', 'credit_card', 'bank_transfer', 'paypal') DEFAULT 'cash',
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Trigger để kiểm tra trạng thái completed trong payments
DELIMITER //
CREATE TRIGGER before_payments_insert
BEFORE INSERT ON payments
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' THEN
        IF EXISTS (
            SELECT 1
            FROM payments
            WHERE order_id = NEW.order_id AND status = 'completed'
        ) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Each order can have only one completed payment';
        END IF;
    END IF;
END//

CREATE TRIGGER before_payments_update
BEFORE UPDATE ON payments
FOR EACH ROW
BEGIN
    IF NEW.status = 'completed' THEN
        IF EXISTS (
            SELECT 1
            FROM payments
            WHERE order_id = NEW.order_id AND status = 'completed' AND id != NEW.id
        ) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Each order can have only one completed payment';
        END IF;
    END IF;
END//
DELIMITER ;

-- Bảng reviews: Đánh giá sản phẩm hoặc combo
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    combo_id INT,
    user_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (combo_id) REFERENCES combos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CHECK (
        (product_id IS NOT NULL AND combo_id IS NULL)
        OR (product_id IS NULL AND combo_id IS NOT NULL)
    ),
    UNIQUE (user_id, product_id),
    UNIQUE (user_id, combo_id)
);

-- Bảng banners: Banner quảng cáo
CREATE TABLE banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    position ENUM('homepage_top', 'homepage_bottom', 'product_page'),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng news: Tin tức
CREATE TABLE news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng faq: Câu hỏi thường gặp
CREATE TABLE faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng contacts: Form liên hệ
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100),
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Trigger để kiểm tra logic user_id, name, email trong contacts
DELIMITER //
CREATE TRIGGER before_contacts_insert
BEFORE INSERT ON contacts
FOR EACH ROW
BEGIN
    DECLARE user_email VARCHAR(100);
    IF NEW.user_id IS NOT NULL THEN
        SELECT email INTO user_email FROM users WHERE id = NEW.user_id;
        IF NEW.email != user_email THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Email must match the user email when user_id is provided';
        END IF;
        SET NEW.name = (SELECT full_name FROM users WHERE id = NEW.user_id);
    ELSE
        IF NEW.name IS NULL OR NEW.email IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Name and email are required when user_id is NULL';
        END IF;
    END IF;
END//

CREATE TRIGGER before_contacts_update
BEFORE UPDATE ON contacts
FOR EACH ROW
BEGIN
    DECLARE user_email VARCHAR(100);
    IF NEW.user_id IS NOT NULL THEN
        SELECT email INTO user_email FROM users WHERE id = NEW.user_id;
        IF NEW.email != user_email THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Email must match the user email when user_id is provided';
        END IF;
        SET NEW.name = (SELECT full_name FROM users WHERE id = NEW.user_id);
    ELSE
        IF NEW.name IS NULL OR NEW.email IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Name and email are required when user_id is NULL';
        END IF;
    END IF;
END//
DELIMITER ;

-- Thêm các chỉ mục để tối ưu hóa truy vấn
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_cart_items_cart_id ON cart_items(cart_id);
CREATE INDEX idx_payments_order_id ON payments(order_id);
CREATE INDEX idx_reviews_user_id ON reviews(user_id);
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_combo_items_combo_id ON combo_items(combo_id);
CREATE INDEX idx_cart_items_product_variant_id ON cart_items(product_variant_id);
CREATE INDEX idx_order_items_product_variant_id ON order_items(product_variant_id);

-- Dữ liệu mẫu cho bảng users
INSERT INTO users (username, password, email, full_name, address, phone, role) VALUES
('admin1', '$2b$12$hash1...', 'admin1@example.com', 'Nguyễn Văn Admin', '123 Đường Lê Lợi, Quận 1, TP.HCM', '0901234567', 'admin'),
('admin2', '$2b$12$hash2...', 'admin2@example.com', 'Trần Thị Quản Lý', '456 Đường Nguyễn Huệ, Quận 1, TP.HCM', '0902345678', 'admin'),
('khachhang1', '$2b$12$hash3...', 'khach1@example.com', 'Lê Văn A', '789 Đường Võ Văn Tần, Quận 3', '0913456789', 'customer'),
('khachhang2', '$2b$12$hash4...', 'khach2@example.com', 'Phạm Thị B', '101 Đường Nguyễn Trãi, Quận 5', '0924567890', 'customer'),
('khachhang3', '$2b$12$hash5...', 'khach3@example.com', 'Hoàng Văn C', '202 Đường Cách Mạng Tháng 8', '0935678901', 'customer'),
('khachhang4', '$2b$12$hash6...', 'khach4@example.com', 'Nguyễn Thị D', '303 Đường Điện Biên Phủ', '0946789012', 'customer'),
('khachhang5', '$2b$12$hash7...', 'khach5@example.com', 'Trần Văn E', '404 Đường Hai Bà Trưng', '0957890123', 'customer'),
('khachhang6', '$2b$12$hash8...', 'khach6@example.com', 'Lê Thị F', '505 Đường Lê Đại Hành', '0968901234', 'customer'),
('khachhang7', '$2b$12$hash9...', 'khach7@example.com', 'Phạm Văn G', '606 Đường Trường Chinh', '0979012345', 'customer'),
('khachhang8', '$2b$12$hash10...', 'khach8@example.com', 'Hoàng Thị H', '707 Đường Nguyễn Văn Cừ', '0980123456', 'customer');

-- Dữ liệu mẫu cho bảng categories
INSERT INTO categories (name, description) VALUES
('Pizza Hải Sản', 'Pizza với các loại hải sản tươi ngon'),
('Pizza Chay', 'Pizza dành cho người ăn chay'),
('Pizza Thịt', 'Pizza với các loại thịt phong phú'),
('Pizza Phô Mai', 'Pizza đậm vị phô mai'),
('Pizza Truyền Thống', 'Pizza theo công thức cổ điển'),
('Pizza Đặc Biệt', 'Pizza sáng tạo độc quyền'),
('Nước Uống', 'Các loại nước giải khát'),
('Món Tráng Miệng', 'Bánh ngọt và kem'),
('Món Ăn Kèm', 'Khoai tây chiên, gà chiên, salad'),
('Combo Khuyến Mãi', 'Combo giá ưu đãi');

-- Dữ liệu mẫu cho bảng coupons
INSERT INTO coupons (code, discount_percentage, discount_amount, expiry_date, min_order_amount, max_discount_amount) VALUES
('PIZZA10', 10.00, NULL, '2025-12-31', 200000, 50000),
('FREESHIP', NULL, 30000, '2025-11-30', 150000, NULL),
('SUMMER20', 20.00, NULL, '2025-09-30', 300000, 100000),
('NEWUSER', NULL, 50000, '2026-01-01', 100000, NULL),
('VIP15', 15.00, NULL, '2025-12-15', 250000, 75000),
('COMBO50', NULL, 50000, '2025-10-31', 400000, NULL),
('WEEKEND', 10.00, NULL, '2025-09-30', 200000, 60000),
('PIZZADAY', NULL, 20000, '2025-11-01', 150000, NULL),
('LOYALTY', 5.00, NULL, '2026-06-30', 100000, 30000),
('FLASH25', 25.00, NULL, '2025-08-31', 500000, 150000);

-- Dữ liệu mẫu cho bảng products
INSERT INTO products (name, description, image_url, category_id) VALUES
('Pizza Hải Sản Deluxe', 'Tôm, mực, cá, phô mai, sốt cà chua', '/images/pizza-seafood.webp', 1),
('Pizza Rau Củ Chay', 'Nấm, ớt chuông, cà chua, phô mai chay', '/images/pizza-veggie.webp', 2),
('Pizza BBQ Gà', 'Gà nướng, sốt BBQ, phô mai mozzarella', '/images/pizza-bbq.webp', 3),
('Pizza 4 Phô Mai', 'Hỗn hợp 4 loại phô mai cao cấp', '/images/pizza-4cheese.webp', 4),
('Pizza Margherita', 'Sốt cà chua, phô mai, lá húng quế', '/images/pizza-margherita.webp', 5),
('Pizza Đặc Biệt Nhà Hàng', 'Hải sản, thịt, rau củ, phô mai', '/images/pizza-special.webp', 6),
('Nước Coca-Cola', 'Coca-Cola lon 330ml', '/images/coca-cola.webp', 7),
('Kem Tiramisu', 'Kem tiramisu thơm ngon', '/images/tiramisu.webp', 8),
('Khoai Tây Chiên', 'Khoai tây chiên giòn', '/images/fries.webp', 9),
('Combo Gia Đình', '2 pizza lớn, 4 nước ngọt', '/images/combo-family.webp', 10);

-- Dữ liệu mẫu cho bảng sizes
INSERT INTO sizes (name, diameter) VALUES
('Nhỏ', 20.00),
('Vừa', 25.00),
('Lớn', 30.00),
('Siêu Nhỏ', 15.00),
('Cỡ Trung', 22.50),
('Cỡ Đại', 35.00),
('Mini', 12.00),
('Gia Đình', 40.00),
('Cỡ Nhỏ Đặc Biệt', 18.00),
('Cỡ Lớn Đặc Biệt', 32.00);

-- Dữ liệu mẫu cho bảng crusts
INSERT INTO crusts (name, description) VALUES
('Đế Mỏng', 'Giòn rụm, nướng vàng đều'),
('Đế Dày', 'Mềm xốp, đậm vị'),
('Viền Phô Mai', 'Đế dày với viền nhồi phô mai'),
('Đế Nhân Nhồi', 'Đế nhồi xúc xích và phô mai'),
('Đế Giòn', 'Siêu giòn, nhẹ'),
('Đế Nguyên Cám', 'Làm từ bột nguyên cám, tốt cho sức khỏe'),
('Đế Không Gluten', 'Phù hợp cho người dị ứng gluten'),
('Đế Hành Lá', 'Đế mỏng với hành lá thơm'),
('Đế Tiêu Đen', 'Đế giòn với tiêu đen rắc'),
('Đế Thảo Mộc', 'Đế mỏng với thảo mộc Ý');

-- Dữ liệu mẫu cho bảng product_variants
INSERT INTO product_variants (product_id, size_id, crust_id, price, stock) VALUES
(1, 1, 1, 150000, 100),
(1, 2, 1, 200000, 80),
(1, 3, 3, 250000, 50),
(2, 1, 2, 120000, 90),
(2, 2, 6, 160000, 70),
(3, 3, 1, 220000, 60),
(4, 2, 3, 180000, 85),
(5, 1, 5, 100000, 100),
(6, 3, 4, 300000, 40),
(7, NULL, NULL, 25000, 200),
(9, NULL, NULL, 40000, 200);

-- Dữ liệu mẫu cho bảng combos
INSERT INTO combos (name, description, price, image_url, start_date, end_date) VALUES
('Combo Cặp Đôi', '1 Pizza BBQ Gà cỡ lớn, 1 Khoai tây chiên và 2 Nước Coca-Cola. Tiết kiệm hơn khi mua lẻ.', 259000, '/images/combo-couple.jpg', '2025-08-01', '2025-12-31'),
('Combo Gia Đình', '2 Pizza lớn bất kỳ, 4 Nước Coca-Cola.', 450000, '/images/combo-family.jpg', '2025-08-01', '2025-12-31');

-- Dữ liệu mẫu cho bảng combo_items
INSERT INTO combo_items (combo_id, product_variant_id, quantity) VALUES
(1, 6, 1),
(1, 11, 1),
(1, 10, 2),
(2, 3, 1),
(2, 6, 1),
(2, 10, 4);

-- Dữ liệu mẫu cho bảng carts
INSERT INTO carts (user_id) VALUES
(3),
(4),
(5),
(6),
(7),
(8),
(9), 
(10);

-- Dữ liệu mẫu cho bảng cart_items
INSERT INTO cart_items (cart_id, product_variant_id, combo_id, quantity) VALUES
(1, 1, NULL, 2),
(1, 7, NULL, 1),
(1, NULL, 1, 1),
(2, 2, NULL, 1),
(3, 3, NULL, 3),
(3, NULL, 2, 2),
(4, 4, NULL, 2),
(5, 5, NULL, 1),
(6, 6, NULL, 2),
(7, 8, NULL, 1),
(8, 9, NULL, 2); 

-- Dữ liệu mẫu cho bảng orders
INSERT INTO orders (user_id, total_amount, status, shipping_address, coupon_id) VALUES
(3, 350000, 'pending', '789 Đường Võ Văn Tần, Quận 3', 1),
(4, 200000, 'confirmed', '101 Đường Nguyễn Trãi, Quận 5', NULL),
(5, 660000, 'shipped', '202 Đường Cách Mạng Tháng 8', 2),
(6, 240000, 'delivered', '303 Đường Điện Biên Phủ', 3),
(7, 100000, 'pending', '404 Đường Hai Bà Trưng', NULL),
(8, 320000, 'confirmed', '505 Đường Lê Đại Hành', 4),
(9, 600000, 'shipped', '606 Đường Trường Chinh', 5),
(10, 200000, 'delivered', '707 Đường Nguyễn Văn Cừ', NULL),
(3, 250000, 'cancelled', '789 Đường Võ Văn Tần, Quận 3', 6),
(4, 259000, 'pending', '101 Đường Nguyễn Trãi, Quận 5', 7);

-- Dữ liệu mẫu cho bảng order_items
INSERT INTO order_items (order_id, product_variant_id, combo_id, quantity, price) VALUES
(1, 1, NULL, 2, 150000),
(1, 10, NULL, 2, 25000),
(2, 2, NULL, 1, 200000),
(3, 3, NULL, 3, 220000),
(4, 4, NULL, 2, 120000),
(5, 5, NULL, 1, 100000),
(6, 6, NULL, 2, 160000),
(7, 9, NULL, 2, 300000),
(8, 8, NULL, 2, 100000),
(9, 7, NULL, 1, 180000),
(10, NULL, 1, 1, 259000);

-- Dữ liệu mẫu cho bảng payments
INSERT INTO payments (order_id, amount, method, status, transaction_id) VALUES
(1, 350000, 'cash', 'pending', NULL),
(2, 200000, 'credit_card', 'completed', 'TXN12345'),
(3, 660000, 'paypal', 'completed', 'PAYID67890'),
(4, 240000, 'bank_transfer', 'completed', 'BANK11223'),
(5, 100000, 'cash', 'pending', NULL),
(6, 320000, 'credit_card', 'completed', 'TXN44556'),
(7, 600000, 'paypal', 'pending', 'PAYID78901'),
(8, 200000, 'cash', 'completed', NULL),
(9, 250000, 'bank_transfer', 'failed', 'BANK22334'),
(10, 259000, 'credit_card', 'completed', 'TXN55667');

-- Dữ liệu mẫu cho bảng reviews
INSERT INTO reviews (product_id, combo_id, user_id, rating, comment) VALUES
(1, NULL, 3, 5, 'Pizza hải sản rất ngon, tôm tươi!'),
(1, NULL, 4, 4, 'Đế mỏng giòn, nhưng hơi ít phô mai.'),
(2, NULL, 5, 3, 'Pizza chay ổn, nhưng cần thêm gia vị.'),
(3, NULL, 6, 5, 'Gà BBQ tuyệt vời, sốt đậm đà!'),
(4, NULL, 7, 4, 'Phô mai béo ngậy, rất đáng thử.'),
(5, NULL, 8, 5, 'Margherita đơn giản mà ngon.'),
(6, NULL, 9, 4, 'Pizza đặc biệt hơi đắt nhưng chất lượng.'),
(7, NULL, 10, 3, 'Coca lạnh, giao nhanh.'),
(8, NULL, 3, 5, 'Kem tiramisu ngọt vừa, rất thích!'),
(NULL, 1, 4, 4, 'Combo Cặp Đôi rất đáng giá, đủ cho 2 người!');

-- Dữ liệu mẫu cho bảng banners
INSERT INTO banners (image_url, link, position, active) VALUES
('/images/banner1.jpg', '/promotions', 'homepage_top', TRUE),
('/images/banner2.jpg', '/new-pizza', 'homepage_bottom', TRUE),
('/images/banner3.jpg', '/combo', 'product_page', TRUE),
('/images/banner4.jpg', '/summer-sale', 'homepage_top', TRUE),
('/images/banner5.jpg', '/pizza-day', 'homepage_bottom', FALSE),
('/images/banner6.jpg', '/new-drinks', 'product_page', TRUE),
('/images/banner7.jpg', '/family-combo', 'homepage_top', TRUE),
('/images/banner8.jpg', '/veggie-pizza', 'homepage_bottom', TRUE),
('/images/banner9.jpg', '/special-offer', 'product_page', FALSE),
('/images/banner10.jpg', '/loyalty-program', 'homepage_top', TRUE);

-- Dữ liệu mẫu cho bảng news
INSERT INTO news (title, content, image_url) VALUES
('Khuyến mãi 50% cuối tuần', 'Giảm 50% cho tất cả pizza vào thứ 7, CN.', '/images/news1.jpg'),
('Pizza mới ra mắt', 'Thử ngay pizza đặc biệt với công thức mới!', '/images/news2.jpg'),
('Miễn phí giao hàng', 'Đơn từ 200k được miễn phí giao hàng.', '/images/news3.jpg'),
('Combo gia đình giá sốc', 'Combo 2 pizza lớn + 4 nước chỉ 400k.', '/images/news4.jpg'),
('Chương trình khách hàng thân thiết', 'Tích điểm đổi quà với mỗi đơn hàng.', '/images/news5.jpg'),
('Pizza chay mới', 'Thêm nhiều lựa chọn cho người ăn chay.', '/images/news6.jpg'),
('Sự kiện Pizza Day', 'Ngày hội pizza với nhiều ưu đãi.', '/images/news7.jpg'),
('Nước uống mới', 'Ra mắt dòng nước trái cây tươi mát.', '/images/news8.jpg'),
('Tiramisu phiên bản đặc biệt', 'Kem tiramisu với hương vị mới.', '/images/news9.jpg'),
('Cửa hàng mới khai trương', 'Chi nhánh mới tại Quận 7, TP.HCM.', '/images/news10.jpg');

-- Dữ liệu mẫu cho bảng faq
INSERT INTO faq (question, answer) VALUES
('Giao hàng mất bao lâu?', 'Giao hàng trong 30-45 phút tùy khu vực.'),
('Có pizza chay không?', 'Có, chúng tôi có danh mục pizza chay đa dạng.'),
('Làm sao để áp dụng mã giảm giá?', 'Nhập mã tại bước thanh toán trên website.'),
('Có giao hàng tận nơi không?', 'Có, chúng tôi giao hàng toàn TP.HCM.'),
('Phương thức thanh toán nào được chấp nhận?', 'Tiền mặt, thẻ tín dụng, chuyển khoản, PayPal.'),
('Làm sao để đổi mật khẩu?', 'Vào phần tài khoản cá nhân để đổi mật khẩu.'),
('Pizza có tùy chỉnh topping không?', 'Hiện tại chúng tôi chưa hỗ trợ tùy chỉnh topping.'),
('Có chương trình khách hàng thân thiết không?', 'Có, tích điểm với mỗi đơn hàng.'),
('Làm sao để liên hệ hỗ trợ?', 'Gửi form liên hệ hoặc gọi hotline.'),
('Có giao hàng ngoài TP.HCM không?', 'Hiện chỉ giao trong TP.HCM.');

-- Dữ liệu mẫu cho bảng contacts
INSERT INTO contacts (user_id, name, email, message) VALUES
(3, 'Lê Văn A', 'khach1@example.com', 'Tôi muốn hỏi về combo gia đình.'),
(NULL, 'Nguyễn Văn X', 'guest1@example.com', 'Giao hàng có nhanh không?'),
(4, 'Phạm Thị B', 'khach2@example.com', 'Pizza chay có tùy chỉnh được không?'),
(NULL, 'Trần Thị Y', 'guest2@example.com', 'Mã giảm giá PIZZA10 còn dùng được không?'),
(5, 'Hoàng Văn C', 'khach3@example.com', 'Yêu cầu thêm phô mai có tốn phí không?'),
(6, 'Nguyễn Thị D', 'khach4@example.com', 'Giao hàng đến Quận 7 bao lâu?'),
(NULL, 'Lê Văn Z', 'guest3@example.com', 'Cửa hàng có mở tối muộn không?'),
(7, 'Trần Văn E', 'khach5@example.com', 'Tôi muốn đặt tiệc sinh nhật.'),
(8, 'Lê Thị F', 'khach6@example.com', 'Có chương trình khuyến mãi nào mới không?'),
(NULL, 'Phạm Thị W', 'guest4@example.com', 'Liên hệ để hợp tác kinh doanh.');
