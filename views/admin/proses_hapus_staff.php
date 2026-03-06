<?php
include "config/database.php";
include "content/auth.php";

header('Content-Type: application/json');

// ===============================
// Cek privilege manage_staff
// ===============================
if (!hasPrivilege('manage_staff')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak! Anda tidak punya izin.'
    ]);
    exit;
}

// ===============================
// Ambil ID dan validasi
// ===============================
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'ID tidak valid.'
    ]);
    exit;
}

// ===============================
// Cegah hapus diri sendiri
// ===============================
$current_user_id = intval($_SESSION['id']);

if ($id === $current_user_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Anda tidak bisa menghapus akun anda sendiri!'
    ]);
    exit;
}

// ===============================
// Pastikan data ada
// ===============================
$cek = mysqli_query($conn, "SELECT id FROM staff WHERE id = $id");
if (mysqli_num_rows($cek) == 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data staff tidak ditemukan.'
    ]);
    exit;
}

// ===============================
// Proses hapus
// ===============================
$query = mysqli_query($conn, "DELETE FROM staff WHERE id = $id");

if ($query) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Staff berhasil dihapus.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menghapus data di database.'
    ]);
}