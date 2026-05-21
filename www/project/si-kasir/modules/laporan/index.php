<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';
requireAdmin();

$db = getDB();

$tgl_dari   = $_GET['dari']   ?? date('Y-m-01');
$tgl_sampai = $_GET['sampai'] ?? date('Y-m-d');

// ── STAT CARDS ──
$stat = $db->prepare("
    SELECT COUNT(id_penjualan) AS total_transaksi,
           COALESCE(SUM(total_bayar),0) AS total_omzet
    FROM t_penjualan
    WHERE DATE(tgl_transaksi) BETWEEN ? AND ?
");
$stat->execute([$tgl_dari, $tgl_sampai]);

$stats = $stat->fetch();

$stat2 = $db->query("
    SELECT COUNT(*) as total_produk,
           SUM(CASE WHEN stok < 5 THEN 1 ELSE 0 END) as stok_kritis
    FROM m_produk
")->fetch();

// ── LAPORAN HARIAN ──
$harian = $db->prepare("
    SELECT DATE(p.tgl_transaksi) AS tanggal,
           COUNT(p.id_penjualan) AS jumlah_nota,
           SUM(p.total_bayar)    AS total_omzet,
           u.username            AS kasir
    FROM t_penjualan p
    INNER JOIN m_user u ON u.id_user = p.id_user
    WHERE DATE(p.tgl_transaksi) BETWEEN ? AND ?
    GROUP BY DATE(p.tgl_transaksi), p.id_user
    ORDER BY tanggal DESC
");
$harian->execute([$tgl_dari, $tgl_sampai]);

$data_harian = $harian->fetchAll();

// ── BEST SELLER ──
$best = $db->prepare("
    SELECT mp.nama_produk,
           SUM(d.qty)      AS total_qty,
           SUM(d.subtotal) AS total_omzet
    FROM t_penjualan_detail d
    INNER JOIN m_produk mp    ON mp.id_produk  = d.id_produk
    INNER JOIN t_penjualan p  ON p.id_penjualan = d.id_penjualan
    WHERE DATE(p.tgl_transaksi) BETWEEN ? AND ?
    GROUP BY d.id_produk
    ORDER BY total_qty DESC
    LIMIT 10
");
$best->execute([$tgl_dari, $tgl_sampai]);

$data_best = $best->fetchAll();

// ── MUTASI STOK ──
$mutasi = $db->query("
    SELECT l.*, p.nama_produk
    FROM t_log_stok l
    JOIN m_produk p ON p.id_produk = l.id_produk
    ORDER BY l.waktu_log DESC LIMIT 50
")->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="page-title">📊 Laporan & <span>Analisis</span></h1>

<!-- ── STAT CARDS ── -->
<div class="grid-4" style="margin-bottom:1.5rem;">
    <div class="stat-card">
        <div class="stat-icon">🧾</div>
        <div class="stat-value"><?= number_format($stats['total_transaksi']) ?></div>
        <div class="stat-label">Total Transaksi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-value" style="font-size:1.1rem;">Rp <?= number_format($stats['total_omzet'],0,',','.') ?></div>
        <div class="stat-label">Total Omzet</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-value"><?= $stat2['total_produk'] ?></div>
        <div class="stat-label">Total Produk</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-value" style="color:var(--danger);"><?= $stat2['stok_kritis'] ?></div>
        <div class="stat-label">Stok Kritis (&lt;5)</div>
    </div>
</div>

<!-- ── FILTER ── -->
<div class="card" style="margin-bottom:1.5rem;">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <label style="color:var(--muted);font-size:.9rem;white-space:nowrap;">📅 Dari:</label>
            <input type="date" name="dari"   class="form-control" value="<?= $tgl_dari ?>"   style="width:160px;">
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <label style="color:var(--muted);font-size:.9rem;white-space:nowrap;">Sampai:</label>
            <input type="date" name="sampai" class="form-control" value="<?= $tgl_sampai ?>" style="width:160px;">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">🔍 Tampilkan</button>
        <a href="index.php" class="btn btn-secondary btn-sm">Reset</a>
    </form>
</div>

<div class="grid-2">
    <!-- ── TABEL HARIAN ── -->
    <div class="card">
        <div class="card-title">📅 Rekap Penjualan Harian</div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Tanggal</th><th>Kasir</th><th>Nota</th><th>Omzet</th></tr></thead>
                <tbody>
                <?php if (empty($data_harian)): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--muted);">Tidak ada data.</td></tr>
                <?php endif; ?>
                <?php foreach ($data_harian as $h): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($h['tanggal'])) ?></td>
                        <td><?= htmlspecialchars($h['kasir']) ?></td>
                        <td><span class="badge badge-info"><?= $h['jumlah_nota'] ?></span></td>
                        <td style="color:var(--success);font-weight:700;">
                            Rp <?= number_format($h['total_omzet'],0,',','.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── BEST SELLER TABLE ── -->
    <div class="card">
        <div class="card-title">🏆 Best Seller</div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>#</th><th>Produk</th><th>Qty</th><th>Omzet</th></tr></thead>
                <tbody>
                <?php if (empty($data_best)): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--muted);">Tidak ada data.</td></tr>
                <?php endif; ?>
                <?php foreach ($data_best as $i => $b): ?>
                    <tr>
                        <td><?= ['🥇','🥈','🥉'][$i] ?? ($i+1) ?></td>
                        <td><?= htmlspecialchars($b['nama_produk']) ?></td>
                        <td><strong style="color:var(--primary)"><?= number_format($b['total_qty']) ?></strong></td>
                        <td style="color:var(--success);font-size:.85rem;">Rp <?= number_format($b['total_omzet'],0,',','.') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── MUTASI STOK ── -->
<div class="card">
    <div class="card-title">🔄 Riwayat Mutasi Stok</div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Waktu</th><th>Produk</th><th>Tipe</th><th>Jumlah</th><th>Keterangan</th></tr></thead>
            <tbody>
            <?php if (empty($mutasi)): ?>
                <tr><td colspan="5" style="text-align:center;color:var(--muted);">Tidak ada data.</td></tr>
            <?php endif; ?>
            <?php foreach ($mutasi as $m): ?>
                <tr>
                    <td style="white-space:nowrap;font-size:.85rem;"><?= date('d/m/Y H:i', strtotime($m['waktu_log'])) ?></td>
                    <td><?= htmlspecialchars($m['nama_produk']) ?></td>
                    <td>
                        <?php if ($m['tipe'] === 'Masuk'): ?>
                            <span class="badge badge-success">📥 Masuk</span>
                        <?php else: ?>
                            <span class="badge badge-danger">📤 Keluar</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= $m['jumlah'] ?></strong></td>
                    <td style="color:var(--muted);font-size:.85rem;"><?= htmlspecialchars($m['keterangan'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>