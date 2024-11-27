<?php
// Kết nối cơ sở dữ liệu
include '../../config/database.php';

// Truy vấn dữ liệu review
$query = "SELECT * FROM reviews ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<div class="content-reviews">
   <div class="tieude"> <h2 style="text-align:center;">Danh sách đánh giá</h2>
</div>
    <?php if ($result->num_rows > 0): ?>
        <table border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ID Sản phẩm</th>
                    <th>ID User</th>
                    <th>Bình chọn</th>
                    <th>Bình luận</th>
                    <th>Được tạo</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['product_id']) ?></td>
                        <td><?= htmlspecialchars($row['user_id']) ?></td>
                        <td><?= htmlspecialchars($row['rating']) ?></td>
                        <td><?= htmlspecialchars($row['comment']) ?></td>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Chưa có đánh giá nào.</p>
    <?php endif; ?>
</div>

<style>
.content-reviews {
   
    padding: 20px;
}

.tieude {
    text-align: center;
    margin-bottom: 20px;
}

.tieude h2 {
    font-size: 24px;
    color: #333;
    text-transform: capitalize; /* Để giữ nguyên cách viết của chữ */
    border-bottom: 2px solid #4CAF50;
    display: inline-block;
    padding-bottom: 5px;
}

#dynamic-content {
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Table styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: #fff;
}

table th, table td {
    text-align: center; /* Căn giữa nội dung */
    padding: 15px; /* Tăng padding để ô dễ nhìn hơn */
    border: 1px solid #ddd;
    font-size: 14px;
}

table th {
    background-color: #4CAF50;
    color: #ffffff;
    font-weight: bold;
    text-transform: none; /* Giữ nguyên cách viết chữ */
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #f1f1f1;
}

p {
    font-size: 16px;
    color: #666;
    margin-top: 20px;
    text-align: center;
}
</style>