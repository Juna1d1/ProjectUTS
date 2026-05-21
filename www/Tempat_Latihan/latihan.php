<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: "Times New Roman", serif;
            background-color: #e6e6e6;
            margin: 0;
            padding: 0;
        }
    
        .container {
            width: 420px;
            margin: 80px auto;
            background: #f9f9f9;
            border: 2px solid #999;
            padding: 20px 30px;
            box-shadow: 3px 3px 5px rgba(0,0,0,0.2);
        }
    
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }
    
        .form-group label {
            width: 150px;
        }
    
        .form-group input[type="text"],
        .form-group select {
            flex: 1;
            padding: 5px;
            border: 1px solid #aaa;
        }
    
        .form-group input[type="radio"] {
            margin-left: 5px;
            margin-right: 3px;
        }
    
        .btn-simpan, .btn-reset {
            padding: 5px 12px;
            margin-top: center;
            border: 1px solid #888;
            background-color: #ddd;
            cursor: pointer;
        }
    
        .btn-simpan:hover, .btn-reset:hover {
            background-color: #ccc;
        }
    
        .result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #999;
            background-color: #fff;
        }
    
        table {
            width: 100%;
            margin-top: 10px;
        }
    
        td {
            padding: 3px;
        }
    
        a {
            text-decoration: none;
            align-items: center;
            color: blue;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><b>KONSER AMAL INDAHNYA BERBAGI</b></h2>
        <form method="post">
            <div class="form-group">
                <label>Nama Pemesanan</label>
                <input type="text" name="nama" required>
            </div>
            <div class="form-group">
                <label for="kode">Kode Studio</label>
                <select name="kode" id="kode">
                    <option value="">--Pilih Kode Studio--</option>
                    <option value="studio_1">studio_1</option>
                    <option value="studio_2">studio_2</option>
                </select>
            </div>
            <div class="form-group">
                <label>Jenis Kelas</label>
                <input type="radio" name="rd_jk" value="VIP" checked> VIP
                <input type="radio" name="rd_jk" value="Festival"> Festival
            </div>
            <div class="form-group">
                <label>Jumlah Tiket</label>
                <input type="text" name="jumlah" required>
            </div>
            <div>
                <button type="submit" name="tampil" class="btn-simpan">Tampil</button>
                <button type="reset" name="batal" class="btn-reset">Batal</button>
            </div>
        </form>
        <?php
        $host = "db";
        $user = "user_php";
        $password = "password_php";
        $db = "db_latihan";

        $conn = mysqli_connect($host, $user, $password, $db); // untuk membuka koneksi

        if (mysqli_connect_errno()) { // untuk mengambil deskripsi kesalahan pada koneksi database
            die("Koneksi Gagal!". mysqli_connect_error());
        }

        $show_modal = false;
        $pesan = "";

        if(isset($_POST["tampil"])){
            $nama = htmlspecialchars($_POST["nama"]);
            $kode = $_POST["kode"];
            $kelas = $_POST["rd_jk"];
            $jumlah = htmlspecialchars($_POST["jumlah"]);

            $query = "INSERT INTO konser (nama, kode, kelas, jumlah) 
            VALUES('$nama', '$kode', '$kelas', '$jumlah')";

            // mysqli_query untuk menjalankan query ke database
            if (mysqli_query($conn, $query)) {
                $show_modal = true;
                $pesan = "Data Berhasil Disimpan Ke Dalam Database";
            }else{
                $pesan = "Data Tidak Berhasil Disimpan Ke Dalam Database";
            }

            if($kode == "studio_1"){
                $bintangtamu = "Opick";
            }else if($kode == "studio_2"){
                $bintangtamu = "Raihan";
            }

            $harga = 0;
            if($kelas == "VIP"){
                $harga = 500000;
                $totalharga = $jumlah * $harga;
            }else if($kelas == "Festival"){
                $harga = 250000;
                $totalharga = $jumlah * $harga;
            }

            echo "<div class='result'>";
            echo "<h3>KONSER AMAL INDAHNYA BERBAGI</h3>";
            echo "=======================================";
            echo "<table>";
            echo "<tr><td>Nama Pemesanan</td><td>:</td><td>$nama</td></tr>";
            echo "<tr><td>Kode Studio</td><td>:</td><td>$kode</td></tr>";
            echo "<tr><td>Bintang Tamu</td><td>:</td><td>$bintangtamu</td></tr>";
            echo "<tr><td>Jenis Kelas</td><td>:</td><td>$kelas</td></tr>";
            echo "<tr><td>Harga</td><td>:</td><td>$harga</td></tr>";
            echo "<tr><td>Jumlah Beli</td><td>:</td><td>$jumlah</td></tr>";
            echo "</table>";
            echo "=======================================";
            echo "<table>";
            echo "<tr><td><b>Total Harga</b></td><td>:</td><td><b>$totalharga</b></td></tr>";
            echo "</table>";
            echo "<a href=''><p>Kembali Ke Awal</p></a>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>