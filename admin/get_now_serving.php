<?php
include "../config.php";
$today = date('Y-m-d');

$sql = "
    SELECT 
        b.nomor_antrian,
        p.nama_lengkap,
        GROUP_CONCAT(bs.nama_layanan SEPARATOR '<br>') AS layanan,
        b.status
    FROM bookings b
    JOIN patients p ON b.patient_id = p.id
    LEFT JOIN booking_services bs ON bs.booking_id = b.id
    WHERE DATE(b.tanggal_booking) = ?
      AND b.status = 'confirmed'
    GROUP BY b.id
    ORDER BY b.waktu_booking ASC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $today);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if ($data) {
    echo json_encode(['success' => true] + $data);
} else {
    echo json_encode(['success' => false]);
}
