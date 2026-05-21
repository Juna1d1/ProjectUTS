<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth_helper.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    header('Location: ' . ($role === 'Admin' ? BASE_URL . '/modules/produk/index.php' : BASE_URL . '/modules/transaksi/index.php'));
    exit;
}

$error = '';

// Bruteforce prevention
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['login_lockout']))  $_SESSION['login_lockout']  = 0;

$locked = (time() < $_SESSION['login_lockout']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validasi empty field
    if (empty($username) || empty($password)) {
        $error = 'Username dan Password wajib diisi!';
    } else {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT id_user, username, password, role
            FROM m_user
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->execute([$username]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            $_SESSION['login_attempts'] = 0;
            $_SESSION['login_lockout']  = 0;

            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            $redirect = $user['role'] === 'Admin'
                ? BASE_URL . '/modules/produk/index.php'
                : BASE_URL . '/modules/transaksi/index.php';

            header('Location: ' . $redirect);
            exit;

        } else {

            $_SESSION['login_attempts']++;

            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_lockout'] = time() + 300;
                $error = 'Terlalu banyak percobaan, tunggu 5 menit.';
            } else {
                $error = 'Username atau Password salah!';
            }
        }
    }
} elseif ($locked) {
    $remaining = ceil(($_SESSION['login_lockout'] - time()) / 60);
    $error = "Terlalu banyak percobaan, tunggu {$remaining} menit.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SI-KASIR</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <style>
        .input-icon-wrap { position: relative; }
        .input-icon-wrap .icon {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%); font-size: 1rem;
        }
        .input-icon-wrap .form-control { padding-left: 2.5rem; }
        .divider { text-align: center; margin: 1rem 0; color: var(--muted); font-size: 0.8rem; }
        .version-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff; font-size: 0.7rem; font-weight: 700;
            padding: 0.15rem 0.6rem; border-radius: 20px; vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <span class="logo-icon">🛒</span>
            <h1>SI-KASIR</h1>
            <p>Sistem Informasi Kasir Terintegrasi <span class="version-badge">v1.0</span></p>
            <p style="margin-top:0.4rem; font-size:0.78rem; color:var(--muted);">Toko Swalayan Maju Jaya</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-icon-wrap">
                    <span class="icon">👤</span>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="Masukkan username..."
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        autocomplete="username"
                        <?= $locked ? 'disabled' : '' ?>
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon-wrap">
                    <span class="icon">🔒</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Masukkan password..."
                        autocomplete="current-password"
                        <?= $locked ? 'disabled' : '' ?>
                    >
                </div>
            </div>

            <button type="submit" class="btn-login" <?= $locked ? 'disabled' : '' ?>>
                Login
            </button>
        </form>

        <div class="login-footer-text">
            &copy; <?= date('Y') ?> SI-KASIR — Maju Jaya &nbsp;|&nbsp;
            Hubungi Admin untuk reset password
        </div>
    </div>
</div>
</body>
</html>