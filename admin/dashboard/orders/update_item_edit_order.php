<?php
include '../../../config/database.php';

if (isset($_POST['item_id']) && isset($_POST['quantity']) && isset($_POST['size_id'])) {
    $itemId = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    $sizeId = intval($_POST['size_id']);

    // Cập nhật số lượng và size trong bảng order_items
    $sql = "UPDATE order_items SET quantity = ?, size_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $quantity, $sizeId, $itemId);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Có lỗi xảy ra: " . $conn->error;
    }

    $stmt->close();
}
$conn->close();
?>