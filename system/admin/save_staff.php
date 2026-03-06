<?php 
include "config.php";

header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$id = isset($input['id']) && !empty($input['id']) ? intval($input['id']) : null;
$nama_lengkap = trim($input['nama_lengkap']);
$gelar = isset($input['gelar']) ? trim($input['gelar']) : '';
$sip = isset($input['sip']) ? trim($input['sip']) : '';
$role = trim($input['role']);

// Validasi
if (empty($nama_lengkap) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Nama lengkap dan role harus diisi']);
    exit;
}

// Validasi role
$valid_roles = ['dokter', 'perawat', 'admin'];
if (!in_array($role, $valid_roles)) {
    echo json_encode(['success' => false, 'message' => 'Role tidak valid']);
    exit;
}

// Escape string untuk keamanan
$nama_lengkap = mysqli_real_escape_string($conn, $nama_lengkap);
$gelar = mysqli_real_escape_string($conn, $gelar);
$sip = mysqli_real_escape_string($conn, $sip);
$role = mysqli_real_escape_string($conn, $role);

if ($id) {
    // Update
    $sql = "UPDATE staff SET nama_lengkap = ?, gelar = ?, sip = ?, role = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    // Binding parameter untuk update
    mysqli_stmt_bind_param($stmt, 'ssssi', $nama_lengkap, $gelar, $sip, $role, $id);
    $message = 'Staff berhasil diperbarui';
} else {
    // Insert
    $sql = "INSERT INTO staff (nama_lengkap, gelar, sip, role, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    // Binding parameter untuk insert
    mysqli_stmt_bind_param($stmt, 'ssss', $nama_lengkap, $gelar, $sip, $role);
    $message = 'Staff baru berhasil ditambahkan';
}

if ($stmt && mysqli_stmt_execute($stmt)) {
    $new_id = $id ? $id : mysqli_insert_id($conn);
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'data' => ['id' => $new_id]
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menyimpan data: ' . mysqli_error($conn)
    ]);
}

if ($stmt) mysqli_stmt_close($stmt);
?>