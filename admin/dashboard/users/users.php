<?php
session_start();

include_once '../../../config/database.php';

// Lấy danh sách người dùng
$usersQuery = "SELECT * FROM users"; 

// Xử lý tìm kiếm người dùng với Prepared Statement
if (isset($_POST['submit_search'])) {
    $search = $_POST['search'];
    $searchQuery = " WHERE username LIKE ? OR role LIKE ?";
    $usersQuery .= $searchQuery;
    
    $searchTerm = "%" . $search . "%";
    $stmt = $conn->prepare($usersQuery);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $usersResult = $stmt->get_result();
} else {
    $usersResult = $conn->query($usersQuery);
}

// Kiểm tra kết quả truy vấn
if ($usersResult === FALSE) {
    echo "Lỗi truy vấn: " . $conn->error . "<br>";
}

// Xử lý xóa người dùng
if (isset($_POST['delete'])) {
    if (isset($_POST['UserID']) && !empty($_POST['UserID'])) {
        $UserIDToDelete = $_POST['UserID'];
        $deleteQuery = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        
        if ($stmt) {
            $stmt->bind_param("i", $UserIDToDelete);
            if ($stmt->execute()) {
                echo "<script>alert('Xóa user thành công!'); window.location.href = '/dashboard/users.php';</script>";
                exit; // Dừng script sau khi chuyển hướng
            } else {
                echo "<script>alert('Lỗi khi xóa user: " . $conn->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Lỗi chuẩn bị truy vấn xóa user.');</script>";
        }
    } else {
        echo "<script>alert('Bạn chưa chọn user để xóa.');</script>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng</title>
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
    <h2>Quản lý Người dùng</h2>

    <!-- Form Tìm kiếm User -->
    <form method="POST" id="search-form" style="display: inline;">
        <input type="text" name="search" placeholder="Tìm kiếm user..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
        <button type="submit" name="submit_search">Tìm kiếm</button>
    </form>


    <!-- Form Thêm User -->
  <button type="button" onclick="loadAddUserForm();">Thêm User</button>
    <form method="post" id="delete-form" style="display: inline;">
        <table>
            <thead>
                <tr>
                    <th>Chọn</th>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Ngày Tạo</th>
                </tr>
            </thead>
            <tbody id="users-table">
                <?php
                if (isset($usersResult) && $usersResult->num_rows > 0) {
                    while ($row = $usersResult->fetch_assoc()) {
                        echo "<tr>
                            <td><input type='radio' name='UserID' value='" . htmlspecialchars($row['id']) . "' required></td>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . htmlspecialchars($row['username']) . "</td>
                            <td>" . htmlspecialchars($row['role']) . "</td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Không Có User nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Nút sửa user -->
        <button type="button" class="editButton" onclick="setEditUser();">Sửa User</button>
        <button type="submit" name="delete" onclick="return confirmDelete();">Xóa User</button>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        
  // AJAX chỉnh sửa người dùng
    function setEditUser() {
        const selectedRadio = document.querySelector('input[name="UserID"]:checked');
        if (selectedRadio) {
            const UserID = selectedRadio.value;
            $.ajax({
                url: 'dashboard/users/edit_user.php',  // Tệp sẽ xử lý chỉnh sửa
                method: 'GET',
                data: { UserID: UserID },
                success: function(response) {
                    // Hiển thị nội dung chỉnh sửa trong phần content
                    $('#dashboard-content').html(response); 
                },
                error: function() {
                    alert("Có lỗi khi tải dữ liệu chỉnh sửa.");
                }
            });
        } else {
            alert("Bạn chưa chọn user để sửa.");
        }
    }

        function confirmDelete() {
            const selectedRadio = document.querySelector('input[name="UserID"]:checked');
            if (!selectedRadio) {
                alert("Bạn chưa chọn user để xóa.");
                return false; // Ngăn không cho form gửi đi
            }
            return confirm('Bạn có chắc chắn muốn xóa user này?');
        }

        // AJAX tìm kiếm người dùng
        $('#search-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const searchTerm = $('input[name="search"]').val();
            $.ajax({
                url: 'dashboard/users/users.php',
                method: 'POST',
                data: { submit_search: true, search: searchTerm },
                success: function(response) {
                    // Cập nhật bảng người dùng
                    $('#users-table').html($(response).find('#users-table').html());
                }
            });
        });

        // AJAX xóa người dùng
        $('#delete-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const selectedRadio = $('input[name="UserID"]:checked');
            if (!selectedRadio.length) {
                alert("Bạn chưa chọn user để xóa.");
                return;
            }
            if (confirm('Bạn có chắc chắn muốn xóa user này?')) {
                const UserID = selectedRadio.val();
                $.ajax({
                    url: 'dashboard/users/users.php',
                    method: 'POST',
                    data: { delete: true, UserID: UserID },
                    success: function(response) {
                        alert('Xóa user thành công!');
                        loadUsers(); // Reload lại trang để cập nhật danh sách người dùng
                    }
                });
            }
        });

// Hàm gửi UserID qua AJAX và cập nhật nội dung trong dashboard-content
// Hàm tải trang thêm người dùng vào phần dashboard-content
function loadAddUserForm() {
    $.ajax({
        url: 'dashboard/users/add_user.php',  // Truyền tới trang thêm người dùng
        method: 'GET',
        success: function(response) {
            $('#dashboard-content').html(response);  // Hiển thị form thêm người dùng
        },
        error: function() {
            alert("Có lỗi khi tải trang thêm user.");
        }
    });
}

// Sau khi thêm người dùng thành công
function handleAddUserSuccess() {
    alert('Thêm người dùng thành công!');
    loadUsers();  // Cập nhật lại danh sách người dùng
}
    // Hàm tải lại danh sách người dùng sau khi thêm mới
    function loadUsers() {
        $.ajax({
            url: 'dashboard/users/users.php',  // Truyền đến trang xử lý lấy lại danh sách người dùng
            method: 'POST',
            success: function(response) {
                $('#users-table').html($(response).find('#users-table').html());
            },
            error: function() {
                alert('Có lỗi khi tải lại danh sách người dùng.');
            }
        });
    }
</script>
    
</body>
</html>