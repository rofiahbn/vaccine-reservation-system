<?php 
include "config.php";

date_default_timezone_set('Asia/Jakarta');

$service_mode = isset($_GET['service']) ? $_GET['service'] : 'In Clinic';
$today = date('Y-m-d');

$sql = "
    SELECT 
        b.id,
        b.nomor_antrian,
        b.status
    FROM bookings b
    WHERE DATE(b.tanggal_booking) = ?
      AND b.status IN ('confirmed', 'pending')
      AND b.service_type = ?
      AND b.parent_id IS NULL
    ORDER BY 
        FIELD(b.status, 'confirmed', 'pending'),
        b.waktu_booking ASC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $today, $service_mode);
$stmt->execute();
$now_serving = $stmt->get_result()->fetch_assoc();

if (!$now_serving) {
    echo json_encode([
        'success' => false,
        'message' => 'Belum ada pasien hari ini'
    ]);
    exit;
}

$parent_id = $now_serving['id'];

// Ambil participants
$sql_participants = "
    SELECT p.nama_lengkap
    FROM bookings b
    JOIN patients p ON b.patient_id = p.id
    WHERE b.id = ? OR b.parent_id = ?
    ORDER BY CASE WHEN b.id = ? THEN 0 ELSE 1 END
";

$stmt_p = $conn->prepare($sql_participants);
$stmt_p->bind_param("iii", $parent_id, $parent_id, $parent_id);
$stmt_p->execute();

$participants = [];
$result_p = $stmt_p->get_result();
while ($row = $result_p->fetch_assoc()) {
    $participants[] = $row['nama_lengkap'];
}

// 🔥 QUERY BARU - Ambil layanan dengan tipe
$sql_services = "
    SELECT bs.nama_layanan, bs.tipe
    FROM booking_services bs
    JOIN bookings b ON bs.booking_id = b.id
    WHERE b.id = ? OR b.parent_id = ?
    ORDER BY 
        CASE bs.tipe 
            WHEN 'pelayanan' THEN 1 
            WHEN 'paket' THEN 2 
        END,
        bs.nama_layanan ASC
";

$stmt_s = $conn->prepare($sql_services);
$stmt_s->bind_param("ii", $parent_id, $parent_id);
$stmt_s->execute();

$services_layanan = [];
$services_paket = [];

$result_s = $stmt_s->get_result();
while ($row = $result_s->fetch_assoc()) {
    if ($row['tipe'] === 'pelayanan') {
        $services_layanan[] = $row['nama_layanan'];
    } else if ($row['tipe'] === 'paket') {
        $services_paket[] = $row['nama_layanan'];
    }
}

echo json_encode([
    'success' => true,
    'nomor_antrian' => $now_serving['nomor_antrian'],
    'status' => $now_serving['status'],
    'participants' => $participants,
    'services_layanan' => $services_layanan,
    'services_paket' => $services_paket
]);
?>