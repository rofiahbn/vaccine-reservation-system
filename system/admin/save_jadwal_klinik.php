<?php 
include "config.php";


/*
=========================================
CEK DATA ADA
=========================================
*/

if(!isset($_POST['jadwal'])){
    $_SESSION['error'] = "Data jadwal klinik tidak ditemukan";
    header("Location: calendar_setting.php");
    exit;
}


/*
=========================================
VALIDASI SEMUA INPUT DULU
=========================================
*/

foreach($_POST['jadwal'] as $row){

    $hari_week = $row['hari_week'] ?? '';
    $jam_buka  = $row['jam_buka'] ?? '';
    $jam_tutup = $row['jam_tutup'] ?? '';

    // Validasi hari
    if(empty($hari_week)){
        $_SESSION['error'] = "Pilih minimal 1 hari untuk setiap jadwal klinik";
        header("Location: calendar_setting.php");
        exit;
    }

    // Validasi jam kosong
    if(!$jam_buka || !$jam_tutup){
        $_SESSION['error'] = "Jam buka dan jam tutup wajib diisi";
        header("Location: calendar_setting.php");
        exit;
    }

    // Validasi logika jam
    if($jam_buka >= $jam_tutup){
        $_SESSION['error'] = "Jam buka harus lebih kecil dari jam tutup";
        header("Location: calendar_setting.php");
        exit;
    }
}


/*
=========================================
DELETE DATA LAMA SETELAH VALIDASI LOLOS
=========================================
*/

mysqli_query($conn, "DELETE FROM jadwal_klinik");


/*
=========================================
INSERT DATA BARU
=========================================
*/

foreach($_POST['jadwal'] as $row){

    $days = explode(',', $row['hari_week']);
    $jam_buka  = $row['jam_buka'];
    $jam_tutup = $row['jam_tutup'];

    foreach($days as $day){

        // Safety: pastikan angka hari valid
        if(!in_array($day, ['1','2','3','4','5','6','7'])) continue;

        $stmt = $conn->prepare("
            INSERT INTO jadwal_klinik
            (hari_week, jam_buka, jam_tutup, status)
            VALUES (?, ?, ?, 'buka')
        ");

        $stmt->bind_param(
            "iss",
            $day,
            $jam_buka,
            $jam_tutup
        );

        $stmt->execute();
    }
}


/*
=========================================
SUCCESS MESSAGE
=========================================
*/

$_SESSION['success'] = "Jadwal klinik berhasil disimpan";

header("Location: calendar_setting.php");
exit;
