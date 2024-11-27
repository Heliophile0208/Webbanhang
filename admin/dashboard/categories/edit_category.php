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

if (isset($_GET['CategoryID'])) {
    $CategoryID = $_GET['CategoryID'];
    $categoryQuery = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($categoryQuery);
    $stmt->bind_param("i", $CategoryID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
    } else {
        echo "Không tìm thấy thể loại.";
        exit;
    }

    // Lấy danh sách vai trò từ ENUM
    $groupQuery = "SHOW COLUMNS FROM categories LIKE 'group'";
    $groupResult = $conn->query($groupQuery);
    $groups = [];
    if ($groupResult && $groupResult->num_rows > 0) {
        $row = $groupResult->fetch_assoc();
        preg_match_all("/'([^']+)'/", $row['Type'], $matches);
        $groups = $matches[1];
    }
    ?>
    <h2>Chỉnh sửa thông tin thể loại</h2>
    <form id="editCategoryForm">
        <input type="hidden" name="CategoryID" value="<?php echo $category['id']; ?>">
        
        <label for="name">Tên thể loại:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required><br>

        <label for="description">Mô tả:</label>
        <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($category['description']); ?>" required><br>

        <label for="group">Nhóm:</label>
        <select id="group" name="group" required>
            <?php foreach ($groups as $group): ?>
                <option value="<?php echo $group; ?>" <?php echo $category['group'] === $group ? 'selected' : ''; ?>>
                    <?php echo ucfirst($group); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit">Cập nhật</button>
    </form>
    <?php
} else {
    echo "Không có CategoryID để sửa.";
}
$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Lắng nghe sự kiện submit form
    document.getElementById("editCategoryForm").addEventListener("submit", function(e) {
        e.preventDefault(); // Ngăn trang web tải lại

        // Lấy dữ liệu từ form
        var formData = new FormData(this);

        $.ajax({
            url: '/admin/dashboard/categories/update_category.php', // URL xử lý cập nhật
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.trim() === "success") {
                    alert("Cập nhật thành công!");
                    // Bạn có thể cập nhật lại bảng categories hoặc làm gì đó sau khi thành công
                    $('#dashboard-content').load('/admin/dashboard/categories/categories.php'); // Ví dụ tải lại trang categories.php trong phần content
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