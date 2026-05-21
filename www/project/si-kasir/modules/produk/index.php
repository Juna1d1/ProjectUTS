<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';
requireAdmin();

$db = getDB();

// --- FILTER & SEARCH ---
$search        = trim($_GET['search'] ?? '');
$filter_kritis = isset($_GET['kritis']);

$where  = [];
$params = [];

if ($search !== '') {
    $where[]  = "nama_produk LIKE ?";
    $params[] = "%{$search}%";
}
if ($filter_kritis) {
    $where[] = "stok < 5";
}

$sql = "SELECT * FROM m_produk";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY id_produk DESC";

if ($params) {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $produks = $stmt->fetchAll();
} else {
    $produks = $db->query($sql)->fetchAll();
}

include __DIR__ . '/../../includes/header.php';
?>

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <h1 class="page-title" style="margin-bottom:0;">📦 Master <span>Produk</span></h1>
    <a href="tambah.php" class="btn btn-primary">➕ Tambah Produk Baru</a>
</div>

<div class="card">
    <div class="card-title">📋 Daftar Produk</div>

    <!-- Search & Filter -->
    <form method="GET" class="search-bar">
        <input type="text" name="search" class="form-control"
            style="flex:1; max-width:280px"
            placeholder="🔍 Cari nama produk..."
            value="<?= htmlspecialchars($search) ?>">
        <label style="display:flex; align-items:center; gap:0.4rem; color:var(--muted); font-size:0.9rem; cursor:pointer;">
            <input type="checkbox" name="kritis" <?= $filter_kritis ? 'checked' : '' ?>>
            ⚠️ Stok Kritis (&lt; 5)
        </label>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="index.php" class="btn btn-secondary btn-sm">Reset</a>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Produk</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produks)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--muted); padding:2rem;">
                        Tidak ada produk ditemukan.
                    </td>
                </tr>
                <?php endif; ?>
                <?php foreach ($produks as $p): ?>
                <tr>
                    <td><?= $p['id_produk'] ?></td>
                    <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                    <td>Rp <?= number_format($p['harga_jual'], 0, ',', '.') ?></td>
                    <td>
                        <?php if ($p['stok'] < 5): ?>
                            <span class="badge badge-danger">⚠️ <?= $p['stok'] ?></span>
                        <?php else: ?>
                            <span class="badge badge-success"><?= $p['stok'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="display:flex; gap:0.4rem;">
                        <a href="edit.php?id=<?= $p['id_produk'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                        <a href="hapus.php?id=<?= $p['id_produk'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Yakin hapus produk \'<?= htmlspecialchars(addslashes($p['nama_produk'])) ?>\'?')">
                           🗑️ Hapus
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
