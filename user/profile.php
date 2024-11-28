<?php session_start();  include '../includes/header.php' ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="../css/profile.css">

    <title>Thông Tin Khách Hàng</title>
    

</head>
<body>
<div class="khungbao">
    <div class="container-profile">
        <?php
        // Include file kết nối database
        include '../config/database.php';

        // Bắt đầu session
        

        // Lấy username từ session
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

        if (!$username) {
            die("<p>Bạn cần đăng nhập để xem thông tin.</p>");
        }

        // Truy vấn dữ liệu từ bảng users
        $sql = "SELECT id FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $user_id = $row['id'];

            // Lấy thông tin khách hàng
            $customer_sql = "
                SELECT customer_name, customer_email, phone_number, address_line1, address_line2, city, state, postal_code, country
                FROM customers
                WHERE user_id = $user_id
            ";
            $customer_result = mysqli_query($conn, $customer_sql);

            if (mysqli_num_rows($customer_result) > 0) {
                $customer = mysqli_fetch_assoc($customer_result);

                // Hiển thị thông tin khách hàng dạng bảng
                echo "<h2>Thông tin khách hàng</h2>";
                echo "<table>
                        <tr>
                            <th>Thông tin</th>
                            <th>Chi tiết</th>
                        </tr>
                        <tr>
                            <td>Tên khách hàng</td>
                            <td>" . htmlspecialchars($customer['customer_name']) . "</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>" . htmlspecialchars($customer['customer_email']) . "</td>
                        </tr>
                        <tr>
                            <td>Số điện thoại</td>
                            <td>" . htmlspecialchars($customer['phone_number']) . "</td>
                        </tr>
                        <tr>
                            <td>Địa chỉ 1</td>
                            <td>" . htmlspecialchars($customer['address_line1']) . "</td>
                        </tr>
                        <tr>
                            <td>Thành phố</td>
                            <td>" . htmlspecialchars($customer['city']) . "</td>
                        </tr>
                        <tr>
                            <td>Quốc gia</td>
                            <td>" . htmlspecialchars($customer['country']) . "</td>
                        </tr>
                      </table>";

                // Tính tổng số tiền khách hàng đã mua từ bảng orders
                $order_sql = "SELECT SUM(total) AS total_spent FROM orders WHERE user_id = $user_id";
                $order_result = mysqli_query($conn, $order_sql);
                $order_data = mysqli_fetch_assoc($order_result);

                $total_spent = $order_data['total_spent'] ? $order_data['total_spent'] : 0;

                echo "<p><strong>Tổng số tiền đã mua:</strong> " . number_format($total_spent, 2) . " VND</p>";
                
                // Nút Cập nhật thông tin
                echo '<a href="update_customer.php" class="btn">Cập nhật thông tin</a>';
            } else {
                echo "<p>Không tìm thấy thông tin khách hàng.</p>";
            }
        } else {
            echo "<p>Không tìm thấy người dùng.</p>";
        }

        // Đóng kết nối
        mysqli_close($conn);
        ?>
    </div>
</div>
</body>
</html>