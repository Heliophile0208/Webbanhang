<?php
session_start();  // Khởi tạo session để lấy dữ liệu từ session
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
    <!-- Thêm liên kết đến Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&family=Lobster&display=swap" rel="stylesheet">

</head> <title>Thời Trang Nữ</title>

    <style>
        header {
            background-color: #f4f4f9;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            position: relative;
        }

        .container {
            display: flex;
            justify-content: space-between;
            align-items: center; /* Căn chỉnh tất cả các phần tử theo chiều dọc */
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center; /* Căn chỉnh các mục trong menu theo chiều dọc */
        }

        nav ul li {
            margin-right: 20px;
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            display: inline-block; /* Đảm bảo các mục này được căn chỉnh đều */
        }

        nav ul li a:hover {
            color: #007BFF;
        }

        /* CSS cho phần chào tên người dùng */
        header nav ul li a {
            font-size: 14px;
            font-style: italic;
            color: #555;
            line-height: 1; /* Đảm bảo không có khoảng cách bất thường */
        }

        header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            line-height: 1; /* Căn chỉnh header */
        }

        /* CSS cho dropdown menu */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropbtn {
            background-color: transparent;
            color: #333;
            padding: 10px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            display: inline-block;
            line-height: 1; /* Đảm bảo căn chỉnh dọc */
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            font-weight: normal;
        }

        .dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown:hover .dropbtn {
            color: #007BFF;
        }
/* Dành cho logo */
.logo {
    font-family: 'Lobster', cursive;  
    font-size: 40px;  
    color: #333;  
    letter-spacing: 2px;  
    
}


.logo-alternate {
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 40px;
    color: #007BFF;
}
    </style>
</head>
<body>

<header>
    <div class="container">
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
                                <a href="/admin/users.php">Người Dùng</a>
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