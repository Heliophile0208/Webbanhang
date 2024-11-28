"<?php
session_start();
include '../../config/database.php';

// Lấy username từ session
$username = $_SESSION['username'];

// Lấy user_id từ bảng users dựa trên username
$get_user_id_query = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($get_user_id_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id);
    $stmt->fetch();
} else {
    echo "<p>Không tìm thấy người dùng với tên đăng nhập này.</p>";
    exit;
}

// Truy vấn để lấy thông tin đơn hàng
$query = "
    SELECT o.id as order_id, c.customer_name, o.created_at
    FROM orders o
    JOIN customers c ON o.user_id = c.user_id
    WHERE o.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
    echo "<div class='order-details'>";
    echo "<h1>Hóa đơn của bạn</h1>";
    echo "<p>Mã đơn hàng: " . $order['order_id'] . "</p>";
    echo "<p>Tên khách hàng: " . $order['customer_name'] . "</p>";
    echo "<p>Ngày đặt hàng: " . $order['created_at'] . "</p>";

    // Truy vấn để lấy danh sách sản phẩm
    $order_id = $order['order_id'];
    $items_query = "
        SELECT oi.product_id, oi.quantity, oi.price, (oi.quantity * oi.price) AS total_price, p.name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    if ($items_result->num_rows > 0) {
        echo "<h2>Chi tiết sản phẩm trong đơn hàng</h2>";
        echo "<table class='product-table'>";
        echo "<thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Giá mỗi sản phẩm</th>
                    <th>Tổng tiền</th>
                </tr>
              </thead>";
        echo "<tbody>";

        $total_amount = 0;
        while ($item = $items_result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $item['name'] . "</td>
                    <td>" . $item['quantity'] . "</td>
                    <td>" . number_format($item['price'], 2) . " VND</td>
                    <td>" . number_format($item['total_price'], 2) . " VND</td>
                  </tr>";
            $total_amount += $item['total_price'];
        }
        echo "</tbody>";
        echo "</table>";

        // Hiển thị tổng tiền
        echo "<h3>Tổng tiền đơn hàng: " . number_format($total_amount, 2) . " VND</h3>";
    } else {
        echo "<p>Không tìm thấy sản phẩm trong đơn hàng.</p>";
    }
    echo "</div>";

    // Nút In hóa đơn và Quay về
    echo "
        <div class='button-container'>
            <button class='back-button' onclick=\"window.location.href='../../index.php';\">Quay về trang chủ</button>
            <button class='print-button' onclick='printInvoice($order_id);'>In hóa đơn</button>
        </div>
    ";
} else {
    echo "<p>Không tìm thấy đơn hàng nào.</p>";
}

// Đóng kết nối
$conn->close();
?>

<script>
// Hàm gửi yêu cầu cập nhật trạng thái đơn hàng
function printInvoice(orderId) {
    if (confirm('Bạn có chắc chắn muốn in hóa đơn và cập nhật trạng thái đơn hàng?')) {
        fetch('update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('In hóa đơn thành công và trạng thái đơn hàng đã được cập nhật.');
                window.print(); // In hóa đơn
            } else {
                alert('Đã xảy ra lỗi khi cập nhật trạng thái đơn hàng.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
<link rel="stylesheet" href="../../css/invoice.css">
