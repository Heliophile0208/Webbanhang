<?php
session_start();

// Kết nối cơ sở dữ liệu
include '../../../config/database.php';

// Kiểm tra dữ liệu được gửi qua AJAX
if (isset($_POST['user_id']) && isset($_POST['username'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];

    // Kiểm tra xem người dùng có đơn hàng nào với status là 'pending' chưa
    $checkQuery = "SELECT id FROM orders WHERE user_id = ? AND status = 'pending'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Nếu đã có đơn hàng pending, trả về thông báo và cho phép thêm sản phẩm
        $order = $checkResult->fetch_assoc();
        echo json_encode([
            'status' => 'error',
            'message' => 'Người dùng đã có đơn hàng đang chờ.',
            'order_id' => $order['id'],
            'show_add_product_button' => true // Hiển thị nút thêm sản phẩm
        ]);
    } else {
        // Nếu không có đơn hàng pending, tạo đơn hàng mới
        // Lấy thời gian hiện tại
        $created_at = date('Y-m-d H:i:s');

        // Thêm đơn hàng vào bảng orders với status là 'pending' và total là 0
        $query = "INSERT INTO orders (user_id, status, total, created_at) VALUES (?, 'pending', 0, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $created_at);

        if ($stmt->execute()) {
            // Lấy ID đơn hàng vừa tạo
            $order_id = $stmt->insert_id;

            // Trả về kết quả thành công
            echo json_encode([
                'status' => 'success',
                'message' => 'Đơn hàng đã được tạo thành công.',
                'order_id' => $order_id,
                'show_add_product_button' => true // Hiển thị nút thêm sản phẩm
            ]);
        } else {
            // Trả về kết quả lỗi
            echo json_encode([
                'status' => 'error',
                'message' => 'Không thể tạo đơn hàng. Vui lòng thử lại.'
            ]);
        }

        $stmt->close();
    }

    $checkStmt->close();
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Dữ liệu không hợp lệ.'
    ]);
}

// Đóng kết nối
$conn->close();
?>