<?php
header('Content-Type: application/json');
include "config.php";

// Ambil tanggal dari parameter
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

if (empty($tanggal)) {
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid']);
    exit;
}

// Parse tanggal
$date = new DateTime($tanggal);
$hari_week = $date->format('N'); // 1=Senin, 7=Minggu
// Convert ke format database (1=Minggu, 2=Senin, ..., 7=Sabtu)
$hari_week_db = ($hari_week == 7) ? 1 : $hari_week + 1;

// 1. CEK APAKAH HARI LIBUR NASIONAL
$query_libur = "SELECT * FROM jadwal_libur WHERE tanggal = '$tanggal'";
$result_libur = mysqli_query($conn, $query_libur);

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

// 2. CEK JADWAL KLINIK UNTUK HARI INI
$query_jadwal = "SELECT * FROM jadwal_klinik WHERE hari_week = $hari_week_db AND status = 'buka'";
$result_jadwal = mysqli_query($conn, $query_jadwal);

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
$jam_buka = $jadwal['jam_buka'];  // Format: HH:MM:SS
$jam_tutup = $jadwal['jam_tutup']; // Format: HH:MM:SS

// 3. AMBIL SEMUA BOOKING YANG SUDAH ADA DI TANGGAL INI
$query_booking = "SELECT waktu_booking, service_type FROM bookings WHERE tanggal_booking = '$tanggal'AND status IN ('pending','confirmed')";
$result_booking = mysqli_query($conn, $query_booking);

$booked_slots = [];
$booked_home_service = [];

while ($row = mysqli_fetch_assoc($result_booking)) {
    $waktu = substr($row['waktu_booking'], 0, 5);

    if ($row['service_type'] === 'in_clinic') {
        // Hanya in_clinic yang menutup slot klinik
        $booked_slots[] = $waktu;
    } else if ($row['service_type'] === 'home_service') {
        // Home service dicatat tapi tidak menutup slot
        $booked_home_service[] = $waktu;
    }
}

// 4. GENERATE ALL AVAILABLE SLOTS
// Jam operasional: 09:00 - 19:30
// Interval: 15 menit
// 1 slot = 1 booking

// Parse jam buka dan tutup
list($buka_hour, $buka_min) = explode(':', $jam_buka);
list($tutup_hour, $tutup_min) = explode(':', $jam_tutup);

$start_time = intval($buka_hour) * 60 + intval($buka_min);
$end_time = intval($tutup_hour) * 60 + intval($tutup_min);

$interval = 15; // 5 menit per slot

$all_slots = [];
for ($time = $start_time; $time <= $end_time; $time += $interval) {
    $hour = floor($time / 60);
    $minute = $time % 60;
    
    $slot_label = sprintf('%02d:%02d', $hour, $minute);
    $all_slots[] = $slot_label;
}

// 5. RETURN RESPONSE
echo json_encode([
    'success' => true,
    'is_holiday' => false,
    'is_closed' => false,
    'jam_buka' => substr($jam_buka, 0, 5),
    'jam_tutup' => substr($jam_tutup, 0, 5),
    'all_slots' => $all_slots,
    'booked' => $booked_slots // Array waktu yang sudah di-booking
]);
?>