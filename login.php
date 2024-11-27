<?php  
session_start();
include_once 'config/database.php';

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Truy vấn CSDL để kiểm tra thông tin người dùng
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    // Kiểm tra lỗi khi chuẩn bị câu lệnh SQL
    if ($stmt === false) {
        die('Lỗi chuẩn bị câu lệnh SQL: ' . $conn->error);
    }

    // Gắn giá trị vào câu lệnh SQL
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Kiểm tra nếu có người dùng với tên đăng nhập tương ứng
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Kiểm tra mật khẩu có khớp không
        if (password_verify($password, $user['password'])) {
            // Lấy vai trò của người dùng
            $role = $user['role'];

            // Lưu tên người dùng và vai trò vào session
            $_SESSION['username'] = $username;
                $_SESSION['role'] =$role;

            // Điều hướng người dùng đến trang tương ứng theo vai trò
            if ($role === 'admin') {
                header("Location: admin/index.php");
                exit;
            } elseif ($role === 'user') {
                header("Location: user/index.php");
                exit;
            } else {
                $message = "Vai trò không hợp lệ!";
            }
        } else {
            $message = "Mật khẩu không đúng!";
        }
    } else {
        $message = "Tên đăng nhập không tồn tại!";
    }
} else {
    $message = "Vui lòng nhập tên đăng nhập và mật khẩu!";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Đăng Nhập</title>

<link rel="stylesheet" href="../css/login_logout.css" type="text/css">

</head>
<body>
    <!-- Form đăng nhập -->
    <div class="container">
  <h2>Đăng Nhập</h2>
<?php if ($message != ""): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
        <form action="login.php" method="post">
          

            <!-- Hiển thị thông báo nếu có -->
            

            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit">Đăng Nhập</button>
            <div class="text">
    <span><a href="register.php">Đăng ký ngay</a></span>
    <span><a href="change_password.php">Đổi mật khẩu</a></span>
</div>


        </form>

    </div>
</body>
</html>