<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $bookingId = $_POST['booking_id'];
    $newDate = $_POST['new_date'];
    $newTime = $_POST['new_time'];
    
    // Validasi input
    if (empty($bookingId) || empty($newDate) || empty($newTime)) {
        throw new Exception('Data tidak lengkap!');
    }
    
    // Cek apakah slot tersedia
    $checkQuery = "SELECT COUNT(*) as count 
                   FROM bookings 
                   WHERE appointment_date = :date 
                   AND appointment_time = :time 
                   AND status IN ('confirmed', 'scheduled')
                   AND id != :booking_id";
    
    $stmt = $pdo->prepare($checkQuery);
    $stmt->execute([
        ':date' => $newDate,
        ':time' => $newTime . ':00',
        ':booking_id' => $bookingId
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] > 0) {
        throw new Exception('Slot sudah terisi! Pilih waktu lain.');
    }
    
    // Update booking
    $updateQuery = "UPDATE bookings 
                    SET appointment_date = :date,
                        appointment_time = :time,
                        updated_at = NOW()
                    WHERE id = :booking_id";
    
    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([
        ':date' => $newDate,
        ':time' => $newTime . ':00',
        ':booking_id' => $bookingId
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Jadwal berhasil diubah!'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>