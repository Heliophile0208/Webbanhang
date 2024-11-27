<?php
session_start();
include_once '../../../config/database.php';

// Lấy thông tin người dùng từ session
$username = $_SESSION['username']; // Giả sử tên người dùng được lưu trong session

// Truy vấn lấy thông tin người dùng từ bảng users
$getUserQuery = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($getUserQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

// Kiểm tra nếu không tìm thấy người dùng
if (!$user) {
    echo "Không tìm thấy người dùng!";
    exit;
}

// Lấy user id của người dùng từ kết quả
$user_id = $user['id'];

// Truy vấn lấy thông tin khách hàng từ bảng customers dựa trên user_id
$getCustomerQuery = "SELECT * FROM customers WHERE user_id = ?";
$stmt = $conn->prepare($getCustomerQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$customerResult = $stmt->get_result();

// Kiểm tra nếu có dữ liệu khách hàng
$customer = $customerResult->fetch_assoc();
?>

<div class="container-profile">
    <h2>Thông Tin Người Dùng</h2>

    <?php if (!$customer): ?>
        <div class="alert">
            <p>Vui lòng nhập thông tin khách hàng của bạn.</p>
            <a href="customer_form.php" class="btn">Điền thông tin</a>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Thuộc tính</th>
                <th>Giá trị</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Tên người dùng</strong></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
            </tr>
            
            <tr>
                <td><strong>Vai trò</strong></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
            </tr>
        </tbody>
    </table>

    <?php if ($customer): ?>
        <h3>Thông Tin Khách Hàng</h3>
        <table>
            <thead>
                <tr>
                    <th>Thuộc tính</th>
                    <th>Giá trị</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Tên khách hàng</strong></td>
                    <td><?php echo htmlspecialchars($customer['customer_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?php echo htmlspecialchars($customer['customer_email']); ?></td>
                </tr>
                <tr>
                    <td><strong>Số điện thoại</strong></td>
                    <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>Địa chỉ</strong></td>
                    <td><?php echo htmlspecialchars($customer['address_line1']); ?></td>
                </tr>
                <tr>
                    <td><strong>Thành phố</strong></td>
                    <td><?php echo htmlspecialchars($customer['city']); ?></td>
                </tr>
                <tr>
                    <td><strong>Quốc gia</strong></td>
                    <td><?php echo htmlspecialchars($customer['country']); ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
?>

<style>

.container-profile {
    width: 80%;
    margin: 30px auto;
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}


table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 12px;
    text-align: left;
}

th {
    background-color: #4CAF50;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

.alert {
    padding: 20px;
    background-color: #f44336;
    color: white;
    margin-bottom: 20px;
    text-align: center;
    border-radius: 5px;
}

.alert a {
    color: #fff;
    text-decoration: none;
    background-color: #4CAF50;
    padding: 10px 20px;
    border-radius: 5px;
}

.alert a:hover {
    background-color: #45a049;
}

.btn {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
}

.btn:hover {
    background-color: #45a049;
}
</style>