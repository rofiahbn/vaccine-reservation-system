<?php
include "../config.php";
date_default_timezone_set('Asia/Jakarta');

$today = date('Y-m-d');

/* ========================
   Ambil Parent Booking
======================== */
$sql = "
    SELECT id, nomor_antrian, status
    FROM bookings
    WHERE DATE(tanggal_booking) = ?
      AND status IN ('confirmed','pending')
      AND parent_id IS NULL
    ORDER BY FIELD(status,'confirmed','pending'), waktu_booking ASC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $today);
$stmt->execute();

$now = $stmt->get_result()->fetch_assoc();

if (!$now) {
    echo json_encode(['success'=>false]);
    exit;
}

$parent_id = $now['id'];

/* ========================
   Ambil Semua Participant
======================== */
$participants = [];

$sql_p = "
    SELECT p.nama_lengkap
    FROM bookings b
    JOIN patients p ON b.patient_id = p.id
    WHERE b.id = ? OR b.parent_id = ?
";

$stmt_p = $conn->prepare($sql_p);
$stmt_p->bind_param("ii", $parent_id, $parent_id);
$stmt_p->execute();

$res_p = $stmt_p->get_result();

while ($row = $res_p->fetch_assoc()) {
    $participants[] = $row['nama_lengkap'];
}

/* ========================
   Ambil Semua Layanan Parent
======================== */
$services = [];

$sql_s = "
    SELECT nama_layanan
    FROM booking_services
    WHERE booking_id = ?
";

$stmt_s = $conn->prepare($sql_s);
$stmt_s->bind_param("i", $parent_id);
$stmt_s->execute();

$res_s = $stmt_s->get_result();

while ($row = $res_s->fetch_assoc()) {
    $services[] = $row['nama_layanan'];
}

/* ========================
   OUTPUT JSON
======================== */
echo json_encode([
    'success' => true,
    'nomor_antrian' => $now['nomor_antrian'],
    'participants' => $participants,
    'services' => $services
]);
