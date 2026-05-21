<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: "Courier New", monospace;
            background-color: #e6e6e6;
            margin: 0;
            padding: 0;
        }
    
        .container {
            width: 600px;
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
            width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border: 1px solid #000;
            font-family: "Courier New", monospace;
        }

        .result h3 {
            text-align: center;
            margin-bottom: 10px;
        }

        .result .line {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .result table {
            width: 100%;
            border-collapse: collapse;
        }

        .result td {
            padding: 3px 0;
        }

        .result .right {
            text-align: right;
        }

        .result .center {
            text-align: center;
        }

        .result a {
            display: inline-block;
            margin-top: 10px;
            color: purple;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><b>TOKO CAT GUNA BANGUN JAYA</b></h2>
        <form method="post">
            <div class="form-group">
                <label>Nama Customer</label>
                <input type="text" name="nama" required>
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" name="alamat" required>
            </div>
            <div class="form-group">
                <label for="jenis">Jenis Cat</label>
                <select name="jenis" id="jenis">
                    <option value="">--Pilih Jenis Cat--</option>
                    <option value="CATYLAC">CATYLAC</option>
                    <option value="MOWILEX">MOWILEX</option>
                    <option value="DANAPAINT">DANAPAINT</option>
                </select>
            </div>
            <div class="form-group">
                <label>Warna Cat</label>
                <input type="radio" name="rd_jk" value="Merah" checked> Merah
                <input type="radio" name="rd_jk" value="Biru"> Biru
                <input type="radio" name="rd_jk" value="Hijau"> Hijau
            </div>
            <div class="form-group">
                <label>Jumlah Beli</label>
                <input type="text" name="jumlah" required>
            </div>
            <div>
                <button type="submit" name="hitung" class="btn-simpan">Hitung</button>
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
        
        if(isset($_POST["hitung"])){
            $nama = htmlspecialchars($_POST["nama"]);
            $alamat = htmlspecialchars($_POST["alamat"]);
            $jenis = $_POST["jenis"];
            $jk = $_POST["rd_jk"];
            $jumlah = htmlspecialchars($_POST["jumlah"]);

            $query = "INSERT INTO cat (nama, alamat, jenis, jk, jumlah) 
            VALUES('$nama', '$alamat', '$jenis', '$jk', '$jumlah')";

            // mysqli_query untuk menjalankan query ke database
            if (mysqli_query($conn, $query)) {
                $show_modal = true;
                $pesan = "Data Berhasil Disimpan Ke Dalam Database";
            }else{
                $pesan = "Data Tidak Berhasil Disimpan Ke Dalam Database";
            }

            if($jenis == "MOWILEX"){
                $harga = 20000;
            }else if($jenis == "DANAPAINT"){
                $harga = 30000;
            }else if($jenis == "CATYLAC"){
                $harga = 40000;
            }else{
                $harga = 0;
            }
            $TotalHarga = $harga * $jumlah;
        
            if($jumlah >= 5){
                $diskon = 0.05 * $TotalHarga;
            }else if($jumlah >= 10 ){
                $diskon = 0.10 * $TotalHarga;
            }else {
                $diskon = 0;
            }
            $TotalBayar = $TotalHarga - $diskon;

            echo "<div class='result'>";
            echo "<h3>TOKO CAT GUNA BANGUN JAYA</h3>";

            echo "<div>----------------------------------------------------</div>";

            echo "<table>";
            echo "<tr><td>Nama Customer</td><td>: $nama</td></tr>";
            echo "<tr><td>Alamat</td><td>: $alamat</td></tr>";
            echo "<tr><td>Jenis Cat</td><td>: $jenis</td></tr>";
            echo "<tr><td>Warna</td><td>: $jk</td></tr>";
            echo "<tr><td>Harga</td><td class='right'> Rp. ".number_format($harga)."</td></tr>";
            echo "<tr><td>Jumlah Beli</td><td>: $jumlah</td></tr>";
            echo "</table>";

            echo "<div>-------------------------------------------------(*)</div>";

            echo "<table>";
            echo "<tr><td>Total Harga</td><td class='right'>Rp. ".number_format($TotalHarga)."</td></tr>";
            echo "<tr><td>Diskon</td><td class='right'>Rp. ".number_format($diskon)."</td></tr>";
            echo "</table>";

            echo "<div>-------------------------------------------------(-)</div>";

            echo "<table>";
            echo "<tr><td><b>Total Bayar</b></td><td class='right'><b>Rp. ".number_format($TotalBayar)."</b></td></tr>";
            echo "</table>";

            echo "<div class='line'></div>";

            echo "<div class='center'><a href=''>Kembali</a></div>";

            echo "</div>";

        }
        ?>
    </div>
</body>
</html>