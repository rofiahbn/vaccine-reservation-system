<?php
require_once '../config.php'; // Sesuaikan path

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Validasi input
    if (!isset($_POST['id']) || !isset($_POST['diskon']) || !isset($_POST['tipe_diskon'])) {
        throw new Exception('Data tidak lengkap!');
    }
    
    $id = $_POST['id'];
    $diskon = $_POST['diskon'];
    $tipe_diskon = $_POST['tipe_diskon'];
    
    // Validasi tipe data
    if (!is_numeric($id)) {
        throw new Exception('ID tidak valid!');
    }
    
    if (!is_numeric($diskon)) {
        throw new Exception('Nilai diskon tidak valid!');
    }
    
    // Update database
    $query = "UPDATE booking_services 
              SET diskon = ?, 
                  diskon_tipe = ?
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Query error: ' . $conn->error);
    }
    
    $stmt->bind_param("isi", $diskon, $tipe_diskon, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Diskon berhasil diperbarui'
            ]);
        } else {
            // Tidak ada perubahan, mungkin ID tidak ditemukan
            echo json_encode([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    } else {
        throw new Exception('Gagal mengeksekusi query: ' . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Tutup koneksi
if (isset($conn)) {
    $conn->close();
}
?>