<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f4f4f4;
    }

    h1 {
        text-align: center;
        color: #4CAF50;
        margin-bottom: 20px;
    }

    p {
        font-size: 16px;
        margin: 10px 0;
    }

    .order-info {
        margin-bottom: 20px;
    }

    .order-info strong {
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
        font-size: 14px;
    }

    th {
        background-color: #4CAF50;
        color: white;
    }

    td {
        background-color: #f9f9f9;
    }

    h3 {
        color: #333;
        margin-top: 30px;
        font-size: 20px;
    }

    /* Style for print */
    @media print {
        body {
            background-color: white;
            padding: 0;
        }

        .order-info {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            margin-top: 15px;
        }

        th, td {
            padding: 8px;
        }

        h1, h3 {
            color: #333;
        }

        /* Hide the print dialog button on print */
        .no-print {
            display: none;
        }
    }
</style>
<?php
include '../../../config/database.php';
if (isset($_GET['OrderID'])) {
    $orderID = intval($_GET['OrderID']);

    // Truy vấn thông tin đơn hàng
    $sql = "
        SELECT o.id AS order_id, o.created_at, c.customer_name, o.total, o.status
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN customers c ON u.id = c.user_id
        WHERE o.id = $orderID
    ";
    $result = $conn->query($sql);
    $order = $result->fetch_assoc();

    // Truy vấn các sản phẩm trong đơn hàng
    $sql_items = "
        SELECT oi.quantity, oi.price, p.name AS product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = $orderID
    ";
    $items_result = $conn->query($sql_items);
    
    echo "<h1>Đơn Hàng #{$order['order_id']}</h1>";
    echo "<div class='order-info'>
            <p><strong>Khách Hàng:</strong> {$order['customer_name']}</p>
            <p><strong>Ngày Tạo:</strong> {$order['created_at']}</p>
            <p><strong>Tổng Tiền:</strong> " . number_format($order['total'], 0, ',', '.') . " VND</p>
            <p><strong>Trạng Thái:</strong> {$order['status']}</p>
          </div>";

    echo "<h3>Chi Tiết Sản Phẩm</h3>";
    if ($items_result->num_rows > 0) {
        echo "<table>
                <thead>
                    <tr>
                        <th>Tên Sản Phẩm</th>
                        <th>Số Lượng</th>
                        <th>Giá</th>
                        <th>Tổng</th>
                    </tr>
                </thead>
                <tbody>";
        while ($item = $items_result->fetch_assoc()) {
            $totalPrice = number_format($item['price'] * $item['quantity'], 0, ',', '.') . " VND";
            echo "<tr>
                    <td>{$item['product_name']}</td>
                    <td>{$item['quantity']}</td>
                    <td>" . number_format($item['price'], 0, ',', '.') . " VND</td>
                    <td>$totalPrice</td>
                </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>Không có sản phẩm trong đơn hàng.</p>";
    }

    // Chức năng in
    echo "<script>window.print();</script>";
} else {
    echo "<p>Không tìm thấy đơn hàng.</p>";
}
?>