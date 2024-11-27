<?php
session_start();

include_once '../../../config/database.php';

// Lấy danh sách sản phẩm
$productsQuery = "SELECT * FROM products"; 

// Xử lý tìm kiếm sản phẩm với Prepared Statement
if (isset($_POST['submit_search'])) {
    $search = $_POST['search'];
    $searchQuery = " WHERE name LIKE ? OR description LIKE ?";
    $productsQuery .= $searchQuery;
    
    $searchTerm = "%" . $search . "%";
    $stmt = $conn->prepare($productsQuery);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $productsResult = $stmt->get_result();
} else {
    $productsResult = $conn->query($productsQuery);
}

// Kiểm tra kết quả truy vấn
if ($productsResult === FALSE) {
    echo "Lỗi truy vấn: " . $conn->error . "<br>";
}

// Xử lý xóa sản phẩm
if (isset($_POST['delete'])) {
    if (isset($_POST['ProductID']) && !empty($_POST['ProductID'])) {
        $ProductIDToDelete = $_POST['ProductID'];
        $deleteQuery = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        
        if ($stmt) {
            $stmt->bind_param("i", $ProductIDToDelete);
            if ($stmt->execute()) {
                echo "<script>alert('Xóa sản phẩm thành công!'); window.location.href = '/dashboard/products.php';</script>";
                exit; // Dừng script sau khi chuyển hướng
            } else {
                echo "<script>alert('Lỗi khi xóa sản phẩm: " . $conn->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Lỗi chuẩn bị truy vấn xóa sản phẩm.');</script>";
        }
    } else {
        echo "<script>alert('Bạn chưa chọn sản phẩm để xóa.');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm</title>
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
    <h2>Quản lý Sản phẩm</h2>

    <!-- Form Tìm kiếm Sản phẩm -->
    <form method="POST" id="search-form" style="display: inline;">
        <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
        <button type="submit" name="submit_search">Tìm kiếm</button>
    </form>

    <!-- Form Thêm Sản phẩm -->
    <button type="button" onclick="loadAddProductForm();">Thêm Sản phẩm</button>

    <form method="post" id="delete-form" style="display: inline;">
<!-- Nút sửa sản phẩm -->
        <button type="button" class="editButton" onclick="setEditProduct();">Sửa</button>
        <button type="submit" name="delete" onclick="return confirmDelete();">Xóa</button>
        <table>
            <thead>
                <tr>
                    <th>Chọn</th>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Mô tả</th>
                    <th>Giá</th>
                    <th>Tồn kho</th>
                    <th>Ngày tạo</th>
                </tr>
            </thead>
            <tbody id="products-table">
                <?php
                if (isset($productsResult) && $productsResult->num_rows > 0) {
                    while ($row = $productsResult->fetch_assoc()) {
                        echo "<tr>
                            <td><input type='radio' name='ProductID' value='" . htmlspecialchars($row['id']) . "' required></td>
                            <td>" . htmlspecialchars($row['id']) . "</td>
                            <td>" . htmlspecialchars($row['name']) . "</td>
                            <td>" . htmlspecialchars($row['description']) . "</td>
                            <td>" . htmlspecialchars($row['price']) . "</td>
                            <td>" . htmlspecialchars($row['stock']) . "</td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Không Có Sản phẩm nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>

       

    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // AJAX chỉnh sửa sản phẩm
        function setEditProduct() {
            const selectedRadio = document.querySelector('input[name="ProductID"]:checked');
            if (selectedRadio) {
                const ProductID = selectedRadio.value;
                $.ajax({
                    url: 'dashboard/products/edit_product.php',  // Tệp sẽ xử lý chỉnh sửa
                    method: 'GET',
                    data: { ProductID: ProductID },
                    success: function(response) {
                        // Hiển thị nội dung chỉnh sửa trong phần content
                        $('#dashboard-content').html(response); 
                    },
                    error: function() {
                        alert("Có lỗi khi tải dữ liệu chỉnh sửa.");
                    }
                });
            } else {
                alert("Bạn chưa chọn sản phẩm để sửa.");
            }
        }

        function confirmDelete() {
            const selectedRadio = document.querySelector('input[name="ProductID"]:checked');
            if (!selectedRadio) {
                alert("Bạn chưa chọn sản phẩm để xóa.");
                return false; // Ngăn không cho form gửi đi
            }
            return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');
        }

        // AJAX tìm kiếm sản phẩm
        $('#search-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const searchTerm = $('input[name="search"]').val();
            $.ajax({
                url: 'dashboard/products/products.php',
                method: 'POST',
                data: { submit_search: true, search: searchTerm },
                success: function(response) {
                    // Cập nhật bảng sản phẩm
                    $('#products-table').html($(response).find('#products-table').html());
                }
            });
        });

        // AJAX xóa sản phẩm
        $('#delete-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định
            const selectedRadio = $('input[name="ProductID"]:checked');
            if (!selectedRadio.length) {
                alert("Bạn chưa chọn sản phẩm để xóa.");
                return;
            }
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                const ProductID = selectedRadio.val();
                $.ajax({
                    url: 'dashboard/products/products.php',
                    method: 'POST',
                    data: { delete: true, ProductID: ProductID },
                    success: function(response) {
                        alert('Xóa sản phẩm thành công!');
                        loadProducts(); // Reload lại trang để cập nhật danh sách sản phẩm
                    }
                });
            }
        });

        // Hàm tải trang thêm sản phẩm vào phần dashboard-content
        function loadAddProductForm() {
            $.ajax({
                url: 'dashboard/products/add_product.php',  // Truyền tới trang thêm sản phẩm
                method: 'GET',
                success: function(response) {
                    $('#dashboard-content').html(response);  // Hiển thị form thêm sản phẩm
                },
                error: function() {
                    alert("Có lỗi khi tải trang thêm sản phẩm.");
                }
            });
        }

        // Hàm tải lại danh sách sản phẩm sau khi thêm mới
        function loadProducts() {
            $.ajax({
                url: 'dashboard/products/products.php',  // Truyền đến trang xử lý lấy lại danh sách sản phẩm
                method: 'POST',
                success: function(response) {
                    $('#products-table').html($(response).find('#products-table').html());
                },
                error: function() {
                    alert('Có lỗi khi tải lại danh sách sản phẩm.');
                }
            });
        }
    </script>
</body>
</html>
