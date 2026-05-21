<?php

require 'koneksi.php';

$id = $_GET['id'] ?? null;

// Validasi sederhana (biar gak error kalau id kosong)
if (!$id) {
    die("ID tidak ditemukan");
}

$data = $pdo->prepare("SELECT * FROM barang WHERE id_barang = ?");
$data->execute([$id]);

$d = $data->fetch();

// Kalau data tidak ada
if (!$d) {
    die("Data tidak ditemukan");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "UPDATE barang SET 
                nama_barang = ?, 
                id_kategori = ?, 
                stok = ?, 
                kondisi = ?
            WHERE id_barang = ?";

    $stmt = $pdo->prepare($sql);

    // Eksekusi update dengan data dari form
    $stmt->execute([
        $_POST['nama'],      
        $_POST['kategori'],  
        $_POST['stok'],      
        $_POST['kondisi'],    
        $id                 
    ]);

    // Redirect ke halaman utama
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Barang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background:#f4f6f9;
        }
        .card {
            border-radius:15px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h4 class="mb-4">✏️ Edit Barang</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input 
                        type="text" 
                        name="nama" 
                        class="form-control" 
                        value="<?= $d['nama_barang'] ?>" 
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select">

                        <?php
                        // Ambil semua kategori
                        $kat = $pdo->query("SELECT * FROM kategori")->fetchAll();

                        foreach ($kat as $k) {
                            // Tandai kategori yang sedang dipakai
                            $selected = ($d['id_kategori'] == $k['id_kategori']) ? 'selected' : '';

                            echo "<option value='{$k['id_kategori']}' $selected>
                                    {$k['nama_kategori']}
                                  </option>";
                        }
                        ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stok" class="form-control" value="<?= $d['stok'] ?>" required></input>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kondisi</label>
                    <select name="kondisi" class="form-select">
                        <option value="Baik" <?= $d['kondisi']=='Baik'?'selected':'' ?>>Baik</option>
                        <option value="Rusak" <?= $d['kondisi']=='Rusak'?'selected':'' ?>>Rusak</option>
                    </select>
                </div>
    
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">← Kembali</a>
                    <button class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>