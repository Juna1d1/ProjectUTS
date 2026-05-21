<?php

require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO barang (nama_barang, id_kategori, stok, kondisi) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nama'],
        $_POST['kategori'],
        $_POST['stok'],
        $_POST['kondisi']
    ]);

    // Redirect ke halaman utama setelah simpan
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Barang</title>
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
        <div class="card p-4 shadow">
            <h4 class="mb-4">➕ Tambah Barang</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama barang"required></input>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select">

                        <?php
                        // Ambil data kategori dari database
                        $kat = $pdo->query("SELECT * FROM kategori")->fetchAll();

                        // Loop untuk isi dropdown
                        foreach ($kat as $k) {
                            echo "<option value='{$k['id_kategori']}'>
                                    {$k['nama_kategori']}
                                  </option>";
                        }
                        ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Stok</label>
                    <input 
                        type="number" 
                        name="stok" 
                        class="form-control" 
                        placeholder="Masukkan jumlah stok"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kondisi</label>
                    <select name="kondisi" class="form-select">
                        <option value="Baik">Baik</option>
                        <option value="Rusak">Rusak</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">
                        ← Kembali
                    </a>
                    <button class="btn btn-success">
                        Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>
</body>
</html>