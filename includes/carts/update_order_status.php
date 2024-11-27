<?php
include '../../config/database.php';

// Nhận dữ liệu từ phía JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['order_id'])) {
    $order_id = $data['order_id'];

    // Cập nhật trạng thái đơn hàng
    $update_query = "UPDATE orders SET status = 'completed' WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Không tìm thấy order_id.']);
}
$conn->close();
?>