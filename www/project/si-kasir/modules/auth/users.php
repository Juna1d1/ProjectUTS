<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';
requireAdmin();

$db = getDB();

// --- DELETE USER ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    if ($del_id === (int)$_SESSION['id_user']) {
        flashMessage('danger', 'Tidak bisa menghapus akun sendiri!');
    } else {
        // Cek apakah user terikat transaksi
        $chk = $db->prepare(" SELECT COUNT(*) as total FROM t_penjualan WHERE id_user = ? ");
        $chk->execute([$del_id]);
        $row = $chk->fetch();
        if ($row['total'] > 0) {
            flashMessage('danger', 'User tidak dapat dihapus karena masih terikat data transaksi!');
        } else {
            $stmt = $db->prepare("
                DELETE FROM m_user
                WHERE id_user = ?
            ");

            $stmt->execute([$del_id]);
            flashMessage('success', 'User berhasil dihapus.');
        }
    }
    header('Location: users.php');
    exit;
}

// --- TAMBAH / EDIT USER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id_user'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? '';

    if (empty($username) || empty($role)) {
        flashMessage('danger', 'Username dan Role wajib diisi!');
    } elseif (!in_array($role, ['Admin','Kasir'])) {
        flashMessage('danger', 'Role tidak valid!');
    } else {
        if ($id === 0) {
            // CREATE
            if (empty($password)) {
                flashMessage('danger', 'Password wajib diisi untuk user baru!');
                header('Location: users.php');
                exit;
            }

            try {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $db->prepare("
                    INSERT INTO m_user (username, password, role) 
                    VALUES (?,?,?)
                ");

                $stmt->execute([$username, $hash, $role]);

                flashMessage('success', "User '{$username}' berhasil ditambahkan.");

            } catch (PDOException $e) {

                if ($e->getCode() == 23000) {
                    flashMessage('danger', 'Username sudah digunakan.');
                } else {
                    flashMessage('danger', 'Terjadi kesalahan database.');
                }
            }
        } else {
            // UPDATE
            try {

                // cek username dipakai user lain atau tidak
                $cek = $db->prepare("
                    SELECT COUNT(*) 
                    FROM m_user 
                    WHERE username = ? AND id_user != ?
                ");
                $cek->execute([$username, $id]);

                if ($cek->fetchColumn() > 0) {
                    flashMessage('danger', 'Username sudah digunakan, silakan pilih yang lain.');
                    header('Location: users.php');
                    exit;
                }

                if (!empty($password)) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $db->prepare("
                        UPDATE m_user 
                        SET username=?, password=?, role=? 
                        WHERE id_user=?
                    ");

                    $success = $stmt->execute([$username, $hash, $role, $id]);

                } else {

                    $stmt = $db->prepare("
                        UPDATE m_user 
                        SET username=?, role=? 
                        WHERE id_user=?
                    ");

                    $success = $stmt->execute([$username, $role, $id]);
                }

                if ($success) {
                    flashMessage('success', 'User berhasil diupdate.');
                } else {
                    flashMessage('danger', 'Gagal mengupdate user.');
                }

            } catch (PDOException $e) {

                if ($e->getCode() == 23000) {
                    flashMessage('danger', 'Username sudah digunakan.');
                } else {
                    flashMessage('danger', 'Terjadi kesalahan database.');
                }
            }
        }
    }
    header('Location: users.php');
    exit;
}

// --- EDIT: ambil data user ---
$edit_user = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $eid  = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM m_user WHERE id_user = ?");
    $stmt->execute([$eid]);
    $edit_user = $stmt->fetch();
}

// --- LIST USERS ---
$users = $db->query("SELECT * FROM m_user ORDER BY id_user ASC")->fetchall();

include __DIR__ . '/../../includes/header.php';
?>

<h1 class="page-title">👥 Manajemen <span>Pengguna</span></h1>

<div class="grid-2">
    <!-- FORM TAMBAH / EDIT -->
    <div class="card">
        <div class="card-title"><?= $edit_user ? '✏️ Edit User' : '➕ Tambah User Baru' ?></div>
        <form method="POST" action="">
            <?php if ($edit_user): ?>
                <input type="hidden" name="id_user" value="<?= $edit_user['id_user'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control"
                    value="<?= htmlspecialchars($edit_user['username'] ?? '') ?>"
                    placeholder="Masukkan username..." required>
            </div>

            <div class="form-group">
                <label>Password <?= $edit_user ? '(kosongkan jika tidak diubah)' : '' ?></label>
                <input type="password" name="password" class="form-control"
                    placeholder="<?= $edit_user ? 'Isi untuk ganti password...' : 'Masukkan password...' ?>">
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="Admin"  <?= ($edit_user['role'] ?? '') === 'Admin'  ? 'selected' : '' ?>>Admin</option>
                    <option value="Kasir"  <?= ($edit_user['role'] ?? '') === 'Kasir'  ? 'selected' : '' ?>>Kasir</option>
                </select>
            </div>

            <div style="display:flex; gap:0.7rem;">
                <button type="submit" class="btn btn-primary">
                    <?= $edit_user ? '💾 Update' : '➕ Tambah' ?>
                </button>
                <?php if ($edit_user): ?>
                    <a href="users.php" class="btn btn-secondary">❌ Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- DAFTAR USER -->
    <div class="card">
        <div class="card-title">📋 Daftar Pengguna</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id_user'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td>
                            <span class="badge <?= $u['role'] === 'Admin' ? 'badge-info' : 'badge-success' ?>">
                                <?= $u['role'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="users.php?edit=<?= $u['id_user'] ?>" class="btn btn-warning btn-sm">✏️</a>
                            <?php if ($u['id_user'] !== (int)$_SESSION['id_user']): ?>
                            <a href="users.php?delete=<?= $u['id_user'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Hapus user ini?')">🗑️</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>