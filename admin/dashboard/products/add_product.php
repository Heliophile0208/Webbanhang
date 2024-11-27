<?php
session_start();
include_once '../../../config/database.php';

// Hàm xử lý upload ảnh
function handleImageUpload() {
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../images/';
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $uploadPath = $uploadDir . $fileName;

        // Kiểm tra định dạng file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            return ['status' => 'error', 'message' => 'Chỉ cho phép ảnh JPG, PNG, GIF'];
        }

        // Di chuyển file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $image_url = 'images/' . $fileName;
        } else {
            return ['status' => 'error', 'message' => 'Lỗi khi tải ảnh lên'];
        }
    }

    return ['status' => 'success', 'image_url' => $image_url];
}

// Hàm kiểm tra thông tin sản phẩm
function validateProductData($name, $description, $price, $stock) {
    if (empty($name) || empty($description) || empty($price) || empty($stock)) {
        return ['status' => 'error', 'message' => 'Vui lòng điền đủ thông tin'];
    }
    return ['status' => 'success'];
}

// Hàm thêm sản phẩm vào cơ sở dữ liệu
function addProductToDatabase($name, $description, $price, $stock, $image_url) {
    global $conn;
    
    $insertQuery = "INSERT INTO products (name, description, price, stock, image_url) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);

    if ($stmt) {
        $stmt->bind_param("ssdss", $name, $description, $price, $stock, $image_url);
        if ($stmt->execute()) {
            return ['status' => 'success', 'message' => 'Thêm sản phẩm thành công!'];
        } else {
            return ['status' => 'error', 'message' => 'Lỗi khi thêm sản phẩm'];
        }
        $stmt->close();
    } else {
        return ['status' => 'error', 'message' => 'Lỗi chuẩn bị truy vấn'];
    }
}

// Xử lý form thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Kiểm tra thông tin sản phẩm
    $validation = validateProductData($name, $description, $price, $stock);
    if ($validation['status'] === 'error') {
        echo json_encode($validation);
        exit;
    }

    // Xử lý upload ảnh
    $imageUploadResult = handleImageUpload();
    if ($imageUploadResult['status'] === 'error') {
        echo json_encode($imageUploadResult);
        exit;
    }

    $image_url = $imageUploadResult['image_url'];

    // Thêm sản phẩm vào cơ sở dữ liệu
    $result = addProductToDatabase($name, $description, $price, $stock, $image_url);
    echo json_encode($result);
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sản Phẩm</title>
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
form input[type="number"],
form textarea,
form input[type="file"] {
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
        <h2>Thêm Sản Phẩm</h2>
        <form id="add-product-form" method="POST" enctype="multipart/form-data">
            <label for="name">Tên sản phẩm:</label>
            <input type="text" name="name" id="name" required>

            <label for="description">Mô tả:</label>
            <textarea name="description" id="description" required></textarea>

            <label for="price">Giá:</label>
            <input type="number" name="price" id="price" step="0.01" required>

            <label for="stock">Số lượng:</label>
            <input type="number" name="stock" id="stock" required>

            <label for="image">Hình ảnh:</label>
            <input type="file" name="image" id="image" accept="image/*" required>

            <button type="submit">Thêm Sản Phẩm</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $('#add-product-form').submit(function(e) {
            e.preventDefault(); // Ngừng gửi form mặc định

            const formData = new FormData(this);

            $.ajax({
                url: 'dashboard/products/add_product.php', // Đường dẫn đến PHP xử lý

                method: 'POST',
                data: formData,
                processData: false, // Để xử lý file
                contentType: false, // Để không gửi Content-Type mặc định
                success: function(response) {
                    const result = JSON.parse(response);
                    alert(result.message);

                    if (result.status === 'success') {
                        // Sau khi thêm thành công, có thể làm gì đó 
loadProducts();          }
                },
                error: function() {
                    alert('Có lỗi khi thêm sản phẩm.');
loadProducts();
                }
            });
        });
// Hàm tải lại danh sách vào phần #dashboard-content
function loadProducts() {
    $.ajax({
        url: 'dashboard/products/products.php', // Tải lại danh sách thể loại
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
</body>
</html>