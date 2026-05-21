<?php

require 'koneksi.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM barang WHERE id_barang=?");
$stmt->execute([$id]);

header("Location: index.php");
exit;

?>