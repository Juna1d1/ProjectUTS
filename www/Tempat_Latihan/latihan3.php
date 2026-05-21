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

if(isset($_POST["submit"])){
    $nama = htmlspecialchars($_POST["nama"]);
    $jk = $_POST["jk"];
    $pendidikan = htmlspecialchars($_POST["pendidikan"]);
    $alamat = htmlspecialchars($_POST["alamat"]);
    $hobi = isset($_POST["hobi"]) ? implode(", ", $_POST["hobi"]) :"Tidak ada hobi";

    $query = "INSERT INTO Users (nama, jk, pendidikan, hobi, alamat) 
    VALUES('$nama', '$jk', '$pendidikan', '$hobi', '$alamat')";

    // mysqli_query untuk menjalankan query ke database
    if (mysqli_query($conn, $query)) {
        $show_modal = true;
        $pesan = "Data Berhasil Disimpan Ke Dalam Database";
    }else{
        $pesan = "Data Tidak Berhasil Disimpan Ke Dalam Database";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form to Database</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 40px; }
        .form-card { max-width: 500px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-submit { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        
        /* Modal Style */
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 15% auto; padding: 20px; border-radius: 8px; width: 300px; text-align: center; }
        .btn-close { background: #007bff; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Formulir Pendaftaran</h2>
        <form method="post">
            
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" required>
            </div>

            <div class="form-group">
                <label>Umur</label>
                <input type="number" name="umur" required>
            </div>

            <div class="form-group">
                <label>Jenis Kelamin</label>
                <input type="radio" name="jk" value="Laki-laki" checked> Laki-laki
                <input type="radio" name="jk" value="Perempuan"> Perempuan
            </div>

            <div class="form-group">
                <label>Pendidikan Terakhir</label>
                <select name="pendidikan">
                    <option value="SMA">SMA/SMK</option>
                    <option value="DIPLOMA">DIPLOMA (D3)</option>
                    <option value="SARJANA">SARJANA (S1)</option>
                    <option value="MAGISTER">MAGISTER (S2)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Hobi (Boleh lebih dari 1)</label>
                <input type="checkbox" name="hobi[]" value="Coding"> Coding
                <input type="checkbox" name="hobi[]" value="Gamer"> Gamer
                <input type="checkbox" name="hobi[]" value="Membaca"> Membaca
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" rows="3"></textarea>
            </div>

            <div class="form-group">
                <button type="submit" name="submit" class="btn-simpan">Submit</button>
                <button type="reset" class="btn-reset">Reset</button>
            </div>
        </form>
    </div>
</body>
</html>