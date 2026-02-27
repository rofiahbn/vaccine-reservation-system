<?php
session_start();
require_once '../config.php';
require_once '../calendar_helper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {

    $bookingId = $_POST['booking_id'];
    $newDate   = $_POST['new_date'];
    $newTime   = $_POST['new_time'];

    if (empty($bookingId) || empty($newDate) || empty($newTime)) {
        throw new Exception('Data tidak lengkap!');
    }

    $newTimeFormatted = $newTime . ':00';

    /*
    =====================================
    VALIDASI STATUS TANGGAL (LIBUR / TUTUP / FULL)
    =====================================
    */

    $status = checkDateStatus($conn, $newDate);

    if ($status['is_holiday']) {
        throw new Exception("Tanggal libur: " . $status['holiday_name']);
    }

    if ($status['is_closed']) {
        throw new Exception("Klinik tutup pada tanggal tersebut");
    }

    if ($status['is_full']) {
        throw new Exception("Slot sudah penuh");
    }

    /*
    =====================================
    VALIDASI JAM MASUK OPERASIONAL
    =====================================
    */

    // CEK JADWAL KHUSUS DULU
    $query_khusus = "SELECT jam_buka, jam_tutup, status 
                     FROM jadwal_khusus 
                     WHERE tanggal = ? LIMIT 1";

    $stmt = $conn->prepare($query_khusus);
    $stmt->bind_param("s", $newDate);
    $stmt->execute();
    $res_khusus = $stmt->get_result();

    if ($khusus = $res_khusus->fetch_assoc()) {

        if ($khusus['status'] === 'tutup') {
            throw new Exception("Klinik tutup pada tanggal tersebut");
        }

        $jam_buka  = substr($khusus['jam_buka'],0,5);
        $jam_tutup = substr($khusus['jam_tutup'],0,5);

    } else {

        // Kalau tidak ada jadwal khusus → pakai jadwal rutin
        $date = new DateTime($newDate);
        $hari_week = $date->format('N');
        $hari_db = ($hari_week == 7) ? 1 : $hari_week + 1;

        $query_rutin = "SELECT jam_buka, jam_tutup 
                        FROM jadwal_klinik 
                        WHERE hari_week = ? 
                        AND status='buka'";

        $stmt = $conn->prepare($query_rutin);
        $stmt->bind_param("i", $hari_db);
        $stmt->execute();
        $res_rutin = $stmt->get_result();

        if (!$rutin = $res_rutin->fetch_assoc()) {
            throw new Exception("Tidak ada jadwal operasional");
        }

        $jam_buka  = substr($rutin['jam_buka'],0,5);
        $jam_tutup = substr($rutin['jam_tutup'],0,5);
    }

    // VALIDASI WAKTU DALAM RANGE
    if ($newTime < $jam_buka || $newTime >= $jam_tutup) {
        throw new Exception("Waktu di luar jam operasional");
    }

    /*
    =====================================
    VALIDASI SLOT DUPLIKAT
    =====================================
    */

    $checkQuery = "SELECT COUNT(*) as count
                   FROM bookings
                   WHERE tanggal_booking = ?
                   AND waktu_booking = ?
                   AND status IN ('confirmed','scheduled')
                   AND id != ?";

    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ssi", $newDate, $newTimeFormatted, $bookingId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        throw new Exception("Slot sudah terisi!");
    }

    /*
    =====================================
    UPDATE BOOKING
    =====================================
    */

    $updateQuery = "UPDATE bookings
                    SET tanggal_booking = ?,
                        waktu_booking = ?,
                        updated_at = NOW()
                    WHERE id = ?";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $newDate, $newTimeFormatted, $bookingId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception("Booking tidak ditemukan atau tidak berubah");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Jadwal berhasil diubah!'
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);

}

$conn->close();
?>
