<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

// Debug logging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Jangan tampilkan error di response
error_log("=== UPDATE DISKON REQUEST ===");
error_log("POST data: " . print_r($_POST, true));

// Ambil data
$action = $_POST['action'] ?? '';
$service_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$diskon = isset($_POST['diskon']) ? floatval($_POST['diskon']) : 0;
$tipe_diskon = isset($_POST['tipe_diskon']) ? trim($_POST['tipe_diskon']) : '';

// Validasi
if ($action !== 'update_diskon') {
    echo json_encode([
        'success' => false,
        'message' => 'Action tidak valid'
    ]);
    exit;
}

if ($service_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID layanan tidak valid'
    ]);
    exit;
}

// Validasi diskon
if ($diskon < 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Diskon tidak boleh negatif'
    ]);
    exit;
}

// Set default tipe jika kosong
if (empty($tipe_diskon) && $diskon > 0) {
    $tipe_diskon = 'nilai';
}

error_log("Service ID: $service_id");
error_log("Diskon: $diskon");
error_log("Tipe: $tipe_diskon");

// Update database
$sql = "UPDATE booking_services 
        SET diskon = ?, 
            diskon_tipe = ?,
            total = harga - ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("dsdi", $diskon, $tipe_diskon, $diskon, $service_id);

if ($stmt->execute()) {
    error_log("✅ Update berhasil untuk service_id: $service_id");
    
    // Verify update
    $verify_sql = "SELECT id, nama_layanan, harga, diskon, diskon_tipe, total 
                   FROM booking_services 
                   WHERE id = ?";
    $stmt_verify = $conn->prepare($verify_sql);
    $stmt_verify->bind_param("i", $service_id);
    $stmt_verify->execute();
    $result = $stmt_verify->get_result();
    $data = $result->fetch_assoc();
    
    error_log("Verified data: " . print_r($data, true));
    
    echo json_encode([
        'success' => true,
        'message' => 'Diskon berhasil disimpan',
        'data' => $data
    ]);
} else {
    error_log("❌ Execute failed: " . $stmt->error);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>