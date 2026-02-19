<?php
session_start();
include "../config.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);


header("Content-Type: application/json");

try {

    // ================= VALIDASI =================
    if (!isset($_POST['booking_id']) || !isset($_POST['patient_id'])) {
        throw new Exception("Booking / pasien tidak valid");
    }

    $booking_id = intval($_POST['booking_id']);
    $patient_id = intval($_POST['patient_id']);

    // ================= DATA ANAMNESIS =================
    $keluhan = $_POST['keluhan'] ?? 'Tidak ada keluhan';
    $kipi_sebelumnya = $_POST['kipi_sebelumnya'] ?? 'Tidak ada';
    $kontraindikasi = $_POST['kontraindikasi'] ?? 'Tidak ada';
    $anamnesis = $_POST['anamnesis'] ?? '';
    
    // ================= DATA PEMERIKSAAN FISIK =================
    $bb = !empty($_POST['bb']) && $_POST['bb'] !== '' ? floatval($_POST['bb']) : null;
    $tb = !empty($_POST['tb']) && $_POST['tb'] !== '' ? floatval($_POST['tb']) : null;
    $lingkar_kepala = !empty($_POST['lingkar_kepala']) && $_POST['lingkar_kepala'] !== '' ? floatval($_POST['lingkar_kepala']) : null;
    $pf_lainnya = $_POST['pf_lainnya'] ?? 'Dalam batas normal';
    $pemeriksaan_fisik = $_POST['pemeriksaan_fisik'] ?? '';

    // ================= VITAL SIGNS =================
    $suhu = !empty($_POST['suhu']) && $_POST['suhu'] !== '' ? floatval($_POST['suhu']) : null;
    $tekanan_darah = $_POST['tekanan_darah'] ?? '';
    $respirasi = !empty($_POST['respirasi']) && $_POST['respirasi'] !== '' ? intval($_POST['respirasi']) : null;
    $nadi = !empty($_POST['nadi']) && $_POST['nadi'] !== '' ? intval($_POST['nadi']) : null;
    
    // ================= DIAGNOSIS & TATALAKSANA =================
    $diagnosis = $_POST['diagnosis'] ?? '';
    $tatalaksana = $_POST['tatalaksana'] ?? '';

    // ================= DATA VAKSIN =================
    $jenis_vaksin = $_POST['jenis_vaksin'] ?? '';
    $batch_vaksin = $_POST['batch_vaksin'] ?? '';
    $expired_vaksin = !empty($_POST['expired_vaksin']) ? $_POST['expired_vaksin'] : null;

    // ================= KEDATANGAN =================
    $kedatangan_ke = !empty($_POST['kedatangan_ke']) ? intval($_POST['kedatangan_ke']) : null;
    // ✅ FIX: kedatangan_selanjutnya adalah DATE, bukan integer!
    $kedatangan_selanjutnya = !empty($_POST['kedatangan_selanjutnya']) ? $_POST['kedatangan_selanjutnya'] : null;
    
    $status = $_POST['status'] ?? 'Aktif';

    // ================= CEK SUDAH ADA TINDAKAN? =================
    $cek = $conn->prepare("SELECT id FROM tindakan WHERE booking_id = ?");
    $cek->bind_param("i", $booking_id);
    $cek->execute();
    $res = $cek->get_result();

    if ($res->num_rows > 0) {

        // ================= UPDATE =================
        $row = $res->fetch_assoc();
        $tindakan_id = $row['id'];

        $sql = "UPDATE tindakan SET
            keluhan = ?,
            kipi_sebelumnya = ?,
            kontraindikasi = ?,
            anamnesis = ?,
            bb = ?,
            tb = ?,
            lingkar_kepala = ?,
            pf_lainnya = ?,
            pemeriksaan_fisik = ?,
            diagnosis = ?,
            tatalaksana = ?,
            suhu = ?,
            tekanan_darah = ?,
            respirasi = ?,
            nadi = ?,
            status = ?,
            jenis_vaksin = ?,
            batch_vaksin = ?,
            expired_vaksin = ?,
            kedatangan_ke = ?,
            kedatangan_selanjutnya = ?,
            updated_at = NOW()
        WHERE id = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare error: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssdddssssdsiissssisi",
            $keluhan,
            $kipi_sebelumnya,
            $kontraindikasi,
            $anamnesis,
            $bb,
            $tb,
            $lingkar_kepala,
            $pf_lainnya,
            $pemeriksaan_fisik,
            $diagnosis,
            $tatalaksana,
            $suhu,
            $tekanan_darah,
            $respirasi,
            $nadi,
            $status,
            $jenis_vaksin,
            $batch_vaksin,
            $expired_vaksin,
            $kedatangan_ke,
            $kedatangan_selanjutnya,
            $tindakan_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Update error: " . $stmt->error);
        }

    } else {

        // ================= INSERT BARU =================
        $sql = "INSERT INTO tindakan (
            booking_id,
            patient_id,
            keluhan,
            kipi_sebelumnya,
            kontraindikasi,
            anamnesis,
            bb,
            tb,
            lingkar_kepala,
            pf_lainnya,
            pemeriksaan_fisik,
            diagnosis,
            tatalaksana,
            suhu,
            tekanan_darah,
            respirasi,
            nadi,
            status,
            jenis_vaksin,
            batch_vaksin,
            expired_vaksin,
            kedatangan_ke,
            kedatangan_selanjutnya,
            created_at,
            updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            NOW(),
            NOW()
        )";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare error: " . $conn->error);
        }

        $stmt->bind_param(
            "iissssdddssssdssisssis",
            $booking_id,
            $patient_id,
            $keluhan,
            $kipi_sebelumnya,
            $kontraindikasi,
            $anamnesis,
            $bb,
            $tb,
            $lingkar_kepala,
            $pf_lainnya,
            $pemeriksaan_fisik,
            $diagnosis,
            $tatalaksana,
            $suhu,
            $tekanan_darah,
            $respirasi,
            $nadi,
            $status,
            $jenis_vaksin,
            $batch_vaksin,
            $expired_vaksin,
            $kedatangan_ke,
            $kedatangan_selanjutnya
        );

        if (!$stmt->execute()) {
            throw new Exception("Insert error: " . $stmt->error);
        }
    }

    // ================= TANDAI BOOKING SUDAH ADA TINDAKAN =================
    $sql_flag = "UPDATE bookings 
            SET tindakan_selesai = 1
            WHERE id = ?";

    $stmt_flag = $conn->prepare($sql_flag);
    $stmt_flag->bind_param("i", $booking_id);
    $stmt_flag->execute();

    echo json_encode([
        "success" => true,
        "message" => "Tindakan berhasil disimpan"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>