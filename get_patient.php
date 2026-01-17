<?php
include "config.php";

header('Content-Type: application/json');

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patient_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID pasien tidak valid'
    ]);
    exit;
}

// Get patient data
$query = "SELECT * FROM patients WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $patient_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Pasien tidak ditemukan'
    ]);
    exit;
}

$patient = mysqli_fetch_assoc($result);

// Get emails
$query_emails = "SELECT email FROM patient_emails WHERE patient_id = ? ORDER BY is_primary DESC";
$stmt_emails = mysqli_prepare($conn, $query_emails);
mysqli_stmt_bind_param($stmt_emails, 'i', $patient_id);
mysqli_stmt_execute($stmt_emails);
$result_emails = mysqli_stmt_get_result($stmt_emails);

$emails = [];
while ($row = mysqli_fetch_assoc($result_emails)) {
    $emails[] = $row['email'];
}

// Get phones
$query_phones = "SELECT phone FROM patient_phones WHERE patient_id = ? ORDER BY is_primary DESC";
$stmt_phones = mysqli_prepare($conn, $query_phones);
mysqli_stmt_bind_param($stmt_phones, 'i', $patient_id);
mysqli_stmt_execute($stmt_phones);
$result_phones = mysqli_stmt_get_result($stmt_phones);

$phones = [];
while ($row = mysqli_fetch_assoc($result_phones)) {
    $phones[] = $row['phone'];
}

// Get address
$query_address = "SELECT alamat, provinsi, kota FROM patient_addresses WHERE patient_id = ? AND is_primary = 1 LIMIT 1";
$stmt_address = mysqli_prepare($conn, $query_address);
mysqli_stmt_bind_param($stmt_address, 'i', $patient_id);
mysqli_stmt_execute($stmt_address);
$result_address = mysqli_stmt_get_result($stmt_address);

$address = null;
if (mysqli_num_rows($result_address) > 0) {
    $address = mysqli_fetch_assoc($result_address);
}

// Close connections
mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_emails);
mysqli_stmt_close($stmt_phones);
mysqli_stmt_close($stmt_address);
mysqli_close($conn);

// Return response
echo json_encode([
    'success' => true,
    'patient' => [
        'id' => $patient['id'],
        'no_rekam_medis' => $patient['no_rekam_medis'],
        'nama_lengkap' => $patient['nama_lengkap'],
        'nama_panggilan' => $patient['nama_panggilan'],
        'tanggal_lahir' => $patient['tanggal_lahir'],
        'usia' => $patient['usia'],
        'kategori_usia' => $patient['kategori_usia'],
        'jenis_kelamin' => $patient['jenis_kelamin'],
        'nik' => $patient['nik'],
        'paspor' => $patient['paspor'],
        'kebangsaan' => $patient['kebangsaan'],
        'pekerjaan' => $patient['pekerjaan'],
        'nama_wali' => $patient['nama_wali'],
        'riwayat_alergi' => $patient['riwayat_alergi'],
        'riwayat_penyakit' => $patient['riwayat_penyakit'],
        'riwayat_obat' => $patient['riwayat_obat'],
        'pelayanan' => $patient['pelayanan']
    ],
    'emails' => $emails,
    'phones' => $phones,
    'address' => $address
]);
?>