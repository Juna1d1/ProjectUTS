<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_helper.php';

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
    exit;
}

if ($_SESSION['role'] === 'Admin') {
    header('Location: ' . BASE_URL . '/modules/produk/index.php');
} else {
    header('Location: ' . BASE_URL . '/modules/transaksi/index.php');
}
exit;