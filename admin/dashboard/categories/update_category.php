<?php
session_start();
include_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $CategoryID = $_POST['CategoryID'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $group = $_POST['group'];

    // Kiểm tra xem CategoryID có tồn tại trong cơ sở dữ liệu
    $checkQuery = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $CategoryID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu có thay đổi, cập nhật thông tin thể loại
        $updateQuery = "UPDATE categories SET name = ?, description = ?, `group` = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssi", $name, $description, $group, $CategoryID);

        if ($stmt->execute()) {
            // Trả về thông báo thành công dưới dạng text
            echo "success";
        } else {
            echo "Lỗi khi cập nhật: " . $conn->error;
        }
    } else {
        echo "Thể loại không tồn tại!";
    }

    $stmt->close();
    $conn->close();
}
?>