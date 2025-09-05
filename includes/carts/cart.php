<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng</title>
    <link rel="stylesheet" href="../../css/cart.css" type="text/css">
    <link rel="stylesheet" href="../../css/header.css" type="text/css">
</head>
<body>
    <?php
  
    include '../header.php';
    include '../../config/database.php';

    // Lấy username từ session
    $username = $_SESSION['username'] ?? '';

    // Kiểm tra nếu username không tồn tại trong session
    if (empty($username)) {
        echo "<section class='notification'><p>Vui lòng đăng nhập để xem giỏ hàng.</p></section>";
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
        echo "<section class='notification'><p>Không tìm thấy người dùng trong hệ thống.</p></section>";
        exit;
    }

    // Truy vấn để lấy order_id của đơn hàng chưa hoàn thành (pending)
    $order_query = "SELECT id FROM orders WHERE user_id = ? AND status = 'pending'";
    $order_stmt = $conn->prepare($order_query);
    $order_stmt->bind_param("i", $user_id);
    $order_stmt->execute();
    $order_stmt->bind_result($order_id);
    $order_stmt->fetch();
    $order_stmt->close();

    // Kiểm tra nếu không có order_id
    if (!$order_id) {
        echo "<section class='empty-cart'><p>Giỏ hàng của bạn trống.</p></section>";
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
        echo "<section class='empty-cart'><p>Giỏ hàng của bạn trống.</p></section>";
        exit;
    }
    ?>

    <main>
        <h2>Giỏ hàng của bạn</h2>
        <section class="cart-container">
            <table>
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
                <tbody>
                    <?php
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

                        echo "
                            <tr>
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
                            </tr>
                        ";
                    }
                    ?>
                    <tr>
                        <td colspan="4">Tổng tiền</td>
                        <td colspan="2"><?php echo number_format($total_price, 0, ',', '.'); ?> VND</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="checkout">
            <a href="checkout.php" class="checkout-btn">Thanh toán</a>
        </section>
    </main>
</body>
</html>
