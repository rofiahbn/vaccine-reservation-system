<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Validasi
if (empty($input['nama_lengkap']) || empty($input['tanggal_lahir']) || empty($input['jenis_kelamin'])) {
    echo json_encode(['success' => false, 'message' => 'Data wajib harus diisi']);
    exit;
}

// Hitung usia
$tgl_lahir = new DateTime($input['tanggal_lahir']);
$today = new DateTime();
$usia = $today->diff($tgl_lahir)->y;

// Tentukan kategori usia
$kategori_usia = $usia < 18 ? 'Anak' : 'Dewasa';

// Generate nomor rekam medis
$tahun = date('Y');
$bulan = date('m');
$sql_rm = "SELECT COUNT(*) as total FROM patients WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?";
$stmt_rm = mysqli_prepare($conn, $sql_rm);
mysqli_stmt_bind_param($stmt_rm, 'ss', $tahun, $bulan);
mysqli_stmt_execute($stmt_rm);
$result_rm = mysqli_stmt_get_result($stmt_rm);
$row_rm = mysqli_fetch_assoc($result_rm);
$urutan = str_pad($row_rm['total'] + 1, 4, '0', STR_PAD_LEFT);
$no_rekam_medis = $tahun . $bulan . $urutan;

// Insert ke tabel patients
$sql = "INSERT INTO patients (
    no_rekam_medis, 
    nama_lengkap, 
    nama_panggilan, 
    tanggal_lahir, 
    usia, 
    kategori_usia, 
    jenis_kelamin, 
    nik, 
    paspor, 
    kebangsaan, 
    pekerjaan, 
    riwayat_alergi, 
    riwayat_penyakit, 
    riwayat_obat,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ssssisssssssss', 
    $no_rekam_medis,
    $input['nama_lengkap'],
    $input['nama_panggilan'] ?? '',
    $input['tanggal_lahir'],
    $usia,
    $kategori_usia,
    $input['jenis_kelamin'],
    $input['nik'] ?? '',
    $input['paspor'] ?? '',
    $input['kebangsaan'] ?? 'WNI',
    $input['pekerjaan'] ?? '',
    $input['riwayat_alergi'] ?? '',
    $input['riwayat_penyakit'] ?? '',
    $input['riwayat_obat'] ?? ''
);

if (mysqli_stmt_execute($stmt)) {
    $patient_id = mysqli_insert_id($conn);
    
    // Simpan email jika ada
    if (!empty($input['email'])) {
        $sql_email = "INSERT INTO patient_emails (patient_id, email) VALUES (?, ?)";
        $stmt_email = mysqli_prepare($conn, $sql_email);
        mysqli_stmt_bind_param($stmt_email, 'is', $patient_id, $input['email']);
        mysqli_stmt_execute($stmt_email);
    }
    
    // Simpan phone jika ada
    if (!empty($input['phone'])) {
        $sql_phone = "INSERT INTO patient_phones (patient_id, phone) VALUES (?, ?)";
        $stmt_phone = mysqli_prepare($conn, $sql_phone);
        mysqli_stmt_bind_param($stmt_phone, 'is', $patient_id, $input['phone']);
        mysqli_stmt_execute($stmt_phone);
    }
    
    // Simpan alamat jika ada
    if (!empty($input['alamat'])) {
        $sql_alamat = "INSERT INTO patient_addresses (patient_id, alamat, kota, provinsi, is_primary) 
                       VALUES (?, ?, ?, ?, 1)";
        $stmt_alamat = mysqli_prepare($conn, $sql_alamat);
        mysqli_stmt_bind_param($stmt_alamat, 'isss', 
            $patient_id, 
            $input['alamat'], 
            $input['kota'] ?? '', 
            $input['provinsi'] ?? ''
        );
        mysqli_stmt_execute($stmt_alamat);
    }
    
    echo json_encode([
        'success' => true,
        'patient_id' => $patient_id,
        'nama_lengkap' => $input['nama_lengkap'],
        'message' => 'Pasien berhasil disimpan'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan: ' . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
?>