<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Gudang Untad</title>
    <style>
        body {
            font-family: sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f5f5f5;
        }
        .container {
            width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        .radio-group {
            display: flex;
            flex-direction: column; 
            gap: 8px;
            margin-top: 5px;
        }
        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px; 
        }
        .btn-hitung {
            background: blue;
            color: white;
            padding: 10px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .btn-reset {
            background: red;
            color: white;
            padding: 10px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .result {
            margin-top: 15px;
            padding: 10px;
            background: #eee;
            border-left: 5px solid green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Transaksi Barang</h2>
        <form method="post">
            <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" name="nm_barang" required>
            </div>
            <div class="form-group">
                <label>Harga Satuan</label>
                <input type="number" name="hg_satuan" required>
            </div>
            <div class="form-group">
                <label>Jumlah Beli</label>
                <input type="number" name="jl_beli" required>
            </div>
            <div class="form-group">
                <label>Status Member</label>
                <div class="radio-group">
                    <label class="radio-item">
                    <input type="radio" name="rd_sm" value="Member" checked> Member
                    <input type="radio" name="rd_sm" value="Tidak Member"> Tidak Member
                    </label> 
                </div>
            </div>
            <div class="form-group">
                <button type="submit" name="save" class="btn-hitung">Hitung</button>
                <button type="reset" class="btn-reset">Reset</button>
            </div>
        </form>
        <?php
        if(isset($_POST['save'])){
            $nama = htmlspecialchars($_POST['nm_barang']);
            $harga = $_POST['hg_satuan'];
            $jumlah = $_POST['jl_beli'];
            $status = $_POST['rd_sm'];

            $total_awal = $harga * $jumlah;

            $diskon = 0;
            if($status == "Member"){
                $diskon = 0.1 * $total_awal;
            }

            $total_setelah_diskon = $total_awal - $diskon;

            $bonus = 0;
            if($total_setelah_diskon > 500000){
                $bonus = 20000;
            }
        
            $total_akhir = $total_setelah_diskon - $bonus;

            echo "<div class='result'>";
            echo "<h3>Rincian Transaksi</h3>";
            echo "<p><b>Nama Barang:</b> $nama</p>";
            echo "<p><b>Harga Satuan:</b> Rp ". number_format($harga,0,",",".") . "</p>";
            echo "<p><b>Jumlah Beli:</B> $jumlah</p>";
            echo "<p><b>Total Awal:</b> Rp " . number_format($total_awal,0,",",".") . "</p>";
            echo "<p><b>Diskon:</b> Rp " . number_format($diskon,0,",",".") . "</p>";
            echo "<p><b>Potongan Tambahan:</b> Rp " . number_format($bonus,0,",",".") . "</p>";
            echo "<p><b>Total Bayar:</b> Rp " . number_format($total_akhir,0,",",".") . "</p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>