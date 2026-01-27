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
$service_master_id = $_POST['service_master_id'];
$nama_layanan      = $_POST['nama_layanan'];

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
// UPDATE / INSERT patient_emails (ANTI DUPLIKAT)
// ============================

$stmt_check = $conn->prepare("
    SELECT id FROM patient_emails 
    WHERE patient_id = ? AND is_primary = 1 
    LIMIT 1
");
$stmt_check->bind_param("i", $patient_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();

if ($res_check->num_rows > 0) {
    // SUDAH ADA → UPDATE
    $stmt = $conn->prepare("
        UPDATE patient_emails 
        SET email = ? 
        WHERE patient_id = ? AND is_primary = 1
    ");
    $stmt->bind_param("si", $email, $patient_id);
    $stmt->execute();
    $stmt->close();
} else {
    // BELUM ADA → INSERT SEKALI
    $stmt = $conn->prepare("
        INSERT INTO patient_emails (patient_id, email, is_primary) 
        VALUES (?, ?, 1)
    ");
    $stmt->bind_param("is", $patient_id, $email);
    $stmt->execute();
    $stmt->close();
}

$stmt_check->close();

// ============================
// UPDATE / INSERT patient_phones (ANTI DUPLIKAT)
// ============================

$stmt_check = $conn->prepare("
    SELECT id FROM patient_phones 
    WHERE patient_id = ? AND is_primary = 1 
    LIMIT 1
");
$stmt_check->bind_param("i", $patient_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();

if ($res_check->num_rows > 0) {
    // SUDAH ADA → UPDATE
    $stmt = $conn->prepare("
        UPDATE patient_phones 
        SET phone = ? 
        WHERE patient_id = ? AND is_primary = 1
    ");
    $stmt->bind_param("si", $phone, $patient_id);
    $stmt->execute();
    $stmt->close();
} else {
    // BELUM ADA → INSERT SEKALI
    $stmt = $conn->prepare("
        INSERT INTO patient_phones (patient_id, phone, is_primary) 
        VALUES (?, ?, 1)
    ");
    $stmt->bind_param("is", $patient_id, $phone);
    $stmt->execute();
    $stmt->close();
}

$stmt_check->close();

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

// ============================
// UPDATE booking_services
// ============================

if (isset($_POST['service_id'])) {

    $service_ids        = $_POST['service_id'];
    $service_master_id = $_POST['service_master_id'];
    $nama_layanan      = $_POST['nama_layanan'];

    for ($i = 0; $i < count($service_ids); $i++) {

        $stmt_srv = $conn->prepare("
            UPDATE booking_services SET
                service_id = ?,
                nama_layanan = ?
            WHERE id = ?
        ");

        if (!$stmt_srv) {
            die("Prepare service error: " . $conn->error);
        }

        $stmt_srv->bind_param(
            "isi",
            $service_master_id[$i],
            $nama_layanan[$i],
            $service_ids[$i]
        );

        if (!$stmt_srv->execute()) {
            die("Execute service error: " . $stmt_srv->error);
        }

        $stmt_srv->close();
    }
}

// Redirect kembali ke detail booking
header("Location: booking_detail.php?id=$booking_id");
exit;
?>
