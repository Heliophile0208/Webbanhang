<?php
include_once '/xampp/htdocs/Webbanhang/includes/header.php';
include_once '/xampp/htdocs/Webbanhang/config/database.php';
// Lấy thông báo nếu có
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['product'])) {
    $success_message = "Sản phẩm '" . htmlspecialchars($_GET['product']) . "' đã được thêm vào giỏ hàng!";
}

// Nhận từ khóa tìm kiếm (nếu có)
$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

// Nhận category_id và group từ URL nếu có
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$group = isset($_GET['group']) ? htmlspecialchars($_GET['group']) : '';

// Truy vấn tất cả danh mục theo group
$query_categories = "SELECT * FROM categories ORDER BY `group`, `name`";
$stmt_categories = $conn->prepare($query_categories);
$stmt_categories->execute();
$categories_result = $stmt_categories->get_result();

// Truy vấn sản phẩm theo category_id và group
if ($category_id > 0 && $group != '') {
    // Nếu có cả category_id và group
    $query = "
        SELECT p.* 
        FROM products p
        JOIN product_categories pc ON p.id = pc.product_id
        JOIN categories c ON pc.category_id = c.id
        WHERE pc.category_id = ? AND c.group = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $category_id, $group);
    $stmt->execute();
    $result = $stmt->get_result();
} else if ($category_id > 0) {
    // Nếu chỉ có category_id
    $query = "
        SELECT p.* 
        FROM products p
        JOIN product_categories pc ON p.id = pc.product_id
        WHERE pc.category_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else if ($group != '') {
    // Nếu chỉ có group
    $query = "
        SELECT p.* 
        FROM products p
        JOIN product_categories pc ON p.id = pc.product_id
        JOIN categories c ON pc.category_id = c.id
        WHERE c.group = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $group);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Nếu không có cả category_id và group, lấy tất cả sản phẩm
    if ($search != '') {
        // Nếu có từ khóa tìm kiếm
        $query = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
        $stmt = $conn->prepare($query);
        $searchParam = '%' . $search . '%';
        $stmt->bind_param("ss", $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Nếu không có tìm kiếm, lấy tất cả sản phẩm
        $query = "SELECT * FROM products";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}

// Lưu danh sách các category theo group
$categories_by_group = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories_by_group[$category['group']][] = $category;
}

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản Phẩm</title>
    <link rel="stylesheet" href="../../css/products.css">
    <link rel="stylesheet" href="../../css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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

        let currentIndex = 0;

        function moveSlider() {
            const slides = document.querySelectorAll('.slide');
            const slider = document.querySelector('.slider');

            currentIndex++;
            if (currentIndex >= slides.length) {
                currentIndex = 0;
            }

            slider.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        // Chạy tự động lướt mỗi 4 giây
        setInterval(moveSlider, 4000);
    </script>

    <header>
        <?php if ($success_message): ?>
            <div class="flash-message" id="flash-message">
                <?php echo $success_message; ?>
                <button onclick="hideFlashMessage()">×</button>
            </div>
        <?php endif; ?>
        <div class="container-products">
            <div class="hamburger" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </div>
            <form action="/includes/products/products.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo $search; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </header>

    <div id="sidebar">
        <span class="close-btn" onclick="toggleSidebar()">&times;</span>
        <a style="font-weight:bold; font-size:20px" href="/includes/products/products.php">TẤT CẢ SẢN PHẨM</a>

        <?php foreach ($categories_by_group as $group_name => $categories): ?>
            <div class="category-group">
                <h3 style="margin:10px" onclick="toggleCategory('<?php echo htmlspecialchars($group_name); ?>')">
                    <?php echo htmlspecialchars($group_name); ?>
                </h3>
                <div id="categories-<?php echo htmlspecialchars($group_name); ?>" class="categories-list" style="display:none;">
                    <?php foreach ($categories as $category): ?>
                        <a style="margin:10px" href="/includes/products/products.php?category_id=<?php echo $category['id']; ?>&group=<?php echo urlencode($group_name); ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        // Kiểm tra trạng thái của các nhóm danh mục đã mở khi tải lại trang
        window.onload = function() {
            var openedGroups = JSON.parse(localStorage.getItem('openedGroups')) || [];
            openedGroups.forEach(function(groupName) {
                document.getElementById('categories-' + groupName).style.display = 'block';
            });
        };

        function toggleCategory(groupName) {
            var categoryList = document.getElementById('categories-' + groupName);

            // Nếu nhóm danh mục đang ẩn, mở nó ra, nếu đang mở thì đóng lại
            if (categoryList.style.display === 'none' || categoryList.style.display === '') {
                categoryList.style.display = 'block';
                saveOpenedGroup(groupName); // Lưu nhóm đã mở
            } else {
                categoryList.style.display = 'none';
                removeOpenedGroup(groupName); // Xóa nhóm đã đóng
            }
        }

        // Lưu nhóm danh mục đã mở vào localStorage
        function saveOpenedGroup(groupName) {
            var openedGroups = JSON.parse(localStorage.getItem('openedGroups')) || [];
            if (!openedGroups.includes(groupName)) {
                openedGroups.push(groupName);
                localStorage.setItem('openedGroups', JSON.stringify(openedGroups));
            }
        }

        // Xóa nhóm danh mục đã đóng khỏi localStorage
        function removeOpenedGroup(groupName) {
            var openedGroups = JSON.parse(localStorage.getItem('openedGroups')) || [];
            var index = openedGroups.indexOf(groupName);
            if (index > -1) {
                openedGroups.splice(index, 1);
                localStorage.setItem('openedGroups', JSON.stringify(openedGroups));
            }
        }
    </script>
    <div class="slider-container">
        <div class="slider">
            <div class="slide">
                <img src="../../../images/banner1.jpeg" alt="Banner 1">
            </div>
            <div class="slide">
                <img src="../../../images/banner2.jpeg" alt="Banner 2">
            </div>
            <div class="slide">
                <img src="../../../images/banner3.jpeg" alt="Banner 3">
            </div>
        </div>
    </div>


    <main>
        <?php
        $category_title = 'THỜI TRANG NỮ'; // Giá trị mặc định nếu không có category_id
        if ($category_id > 0) {
            // Truy vấn tên danh mục nếu có category_id
            $query_category = "SELECT name FROM categories WHERE id = ?";
            $stmt_category = $conn->prepare($query_category);
            $stmt_category->bind_param("i", $category_id);
            $stmt_category->execute();
            $result_category = $stmt_category->get_result();

            if ($result_category->num_rows > 0) {
                $category = $result_category->fetch_assoc();
                $category_title = htmlspecialchars($category['name']);
            }
        } else if ($group != '') {
            // Nếu chỉ có group, hiển thị tên nhóm
            $category_title = htmlspecialchars($group);
        }
        ?>
        <div class="category-title"><?php echo $category_title; ?></div>

        <div class="products">


            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='product'>";
                    echo "<a href='/includes/products/product_detail.php?id=" . $row['id'] . "'>";
                    echo "<img src='../../" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
                    echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                    echo "<p>Giá: " . number_format($row['price'], 0, ',', '.') . " VND</p>";
                    echo "</a>";

                    // Thêm nút "Thêm vào giỏ hàng"
                    echo "<form action='/includes/carts/add_to_cart.php' method='POST' style='text-align: center;'>
        <input type='hidden' name='product_id' value='" . $row['id'] . "'>
        <input type='hidden' name='product_name' value='" . htmlspecialchars($row['name']) . "'>
        <input type='hidden' name='product_price' value='" . $row['price'] . "'>
        <div style='display: flex; align-items: center; justify-content: center; gap: 5px; margin-bottom:10px'>
            <button type='button' onclick='decreaseQuantity(this)' style='width: 30px; height: 30px; font-size: 16px; border: 1px solid #ddd; background-color: #f8f8f8; cursor: pointer;'>-</button>
            <input type='number' name='quantity' value='1' min='1' style='width: 60px; padding: 5px; text-align: center; border: 1px solid #ddd;'>
            <button type='button' onclick='increaseQuantity(this)' style='width: 30px; height: 30px; font-size: 16px; border: 1px solid #ddd; background-color: #f8f8f8; cursor: pointer;'>+</button>
        </div>
        <button type='submit' name='add_to_cart' style='background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;'>Thêm vào giỏ</button>
      </form>";
                    echo "</div>";
                }
            } else {
                echo "<p>Không tìm thấy sản phẩm nào.</p>";
            }
            ?>
        </div>
    </main>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

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
    </script>

</body>

</html>