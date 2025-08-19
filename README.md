# Pizza Shop - Website Bán Pizza

## Giới thiệu
Website bán pizza trực tuyến với đầy đủ chức năng quản lý sản phẩm, đơn hàng và khách hàng. Hệ thống được thiết kế để phục vụ cả khách hàng mua sắm và quản trị viên quản lý cửa hàng.

## Chức năng chính

### 1. Trang chủ (Home Page)
- **Giới thiệu tổng quan**: Thông tin về cửa hàng pizza
- **Sản phẩm nổi bật**: Hiển thị các pizza bestseller
- **Banner quảng cáo**: Khuyến mãi, sự kiện đặc biệt
- **Tin tức mới**: Cập nhật về menu mới, ưu đãi

### 2. Quản lý sản phẩm
- **Danh mục đa dạng**: 
  - Pizza Hải Sản
  - Pizza Chay  
  - Pizza Thịt
  - Pizza Phô Mai
  - Pizza Truyền Thống
  - Pizza Đặc Biệt
  - Nước Uống
  - Món Tráng Miệng
  - Món Ăn Kèm
  - Combo Khuyến Mãi

- **Chi tiết sản phẩm**: Tên, mô tả, hình ảnh, giá theo size và loại đế
- **Tùy chọn pizza**:
  - **Size**: Nhỏ (20cm), Vừa (25cm), Lớn (30cm), v.v.
  - **Loại đế**: Mỏng, dày, viền phô mai, nhân nhồi, v.v.
- **Tìm kiếm và lọc**: Theo tên, danh mục, giá, size
- **Đánh giá sản phẩm**: Rating 1-5 sao và bình luận

### 3. Giỏ hàng (Shopping Cart)
- **Thêm sản phẩm**: Chọn pizza với size và loại đế
- **Quản lý giỏ hàng**: Cập nhật số lượng, xóa sản phẩm
- **Tính toán tự động**: Tổng tiền đơn hàng
- **Hỗ trợ khách vãng lai**: Lưu giỏ hàng theo session

### 4. Thanh toán (Checkout)
- **Thông tin giao hàng**: Địa chỉ, số điện thoại
- **Phương thức thanh toán**:
  - Tiền mặt (COD)
  - Thẻ tín dụng
  - Chuyển khoản ngân hàng
  - PayPal
- **Áp dụng mã giảm giá**: Voucher theo % hoặc số tiền cố định
- **Xác nhận đơn hàng**: Email/SMS thông báo

### 5. Tài khoản người dùng
- **Đăng ký/Đăng nhập**: Hệ thống xác thực an toàn
- **Thông tin cá nhân**: Họ tên, email, địa chỉ, số điện thoại
- **Lịch sử đơn hàng**: Theo dõi trạng thái đơn hàng
- **Đổi mật khẩu**: Bảo mật tài khoản

### 6. Hệ thống quản trị (Admin)
- **Dashboard tổng quan**: Thống kê doanh thu, đơn hàng
- **Quản lý sản phẩm**:
  - Thêm/sửa/xóa pizza
  - Quản lý biến thể (size, đế, giá)
  - Upload hình ảnh
- **Quản lý đơn hàng**:
  - Trạng thái: Chờ xử lý → Xác nhận → Đang giao → Hoàn thành
  - In hóa đơn, phiếu giao hàng
- **Quản lý khách hàng**: Thông tin, lịch sử mua hàng
- **Quản lý danh mục**: Phân loại sản phẩm
- **Quản lý nội dung**:
  - Banner quảng cáo
  - Tin tức, khuyến mãi
  - FAQ

### 7. Tìm kiếm và lọc
- **Tìm kiếm thông minh**: Theo tên pizza, nguyên liệu
- **Bộ lọc nâng cao**:
  - Theo danh mục
  - Theo khoảng giá
  - Theo size pizza
  - Theo loại đế

### 8. Đánh giá và bình luận
- **Rating 5 sao**: Đánh giá chất lượng pizza
- **Bình luận chi tiết**: Chia sẻ trải nghiệm
- **Hiển thị trung bình**: Rating tổng hợp cho mỗi sản phẩm

### 9. Hỗ trợ khách hàng
- **Form liên hệ**: Gửi câu hỏi, góp ý
- **FAQ tự động**: Câu hỏi thường gặp
- **Thông tin liên hệ**: Hotline, email, địa chỉ cửa hàng

### 10. Khuyến mãi và mã giảm giá
- **Loại mã giảm giá**:
  - Giảm theo phần trăm (10%, 15%, 20%...)
  - Giảm số tiền cố định (20k, 50k...)
  - Miễn phí ship
- **Điều kiện áp dụng**: Đơn hàng tối thiểu
- **Thời gian hiệu lực**: Ngày hết hạn
- **Mã ví dụ**: PIZZA10, FREESHIP, SUMMER20, NEWUSER

## Cấu trúc cơ sở dữ liệu

### Bảng chính
- **users**: Tài khoản khách hàng và admin
- **products**: Danh sách pizza và món ăn
- **categories**: Phân loại sản phẩm
- **sizes**: Kích cỡ pizza (20cm, 25cm, 30cm...)
- **crusts**: Loại đế pizza (mỏng, dày, viền phô mai...)
- **product_variants**: Biến thể sản phẩm (size + crust + giá)
- **orders**: Đơn hàng
- **order_items**: Chi tiết sản phẩm trong đơn hàng
- **carts**: Giỏ hàng
- **cart_items**: Sản phẩm trong giỏ hàng
- **coupons**: Mã giảm giá
- **payments**: Thanh toán
- **reviews**: Đánh giá sản phẩm
- **banners**: Banner quảng cáo
- **news**: Tin tức
- **faq**: Câu hỏi thường gặp
- **contacts**: Tin nhắn liên hệ

### Dữ liệu mẫu
- **10 người dùng**: 8 khách hàng + 2 admin
- **10 danh mục sản phẩm**: Pizza các loại, đồ uống, tráng miệng...
- **10 sản phẩm pizza**: Từ cơ bản đến đặc biệt
- **10 mã giảm giá**: Đa dạng loại và mức giảm
- **Đầy đủ dữ liệu**: Đơn hàng, giỏ hàng, đánh giá, banner...

## Đặc điểm nổi bật

### 🍕 Chuyên về Pizza
- Hệ thống biến thể phức tạp: size × loại đế × giá
- Quản lý kho theo từng biến thể
- Tùy chọn đa dạng cho người dùng

### 👥 Hỗ trợ đa người dùng
- Khách hàng đăng ký/đăng nhập
- Khách vãng lai (guest checkout)
- Admin với quyền quản lý toàn bộ

### 🛒 Giỏ hàng thông minh
- Lưu trữ theo user_id hoặc session_id
- Persistent cart cho khách đăng nhập
- Session cart cho khách vãng lai

### 💰 Hệ thống khuyến mãi linh hoạt
- Giảm giá theo % hoặc số tiền
- Điều kiện đơn hàng tối thiểu
- Thời gian hiệu lực

### 📊 Quản lý đơn hàng chuyên nghiệp
- Workflow: Pending → Confirmed → Shipped → Delivered
- Nhiều phương thức thanh toán
- Tracking trạng thái realtime

## Công nghệ sử dụng
- **Database**: MySQL
- **Backend**: PHP/Node.js/Python (tùy chọn)
- **Frontend**: HTML/CSS/JavaScript hoặc React/Vue
- **Authentication**: Session-based hoặc JWT
- **Payment**: Integration với các gateway phổ biến

## Hướng dẫn cài đặt

### 1. Cài đặt cơ sở dữ liệu
```sql
-- Chạy file SQL đã cung cấp để tạo database và insert dữ liệu mẫu
mysql -u username -p < pizza_shop_database.sql
```

### 2. Cấu hình kết nối
```php
// config/database.php
$host = 'localhost';
$dbname = 'pizza_shop';
$username = 'your_username';
$password = 'your_password';
```

### 3. Cài đặt dependencies
```bash
# Nếu dùng Node.js
npm install

# Nếu dùng PHP
composer install
```

### 4. Chạy ứng dụng
```bash
# Node.js
npm start

# PHP
php -S localhost:8000
```

## API Endpoints (Ví dụ)

### Authentication
- `POST /api/login` - Đăng nhập
- `POST /api/register` - Đăng ký
- `POST /api/logout` - Đăng xuất

### Products
- `GET /api/products` - Danh sách sản phẩm
- `GET /api/products/:id` - Chi tiết sản phẩm
- `GET /api/categories` - Danh mục

### Cart
- `GET /api/cart` - Lấy giỏ hàng
- `POST /api/cart/add` - Thêm vào giỏ
- `PUT /api/cart/update` - Cập nhật giỏ hàng
- `DELETE /api/cart/remove` - Xóa khỏi giỏ

### Orders
- `POST /api/orders` - Tạo đơn hàng
- `GET /api/orders` - Lịch sử đơn hàng
- `GET /api/orders/:id` - Chi tiết đơn hàng

### Admin
- `GET /api/admin/dashboard` - Dashboard
- `GET /api/admin/orders` - Quản lý đơn hàng
- `POST /api/admin/products` - Thêm sản phẩm
- `PUT /api/admin/products/:id` - Cập nhật sản phẩm

## Bảo mật
- **Mã hóa mật khẩu**: bcrypt hoặc argon2
- **Xác thực**: JWT hoặc session-based
- **SQL Injection**: Prepared statements
- **XSS Protection**: Input sanitization
- **CSRF Protection**: CSRF tokens

## Tối ưu hóa
- **Database indexing**: Trên các cột tìm kiếm thường xuyên
- **Caching**: Redis cho session, query results
- **Image optimization**: Compress và resize ảnh
- **CDN**: Cho static assets
- **Pagination**: Cho danh sách sản phẩm, đơn hàng

## Hỗ trợ
- **Email**: support@pizzashop.com
- **Hotline**: 1900-xxxx
- **Documentation**: Xem thêm tại /docs
- **Issues**: Báo lỗi tại GitHub Issues

---

**Phiên bản**: 1.0.0  
**Cập nhật cuối**: Tháng 8, 2025  
**Tác giả**: Pizza Shop Development Team