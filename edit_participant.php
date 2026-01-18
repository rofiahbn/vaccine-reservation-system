<?php
session_start();

// Cek apakah ada index yang mau diedit
if (!isset($_GET['index']) || !isset($_SESSION['participants'])) {
    header('Location: booking_confirmation.php');
    exit;
}

$index = intval($_GET['index']);

// Cek apakah peserta ada
if (!isset($_SESSION['participants'][$index])) {
    $_SESSION['error_message'] = 'Peserta tidak ditemukan!';
    header('Location: booking_confirmation.php');
    exit;
}

// Simpan index yang sedang diedit ke session
$_SESSION['editing_index'] = $index;
$_SESSION['editing_mode'] = true;

// Redirect ke add_participant.php (akan auto-fill data)
header('Location: add_participant.php');
exit;
?>