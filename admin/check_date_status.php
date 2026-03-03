<?php
header('Content-Type: application/json');
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

// Ambil tanggal dari parameter
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    echo json_encode(['success'=>false,'message'=>'Format tanggal salah']);
    exit;
}

$result = [
    'success' => true,
    'is_holiday' => false,
    'is_closed' => false,
    'is_full' => false,
    'holiday_name' => ''
];

// 1. CEK HARI LIBUR (RANGE TANGGAL)
$query_libur = "SELECT keterangan FROM jadwal_libur WHERE ? BETWEEN tanggal_mulai AND tanggal_selesai LIMIT 1";
$stmt = mysqli_prepare($conn, $query_libur);
mysqli_stmt_bind_param($stmt, 's', $tanggal);
mysqli_stmt_execute($stmt);
$res_libur = mysqli_stmt_get_result($stmt);

if ($libur = mysqli_fetch_assoc($res_libur)) {
    $result['is_holiday'] = true;
    $result['holiday_name'] = $libur['keterangan'];
    echo json_encode($result);
    exit;
}

// 2. CEK JADWAL KHUSUS (RANGE TANGGAL)
$query_khusus = "SELECT jam_buka, jam_tutup, status, keterangan FROM jadwal_khusus WHERE ? BETWEEN tanggal_mulai AND tanggal_selesai LIMIT 1";
$stmt = mysqli_prepare($conn, $query_khusus);
mysqli_stmt_bind_param($stmt, 's', $tanggal);
mysqli_stmt_execute($stmt);
$res_khusus = mysqli_stmt_get_result($stmt);

if ($khusus = mysqli_fetch_assoc($res_khusus)) {
    // Ada jadwal khusus untuk tanggal ini
    if ($khusus['status'] == 'tutup') {
        $result['is_closed'] = true;
        $result['holiday_name'] = !empty($khusus['keterangan']) ? $khusus['keterangan'] : 'Tutup (Jadwal Khusus)';
        echo json_encode($result);
        exit;
    }
    
    // Jika buka, gunakan jam khusus ini
    $jam_buka = $khusus['jam_buka'];
    $jam_tutup = $khusus['jam_tutup'];
    
    // Lanjut ke pengecekan slot booking
} else {
    // 3. CEK JADWAL KLINIK RUTIN
    $date = new DateTime($tanggal);
    $hari_week = $date->format('N'); // 1=Senin, 7=Minggu
    // Convert ke format database (1=Minggu, 2=Senin, ..., 7=Sabtu)
    $hari_week_db = ($hari_week == 7) ? 1 : $hari_week + 1;

    $query_jadwal = "SELECT jam_buka, jam_tutup FROM jadwal_klinik WHERE hari_week = ? AND status = 'buka'";
    $stmt = mysqli_prepare($conn, $query_jadwal);
    mysqli_stmt_bind_param($stmt, 'i', $hari_week_db);
    mysqli_stmt_execute($stmt);
    $res_jadwal = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res_jadwal) == 0) {
        $result['is_closed'] = true;
        echo json_encode($result);
        exit;
    }

    $jadwal = mysqli_fetch_assoc($res_jadwal);
    $jam_buka = $jadwal['jam_buka'];
    $jam_tutup = $jadwal['jam_tutup'];
}

// 4. CEK APAKAH SEMUA SLOT PENUH
list($buka_hour, $buka_min) = explode(':', $jam_buka);
list($tutup_hour, $tutup_min) = explode(':', $jam_tutup);

$start_time = intval($buka_hour) * 60 + intval($buka_min);
$end_time = intval($tutup_hour) * 60 + intval($tutup_min);

$interval = 15; 
$total_slots = ceil(($end_time - $start_time) / $interval);

// Hitung berapa slot yang sudah di-booking
$query_booking = "
    SELECT COUNT(DISTINCT waktu_booking) as total 
    FROM bookings 
    WHERE tanggal_booking = ?
    AND status IN ('pending', 'confirmed')
    AND parent_id IS NULL
";
$stmt = mysqli_prepare($conn, $query_booking);
mysqli_stmt_bind_param($stmt, 's', $tanggal);
mysqli_stmt_execute($stmt);
$res_booking = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($res_booking);

$booked_count = $booking['total'];

if ($booked_count >= $total_slots) {
    $result['is_full'] = true;
}

echo json_encode($result);
?>