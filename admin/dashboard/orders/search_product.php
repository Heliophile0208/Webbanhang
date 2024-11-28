<?php
include '../../../config/database.php';

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$orderID = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Truy vấn để tìm sản phẩm theo tên
$sql = "SELECT id, name, price FROM products WHERE name LIKE ?";
$stmt = $conn->prepare($sql);
$searchTerm = "%$searchTerm%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<ul class='search-list'>";
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['id'];
        $product_name = $row['name'];
        $price = number_format($row['price'], 0, ',', '.') . " VND";

        // Truy vấn lấy size từ bảng product_sizes liên kết giữa products và size
        $sizeQuery = "SELECT s.id, s.size FROM size s
                      JOIN product_sizes ps ON s.id = ps.size_id
                      WHERE ps.product_id = ?";
        $sizeStmt = $conn->prepare($sizeQuery);
        $sizeStmt->bind_param("i", $product_id);
        $sizeStmt->execute();
        $sizeResult = $sizeStmt->get_result();

        // Tạo các options size cho dropdown
        $sizeOptions = "<option value='0'>Chọn Size</option>";
        while ($size = $sizeResult->fetch_assoc()) {
            $sizeOptions .= "<option value='{$size['id']}'>{$size['size']}</option>";
        }

        echo "<li class='search-item'>
                <span>$product_name - $price</span>
                <select id='size-$product_id'>
                    $sizeOptions
                </select>
                <button class='add-cart-btn' onclick='addToCart($product_id, $orderID)'>Thêm vào giỏ</button>
              </li>";
    }
    echo "</ul>";
} else {
    echo "<p>Không tìm thấy sản phẩm nào.</p>";
}

$conn->close();
?>