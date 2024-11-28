<?php
include '../../../config/database.php';
session_start();

// Kiểm tra xem dữ liệu có được gửi qua POST không
if (isset($_POST['product_id'], $_POST['order_id'], $_POST['quantity'], $_POST['size_id'])) {
    $product_id = $_POST['product_id'];
    $order_id = $_POST['order_id'];
    $quantity = $_POST['quantity'];
    $size_id = $_POST['size_id'];

    // Lấy giá sản phẩm từ bảng products
    $getPriceQuery = "SELECT price FROM products WHERE id = ?";
    $stmt = $conn->prepare($getPriceQuery);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $productResult = $stmt->get_result();
    $product = $productResult->fetch_assoc();
    $price = $product['price'];  // Lấy giá sản phẩm

    // Kiểm tra xem giỏ hàng có tồn tại cho order_id không
    // Nếu chưa, tạo giỏ hàng mới
    $checkOrderQuery = "SELECT id FROM orders WHERE id = ?";
    $stmt = $conn->prepare($checkOrderQuery);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $orderResult = $stmt->get_result();

    if ($orderResult->num_rows == 0) {
        // Nếu không có đơn hàng, tạo một đơn hàng mới
        $createOrderQuery = "INSERT INTO orders (status) VALUES ('pending')";
        $conn->query($createOrderQuery);
        $order_id = $conn->insert_id;  // Lấy ID của đơn hàng mới
    }

    // Kiểm tra xem sản phẩm và size đã tồn tại trong giỏ hàng chưa
    $checkProductQuery = "SELECT id FROM order_items WHERE product_id = ? AND order_id = ? AND size_id = ?";
    $stmt = $conn->prepare($checkProductQuery);
    $stmt->bind_param("iii", $product_id, $order_id, $size_id);
    $stmt->execute();
    $productResult = $stmt->get_result();

    if ($productResult->num_rows > 0) {
        // Nếu sản phẩm đã có trong giỏ, tăng số lượng
        $updateQuery = "UPDATE order_items SET quantity = quantity + ?, price = ? WHERE product_id = ? AND order_id = ? AND size_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("iiiii", $quantity, $price, $product_id, $order_id, $size_id);
        $stmt->execute();
    } else {
        // Nếu sản phẩm chưa có trong giỏ, thêm sản phẩm mới vào giỏ
        $insertQuery = "INSERT INTO order_items (order_id, product_id, quantity, size_id, price) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("iiiid", $order_id, $product_id, $quantity, $size_id, $price);
        $stmt->execute();
    }

    // Tính toán tổng tiền cho đơn hàng
    $totalQuery = "SELECT SUM(quantity * price) AS total FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($totalQuery);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $total = $totalResult->fetch_assoc()['total'];

    // Cập nhật tổng tiền vào bảng orders
    $updateOrderQuery = "UPDATE orders SET total = ? WHERE id = ?";
    $stmt = $conn->prepare($updateOrderQuery);
    $stmt->bind_param("di", $total, $order_id);
    $stmt->execute();

    echo "success";  // Trả về phản hồi thành công
} else {
    echo "error";  // Trả về lỗi nếu không nhận được dữ liệu
}
?>