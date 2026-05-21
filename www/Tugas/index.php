<?php

require 'koneksi.php';

$search   = $_GET['search'] ?? '';     // keyword pencarian
$kategori = $_GET['kategori'] ?? '';   // filter kategori

$sql = "SELECT barang.*, kategori.nama_kategori 
        FROM barang
        JOIN kategori ON barang.id_kategori = kategori.id_kategori
        WHERE nama_barang LIKE :search";

if ($kategori != '') {
    $sql .= " AND barang.id_kategori = :kategori";
}

$stmt = $pdo->prepare($sql);

$stmt->bindValue(':search', "%$search%");

if ($kategori != '') {
    $stmt->bindValue(':kategori', $kategori);
}

$stmt->execute();

$data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventaris Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background:#f4f6f9;
        }
        .card {
            border-radius:15px;
        }
        /* Highlight stok kritis */
        .low-stock {
            background:#ffe5e5 !important;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h3 class="mb-4 text-center">📦 Inventaris Lab Komputer</h3>
            <form method="GET" class="row g-2 mb-3">
                <!-- Input pencarian -->
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Cari barang..." value="<?= $search ?>"></input>
                </div>

                <div class="col-md-4">
                    <select name="kategori" class="form-select">
                        <option value="">Semua Kategori</option>

                        <?php
                        // Ambil data kategori dari database
                        $kat = $pdo->query("SELECT * FROM kategori")->fetchAll();

                        foreach ($kat as $k) {
                            // Tandai selected jika sesuai filter
                            $selected = ($kategori == $k['id_kategori']) ? 'selected' : '';
                            echo "<option value='{$k['id_kategori']}' $selected>
                                    {$k['nama_kategori']}
                                  </option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Tombol filter -->
                <div class="col-md-3">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>

            </form>

            <div class="mb-3 text-end">
                <a href="tambah.php" class="btn btn-success">
                    + Tambah Barang
                </a>
            </div>

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Kondisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                <?php $no = 1;foreach($data as $d): ?>

                <tr class="<?= ($d['stok'] < 5) ? 'low-stock' : '' ?>">
                    <td><?= $no++ ?></td>
                    <td><?= $d['nama_barang'] ?></td>
                    <td><?= $d['nama_kategori'] ?></td>
                    <td>
                        <?php if($d['stok'] < 5): ?>
                            <span class="badge bg-danger">
                                <?= $d['stok'] ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success">
                                <?= $d['stok'] ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge 
                            <?= $d['kondisi']=='Baik' ? 'bg-primary':'bg-secondary' ?>">
                            <?= $d['kondisi'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $d['id_barang'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="hapus.php?id=<?= $d['id_barang'] ?>" class="btn btn-danger btn-sm"onclick="return confirm('Hapus data?')">Hapus</a>
                    </td>
                </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

        </div>
    </div>

</body>
</html>