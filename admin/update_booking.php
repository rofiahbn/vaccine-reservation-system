<?php
session_start();
include "../config.php";

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// Ambil data dari form
$booking_id      = intval($_POST['booking_id']);
$patient_id      = intval($_POST['patient_id']);

$nama_lengkap    = $_POST['nama_lengkap'];
$nama_panggilan  = $_POST['nama_panggilan'];
$tanggal_lahir   = $_POST['tanggal_lahir'];
$jenis_kelamin   = $_POST['jenis_kelamin'];
$nik             = $_POST['nik'];
$paspor          = $_POST['paspor'];
$kebangsaan      = $_POST['kebangsaan'];
$pekerjaan       = $_POST['pekerjaan'];

$email           = $_POST['email'];
$phone           = $_POST['phone'];
$alamat          = $_POST['alamat'];
$provinsi        = $_POST['provinsi'];
$kota            = $_POST['kota'];

$tanggal_booking = $_POST['tanggal_booking'];
$waktu_booking   = $_POST['waktu_booking'];
$service_type    = $_POST['service_type'];
$status          = $_POST['status'];

$riwayat_alergi   = $_POST['riwayat_alergi'] ?? '';
$riwayat_penyakit = $_POST['riwayat_penyakit'] ?? '';
$riwayat_obat     = $_POST['riwayat_obat'] ?? '';

// ============================
// UPDATE TABLE patients
// ============================
$stmt = $conn->prepare("
    UPDATE patients SET
        nama_lengkap = ?, 
        nama_panggilan = ?, 
        tanggal_lahir = ?, 
        jenis_kelamin = ?, 
        nik = ?, 
        paspor = ?, 
        kebangsaan = ?, 
        pekerjaan = ?,
        riwayat_alergi = ?, 
        riwayat_penyakit = ?, 
        riwayat_obat = ?
    WHERE id = ?
");
$stmt->bind_param(
    "sssssssssssi",
    $nama_lengkap,
    $nama_panggilan,
    $tanggal_lahir,
    $jenis_kelamin,
    $nik,
    $paspor,
    $kebangsaan,
    $pekerjaan,
    $riwayat_alergi,
    $riwayat_penyakit,
    $riwayat_obat,
    $patient_id
);
$stmt->execute();
$stmt->close();

// ============================
// UPDATE patient_emails
// ============================
$stmt = $conn->prepare("
    UPDATE patient_emails SET email = ? WHERE patient_id = ? AND is_primary = 1
");
$stmt->bind_param("si", $email, $patient_id);
$stmt->execute();
$stmt->close();

// ============================
// UPDATE patient_phones
// ============================
$stmt = $conn->prepare("
    UPDATE patient_phones SET phone = ? WHERE patient_id = ? AND is_primary = 1
");
$stmt->bind_param("si", $phone, $patient_id);
$stmt->execute();
$stmt->close();

// ============================
// UPDATE patient_addresses
// ============================
$stmt = $conn->prepare("
    UPDATE patient_addresses SET 
        alamat = ?, 
        provinsi = ?, 
        kota = ? 
    WHERE patient_id = ? AND is_primary = 1
");
$stmt->bind_param("sssi", $alamat, $provinsi, $kota, $patient_id);
$stmt->execute();
$stmt->close();

// ============================
// UPDATE bookings
// ============================
$stmt = $conn->prepare("
    UPDATE bookings SET 
        tanggal_booking = ?, 
        waktu_booking = ?, 
        service_type = ?, 
        status = ?
    WHERE id = ?
");
$stmt->bind_param(
    "ssssi",
    $tanggal_booking,
    $waktu_booking,
    $service_type,
    $status,
    $booking_id
);
$stmt->execute();
$stmt->close();


// Redirect kembali ke detail booking
header("Location: booking_detail.php?id=$booking_id");
exit;
?>
