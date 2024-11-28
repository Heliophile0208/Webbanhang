<?php

include '../../config/database.php'; // Kết nối với database

// Lấy số liệu thống kê tổng quan
$query_stats = "SELECT COUNT(id) AS total_orders, SUM(total) AS total_revenue FROM orders WHERE status = 'completed'";
$result = $conn->query($query_stats);
$order_data = $result->fetch_assoc();
$total_orders = $order_data['total_orders'] ?? 0;
$total_revenue = $order_data['total_revenue'] ?? 0;

$query_products = "SELECT COUNT(id) AS total_products FROM products";
$result = $conn->query($query_products);
$product_data = $result->fetch_assoc();
$total_products = $product_data['total_products'] ?? 0;

$query_customers = "SELECT COUNT(customer_id) AS total_customers FROM customers";
$result = $conn->query($query_customers);
$customer_data = $result->fetch_assoc();
$total_customers = $customer_data['total_customers'] ?? 0;

// Lấy số lượng đơn hàng pending
$query_pending_orders = "SELECT COUNT(id) AS pending_orders FROM orders WHERE status = 'pending'";
$result = $conn->query($query_pending_orders);
$pending_data = $result->fetch_assoc();
$pending_orders = $pending_data['pending_orders'] ?? 0;

// Lấy số lượng đơn hàng cancelled
$query_cancelled_orders = "SELECT COUNT(id) AS cancelled_orders FROM orders WHERE status = 'cancelled'";
$result = $conn->query($query_cancelled_orders);
$cancelled_data = $result->fetch_assoc();
$cancelled_orders = $cancelled_data['cancelled_orders'] ?? 0;

// Lấy doanh thu chờ thanh toán từ các đơn hàng đang chờ xử lý
$query_pending_revenue = "SELECT SUM(total) AS pending_revenue FROM orders WHERE status = 'pending'";
$result = $conn->query($query_pending_revenue);
$pending_revenue_data = $result->fetch_assoc();
$pending_revenue = $pending_revenue_data['pending_revenue'] ?? 0;

// Lấy các sản phẩm sắp hết hàng (stock < 10)
$query_low_stock = "SELECT name, stock FROM products WHERE stock < 10";
$result_low_stock = $conn->query($query_low_stock);
$low_stock_products = [];
while ($row = $result_low_stock->fetch_assoc()) {
    $low_stock_products[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>


    <link rel="stylesheet" href="../../css/dashboard.css">

</head>
<body>

<h1>Dashboard</h1>

<div class="dashboard-container">
    <div class="dashboard-box total-orders">
        <h3>Tổng số đơn hàng</h3>
        <p><?php echo $total_orders; ?></p>
    </div>

    <div class="dashboard-box total-revenue">
        <h3>Tổng doanh thu</h3>
        <p><?php echo number_format($total_revenue, 2); ?> VND</p>
    </div>

    <div class="dashboard-box total-products">
        <h3>Tổng số sản phẩm</h3>
        <p><?php echo $total_products; ?></p>
    </div>

    <div class="dashboard-box total-customers">
        <h3>Tổng số khách hàng</h3>
        <p><?php echo $total_customers; ?></p>
    </div>

    <div class="dashboard-box pending-orders">
        <h3>Đơn hàng đang chờ</h3>
        <p><?php echo $pending_orders; ?></p>
    </div>

    <div class="dashboard-box cancelled-orders">
        <h3>Đơn hàng đã hủy</h3>
        <p><?php echo $cancelled_orders; ?></p>
    </div>

    <div class="dashboard-box pending-revenue">
        <h3>Doanh thu chờ thanh toán</h3>
        <p><?php echo number_format($pending_revenue, 2); ?> VND</p>
    </div>

    <div class="dashboard-box low-stock">
        <h3>Sản phẩm sắp hết hàng</h3>
        <?php if (count($low_stock_products) > 0): ?>
            <ul>
                <?php foreach ($low_stock_products as $product): ?>
                    <li><strong><?php echo $product['name']; ?></strong> - Còn <?php echo $product['stock']; ?> sản phẩm</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Không có sản phẩm sắp hết hàng.</p>
        <?php endif; ?>
    </div>
</div>
</body>
<style>
/* Container chính cho toàn bộ dashboard */
.dashboard-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px; 
    padding: 20px;
    background-color: #f8f9fa;
}

/* Ô thống kê tổng quan */
.dashboard-box {
    padding: 20px;
    border-radius: 8px; /* Bo góc */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center; 
    color: white; 
    font-weight: bold;
    transition: transform 0.3s, box-shadow 0.3s; 
}

/* Hiệu ứng hover */
.dashboard-box:hover {
    transform: translateY(-5px); 
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
}

/* Tiêu đề các ô */
.dashboard-box h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

/* Nội dung số liệu */
.dashboard-box p {
    font-size: 24px;
    margin: 0;
}

/* Màu sắc cho từng ô */
.dashboard-box.total-orders {
    background-color: #007bff; 
}

.dashboard-box.total-revenue {
    background-color: #28a745; 
}

.dashboard-box.total-products {
    background-color: #17a2b8; 
}

.dashboard-box.total-customers {
    background-color: #ffc107; 
}

.dashboard-box.pending-orders {
    background-color: #fd7e14; 
}

.dashboard-box.cancelled-orders {
    background-color: #dc3545; 
}

.dashboard-box.pending-revenue {
    background-color: #6c757d; 
}

/* Ô cảnh báo sản phẩm sắp hết hàng */
.dashboard-box.low-stock {
    background-color: #fff4e5;
    color: #555; 
    border-left: 5px solid #ffa502; 
    text-align: left; 
}

.low-stock h3 {
    font-size: 18px;
    color: #ff6f00;
    margin-bottom: 10px;
}

.low-stock ul {
    list-style: none;
    padding: 0;
    margin-top: 10px;
}

.low-stock ul li {
    font-size: 16px;
    color: #555;
    margin-bottom: 8px;
}


@media (max-width: 768px) {
    .dashboard-container {
        grid-template-columns: 1fr;
        padding: 10px;
    }
}
</style>