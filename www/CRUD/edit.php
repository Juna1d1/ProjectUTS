<?php
require 'koneksi.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT id,nama, jk, pendidikan, hobi, alamat FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if(!$user){
        die("Data tidak ditemukan!");
    }
}

if(isset($_POST['update'])){
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $jk = $_POST['jk'];
    $pendidikan = $_POST['pendidikan'];
    $alamat = $_POST['alamat'];
    $hobi = isset($_POST['hobi']) ? implode(", ", $_POST['hobi']) : "-";

    $sql = "UPDATE users SET nama = ?, jk = ?, pendidikan = ?, hobi = ?, alamat = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$nama, $jk, $pendidikan, $hobi, $alamat, $id])){
        header("Location: index.php");
        exit;
    }

    
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Form to Database</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">
    <h2>Manajemen User</h2>
    <form method="post">
        <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="hidden" name="id" value="<?= $user['id']  ?>">
            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama'])  ?>">
        </div>

        <div class="form-group">
            <label>Jenis Kelamin</label>
            <div class="radio-group">
                <input type="radio" name="jk" value="Laki-Laki" <?= ($user['jk'] == 'Laki-Laki') ? 'checked' : '' ?>>Laki-laki 
                <input type="radio" name="jk" value="Perempuan" <?= ($user['jk'] == 'Perempuan') ? 'checked' : '' ?>> Perempuan
            </div>
        </div>

        <div class="form-group">
            <label>Pendidikan</label>
            <select name="pendidikan">
                <option value="SMA" <?= ($user['pendidikan'] == 'SMA') ? 'selected' : '' ?>>SMA/SMK</option>
                <option value="D3" <?= ($user['pendidikan'] == 'D3') ? 'selected' : '' ?>>Diploma (D3)</option>
                <option value="S1" <?= ($user['pendidikan'] == 'S1') ? 'selected' : '' ?>>Sarjana (S1)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Hobi</label>
            <div class="check-group">
                <?php
                $hobi_user = explode(", ", $user['hobi']);
                ?>
                <input type="checkbox" name="hobi[]" value="Coding" <?= in_array("Coding", $hobi_user) ? 'checked' : '' ?>> Coding
                <input type="checkbox" name="hobi[]" value="Gaming" <?= in_array("Gaming", $hobi_user) ? 'checked' : ''?>> Gaming
            </div>
        </div>

        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" rows="3"><?= htmlspecialchars($user['alamat']) ?></textarea>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" name="update" class="btn btn-add" style="flex: 3;">Simpan Perubahan</button>
            <a href="index.php" class="btn btn-del" style="flex: 1; text-align: center; background: #6c757d;">Batal</a>
        </div>
    </form>
</div>
    
</body>
</html>