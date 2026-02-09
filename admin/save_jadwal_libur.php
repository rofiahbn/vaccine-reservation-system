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
INSERT DATA BARU
=========================================
*/

foreach($_POST['libur'] as $row){

$mulai = $row['mulai'];
$selesai = $row['selesai'];
$ket = $row['keterangan'];
$jenis = $row['jenis'];

$current = strtotime($mulai);
$end = strtotime($selesai);

while($current <= $end){

$tanggal = date('Y-m-d',$current);

$stmt = $conn->prepare("
INSERT INTO jadwal_libur
(tanggal,keterangan,jenis)
VALUES (?,?,?)
");

$stmt->bind_param("sss",$tanggal,$ket,$jenis);
$stmt->execute();

$current = strtotime("+1 day",$current);
}

}


/*
=========================================
SUCCESS MESSAGE
=========================================
*/

$_SESSION['success'] = "Jadwal libur berhasil disimpan";

header("Location: calendar_setting.php");
exit;
