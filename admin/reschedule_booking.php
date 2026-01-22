<?php
session_start();
require_once '../config.php';

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
    
    $newTimeFormatted = $newTime . ':00';
    
    // Cek apakah slot tersedia
    $checkQuery = "SELECT COUNT(*) as count 
                   FROM bookings 
                   WHERE tanggal_booking = ? 
                   AND waktu_booking = ? 
                   AND status IN ('confirmed', 'scheduled')
                   AND id != ?";
    
    $stmt = $conn->prepare($checkQuery);
    
    if (!$stmt) {
        throw new Exception('Query error: ' . $conn->error);
    }
    
    $stmt->bind_param("ssi", $newDate, $newTimeFormatted, $bookingId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        throw new Exception('Slot sudah terisi! Pilih waktu lain.');
    }
    
    // Update booking
    $updateQuery = "UPDATE bookings 
                    SET tanggal_booking = ?,
                        waktu_booking = ?,
                        updated_at = NOW()
                    WHERE id = ?";
    
    $stmt = $conn->prepare($updateQuery);
    
    if (!$stmt) {
        throw new Exception('Query error: ' . $conn->error);
    }
    
    $stmt->bind_param("ssi", $newDate, $newTimeFormatted, $bookingId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Tidak ada perubahan data atau booking tidak ditemukan!');
    }
    
    $stmt->close();
    
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

// Tutup koneksi jika diperlukan
if (isset($conn)) {
    $conn->close();
}
?>