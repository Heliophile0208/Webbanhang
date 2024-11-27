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
<link rel="stylesheet" href="../css/contact.css" type="text/css">
<?php
include_once 'includes/footer.php'; // Kết nối footer và các script
?>