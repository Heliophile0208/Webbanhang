<?php
session_start();
include '../../config/database.php';

// Lấy username từ session
$username = $_SESSION['username']; // Lấy username từ session

// Lấy user_id từ bảng users dựa trên username
$get_user_id_query = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($get_user_id_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($id); // Gán id vào biến $id
    $stmt->fetch();
} else {
    echo "<p>Không tìm thấy người dùng với tên đăng nhập này.</p>";
    exit;
}

// Truy vấn để lấy thông tin đơn hàng và thông tin khách hàng
$query = "
    SELECT o.id as order_id, c.customer_name, o.created_at
    FROM orders o
    JOIN customers c ON o.user_id = c.user_id
    WHERE o.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id); // Dùng biến $id thay vì $user_id
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
    // Hiển thị thông tin hóa đơn
    echo "<div class='order-details'>";
    echo "<h1>Hóa đơn của bạn</h1>";
    echo "<p>Mã đơn hàng: " . $order['order_id'] . "</p>";
    echo "<p>Tên khách hàng: " . $order['customer_name'] . "</p>";
    echo "<p>Ngày đặt hàng: " . $order['created_at'] . "</p>";

    // Truy vấn để lấy danh sách sản phẩm trong đơn hàng, với tên sản phẩm từ bảng products
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
} else {
    echo "<p>Không tìm thấy đơn hàng nào.</p>";
}
?>

<!-- Nút Quay về trang chủ và In hóa đơn -->
<div class="button-container">
    <button class="back-button" onclick="window.location.href='../../index.php';">Quay về trang chủ</button>
    <button class="print-button" onclick="window.print();">In hóa đơn</button>
</div>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    h1, h2, h3 {
        color: #333;
        text-align: center;
    }

    .order-details {
        background-color: #fff;
        padding: 20px;
        margin: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .product-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .product-table th, .product-table td {
        padding: 10px;
        text-align: center;
        border: 1px solid #ddd;
    }

    .product-table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .product-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .product-table tbody tr:hover {
        background-color: #f1f1f1;
    }

    .button-container {
        text-align: center;
        margin-top: 20px;
    }

    .button-container button {
        padding: 10px 20px;
        font-size: 16px;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin: 5px;
    }

    .button-container button:hover {
        background-color: #0056b3;
    }

    .button-container .back-button {
        background-color: #6c757d;
    }

    .button-container .back-button:hover {
        background-color: #5a6268;
    }

    .button-container .print-button {
        background-color: #28a745;
    }

    .button-container .print-button:hover {
        background-color: #218838;
    }

    /* Ẩn các nút khi in */
    @media print {
        .button-container {
            display: none;
        }
    }
</style>