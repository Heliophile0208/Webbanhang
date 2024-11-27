<?php
include '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && isset($_POST['OrderID'])) {
    $orderID = intval($_POST['OrderID']);
    
    // Bắt đầu giao dịch để xóa các mục liên quan
    $conn->begin_transaction();
    try {
        // Xóa các mục liên quan trong bảng order_items trước
        $deleteItemsQuery = "DELETE FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($deleteItemsQuery);
        $stmt->bind_param("i", $orderID);
        $stmt->execute();

        // Xóa đơn hàng trong bảng orders
        $deleteOrderQuery = "DELETE FROM orders WHERE id = ?";
        $stmt = $conn->prepare($deleteOrderQuery);
        $stmt->bind_param("i", $orderID);
        $stmt->execute();

        // Cam kết giao dịch
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Xóa đơn hàng thành công.']);
    } catch (Exception $e) {
        // Nếu có lỗi, hủy bỏ giao dịch
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa đơn hàng: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Đơn Hàng</title>
    <style>
      
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        .no-orders {
            font-size: 18px;
            color: #ff6f61;
            text-align: center;
        }
        .product-details {
            display: none;
            margin-top: 10px;
            padding: 10px;
        }
        .toggle-button {
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .toggle-button:hover {
            background-color: #0056b3;
        }
        .inner-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .inner-table th, .inner-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .inner-table thead {
            background-color: #4CAF50;
            color: white;
        }
        .action-buttons {
            margin-bottom: 20px;
            text-align: left;
        }
        .action-buttons button {
            margin-right: 10px;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }
        .action-buttons button:hover {
            background-color: #45a049;
        }
    </style>
    <script>
        function toggleDetails(orderId) {
            const details = document.getElementById("details-" + orderId);
            if (details.style.display === "none" || details.style.display === "") {
                details.style.display = "table-row";
            } else {
                details.style.display = "none";
            }
        }

       
    </script>
</head>
<body>
    <!-- Nút hành động -->

   
       
<div class="action-buttons">
    <button onclick="loadAddOrderForm()">Thêm</button>
    <button onclick="loadEditOrderForm()">Sửa</button>
    <button onclick="deleteOrder()">Xóa</button>
</div>

<?php
$sql = "
    SELECT o.id AS order_id, o.created_at, c.customer_name, o.total, o.status
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN customers c ON u.id = c.user_id
";
$result = $conn->query($sql);

$completed_orders = [];
$pending_orders = [];

if ($result->num_rows > 0) {
    while ($order = $result->fetch_assoc()) {
        if ($order['status'] === 'completed') {
            $completed_orders[] = $order;
        } else {
            $pending_orders[] = $order;
        }
    }
}

function displayOrderTable($orders, $title) {
    global $conn;
    echo "<h1>$title</h1>";
    if (count($orders) > 0) {
        echo "<table>";
        echo "<thead>
                <tr>
                    <th>Chọn</th>
                    <th>Mã Đơn Hàng</th>
                    <th>Khách Hàng</th>
                    <th>Ngày Tạo</th>
                    <th>Tổng Tiền</th>
                    <th>Hành Động</th>
                </tr>
              </thead>";
        echo "<tbody>";
        foreach ($orders as $order) {
            $order_id = $order['order_id'];
            echo "<tr>";
            echo "<td><input type='radio' name='selected_order' value='$order_id'></td>";
            echo "<td>" . $order['order_id'] . "</td>";
            echo "<td>" . ($order['customer_name'] ? $order['customer_name'] : "Chưa có thông tin") . "</td>";
            echo "<td>" . $order['created_at'] . "</td>";
            echo "<td>" . number_format($order['total'], 0, ',', '.') . " VND</td>";
            echo "<td><button class='toggle-button' onclick='toggleDetails($order_id)'>Mở</button></td>";
            echo "</tr>";

            // Lấy danh sách sản phẩm trong đơn hàng
            $sql_items = "
                SELECT oi.id AS item_id, oi.quantity, oi.price, p.name AS product_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = $order_id
            ";
            $items_result = $conn->query($sql_items);

            echo "<tr id='details-$order_id' class='product-details' style='display:none;'>";
            echo "<td colspan='6'>";  // colspan="6" để chiếm toàn bộ bảng
            if ($items_result->num_rows > 0) {
                echo "<table class='inner-table'>";
                echo "<thead>
                        <tr>
                            <th>ID Sản Phẩm</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Số Lượng</th>
                            <th>Giá</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                while ($item = $items_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $item['item_id'] . "</td>";
                    echo "<td>" . $item['product_name'] . "</td>";
                    echo "<td>" . $item['quantity'] . "</td>";
                    echo "<td>" . number_format($item['price'], 0, ',', '.') . " VND</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>Không có sản phẩm nào trong đơn hàng này.</p>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p class='no-orders'>Không có đơn hàng nào trong danh sách này.</p>";
    }
}

// Hiển thị bảng Đơn Hàng Hoàn Thành
displayOrderTable($completed_orders, "Đơn Hàng Hoàn Thành");

// Hiển thị bảng Đơn Hàng Đang Chờ Xử Lý
displayOrderTable($pending_orders, "Đơn Hàng Đang Chờ Xử Lý");

$conn->close();
?>

</body>
</html>
<script>
function loadAddOrderForm() {
    $.ajax({
        url: 'dashboard/orders/add_order.php',  // Đường dẫn đến form thêm đơn hàng
        method: 'GET',
        success: function(response) {
            $('#dashboard-content').html(response);  // Hiển thị form thêm đơn hàng
        },
        error: function() {
            alert("Có lỗi khi tải trang thêm đơn hàng.");
        }
    });
}

// Sau khi thêm đơn hàng thành công
function handleAddOrderSuccess() {
    alert('Thêm đơn hàng thành công!');
    loadOrders();  // Cập nhật lại danh sách đơn hàng
}
function loadEditOrderForm() {
    const selectedRadio = document.querySelector('input[name="selected_order"]:checked');
    if (selectedRadio) {
        const OrderID = selectedRadio.value;
        $.ajax({
            url: 'dashboard/orders/edit_order.php',
            method: 'GET',
            data: { OrderID: OrderID },
            success: function(response) {
                $('#dashboard-content').html(response);
            },
            error: function() {
                alert("Có lỗi khi tải dữ liệu chỉnh sửa.");
            }
        });
    } else {
        alert("Bạn chưa chọn đơn hàng để sửa.");
    }
}
function deleteOrder() {
    const selectedRadio = document.querySelector('input[name="selected_order"]:checked');
    if (!selectedRadio) {
        alert("Bạn chưa chọn đơn hàng để xóa.");
        return;
    }
    if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này?')) {
        const OrderID = selectedRadio.value;
        $.ajax({
            url: 'dashboard/orders/orders.php',
            method: 'POST',
            data: { delete: true, OrderID: OrderID },
            success: function(response) {
                alert('Xóa đơn hàng thành công!');
                loadOrders();
            },
            error: function() {
                alert('Có lỗi khi xóa đơn hàng.');
            }
        });
    }
}
function loadOrders() {
    $.ajax({
        url: 'dashboard/orders/orders.php',  // Xử lý tải lại danh sách đơn hàng
        method: 'POST',
        success: function(response) {
            $('#dashboard-content').html(response);  // Hiển thị danh sách đơn hàng
        },
        error: function() {
            alert('Có lỗi khi tải lại danh sách đơn hàng.');
        }
    });
}
</script>