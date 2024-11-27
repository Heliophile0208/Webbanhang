<?php
include '../../../config/database.php';

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Truy vấn sản phẩm theo tên
$sql = "SELECT id, name, price FROM products WHERE name LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$searchTerm = '%' . $searchTerm . '%'; // Thêm dấu % để tìm kiếm theo chuỗi
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='product-item' data-id='{$row['id']}'>
                <p><strong>{$row['name']}</strong> - " . number_format($row['price'], 0, ',', '.') . " VND</p>
                <button onclick='addToCart({$row['id']})'>Thêm vào giỏ</button>
              </div>";
    }
} else {
    echo "<p>Không có sản phẩm nào phù hợp với từ khóa tìm kiếm.</p>";
}

$conn->close();
?>