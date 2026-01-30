<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? intval($data['id']) : 0;
    
    if ($id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID produk tidak valid'
        ]);
        exit;
    }
    
    // Check if product exists
    $sql_check_exists = "SELECT nama_layanan FROM services WHERE id = ?";
    $stmt_exists = $conn->prepare($sql_check_exists);
    $stmt_exists->bind_param('i', $id);
    $stmt_exists->execute();
    $result_exists = $stmt_exists->get_result();
    
    if ($result_exists->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Produk tidak ditemukan'
        ]);
        exit;
    }
    
    $product_name = $result_exists->fetch_assoc()['nama_layanan'];
    
    // Check if product is being used in bookings
    $sql_check = "SELECT COUNT(*) as count FROM booking_services WHERE service_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('i', $id);
    $stmt_check->execute();
    $result = $stmt_check->get_result()->fetch_assoc();
    
    if ($result['count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Produk "'.$product_name.'" tidak dapat dihapus karena sedang digunakan dalam '.$result['count'].' booking'
        ]);
        exit;
    }
    
    // Begin transaction for safe deletion
    $conn->begin_transaction();
    
    try {
        // Delete product
        $sql = "DELETE FROM services WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Produk "'.$product_name.'" berhasil dihapus'
            ]);
        } else {
            throw new Exception('Gagal menghapus produk dari database');
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menghapus produk: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>