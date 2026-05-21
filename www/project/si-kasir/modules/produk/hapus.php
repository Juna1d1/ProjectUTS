<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';

requireAdmin();

$db = getDB();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    flashMessage('danger', 'ID produk tidak valid.');
    header('Location: index.php');
    exit;
}

// Ambil nama produk
$stmt = $db->prepare("
    SELECT nama_produk
    FROM m_produk
    WHERE id_produk = ?
");

$stmt->execute([$id]);

$produk = $stmt->fetch();

if (!$produk) {
    flashMessage('danger', 'Produk tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Cek apakah produk pernah dipakai transaksi
$chk = $db->prepare(" SELECT COUNT(*) AS total FROM t_penjualan_detail WHERE id_produk = ?");

$chk->execute([$id]);
$row = $chk->fetch();

if ($row['total'] > 0) {

    flashMessage(
        'danger',
        "Produk '{$produk['nama_produk']}' tidak dapat dihapus karena sudah pernah ada dalam transaksi!"
    );

    header('Location: index.php');
    exit;
}

// Hapus log stok
$del_log = $db->prepare("
    DELETE FROM t_log_stok
    WHERE id_produk = ?
");

$del_log->execute([$id]);

// Hapus produk
$del_produk = $db->prepare("
    DELETE FROM m_produk
    WHERE id_produk = ?
");

$del_produk->execute([$id]);

flashMessage(
    'success',
    "Produk '{$produk['nama_produk']}' berhasil dihapus."
);

header('Location: index.php');
exit;