<?php
// Pastikan folder ini sudah kamu buat manual via FTP/File Manager
// Jalur absolut dari file ini ke folder assets
$targetDir = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploads/";

// Jika folder belum ada, buat otomatis
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

reset($_FILES);
$temp = current($_FILES);

if (is_uploaded_file($temp['tmp_name'])) {
    $fileExt = strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION));
    $newFileName = time() . "_" . rand(100, 999) . "." . $fileExt;
    $targetFile = $targetDir . $newFileName;

    if (move_uploaded_file($temp['tmp_name'], $targetFile)) {
        // Balikan URL yang bisa diakses publik untuk ditampilkan di editor
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        echo json_encode(['location' => $protocol . $host . "/assets/images/uploads/" . $newFileName]);
    } else {
        header("HTTP/1.1 500 Upload Gagal. Cek Izin Folder.");
        echo json_encode(['error' => 'Gagal memindahkan file ke folder tujuan']);
    }
} else {
    header("HTTP/1.1 500 Gagal Upload.");
    echo json_encode(['error' => 'File tidak terdeteksi']);
}