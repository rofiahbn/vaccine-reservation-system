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

    $current = strtotime($mulai);
    $end = strtotime($selesai);

    while($current <= $end){

        $tanggal = date('Y-m-d',$current);

        $stmt = $conn->prepare("
            INSERT INTO jadwal_khusus 
            (tanggal,tanggal_mulai,tanggal_selesai,jam_buka,jam_tutup,keterangan,status)
            VALUES (?,?,?,?,?,?,?)
        ");

        $stmt->bind_param(
            "sssssss",
            $tanggal,
            $mulai,
            $selesai,
            $jam_buka,
            $jam_tutup,
            $ket,
            $status
        );

        $stmt->execute();

        $current = strtotime("+1 day",$current);
    }
}


/*
================================================
SUCCESS MESSAGE
================================================
*/

$_SESSION['success'] = "Jadwal khusus berhasil disimpan";

header("Location: calendar_setting.php");
exit;
