<?php
include '../../../config/database.php';

// Lấy OrderID từ URL
$orderID = isset($_GET['OrderID']) ? intval($_GET['OrderID']) : 0;

// Lấy danh sách size từ bảng size
$sizeQuery = "SELECT id, size FROM size";

$sizeResult = $conn->query($sizeQuery);
$sizes = [];
while ($row = $sizeResult->fetch_assoc()) {
    $sizes[] = $row;
}

if ($orderID > 0) {
    // Truy vấn để lấy thông tin các sản phẩm trong đơn hàng
    $sql = "SELECT oi.id AS item_id, oi.quantity, oi.price, oi.size_id, p.name AS product_name, s.size AS size_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN size s ON oi.size_id = s.id
            WHERE oi.order_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kiểm tra xem có sản phẩm trong đơn hàng
    if ($result->num_rows > 0) {
        echo "<h1>Chi tiết Đơn Hàng #$orderID</h1>";
        echo "<table border='1'>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Số Lượng</th>
                        <th>Giá</th>
                        <th>Size</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>";

        // Hiển thị các sản phẩm trong đơn hàng
        while ($item = $result->fetch_assoc()) {
            $item_id = $item['item_id'];
            $product_name = $item['product_name'];
            $quantity = $item['quantity'];
            $price = number_format($item['price'], 0, ',', '.') . " VND";
            $size_name = $item['size_name'] ? $item['size_name'] : 'Không có size';

            $sizeSelect = "<select id='size-$item_id'>";
            $sizeSelect .= "<option value='0'>Chọn Size</option>";
            foreach ($sizes as $size) {
                $selected = ($size['id'] == $item['size_id']) ? "selected" : "";
                $sizeSelect .= "<option value='{$size['id']}' $selected>{$size['size']}</option>";
            }
            $sizeSelect .= "</select>";

            echo "<tr id='item-$item_id'>
                    <td>$item_id</td>
                    <td>$product_name</td>
                    <td><input type='number' id='quantity-$item_id' value='$quantity' min='1'></td>
                    <td>$price</td>
                    <td>$sizeSelect</td>
                    <td>  
                        <div class='action-buttons'>
                            <button onclick='updateItem($item_id)'>Cập nhật</button>
                            <button onclick='deleteItem($item_id)'>Xóa</button>
                        </div>
                    </td>
                </tr>";
        }

        echo "</tbody></table>";
    } else {
        echo "<p>Không có sản phẩm trong đơn hàng này.</p>";
    }


    // Phần tìm kiếm sản phẩm
    echo '<input type="text" id="search" placeholder="Tìm kiếm sản phẩm..." onkeyup="searchProducts()">';
    echo '<div id="search-results"></div>';
} else {
    echo "<p>Không có OrderID hợp lệ!</p>";
}
$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Hàm tìm kiếm sản phẩm
    function searchProducts() {
        var searchTerm = document.getElementById('search').value;
        $.ajax({
            url: 'dashboard/orders/search_product.php',
            method: 'GET',
            data: { search: searchTerm, order_id: <?= $orderID ?> },
            success: function(response) {
                document.getElementById('search-results').innerHTML = response;
            },
            error: function() {
                alert('Lỗi kết nối tới máy chủ!');
            }
        });
    }

 
</script>

<style>
/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table th, table td {
    padding: 12px;
    text-align: center;
    border: 1px solid #ddd;
}

table th {
    background-color: #007bff;
    color: white;
}

table tr:hover {
    background-color: #f1f1f1;
}

/* Search bar */
#search {
    width: 80%;
    padding: 8px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

#search-results {
    margin-top: 15px;
}

.search-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 8px;
    border: 1px solid #ddd;
    background-color: #f9f9f9;
}

.search-item:hover {
    background-color: #f1f1f1;
}

.add-cart-btn {
    padding: 6px 12px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
}
/* Căn chỉnh nút hành động trong 1 hàng */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 10px; /* Khoảng cách giữa các nút */
}

.action-buttons button {
    padding: 6px 12px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s ease;
    border-radius: 4px; /* Bo góc nhẹ */
}

.action-buttons button:hover {
    background-color: #0056b3;
}

.add-cart-btn:hover {
    background-color: #0056b3;
}
</style>
<script>
function addToCart(productId, orderId) {
    console.log('Product ID:', productId, 'Order ID:', orderId);  // Kiểm tra console

    // Lấy size ID từ dropdown, nếu không chọn thì mặc định là 1
    var sizeId = document.getElementById('size-' + productId).value || 1;

    console.log('Size ID:', sizeId);  // Kiểm tra size ID

    // Gửi dữ liệu tới server bằng AJAX
    $.ajax({
        url: 'dashboard/orders/add_to_cart.php', // Đảm bảo đường dẫn chính xác
        method: 'POST',
        data: {
            product_id: productId,
            order_id: orderId,
            quantity: 1, // Mặc định số lượng là 1
            size_id: sizeId
        },
        success: function(response) {
            console.log('Response:', response);  // Kiểm tra phản hồi từ server
            if (response.trim() === "success") {
                alert('Sản phẩm đã được thêm vào giỏ hàng!');
                // Reload lại trang với OrderID
                 $("#dashboard-content").load('dashboard/orders/edit_order.php?OrderID=' + orderId);
            } else {
                alert('Có lỗi xảy ra: ' + response);
            }
        },
        error: function(xhr, status, error) {
            // Xử lý lỗi kết nối
            alert('Lỗi kết nối tới máy chủ: ' + error);
        }
    });
}


    // Hàm xóa sản phẩm
    function deleteItem(itemId) {
        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
            $.ajax({
                url: 'dashboard/orders/delete_item_edit_order.php', // Đảm bảo đường dẫn đúng
                method: 'POST',
                data: { item_id: itemId },
                success: function(response) {
                    if (response.trim() === "success") {
                        alert('Sản phẩm đã được xóa!');
                        $('#item-' + itemId).remove();  // Xóa sản phẩm khỏi giao diện
                    } else {
                        alert('Có lỗi xảy ra: ' + response);
                    }
                },
                error: function(xhr, status, error) {
                    // Xử lý lỗi kết nối
                    alert('Lỗi kết nối tới máy chủ: ' + error);
                }
            });
        }
    }

    // Hàm cập nhật thông tin sản phẩm
    function updateItem(itemId) {
        // Lấy số lượng và size từ các input và select
        var quantity = document.getElementById('quantity-' + itemId).value;
        var sizeId = document.getElementById('size-' + itemId).value;

        // Kiểm tra nếu số lượng hợp lệ
        if (quantity <= 0) {
            alert('Số lượng phải lớn hơn 0!');
            return;
        }

        // Gửi dữ liệu tới server bằng AJAX
        $.ajax({
            url: 'dashboard/orders/update_item_edit_order.php', // Đảm bảo đường dẫn đúng
            method: 'POST',
            data: {
                item_id: itemId,
                quantity: quantity,
                size_id: sizeId
            },
            success: function(response) {
                if (response.trim() === "success") {
                    alert('Cập nhật thành công!');
                 $("#dashboard-content").load('dashboard/orders/edit_order.php?OrderID=' + orderId);
                } else {
                    alert('Có lỗi xảy ra: ' + response);
                }
            },
            error: function(xhr, status, error) {
                // Xử lý lỗi kết nối
                alert('Lỗi kết nối tới máy chủ: ' + error);
            }
        });
    }


</script>