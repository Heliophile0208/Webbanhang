<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Đơn Hàng</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<style>
    #orderForm {
        width: 80%;
        max-width: 600px;
        margin: 30px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    label {
        font-size: 16px;
        font-weight: bold;
        display: block;
        margin-bottom: 8px;
    }

    input[type="text"] {
        width: 80%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    input[type="submit"], button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button {
        background-color: #008CBA;
    }

    input[type="submit"]:hover, button:hover {
        background-color: #45a049;
    }

    #user_search_results {
        text-align: center;
        width: 80%;   
        margin: 0 auto; /* Căn giữa kết quả tìm kiếm */
        padding: 10px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        display: none;
    }

    .user-item {
        padding: 8px;
        margin: 5px 0;
        background-color: #f1f1f1;
        cursor: pointer;
        border-radius: 4px;
        text-align: center; /* Căn giữa tên người dùng */
    }

    .user-item:hover {
        background-color: #ddd;
    }

    #order_creation_section {
        margin-top: 30px;
        padding: 20px;
        background-color: #e8f7e9;
        border-radius: 8px;
        text-align: center;
    }

    #order_creation_section h3 {
        color: #4CAF50;
    }

    #order_creation_section button {
        background-color: #ff9800;
    }

    #order_creation_section button:hover {
        background-color: #e68900;
    }

    @media (max-width: 768px) {
        #orderForm {
            width: 90%;
        }

        .user-item {
            padding: 6px;
            font-size: 14px;
        }
    }
</style>

<body>

<h2>Thêm Đơn Hàng</h2>

<!-- Form nhập thông tin đơn hàng -->
<form id="orderForm">
    <label for="user_id_search">Tìm Kiếm Người Dùng:</label>
    <input type="text" id="user_id_search" name="user_id_search" placeholder="Nhập tên người dùng" required>
    <input type="hidden" id="user_id" name="user_id"> <!-- Lưu user_id đã chọn -->

    <br><br>

    <button type="submit">Tạo Đơn Hàng</button>
</form>

<!-- Kết quả tìm kiếm người dùng -->
<div id="user_search_results" style="border: 1px solid #ddd; display: none; ">
    <!-- Kết quả sẽ hiển  ở đây -->
</div>

<!-- Phần tạo đơn hàng (hiển thị khi tạo đơn thành công) -->
<div id="order_creation_section" style="display:none;">
    <h3>Đơn hàng đã được tạo thành công!</h3>
    <p>Tiến hành thêm sản phẩm vào đơn hàng.</p>
    <!-- Thêm nút điều hướng hoặc tiếp tục thêm sản phẩm -->
    <button id="addProductBtn">Thêm sản phẩm vào đơn</button>
</div><script>

$(document).ready(function() {
    // Tìm kiếm người dùng
    $('#user_id_search').keyup(function() {
        var searchTerm = $(this).val();

        if (searchTerm.length >= 2) {  // Chỉ tìm kiếm khi nhập ít nhất 2 ký tự
            $.ajax({
                url: 'dashboard/orders/search_user.php', // Tìm kiếm người dùng qua AJAX
                method: 'GET',
                data: { search: searchTerm },
                success: function(response) {
                    var data = JSON.parse(response);
                    var results = data.results;
                    
                    // Hiển thị kết quả tìm kiếm
                    if (results.length > 0) {
                        $('#user_search_results').empty().show();
                        results.forEach(function(user) {
                            $('#user_search_results').append('<div class="user-item" data-id="' + user.id + '">' + user.username + '</div>');
                        });
                    } else {
                        $('#user_search_results').empty().hide();
                    }
                }
            });
        } else {
            $('#user_search_results').empty().hide();  // Ẩn kết quả khi nhập ít hơn 2 ký tự
        }
    });

    // Chọn người dùng từ kết quả tìm kiếm
    $(document).on('click', '.user-item', function() {
        var userId = $(this).data('id');
        var userName = $(this).text();

        // Điền vào ô tìm kiếm và lưu user_id vào input hidden
        $('#user_id_search').val(userName);
        $('#user_id').val(userId);
        $('#user_search_results').hide(); // Ẩn kết quả tìm kiếm
    });

    // Xử lý khi nhấn tạo đơn hàng
    $('#orderForm').submit(function(event) {
        event.preventDefault(); // Ngừng gửi form thông thường

        // Lấy dữ liệu từ form
        var user_id = $('#user_id').val();
        var username = $('#user_id_search').val();

        if (user_id) {
            $.ajax({
                url: 'dashboard/orders/add_new_order.php', // Gửi yêu cầu đến PHP xử lý
                method: 'POST',
                data: {
                    user_id: user_id,
                    username: username // Truyền username qua AJAX
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.status === 'success') {
                        alert(data.message);

                        // Lưu orderId từ phản hồi nếu có
                        var orderId = data.order_id;

                        // Hiển thị phần tạo đơn hàng và nút thêm sản phẩm
                        $('#order_creation_section').show(); // Hiển thị phần tạo đơn hàng
                        $('#addProductBtn').data('orderId', orderId); // Lưu orderId vào button
                    } else {
                        alert(data.message);
                    }
                }
            });
        } else {
            alert('Vui lòng chọn người dùng.');
        }
    });

    // Xử lý khi nhấn nút thêm sản phẩm vào đơn hàng
    $('#addProductBtn').click(function() {
        var orderId = $(this).data('orderId'); // Lấy orderId từ data attribute của button
        if (orderId) {
            $("#dashboard-content").load('dashboard/orders/edit_order.php?OrderID=' + orderId);
        } else {
            alert('Không tìm thấy ID đơn hàng.');
        }
    });
});
</script>

</body>
</html>