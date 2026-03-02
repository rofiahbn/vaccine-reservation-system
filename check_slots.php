<?php
header('Content-Type: application/json');
include "config.php";

date_default_timezone_set('Asia/Jakarta');

// Ambil tanggal dari parameter
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    echo json_encode(['success'=>false,'message'=>'Format tanggal salah']);
    exit;
}

// Parse tanggal
$date = new DateTime($tanggal);
$hari_week = $date->format('N'); // 1=Senin, 7=Minggu
// Convert ke format database (1=Minggu, 2=Senin, ..., 7=Sabtu)
$hari_week_db = ($hari_week == 7) ? 1 : $hari_week + 1;

// =====================================
// 1. CEK APAKAH HARI LIBUR (RANGE TANGGAL)
// =====================================
$query_libur = "SELECT keterangan FROM jadwal_libur WHERE ? BETWEEN tanggal_mulai AND tanggal_selesai LIMIT 1";
$stmt = mysqli_prepare($conn, $query_libur);
mysqli_stmt_bind_param($stmt, 's', $tanggal);
mysqli_stmt_execute($stmt);
$result_libur = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result_libur) > 0) {
    $libur = mysqli_fetch_assoc($result_libur);
    echo json_encode([
        'success' => true,
        'is_holiday' => true,
        'holiday_name' => $libur['keterangan'],
        'booked' => []
    ]);
    exit;
}

// =====================================
// 2. CEK JADWAL KHUSUS (RANGE TANGGAL)
// =====================================
$query_khusus = "SELECT jam_buka, jam_tutup, status FROM jadwal_khusus WHERE ? BETWEEN tanggal_mulai AND tanggal_selesai LIMIT 1";
$stmt = mysqli_prepare($conn, $query_khusus);
mysqli_stmt_bind_param($stmt, 's', $tanggal);
mysqli_stmt_execute($stmt);
$result_khusus = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result_khusus) > 0) {
    $khusus = mysqli_fetch_assoc($result_khusus);
    
    if ($khusus['status'] === 'tutup') {
        echo json_encode([
            'success' => true,
            'is_closed' => true,
            'booked' => []
        ]);
        exit;
    }
    
    $jam_buka = $khusus['jam_buka'];
    $jam_tutup = $khusus['jam_tutup'];
    
} else {
    // =====================================
    // 3. CEK JADWAL KLINIK UNTUK HARI INI
    // =====================================
    $query_jadwal = "SELECT jam_buka, jam_tutup FROM jadwal_klinik WHERE hari_week = ? AND status = 'buka' LIMIT 1";
    $stmt = mysqli_prepare($conn, $query_jadwal);
    mysqli_stmt_bind_param($stmt, 'i', $hari_week_db);
    mysqli_stmt_execute($stmt);
    $result_jadwal = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result_jadwal) == 0) {
        // Klinik tutup di hari ini
        echo json_encode([
            'success' => true,
            'is_closed' => true,
            'booked' => []
        ]);
        exit;
    }

    $jadwal = mysqli_fetch_assoc($result_jadwal);
    $jam_buka = $jadwal['jam_buka'];
    $jam_tutup = $jadwal['jam_tutup'];
}

// =====================================
// 4. AMBIL SEMUA BOOKING YANG SUDAH ADA
// =====================================
$query_booking = "
SELECT 
    waktu_booking,
    service_type,
    COUNT(*) as total
FROM bookings 
WHERE tanggal_booking = ?
AND status IN ('pending','confirmed')
AND parent_id IS NULL
GROUP BY waktu_booking, service_type
";

$stmt = mysqli_prepare($conn, $query_booking);
mysqli_stmt_bind_param($stmt, 's', $tanggal);
mysqli_stmt_execute($stmt);
$result_booking = mysqli_stmt_get_result($stmt);

if (!$result_booking) {
   echo json_encode(['success'=>false,'message'=>'Database error']);
   exit;
}

$max_slot = 5;
$booked_slots = [];

while ($row = mysqli_fetch_assoc($result_booking)) {
    $waktu = substr($row['waktu_booking'], 0, 5);

    if ($row['service_type'] === 'In Clinic') {
        if ($row['total'] >= 1) {
            $booked_slots[] = $waktu;
        }
    }
}

// =====================================
// 5. GENERATE ALL AVAILABLE SLOTS
// =====================================
list($buka_hour, $buka_min) = explode(':', $jam_buka);
list($tutup_hour, $tutup_min) = explode(':', $jam_tutup);

$start_time = intval($buka_hour) * 60 + intval($buka_min);
$end_time = intval($tutup_hour) * 60 + intval($tutup_min);

$interval = 15; // 15 menit per slot

$all_slots = [];
for ($time = $start_time; $time < $end_time; $time += $interval) {
    $hour = floor($time / 60);
    $minute = $time % 60;
    
    $slot_label = sprintf('%02d:%02d', $hour, $minute);
    $all_slots[] = $slot_label;
}

// =====================================
// 6. RETURN RESPONSE
// =====================================
echo json_encode([
    'success' => true,
    'is_holiday' => false,
    'is_closed' => false,
    'jam_buka' => substr($jam_buka, 0, 5),
    'jam_tutup' => substr($jam_tutup, 0, 5),
    'all_slots' => $all_slots,
    'booked' => $booked_slots
]);
?>