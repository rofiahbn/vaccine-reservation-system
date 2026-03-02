<?php
session_start();
include "../config.php";

/*
================================================
CEK DATA ADA ATAU TIDAK
================================================
*/

if(!isset($_POST['khusus'])){
    $_SESSION['error'] = "Data jadwal khusus tidak ditemukan";
    header("Location: calendar_setting.php");
    exit;
}


/*
================================================
VALIDASI SEMUA INPUT DULU
================================================
*/

foreach($_POST['khusus'] as $index => $row){

    $mulai = $row['tanggal_mulai'] ?? '';
    $selesai = $row['tanggal_selesai'] ?? '';
    $jam_buka = $row['jam_buka'] ?? '';
    $jam_tutup = $row['jam_tutup'] ?? '';
    $status = $row['status'] ?? '';

    // Validasi kosong
    if(!$mulai || !$selesai){
        $_SESSION['error'] = "Tanggal mulai & selesai wajib diisi";
        header("Location: calendar_setting.php");
        exit;
    }

    // Validasi tanggal
    if($mulai > $selesai){
        $_SESSION['error'] = "Tanggal mulai tidak boleh lebih besar dari selesai";
        header("Location: calendar_setting.php");
        exit;
    }

    // Validasi jam jika buka
    if($status === 'buka'){
        if(!$jam_buka || !$jam_tutup){
            $_SESSION['error'] = "Jam buka & tutup wajib diisi";
            header("Location: calendar_setting.php");
            exit;
        }

        if($jam_buka >= $jam_tutup){
            $_SESSION['error'] = "Jam buka harus lebih kecil dari jam tutup";
            header("Location: calendar_setting.php");
            exit;
        }
    }
}


/*
================================================
HAPUS DATA LAMA SETELAH VALIDASI LOLOS
================================================
*/

mysqli_query($conn,"DELETE FROM jadwal_khusus");


/*
================================================
INSERT DATA BARU
================================================
*/

foreach($_POST['khusus'] as $row){

    $mulai = $row['tanggal_mulai'];
    $selesai = $row['tanggal_selesai'];
    $jam_buka = $row['jam_buka'];
    $jam_tutup = $row['jam_tutup'];
    $ket = $row['keterangan'] ?? '';
    $status = $row['status'];

    // INSERT LANGSUNG DENGAN RANGE TANGGAL (TIDAK PERLU LOOP PER HARI)
    $stmt = $conn->prepare("
        INSERT INTO jadwal_khusus 
        (tanggal_mulai, tanggal_selesai, jam_buka, jam_tutup, keterangan, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if ($stmt === false) {
        die("Error prepare: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssss",
        $mulai,
        $selesai,
        $jam_buka,
        $jam_tutup,
        $ket,
        $status
    );

    $stmt->execute();

    if ($stmt->error) {
        die("Error execute: " . $stmt->error);
    }

    $stmt->close();
}


/*
================================================
SUCCESS MESSAGE
================================================
*/

$_SESSION['success'] = "Jadwal khusus berhasil disimpan";

header("Location: calendar_setting.php");
exit;
?>