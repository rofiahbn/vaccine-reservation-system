<?php
session_start();
include "../config.php";

header("Content-Type: application/json");

try {

    // ================= VALIDASI =================
    if (!isset($_POST['booking_id']) || !isset($_POST['patient_id'])) {
        throw new Exception("Booking / pasien tidak valid");
    }

    $booking_id = intval($_POST['booking_id']);
    $patient_id = intval($_POST['patient_id']);

    // ================= DATA MEDIS =================
    $anamnesis = $_POST['anamnesis'] ?? '';
    $pemeriksaan_fisik = $_POST['pemeriksaan_fisik'] ?? '';
    $diagnosis = $_POST['diagnosis'] ?? '';
    $tatalaksana = $_POST['tatalaksana'] ?? '';

    $suhu = ($_POST['suhu'] !== "") ? floatval($_POST['suhu']) : null;
    $tekanan = $_POST['tekanan_darah'] ?? '';
    $respirasi = ($_POST['respirasi'] !== "") ? intval($_POST['respirasi']) : null;
    $nadi = ($_POST['nadi'] !== "") ? intval($_POST['nadi']) : null;

    // ================= DATA VAKSIN =================
    $jenis_vaksin = $_POST['jenis_vaksin'] ?? '';
    $batch_vaksin = $_POST['batch_vaksin'] ?? '';
    $expired_vaksin = ($_POST['expired_vaksin'] !== "") ? $_POST['expired_vaksin'] : null;

    $kedatangan_ke = ($_POST['kedatangan_ke'] !== "") ? intval($_POST['kedatangan_ke']) : null;
    $kedatangan_selanjutnya = ($_POST['kedatangan_selanjutnya'] !== "") ? intval($_POST['kedatangan_selanjutnya']) : null;

    $status = $_POST['status'] ?? 'Aktif';

    $respirasi = ($respirasi === null) ? 0 : $respirasi;
    $nadi = ($nadi === null) ? 0 : $nadi;
    $kedatangan_ke = ($kedatangan_ke === null) ? 0 : $kedatangan_ke;
    $kedatangan_selanjutnya = ($kedatangan_selanjutnya === null) ? 0 : $kedatangan_selanjutnya;

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
            anamnesis = ?,
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
            kedatangan_selanjutnya = ?
        WHERE id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssssdsiiisssiii",
            $anamnesis,
            $pemeriksaan_fisik,
            $diagnosis,
            $tatalaksana,
            $suhu,
            $tekanan,
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

        $stmt->execute();

    } else {

        // ================= INSERT BARU =================
        $sql = "INSERT INTO tindakan 
        (booking_id, patient_id,
        anamnesis, pemeriksaan_fisik, diagnosis, tatalaksana,
        suhu, tekanan_darah, respirasi, nadi,
        status,
        created_at,
        jenis_vaksin, batch_vaksin, expired_vaksin,
        kedatangan_ke, kedatangan_selanjutnya)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $conn->prepare($sql);

        $created_at = date("Y-m-d H:i:s");

        $stmt->bind_param(
            "iissssdsiiissssii",
            $booking_id,
            $patient_id,
            $anamnesis,
            $pemeriksaan_fisik,
            $diagnosis,
            $tatalaksana,
            $suhu,
            $tekanan,
            $respirasi,
            $nadi,
            $status,
            $created_at,
            $jenis_vaksin,
            $batch_vaksin,
            $expired_vaksin,
            $kedatangan_ke,
            $kedatangan_selanjutnya
        );

        $stmt->execute();
    }

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
