<?php

define('DB_HOST', 'db');
define('DB_USER', 'user_php');
define('DB_PASS', 'password_php');
define('DB_NAME', 'db_majujaya');

if (!defined('BASE_URL')) {
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $doc_root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
    $self_dir = rtrim(dirname(__DIR__), '/');
    $rel      = str_replace($doc_root, '', $self_dir);

    define('BASE_URL', $scheme . '://' . $host . $rel);
}

function getDB() {
    static $conn = null;

    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

            $conn = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

        } catch (PDOException $e) {
            die(json_encode([
                'error' => 'Koneksi database gagal: ' . $e->getMessage()
            ]));
        }
    }
    return $conn;
}