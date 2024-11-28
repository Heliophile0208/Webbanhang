<?php
include '../../../config/database.php';

if (isset($_POST['item_id'])) {
    $itemId = intval($_POST['item_id']);

    // Xóa sản phẩm trong bảng order_items
    $sql = "DELETE FROM order_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $itemId);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Có lỗi xảy ra: " . $conn->error;
    }

    $stmt->close();
}
$conn->close();
?>