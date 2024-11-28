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
textarea,
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
textarea,
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

if (isset($_GET['ProductID'])) {
    $ProductID = $_GET['ProductID'];
    $productQuery = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($productQuery);
    $stmt->bind_param("i", $ProductID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Không tìm thấy sản phẩm.";
        exit;
    }
    ?>
    <h2>Chỉnh sửa sản phẩm</h2>
    <form id="editProductForm">
        <input type="hidden" name="ProductID" value="<?php echo $product['id']; ?>">

        <label for="product_name">Tên sản phẩm:</label>
        <input type="text" id="product_name" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>" required><br>

        <label for="description">Mô tả:</label>
        <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea><br>

        <label for="price">Giá:</label>
        <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required><br>

        <label for="stock">Số lượng tồn kho:</label>
        <input type="text" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" required><br>

        <button type="submit">Cập nhật</button>
    </form>
    <?php
} else {
    echo "Không có ProductID để sửa.";
}
$conn->close();
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Lắng nghe sự kiện submit form
    document.getElementById("editProductForm").addEventListener("submit", function(e) {
        e.preventDefault(); // Ngăn trang web tải lại

        // Lấy dữ liệu từ form
        var formData = new FormData(this);

        $.ajax({
            url: '/admin/dashboard/products/update_product.php', // URL xử lý cập nhật sản phẩm
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.trim() === "success") {
                    alert("Cập nhật sản phẩm thành công!");
                    // Bạn có thể cập nhật lại bảng sản phẩm hoặc làm gì đó sau khi thành công
                    $('#dashboard-content').load('/admin/dashboard/products/products.php'); // Ví dụ tải lại trang sản phẩm trong phần content
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
