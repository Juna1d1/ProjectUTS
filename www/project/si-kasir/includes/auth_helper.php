<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan BASE_URL sudah didefinisikan
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}

function isLoggedIn() {
    return isset($_SESSION['id_user']) && !empty($_SESSION['id_user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/modules/auth/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'Admin') {
        header('Location: ' . BASE_URL . '/modules/transaksi/index.php');
        exit;
    }
}

function requireKasir() {
    requireLogin();
    if ($_SESSION['role'] !== 'Kasir') {
        header('Location: ' . BASE_URL . '/modules/produk/index.php');
        exit;
    }
}

function currentUser() {
    return [
        'id'       => $_SESSION['id_user'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'role'     => $_SESSION['role'] ?? null,
    ];
}

function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}