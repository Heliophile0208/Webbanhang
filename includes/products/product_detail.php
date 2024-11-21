<?php
session_start();
include '../../config/database.php';
include '../../includes/header.php';
// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Lấy thông báo nếu có
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['product'])) {
    $success_message = "Sản phẩm '" . htmlspecialchars($_GET['product']) . "' đã được thêm vào giỏ hàng!";
}

// Truy vấn thông tin chi tiết sản phẩm
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "<p>Không tìm thấy sản phẩm.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Sản Phẩm</title>
    <style>
        /* Style for product details */
        .product-detail {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .product-detail img {
            max-width: 100%;
            border-radius: 5px;
        }

        .product-detail h2 {
            font-size: 24px;
            margin: 20px 0;
        }

        .product-detail p {
            margin: 10px 0;
            color: #555;
        }

        .product-detail .price {
            font-size: 20px;
            color: #007BFF;
        }

        .add-to-cart {
            margin-top: 20px;
        }

        .quantity-control {
            display: inline-flex;
            align-items: center;
            font-size: 18px;
        }

        .quantity-control button {
            padding: 5px 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }

        .quantity-control button:disabled {
            background-color: #ddd;
            cursor: not-allowed;
        }

        .quantity-control input {
            width: 40px;
            text-align: center;
            margin: 0 10px;
            padding: 5px;
        }

        .add-to-cart button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .add-to-cart button:hover {
            background-color: #218838;
        }

        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .flash-message.show {
            opacity: 1;
            transform: translateY(0);
        }

        .flash-message button {
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            margin-left: 15px;
            cursor: pointer;
        }

    </style>
</head>
<body>

<script>
    // Hiện thông báo và tự động ẩn sau 3 giây
    document.addEventListener("DOMContentLoaded", function() {
        const flashMessage = document.getElementById("flash-message");
        if (flashMessage) {
            flashMessage.classList.add("show");
            setTimeout(hideFlashMessage, 3000);
        }
    });

    function hideFlashMessage() {
        const flashMessage = document.getElementById("flash-message");
        if (flashMessage) {
            flashMessage.classList.remove("show");
            setTimeout(() => flashMessage.style.display = "none", 300);
        }
    }

    // Thêm sự kiện để điều chỉnh số lượng
    function updateQuantity(change) {
        const quantityInput = document.getElementById('quantity');
        let quantity = parseInt(quantityInput.value);
        quantity += change;

        if (quantity < 1) {
            quantity = 1; // Đảm bảo số lượng không nhỏ hơn 1
        }

        quantityInput.value = quantity;
    }
</script>

<div class="product-detail">
    <img src="../../<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <p class="price">Giá: <?php echo number_format($product['price'], 0, ',', '.'); ?> VND</p>

    <!-- Thêm vào giỏ hàng -->
    <form class="add-to-cart" method="POST" action="/includes/carts/add_to_cart.php">
        <div class="quantity-control">
            <button type="button" onclick="updateQuantity(-1)">-</button>
            <input id="quantity" type="number" name="quantity" value="1" min="1" max="100">
            <button type="button" onclick="updateQuantity(1)">+</button>
        </div>
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
        <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
<br>
        <button style="margin-top:10px;" type="submit" name="add_to_cart">Thêm vào giỏ hàng</button>
    </form>
</div>

</body>
</html>