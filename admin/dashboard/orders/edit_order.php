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

            // Tạo một danh sách thả xuống cho việc chọn size mới
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
                        <button onclick='updateItem($item_id)'>Cập nhật</button>
                        <button onclick='deleteItem($item_id)'>Xóa</button>
                    </td>
                </tr>";
        }

      
  echo "</tbody></table>";
// Phần tìm kiếm sản phẩm
        echo '<input type="text" id="search" placeholder="Tìm kiếm sản phẩm..." onkeyup="searchProducts()">';
        echo '<div id="search-results"></div>';


    } else {
        echo "<p>Không có sản phẩm trong đơn hàng này.</p>";
    }
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
            url: 'dashboard/orders/search_product.php',  // Đường dẫn đến file xử lý tìm kiếm
            method: 'GET',
            data: { search: searchTerm },
            success: function(response) {
                document.getElementById('search-results').innerHTML = response;
            },
            error: function() {
                alert('Lỗi kết nối tới máy chủ!');
            }
        });
    }

function addToCart(productId, orderId) {
    console.log("Product ID:", productId);
    console.log("Order ID:", orderId);

    if (productId <= 0 || orderId <= 0) {
        alert("Dữ liệu không hợp lệ: product_id hoặc order_id không đúng.");
        return;
    }

    var quantity = 1; // Hoặc lấy từ input tương ứng
    console.log("Quantity:", quantity);

    if (quantity <= 0) {
        alert("Dữ liệu không hợp lệ: Số lượng không hợp lệ.");
        return;
    }

    $.ajax({
        url: 'dashboard/orders/add_to_cart.php',
        method: 'POST',
        data: {
            product_id: productId,
            order_id: orderId,
            quantity: quantity
        },
        success: function(response) {
            console.log("Server response:", response);
            if (response.trim() === "success") {
                alert('Sản phẩm đã được thêm vào giỏ!');
            } else {
                alert('Có lỗi khi thêm sản phẩm vào giỏ.');
            }
        },
        error: function() {
            alert('Lỗi kết nối tới máy chủ!');
        }
    });
}
    // Hàm cập nhật số lượng sản phẩm và size
    function updateItem(itemId) {
        var quantity = document.getElementById('quantity-' + itemId).value;
        var size = document.getElementById('size-' + itemId).value;

        $.ajax({
            url: 'dashboard/orders/update_item.php',  // Đường dẫn xử lý cập nhật
            method: 'POST',
            data: {
                item_id: itemId,
                quantity: quantity,
                size: size  // Gửi size mới
            },
            success: function(response) {
                if (response.trim() === "success") {
                    alert('Cập nhật thành công!');
                } else {
                    alert('Có lỗi xảy ra khi cập nhật sản phẩm.');
                }
            },
            error: function() {
                alert('Lỗi kết nối tới máy chủ!');
            }
        });
    }

    // Hàm xóa sản phẩm khỏi đơn hàng
    function deleteItem(itemId) {
        if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi đơn hàng?')) {
            $.ajax({
                url: 'delete_item.php',  // Đường dẫn xử lý xóa
                method: 'POST',
                data: {
                    item_id: itemId
                },
                success: function(response) {
                    if (response.trim() === "success") {
                        document.getElementById('item-' + itemId).remove();
                        alert('Xóa sản phẩm thành công!');
                    } else {
                        alert('Có lỗi xảy ra khi xóa sản phẩm.');
                    }
                },
                error: function() {
                    alert('Lỗi kết nối tới máy chủ!');
                }
            });
        }
    }
</script>

<style>
/* General Styles */




/* Table Styles */
table {
    width: 100%;
    margin-bottom: 30px;
    border-collapse: collapse;
    background-color: #ffffff;
    border-radius: 8px;
    overflow: hidden;
}

table th, table td {
    padding: 15px;
    text-align: center;
    border: 1px solid #ddd;
}

table th {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

table td {
    color: #333;
    font-size: 14px;
}

table tr:hover {
    background-color: #f1f1f1;
}

/* Input Fields */
input[type="number"] {
    width: 80px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    font-size: 14px;
}

select {
    width: 150px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

/* Button Styles */
button {
    padding: 8px 16px;
    margin: 5px;
    border: none;
    border-radius: 4px;
    background-color: #28a745;
    color: white;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #218838;
}

button:active {
    transform: scale(0.98);
}

button.delete-btn {
    background-color: #dc3545;
}


/* Search Bar */
#search {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    background-color: #f8f9fa;
}

#search-results {
    margin-top: 20px;
}

/* Alert Styles */
.alert {
    padding: 12px;
    background-color: #f44336;
    color: white;
    margin-bottom: 20px;
    border-radius: 4px;
    display: none;
    font-size: 14px;
}

.alert.success {
    background-color: #4CAF50;
}

.alert.info {
    background-color: #2196F3;
}

.alert.warning {
    background-color: #ff9800;
}

.alert.show {
    display: block;
}



.delete-btn:hover {
    background-color: #c82333;
}


}</style>