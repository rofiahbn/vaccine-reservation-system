<?php
include "config.php";

header('Content-Type: application/json');

$name = isset($_GET['name']) ? trim($_GET['name']) : '';
$nik = isset($_GET['nik']) ? trim($_GET['nik']) : '';

// Minimal harus ada 1 kriteria
if (empty($name) && empty($nik)) {
    echo json_encode([
        'success' => false,
        'message' => 'Minimal 1 kriteria harus diisi',
        'patients' => []
    ]);
    exit;
}

// Build query dinamis berdasarkan kriteria yang diisi
$conditions = [];
$params = [];
$types = '';

$query = "SELECT p.*, 
          (SELECT phone FROM patient_phones WHERE patient_id = p.id AND is_primary = 1 LIMIT 1) as phone,
          (SELECT email FROM patient_emails WHERE patient_id = p.id AND is_primary = 1 LIMIT 1) as email
          FROM patients p WHERE 1=1";

if (!empty($name)) {
    $query .= " AND p.nama_lengkap LIKE ?";
    $params[] = "%$name%";
    $types .= 's';
}

if (!empty($nik)) {
    // FIXED: Cari di kolom nik ATAU paspor
    $query .= " AND (p.nik LIKE ? OR p.paspor LIKE ?)";
    $params[] = "%$nik%";
    $params[] = "%$nik%";
    $types .= 'ss';
}

$query .= " ORDER BY p.created_at DESC LIMIT 10";

// Prepare statement
$stmt = mysqli_prepare($conn, $query);

if (!empty($params)) {
    // Bind parameters dinamis
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$patients = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Format tanggal lahir
    $dob_formatted = date('d/m/Y', strtotime($row['tanggal_lahir']));
    
    $patients[] = [
        'id' => $row['id'],
        'no_rekam_medis' => $row['no_rekam_medis'],
        'nama_lengkap' => $row['nama_lengkap'],
        'tanggal_lahir' => $dob_formatted,
        'usia' => $row['usia'],
        'kategori_usia' => $row['kategori_usia'],
        'jenis_kelamin' => $row['jenis_kelamin'],
        'phone' => $row['phone'],
        'email' => $row['email']
    ];
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

echo json_encode([
    'success' => true,
    'count' => count($patients),
    'patients' => $patients
]);
?>