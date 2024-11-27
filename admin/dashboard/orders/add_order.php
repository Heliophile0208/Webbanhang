<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Đơn Hàng và Sản Phẩm</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<h2>Thêm Đơn Hàng và Sản Phẩm</h2>

<!-- Form nhập thông tin đơn hàng -->
<form id="orderForm">
    <label for="user_id">Chọn Người Dùng:</label>
    <select name="user_id" id="user_id" required>
        <option value="">Chọn người dùng</option>
        <?php
        // Kết nối cơ sở dữ liệu và lấy danh sách người dùng
        include '../../../config/database.php';
        $query = "SELECT id, username FROM users";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($user = $result->fetch_assoc()) {
            echo "<option value='{$user['id']}'>{$user['username']}</option>";
        }
        ?>
    </select>
    <br><br>

    <label for="product_id">Chọn Sản Phẩm:</label>
    <select name="product_id" id="product_id" required>
        <option value="">Chọn sản phẩm</option>
        <?php
        // Lấy danh sách sản phẩm và kích thước từ cơ sở dữ liệu
        $query_products = "SELECT p.id, p.name, GROUP_CONCAT(ps.size ORDER BY ps.size ASC) as sizes 
                           FROM products p
                           LEFT JOIN product_sizes ps ON p.id = ps.product_id
                           GROUP BY p.id";
        $stmt_products = $conn->prepare($query_products);
        $stmt_products->execute();
        $result_products = $stmt_products->get_result();
        while ($product = $result_products->fetch_assoc()) {
            $sizes = explode(',', $product['sizes']);
            echo "<option value='{$product['id']}' data-sizes='" . json_encode($sizes) . "'>{$product['name']}</option>";
        }
        ?>
    </select>
    <br><br>

    <label for="quantity">Số Lượng:</label>
    <input type="number" name="quantity" id="quantity" value="1" min="1" required>
    <br><br>

    <label for="size">Kích Thước:</label>
    <select name="size" id="size" required>
        <option value="">Chọn kích thước</option>
    </select>
    <br><br>

    <button type="submit">Thêm Vào Giỏ Hàng</button>
</form>

<script>
$(document).ready(function() {
    $('#orderForm').submit(function(event) {
        event.preventDefault(); // Ngừng gửi form thông thường

        // Lấy dữ liệu từ form
        var user_id = $('#user_id').val();
        var product_id = $('#product_id').val();
        var quantity = $('#quantity').val();
        var size = $('#size').val();

        if (user_id && product_id && quantity && size) {
            $.ajax({
                url: 'add_order.php', // Gửi yêu cầu đến PHP xử lý
                method: 'POST',
                data: {
                    user_id: user_id,
                    product_id: product_id,
                    product_name: $('#product_id option:selected').text(),
                    product_price: 1000, // Thay thế bằng giá thực tế từ CSDL
                    quantity: quantity,
                    size: size
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert(data.message);

                        // Điều hướng đến trang orders.php và cập nhật nội dung dashboard-content
                        $.ajax({
                            url: 'orders.php', // Trang orders.php sẽ trả về chỉ phần dashboard-content
                            method: 'GET',
                            data: { user_id: user_id }, // Truyền user_id để lấy thông tin đơn hàng
                            success: function(content) {
                                // Cập nhật nội dung của phần dashboard-content
                                $('#dashboard-content').html(content);
                            }
                        });
                    } else {
                        alert(data.message);
                    }
                }
            });
        } else {
            alert('Vui lòng điền đầy đủ thông tin.');
        }
    });
});
</script>

</body>
</html>