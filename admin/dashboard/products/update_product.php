<?php
session_start();
include_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ProductID = $_POST['ProductID'];
    $productName = $_POST['product_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Kiểm tra dữ liệu đầu vào
    if (empty($productName) || empty($description) || empty($price) || empty($stock)) {
        echo "Vui lòng điền đầy đủ thông tin sản phẩm.";
        exit;
    }

    // Cập nhật sản phẩm
    $updateQuery = "UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssdii", $productName, $description, $price, $stock, $ProductID);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Lỗi khi cập nhật sản phẩm: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>