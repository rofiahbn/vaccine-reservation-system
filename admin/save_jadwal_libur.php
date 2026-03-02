<?php
session_start();
include "../config.php";


/*
=========================================
CEK DATA ADA
=========================================
*/

if(!isset($_POST['libur'])){
    $_SESSION['error'] = "Data libur tidak ditemukan";
    header("Location: calendar_setting.php");
    exit;
}


/*
=========================================
VALIDASI SEMUA INPUT DULU
=========================================
*/

foreach($_POST['libur'] as $row){

    $mulai = $row['mulai'] ?? '';
    $selesai = $row['selesai'] ?? '';
    $ket = $row['keterangan'] ?? '';
    $jenis = $row['jenis'] ?? '';

    // Validasi tanggal kosong
    if(!$mulai || !$selesai){
        $_SESSION['error'] = "Tanggal libur wajib diisi";
        header("Location: calendar_setting.php");
        exit;
    }

    // Validasi tanggal
    if($mulai > $selesai){
        $_SESSION['error'] = "Tanggal mulai libur tidak boleh lebih besar dari selesai";
        header("Location: calendar_setting.php");
        exit;
    }

    // Validasi keterangan
    if(empty(trim($ket))){
        $_SESSION['error'] = "Keterangan libur wajib diisi";
        header("Location: calendar_setting.php");
        exit;
    }

}


/*
=========================================
DELETE DATA LAMA SETELAH VALIDASI LOLOS
=========================================
*/

mysqli_query($conn,"DELETE FROM jadwal_libur");


/*
=========================================
INSERT DATA BARU (LANGSUNG RANGE)
=========================================
*/

foreach($_POST['libur'] as $row){

    $mulai = $row['mulai'];
    $selesai = $row['selesai'];
    $ket = $row['keterangan'];
    $jenis = $row['jenis'];

    // INSERT LANGSUNG DENGAN RANGE TANGGAL (TIDAK PERLU LOOP PER HARI)
    $stmt = $conn->prepare("
        INSERT INTO jadwal_libur
        (tanggal_mulai, tanggal_selesai, keterangan, jenis)
        VALUES (?, ?, ?, ?)
    ");

    if ($stmt === false) {
        die("Error prepare: " . $conn->error);
    }

    $stmt->bind_param("ssss", $mulai, $selesai, $ket, $jenis);
    $stmt->execute();

    if ($stmt->error) {
        die("Error execute: " . $stmt->error);
    }

    $stmt->close();
}


/*
=========================================
SUCCESS MESSAGE
=========================================
*/

$_SESSION['success'] = "Jadwal libur berhasil disimpan";

header("Location: calendar_setting.php");
exit;
?>