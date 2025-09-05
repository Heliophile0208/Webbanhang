<?php
$host = "dpg-d2tb7qje5dus73dm0rng-a";
$port = "5432";
$dbname = "shop_db_rzgm";
$user = "admin";
$password = "6Zm28vKzRP9fD0hskWY6AR2SvLcxHDo2";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Kết nối PostgreSQL thành công!";
} catch (PDOException $e) {
    echo "Lỗi kết nối: " . $e->getMessage();
}
?>
