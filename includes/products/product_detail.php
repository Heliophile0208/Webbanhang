<?php
session_start();
include '../../includes/header.php';
include '../../config/database.php';

// Khởi tạo biến lỗi
$message_error = '';

// Lấy thông tin sản phẩm từ ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

// Kiểm tra nếu người dùng đã đăng nhập và có username trong session
if (isset($_SESSION['username'])) {
    // Lấy username từ session
    $username = $_SESSION['username'];

    // Truy vấn để lấy user_id từ bảng users
    $query_user_id = "SELECT id FROM users WHERE username = ?";
    $stmt_user_id = $conn->prepare($query_user_id);
    $stmt_user_id->bind_param("s", $username); // Bind username
    $stmt_user_id->execute();
    $result_user_id = $stmt_user_id->get_result();

    if ($result_user_id->num_rows > 0) {
        // Lấy user_id
        $user_row = $result_user_id->fetch_assoc();
        $user_id = $user_row['id'];
    } else {
        // Không tìm thấy user_id, người dùng chưa đăng nhập hoặc có lỗi
        $message_error = "Không tìm thấy người dùng.";
    }
} else {
    // Nếu chưa đăng nhập, thông báo lỗi hoặc yêu cầu đăng nhập
    $message_error = "Vui lòng đăng nhập để gửi mua sản phẩm.";
}


// Nếu có session product_id, dùng nó để lấy thông tin sản phẩm
if ($product_id > 0) {
    // Lưu product_id vào session nếu chưa có
    $_SESSION['product_id'] = $product_id;

    // Truy vấn cơ sở dữ liệu để lấy sản phẩm từ product_id
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $message_error = "Sản phẩm không tồn tại.";
    }
}

if (!$product) {
    echo "<p>$message_error</p>";
    exit;
}

// Lấy thông báo nếu có
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Sản phẩm '" . htmlspecialchars($product['name']) . "' đã được thêm vào giỏ hàng!";
}

$success = '';
if (isset($_GET['success_review']) && $_GET['success_review'] == 1) {
    $success = "Đánh giá thành công sản phẩm '" . htmlspecialchars($product['name']) ;
}

// Lấy category_id từ bảng product_categories theo product_id
$query_category = "SELECT category_id FROM product_categories WHERE product_id = ?";
$stmt_category = $conn->prepare($query_category);
$stmt_category->bind_param("i", $product_id);
$stmt_category->execute();
$result_category = $stmt_category->get_result();

if ($result_category->num_rows > 0) {
    $category_row = $result_category->fetch_assoc();
    $category_id = $category_row['category_id'];
} else {
    // Nếu không tìm thấy danh mục, thông báo lỗi và tiếp tục hiển thị chi tiết sản phẩm
    $message_error = "Không tìm thấy danh mục sản phẩm.";
    $category_id = null;  // Chắc chắn rằng category_id là null khi không có danh mục
}

// Truy vấn các sản phẩm cùng danh mục
$query_related_products = "SELECT p.id, p.name, p.price, p.image_url
                           FROM products p
                           JOIN product_categories pc ON p.id = pc.product_id
                           WHERE pc.category_id = ? AND p.id != ?";
$stmt_related = $conn->prepare($query_related_products);
$stmt_related->bind_param("ii", $category_id, $product_id);
$stmt_related->execute();
$related_result = $stmt_related->get_result();

// Truy vấn các đánh giá sản phẩm
$query_reviews = "SELECT * FROM reviews WHERE product_id = ?";
$stmt_reviews = $conn->prepare($query_reviews);
$stmt_reviews->bind_param("i", $product_id);
$stmt_reviews->execute();
$reviews_result = $stmt_reviews->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['review'])) {
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    if ($user_id > 0 && $rating && $review_text) {
        $query_insert_review = "INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt_insert = $conn->prepare($query_insert_review);
        $stmt_insert->bind_param("iiis", $product_id, $user_id, $rating, $review_text);

        if ($stmt_insert->execute()) {
            header("Location: product_detail.php?id=$product_id&success_review=1");
            exit;
        } else {
            $message_error = "Lỗi khi thêm đánh giá: " . $stmt_insert->error;
        }
    } else {
        $message_error = "Vui lòng điền đầy đủ thông tin đánh giá.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm</title>
    <link rel="stylesheet" href="../../css/products_detail.css">
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>


<body>



 
<?php if ($success_message): ?>
    <div class="flash-message" id="flash-message">
        <?php echo $success_message; ?>
        <button onclick="hideFlashMessage()">×</button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="flash-message" id="flash-message">
        <?php echo $success; ?>
        <button onclick="hideFlashMessage()">×</button>
    </div>
<?php endif; ?>


<main>


<?php if (!empty(trim($message_error))) : ?>
    <div style="color: red; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 5px; font-size: 20px; text-align: center; border-radius: 5px;" id="error-message">
        <p><?php echo htmlspecialchars($message_error); ?></p>
    </div>
<?php endif; ?>

   <script>
        // Ẩn thông báo sau 2 giây
        setTimeout(function() {
            const errorMessage = document.getElementById('error-message');
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
        }, 2000);
    </script>

    <div class="product-detail">
        <div class="product-left">
            <img src="../../<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
        </div>
        <div class="product-right">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p><strong>Giá:</strong> <?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>
            <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <form action="/includes/carts/add_to_cart.php" method="POST" style="text-align: center;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">

                <div style="display: flex; align-items: center; justify-content: center; gap: 5px; margin-bottom:10px">
                    <button type="button" onclick="decreaseQuantity(this)" style="width: 30px; height: 30px; font-size: 16px; border: 1px solid #ddd; background-color: #f8f8f8; cursor: pointer;">-</button>
                    <input type="number" name="quantity" value="1" min="1" style="width: 60px; padding: 5px; text-align: center; border: 1px solid #ddd;">
                    <button type="button" onclick="increaseQuantity(this)" style="width: 30px; height: 30px; font-size: 16px; border: 1px solid #ddd; background-color: #f8f8f8; cursor: pointer;">+</button>
                </div>
                
                <button type="submit" name="add_to_cart" style="background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">Thêm vào giỏ</button>
            </form>
        </div>
    </div>


<!-- Phần Đánh Giá -->
<div class="product-reviews">
    <h3>Đánh Giá Sản Phẩm</h3>
    <div class="ratings">
        <?php
        // Hiển thị đánh giá từ cơ sở dữ liệu
        $total_rating = 0;
        $total_reviews = $reviews_result->num_rows;
        while ($review = $reviews_result->fetch_assoc()) {
            $total_rating += $review['rating'];
        }

        // Tính điểm trung bình
        $average_rating = ($total_reviews > 0) ? ($total_rating / $total_reviews) : 0;
        $stars = round($average_rating);
        for ($i = 1; $i <= 5; $i++) {
            echo "<span class='star " . ($i <= $stars ? 'filled' : '') . "'>&#9733;</span>";
        }
        ?>
        <p><?php echo $total_reviews ? $average_rating . '/5' : 'Chưa có đánh giá.'; ?></p>
    </div>

    <!-- Form gửi đánh giá -->
    <form method="POST">
        <div class="rating-stars">
            <label for="rating">Chọn Đánh Giá: </label>
            <select name="rating" required>
                <option value="1">1 sao</option>
                <option value="2">2 sao</option>
                <option value="3">3 sao</option>
                <option value="4">4 sao</option>
                <option value="5">5 sao</option>
            </select>
        </div>
        <textarea name="review_text" placeholder="Viết đánh giá của bạn..." required></textarea>
        <button type="submit" name="review">Gửi Đánh Giá</button>
    </form>
</div>
    

    <!-- Các sản phẩm cùng danh mục -->
   <div class="related-products">
   <div class="related-products">
    <h3>Sản Phẩm Cùng Danh Mục</h3>
    <div class="products-scroll-container">
        <div class="products">
            <?php
            if ($related_result->num_rows > 0) {
                while ($row = $related_result->fetch_assoc()) {
                    echo "<div class='product'>";
                    echo "<a href='/includes/products/product_detail.php?id=" . $row['id'] . "'>";
                    echo "<img src='../../" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
                    echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                    echo "<p>Giá: " . number_format($row['price'], 0, ',', '.') . " VND</p>";
                    echo "</a>";
                    echo "</div>";
                }
            } else {
                echo "<p>Không có sản phẩm nào cùng danh mục.</p>";
            }
            ?>
        </div>
    </div>
</div>
</main>

<script>
    function decreaseQuantity(button) {
        const input = button.nextElementSibling; // Lấy ô input ngay sau nút "-"
        let currentValue = parseInt(input.value);
        if (currentValue > 1) {
            input.value = currentValue - 1; // Giảm giá trị
        }
    }

    function increaseQuantity(button) {
        const input = button.previousElementSibling; // Lấy ô input ngay trước nút "+"
        let currentValue = parseInt(input.value);
        input.value = currentValue + 1; // Tăng giá trị
    }

    function hideFlashMessage() {
        const flashMessage = document.getElementById("flash-message");
        if (flashMessage) {
            flashMessage.classList.remove("show");
            setTimeout(() => flashMessage.style.display = "none", 300);
        }
    }
window.onload = function() {
    const errorMessage = document.querySelector('.message-error');

    if (errorMessage) {
        // Đợi 5 giây và ẩn thông báo
        setTimeout(() => {
            errorMessage.classList.remove('show');  // Loại bỏ lớp 'show'
        }, 5000);  // 5000ms = 5 giây
    }
};

</script>

</body>
</html>