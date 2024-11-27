<?php
session_start();
include_once '../config/database.php';
include_once '../includes/header.php'; // Đảm bảo kết nối cơ sở dữ liệu

if (isset($_SESSION['username'])) {
    // Lấy username từ session
    $username = $_SESSION['username'];

    // Truy vấn để lấy user_id từ bảng users
    $query_user_id = "SELECT id FROM users WHERE username = ?";
    $stmt_user_id = $conn->prepare($query_user_id);
    $stmt_user_id->bind_param("s", $username); // Bind username
    $stmt_user_id->execute();
    $result_user_id = $stmt_user_id->get_result();

    if ($result_user_id->num_rows > 0) {
        // Lấy user_id
        $user_row = $result_user_id->fetch_assoc();
        $user_id = $user_row['id'];
    } else {
        // Không tìm thấy user_id, người dùng chưa đăng nhập hoặc có lỗi
        $message_error = "Không tìm thấy người dùng.";
    }
} else {
    // Nếu chưa đăng nhập, thông báo lỗi hoặc yêu cầu đăng nhập
    $message_error = "Vui lòng đăng nhập để tiếp tục dùng dịch vụ .";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php if (isset($message_error)) : ?>
    <div style="color: red;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 5px;
    font-size:20px;
    text-align:center;
    border-radius: 5px;" id="error-message">
        <p><?php echo $message_error; ?></p>
    </div>

    <script>
        // Hiển thị thông báo
        setTimeout(function() {
            document.getElementById('error-message').style.display = 'none';
        }, 2000); 
    </script>
<?php endif; ?>
    <div class="container-dashboard">
        <!-- Sidebar for navigation -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="#" id="stats">Tổng quan</a></li>
                <li><a href="#" id="profile">Thông tin tài khoản</a></li>
                <li><a href="#" id="products">Sản phẩm</a></li>
                <li><a href="#" id="orders">Đơn hàng</a></li>
                <li><a href="#" id="users">Người dùng</a></li>
                <li><a href="#" id="categories">Danh mục</a></li>
                <li><a href="#" id="reviews">Đánh giá</a></li>
            </ul>
        </div>

        <!-- Dashboard content -->
        <div class="dashboard">
         

            <div id="dashboard-content">
       <h1> Chào mừng <?php echo $username; ?> đến trang quản lý cửa hàng</h1>

                <h2>Vui lòng chọn một mục từ sidebar để hiển thị dữ liệu</h2>


            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Lắng nghe sự kiện click vào các mục trong sidebar
            $('#stats').click(function() {
                $('#dashboard-content').load('dashboard/stats.php'); // Tải nội dung của trang stats.php vào div
            });


            $('#profile').click(function() {
                $('#dashboard-content').load('dashboard/profile/profile.php'); // Tải nội dung của trang info_account.php vào div
            });

            $('#products').click(function() {
                $('#dashboard-content').load('dashboard/products/products.php'); // Tải danh sách sản phẩm vào div
            });

            $('#orders').click(function() {
                $('#dashboard-content').load('dashboard/orders/orders.php'); // Tải danh sách đơn hàng vào div
            });

            $('#users').click(function() {
                $('#dashboard-content').load('dashboard/users/users.php'); // Tải danh sách người dùng vào div
            });

            $('#categories').click(function() {
                $('#dashboard-content').load('dashboard/categories/categories.php'); // Tải danh mục sản phẩm vào div
            });

            $('#reviews').click(function() {
                $('#dashboard-content').load('dashboard/reviews.php'); // Tải đánh giá khách hàng vào div
            });
        });
    </script>
</body>
</html>