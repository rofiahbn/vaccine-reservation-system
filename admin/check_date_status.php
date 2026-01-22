<?php
session_start();

// Include file yang diperlukan
if (file_exists("config.php")) {
    include "config.php";
} else {
    include "../config.php";
}

if (file_exists("calendar_helper.php")) {
    include "calendar_helper.php";
} else {
    include "../calendar_helper.php";
}

header('Content-Type: application/json');

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

if (empty($tanggal)) {
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
    exit;
}

// Gunakan function yang sudah ada
$status = checkDateStatus($conn, $tanggal);

echo json_encode([
    'success' => true,
    'is_holiday' => $status['is_holiday'],
    'is_closed' => $status['is_closed'],
    'is_full' => $status['is_full'],
    'holiday_name' => $status['holiday_name']
]);
?>