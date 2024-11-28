<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Giỏ Hàng</title>
    <link rel="stylesheet" href="../../css/cart.css">
    <link rel="stylesheet" href="../../css/header.css">
</head>
<body>
    <?php
    session_start();
    include '../../config/database.php';
    include '../header.php';

    // Lấy user_id từ session
    $username = $_SESSION['username'] ?? '';

    // Kiểm tra nếu username không tồn tại trong session
    if (empty($username)) {
        echo "<main class='notification'><p>Vui lòng đăng nhập để tiến hành thanh toán.</p></main>";
        exit;
    }

    // Truy vấn để lấy user_id từ bảng users dựa vào username
    $user_query = "SELECT id FROM users WHERE username = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("s", $username);
    $user_stmt->execute();
    $user_stmt->bind_result($user_id);
    $user_stmt->fetch();
    $user_stmt->close();

    // Kiểm tra nếu không có user_id
    if (!$user_id) {
        echo "<main class='notification'><p>Không tìm thấy người dùng trong hệ thống.</p></main>";
        exit;
    }

    // Truy vấn thông tin khách hàng từ bảng customer
    $customer_query = "SELECT customer_name, customer_email, phone_number, address_line1, address_line2, city, state, postal_code, country FROM customers WHERE user_id = ?";
    $customer_stmt = $conn->prepare($customer_query);
    $customer_stmt->bind_param("i", $user_id);
    $customer_stmt->execute();
    $customer_stmt->bind_result($customer_name, $customer_email, $phone_number, $address_line1, $address_line2, $city, $state, $postal_code, $country);
    $customer_stmt->fetch();
    $customer_stmt->close();

    // Nếu không có thông tin khách hàng, yêu cầu nhập thông tin
    if (!$customer_name) {
        echo "<main class='notification'><p>Không tìm thấy thông tin khách hàng trong hệ thống. Vui lòng nhập thông tin dưới đây.</p></main>";
        $customer_name = $customer_email = $phone_number = $address_line1 = $address_line2 = $city = $state = $postal_code = $country = '';
    }

    // Hiển thị form nhập thông tin nếu không có trong cơ sở dữ liệu
    echo "<h3>Thông tin giao hàng</h3>";
    echo "<form class='form-checkout' action='process_checkout.php' method='POST'>
            <label for='customer_name'>Tên khách hàng:</label><br>
            <input type='text' id='customer_name' name='customer_name' value='$customer_name' required><br><br>

            <label for='customer_email'>Email:</label><br>
            <input type='email' id='customer_email' name='customer_email' value='$customer_email' required><br><br>

            <label for='phone_number'>Số điện thoại:</label><br>
            <input type='tel' id='phone_number' name='phone_number' value='$phone_number' required><br><br>

            <label for='address_line1'>Địa chỉ (Đường):</label><br>
            <input type='text' id='address_line1' name='address_line1' value='$address_line1' required><br><br>

            <label for='address_line2'>Địa chỉ (Khu vực):</label><br>
            <input type='text' id='address_line2' name='address_line2' value='$address_line2'><br><br>

            <label for='city'>Thành phố:</label><br>
            <input type='text' id='city' name='city' value='$city' required><br><br>

            <label for='state'>Tỉnh / Thành phố:</label><br>
            <input type='text' id='state' name='state' value='$state'><br><br>

            <label for='postal_code'>Mã bưu điện:</label><br>
            <input type='text' id='postal_code' name='postal_code' value='$postal_code'><br><br>

            <label for='country'>Quốc gia:</label><br>
            <input type='text' id='country' name='country' value='$country' required><br><br>

            <label for='payment_method'>Phương thức thanh toán:</label><br>
            <select id='payment_method' name='payment_method' required>
                <option value='cod'>Thanh toán khi nhận hàng (COD)</option>
                <option value='bank_transfer'>Chuyển khoản ngân hàng</option>
            </select><br><br>

            <input type='submit' value='Thanh toán' class='checkout-bt'>
        </form>";
    ?>
   
</body>
</html>