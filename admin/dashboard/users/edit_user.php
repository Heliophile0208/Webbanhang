<style>
     
    form {
        background-color: #fff;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        margin: 0 auto;
    }

    label {
        font-size: 14px;
        margin-bottom: 5px;
        display: block;
        font-weight: bold;
    }

    input[type="text"],
    input[type="password"],
    select,
    button {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
    }

    input[type="text"]:focus,
    input[type="password"]:focus,
    select:focus {
        border-color: #4CAF50;
    }

    button {
        background-color: #4CAF50;
        color: white;
        font-size: 16px;
        cursor: pointer;
        border: none;
    }

    button:hover {
        background-color: #45a049;
    }

    button:disabled {
        background-color: #ddd;
        cursor: not-allowed;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }
</style>



<?php
session_start();
include_once '../../../config/database.php';

if (isset($_GET['UserID'])) {
    $UserID = $_GET['UserID'];
    $userQuery = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param("i", $UserID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "Không tìm thấy người dùng.";
        exit;
    }

    // Lấy danh sách vai trò từ ENUM
    $roleQuery = "SHOW COLUMNS FROM users LIKE 'role'";
    $roleResult = $conn->query($roleQuery);
    $roles = [];
    if ($roleResult && $roleResult->num_rows > 0) {
        $row = $roleResult->fetch_assoc();
        preg_match_all("/'([^']+)'/", $row['Type'], $matches);
        $roles = $matches[1];
    }
    ?>
    <h2>Chỉnh sửa thông tin người dùng</h2>
    <form id="editUserForm">
        <input type="hidden" name="UserID" value="<?php echo $user['id']; ?>">
        
        <label for="username">Tên đăng nhập:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>

        <label for="password">Mật khẩu mới (để trống nếu không thay đổi):</label>
        <input type="password" id="password" name="password"><br>

        <label for="role">Vai trò:</label>
        <select id="role" name="role" required>
            <?php foreach ($roles as $role): ?>
                <option value="<?php echo $role; ?>" <?php echo $user['role'] === $role ? 'selected' : ''; ?>>
                    <?php echo ucfirst($role); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Cập nhật</button>
    </form>
    <?php
} else {
    echo "Không có UserID để sửa.";
}
$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Lắng nghe sự kiện submit form
    document.getElementById("editUserForm").addEventListener("submit", function(e) {
        e.preventDefault(); // Ngăn trang web tải lại

        // Lấy dữ liệu từ form
        var formData = new FormData(this);

        $.ajax({
            url: '/admin/dashboard/users/update_user.php', // URL xử lý cập nhật
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.trim() === "success") {
                    alert("Cập nhật thành công!");
                    // Bạn có thể cập nhật lại bảng users hoặc làm gì đó sau khi thành công
                    $('#dashboard-content').load('/admin/dashboard/users/users.php'); // Ví dụ tải lại trang users.php trong phần content
                } else {
                    alert("Có lỗi xảy ra: " + response);
                }
            },
            error: function() {
                alert("Lỗi kết nối tới máy chủ!");
            }
        });
    });
</script>