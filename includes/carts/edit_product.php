<?php
session_start();
include '../header.php';
include '../../config/database.php';

// Kiểm tra nếu người dùng đã đăng nhập
$username = $_SESSION['username'] ?? '';
if (empty($username)) {
    echo "<p>Vui lòng đăng nhập để sửa sản phẩm trong giỏ hàng.</p>";
    exit;
}

// Lấy product_id từ URL
$product_id = $_GET['product_id'] ?? '';
if (empty($product_id)) {
    echo "<p>Sản phẩm không tồn tại.</p>";
    exit;
}

// Truy vấn để lấy thông tin sản phẩm
$product_query = "
    SELECT op.id, p.name, op.quantity, op.size_id, p.price
    FROM order_items op
    JOIN products p ON op.product_id = p.id
    WHERE op.product_id = ? AND op.order_id IN (SELECT id FROM orders WHERE user_id = (SELECT id FROM users WHERE username = ?) AND status = 'pending')
";
$product_stmt = $conn->prepare($product_query);
$product_stmt->bind_param("is", $product_id, $username);
$product_stmt->execute();
$product_stmt->bind_result($order_item_id, $product_name, $quantity, $size_id, $price);
$product_stmt->fetch();
$product_stmt->close();

// Nếu không có sản phẩm trong giỏ hàng
if (!$order_item_id) {
    echo "<p>Sản phẩm không tồn tại trong giỏ hàng của bạn.</p>";
    exit;
}

// Lấy danh sách kích thước từ bảng sizes
$sizes_query = "SELECT id, size FROM size";
$sizes_result = $conn->query($sizes_query);

// Xử lý khi form được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_quantity = $_POST['quantity'] ?? $quantity;
    $new_size_id = $_POST['size_id'] ?? $size_id;

    // Cập nhật thông tin sản phẩm trong giỏ hàng
    $update_query = "
        UPDATE order_items
        SET quantity = ?, size_id = ?
        WHERE id = ?
    ";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("iii", $new_quantity, $new_size_id, $order_item_id);
    $update_stmt->execute();
    $update_stmt->close();

    echo "<p>Thông tin sản phẩm đã được cập nhật.</p>";
    header("Location: cart.php"); // Quay lại trang giỏ hàng
    exit;
}

?>

<h2>Sửa thông tin sản phẩm trong giỏ hàng</h2>
<form method="POST">
    <label for="name">Tên sản phẩm:</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($product_name) ?>" disabled><br><br>

    <label for="quantity">Số lượng:</label>
    <input type="number" id="quantity" name="quantity" value="<?= $quantity ?>" min="1" required><br><br>

    <label for="size_id">Kích thước:</label>
    <select id="size_id" name="size_id" required>
        <?php while ($size = $sizes_result->fetch_assoc()): ?>
            <option value="<?= $size['id'] ?>" <?= ($size_id == $size['id']) ? 'selected' : '' ?>>
                <?= $size['size'] ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>


    <button type="submit">Cập nhật</button>

<!-- Liên kết quay lại giỏ hàng -->
<p style="text-align: center; margin-top: 20px;">
    <a href="cart.php" style="color: #007BFF; text-decoration: none;">Quay lại giỏ hàng</a>
</p>
</form>
<style>
/* Tổng quan trang */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

h2 {
    text-align: center;
    color: #333;
    margin-top: 30px;
}

/* Form sửa sản phẩm */
form {
    max-width: 500px;
    margin: 30px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

form label {
    font-size: 16px;
    font-weight: bold;
    display: block;
    margin-bottom: 10px;
    color: #333;
}

form input,
form select {
    width: 100%;
    padding: 10px;
    margin: 5px 0 20px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

form input[type="number"] {
    -moz-appearance: textfield;  /* Remove number input arrows */
}

form button {
    background-color: #4CAF50;
    color: white;
    font-size: 18px;
    border: none;
    padding: 12px 20px;
    cursor: pointer;
    border-radius: 5px;
    width: 100%;
}

form button:hover {
    background-color: #45a049;
}

/* Thông báo lỗi và thành công */
p {
    text-align: center;
    font-size: 18px;
    color: #333;
    margin-top: 20px;
}

p.success {
    color: green;
}

p.error {
    color: red;
}

/* Thêm kiểu cho các trường bị lỗi */
form input:invalid,
form select:invalid {
    border-color: red;
}

form input:valid,
form select:valid {
    border-color: green;
}

</style>


<?php
include '../footer.php'; // Bao gồm footer nếu cần
?>