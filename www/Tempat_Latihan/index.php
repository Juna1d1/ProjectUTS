<!-- <?php

$Nama = "Manan Gerakan Bawah Tanah";
$Alamat = "Jl.cibereum 67";
$Tgl = "Banten, 30 Febuari 1945";
$Notlp = "09886775757";

define("Contoh",8);
const va ="2.1.0";

echo "Contoh ";
echo "Nama Guweh: $Nama <br>";
echo "Alamat: $Alamat <br>";
echo "TTL: $Tgl <br>";
echo "NoTlp: $Notlp";
echo "<hr>";

//Contoh Konstanta
define("DB_HOST","localhost");
define("DB_NAME","db_ipwija_jaya");

try {
    $conn = new PDO("mysql:host=".DB_HOST.";db_name=".DB_NAME."user"."password");
    echo "Berhasil";
}catch(PDOException $e) {
    echo "GAGAL". $e->getMessage();
}

?> -->

<!-- <?php

$p_balok = 10;
$l_balok = 7;
$t_balok = 15;

$volume = $p_balok * $l_balok * $t_balok;

$teks1 = "Belajar Menghitung";
$teks2 = "Volume Balok";

$judul ="<b>".$teks1." ".$teks2."</b>";

echo $judul;
echo "<br>";
echo "Panjang Balok = $p_balok <br>";
echo "Lebar Balok   = $l_balok <br>";
echo "Tinggi Balok  = $t_balok <br>";
echo "Volume Balok  = $volume";

?> -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center; 
            align-items: center;     
            background: #f5f5f5;
        }
        .container {
            max-width: 600px;
            width: 100%;
            background-color: rgba(98, 98, 98, 0.2);
            padding: 20px 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-group{
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], select, textarea{
            width: 95%;
            padding: 8px;
            margin-top: 8px;
        }
        .btn-simpan{
            background: rgb(20, 20, 201);
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-reset{
            background: rgb(227, 11, 25);
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .result{
            margin-top: 20px;
            padding: 15px;
            background: #eee;
            border-left: 5px solid purple;
        }
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
                <input type="radio" name="rd_jk" value="Laki-laki" checked> Laki-laki
                <input type="radio" name="rd_jk" value="Perempuan"> Perempuan
            </div>

            <div class="form-group">
                <label>Pendidikan Terakhir</label>
                <select name="Pendidikan">
                    <option value="SMA">SMA/SMK</option>
                    <option value="DIPLOMA">DIPLOMA (D3)</option>
                    <option value="SARJANA">SARJANA (S1)</option>
                    <option value="MAGISTER">MAGISTER (S2)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Hobi (Boleh lebih dari 1)</label>
                <input type="checkbox" name="Hobi[]" value="Coding"> Coding
                <input type="checkbox" name="Hobi[]" value="Gamer"> Gamer
                <input type="checkbox" name="Hobi[]" value="Membaca"> Membaca
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="Alamat" rows="3"></textarea>
            </div>

            <div class="form-group">
                <button type="submit" name="save" class="btn-simpan">Submit</button>
                <button type="reset" class="btn-reset">Reset</button>
            </div>
        </form>

        <?php
        if(isset($_POST['save'])){
            $nama = htmlspecialchars($_POST['nama']);
            $umur = htmlspecialchars($_POST['umur']);
            $jk = $_POST['rd_jk'];
            $pendidikan = $_POST['Pendidikan'];
            $hobi = isset($_POST['Hobi']) ? implode(", ", $_POST['Hobi']) : "Tidak ada hobi";
            $alamat = htmlspecialchars($_POST['Alamat']);

            echo "<div class='result'>";
            echo "<h3>Hasil Input</h3>";
            echo "<p><b>Nama:</b> $nama</p>";
            echo "<p><b>Umur:</b> $umur</p>";
            echo "<p><b>Jenis Kelamin:</b> $jk</p>";
            echo "<p><b>Pendidikan:</b> $pendidikan</p>";
            echo "<p><b>Hobi:</b> $hobi</p>";
            echo "<p><b>Alamat:</b> $alamat</p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>

