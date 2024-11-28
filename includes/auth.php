<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['user']['role'] !== 'admin' && strpos($_SERVER['PHP_SELF'], 'admin/') !== false) {
    header("Location: ../user/index.php");
    exit;
}