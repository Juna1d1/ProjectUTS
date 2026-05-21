<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';
requireAdmin();

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama      = trim($_POST['nama_produk'] ?? '');
    $harga_raw = preg_replace('/[^0-9.]/', '', $_POST['harga_jual'] ?? '');
    $harga     = (float)$harga_raw;
    $stok      = (int)($_POST['stok'] ?? 0);

    // Validasi
    if (empty($nama)) {
        flashMessage('danger', 'Data produk tidak lengkap, semua kolom wajib diisi!');
        header('Location: create.php');
        exit;
    }

    if ($harga < 0) {
        flashMessage('danger', 'Harga harus berupa angka positif!');
        header('Location: create.php');
        exit;
    }

    if ($stok < 0) {
        flashMessage('danger', 'Stok harus berupa angka positif!');
        header('Location: create.php');
        exit;
    }

    // INSERT produk
    $stmt = $db->prepare(" INSERT INTO m_produk (nama_produk, harga_jual, stok) VALUES (?, ?, ?) ");

    $stmt->execute([$nama, $harga, $stok]);
    $new_id = $db->lastInsertId();

    // Log stok awal
    $ket = "Stok awal produk: {$nama}";
    $log = $db->prepare(" INSERT INTO t_log_stok (id_produk, jumlah, tipe, keterangan) VALUES (?, ?, 'Masuk', ?) ");
    $log->execute([$new_id, $stok, $ket]);
    flashMessage('success', "Produk '{$nama}' berhasil ditambahkan.");

    header('Location: index.php');
    exit;
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

    <div style="width:100%; max-width:620px;">

        <!-- HEADER -->
        <div style="
            display:flex;
            align-items:center;
            gap:1rem;
            margin-bottom:1.5rem;
            justify-content:center;
            flex-wrap:wrap;
        ">
            <a href="index.php" class="btn btn-secondary btn-sm">← Kembali</a>

            <h1 class="page-title" style="margin-bottom:0; text-align:center;">
                ➕ Tambah <span>Produk Baru</span>
            </h1>
        </div>

        <!-- CARD -->
        <div class="card" style="
            width:100%;
            max-width:620px;
            margin:auto;
        ">

            <div class="card-title">
                📦 Form Tambah Produk
            </div>

            <form method="POST" action="">

                <div class="form-group">
                    <label>
                        Nama Produk
                        <span style="color:var(--danger)">*</span>
                    </label>

                    <input
                        type="text"
                        name="nama_produk"
                        class="form-control"
                        placeholder="Contoh: Indomie Goreng"
                        value="<?= htmlspecialchars($_POST['nama_produk'] ?? '') ?>"
                        autofocus
                        required>
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
                        placeholder="Contoh: 3500"
                        value="<?= htmlspecialchars($_POST['harga_jual'] ?? '') ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>
                        Stok Awal
                        <span style="color:var(--danger)">*</span>
                    </label>

                    <input
                        type="number"
                        name="stok"
                        class="form-control"
                        min="0"
                        placeholder="Contoh: 100"
                        value="<?= htmlspecialchars($_POST['stok'] ?? '0') ?>"
                        required>

                    <small style="
                        color:var(--muted);
                        font-size:0.8rem;
                        margin-top:0.3rem;
                        display:block;
                    ">
                        Stok awal otomatis dicatat di log mutasi sebagai stok masuk.
                    </small>
                </div>

                <div style="
                    display:flex;
                    gap:0.7rem;
                    margin-top:1.5rem;
                    justify-content:center;
                    flex-wrap:wrap;
                ">
                    <button type="submit" class="btn btn-primary">
                        💾 Simpan Produk
                    </button>

                    <a href="index.php" class="btn btn-secondary">
                        ❌ Batal
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>