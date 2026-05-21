<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';
requireLogin();

$db   = getDB();
$user = currentUser();

// ── Inisialisasi keranjang ──
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// ── TAMBAH ke keranjang ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_cart') {
    $id_produk = (int)$_POST['id_produk'];
    $qty       = (int)$_POST['qty'];

    if ($qty <= 0) {
        flashMessage('danger', 'Qty harus lebih dari 0.');
        header('Location: index.php'); exit;
    }

    // Ambil data produk
    $stmt = $db->prepare("
        SELECT * FROM m_produk
        WHERE id_produk = ?
    ");

    $stmt->execute([$id_produk]);

    $produk = $stmt->fetch();

    if (!$produk) {
        flashMessage('danger', 'Produk tidak ditemukan.');
        header('Location: index.php'); exit;
    }

    // Hitung total qty sudah di keranjang
    $sudah_di_cart = 0;
    foreach ($_SESSION['cart'] as $item) {
        if ($item['id_produk'] === $id_produk) {
            $sudah_di_cart = $item['qty'];
            break;
        }
    }

    $total_qty = $sudah_di_cart + $qty;

    // Cek stok
    if ($total_qty > $produk['stok']) {
        flashMessage('danger', "Stok {$produk['nama_produk']} tidak mencukupi untuk transaksi ini.");
        header('Location: index.php'); exit;
    }

    // Update atau tambah ke keranjang
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id_produk'] === $id_produk) {
            $item['qty']      = $total_qty;
            $item['subtotal'] = $total_qty * $item['harga_jual'];
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        $_SESSION['cart'][] = [
            'id_produk'   => $id_produk,
            'nama_produk' => $produk['nama_produk'],
            'harga_jual'  => (float)$produk['harga_jual'],
            'qty'         => $qty,
            'subtotal'    => $qty * (float)$produk['harga_jual'],
        ];
    }

    header('Location: index.php'); exit;
}

// ── HAPUS item dari keranjang ──
if (isset($_GET['remove'])) {
    $rm = (int)$_GET['remove'];
    $_SESSION['cart'] = array_values(array_filter(
        $_SESSION['cart'],
        fn($i) => $i['id_produk'] !== $rm
    ));
    header('Location: index.php'); exit;
}

// ── KOSONGKAN keranjang ──
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header('Location: index.php'); exit;
}

// ── SELESAIKAN TRANSAKSI ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
    $uang_bayar = (float)preg_replace('/[^0-9.]/', '', $_POST['uang_bayar'] ?? '0');

    if (empty($_SESSION['cart'])) {
        flashMessage('danger', 'Keranjang kosong!');
        header('Location: index.php'); exit;
    }

    // Hitung total
    $total = array_sum(array_column($_SESSION['cart'], 'subtotal'));

    if ($uang_bayar < $total) {
        flashMessage('danger', 'Uang bayar kurang dari total tagihan.');
        header('Location: index.php'); exit;
    }

    // Generate nomor nota: PJN + YYYYMMDD + random 4 digit
    $nomor_nota = 'PJN' . date('Ymd') . rand(1000, 9999);

    // Cek duplikat nota
    $chk = $db->prepare("
        SELECT id_penjualan
        FROM t_penjualan
        WHERE nomor_nota = ?
    ");

    $chk->execute([$nomor_nota]);

    if ($chk->rowCount() > 0) {
        flashMessage('danger', 'Transaksi sudah diproses sebelumnya (Duplicate Nota).');
        header('Location: index.php'); exit;
    }

    // ── DB TRANSACTION ──
    $db->beginTransaction();
    try {
        $tgl      = date('Y-m-d H:i:s');
        $id_user  = (int)$_SESSION['id_user'];

        // Insert header penjualan
        $stmt = $db->prepare("
            INSERT INTO t_penjualan
            (nomor_nota, tgl_transaksi, total_bayar, id_user)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$nomor_nota, $tgl, $total, $id_user]);

        $id_penjualan = $db->lastInsertId();

        foreach ($_SESSION['cart'] as $item) {
            $id_produk = $item['id_produk'];
            $qty       = $item['qty'];
            $subtotal  = $item['subtotal'];

            // Re-check stok (race condition guard)
            $cek = $db->prepare("
                SELECT stok, nama_produk
                FROM m_produk
                WHERE id_produk = ?
                FOR UPDATE
            ");

            $cek->execute([$id_produk]);

            $row = $cek->fetch();

            if ($row['stok'] < $qty) {
                throw new Exception("Stok {$row['nama_produk']} tidak mencukupi untuk transaksi ini.");
            }

            // Insert detail
            $det = $db->prepare("INSERT INTO t_penjualan_detail (id_penjualan, id_produk, qty, subtotal) VALUES (?,?,?,?)");
            $det->execute([$id_penjualan, $id_produk, $qty, $subtotal]);

            // Kurangi stok
            $upd = $db->prepare("UPDATE m_produk SET stok = stok - ? WHERE id_produk = ?");
            $upd->execute([$qty, $id_produk]);

            // Log stok keluar
            $ket = "Penjualan Nota #{$nomor_nota}";
            $log = $db->prepare("INSERT INTO t_log_stok (id_produk, jumlah, tipe, keterangan) VALUES (?,?,'Keluar',?)");
            $log->execute([$id_produk, $qty, $ket]);
        }

        $db->commit();

        // Simpan data nota ke session untuk ditampilkan
        $_SESSION['last_nota'] = [
            'nomor_nota'   => $nomor_nota,
            'tgl'          => $tgl,
            'items'        => $_SESSION['cart'],
            'total'        => $total,
            'uang_bayar'   => $uang_bayar,
            'kembalian'    => $uang_bayar - $total,
            'kasir'        => $user['username'],
        ];

        $_SESSION['cart'] = [];
        header('Location: simpan.php');
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        flashMessage('danger', $e->getMessage());
        header('Location: index.php');
        exit;
    }
}

// ── Data produk untuk dropdown ──
$produks = $db->query("SELECT id_produk, nama_produk, harga_jual, stok FROM m_produk WHERE stok > 0 ORDER BY nama_produk ASC")->fetchall();

// Hitung total keranjang
$cart_total = array_sum(array_column($_SESSION['cart'], 'subtotal'));

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="page-title">🧾 Transaksi <span>Penjualan</span></h1>

<div class="grid-2">

    <!-- ── FORM TAMBAH BARANG ── -->
    <div class="card">
        <div class="card-title">🛍️ Tambah Barang ke Keranjang</div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add_cart">

            <div class="form-group">
                <label>Pilih Barang</label>
                <select name="id_produk" id="select_produk" class="form-control" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($produks as $p): ?>
                    <option value="<?= $p['id_produk'] ?>"
                        data-harga="<?= $p['harga_jual'] ?>"
                        data-stok="<?= $p['stok'] ?>">
                        <?= htmlspecialchars($p['nama_produk']) ?>
                        (Stok: <?= $p['stok'] ?> | Rp <?= number_format($p['harga_jual'], 0, ',', '.') ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Harga Satuan</label>
                <input type="text" id="info_harga" class="form-control" readonly
                    placeholder="Pilih produk dulu..." style="color:var(--primary); font-weight:700;">
            </div>

            <div class="form-group">
                <label>Qty</label>
                <input type="number" name="qty" id="input_qty" class="form-control"
                    min="1" value="1" placeholder="Jumlah">
            </div>

            <div class="form-group">
                <label>Subtotal Estimasi</label>
                <input type="text" id="info_subtotal" class="form-control" readonly
                    placeholder="—" style="color:var(--success); font-weight:700;">
            </div>

            <button type="submit" class="btn btn-primary">➕ Masukkan ke Keranjang</button>
        </form>
    </div>

    <!-- ── KERANJANG ── -->
    <div class="card">
        <div class="card-title">🛒 Keranjang Belanja</div>

        <?php if (empty($_SESSION['cart'])): ?>
            <p style="color:var(--muted); text-align:center; padding:2rem 0;">Keranjang masih kosong.</p>
        <?php else: ?>
            <?php foreach ($_SESSION['cart'] as $item): ?>
            <div class="cart-item">
                <div>
                    <strong><?= htmlspecialchars($item['nama_produk']) ?></strong><br>
                    <small style="color:var(--muted)">
                        <?= $item['qty'] ?> × Rp <?= number_format($item['harga_jual'], 0, ',', '.') ?>
                    </small>
                </div>
                <div style="display:flex; align-items:center; gap:1rem;">
                    <strong style="color:var(--success)">
                        Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                    </strong>
                    <a href="index.php?remove=<?= $item['id_produk'] ?>" class="btn btn-danger btn-sm">✕</a>
                </div>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:1.2rem; padding-top:1rem; border-top:2px solid var(--primary);">
                <div style="display:flex; justify-content:space-between; font-size:1.2rem; font-weight:800; margin-bottom:1rem;">
                    <span>Total</span>
                    <span style="color:var(--primary)">Rp <?= number_format($cart_total, 0, ',', '.') ?></span>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="checkout">
                    <div class="form-group">
                        <label>💵 Uang Bayar (Rp)</label>
                        <input type="number" name="uang_bayar" id="uang_bayar" class="form-control"
                            min="<?= $cart_total ?>" placeholder="Masukkan nominal..." required>
                    </div>
                    <div class="form-group">
                        <label>💰 Kembalian</label>
                        <input type="text" id="kembalian_preview" class="form-control" readonly
                            placeholder="—" style="color:var(--warning); font-weight:800; font-size:1.1rem;">
                    </div>
                    <div style="display:flex; gap:0.7rem;">
                        <button type="submit" class="btn btn-success">✅ Selesaikan Transaksi</button>
                        <a href="index.php?clear=1" class="btn btn-danger"
                           onclick="return confirm('Kosongkan keranjang?')">🗑️ Kosongkan</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const total = <?= $cart_total ?>;
const fmt = n => 'Rp ' + Math.floor(n).toLocaleString('id-ID');

// Update info harga & subtotal saat pilih produk
const sel   = document.getElementById('select_produk');
const qty   = document.getElementById('input_qty');
const infoH = document.getElementById('info_harga');
const infoS = document.getElementById('info_subtotal');

function updateInfo() {
    const opt   = sel.options[sel.selectedIndex];
    const harga = parseFloat(opt?.dataset?.harga || 0);
    const q     = parseInt(qty?.value || 1);
    infoH.value = harga ? fmt(harga) : '';
    infoS.value = harga ? fmt(harga * q) : '';
}

sel?.addEventListener('change', updateInfo);
qty?.addEventListener('input',  updateInfo);

// Kembalian preview
const uangInput = document.getElementById('uang_bayar');
const kemInput  = document.getElementById('kembalian_preview');

uangInput?.addEventListener('input', () => {
    const bayar = parseFloat(uangInput.value || 0);
    const selisih = bayar - total;
    kemInput.value = selisih >= 0 ? fmt(selisih) : '⚠️ Kurang: ' + fmt(Math.abs(selisih));
    kemInput.style.color = selisih >= 0 ? 'var(--warning)' : 'var(--danger)';
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>