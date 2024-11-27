<?php include '../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/profile.css">
    <title>Cập Nhật Thông Tin Khách Hàng</title>
</head>
<body>
<div class="khungbao">
    <div class="container-profile-update">
        <?php
        // Include file kết nối database
        include '../config/database.php';

        // Bắt đầu session
        session_start();

        // Lấy username từ session
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;

        if (!$username) {
            die("<p>Bạn cần đăng nhập để cập nhật thông tin.</p>");
        }

        // Lấy user_id từ bảng users
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

                // Xử lý cập nhật thông tin
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $customer_name = $_POST['customer_name'];
                    $customer_email = $_POST['customer_email'];
                    $phone_number = $_POST['phone_number'];
                    $address_line1 = $_POST['address_line1'];
                    $address_line2 = $_POST['address_line2'];
                    $city = $_POST['city'];
                    $state = $_POST['state'];
                    $postal_code = $_POST['postal_code'];
                    $country = $_POST['country'];

                    $update_sql = "
                        UPDATE customers
                        SET 
                            customer_name = '$customer_name',
                            customer_email = '$customer_email',
                            phone_number = '$phone_number',
                            address_line1 = '$address_line1',
                            address_line2 = '$address_line2',
                            city = '$city',
                            state = '$state',
                            postal_code = '$postal_code',
                            country = '$country'
                        WHERE user_id = $user_id
                    ";

                    if (mysqli_query($conn, $update_sql)) {
                        echo "<p class='message-success'>Thông tin khách hàng đã được cập nhật thành công.</p>";
                    } else {
                        echo "<p class='message-fail'>Cập nhật thất bại: " . mysqli_error($conn) . "</p>";
                    }
                }

                // Hiển thị form cập nhật dạng flexbox
                echo "<h2>Cập nhật thông tin khách hàng</h2>";
                echo "<form method='POST' class='form-update'>
                        <div class='form-group'>
                            <label for='customer_name'>Tên khách hàng:</label>
                            <input type='text' name='customer_name' value='" . htmlspecialchars($customer['customer_name']) . "' required>
                        </div>
                        <div class='form-group'>
                            <label for='customer_email'>Email:</label>
                            <input type='email' name='customer_email' value='" . htmlspecialchars($customer['customer_email']) . "' required>
                        </div>
                        <div class='form-group'>
                            <label for='phone_number'>Số điện thoại:</label>
                            <input type='text' name='phone_number' value='" . htmlspecialchars($customer['phone_number']) . "' required>
                        </div>
                        <div class='form-group'>
                            <label for='address_line1'>Địa chỉ 1:</label>
                            <input type='text' name='address_line1' value='" . htmlspecialchars($customer['address_line1']) . "' required>
                        </div>
                        <div class='form-group'>
                            <label for='address_line2'>Địa chỉ 2:</label>
                            <input type='text' name='address_line2' value='" . htmlspecialchars($customer['address_line2']) . "'>
                        </div>
                        <div class='form-group'>
                            <label for='city'>Thành phố:</label>
                            <input type='text' name='city' value='" . htmlspecialchars($customer['city']) . "' required>
                        </div>
                        <div class='form-group'>
                            <label for='state'>Bang/Quận:</label>
                            <input type='text' name='state' value='" . htmlspecialchars($customer['state']) . "' required>
                        </div>
                        <div class='form-group'>
                            <label for='postal_code'>Mã bưu điện:</label>
                            <input type='text' name='postal_code' value='" . htmlspecialchars($customer['postal_code']) . "' required>
                        </div>
                        <div class='form-group'>
                            <label for='country'>Quốc gia:</label>
                            <input type='text' name='country' value='" . htmlspecialchars($customer['country']) . "' required>
                        </div>
                        <div class='form-group'>
                            <button   type='submit'>Lưu thông tin</button>

                        </div>
                    </form>";
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