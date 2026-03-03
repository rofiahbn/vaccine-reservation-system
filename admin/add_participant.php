<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

// Ambil JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$parent_booking_id = isset($input['parent_booking_id']) ? intval($input['parent_booking_id']) : 0;
$patient_id = isset($input['patient_id']) ? intval($input['patient_id']) : 0;
$services = isset($input['services']) ? $input['services'] : [];

// Validasi
if (!$parent_booking_id || !$patient_id || empty($services)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// ================= CEK BOOKING PARENT =================
$sql_check = "SELECT status, nomor_antrian, tanggal_booking, waktu_booking, service_type 
              FROM bookings WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $parent_booking_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$parent_data = $result_check->fetch_assoc();

if (!$parent_data) {
    echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan']);
    exit;
}

if ($parent_data['status'] !== 'pending') {
    echo json_encode([
        'success' => false,
        'message' => 'Tidak dapat menambah peserta karena status booking sudah ' . $parent_data['status']
    ]);
    exit;
}

// ================= INSERT BOOKING BARU =================
$nomor_antrian = $parent_data['nomor_antrian'] . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

$sql_insert = "INSERT INTO bookings (
    parent_id,
    patient_id,
    nomor_antrian,
    tanggal_booking,
    waktu_booking,
    service_type,
    status,
    payment_status,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'unpaid', NOW())";

$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param(
    "iissss",
    $parent_booking_id,
    $patient_id,
    $nomor_antrian,
    $parent_data['tanggal_booking'],
    $parent_data['waktu_booking'],
    $parent_data['service_type']
);

if (!$stmt_insert->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan peserta: ' . $stmt_insert->error
    ]);
    exit;
}

$new_booking_id = $conn->insert_id;

// ================= INSERT LAYANAN KE BOOKING_SERVICES =================
$sql_service = "INSERT INTO booking_services (
    booking_id,
    patient_id,
    service_id,
    nama_layanan,
    harga,
    total,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, NOW())";

$stmt_service = $conn->prepare($sql_service);

if (!$stmt_service) {
    echo json_encode(['success' => false, 'message' => 'Prepare service gagal: ' . $conn->error]);
    exit;
}

$sql_get_service = "SELECT id, nama_layanan, harga FROM services WHERE id = ?";
$stmt_get = $conn->prepare($sql_get_service);

if (!$stmt_get) {
    echo json_encode(['success' => false, 'message' => 'Prepare get service gagal: ' . $conn->error]);
    exit;
}

$success_count = 0;

foreach ($services as $service_id) {
    $service_id = intval($service_id);
    
    // Ambil data service
    $stmt_get->bind_param("i", $service_id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $service = $result->fetch_assoc();
    
    if ($service) {
        // Insert ke booking_services
        $stmt_service->bind_param(
            "iiissi",
            $new_booking_id,
            $patient_id,
            $service['id'],
            $service['nama_layanan'],
            $service['harga'],
            $service['harga'] // total = harga (karena quantity=1)
        );
        
        if ($stmt_service->execute()) {
            $success_count++;
        } else {
            error_log("Error insert service: " . $stmt_service->error);
        }
    }
}

// ================= RESPONSE =================
echo json_encode([
    'success' => true,
    'message' => 'Peserta berhasil ditambahkan dengan ' . $success_count . ' layanan',
    'booking_id' => $new_booking_id,
    'nomor_antrian' => $nomor_antrian
]);
?>