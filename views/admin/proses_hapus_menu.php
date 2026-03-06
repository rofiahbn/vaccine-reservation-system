<?php 

include "config/database.php";  
include "content/auth.php";

header('Content-Type: application/json');

// Proteksi: Hanya Admin (1) atau Owner (4) yang boleh menghapus
if ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 4) {
    echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki otoritas!']);
    exit;
}

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if ($id != "") {
    // Jalankan query hapus menu
    $query = mysqli_query($conn, "DELETE FROM menus WHERE id = '$id'");

    if ($query) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Menu dan konten halaman berhasil dihapus secara permanen.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Gagal menghapus data: ' . mysqli_error($conn)
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID Menu tidak valid.']);
}