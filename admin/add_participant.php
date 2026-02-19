<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$parent_booking_id = intval($input['parent_booking_id']);
$patient_id = intval($input['patient_id']);
$services = $input['services']; // array of service IDs

// Validasi
if (!$parent_booking_id || !$patient_id || empty($services)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Cek apakah booking parent masih pending
$sql_check = "SELECT status, nomor_antrian, tanggal_booking, waktu_booking, service_type FROM bookings WHERE id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, 'i', $parent_booking_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$parent_data = mysqli_fetch_assoc($result_check);

if (!$parent_data) {
    echo json_encode(['success' => false, 'message' => 'Booking tidak ditemukan']);
    exit;
}

if ($parent_data['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Tidak dapat menambah peserta karena status booking sudah ' . $parent_data['status']]);
    exit;
}

// Buat booking baru untuk peserta
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

// Generate nomor antrian untuk peserta baru (parent_nomor + suffix)
$nomor_antrian = $parent_data['nomor_antrian'] . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

$stmt_insert = mysqli_prepare($conn, $sql_insert);
mysqli_stmt_bind_param($stmt_insert, 'iissss', 
    $parent_booking_id,
    $patient_id,
    $nomor_antrian,
    $parent_data['tanggal_booking'],
    $parent_data['waktu_booking'],
    $parent_data['service_type']
);

if (mysqli_stmt_execute($stmt_insert)) {
    $new_booking_id = mysqli_insert_id($conn);
    
    // Insert services
    $sql_service = "INSERT INTO booking_services (booking_id, patient_id, nama_layanan) VALUES (?, ?, ?)";
    $stmt_service = mysqli_prepare($conn, $sql_service);
    
    $success_count = 0;
    foreach ($services as $service_id) {
        // Ambil nama layanan
        $sql_nama = "SELECT nama_layanan FROM products_pelayanan WHERE id = ?";
        $stmt_nama = mysqli_prepare($conn, $sql_nama);
        mysqli_stmt_bind_param($stmt_nama, 'i', $service_id);
        mysqli_stmt_execute($stmt_nama);
        $result_nama = mysqli_stmt_get_result($stmt_nama);
        $service = mysqli_fetch_assoc($result_nama);
        
        if ($service) {
            mysqli_stmt_bind_param($stmt_service, 'iis', $new_booking_id, $patient_id, $service['nama_layanan']);
            if (mysqli_stmt_execute($stmt_service)) {
                $success_count++;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Peserta berhasil ditambahkan dengan ' . $success_count . ' layanan',
        'booking_id' => $new_booking_id,
        'nomor_antrian' => $nomor_antrian
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menambahkan peserta: ' . mysqli_error($conn)
    ]);
}
?>