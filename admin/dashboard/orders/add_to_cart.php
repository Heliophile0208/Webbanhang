<?php
include '../../../config/database.php';

// Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ request
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Kiểm tra dữ liệu hợp lệ
    if ($product_id > 0 && $order_id > 0 && $quantity > 0) {
        // Kiểm tra nếu sản phẩm đã có trong giỏ hàng
        $checkQuery = "SELECT * FROM cart WHERE product_id = ? AND order_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $product_id, $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Cập nhật số lượng sản phẩm nếu đã có trong giỏ
            $updateQuery = "UPDATE cart SET quantity = quantity + ? WHERE product_id = ? AND order_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("iii", $quantity, $product_id, $order_id);
            if ($updateStmt->execute()) {
                echo "success";
            } else {
                echo "error";
            }
        } else {
            // Thêm sản phẩm mới vào giỏ hàng
            $insertQuery = "INSERT INTO cart (product_id, order_id, quantity) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iii", $product_id, $order_id, $quantity);
            if ($insertStmt->execute()) {
                echo "success";
            } else {
                echo "error";
            }
        }
    } else {
        echo "invalid data";
    }
} else {
    echo "invalid request method";
}
?>