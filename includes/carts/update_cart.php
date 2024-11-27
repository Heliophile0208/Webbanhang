<?php
session_start();
include '../../config/database.php';

// Kiểm tra nếu user_id có trong session
$user_id = $_SESSION['user_id'] ?? 0;


// Nếu có yêu cầu cập nhật giỏ hàng (cập nhật số lượng hoặc size)
if (isset($_POST['quantity'])) {
    // Lấy order_id từ session
    $order_id = $_SESSION['order_id'] ?? null;
    if (!$order_id) {
        // Nếu chưa có order_id trong session, tạo mới
        if ($user_id) {
            $insert_order_query = "INSERT INTO orders (user_id, status) VALUES (?, 'pending')";
            $insert_order_stmt = $conn->prepare($insert_order_query);
            $insert_order_stmt->bind_param("i", $user_id);
            $insert_order_stmt->execute();
            $order_id = $insert_order_stmt->insert_id;  // Lấy order_id vừa tạo
            $insert_order_stmt->close();

            // Lưu order_id vào session
            $_SESSION['order_id'] = $order_id;
        }
    }

    // Tiến hành xử lý cập nhật giỏ hàng
    if ($order_id) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            $size_id = $_POST['size_id'][$product_id];

            // Kiểm tra nếu sản phẩm đã tồn tại trong order_items
            $query = "SELECT quantity FROM order_items WHERE order_id = ? AND product_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $order_id, $product_id);
            $stmt->execute();
            $stmt->bind_result($existing_quantity);
            $stmt->fetch();
            $stmt->close();

            if ($quantity > 0) {
                if ($existing_quantity) {
                    // Ghi đè số lượng mới trong order_items
                    $update_query = "UPDATE order_items SET quantity = ?, size_id = ? WHERE order_id = ? AND product_id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("iiii", $quantity, $size_id, $order_id, $product_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                } else {
                    // Thêm sản phẩm mới vào order_items nếu chưa có
                    $price = $_SESSION['cart'][$product_id]['price'];
                    $insert_query = "INSERT INTO order_items (order_id, product_id, quantity, price, size_id) VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("iiiii", $order_id, $product_id, $quantity, $price, $size_id);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                }

                // Cập nhật lại session
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
                $_SESSION['cart'][$product_id]['size_id'] = $size_id;
            }
        }

        // Cập nhật tổng tiền đơn hàng trong bảng orders
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

    // Chuyển hướng lại về trang giỏ hàng
    header("Location: cart.php");
    exit();
}
?>