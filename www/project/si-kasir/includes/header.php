<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_helper.php';
requireLogin();
$user  = currentUser();
$flash = getFlash();
$base  = BASE_URL;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SI-KASIR | Maju Jaya</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="<?= $base ?>/" class="navbar-brand">🛒 SI-KASIR</a>
    <div class="navbar-menu">
        <?php if ($user['role'] === 'Admin'): ?>
            <a href="<?= $base ?>/modules/produk/index.php"  class="nav-link">📦 Produk</a>
            <a href="<?= $base ?>/modules/laporan/index.php" class="nav-link">📊 Laporan</a>
            <a href="<?= $base ?>/modules/auth/users.php"    class="nav-link">👥 Users</a>
        <?php else: ?>
            <a href="<?= $base ?>/modules/transaksi/index.php"       class="nav-link">🧾 Transaksi</a>
        <?php endif; ?>
        <span class="nav-user">👤 <?= htmlspecialchars($user['username']) ?> (<?= $user['role'] ?>)</span>
        <a href="<?= $base ?>/modules/auth/logout.php" class="nav-link logout">🚪 Logout</a>
    </div>
</nav>
<div class="container">
<?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>