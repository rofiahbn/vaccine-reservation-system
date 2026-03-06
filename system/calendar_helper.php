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


    /*
    =====================================
    1. CEK LIBUR (RANGE TANGGAL - SESUAI STRUKTUR TABEL)
    =====================================
    */

    $query_libur = "SELECT keterangan FROM jadwal_libur WHERE ? BETWEEN tanggal_mulai AND tanggal_selesai LIMIT 1";
    $stmt = mysqli_prepare($conn, $query_libur);
    mysqli_stmt_bind_param($stmt, 's', $tanggal);
    mysqli_stmt_execute($stmt);
    $res_libur = mysqli_stmt_get_result($stmt);

    if ($libur = mysqli_fetch_assoc($res_libur)) {
        $result['is_holiday'] = true;
        $result['holiday_name'] = $libur['keterangan'];
        return $result;
    }


    /*
    =====================================
    2. CEK JADWAL KHUSUS (RANGE TANGGAL)
    =====================================
    */

    $query_khusus = "
        SELECT jam_buka, jam_tutup, status
        FROM jadwal_khusus
        WHERE ? BETWEEN tanggal_mulai AND tanggal_selesai
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $query_khusus);
    mysqli_stmt_bind_param($stmt, 's', $tanggal);
    mysqli_stmt_execute($stmt);
    $res_khusus = mysqli_stmt_get_result($stmt);

    if($khusus = mysqli_fetch_assoc($res_khusus)){

        if($khusus['status'] === 'tutup'){
            $result['is_closed'] = true;
            return $result;
        }

        $jam_buka = $khusus['jam_buka'];
        $jam_tutup = $khusus['jam_tutup'];

    } else {

        /*
        =====================================
        3. CEK JADWAL RUTIN
        =====================================
        */

        $date = new DateTime($tanggal);
        $hari_week = $date->format('N');
        $hari_week_db = ($hari_week == 7) ? 1 : $hari_week + 1;

        $query_jadwal = "
            SELECT jam_buka, jam_tutup
            FROM jadwal_klinik
            WHERE hari_week = ? AND status = 'buka'
            LIMIT 1
        ";

        $stmt = mysqli_prepare($conn, $query_jadwal);
        mysqli_stmt_bind_param($stmt, 'i', $hari_week_db);
        mysqli_stmt_execute($stmt);
        $res_jadwal = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($res_jadwal) == 0){
            $result['is_closed'] = true;
            return $result;
        }

        $jadwal = mysqli_fetch_assoc($res_jadwal);
        $jam_buka = $jadwal['jam_buka'];
        $jam_tutup = $jadwal['jam_tutup'];
    }


    /*
    =====================================
    4. CEK SLOT BOOKING
    =====================================
    */

    list($buka_hour, $buka_min) = explode(':', $jam_buka);
    list($tutup_hour, $tutup_min) = explode(':', $jam_tutup);

    $start_time = intval($buka_hour) * 60 + intval($buka_min);
    $end_time   = intval($tutup_hour) * 60 + intval($tutup_min);

    $interval = 15;
    $total_slots = ceil(($end_time - $start_time) / $interval);


    $query_booking = "
        SELECT COUNT(DISTINCT waktu_booking) as total 
        FROM bookings 
        WHERE tanggal_booking = ?
        AND status IN ('pending','confirmed')
        AND parent_id IS NULL
    ";

    $stmt = mysqli_prepare($conn, $query_booking);
    mysqli_stmt_bind_param($stmt, 's', $tanggal);
    mysqli_stmt_execute($stmt);
    $res_booking = mysqli_stmt_get_result($stmt);

    $booking = mysqli_fetch_assoc($res_booking);
    $booked_count = $booking['total'];

    if($booked_count >= $total_slots){
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