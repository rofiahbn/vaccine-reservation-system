<?php 
include "config.php";

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID service tidak valid'
    ]);
    exit;
}

$service_id = intval($data['id']);

if ($service_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID service tidak valid'
    ]);
    exit;
}

try {
    // Check apakah service masih digunakan di booking_services
    $check_sql = "SELECT COUNT(*) as total FROM booking_services WHERE service_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    
    if (!$check_stmt) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($check_stmt, 'i', $service_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_data = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);
    
    if ($check_data['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Service tidak dapat dihapus karena masih terdapat ' . $check_data['total'] . ' booking yang menggunakan service ini'
        ]);
        exit;
    }
    
    // Delete service
    $delete_sql = "DELETE FROM services WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);
    
    if (!$delete_stmt) {
        throw new Exception('Prepare error: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($delete_stmt, 'i', $service_id);
    
    if (!mysqli_stmt_execute($delete_stmt)) {
        throw new Exception('Execute error: ' . mysqli_stmt_error($delete_stmt));
    }
    
    $affected_rows = mysqli_stmt_affected_rows($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    
    if ($affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Service berhasil dihapus'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Service tidak ditemukan'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>