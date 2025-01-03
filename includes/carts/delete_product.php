<link rel="stylesheet" href="../../css/header.css">
<?php
session_start();
include '../../config/database.php';
include '../header.php';

// Kiểm tra nếu username có trong session
$username = $_SESSION['username'] ?? '';

// Kiểm tra nếu username không tồn tại trong session
if (empty($username)) {
    echo "<main class='notification'><p>Vui lòng đăng nhập để xóa sản phẩm.</p></main>";
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
    echo "<main class='notification'><p>Không tìm thấy người dùng trong hệ thống.</p></main>";
    exit;
}

// Lấy product_id từ URL
$product_id = $_GET['product_id'] ?? 0;
if (!$product_id) {
    echo "<main class='notification'><p>Không có sản phẩm để xóa.</p></main>";
    exit;
}

// Truy vấn để lấy order_id của đơn hàng chưa hoàn thành của người dùng
$order_query = "SELECT id FROM orders WHERE user_id = ? AND status = 'pending'";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_stmt->bind_result($order_id);
$order_stmt->fetch();
$order_stmt->close();

// Kiểm tra nếu không có đơn hàng nào
if (!$order_id) {
    echo "<main class='notification'><p>Giỏ hàng của bạn hiện trống.</p></main>";
    exit;
}

// Lấy id của sản phẩm từ bảng order_items
$get_item_id_query = "SELECT id FROM order_items WHERE order_id = ? AND product_id = ?";
$get_item_id_stmt = $conn->prepare($get_item_id_query);
$get_item_id_stmt->bind_param("ii", $order_id, $product_id);
$get_item_id_stmt->execute();
$get_item_id_stmt->bind_result($item_id);
$get_item_id_stmt->fetch();
$get_item_id_stmt->close();

// Nếu không tìm thấy sản phẩm trong giỏ, hiển thị thông báo
if (!$item_id) {
    echo "<main class='notification'><p>Sản phẩm không tồn tại trong giỏ hàng.</p></main>";
    exit;
}

// Xóa sản phẩm khỏi bảng order_items
$delete_query = "DELETE FROM order_items WHERE id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("i", $item_id); // Chỉ cần sử dụng item_id
$delete_stmt->execute();
$delete_stmt->close();

// Kiểm tra nếu giỏ hàng còn sản phẩm nào không
$check_products_query = "SELECT COUNT(*) FROM order_items WHERE order_id = ?";
$check_products_stmt = $conn->prepare($check_products_query);
$check_products_stmt->bind_param("i", $order_id);
$check_products_stmt->execute();
$check_products_stmt->bind_result($product_count);
$check_products_stmt->fetch();
$check_products_stmt->close();

// Nếu không còn sản phẩm, cập nhật tổng tiền là 0 và hiển thị thông báo
if ($product_count == 0) {
    $update_total_query = "UPDATE orders SET total = 0 WHERE id = ?";
    $update_total_stmt = $conn->prepare($update_total_query);
    $update_total_stmt->bind_param("i", $order_id);
    $update_total_stmt->execute();
    $update_total_stmt->close();
    
    echo "<main class='notification'><p>Giỏ hàng của bạn hiện trống. Bạn có thể tiếp tục mua sắm hoặc quay lại sau.</p></main>";
    exit;
} else {
    // Cập nhật lại tổng tiền trong đơn hàng nếu còn sản phẩm
    $update_total_query = "
        UPDATE orders 
        SET total = (
            SELECT SUM(quantity * price) 
            FROM order_items 
            WHERE order_items.order_id = orders.id
        )
        WHERE id = ?";
    $update_total_stmt = $conn->prepare($update_total_query);
    $update_total_stmt->bind_param("i", $order_id);
    $update_total_stmt->execute();
    $update_total_stmt->close();
}

// Chuyển hướng về trang giỏ hàng sau khi xóa sản phẩm
header("Location: cart.php");
exit();
?>
