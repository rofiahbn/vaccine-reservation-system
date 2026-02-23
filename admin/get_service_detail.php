<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

// Validasi request
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID service tidak valid'
    ]);
    exit;
}

$service_id = intval($_GET['id']);

// Query untuk ambil 1 service by ID
$sql = "SELECT * FROM services WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $service_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $service = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'success' => true,
        'data' => $service
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Service tidak ditemukan'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>