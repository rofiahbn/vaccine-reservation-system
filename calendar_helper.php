<?php
/**
 * Helper function untuk cek status tanggal di kalender
 * 
 * @param mysqli $conn - Database connection
 * @param string $tanggal - Format: YYYY-MM-DD
 * @return array - ['is_holiday' => bool, 'is_closed' => bool, 'is_full' => bool, 'holiday_name' => string]
 */
function checkDateStatus($conn, $tanggal) {
    $result = [
        'is_holiday' => false,
        'is_closed' => false,
        'is_full' => false,
        'holiday_name' => ''
    ];
    
    // 1. CEK HARI LIBUR
    $query_libur = "SELECT keterangan FROM jadwal_libur WHERE tanggal = ?";
    $stmt = mysqli_prepare($conn, $query_libur);
    mysqli_stmt_bind_param($stmt, 's', $tanggal);
    mysqli_stmt_execute($stmt);
    $res_libur = mysqli_stmt_get_result($stmt);
    
    if ($libur = mysqli_fetch_assoc($res_libur)) {
        $result['is_holiday'] = true;
        $result['holiday_name'] = $libur['keterangan'];
        return $result; // Langsung return, tidak perlu cek yang lain
    }
    
    // 2. CEK JADWAL KLINIK
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
        return $result;
    }
    
    $jadwal = mysqli_fetch_assoc($res_jadwal);
    $jam_buka = $jadwal['jam_buka'];
    $jam_tutup = $jadwal['jam_tutup'];
    
    // 3. CEK APAKAH SEMUA SLOT PENUH
    // Hitung total slot yang tersedia
    list($buka_hour, $buka_min) = explode(':', $jam_buka);
    list($tutup_hour, $tutup_min) = explode(':', $jam_tutup);
    
    $start_time = intval($buka_hour) * 60 + intval($buka_min);
    $end_time = intval($tutup_hour) * 60 + intval($tutup_min);
    
    $interval = 15; 
    $total_slots = floor(($end_time - $start_time) / $interval) + 1;
    
    // Hitung berapa slot yang sudah di-booking
    $query_booking = "
        SELECT COUNT(*) as total 
        FROM bookings 
        WHERE tanggal_booking = ?
        AND status IN ('pending', 'confirmed')
    ";
    $stmt = mysqli_prepare($conn, $query_booking);
    mysqli_stmt_bind_param($stmt, 's', $tanggal);
    mysqli_stmt_execute($stmt);
    $res_booking = mysqli_stmt_get_result($stmt);
    $booking = mysqli_fetch_assoc($res_booking);
    
    $booked_count = $booking['total'];
    
    // Jika semua slot penuh
    if ($booked_count >= $total_slots) {
        $result['is_full'] = true;
    }
    
    return $result;
}

/**
 * Generate CSS class untuk kalender berdasarkan status tanggal
 */
function getDateClass($status, $is_today = false) {
    $class = 'day';
    
    if ($is_today) {
        $class .= ' today';
    }
    
    if ($status['is_holiday']) {
        $class .= ' holiday';
        return $class;
    }
    
    if ($status['is_closed']) {
        $class .= ' closed';
        return $class;
    }
    
    if ($status['is_full']) {
        $class .= ' full';
        return $class;
    }
    
    return $class;
}

/**
 * Generate title/tooltip untuk kalender
 */
function getDateTitle($status) {
    if ($status['is_holiday']) {
        return 'Libur: ' . $status['holiday_name'];
    }
    
    if ($status['is_closed']) {
        return 'Klinik tutup';
    }
    
    if ($status['is_full']) {
        return 'Jadwal penuh';
    }
    
    return 'Klik untuk pilih jadwal';
}

/**
 * Check apakah tanggal bisa diklik
 */
function isDateClickable($status) {
    return !$status['is_holiday'] && !$status['is_closed'] && !$status['is_full'];
}
?>