<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$booking_id = intval($input['booking_id']);
$doctor_ids = $input['doctor_ids']; // array
$mode = isset($input['mode']) ? $input['mode'] : 'add'; // default 'add'

if (!$booking_id || empty($doctor_ids)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Cek apakah booking ada
$check = $conn->prepare("SELECT id, status FROM bookings WHERE id = ?");
$check->bind_param("i", $booking_id);
$check->execute();
$booking = $check->get_result()->fetch_assoc();

if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan']);
    exit;
}

// Mulai transaksi
$conn->begin_transaction();

try {
    
    if ($mode === 'replace') {
        // MODE REPLACE: Hapus semua dulu (untuk proses_tindakan)
        $delete = $conn->prepare("DELETE FROM booking_staff WHERE booking_id = ?");
        $delete->bind_param("i", $booking_id);
        $delete->execute();
        
        // Insert semua dokter baru
        $insert = $conn->prepare("INSERT INTO booking_staff (booking_id, staff_id) VALUES (?, ?)");
        $success_count = 0;
        
        foreach ($doctor_ids as $doctor_id) {
            $doctor_id = intval($doctor_id);
            $insert->bind_param("ii", $booking_id, $doctor_id);
            if ($insert->execute()) {
                $success_count++;
            }
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => "Tim dokter berhasil diperbarui ($success_count orang)",
            'count' => $success_count,
            'mode' => 'replace'
        ]);
        
    } else {
        // MODE ADD: Tambah tanpa hapus yang lama (untuk booking_detail)
        
        // Ambil dokter yang sudah ada
        $existing = $conn->prepare("SELECT staff_id FROM booking_staff WHERE booking_id = ?");
        $existing->bind_param("i", $booking_id);
        $existing->execute();
        $result = $existing->get_result();
        $existing_ids = [];
        while ($row = $result->fetch_assoc()) {
            $existing_ids[] = $row['staff_id'];
        }
        
        // Filter dokter yang belum ada
        $new_doctors = array_diff($doctor_ids, $existing_ids);
        
        if (empty($new_doctors)) {
            echo json_encode(['success' => false, 'message' => 'Semua dokter sudah ditambahkan sebelumnya']);
            $conn->rollback();
            exit;
        }
        
        // Insert dokter baru
        $insert = $conn->prepare("INSERT INTO booking_staff (booking_id, staff_id) VALUES (?, ?)");
        $success_count = 0;
        
        foreach ($new_doctors as $doctor_id) {
            $doctor_id = intval($doctor_id);
            $insert->bind_param("ii", $booking_id, $doctor_id);
            if ($insert->execute()) {
                $success_count++;
            }
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => "$success_count dokter berhasil ditambahkan",
            'count' => $success_count,
            'mode' => 'add'
        ]);
    }
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
}
?>