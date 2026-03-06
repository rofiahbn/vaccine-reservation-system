<?php
session_start();

// Cek apakah ada index yang mau dihapus
if (!isset($_GET['index']) || !isset($_SESSION['participants'])) {
    header('Location: booking_confirmation.php');
    exit;
}

$index = intval($_GET['index']);

// Hapus peserta berdasarkan index
if (isset($_SESSION['participants'][$index])) {
    unset($_SESSION['participants'][$index]);
    
    // Re-index array agar tidak ada gap
    $_SESSION['participants'] = array_values($_SESSION['participants']);
    
    $_SESSION['success_message'] = 'Peserta berhasil dihapus!';
} else {
    $_SESSION['error_message'] = 'Peserta tidak ditemukan!';
}

// Redirect kembali ke konfirmasi
// Kalau semua peserta sudah dihapus, redirect ke order.php
if (empty($_SESSION['participants'])) {
    unset($_SESSION['booking_active']);
    header('Location: order.php');
} else {
    header('Location: booking_confirmation.php');
}
exit;
?>