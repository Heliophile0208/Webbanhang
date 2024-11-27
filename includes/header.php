<?php
session_start();  // Khởi tạo session để lấy dữ liệu từ session
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
 <link rel="stylesheet" href="../css/header.css">

   <!-- Thêm liên kết đến Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&family=Lobster&display=swap" rel="stylesheet">

</head> <title>Thời Trang Nữ</title>

    
</head>
<body>

<header>
    <div class="container-header">
<div class="logo">
        <h1><a style="text-decoration:none; color:black" href="/index.php">AlwaysWonder</a></h1></div>
        <nav>
            <ul>
                
                <li><a href="/includes/about.php">Giới Thiệu</a></li>
            <li><a href="/includes/products/products.php">Sản Phẩm</a></li>
                <li><a href="/includes/contact.php">Liên Hệ</a></li>
<li >
    <a href="/includes/carts/cart.php">
        <i class="fas fa-shopping-cart"></i> Giỏ Hàng
    </a>
</li>

                <?php if (isset($_SESSION['username'])): ?>
                    <!-- Dropdown cho người dùng bình thường -->
                    <?php if ($_SESSION['role'] === 'user'): ?>
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropbtn">Chào, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                            <div class="dropdown-content">
                                <a href="/user/profile.php">Trang Cá Nhân</a>
                                <a href="/includes/carts/checkout.php">Thanh Toán</a>
                                <a href="/logout.php">Đăng Xuất</a>
                            </div>
                        </li>
                    <?php endif; ?>

                    <!-- Dropdown cho quản trị viên -->
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropbtn">Quản Trị</a>
                            <div class="dropdown-content">
                                <a href="/admin/dashboard.php">Dashboard</a>

                                <a href="/logout.php">Đăng Xuất</a>
                            </div>
                        </li>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Hiển thị liên kết Đăng Nhập nếu chưa đăng nhập -->
                    <li><a href="/login.php">Đăng Nhập</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

</body>
</html>