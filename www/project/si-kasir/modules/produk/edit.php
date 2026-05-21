<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';
requireAdmin();

$db = getDB();

// Ambil data produk yang mau diedit
$id = (int)($_GET['id'] ?? $_POST['id_produk'] ?? 0);
if ($id <= 0) {
    flashMessage('danger', 'ID produk tidak valid.');
    header('Location: index.php'); exit;
}

$stmt = $db->prepare("SELECT * FROM m_produk WHERE id_produk = ?");
$stmt->execute([$id]);
$produk = $stmt->fetch();

if (!$produk) {
    flashMessage('danger', 'Produk tidak ditemukan.');
    header('Location: index.php'); exit;
}

// --- PROSES UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama_produk'] ?? '');
    $harga_raw = preg_replace('/[^0-9.]/', '', $_POST['harga_jual'] ?? '');
    $harga     = (float)$harga_raw;
    $stok      = (int)($_POST['stok'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $action    = $_POST['action'] ?? 'save';

    // Validasi
    if (empty($nama)) {
        flashMessage('danger', 'Data produk tidak lengkap, semua kolom wajib diisi!');
        header("Location: edit.php?id={$id}"); exit;
    }
    if ($harga < 0) {
        flashMessage('danger', 'Harga harus berupa angka positif!');
        header("Location: edit.php?id={$id}"); exit;
    }
    if ($stok < 0) {
        flashMessage('danger', 'Stok harus berupa angka positif!');
        header("Location: edit.php?id={$id}"); exit;
    }

    // UPDATE data produk
    $upd = $db->prepare("UPDATE m_produk SET nama_produk = ?, harga_jual = ?, stok = ? WHERE id_produk = ?");
    $upd->execute([$nama, $harga, $stok, $id]);

    // Jika stock opname — catat log mutasi
    if ($action === 'opname') {
        $ket_log = !empty($keterangan)
            ? $keterangan
            : "Stock Opname manual untuk produk: {$nama}";
        $log = $db->prepare("INSERT INTO t_log_stok (id_produk, jumlah, tipe, keterangan) VALUES (?, ?, 'Masuk', ?)");
        $log->execute([$id, $stok, $ket_log]);
        flashMessage('success', "Produk diperbarui & log opname dicatat.");
    } else {
        flashMessage('success', "Produk '{$nama}' berhasil diperbarui.");
    }

    header('Location: index.php'); exit;
}

include __DIR__ . '/../../includes/header.php';
?>

<div style="
    min-height: calc(100vh - 140px);
    display:flex;
    justify-content:center;
    align-items:center;
    padding:2rem;
">

    <div style="width:100%; max-width:650px;">

        <!-- HEADER -->
        <div style="
            display:flex;
            align-items:center;
            justify-content:center;
            gap:1rem;
            margin-bottom:1.5rem;
            flex-wrap:wrap;
        ">
            <a href="index.php" class="btn btn-secondary btn-sm">
                ← Kembali
            </a>

            <h1 class="page-title" style="margin-bottom:0; text-align:center;">
                ✏️ Edit <span>Produk</span>
            </h1>
        </div>

        <!-- CARD -->
        <div class="card" style="
            width:100%;
            max-width:650px;
            margin:auto;
        ">

            <div class="card-title">
                📦 Edit:
                <?= htmlspecialchars($produk['nama_produk']) ?>

                <small style="
                    font-size:0.8rem;
                    color:var(--muted);
                    font-weight:400;
                    margin-left:0.5rem;
                ">
                    ID #<?= $produk['id_produk'] ?>
                </small>
            </div>

            <form method="POST" action="">
                <input
                    type="hidden"
                    name="id_produk"
                    value="<?= $produk['id_produk'] ?>"
                >

                <div class="form-group">
                    <label>
                        Nama Produk
                        <span style="color:var(--danger)">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_produk"
                        class="form-control"
                        value="<?= htmlspecialchars($_POST['nama_produk'] ?? $produk['nama_produk']) ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label>
                        Harga Jual (Rp)
                        <span style="color:var(--danger)">*</span>
                    </label>

                    <input
                        type="number"
                        name="harga_jual"
                        class="form-control"
                        min="0"
                        step="0.01"
                        value="<?= $_POST['harga_jual'] ?? $produk['harga_jual'] ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>
                        Stok
                        <span style="color:var(--danger)">*</span>
                    </label>

                    <input
                        type="number"
                        name="stok"
                        class="form-control"
                        min="0"
                        value="<?= $_POST['stok'] ?? $produk['stok'] ?>"
                        required
                    >

                    <small style="
                        color:var(--muted);
                        font-size:0.8rem;
                        margin-top:0.3rem;
                        display:block;
                    ">
                        Stok saat ini:
                        <strong style="color:var(--primary)">
                            <?= $produk['stok'] ?>
                        </strong>
                    </small>
                </div>

                <!-- Keterangan -->
                <div class="form-group">
                    <label>
                        Keterangan Stock Opname
                        <span style="
                            color:var(--muted);
                            font-weight:400;
                        ">
                            (opsional)
                        </span>
                    </label>

                    <input
                        type="text"
                        name="keterangan"
                        class="form-control"
                        placeholder="Misal: Hasil stock opname bulan Mei 2026"
                    >
                </div>

                <!-- INFO -->
                <div style="
                    border-top:1px solid var(--border);
                    padding-top:1rem;
                    margin-top:0.5rem;
                ">

                    <p style="
                        font-size:0.82rem;
                        color:var(--muted);
                        margin-bottom:1rem;
                        text-align:center;
                    ">
                        <strong>Simpan & Log Opname</strong>
                        untuk update + catat mutasi stok ke riwayat.
                    </p>

                    <div style="
                        display:flex;
                        gap:0.7rem;
                        flex-wrap:wrap;
                        justify-content:center;
                    ">
                        <button
                            type="submit"
                            name="action"
                            value="opname"
                            class="btn btn-warning"
                        >
                            📦 Simpan & Log Opname
                        </button>

                        <a href="index.php" class="btn btn-secondary">
                            ❌ Batal
                        </a>
                    </div>

                </div>

            </form>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
