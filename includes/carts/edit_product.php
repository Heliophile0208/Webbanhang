<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Thông Tin Sản Phẩm trong Giỏ Hàng</title>
    <link rel="stylesheet" href="../../css/cart.css">
    <link rel="stylesheet" href="../../css/header.css">
</head>
<body>
    <?php
    session_start();
    include '../../config/database.php';
    include '../header.php';

    // Kiểm tra nếu người dùng đã đăng nhập
    $username = $_SESSION['username'] ?? '';
    if (empty($username)) {
        echo "<main class='notification'><p>Vui lòng đăng nhập để sửa sản phẩm trong giỏ hàng.</p></main>";
        exit;
    }

    // Lấy product_id từ URL
    $product_id = $_GET['product_id'] ?? '';
    if (empty($product_id)) {
        echo "<main class='notification'><p>Sản phẩm không tồn tại.</p></main>";
        exit;
    }

    // Truy vấn để lấy thông tin sản phẩm trong giỏ hàng
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
        echo "<main class='notification'><p>Sản phẩm không tồn tại trong giỏ hàng của bạn.</p></main>";
        exit;
    }

    // Lấy danh sách kích thước có sẵn cho sản phẩm từ bảng product_sizes
    $sizes_query = "
        SELECT s.id, s.size
        FROM size s
        JOIN product_sizes ps ON ps.size_id = s.id
        WHERE ps.product_id = ?
    ";
    $sizes_stmt = $conn->prepare($sizes_query);
    $sizes_stmt->bind_param("i", $product_id);
    $sizes_stmt->execute();
    $sizes_result = $sizes_stmt->get_result();
    $sizes_stmt->close();

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

        echo "<main class='notification'><p>Thông tin sản phẩm đã được cập nhật.</p></main>";
        header("Location: cart.php"); // Quay lại trang giỏ hàng
        exit;
    }
    ?>

    <h2>Sửa thông tin sản phẩm trong giỏ hàng</h2>
    <form class='form-edit' method="POST">
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

    <?php include '../footer.php'; ?> <!-- Bao gồm footer nếu cần -->
</body>
</html>