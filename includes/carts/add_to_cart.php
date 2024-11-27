<?php
session_start();
include '../../config/database.php';
include '../../includes/functions.php';  // Bao gồm các hàm hỗ trợ (nếu cần)

// Kiểm tra nếu người dùng đã đăng nhập thông qua username
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
if (!$username) {
    // Nếu người dùng chưa đăng nhập, chuyển hướng đến trang đăng nhập
    header("Location: /login.php");
    exit();
}

// Kiểm tra nếu người dùng đã nhấn thêm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    // Lấy thông tin sản phẩm từ form
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $quantity = $_POST['quantity'];

    // Cập nhật giỏ hàng trong session
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = array(
            'name' => $product_name,
            'price' => $product_price,
            'quantity' => $quantity
        );
    }

    // Tính tổng giá trị đơn hàng
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Kiểm tra xem người dùng đã có đơn hàng chưa, nếu chưa thì tạo mới hoặc cập nhật
    $order_id = getOrderId($username);

    if (!$order_id) {
        // Nếu không có đơn hàng, tạo đơn hàng mới
        $order_id = createOrder($username, $total);  // Hàm tạo đơn hàng, truyền thêm total
    } else {
        // Nếu có đơn hàng, cập nhật tổng tiền của đơn hàng
        updateOrderTotal($order_id, $total);
    }

   // Thêm sản phẩm vào bảng order_items với size_id mặc định là NULL
$query = "INSERT INTO order_items (order_id, product_id, quantity, price, size_id) VALUES (?, ?, ?, ?, NULL)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $order_id, $product_id, $quantity, $product_price);
$stmt->execute();
    
    // Chuyển hướng về trang sản phẩm với thông báo thành công
    header("Location: /includes/products/products.php?success=1&product=" . urlencode($product_name));
    exit();
}

/**
 * Hàm kiểm tra xem người dùng đã có đơn hàng chưa
 */
function getOrderId($username) {
    global $conn;

    // Lấy user_id từ username
    $query = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Kiểm tra xem người dùng đã có đơn hàng chưa (với trạng thái 'pending')
        $query = "SELECT id FROM orders WHERE user_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Nếu đã có đơn hàng 'pending', trả về id đơn hàng
            $order = $result->fetch_assoc();
            return $order['id'];
        }
    }

    // Nếu không có đơn hàng, trả về null
    return null;
}

/**
 * Hàm tạo đơn hàng
 */
function createOrder($username, $total) {
    global $conn;

    // Lấy user_id từ username (nếu cần)
    $query = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
    } else {
        // Nếu không tìm thấy user, trả về false
        return false;
    }

    // Tạo đơn hàng mới với trạng thái 'pending' và tổng tiền
    $query = "INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, 'pending', NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $total);
    $stmt->execute();

    // Lấy id của đơn hàng mới tạo
    return $stmt->insert_id;
}

/**
 * Hàm cập nhật tổng tiền của đơn hàng
 */
function updateOrderTotal($order_id, $total) {
    global $conn;

    // Cập nhật tổng tiền của đơn hàng
    $query = "UPDATE orders SET total = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $total, $order_id);
    $stmt->execute();
}
?>