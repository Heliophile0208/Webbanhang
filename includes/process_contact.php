<?php
include_once 'header.php'; // Kết nối header và CSS

// Kiểm tra xem form đã được gửi không
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy dữ liệu từ biểu mẫu
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Thực hiện các thao tác xử lý thông tin (gửi email, lưu vào database, v.v.)
    // Ví dụ: Gửi email (chỉ là ví dụ, bạn có thể dùng thư viện gửi mail như PHPMailer)
    
    $to = "lethikimngan20803@gmail.com";
    $subject = "Liên hệ từ khách hàng: $name";
    $body = "Tên: $name\nEmail: $email\nTin nhắn: $message";
    $headers = "From: $email";

    // Gửi email
    if (mail($to, $subject, $body, $headers)) {
        // Gửi thành công, thông báo cho người dùng
        echo "<div class='success-message'>Gửi tin nhắn thành công! Chúng tôi sẽ phản hồi sớm nhất.</div>";
    } else {
        // Gửi thất bại, thông báo lỗi
        echo "<div class='error-message'>Đã xảy ra lỗi khi gửi tin nhắn. Vui lòng thử lại sau.</div>";
    }
}
?>
<style>
.success-message {
    background-color: #28a745;
    color: white;
    padding: 20px;
    border-radius: 5px;
    margin-top: 20px;
    text-align: center;
}

.error-message {
    background-color: #dc3545;
    color: white;
    padding: 20px;
    border-radius: 5px;
    margin-top: 20px;
    text-align: center;
}


.success-message, .error-message {
    font-size: 18px;
    font-weight: bold;
}
</style>
