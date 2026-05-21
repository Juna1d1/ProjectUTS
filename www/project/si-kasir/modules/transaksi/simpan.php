<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';
requireLogin();

// Ambil data nota dari session
$nota = $_SESSION['last_nota'] ?? null;

if (!$nota) {
    flashMessage('warning', 'Tidak ada transaksi aktif.');
    header('Location: index.php');
    exit;
}

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="page-title">✅ Transaksi <span>Berhasil</span></h1>

<div style="max-width:480px; margin:0 auto;">
    <div class="nota-box" id="nota-cetak">
        <h2>🛒 TOKO SWALAYAN MAJU JAYA</h2>
        <p style="text-align:center; font-size:0.8rem; color:#666;">Sistem Informasi Kasir Terintegrasi</p>
        <hr>
        <div class="nota-row">
            <span>No. Nota</span>
            <strong><?= htmlspecialchars($nota['nomor_nota']) ?></strong>
        </div>
        <div class="nota-row">
            <span>Tanggal</span>
            <span><?= date('d/m/Y H:i', strtotime($nota['tgl'])) ?></span>
        </div>
        <div class="nota-row">
            <span>Kasir</span>
            <span><?= htmlspecialchars($nota['kasir']) ?></span>
        </div>
        <hr>

        <?php foreach ($nota['items'] as $item): ?>
        <div class="nota-row">
            <div>
                <div><?= htmlspecialchars($item['nama_produk']) ?></div>
                <small><?= $item['qty'] ?> × Rp <?= number_format($item['harga_jual'], 0, ',', '.') ?></small>
            </div>
            <strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong>
        </div>
        <?php endforeach; ?>

        <hr>
        <div class="nota-row nota-total">
            <span>TOTAL</span>
            <strong>Rp <?= number_format($nota['total'], 0, ',', '.') ?></strong>
        </div>
        <div class="nota-row">
            <span>Bayar</span>
            <span>Rp <?= number_format($nota['uang_bayar'], 0, ',', '.') ?></span>
        </div>
        <div class="nota-row" style="color:#27ae60; font-weight:800; font-size:1.1rem;">
            <span>Kembalian</span>
            <strong>Rp <?= number_format($nota['kembalian'], 0, ',', '.') ?></strong>
        </div>
        <hr>
        <p style="text-align:center; font-size:0.78rem; color:#888; margin-top:0.5rem;">
            Terima kasih telah berbelanja! 🙏
        </p>
    </div>

    <div style="display:flex; gap:1rem; justify-content:center; margin-top:1.5rem;">
        <button onclick="cetakNota()" class="btn btn-primary">🖨️ Cetak Nota</button>
        <a href="index.php" class="btn btn-success">🧾 Transaksi Baru</a>
    </div>
</div>

<script>
function cetakNota() {
    const el = document.getElementById('nota-cetak');
    const win = window.open('', '_blank', 'width=400,height=600');
    win.document.write(`
        <html><head><title>Nota <?= $nota['nomor_nota'] ?></title>
        <style>
            body { font-family: 'Courier New', monospace; padding: 20px; font-size: 13px; }
            .nota-row { display: flex; justify-content: space-between; margin: 4px 0; }
            hr { border: 1px dashed #999; margin: 8px 0; }
            h2 { text-align: center; font-size: 15px; }
            .nota-total { font-weight: 800; font-size: 15px; }
        </style></head><body>
        ${el.innerHTML}
        </body></html>
    `);
    win.document.close();
    win.print();
    win.close();
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>