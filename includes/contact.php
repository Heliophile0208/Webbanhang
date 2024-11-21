<?php
include_once 'header.php'; // Kết nối header và CSS
?>

<div class="contact-container">
    <h2>Liên hệ với chúng tôi</h2>

    <form action="process_contact.php" method="POST" class="contact-form">
        <div class="form-group">
            <label for="name">Họ và tên</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="message">Tin nhắn</label>
            <textarea id="message" name="message" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn-submit">Gửi Tin Nhắn</button>
    </form>

    <div class="contact-info">
        <h3>Thông tin liên hệ:</h3>
        <p><strong>Địa chỉ:</strong> 123 Đường ABC, Quận 1, TP.HCM</p>
        <p><strong>Số điện thoại:</strong> (+84) 123 456 789</p>
        <p><strong>Email:</strong> contact@yourdomain.com</p>
    </div>

    <div class="contact-map">
        <h3>Vị trí của chúng tôi</h3>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.222272317377!2d106.69101351469707!3d10.762622392328125!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f44bfabfe39%3A0x403eafebd7d3fa16!2zMjQgVGhhnhqIHRvY2hhbmdh!5e0!3m2!1svi!2s!4v1634877407000!5m2!1svi!2s" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
</div>
<style>
/* Đặt lại một số mặc định */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

/* Container của trang liên hệ */
.contact-container {
    max-width: 1200px;
    margin: 50px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* Tiêu đề */
.contact-container h2 {
    text-align: center;
    color: #333;
    margin-bottom: 30px;
}

/* Form liên hệ */
.contact-form {
    display: grid;
    gap: 15px;
    margin-bottom: 30px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input, .form-group textarea {
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.form-group input:focus, .form-group textarea:focus {
    border-color: #007bff;
    outline: none;
}

/* Nút gửi */
.btn-submit {
    background-color: #28a745;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
}

.btn-submit:hover {
    background-color: #218838;
}

/* Thông tin liên hệ */
.contact-info {
    background-color: #f1f1f1;
    padding: 20px;
    border-radius: 8px;
    margin-top: 30px;
}

.contact-info h3 {
    margin-bottom: 15px;
    font-size: 18px;
}

.contact-info p {
    font-size: 16px;
    margin: 5px 0;
}

/* Bản đồ */
.contact-map {
    margin-top: 30px;
    text-align: center;
}

.contact-map iframe {
    border-radius: 8px;
    border: none;
}

</style>
<?php
include_once 'includes/footer.php'; // Kết nối footer và các script
?>