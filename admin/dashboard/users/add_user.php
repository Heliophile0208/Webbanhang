<?php
session_start();
include_once '../../../config/database.php';

// Xử lý form thêm người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($password) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đủ thông tin']);
    } else {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Chuẩn bị câu truy vấn thêm người dùng vào cơ sở dữ liệu
        $insertQuery = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        if ($stmt) {
            $stmt->bind_param("sss", $username, $hashedPassword, $role);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Thêm người dùng thành công!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Lỗi khi thêm người dùng']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi chuẩn bị truy vấn']);
        }
    }
    $conn->close();
    exit;
}
?>

<!-- HTML form cho thêm người dùng -->
<form id="add-user-form" method="POST">
    <label for="username">Tên người dùng:</label>
    <input type="text" name="username" id="username" required>

    <label for="password">Mật khẩu:</label>
    <input type="password" name="password" id="password" required>

    <label for="role">Vai trò:</label>
    <select name="role" id="role" required>
        <option value="admin">Admin</option>
        <option value="user">User</option>
    </select>

    <button type="submit">Thêm Người Dùng</button>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

$('#add-user-form').submit(function(e) {
    e.preventDefault(); // Ngừng gửi form mặc định

    const username = $('#username').val();
    const password = $('#password').val();
    const role = $('#role').val();

    $.ajax({
        url: 'dashboard/users/add_user.php',
        method: 'POST',
        data: {
            username: username,
            password: password,
            role: role
        },
        success: function(response) {
            const result = JSON.parse(response);
            alert(result.message);

            if (result.status === 'success') {
                // Sau khi thêm thành công, tải lại danh sách người dùng
                loadUsers(); // Hàm này sẽ gọi AJAX tải lại danh sách người dùng
            }
        },
        error: function() {
            alert('Có lỗi khi thêm người dùng.');
        }
    });
});

// Hàm tải lại danh sách người dùng vào phần #dashboard-content
function loadUsers() {
    $.ajax({
        url: 'dashboard/users/users.php', // Tải lại danh sách người dùng
        method: 'GET',
        success: function(response) {
            // Cập nhật phần #dashboard-content với nội dung danh sách người dùng
            $('#dashboard-content').html(response);
        },
        error: function() {
            alert('Có lỗi khi tải lại danh sách người dùng.');
        }
    });
}
</script>

<style>
/* Đặt kiểu cho toàn bộ trang */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

/* Đặt kiểu cho các phần tử trong form */
form {
    background-color: #fff;
    max-width: 500px;
    margin: 30px auto;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Đặt kiểu cho các nhãn trong form */
form label {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}

/* Đặt kiểu cho các input và select trong form */
form input[type="text"],
form input[type="password"],
form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

/* Đặt kiểu cho button */
form button[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    width: 100%;
    transition: background-color 0.3s;
}

/* Thay đổi màu sắc button khi hover */
form button[type="submit"]:hover {
    background-color: #45a049;
}

/* Đặt kiểu cho các thông báo lỗi hoặc thành công */
.alert {
    padding: 10px;
    margin-top: 20px;
    text-align: center;
    border-radius: 4px;
    font-size: 16px;
}

/* Kiểu cho thông báo thành công */
.alert.success {
    background-color: #4CAF50;
    color: white;
}

/* Kiểu cho thông báo lỗi */
.alert.error {
    background-color: #f44336;
    color: white;
}
</style>

