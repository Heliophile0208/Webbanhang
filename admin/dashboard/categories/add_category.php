
<?php
session_start();
include_once '../../../config/database.php';


// Hàm lấy các giá trị ENUM của cột group trong bảng categories
function getEnumValues($table, $column) {
    global $conn;
    // Truy vấn để lấy COLUMN_TYPE của cột ENUM
    $query = "
        SELECT COLUMN_TYPE 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_NAME = ? 
        AND COLUMN_NAME = ? 
        AND TABLE_SCHEMA = DATABASE();
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Lấy các giá trị ENUM từ COLUMN_TYPE
        if (preg_match("/^enum\('(.*)'\)$/", $row['COLUMN_TYPE'], $matches)) {
            $enumValues = explode(',', $matches[1]); // Tách các giá trị ENUM
            // Loại bỏ dấu nháy đơn và trả về mảng các giá trị
            $enumValues = array_map(function($value) {
                return trim($value, "'");
            }, $enumValues);
            return $enumValues;
        }
    }
    
    // Nếu không có giá trị ENUM hoặc có lỗi, trả về mảng rỗng
    return [];
}




// Xử lý form thêm thể loại
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $group = $_POST['group'];

    if (empty($name) || empty($description) || empty($group)) {
        echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đủ thông tin']);
    } else {
        // Chuẩn bị câu truy vấn thêm thể loại vào cơ sở dữ liệu
        $insertQuery = "INSERT INTO categories (name, description, `group`) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        if ($stmt) {
            $stmt->bind_param("sss", $name, $description, $group);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Thêm thể loại thành công!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Lỗi khi thêm thể loại']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi chuẩn bị truy vấn']);
        }
    }
    $conn->close();
    exit;
}

// Lấy danh sách giá trị ENUM cho cột 'group' trong bảng 'categories'
$groups = getEnumValues('categories', 'group');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Thể Loại</title>
    <link rel="stylesheet" href="style.css">
</head>
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

<body>
    <div class="container">
        <h2>Thêm Thể Loại</h2>

        <form id="add-category-form" method="POST">
            <label for="name">Tên thể loại:</label>
            <input type="text" name="name" id="name" required>

            <label for="description">Mô tả:</label>
            <input type="text" name="description" id="description" required>

            <label for="group">Nhóm:</label>
            <select name="group" id="group" required>
                <?php
                // Tạo các option cho nhóm từ giá trị enum
                foreach ($groups as $group) {
                    // Xóa dấu nháy đơn nếu có
                    $group = trim($group, "'");
                    echo "<option value=\"$group\">$group</option>";
                }
                ?>
            </select>

            <button type="submit">Thêm Thể Loại</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
</body>
</html>

<script>
$('#add-category-form').submit(function(e) {
    e.preventDefault(); // Ngừng gửi form mặc định

    const name = $('#name').val();
    const description = $('#description').val();
    const group = $('#group').val();

    $.ajax({
        url: 'dashboard/categories/add_category.php',
        method: 'POST',
        data: {
            name: name,
            description: description,
            group: group
        },
        success: function(response) {
            const result = JSON.parse(response);
            alert(result.message);

            if (result.status === 'success') {
                // Sau khi thêm thành công, tải lại danh sách thể loại
                loadCategories(); // Hàm này sẽ gọi AJAX tải lại danh sách thể loại
            }
        },
        error: function() {
            alert('Có lỗi khi thêm thể loại.');
        }
    });
});

// Hàm tải lại danh sách thể loại vào phần #dashboard-content
function loadCategories() {
    $.ajax({
        url: 'dashboard/categories/categories.php', // Tải lại danh sách thể loại
        method: 'GET',
        success: function(response) {
            // Cập nhật phần #dashboard-content với nội dung danh sách thể loại
            $('#dashboard-content').html(response);
        },
        error: function() {
            alert('Có lỗi khi tải lại danh sách thể loại.');
        }
    });
}
</script>