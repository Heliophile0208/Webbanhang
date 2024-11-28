<?php
session_start();

include_once '../../../config/database.php';

// Lấy danh sách danh mục
$categoriesQuery = "SELECT * FROM categories"; 

// Xử lý tìm kiếm danh mục với Prepared Statement
if (isset($_POST['submit_search'])) {
    $search = $_POST['search'];
    $searchQuery = " WHERE name LIKE ? ";
    $categoriesQuery .= $searchQuery;
    
    $searchTerm = "%" . $search . "%";
    $stmt = $conn->prepare($categoriesQuery);
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $categoriesResult = $stmt->get_result();
} else {
    $categoriesResult = $conn->query($categoriesQuery);
}

// Kiểm tra kết quả truy vấn
if ($categoriesResult === FALSE) {
    echo "Lỗi truy vấn: " . $conn->error . "<br>";
}

// Xử lý xóa danh mục
if (isset($_POST['delete'])) {
    if (isset($_POST['CategoryID']) && !empty($_POST['CategoryID'])) {
        $CategoryIDToDelete = $_POST['CategoryID'];
        $deleteQuery = "DELETE FROM categories WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        
        if ($stmt) {
            $stmt->bind_param("i", $CategoryIDToDelete);
            if ($stmt->execute()) {
                echo "<script>alert('Xóa danh mục thành công!'); window.location.href = '/dashboard/categories.php';</script>";
                exit; // Dừng script sau khi chuyển hướng
            } else {
                echo "<script>alert('Lỗi khi xóa danh mục: " . $conn->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Lỗi chuẩn bị truy vấn xóa danh mục.');</script>";
        }
    } else {
        echo "<script>alert('Bạn chưa chọn danh mục để xóa.');</script>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục</title>
    <style>
        input[type="text"], button {
            padding: 10px;
            margin: 10px;
        }
        button[type="submit"] {
            margin-right: 10px;
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
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <h2>Quản lý Danh mục</h2>

    <!-- Form Tìm kiếm Danh mục -->
    <form method="POST" id="search-form" style="display: inline;">
        <input type="text" name="search" placeholder="Tìm kiếm danh mục..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
        <button type="submit" name="submit_search">Tìm kiếm</button>
    </form>

    <!-- Form Thêm Danh mục -->
    <button type="button" onclick="loadAddCategoryForm();">Thêm Danh mục</button>
    <form method="post" id="delete-form" style="display: inline;">
        <table>
            <thead>
                <tr>
                    <th>Chọn</th>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Mô tả</th>
                    <th>Nhóm</th>
                </tr>
            </thead>
            <tbody id="categories-table">
                <?php
                if (isset($categoriesResult) && $categoriesResult->num_rows > 0) {
                    while ($row = $categoriesResult->fetch_assoc()) {
                        echo "<tr>
                            <td><input type='radio' name='CategoryID' value='" . htmlspecialchars($row['id']) . "' required></td>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . htmlspecialchars($row['name']) . "</td>
                            <td>" . htmlspecialchars($row['description']) . "</td>
                            <td>" . htmlspecialchars($row['group']) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Không Có Danh mục nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Nút sửa danh mục -->
        <button type="button" class="editButton" onclick="setEditCategory();">Sửa Danh mục</button>
        <button type="submit" name="delete" onclick="return confirmDelete();">Xóa Danh mục</button>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // AJAX chỉnh sửa danh mục
        function setEditCategory() {
            const selectedRadio = document.querySelector('input[name="CategoryID"]:checked');
            if (selectedRadio) {
                const CategoryID = selectedRadio.value;
                $.ajax({
                    url: 'dashboard/categories/edit_category.php',  // Tệp sẽ xử lý chỉnh sửa
                    method: 'GET',
                    data: { CategoryID: CategoryID },
                    success: function(response) {
                        // Hiển thị nội dung chỉnh sửa trong phần content
                        $('#dashboard-content').html(response); 
                    },
                    error: function() {
                        alert("Có lỗi khi tải dữ liệu chỉnh sửa.");
                    }
                });
            } else {
                alert("Bạn chưa chọn danh mục để sửa.");
            }
        }

        function confirmDelete() {
            const selectedRadio = document.querySelector('input[name="CategoryID"]:checked');
            if (!selectedRadio) {
                alert("Bạn chưa chọn danh mục để xóa.");
                return false; // Ngăn không cho form gửi đi
            }
            return confirm('Bạn có chắc chắn muốn xóa danh mục này?');
        }

        // AJAX tìm kiếm danh mục
        $('#search-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const searchTerm = $('input[name="search"]').val();
            $.ajax({
                url: 'dashboard/categories/categories.php',
                method: 'POST',
                data: { submit_search: true, search: searchTerm },
                success: function(response) {
                    // Cập nhật bảng danh mục
                    $('#categories-table').html($(response).find('#categories-table').html());
                }
            });
        });

        // AJAX xóa danh mục
        $('#delete-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const selectedRadio = $('input[name="CategoryID"]:checked');
            if (!selectedRadio.length) {
                alert("Bạn chưa chọn danh mục để xóa.");
                return;
            }
            if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
                const CategoryID = selectedRadio.val();
                $.ajax({
                    url: 'dashboard/categories/categories.php',
                    method: 'POST',
                    data: { delete: true, CategoryID: CategoryID },
                    success: function(response) {
                        alert('Xóa danh mục thành công!');
                        loadCategories(); // Reload lại trang để cập nhật danh sách danh mục
                    }
                });
            }
        });

        // Hàm gửi CategoryID qua AJAX và cập nhật nội dung trong dashboard-content
        // Hàm tải trang thêm danh mục vào phần dashboard-content
        function loadAddCategoryForm() {
            $.ajax({
                url: 'dashboard/categories/add_category.php',  // Truyền tới trang thêm danh mục
                method: 'GET',
                success: function(response) {
                    $('#dashboard-content').html(response);  // Hiển thị form thêm danh mục
                },
                error: function() {
                    alert("Có lỗi khi tải trang thêm danh mục.");
                }
            });
        }

        // Sau khi thêm danh mục thành công
        function handleAddCategorySuccess() {
            alert('Thêm danh mục thành công!');
            loadCategories();  // Cập nhật lại danh sách danh mục
        }

        // Hàm tải lại danh sách danh mục sau khi thêm mới
        function loadCategories() {
            $.ajax({
                url: 'dashboard/categories/categories.php',  // Truyền đến trang xử lý lấy lại danh sách danh mục
                method: 'POST',
                success: function(response) {
                    $('#categories-table').html($(response).find('#categories-table').html());
                },
                error: function() {
                    alert('Có lỗi khi tải lại danh sách danh mục.');
                }
            });
        }
    </script>

</body>
</html>