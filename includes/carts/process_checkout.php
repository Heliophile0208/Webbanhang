<?php
session_start();

include '../../config/database.php';

// Lấy dữ liệu từ form
$customer_name = $_POST['customer_name'] ?? '';
$customer_email = $_POST['customer_email'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$address_line1 = $_POST['address_line1'] ?? '';
$address_line2 = $_POST['address_line2'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';
$postal_code = $_POST['postal_code'] ?? '';
$country = $_POST['country'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';

// Kiểm tra nếu các trường thông tin cần thiết không trống
if (empty($customer_name) || empty($customer_email) || empty($address_line1) || empty($phone_number) || empty($city) || empty($country)) {
    echo "<p>Vui lòng nhập đầy đủ thông tin.</p>";
    exit;
}

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

// Cập nhật hoặc thêm mới thông tin khách hàng
$check_query = "SELECT customer_id FROM customers WHERE user_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $id); // Sử dụng $id thay vì $user_id
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Cập nhật thông tin khách hàng nếu đã có
    $update_query = "UPDATE customers SET customer_name = ?, customer_email = ?, phone_number = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, country = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssssssssi", $customer_name, $customer_email, $phone_number, $address_line1, $address_line2, $city, $state, $postal_code, $country, $id); // Sử dụng $id
    $update_stmt->execute();
    $update_stmt->close();
} else {
    // Thêm mới thông tin khách hàng nếu chưa có
    $insert_query = "INSERT INTO customers (user_id, customer_name, customer_email, phone_number, address_line1, address_line2, city, state, postal_code, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("isssssssss", $id, $customer_name, $customer_email, $phone_number, $address_line1, $address_line2, $city, $state, $postal_code, $country); // Sử dụng $id
    $insert_stmt->execute();
    $insert_stmt->close();
}

echo "<p>Thông tin của bạn đã được cập nhật. Bạn có thể tiếp tục thanh toán.</p>";
header("Location: invoice.php");
exit;

?>