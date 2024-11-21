<?php
session_start();
include '../header.php';
include '../../config/database.php';

// Lấy user_id từ session
$username = $_SESSION['username'] ?? '';

// Kiểm tra nếu username không tồn tại trong session
if (empty($username)) {
    echo "<p>Vui lòng đăng nhập để tiến hành thanh toán.</p>";
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
    echo "<p>Không tìm thấy người dùng trong hệ thống.</p>";
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
    echo "<p style='text-align:center; font-size:24px; color:red'>Không tìm thấy thông tin khách hàng trong hệ thống. Vui lòng nhập thông tin dưới đây.</p>";
    $customer_name = $customer_email = $phone_number = $address_line1 = $address_line2 = $city = $state = $postal_code = $country = '';
}

// Hiển thị form nhập thông tin nếu không có trong cơ sở dữ liệu
echo "<h3>Thông tin giao hàng</h3>";
echo "<form action='process_checkout.php' method='POST'>
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

        <input type='submit' value='Thanh toán' class='checkout-btn'>
      </form>";
?>
<style>
/* Style chung cho toàn trang */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

h3, h2 {
    text-align: center;
    color: #333;
    margin-top: 20px;
}

/* Style cho form thanh toán */
form {
    width: 60%;
    margin: 0 auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

form input[type="text"], form input[type="email"], form input[type="tel"], form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
    font-size: 14px;
}

form input[type="submit"] {
    display: block;
    width: 200px;
    margin: 20px auto;
    padding: 10px;
    background-color: #28a745;
    color: white;
    text-align: center;
    font-size: 16px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
}

form input[type="submit"]:hover {
    background-color: #218838;
}

/* Style cho bảng giỏ hàng */
table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: center;
}

th {
    background-color: #007BFF;
    color: white;
    font-weight: bold;
}

td {
    background-color: #f9f9f9;
}

a {
    color: #007BFF;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

/* Nút thanh toán */
.checkout-btn {
    display: block;
    width: 200px;
    margin: 20px auto;
    padding: 10px;
    background-color: #28a745;
    color: white;
    text-align: center;
    font-size: 16px;
    border-radius: 5px;
    text-decoration: none;
}

.checkout-btn:hover {
    background-color: #218838;
}

/* Style cho thông báo lỗi */
.error-message {
    color: red;
    text-align: center;
    font-weight: bold;
}

/* Style cho footer */
footer {
    text-align: center;
    margin-top: 40px;
    font-size: 14px;
    color: #555;
}

</style>