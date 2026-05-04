# 🍕 Pizza Shop - Website Bán Pizza Online

<div align="center">

![Pizza Shop Logo](https://img.shields.io/badge/🍕-Pizza%20Shop-orange?style=for-the-badge&logo=pizza&logoColor=white)

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg?style=flat-square)](https://github.com/pizzashop/releases)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg?style=flat-square)](https://github.com/pizzashop/actions)
[![Contributors](https://img.shields.io/badge/contributors-3-yellow.svg?style=flat-square)](https://github.com/pizzashop/contributors)

**🚀 Website bán pizza trực tuyến hiện đại với đầy đủ chức năng quản lý và thanh toán**

[🌟 Demo Live](https://demo.pizzashop.com) • [📖 Documentation](https://docs.pizzashop.com) • [🐛 Report Bug](https://github.com/pizzashop/issues) • [💡 Request Feature](https://github.com/pizzashop/issues)

</div>

---

## 📋 Table of Contents

-   [🌟 Giới thiệu](#-giới-thiệu)
-   [✨ Tính năng nổi bật](#-tính-năng-nổi-bật)
-   [🖼️ Giao diện người dùng](#️-giao-diện-người-dùng)
-   [🚀 Chức năng chính](#-chức-năng-chính)
-   [🏗️ Cấu trúc Database](#️-cấu-trúc-database)
-   [💻 Công nghệ sử dụng](#-công-nghệ-sử-dụng)
-   [⚡ Quick Start](#-quick-start)
-   [🤖 Chatbot Text-to-SQL](#-chatbot-text-to-sql)
-   [🔧 API Reference](#-api-reference)
-   [🔒 Bảo mật](#-bảo-mật)
-   [🎯 Roadmap](#-roadmap)
-   [🤝 Contributing](#-contributing)

---

## 💳 VNPAY (thanh toán) — tích hợp nhanh

Hướng dẫn nhanh để sử dụng VNPAY (sandbox) trong dự án:

-   Thêm vào file `.env` (đã thêm sẵn trong repo):

    -   VNPAY_TMN_CODE=86ED9PKB
    -   VNPAY_HASH_SECRET=C4OX6WKZY7XBMCZK9PWCNU20JCOT3005
    -   VNPAY_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
    -   VNPAY_RETURN_URL=http://localhost:5173/thanks

-   File cấu hình: `config/vnpay.php` (đã tạo) — dùng `env()` để đọc cấu hình.

-   Controller: `app/Http/Controllers/VnPayController.php` với 2 endpoint:

    -   `POST /vnpay/pay` — tạo URL thanh toán và redirect người dùng tới VNPAY sandbox.
        -   Tham số: `amount` (bắt buộc, VND), `order_id` (tùy chọn), `description` (tùy chọn)
    -   `GET /vnpay/return` — VNPAY redirect về đây, controller kiểm tra `vnp_SecureHash` rồi trả kết quả JSON (ví dụ).

-   Routes đã đăng ký trong `routes/web.php`.

Ghi chú: phần xử lý sau khi thanh toán thành công (cập nhật `orders`/`payments`) cần tích hợp thêm vào `VnPayController::return` theo luồng nghiệp vụ của bạn (ghi trạng thái payment, gửi email, giảm stock...).

### IPN (server-to-server)

Một endpoint `POST /vnpay/ipn` đã được thêm để nhận notification từ VNPAY (server-to-server). Controller sẽ verify chữ ký và cập nhật `payments` tương ứng.

### Postman collection

Bạn có thể dùng Postman collection mẫu ở `postman/VNPAY.postman_collection.json` để thử các bước:

-   `POST /vnpay/pay` tạo payment và redirect (thực tế trả về redirect; trong Postman bạn sẽ nhận response hoặc redirect header)
-   `GET /vnpay/return` mô phỏng redirect từ VNPAY
-   `POST /vnpay/ipn` mô phỏng IPN server-to-server

---

## 🌟 Giới thiệu

> **Pizza Shop** là một website thương mại điện tử chuyên về pizza, được thiết kế với trải nghiệm người dùng tối ưu và hệ thống quản lý mạnh mẽ.

### 🎯 Mục tiêu

-   🍕 Mang đến trải nghiệm đặt pizza online tuyệt vời
-   📱 Responsive design cho mọi thiết bị
-   ⚡ Performance cao và tải trang nhanh
-   🔐 Bảo mật thông tin khách hàng tuyệt đối

---

## ✨ Tính năng nổi bật

<table>
<tr>
<td width="50%">

### 🛍️ **Cho Khách Hàng**

-   🏠 **Trang chủ hiện đại** với banner động
-   🍕 **Pizza customization** (size + đế)
-   🛒 **Smart cart** lưu trạng thái
-   💳 **Thanh toán đa dạng** (COD, Card, PayPal...)
-   ⭐ **Review system** 5 sao
-   🎟️ **Mã giảm giá** thông minh
-   📱 **Mobile-first design**
-   🍱 **Combo deals** tiết kiệm

</td>
<td width="50%">

### 👨‍💼 **Cho Admin**

-   📊 **Dashboard** với charts realtime
-   📦 **Quản lý sản phẩm** advanced
-   🚚 **Order tracking** workflow
-   👥 **Customer management**
-   🎨 **Content management** (banners, news)
-   📈 **Analytics & Reports**
-   🔧 **System settings**
-   🎯 **Coupon management**

</td>
</tr>
</table>

---

## 🖼️ Giao diện người dùng

> Dưới đây là một số màn hình giao diện thực tế của ứng dụng Pizza Shop.

### 🏠 Trang chủ (Home Page)

![Home Page](docs/home-page.png)

> Trang chủ hiện đại với hero banner động, danh sách sản phẩm nổi bật, tin tức và khu vực newsletter.

---

### 🍕 Trang sản phẩm (Product Page)

![Product Page](docs/product-page.png)

> Hiển thị toàn bộ danh sách sản phẩm theo danh mục, hỗ trợ lọc và tìm kiếm nhanh.

---

### 🔍 Chi tiết sản phẩm (Product Detail)

![Product Detail Page](docs/product-detail-page.png)

> Trang chi tiết pizza với tùy chọn kích thước, loại đế, số lượng và nút thêm vào giỏ hàng.

---

### 🛒 Giỏ hàng (Cart Page)

![Cart Page](docs/cart-page.png)

> Giao diện giỏ hàng thông minh: hiển thị sản phẩm, cập nhật số lượng, áp mã giảm giá và thanh toán.

---

### 🤖 Chatbot hỗ trợ khách hàng

![Chatbot Interface](docs/chatbot.png)

> Chatbot AI tích hợp trực tiếp trên giao diện, hỗ trợ người dùng tìm kiếm sản phẩm phù hợp bằng ngôn ngữ tự nhiên.

---

## 🚀 Chức năng chính

### 🏠 **1. Trang chủ (Home Page)**

```mermaid
graph LR
    A[🏠 Trang chủ] --> B[🎯 Banner quảng cáo]
    A --> C[🔥 Sản phẩm hot]
    A --> D[📰 Tin tức mới]
    A --> E[💌 Newsletter]
```

<details>
<summary>📋 Chi tiết chức năng</summary>

-   **🎨 Hero Banner**: Slider với các promotion hot
-   **🔥 Featured Products**: Top pizza bestseller
-   **📰 Latest News**: Tin tức, khuyến mãi mới
-   **⭐ Customer Reviews**: Đánh giá từ khách hàng
-   **📍 Store Locator**: Bản đồ cửa hàng

</details>

---

### 🍕 **2. Quản lý sản phẩm**

<div align="center">

|    🏷️ **Danh mục**    |       📝 **Mô tả**        |   🎯 **Đặc điểm**   |
| :-------------------: | :-----------------------: | :-----------------: |
|   🦐 Pizza Hải Sản    |    Tôm, cua, mực tươi     | Premium ingredients |
|     🥬 Pizza Chay     |      100% thuần chay      |   Healthy choice    |
|     🥩 Pizza Thịt     |    Pepperoni, xúc xích    |   Classic flavors   |
|   🧀 Pizza Phô Mai    |      4 loại phô mai       |    Cheese lovers    |
| 🍕 Pizza Truyền Thống |     Công thức cổ điển     |   Authentic taste   |
|   ⭐ Pizza Đặc Biệt   |    Sáng tạo độc quyền     |   Chef's special    |
|     🥤 Nước Uống      |     Giải khát đa dạng     |    Fresh drinks     |
|    🍰 Tráng Miệng     |      Bánh ngọt, kem       |    Sweet endings    |
|     🍟 Món Ăn Kèm     | Khoai tây chiên, gà chiên |     Side dishes     |
|  🎁 Combo Khuyến Mãi  |     Combo giá ưu đãi      |   Value packages    |

</div>

**🎛️ Tùy chọn Pizza:**

```yaml
Sizes:
    - 🔸 Mini (12cm): Perfect for kids
    - 🔹 Siêu Nhỏ (15cm): Light meal
    - 🔸 Cỡ Nhỏ Đặc Biệt (18cm): Individual
    - 🔹 Nhỏ (20cm): Single serving
    - 🔶 Cỡ Trung (22.5cm): For couples
    - 🔷 Vừa (25cm): For 2-3 people
    - 🔶 Lớn (30cm): Family size
    - 🔷 Cỡ Lớn Đặc Biệt (32cm): Extra large
    - 🟠 Cỡ Đại (35cm): Party size
    - 🟡 Gia Đình (40cm): Super family

Crusts:
    - 🥖 Đế Mỏng: Crispy & light
    - 🍞 Đế Dày: Soft & fluffy
    - 🧀 Viền Phô Mai: Cheese-stuffed crust
    - 🌭 Đế Nhân Nhồi: Sausage-filled
    - 🔥 Đế Giòn: Extra crispy
    - 🌿 Đế Nguyên Cám: Whole wheat healthy
    - 🚫 Đế Không Gluten: Gluten-free option
    - 🧄 Đế Hành Lá: Green onion flavored
    - 🌶️ Đế Tiêu Đen: Black pepper crust
    - 🌿 Đế Thảo Mộc: Italian herbs
```

---

**✨ Features:**

-   🔄 **Auto-sync** cho user đăng nhập
-   💾 **Persistent storage** với database
-   🍪 **Session storage** cho guest
-   ⚡ **Real-time updates** khi thay đổi
-   🧮 **Auto-calculate** tổng tiền, thuế, phí ship
-   🍱 **Combo support** - có thể thêm cả sản phẩm lẻ và combo

---

### 🍱 **4. Hệ thống Combo**

```mermaid
graph TB
    A[🍱 Combos] --> B[📋 Combo Items]
    B --> C[🍕 Product Variants]
    C --> D[📏 Sizes]
    C --> E[🥖 Crusts]
    A --> F[💰 Special Pricing]
    A --> G[📅 Time-limited]
```

**🎯 Combo Features:**

-   🎁 **Bundle Products**: Kết hợp nhiều sản phẩm với giá ưu đãi
-   📅 **Time-limited**: Có thời hạn bắt đầu và kết thúc
-   💰 **Special Pricing**: Giá combo thấp hơn mua lẻ
-   🔄 **Flexible Quantities**: Số lượng linh hoạt cho từng item
-   📱 **Easy Management**: Admin dễ dàng tạo/chỉnh sửa combo

---

### 💳 **5. Hệ thống thanh toán**

<div align="center">

| 💳 **Phương thức** | 🏷️ **Phí** | ⏱️ **Xử lý** | 🔒 **Bảo mật** |
| :----------------: | :--------: | :----------: | :------------: |
|   💵 Cash (COD)    |  Miễn phí  |   Tức thì    |     ⭐⭐⭐     |
|   💳 Credit Card   |    2.9%    |   1-3 phút   |   ⭐⭐⭐⭐⭐   |
|  🏦 Bank Transfer  |  Miễn phí  |  15-30 phút  |    ⭐⭐⭐⭐    |
|     💙 PayPal      |    3.4%    |   Tức thì    |   ⭐⭐⭐⭐⭐   |

</div>

**💡 Payment Features:**

-   🛡️ **One Payment Rule**: Mỗi đơn hàng chỉ có 1 payment thành công
-   🔄 **Status Tracking**: Pending → Completed/Failed
-   🧾 **Transaction ID**: Lưu trữ mã giao dịch
-   📊 **Payment Analytics**: Thống kê theo phương thức

---

### 👤 **6. Quản lý tài khoản**

```yaml
Authentication:
    📝 Register: Email verification required
    🔐 Login: Username/Email + Password
    🔑 Password Reset: OTP via email/SMS
    👤 Profile Management: Update info anytime

User Dashboard:
    📊 Overview: Order stats, points earned
    📦 Order History: Track all purchases
    ⭐ Reviews: Rate & comment products/combos
    🎟️ Coupons: Available vouchers
    📍 Addresses: Multiple delivery locations
    🔔 Notifications: Order updates, promotions
```

---

### 🔧 **7. Admin Panel**

<div align="center">

```mermaid
graph TB
    A[👨‍💼 Admin Dashboard] --> B[📊 Analytics]
    A --> C[📦 Products]
    A --> D[🛍️ Orders]
    A --> E[👥 Customers]
    A --> F[🎨 Content]
    A --> G[🍱 Combos]
    A --> H[🎟️ Coupons]

    B --> B1[💰 Revenue Charts]
    B --> B2[📈 Sales Trends]

    C --> C1[➕ Add Product]
    C --> C2[✏️ Edit Variants]
    C --> C3[📸 Image Upload]

    D --> D1[📋 Order List]
    D --> D2[🔄 Status Update]
    D --> D3[🖨️ Print Invoice]

    E --> E1[👤 Customer Info]
    E --> E2[📊 Purchase History]

    F --> F1[🎨 Banners]
    F --> F2[📰 News]
    F --> F3[❓ FAQ]

    G --> G1[🍱 Create Combo]
    G --> G2[📋 Manage Items]

    H --> H1[🎟️ Create Coupon]
    H --> H2[📊 Usage Stats]
```

</div>

**🎯 Key Features:**

-   📈 **Real-time Dashboard** với charts động
-   🔄 **Bulk Operations** cho products/orders
-   📱 **Mobile Admin** responsive design
-   🔔 **Push Notifications** cho orders mới
-   📊 **Advanced Analytics** với filters
-   🎨 **WYSIWYG Editor** cho content
-   🍱 **Combo Management** tạo và quản lý combo
-   🎟️ **Smart Coupons** với điều kiện phức tạp

---

### 🎟️ **8. Hệ thống khuyến mãi**

<table>
<tr>
<td width="33%">

#### 💯 **Percentage Discount**

```yaml
Examples:
    PIZZA10: 10% off (min 200k, max 50k)
    SUMMER20: 20% off (min 300k, max 100k)
    FLASH25: 25% off (min 500k, max 150k)
    VIP15: 15% off (min 250k, max 75k)

Features: ✅ Min order amount
    ✅ Max discount cap
    ✅ Expiry date
    ✅ Active/Inactive status
```

</td>
<td width="33%">

#### 💰 **Fixed Amount**

```yaml
Examples:
    FREESHIP: -30k shipping (min 150k)
    NEWUSER: -50k first order (min 100k)
    COMBO50: -50k combo deals (min 400k)
    PIZZADAY: -20k special day (min 150k)

Benefits: ✅ Easy to understand
    ✅ Great for small orders
    ✅ Shipping incentives
    ✅ New customer attraction
```

</td>
<td width="34%">

#### 🎁 **Smart Validation**

```yaml
Business Rules: ⚡ Either percentage OR amount
    ⚡ Not both at same time
    ⚡ Auto-check minimum order
    ⚡ Validate expiry date
    ⚡ Usage tracking

Database Constraints: ✅ CHECK constraints
    ✅ UNIQUE coupon codes
    ✅ Proper data types
    ✅ Audit timestamps
```

</td>
</tr>
</table>

---

## 🏗️ Cấu trúc Database

### 📊 **Database Schema Overview**

```mermaid
erDiagram
    USERS ||--o{ ORDERS : places
    USERS ||--o{ CARTS : owns
    USERS ||--o{ REVIEWS : writes
    USERS ||--o{ CONTACTS : submits

    PRODUCTS ||--o{ PRODUCT_VARIANTS : has
    PRODUCTS }|--|| CATEGORIES : belongs_to
    PRODUCTS ||--o{ REVIEWS : receives

    ORDERS ||--o{ ORDER_ITEMS : contains
    ORDERS }|--o| COUPONS : uses
    ORDERS ||--|| PAYMENTS : has

    CARTS ||--o{ CART_ITEMS : contains
    CART_ITEMS }|--o| PRODUCT_VARIANTS : references
    CART_ITEMS }|--o| COMBOS : references
    ORDER_ITEMS }|--o| PRODUCT_VARIANTS : references
    ORDER_ITEMS }|--o| COMBOS : references

    PRODUCT_VARIANTS }|--|| SIZES : has
    PRODUCT_VARIANTS }|--|| CRUSTS : has

    COMBOS ||--o{ COMBO_ITEMS : contains
    COMBO_ITEMS }|--|| PRODUCT_VARIANTS : includes

    REVIEWS }|--o| COMBOS : rates
```

### 🗃️ **Core Tables**

<details>
<summary>👥 <strong>Users & Authentication</strong></summary>

```sql
-- 👤 Users: Customer & Admin accounts
users (
  id, username, password, email, full_name,
  address, phone, role[customer|admin],
  created_at, updated_at
)

-- 📞 Contacts: Customer inquiries (flexible user/guest)
contacts (
  id, user_id?, name?, email, message,
  created_at, updated_at
)
-- Logic: user_id thì auto-fill name/email, guest thì required name/email
```

</details>

<details>
<summary>🍕 <strong>Products & Catalog</strong></summary>

```sql
-- 🏷️ Categories: 10 product types
categories (id, name, description, created_at, updated_at)

-- 🍕 Products: Pizza and items
products (id, name, description, image_url, category_id, timestamps)

-- 📏 Sizes: 10 pizza dimensions
sizes (id, name, diameter, created_at, updated_at)

-- 🥖 Crusts: 10 pizza base types
crusts (id, name, description, created_at, updated_at)

-- 🎛️ Product Variants: Price combinations
product_variants (
  id, product_id, size_id?, crust_id?,
  price, stock, created_at, updated_at
)
-- Logic: Pizza cần size+crust, non-pizza thì NULL
```

</details>

<details>
<summary>🛒 <strong>Shopping & Orders</strong></summary>

```sql
-- 🛒 Carts: Shopping baskets (user-based)
carts (id, user_id, created_at, updated_at)

-- 📦 Cart Items: Products OR combos in cart
cart_items (
  id, cart_id, product_variant_id?, combo_id?,
  quantity, created_at, updated_at
)
-- Logic: Either product_variant_id OR combo_id (XOR)

-- 🛍️ Orders: Purchase records with auto-total
orders (
  id, user_id, total_amount[auto-calculated],
  status[pending|confirmed|shipped|delivered|cancelled],
  shipping_address, coupon_id?, created_at, updated_at
)

-- 📋 Order Items: Products OR combos in order
order_items (
  id, order_id, product_variant_id?, combo_id?,
  quantity, price, created_at, updated_at
)
-- Logic: Either product_variant_id OR combo_id (XOR)

-- 💳 Payments: One completed payment per order
payments (
  id, order_id, amount,
  method[cash|credit_card|bank_transfer|paypal],
  status[pending|completed|failed],
  transaction_id?, created_at, updated_at
)
```

</details>

<details>
<summary>🍱 <strong>Combos & Bundles</strong></summary>

```sql
-- 🍱 Combos: Bundle deals with time limits
combos (
  id, name, description, price, image_url,
  start_date?, end_date?, is_active,
  created_at, updated_at
)

-- 📋 Combo Items: Products included in combo
combo_items (
  id, combo_id, product_variant_id,
  quantity, created_at, updated_at
)
```

</details>

<details>
<summary>🎯 <strong>Marketing & Content</strong></summary>

```sql
-- 🎟️ Coupons: Smart discount system
coupons (
  id, code[unique], discount_percentage?, discount_amount?,
  expiry_date?, min_order_amount?, max_discount_amount?,
  is_active, created_at, updated_at
)
-- Logic: Either percentage OR amount (XOR)

-- ⭐ Reviews: Product OR combo ratings
reviews (
  id, product_id?, combo_id?, user_id,
  rating[1-5], comment?, created_at, updated_at
)
-- Logic: Either product_id OR combo_id (XOR)
-- Unique: user can review each product/combo only once

-- 🎨 Banners: Position-based ads
banners (
  id, image_url, link?,
  position[homepage_top|homepage_bottom|product_page],
  active, created_at, updated_at
)

-- 📰 News: Company updates
news (id, title, content, image_url?, created_at, updated_at)

-- ❓ FAQ: Customer support
faq (id, question, answer, created_at, updated_at)
```

</details>

### 📈 **Sample Data Overview**

<div align="center">

|    📊 **Table**     | 🔢 **Records** | 📝 **Description**                     |
| :-----------------: | :------------: | :------------------------------------- |
|      👥 Users       |       10       | 8 customers + 2 admins                 |
|    🏷️ Categories    |       10       | Pizza types, drinks, desserts, combos  |
|     🍕 Products     |       10       | Various pizzas and items               |
|      📏 Sizes       |       10       | From mini (12cm) to family (40cm)      |
|      🥖 Crusts      |       10       | Thin, thick, stuffed, specialty crusts |
| 🎛️ Product Variants |       11       | Size+crust combinations with prices    |
|      🍱 Combos      |       2        | Couple combo & Family combo            |
|   📋 Combo Items    |       6        | Products included in combos            |
|     🎟️ Coupons      |       10       | Percentage & fixed discounts           |
|      🛍️ Orders      |       10       | Different statuses & customers         |
|   📦 Order Items    |       11       | Mix of products and combos             |
|     💳 Payments     |       10       | Various payment methods                |
|     ⭐ Reviews      |       10       | 1-5 star ratings with comments         |
|      🛒 Carts       |       8        | Active customer carts                  |
|     🎨 Banners      |       10       | Homepage & product page ads            |
|       📰 News       |       10       | Promotions & updates                   |
|       ❓ FAQ        |       10       | Common questions & answers             |
|     📞 Contacts     |       10       | Customer inquiries (mix users/guests)  |

</div>

---

### 🔧 **Advanced Database Features**

#### 🛡️ **Triggers & Constraints**

```sql
-- ✅ Order Items Validation
-- Đảm bảo order_items có EITHER product_variant_id OR combo_id (không cả hai, không thiếu)

-- ✅ Cart Items Validation
-- Đảm bảo cart_items có EITHER product_variant_id OR combo_id

-- ✅ Auto Total Calculation
-- Tự động tính total_amount khi thêm/sửa/xóa order_items

-- ✅ Payment Validation
-- Mỗi order chỉ có thể có 1 payment với status 'completed'

-- ✅ Contact Logic
-- Nếu user_id có thì auto-fill name/email từ users table
-- Nếu guest thì require name và email

-- ✅ Coupon Validation
-- Either discount_percentage OR discount_amount (không cả hai)

-- ✅ Review Constraints
-- User chỉ có thể review mỗi product/combo 1 lần
-- Either product_id OR combo_id (không cả hai)
```

#### 📊 **Indexes for Performance**

```sql
-- 🚀 Optimized queries
idx_orders_user_id              -- Fast user order lookup
idx_order_items_order_id        -- Quick order details
idx_cart_items_cart_id          -- Instant cart loading
idx_payments_order_id           -- Payment tracking
idx_reviews_user_id             -- User reviews
idx_products_category_id        -- Category filtering
idx_combo_items_combo_id        -- Combo details
idx_cart_items_product_variant_id -- Cart product lookup
idx_order_items_product_variant_id -- Order product lookup
```

---

## 💻 Công nghệ sử dụng

### 🏗️ **Tech Stack**

<div align="center">

![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)

</div>

### Backend Options

<table>
<tr>
<td width="33%">

#### 🐘 **PHP Stack**

```yaml
Core: PHP 8.1+
Framework: Laravel/CodeIgniter
Database: MySQL 8.0+
Server: Apache/Nginx
Cache: Redis
ORM: Eloquent/Active Record
```

</td>
<td width="33%">

#### 🟢 **Node.js Stack**

```yaml
Runtime: Node.js 18+
Framework: Express.js
Database: MySQL + Sequelize
Auth: JWT/Passport
Cache: Redis
Validation: Joi/Yup
```

</td>
<td width="34%">

#### 🐍 **Python Stack**

```yaml
Language: Python 3.9+
Framework: Django/FastAPI
Database: MySQL + SQLAlchemy
Auth: Django Auth/OAuth2
Cache: Redis
Validation: Pydantic
```

</td>
</tr>
</table>

### Frontend Technologies

```yaml
🎨 Core:
    - HTML5 semantic markup
    - CSS3 with Flexbox/Grid
    - Vanilla JavaScript ES6+
    - Responsive design principles

📱 Frameworks (Optional):
    - React.js with hooks
    - Vue.js 3 composition API
    - Bootstrap 5 / Tailwind CSS
    - SCSS for advanced styling

⚡ Performance:
    - Lazy loading images
    - Code splitting
    - Service workers
    - CDN integration
```

---

## ⚡ Quick Start

### 🚀 **Installation**

<details>
<summary>🐳 <strong>Docker Setup (Recommended)</strong></summary>

```bash
# Clone repository
git clone https://github.com/yourusername/pizza-shop.git
cd pizza-shop

# Start with Docker Compose
docker-compose up -d

# Import database
docker exec -i pizza_shop_db mysql -u root -ppassword pizza_shop < database/pizza_shop.sql

# Access application
open http://localhost:8080
```

</details>

<details>
<summary>💻 <strong>Manual Setup</strong></summary>

```bash
# 1️⃣ Clone & Setup
git clone https://github.com/yourusername/pizza-shop.git
cd pizza-shop

# 2️⃣ Database Setup
mysql -u root -p
CREATE DATABASE pizza_shop;
mysql -u root -p pizza_shop < database/pizza_shop.sql

# 3️⃣ PHP Setup
composer install
cp .env.example .env
php artisan key:generate
php artisan serve

# 4️⃣ Node.js Setup (Alternative)
npm install
cp .env.example .env
npm run dev
```

</details>

### ⚙️ **Configuration**

```yaml
# .env file
APP_NAME=Pizza Shop
APP_URL=http://localhost:8000
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pizza_shop
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password

PAYMENT_STRIPE_KEY=sk_test_...
PAYMENT_PAYPAL_CLIENT_ID=your_paypal_id
```

---

# 🔧 Tài liệu API

## 🔐 **API Xác thực**

```http
POST   /api/auth/login             # 🔐 Đăng nhập người dùng
GET    /api/auth/me               # 👤 Lấy thông tin người dùng hiện tại
POST   /api/auth/logout           # 🚪 Đăng xuất người dùng
GET    /api/profile               # 👤 Lấy thông tin hồ sơ (yêu cầu đăng nhập)
```

## 🍕 **API Sản phẩm**

```http
GET    /api/products               # 📋 Danh sách tất cả sản phẩm
POST   /api/products               # ➕ Tạo sản phẩm mới (admin)
GET    /api/products/featured      # ⭐ Sản phẩm nổi bật
GET    /api/products/{id}          # 🔍 Chi tiết sản phẩm
PUT    /api/products/{id}          # ✏️ Cập nhật sản phẩm (admin)
DELETE /api/products/{id}          # 🗑️ Xóa sản phẩm (admin)
```

## 🏷️ **API Danh mục**

```http
GET    /api/categories             # 📋 Danh sách danh mục
POST   /api/categories             # ➕ Tạo danh mục mới (admin)
GET    /api/categories/{id}        # 🔍 Chi tiết danh mục
PUT    /api/categories/{id}        # ✏️ Cập nhật danh mục (admin)
DELETE /api/categories/{id}        # 🗑️ Xóa danh mục (admin)
```

## 📏 **API Kích thước & Loại đế**

```http
GET    /api/sizes                  # 📏 Danh sách kích thước
POST   /api/sizes                  # ➕ Tạo kích thước mới (admin)
GET    /api/sizes/{id}             # 🔍 Chi tiết kích thước
PUT    /api/sizes/{id}             # ✏️ Cập nhật kích thước (admin)
DELETE /api/sizes/{id}             # 🗑️ Xóa kích thước (admin)

GET    /api/crusts                 # 🥖 Danh sách loại đế pizza
POST   /api/crusts                 # ➕ Tạo loại đế mới (admin)
GET    /api/crusts/{id}            # 🔍 Chi tiết loại đế
PUT    /api/crusts/{id}            # ✏️ Cập nhật loại đế (admin)
DELETE /api/crusts/{id}            # 🗑️ Xóa loại đế (admin)
```

## 🛒 **API Giỏ hàng**

```http
GET    /api/cart/products          # 📋 Lấy tất cả sản phẩm cho giỏ hàng
GET    /api/cart                   # 👀 Xem giỏ hàng (yêu cầu đăng nhập)
POST   /api/cart/items             # ➕ Thêm vào giỏ hàng (yêu cầu đăng nhập)
PUT    /api/cart/items/{itemId}    # ✏️ Cập nhật sản phẩm trong giỏ (yêu cầu đăng nhập)
DELETE /api/cart/items/{itemId}    # 🗑️ Xóa khỏi giỏ hàng (yêu cầu đăng nhập)
DELETE /api/cart/clear             # 🧹 Xóa toàn bộ giỏ hàng (yêu cầu đăng nhập)
```

## 🍱 **API Combo**

```http
GET    /api/combos                 # 🍱 Danh sách tất cả combo
POST   /api/combos                 # ➕ Tạo combo mới (admin)
GET    /api/combos/active          # ✅ Combo đang hoạt động
GET    /api/combos/{id}            # 🔍 Chi tiết combo
PUT    /api/combos/{id}            # ✏️ Cập nhật combo (admin)
DELETE /api/combos/{id}            # 🗑️ Xóa combo (admin)
```

## 🍕 **API Sản phẩm trong Combo**

```http
GET    /api/combo-items/{id}       # 🔍 Chi tiết sản phẩm trong combo
PUT    /api/combo-items/{id}       # ✏️ Cập nhật sản phẩm trong combo
DELETE /api/combo-items/{id}       # 🗑️ Xóa sản phẩm khỏi combo
```

## 🛍️ **API Đơn hàng**

```http
GET    /api/orders                 # 📋 Danh sách đơn hàng (yêu cầu đăng nhập)
POST   /api/orders                 # 🛍️ Tạo đơn hàng mới (yêu cầu đăng nhập)
GET    /api/orders/{id}            # 🔍 Chi tiết đơn hàng (yêu cầu đăng nhập)
PATCH  /api/orders/{id}/status     # ✏️ Cập nhật trạng thái đơn hàng
POST   /api/orders/{id}/cancel     # ❌ Hủy đơn hàng (yêu cầu đăng nhập)
```

## 📦 **API Chi tiết đơn hàng**

```http
GET    /api/order-items            # 📋 Danh sách chi tiết đơn hàng (yêu cầu đăng nhập)
POST   /api/order-items            # ➕ Thêm sản phẩm vào đơn hàng (yêu cầu đăng nhập)
GET    /api/order-items/{id}       # 🔍 Chi tiết sản phẩm trong đơn hàng (yêu cầu đăng nhập)
PUT    /api/order-items/{id}       # ✏️ Cập nhật sản phẩm trong đơn hàng (yêu cầu đăng nhập)
DELETE /api/order-items/{id}       # 🗑️ Xóa sản phẩm khỏi đơn hàng (yêu cầu đăng nhập)
GET    /api/order-items/by-order/{orderId}           # 📦 Lấy chi tiết theo đơn hàng (yêu cầu đăng nhập)
GET    /api/order-items/stats/best-selling-products  # 📊 Sản phẩm bán chạy nhất (yêu cầu đăng nhập)
GET    /api/order-items/stats/best-selling-combos    # 📊 Combo bán chạy nhất (yêu cầu đăng nhập)
```

## 💳 **API Thanh toán**

```http
GET    /api/payments               # 📋 Danh sách thanh toán (yêu cầu đăng nhập)
POST   /api/payments               # ➕ Tạo thanh toán mới (yêu cầu đăng nhập)
GET    /api/payments/{id}          # 🔍 Chi tiết thanh toán (yêu cầu đăng nhập)
PATCH  /api/payments/{id}/status   # ✏️ Cập nhật trạng thái thanh toán
```

## 🎟️ **API Mã giảm giá**

```http
GET    /api/coupons                # 📋 Danh sách mã giảm giá
POST   /api/coupons                # ➕ Tạo mã giảm giá mới (admin)
GET    /api/coupons/{id}           # 🔍 Chi tiết mã giảm giá
PUT    /api/coupons/{id}           # ✏️ Cập nhật mã giảm giá (admin)
DELETE /api/coupons/{id}           # 🗑️ Xóa mã giảm giá (admin)
POST   /api/coupons/validate       # ✅ Kiểm tra tính hợp lệ của mã giảm giá
```

## 🔧 **API Biến thể sản phẩm**

```http
GET    /api/product-variants       # 📋 Danh sách biến thể sản phẩm
POST   /api/product-variants       # ➕ Tạo biến thể mới (admin)
GET    /api/product-variants/{id}  # 🔍 Chi tiết biến thể
PUT    /api/product-variants/{id}  # ✏️ Cập nhật biến thể (admin)
DELETE /api/product-variants/{id}  # 🗑️ Xóa biến thể (admin)
```

## ⭐ **API Đánh giá**

```http
GET    /api/reviews                # 📋 Danh sách đánh giá (yêu cầu đăng nhập)
POST   /api/reviews                # ➕ Tạo đánh giá mới (yêu cầu đăng nhập)
GET    /api/reviews/{id}           # 🔍 Chi tiết đánh giá (yêu cầu đăng nhập)
PUT    /api/reviews/{id}           # ✏️ Cập nhật đánh giá (yêu cầu đăng nhập)
PATCH  /api/reviews/{id}           # ✏️ Cập nhật đánh giá (yêu cầu đăng nhập)
DELETE /api/reviews/{id}           # 🗑️ Xóa đánh giá (yêu cầu đăng nhập)
```

## 👥 **API Người dùng**

```http
GET    /api/users                  # 📋 Danh sách người dùng (admin)
POST   /api/users                  # ➕ Tạo người dùng mới (admin)
GET    /api/users/{id}             # 🔍 Chi tiết người dùng (admin)
PUT    /api/users/{id}             # ✏️ Cập nhật người dùng (admin)
PATCH  /api/users/{id}             # ✏️ Cập nhật người dùng (admin)
DELETE /api/users/{id}             # 🗑️ Xóa người dùng (admin)
```

## 🎨 **API Quản lý Banner**

```http
GET    /api/banners                # 🎨 Danh sách banner
POST   /api/banners                # ➕ Tạo banner mới (admin)
GET    /api/banners/{id}           # 🔍 Chi tiết banner
PUT    /api/banners/{id}           # ✏️ Cập nhật banner (admin)
DELETE /api/banners/{id}           # 🗑️ Xóa banner (admin)
```

## 📰 **API Tin tức**

```http
GET    /api/news                   # 📰 Danh sách tin tức
POST   /api/news                   # ➕ Tạo tin tức mới (admin)
GET    /api/news/latest/{count?}   # 📰 Tin tức mới nhất (count tùy chọn)
GET    /api/news/{id}              # 🔍 Chi tiết tin tức
PUT    /api/news/{id}              # ✏️ Cập nhật tin tức (admin)
DELETE /api/news/{id}              # 🗑️ Xóa tin tức (admin)
```

## ❓ **API Câu hỏi thường gặp**

```http
GET    /api/faqs                   # ❓ Danh sách FAQ
POST   /api/faqs                   # ➕ Tạo FAQ mới (admin)
GET    /api/faqs/{id}              # 🔍 Chi tiết FAQ
PUT    /api/faqs/{id}              # ✏️ Cập nhật FAQ (admin)
DELETE /api/faqs/{id}              # 🗑️ Xóa FAQ (admin)
```

## 📞 **API Liên hệ**

```http
GET    /api/contacts               # 📋 Danh sách liên hệ (admin)
POST   /api/contacts               # 📞 Gửi form liên hệ
GET    /api/contacts/{id}          # 🔍 Chi tiết liên hệ (admin)
DELETE /api/contacts/{id}          # 🗑️ Xóa liên hệ (admin)
```

## 🔒 **API Quên mật khẩu**

```http
POST   /api/password/forgot        # 📧 Gửi email đặt lại mật khẩu
POST   /api/password/verify-token  # ✅ Xác thực token reset
POST   /api/password/reset         # 🔑 Đặt lại mật khẩu mới
POST   /api/password/cancel-reset  # ❌ Hủy yêu cầu reset
GET    /reset-password             # 📝 Form đặt lại mật khẩu (Web)
```

### **Chi tiết API:**

**1. Gửi email đặt lại mật khẩu**

```http
POST /api/password/forgot
Content-Type: application/json

{
    "email": "user@example.com"
}

# Response Success (200)
{
    "message": "Password reset email sent successfully",
    "expires_in": "15 phút"
}

# Response Error (404/429)
{
    "error": "Email does not exist"
    // hoặc "You can only request a password reset once every 5 minutes"
}
```

**2. Xác thực token reset**

```http
POST /api/password/verify-token
Content-Type: application/json

{
    "email": "user@example.com",
    "token": "QlrgjJUkSFuaoxIzjKLNwrGly81ppzvYiHox6qHk..."
}

# Response Success (200)
{
    "valid": true,
    "email": "user@example.com",
    "username": "john_doe",
    "expires_at": "2025-09-01T10:15:00.000Z",
    "time_remaining": "12 phút"
}

# Response Error (400)
{
    "valid": false,
    "error": "Token không hợp lệ hoặc đã hết hạn"
}
```

**3. Đặt lại mật khẩu**

```http
POST /api/password/reset
Content-Type: application/json

{
    "email": "user@example.com",
    "token": "QlrgjJUkSFuaoxIzjKLNwrGly81ppzvYiHox6qHk...",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}

# Response Success (200)
{
    "message": "Mật khẩu đã được cập nhật thành công. Vui lòng đăng nhập lại với mật khẩu mới."
}

# Response Error (400)
{
    "error": "Token đã hết hạn"
}
```

**4. Hủy yêu cầu reset**

```http
POST /api/password/cancel-reset
Content-Type: application/json

{
    "email": "user@example.com"
}

# Response Success (200)
{
    "message": "Yêu cầu đặt lại mật khẩu đã được hủy"
}
```

**5. Form đặt lại mật khẩu (Web Interface)**

```http
GET /reset-password?email=user@example.com&token=abc123
# Hiển thị giao diện form để người dùng nhập mật khẩu mới
```

### **🔐 Bảo mật & Giới hạn:**

-   ⏱️ Token có hiệu lực **15 phút**
-   🔄 Chỉ sử dụng **1 lần duy nhất**
-   🚫 Rate limit: **1 lần/5 phút** mỗi email
-   📧 Email template responsive với thiết kế Pizza Shop
-   🛡️ Token được lưu trong cache, tự động xóa sau khi sử dụng

### **📝 Flow hoạt động:**

1. User nhập email → `POST /api/password/forgot`
2. Nhận email với link reset → Click vào link
3. Mở form → `GET /reset-password?email=...&token=...`
4. Form tự động verify token → `POST /api/password/verify-token`
5. User nhập mật khẩu mới → `POST /api/password/reset`

---

## 📝 **Chi tiết tham số và response**

### 🔐 Đăng nhập

**POST** `/api/auth/login`

```json
// Request
{
  "email": "user@example.com",
  "password": "password123"
}

// Response
{
  "success": true,
  "token": "jwt_token_here",
  "user": { ... }
}
```

### 🛒 Thêm vào giỏ hàng

**POST** `/api/cart/items`

```json
// Request (Sản phẩm)
{
  "product_variant_id": 1,
  "quantity": 2
}

// Request (Combo)
{
  "combo_id": 1,
  "quantity": 1
}
```

### 🛍️ Tạo đơn hàng

**POST** `/api/orders`

```json
// Request
{
    "shipping_address": "123 Đường ABC, Quận 1, TP.HCM",
    "coupon_code": "DISCOUNT10"
}
```

### ✅ Kiểm tra mã giảm giá

**POST** `/api/coupons/validate`

```json
// Request
{
  "code": "DISCOUNT10",
  "order_amount": 500000
}

// Response
{
  "valid": true,
  "coupon": { ... },
  "discount": 50000
}
```

### ⭐ Tạo đánh giá

**POST** `/api/reviews`

```json
// Request (Đánh giá sản phẩm)
{
  "product_id": 1,
  "rating": 5,
  "comment": "Pizza rất ngon!"
}

// Request (Đánh giá combo)
{
  "combo_id": 1,
  "rating": 4,
  "comment": "Combo tuyệt vời!"
}
```

### 📊 Thống kê sản phẩm bán chạy

**GET** `/api/order-items/stats/best-selling-products?days=30&limit=10`

### 📊 Thống kê combo bán chạy

**GET** `/api/order-items/stats/best-selling-combos?days=30&limit=10`

---

## 🔒 **Yêu cầu xác thực**

-   🟢 **Công khai**: Có thể truy cập mà không cần đăng nhập
-   🔐 **Yêu cầu đăng nhập**: Cần JWT token trong header `Authorization: Bearer {token}`
-   👑 **Admin**: Chỉ admin mới có thể truy cập

## 📊 **Các tham số query phổ biến**

-   `?active=true/false` - Lọc theo trạng thái hoạt động
-   `?search=keyword` - Tìm kiếm theo từ khóa
-   `?status=pending/confirmed/shipped/delivered/cancelled` - Lọc theo trạng thái
-   `?days=30` - Thống kê trong X ngày
-   `?limit=10` - Giới hạn số kết quả
-   `?position=homepage_top/homepage_bottom/product_page` - Lọc banner theo vị trí

---

# 📊 Revenue Statistics API Documentation

## **Revenue API Endpoints**

```http
GET    /api/revenue/daily          # 📅 Thống kê doanh thu theo ngày
GET    /api/revenue/weekly         # 📅 Thống kê doanh thu theo tuần
GET    /api/revenue/monthly        # 📅 Thống kê doanh thu theo tháng
GET    /api/revenue/yearly         # 📅 Thống kê doanh thu theo năm

GET    /api/revenue/top-products   # 🏆 Top sản phẩm bán chạy nhất
GET    /api/revenue/combo          # 🎯 Thống kê doanh thu combo

GET    /api/revenue/top-customers  # 👑 Top khách hàng VIP

GET    /api/revenue/coupons        # 🎟️ Thống kê doanh thu theo coupon

GET    /api/revenue/dashboard      # 📈 Tổng quan dashboard
```

---

## **📊 1. THỐNG KÊ DOANH THU THEO THỜI GIAN**

### **1.1 Doanh thu theo ngày**

```http
GET /api/revenue/daily?date=05/09/2025

# Response Success (200)
{
    "data": [
        {
            "date": "05/09/2025",
            "total_revenue": "2500000.00",
            "total_orders": 45,
            "avg_order_value": "555555.56"
        }
    ]
}

# Response Error (400)
{
    "error": "Invalid date format. Please use d/m/Y (e.g., 04/09/2025)."
}
```

**Parameters:**

-   `date` (optional): Ngày cần thống kê (format: d/m/Y). Mặc định: hôm nay

---

### **1.2 Doanh thu theo tuần**

```http
GET /api/revenue/weekly?year=2025&week=36

# Response Success (200)
{
    "data": [
        {
            "week": 36,
            "year": 2025,
            "total_revenue": "15000000.00",
            "total_orders": 280
        }
    ]
}

# Response Error (400)
{
    "error": "Invalid year or week number."
}
```

**Parameters:**

-   `year` (optional): Năm cần thống kê. Mặc định: năm hiện tại
-   `week` (optional): Tuần cần thống kê (1-53). Mặc định: tuần hiện tại

---

### **1.3 Doanh thu theo tháng**

```http
GET /api/revenue/monthly?year=2025&month=9

# Response Success (200)
{
    "data": [
        {
            "month": 9,
            "year": 2025,
            "month_name": "09/2025",
            "total_revenue": "50000000.00",
            "total_orders": 850,
            "avg_order_value": "588235.29"
        }
    ]
}

# Response Error (400)
{
    "error": "Invalid month format. Month must be a number between 1 and 12."
}
```

**Parameters:**

-   `year` (optional): Năm cần thống kê. Mặc định: năm hiện tại
-   `month` (optional): Tháng cần thống kê (1-12). Nếu không có sẽ lấy tất cả tháng trong năm

---

### **1.4 Doanh thu theo năm**

```http
GET /api/revenue/yearly?year=2025

# Response Success (200)
{
    "data": [
        {
            "year": 2025,
            "total_revenue": "500000000.00",
            "total_orders": 8500,
            "avg_order_value": "588235.29"
        }
    ]
}

# Response Error (400)
{
    "error": "Invalid year format. Year must be a number between 1900 and 9999."
}
```

**Parameters:**

-   `year` (optional): Năm cần thống kê. Nếu không có sẽ lấy tất cả năm

---

## **🏆 2. THỐNG KÊ DOANH THU THEO SẢN PHẨM**

### **2.1 Top sản phẩm bán chạy**

```http
GET /api/revenue/top-products?limit=10

# Response Success (200)
{
    "data": [
        {
            "product_name": "Pizza Margherita",
            "size_name": "Large",
            "crust_name": "Thin Crust",
            "total_sold": 150,
            "total_revenue": "7500000.00",
            "avg_price": "50000.00"
        },
        {
            "product_name": "Pizza Hawaii",
            "size_name": "Medium",
            "crust_name": "Thick Crust",
            "total_sold": 120,
            "total_revenue": "6000000.00",
            "avg_price": "50000.00"
        }
    ]
}

# Response Error (400)
{
    "error": "Invalid limit value."
}
```

**Parameters:**

-   `limit` (optional): Số lượng sản phẩm trả về. Mặc định: 10

---

### **2.2 Doanh thu combo**

```http
GET /api/revenue/combo?limit=10

# Response Success (200)
{
    "data": [
        {
            "combo_name": "Family Deluxe",
            "total_sold": 80,
            "total_revenue": "12000000.00",
            "combo_price": "150000.00",
            "total_orders": 65
        },
        {
            "combo_name": "Couple Special",
            "total_sold": 95,
            "total_revenue": "9500000.00",
            "combo_price": "100000.00",
            "total_orders": 78
        }
    ]
}

# Response Error (500)
{
    "error": "Database query failed: [error message]"
}
```

**Parameters:**

-   `limit` (optional): Số lượng combo trả về. Mặc định: 10

---

## **👑 3. THỐNG KÊ DOANH THU THEO KHÁCH HÀNG**

### **3.1 Top khách hàng VIP**

```http
GET /api/revenue/top-customers?limit=20

# Response Success (200)
{
    "data": [
        {
            "full_name": "Nguyễn Văn A",
            "email": "nguyenvana@example.com",
            "phone": "0901234567",
            "total_orders": 25,
            "total_spent": "5000000.00",
            "avg_order_value": "200000.00",
            "last_order_date": "05/09/2025"
        },
        {
            "full_name": "Trần Thị B",
            "email": "tranthib@example.com",
            "phone": "0987654321",
            "total_orders": 20,
            "total_spent": "4500000.00",
            "avg_order_value": "225000.00",
            "last_order_date": "03/09/2025"
        }
    ]
}

# Response Error (400)
{
    "error": "Invalid limit value."
}
```

**Parameters:**

-   `limit` (optional): Số lượng khách hàng trả về. Mặc định: 20

---

## **🎟️ 4. THỐNG KÊ DOANH THU THEO COUPON**

### **4.1 Doanh thu với coupon**

```http
GET /api/revenue/coupons

# Response Success (200)
{
    "data": [
        {
            "coupon_code": "WELCOME10",
            "discount_percentage": 10.00,
            "discount_amount": null,
            "usage_count": 150,
            "total_revenue_after_discount": "13500000.00",
            "total_discount_given": "1500000.00"
        },
        {
            "coupon_code": "FREESHIP",
            "discount_percentage": null,
            "discount_amount": "30000.00",
            "usage_count": 200,
            "total_revenue_after_discount": "10000000.00",
            "total_discount_given": "6000000.00"
        },
        {
            "coupon_code": "NO_COUPON",
            "discount_percentage": null,
            "discount_amount": null,
            "usage_count": 500,
            "total_revenue_after_discount": "25000000.00",
            "total_discount_given": "0.00"
        }
    ]
}

# Response Error (500)
{
    "error": "Database query failed: [error message]"
}
```

---

## **📈 5. TỔNG QUAN DASHBOARD**

### **5.1 Thống kê tổng quan**

```http
GET /api/revenue/dashboard?start_date=01/09/2025&end_date=05/09/2025

# Response Success (200)
{
    "data": {
        "overview": {
            "total_revenue": "50000000.00",
            "total_orders": 850,
            "unique_customers": 420,
            "avg_order_value": "588235.29",
            "highest_order": "2500000.00",
            "lowest_order": "50000.00"
        },
        "order_status": [
            {
                "status": "completed",
                "count": 750,
                "total_amount": "45000000.00"
            },
            {
                "status": "pending",
                "count": 50,
                "total_amount": "3000000.00"
            },
            {
                "status": "cancelled",
                "count": 50,
                "total_amount": "2000000.00"
            }
        ]
    }
}

# Response Error (400)
{
    "error": "Invalid date format. Please use d/m/Y (e.g., 04/09/2025)."
}
```

**Parameters:**

-   `start_date` (optional): Ngày bắt đầu (format: d/m/Y)
-   `end_date` (optional): Ngày kết thúc (format: d/m/Y)

---

## **🔧 Common Error Responses**

```json
// 400 - Bad Request
{
    "error": "Invalid date format. Please use d/m/Y (e.g., 04/09/2025)."
}

// 500 - Database Error
{
    "error": "Database query failed: [specific error message]"
}

// 500 - Unexpected Error
{
    "error": "An unexpected error occurred: [specific error message]"
}
```

## **📝 Notes**

-   Tất cả dữ liệu doanh thu chỉ tính từ các đơn hàng có `payments.status = 'completed'`
-   Định dạng ngày tháng sử dụng: `d/m/Y` (VD: 05/09/2025)
-   Số tiền trả về dưới dạng string để đảm bảo độ chính xác
-   Các API hỗ trợ xử lý lỗi chi tiết với mã HTTP status code phù hợp

## 🤖 Chatbot Text-to-SQL

<div align="center">

![Chatbot](https://img.shields.io/badge/🤖-AI%20Chatbot-blueviolet?style=for-the-badge)
![Text2SQL](https://img.shields.io/badge/Text--to--SQL-Gemini%20AI-orange?style=for-the-badge)

</div>

### 🎯 **Tổng quan**

Pizza Shop tích hợp **Chatbot thông minh hỗ trợ Text-to-SQL** — cho phép người dùng tìm kiếm và lọc sản phẩm pizza **bằng ngôn ngữ tự nhiên tiếng Việt**, thay vì phải thao tác thủ công trên bộ lọc.

> 💡 Ví dụ: Người dùng nhập *"Tôi muốn tìm pizza phô mai giá dưới 150.000đ, đế mỏng, cỡ vừa"* → Chatbot hiểu yêu cầu, tự động tạo câu truy vấn SQL phù hợp và trả về danh sách sản phẩm khớp.

---

### 🏗️ **Kiến trúc hệ thống Chatbot**

```mermaid
sequenceDiagram
    participant U as 👤 Người dùng
    participant FE as 🌐 Frontend
    participant API as ⚙️ Laravel API
    participant AI as 🤖 Gemini AI
    participant DB as 🗄️ MySQL

    U->>FE: Nhập câu hỏi tự nhiên
    FE->>API: POST /api/chatbot/query
    API->>AI: Gửi prompt + schema DB
    AI-->>API: Trả về câu SQL
    API->>DB: Thực thi SQL (safe mode)
    DB-->>API: Kết quả sản phẩm
    API-->>FE: JSON response
    FE-->>U: Hiển thị danh sách pizza phù hợp
```

---

### ✨ **Tính năng nổi bật của Chatbot**

<table>
<tr>
<td width="50%">

#### 🗣️ **Hiểu ngôn ngữ tự nhiên**

- Hỗ trợ câu hỏi **tiếng Việt** và **tiếng Anh**
- Nhận dạng ý định người dùng (intent detection)
- Xử lý câu hỏi mơ hồ và thiếu thông tin
- Gợi ý câu hỏi tiếp theo thông minh

</td>
<td width="50%">

#### 🔍 **Lọc sản phẩm thông minh**

- Lọc theo **tên**, **danh mục**, **giá**
- Lọc theo **kích thước** (size) và **loại đế** (crust)
- Kết hợp nhiều điều kiện cùng lúc
- Hỗ trợ tìm theo **combo** và **khuyến mãi**

</td>
</tr>
<tr>
<td width="50%">

#### 🛡️ **Bảo mật SQL**

- Chỉ cho phép câu lệnh **SELECT** (read-only)
- Whitelist các bảng được phép truy vấn
- Validate và sanitize SQL trước khi thực thi
- Giới hạn số dòng kết quả trả về

</td>
<td width="50%">

#### ⚡ **Hiệu suất cao**

- Cache kết quả truy vấn phổ biến với **Redis**
- Giới hạn **rate limiting** theo IP/user
- Thời gian phản hồi trung bình < 2 giây
- Fallback về tìm kiếm thủ công nếu AI lỗi

</td>
</tr>
</table>

---

### 📝 **Các dạng câu hỏi được hỗ trợ**

| 🗂️ **Loại truy vấn** | 💬 **Ví dụ câu hỏi** | 🎯 **Kết quả** |
| :------------------: | :------------------- | :------------- |
| Tìm theo tên | *"Có pizza Margherita không?"* | Sản phẩm khớp tên |
| Tìm theo giá | *"Pizza nào dưới 100.000đ?"* | Lọc theo giá variant |
| Tìm theo danh mục | *"Cho tôi xem pizza hải sản"* | Lọc theo category |
| Tìm theo kích thước | *"Pizza cỡ gia đình có gì?"* | Lọc theo size |
| Tìm theo đế bánh | *"Pizza đế không gluten"* | Lọc theo crust type |
| Tìm combo | *"Combo nào đang khuyến mãi?"* | Combos đang active |
| Kết hợp | *"Pizza phô mai đế mỏng giá dưới 150k cỡ vừa"* | Multi-filter query |
| Gợi ý | *"Pizza nào bán chạy nhất?"* | Top sản phẩm theo doanh số |

---

### 🔧 **API Endpoint Chatbot**

```http
POST /api/chatbot/query
Content-Type: application/json
```

**Request Body:**

```json
{
  "message": "Tôi muốn tìm pizza phô mai đế mỏng giá dưới 150.000đ",
  "conversation_id": "uuid-optional",
  "language": "vi"
}
```

**Response thành công (200):**

```json
{
  "success": true,
  "message": "Tìm thấy 3 sản phẩm phù hợp với yêu cầu của bạn!",
  "sql_generated": "SELECT p.name, pv.price, s.name as size, c.name as crust FROM products p JOIN product_variants pv ON p.id = pv.product_id JOIN categories cat ON p.category_id = cat.id JOIN sizes s ON pv.size_id = s.id JOIN crusts c ON pv.crust_id = c.id WHERE cat.name LIKE '%phô mai%' AND c.name LIKE '%mỏng%' AND pv.price < 150000 LIMIT 20",
  "results": [
    {
      "id": 3,
      "name": "Pizza 4 Phô Mai",
      "image_url": "https://...",
      "size": "Vừa (25cm)",
      "crust": "Đế Mỏng",
      "price": 135000,
      "stock": 15
    }
  ],
  "total": 3,
  "suggestions": [
    "Bạn có muốn xem thêm pizza trong khoảng giá 150k - 200k?",
    "Tôi cũng có Combo Đôi với pizza phô mai, bạn có muốn xem không?"
  ]
}
```

**Response khi không tìm thấy (200):**

```json
{
  "success": true,
  "message": "Không tìm thấy sản phẩm phù hợp. Bạn thử tìm kiếm với tiêu chí khác nhé!",
  "results": [],
  "total": 0,
  "suggestions": [
    "Thử tìm pizza phô mai với kích thước khác?",
    "Xem tất cả pizza hiện có?"
  ]
}
```

**Response lỗi (422):**

```json
{
  "success": false,
  "error": "Câu hỏi không rõ ràng, vui lòng mô tả chi tiết hơn.",
  "hint": "Ví dụ: 'Pizza hải sản cỡ lớn giá dưới 200k'"
}
```

---

### 🏛️ **Schema các bảng Chatbot được phép truy vấn**

```yaml
Whitelisted Tables:
  ✅ products          # Tên, mô tả, hình ảnh sản phẩm
  ✅ product_variants  # Giá, tồn kho, biến thể
  ✅ categories        # Danh mục sản phẩm
  ✅ sizes             # Kích thước pizza
  ✅ crusts            # Loại đế bánh
  ✅ combos            # Combo đang hoạt động
  ✅ combo_items       # Chi tiết sản phẩm trong combo
  ✅ reviews           # Đánh giá sản phẩm (avg rating)

Blacklisted Tables (không được truy vấn):
  ❌ users             # Thông tin cá nhân khách hàng
  ❌ orders            # Lịch sử đơn hàng
  ❌ payments          # Thông tin thanh toán
  ❌ contacts          # Dữ liệu liên hệ
  ❌ password_resets   # Token reset mật khẩu
```

---

### 🔄 **Luồng xử lý Text-to-SQL chi tiết**

```
1. 📥 Nhận câu hỏi từ người dùng
       ↓
2. 🧹 Tiền xử lý: chuẩn hóa tiếng Việt, loại bỏ ký tự đặc biệt
       ↓
3. 🤖 Gửi đến Gemini AI với:
       - System prompt: mô tả schema DB + quy tắc bảo mật
       - User message: câu hỏi đã chuẩn hóa
       ↓
4. 📝 Nhận câu SQL từ AI
       ↓
5. 🛡️ Kiểm tra bảo mật SQL:
       - Chỉ cho phép SELECT
       - Validate tên bảng & cột
       - Thêm LIMIT nếu thiếu
       ↓
6. ⚡ Kiểm tra Redis cache
       ↓ (cache miss)
7. 🗄️ Thực thi truy vấn trên MySQL
       ↓
8. 💾 Lưu kết quả vào Redis (TTL: 5 phút)
       ↓
9. 📤 Format và trả về JSON response
```

---

### ⚙️ **Cấu hình Chatbot trong `.env`**

```env
# Gemini AI Configuration
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-2.0-flash
GEMINI_MAX_TOKENS=1024
GEMINI_TEMPERATURE=0.1

# Chatbot Settings
CHATBOT_MAX_RESULTS=20
CHATBOT_CACHE_TTL=300
CHATBOT_RATE_LIMIT=30
```

---

## 🔒 Bảo mật

### 🛡️ **Security Measures**

<table>
<tr>
<td width="50%">

#### 🔐 **Authentication & Authorization**

-   ✅ **Password Hashing**: bcrypt with salt
-   ✅ **JWT Tokens**: Secure & stateless
-   ✅ **Role-based Access**: Admin vs Customer
-   ✅ **Session Management**: Secure sessions
-   ✅ **Input Validation**: All inputs sanitized

</td>
<td width="50%">

#### 🛡️ **Data Protection**

-   ✅ **SQL Injection**: Prepared statements only
-   ✅ **XSS Protection**: Input sanitization
-   ✅ **CSRF Protection**: CSRF tokens
-   ✅ **HTTPS Enforcement**: SSL/TLS required
-   ✅ **Database Triggers**: Business logic protection

</td>
</tr>
</table>

### 🔍 **Security Features**

-   [x] 🔒 **HTTPS Only** - All communications encrypted
-   [x] 🛡️ **Input Validation** - All user inputs validated & sanitized
-   [x] 🔐 **Secure Headers** - Security headers implemented
-   [x] 🚫 **Rate Limiting** - Prevent abuse & DDoS attacks
-   [x] 📝 **Audit Logs** - Track all admin actions
-   [x] 🔄 **Regular Updates** - Dependencies kept current
-   [x] 🧪 **Security Testing** - Regular vulnerability scanning
-   [x] 💾 **Backup Strategy** - Regular encrypted backups
-   [x] 🎯 **Database Constraints** - Business rules enforced at DB level
-   [x] 💳 **Payment Security** - PCI DSS compliance for card payments

---

## 🎯 Roadmap

### 🚀 **Version 1.1 (Next Release)**

-   [ ] 📱 **Mobile App** (React Native/Flutter)
-   [ ] 🔔 **Push Notifications** real-time
-   [ ] 🗺️ **Live Delivery Tracking** with GPS
-   [ ] 💬 **Live Chat Support** customer service
-   [ ] 🎁 **Loyalty Points System** advanced rewards
-   [ ] 🤖 **Chatbot** for FAQ automation

### 🌟 **Version 1.2 (Future)**

-   [ ] 🤖 **AI Recommendations** based on order history
-   [ ] 🎨 **Pizza Builder Tool** drag & drop toppings
-   [ ] 📊 **Advanced Analytics** with ML insights
-   [ ] 🌐 **Multi-language Support** (EN, VI, JP)
-   [ ] 💰 **Cryptocurrency Payments** (Bitcoin, Ethereum)
-   [ ] 📸 **AR Pizza Preview** augmented reality

### 🔮 **Version 2.0 (Long-term)**

-   [ ] 🏪 **Multi-store Management** franchise system
-   [ ] 🤝 **B2B Portal** for corporate clients
-   [ ] 📱 **POS Integration** in-store system
-   [ ] 🚁 **Drone Delivery** automated delivery
-   [ ] 🌍 **International Expansion** multi-currency
-   [ ] 🔮 **IoT Integration** smart kitchen equipment

---

## 🤝 Contributing

### 💝 **How to Contribute**

<div align="center">

[![Contribute](https://img.shields.io/badge/🤝-Contribute-blue?style=for-the-badge)](CONTRIBUTING.md)
[![Issues](https://img.shields.io/badge/🐛-Report%20Bug-red?style=for-the-badge)](https://github.com/pizzashop/issues)
[![Feature Request](https://img.shields.io/badge/💡-Request%20Feature-green?style=for-the-badge)](https://github.com/pizzashop/issues)

</div>

1. 🍴 **Fork** the repository
2. 🌿 **Create** your feature branch (`git checkout -b feature/AmazingFeature`)
3. 💾 **Commit** your changes (`git commit -m 'Add some AmazingFeature'`)
4. 📤 **Push** to the branch (`git push origin feature/AmazingFeature`)
5. 🔀 **Open** a Pull Request

### 📝 **Development Guidelines**

```yaml
Code Style: ✅ Follow PSR-12 for PHP
    ✅ ESLint rules for JavaScript
    ✅ Meaningful variable names
    ✅ Comprehensive comments

Database: ✅ All changes via migrations
    ✅ Maintain referential integrity
    ✅ Add proper indexes
    ✅ Test triggers thoroughly

Testing: ✅ Unit tests for business logic
    ✅ Integration tests for APIs
    ✅ Database constraint testing
    ✅ Security vulnerability tests
```

### 👥 **Contributors**

<div align="center">

<a href="https://github.com/pizzashop/contributors">
  <img src="https://contrib.rocks/image?repo=pizzashop/pizzashop" />
</a>

**Made with ❤️ by our amazing contributors**

</div>

---

## 📞 Support & Contact

<div align="center">

### 🆘 **Need Help?**

[![Email](https://img.shields.io/badge/📧-support@pizzashop.com-blue?style=for-the-badge)](mailto:support@pizzashop.com)
[![Discord](https://img.shields.io/badge/💬-Discord-7289da?style=for-the-badge&logo=discord&logoColor=white)](https://discord.gg/pizzashop)
[![Documentation](https://img.shields.io/badge/📖-Documentation-green?style=for-the-badge)](https://docs.pizzashop.com)

### 📱 **Hotline**: 1900-PIZZA (24/7)

</div>

---

## 📄 License

```
MIT License

Copyright (c) 2025 Pizza Shop Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

---

<div align="center">

## 🏆 **Pizza Shop - Delivering Happiness, One Slice at a Time!**

[![GitHub stars](https://img.shields.io/github/stars/pizzashop/pizzashop?style=social)](https://github.com/pizzashop/pizzashop/stargazers)
[![GitHub forks](https://img.shields.io/github/forks/pizzashop/pizzashop?style=social)](https://github.com/pizzashop/pizzashop/network)
[![GitHub watchers](https://img.shields.io/github/watchers/pizzashop/pizzashop?style=social)](https://github.com/pizzashop/pizzashop/watchers)

**⭐ Nếu project này hữu ích, đừng quên give us a star! ⭐**

---

_Made with 🍕 and ❤️ by Pizza Shop Team_

**© 2025 Pizza Shop. All rights reserved.**

</div>
