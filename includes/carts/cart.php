<?php
session_start();
include '../header.php';
include '../../config/database.php';

// Lấy username từ session
$username = $_SESSION['username'] ?? '';

// Kiểm tra nếu username không tồn tại trong session
if (empty($username)) {
    echo "<p>Vui lòng đăng nhập để xem giỏ hàng.</p>";
    exit;
}

// Truy vấn để lấy user_id từ bảng users dựa vào username
$user_query = "SELECT id FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_stmt->bind_result($user_id);
$user_stmt->fetch();
$user_stmt->close();

// Kiểm tra nếu không có user_id
if (!$user_id) {
    echo "<p>Không tìm thấy người dùng trong hệ thống.</p>";
    exit;
}

// Truy vấn để lấy order_id của đơn hàng chưa hoàn thành (pending) của người dùng
$order_query = "SELECT id FROM orders WHERE user_id = ? AND status = 'pending'";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_stmt->bind_result($order_id);
$order_stmt->fetch();
$order_stmt->close();

// Kiểm tra nếu không có order_id
if (!$order_id) {
    echo "<p>Không tìm thấy giỏ hàng của bạn.</p>";
    exit;
}

// Truy vấn thông tin sản phẩm trong giỏ hàng
$product_query = "
    SELECT p.id, p.name, p.price, op.quantity, op.size_id 
    FROM order_items op
    JOIN products p ON op.product_id = p.id
    WHERE op.order_id = ?
";
$product_stmt = $conn->prepare($product_query);
$product_stmt->bind_param("i", $order_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

// Kiểm tra nếu giỏ hàng trống
if ($product_result->num_rows === 0) {
    echo "<p>Giỏ hàng của bạn trống.</p>";
    exit;
}

// Hiển thị giỏ hàng
echo "<h2>Giỏ hàng của bạn</h2>";
echo "<table border='1' style='width: 100%; text-align: center;'>
        <thead>
            <tr>
                <th>Tên sản phẩm</th>
                <th>Số lượng</th>
                <th>Kích thước / Size</th>
                <th>Giá</th>
                <th>Tổng tiền</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>";

$total_price = 0;

// Duyệt qua các sản phẩm trong giỏ hàng
while ($product = $product_result->fetch_assoc()) {
    $total_price += $product['quantity'] * $product['price'];

    // Lấy các kích thước có sẵn từ bảng sizes
    $sizes_query = "SELECT id, size FROM size";
    $sizes_result = $conn->query($sizes_query);

    // Tạo dropdown cho size_id
    $size_options = '';
    while ($size = $sizes_result->fetch_assoc()) {
        $selected = ($product['size_id'] == $size['id']) ? 'selected' : '';
        $size_options .= "<option value='{$size['id']}' $selected>{$size['size']}</option>";
    }

    // Hiển thị thông tin sản phẩm trong giỏ hàng
    echo "<tr>
            <td>{$product['name']}</td>
            <td>{$product['quantity']}</td>
            <td>
                <select disabled>
                    $size_options
                </select>
            </td>
            <td>" . number_format($product['price'], 0, ',', '.') . " VND</td>
            <td>" . number_format($product['quantity'] * $product['price'], 0, ',', '.') . " VND</td>
            <td>
                <a href='delete_product.php?product_id={$product['id']}'>Xóa</a> |
                <a href='edit_product.php?product_id={$product['id']}'>Sửa</a>
            </td>
        </tr>";
}

echo "<tr><td colspan='4'>Tổng tiền</td><td colspan='2'>" . number_format($total_price, 0, ',', '.') . " VND</td>";
echo "</table>";

// Nút chuyển hướng checkout
echo "<a href='checkout.php' class='checkout-btn'>Thanh toán</a>";

?>
<style>


/* Style chung cho giỏ hàng */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

h2 {
    text-align: center;
    color: #333;
    margin-top: 20px;
}

/* Bảng giỏ hàng */
table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
}

th {
    background-color: #007BFF;
    color: white;
    font-weight: bold;
}

td {
    background-color: #f9f9f9;
}

/* Link Xóa và Sửa */
a {
    color: #007BFF;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Nút chuyển hướng checkout */
.checkout-btn {
    display: block;
    width: 200px;
    margin: 20px auto;
    padding: 10px;
    background-color: #28a745;
    color: white;
    text-align: center;
    font-size: 16px;
    border-radius: 5px;
    text-decoration: none;
}

.checkout-btn:hover {
    background-color: #218838;
}

footer {
    text-align: center;
    margin-top: 40px;
    font-size: 14px;
    color: #555;
}
</style>