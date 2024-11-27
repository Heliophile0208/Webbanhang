<?php
include '../../../config/database.php';

// Kiểm tra nếu có dữ liệu từ AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy thông tin từ yêu cầu POST
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $size_id = isset($_POST['size']) ? intval($_POST['size']) : 0;

    // Kiểm tra dữ liệu hợp lệ
    if ($item_id > 0 && $quantity > 0) {
        // Cập nhật số lượng và size trong bảng order_items
        $sql = "UPDATE order_items SET quantity = ?, size_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        // Kiểm tra nếu có lỗi trong quá trình chuẩn bị câu lệnh
        if ($stmt === false) {
            echo "Lỗi khi chuẩn bị câu lệnh: " . $conn->error;
            exit;
        }

        $stmt->bind_param("iii", $quantity, $size_id, $item_id);
        
        // Thực thi câu lệnh SQL
        if ($stmt->execute()) {
            // Trả về "success" nếu cập nhật thành công
            echo "success";
        } else {
            // Nếu có lỗi xảy ra trong quá trình thực thi
            echo "Lỗi khi cập nhật sản phẩm.";
        }
        
        // Đóng câu lệnh chuẩn bị
        $stmt->close();
    } else {
        echo "Dữ liệu không hợp lệ.";
    }
} else {
    echo "Yêu cầu không hợp lệ.";
}

$conn->close();
?>