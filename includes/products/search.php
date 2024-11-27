<?php
include '../../config/database.php'; // Kết nối đến cơ sở dữ liệu
include_once '../../includes/header.php'; // Gọi header

$query = isset($_GET['query']) ? $_GET['query'] : '';
$filteredProducts = [];

// Nếu có truy vấn tìm kiếm, thực hiện truy vấn
if ($query) {
    // Kiểm tra xem kết nối còn mở không
    if ($conn) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
        if ($stmt) {
            $searchTerm = "%" . $query . "%";
            $stmt->bind_param("s", $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();

            // Lưu kết quả vào mảng
            while ($row = $result->fetch_assoc()) {
                $filteredProducts[] = $row;
            }

            $stmt->close();
        } else {
            echo "Lỗi trong việc chuẩn bị truy vấn: " . $conn->error;
        }
    } else {
        echo "Kết nối cơ sở dữ liệu không hợp lệ.";
    }
}

// Chỉ đóng kết nối sau khi hoàn tất tất cả các thao tác
if (isset($conn)) {
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết Quả Tìm Kiếm</title>
    <link rel="stylesheet" href="../styles.css"> <!-- Đường dẫn tới file CSS của bạn -->
</head>
<body>
    <main>
        <h2>Kết Quả Tìm Kiếm Cho: "<?php echo htmlspecialchars($query); ?>"</h2>
        <table>
            <thead>
                <tr>
                    <th>Tên Sản Phẩm</th>
                    <th>Hình Ảnh</th>
                    <th>Giá</th>
                    <th>Mô Tả</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($filteredProducts)): ?>
                    <?php foreach ($filteredProducts as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 100px; height: auto;">
                            </td>
                            <td><?php echo number_format($product['price'], 0, ',', '.') . ' VNĐ'; ?></td>
                            <td><?php echo htmlspecialchars($product['description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    
</body>
</html>