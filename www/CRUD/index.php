<?php
require 'koneksi.php';

//-- Logika Tambah Data ---
if(isset($_POST['tambah'])){
    $nama = $_POST['nama'];
    $jk = $_POST['jk'];
    $pendidikan = $_POST['pendidikan'];
    $alamat = $_POST['alamat'];
    $hobi = isset($_POST['hobi']) ? implode(", ", $_POST['hobi']) : "-";

    $sql = "INSERT INTO Users (nama, jk, pendidikan, hobi, alamat) VALUES(?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nama, $jk, $pendidikan, $hobi, $alamat]);

    header("Location: index.php");
    exit;
}

if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;
}

$stmt = $pdo->query("SELECT id,nama,jk,pendidikan,hobi,alamat FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();


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
            <input type="text" name="nama" placeholder="Masukan Nama Lengkap" required>
        </div>

        <div class="form-group">
            <label>Jenis Kelamin</label>
            <div class="radio-group">
                <input type="radio" name="jk" value="Laki-Laki" checked> Laki-Laki
                <input type="radio" name="jk" value="Perempuan"> Perempuan
            </div>
        </div>

        <div class="form-group">
            <label>Pendidikan</label>
            <select name="pendidikan">
                <option value="SMA">SMA/SMK</option>
                <option value="D3">Diploma (D3)</option>
                <option value="S1">Sarjana (S1)</option>
            </select>
        </div>

        <div class="form-group">
            <label>Hobi</label>
            <div class="check-group">
                <input type="checkbox" name="hobi[]" value="Coding"> Coding
                <input type="checkbox" name="hobi[]" value="Gaming"> Gaming
            </div>
        </div>

        <div class="form-group">
            <label>Alamat</label>
            <textarea name="alamat" rows="3" placeholder="Masukan Alamat"></textarea>
        </div>

        <button type="submit" name="tambah" class="btn btn-add">Simpan Data</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>NAMA LENGKAP</th>
            <th>JENIS KELAMIN</th>
            <th>PENDIDIKAN</th>
            <th>HOBI</th>
            <th>ALAMAT</th>
            <th>AKSI</th>
        </tr>
        <?php foreach($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['nama'] )?></td>
            <td><?= $user['jk']?></td>
            <td><?= $user['pendidikan']?></td>
            <td><?= htmlspecialchars($user['hobi'] )?></td>
            <td><?= nl2br(htmlspecialchars($user['alamat'])) ?></td>
            <td>
                <a href="edit.php?id=<?= $user['id']  ?>" class="btn btn-edit">Edit</a>
                <a href="?hapus=<?= $user['id'] ?>" class="btn btn-del">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
    
</body>
</html>