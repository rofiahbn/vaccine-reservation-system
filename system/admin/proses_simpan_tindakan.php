<?php 
require "config.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {

    if (empty($_POST['booking_id']) || empty($_POST['patient_id'])) {
        throw new Exception("Booking / pasien tidak valid");
    }

    $booking_id = (int) $_POST['booking_id'];
    $patient_id = (int) $_POST['patient_id'];

    // ================= HEADER DATA =================
    $keluhan                = $_POST['keluhan'] ?? null;
    $kipi                   = $_POST['kipi_sebelumnya'] ?? null;
    $kontraindikasi         = $_POST['kontraindikasi'] ?? null;
    $anamnesis              = $_POST['anamnesis'] ?? null;
    $pemeriksaan_fisik      = $_POST['pemeriksaan_fisik'] ?? null;
    $diagnosis              = $_POST['diagnosis'] ?? null;
    $tatalaksana_text       = $_POST['tatalaksana'] ?? null;

    $suhu                   = $_POST['suhu'] !== '' ? $_POST['suhu'] : null;
    $tekanan_darah          = $_POST['tekanan_darah'] ?? null;
    $respirasi              = isset($_POST['respirasi']) && $_POST['respirasi'] !== '' ? $_POST['respirasi'] : null;
    $nadi                   = isset($_POST['nadi']) && $_POST['nadi'] !== '' ? $_POST['nadi'] : null;
    $bb                     = $_POST['bb'] !== '' ? $_POST['bb'] : null;
    $tb                     = $_POST['tb'] !== '' ? $_POST['tb'] : null;
    $lingkar_kepala         = $_POST['lingkar_kepala'] !== '' ? $_POST['lingkar_kepala'] : null;
    $pf_lainnya             = $_POST['pf_lainnya'] ?? null;

    $jenis_vaksin           = $_POST['jenis_vaksin'] ?? null;
    $batch_vaksin           = $_POST['batch_vaksin'] ?? null;
    $expired_vaksin         = !empty($_POST['expired_vaksin']) ? $_POST['expired_vaksin'] : null;

    $kedatangan_ke          = $_POST['kedatangan_ke'] !== '' ? $_POST['kedatangan_ke'] : null;
    $kedatangan_selanjutnya = !empty($_POST['kedatangan_selanjutnya']) ? $_POST['kedatangan_selanjutnya'] : null;
    $status                 = $_POST['status'] ?? null;

    $conn->begin_transaction();

    // ================= CEK HEADER TINDAKAN =================
    $cek = $conn->prepare("SELECT id FROM tindakan WHERE booking_id = ?");
    $cek->bind_param("i", $booking_id);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $tindakan_id = $row['id'];

        $stmt = $conn->prepare("
            UPDATE tindakan SET
                keluhan = ?, kipi_sebelumnya = ?, kontraindikasi = ?,
                anamnesis = ?, pemeriksaan_fisik = ?,
                diagnosis = ?, tatalaksana = ?,
                suhu = ?, tekanan_darah = ?, respirasi = ?, nadi = ?,
                bb = ?, tb = ?, lingkar_kepala = ?,
                pf_lainnya = ?,
                jenis_vaksin = ?, batch_vaksin = ?, expired_vaksin = ?,
                kedatangan_ke = ?, kedatangan_selanjutnya = ?,
                status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");

        $stmt->bind_param(
            "sssssssssssssssssssssi",
            $keluhan, $kipi, $kontraindikasi,
            $anamnesis, $pemeriksaan_fisik,
            $diagnosis, $tatalaksana_text,
            $suhu, $tekanan_darah, $respirasi, $nadi,
            $bb, $tb, $lingkar_kepala,
            $pf_lainnya,
            $jenis_vaksin, $batch_vaksin, $expired_vaksin,
            $kedatangan_ke, $kedatangan_selanjutnya,
            $status,
            $tindakan_id
        );

        $stmt->execute();
        $stmt->close();

    } else {

        $stmt = $conn->prepare("
            INSERT INTO tindakan (
                booking_id, patient_id,
                keluhan, kipi_sebelumnya, kontraindikasi,
                anamnesis, pemeriksaan_fisik,
                diagnosis, tatalaksana,
                suhu, tekanan_darah, respirasi, nadi,
                bb, tb, lingkar_kepala, pf_lainnya,
                jenis_vaksin, batch_vaksin, expired_vaksin,
                kedatangan_ke, kedatangan_selanjutnya,
                status, created_at, updated_at
            ) VALUES (
                ?, ?,
                ?, ?, ?,
                ?, ?,
                ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?,
                ?, NOW(), NOW()
            )
        ");

        $stmt->bind_param(
            "iisssssssssssssssssssss",
            $booking_id, $patient_id,
            $keluhan, $kipi, $kontraindikasi,
            $anamnesis, $pemeriksaan_fisik,
            $diagnosis, $tatalaksana_text,
            $suhu, $tekanan_darah, $respirasi, $nadi,
            $bb, $tb, $lingkar_kepala, $pf_lainnya,
            $jenis_vaksin, $batch_vaksin, $expired_vaksin,
            $kedatangan_ke, $kedatangan_selanjutnya,
            $status
        );

        $stmt->execute();
        $tindakan_id = $conn->insert_id;
        $stmt->close();
    }

    // ================= HAPUS DETAIL LAMA =================
    $hapus = $conn->prepare("DELETE FROM tatalaksana WHERE tindakan_id = ?");
    $hapus->bind_param("i", $tindakan_id);
    $hapus->execute();
    $hapus->close();

    // ================= SIMPAN DETAIL TATALAKSANA =================
    $tipe_baris   = $_POST['tipe_baris'] ?? [];
    $staff_arr    = $_POST['staff'] ?? [];
    $lokasi_arr   = $_POST['lokasi'] ?? [];
    $rute_arr     = $_POST['rute'] ?? [];
    $dosis_arr    = $_POST['dosis'] ?? [];
    $batch_arr    = $_POST['batch'] ?? [];
    $product_arr  = $_POST['product_id'] ?? [];
    $expired_arr  = $_POST['expired_date'] ?? [];
    $jasa_arr     = $_POST['jasa_id'] ?? [];

    foreach ($tipe_baris as $index => $tipe) {

        $staff_id = isset($staff_arr[$index]) && $staff_arr[$index] !== ''
            ? (int)$staff_arr[$index] : null;

        if ($tipe === 'jasa') {
            // ===== SIMPAN BARIS JASA =====
            $jasa_id = isset($jasa_arr[$index]) ? (int)$jasa_arr[$index] : null;
            if (!$jasa_id) continue;

            $insert = $conn->prepare("
                INSERT INTO tatalaksana
                (tindakan_id, booking_id, patient_id, jasa_id, staff_id, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $insert->bind_param("iiiii",
                $tindakan_id, $booking_id, $patient_id,
                $jasa_id, $staff_id
            );
            $insert->execute();
            $insert->close();

        } elseif ($tipe === 'produk') {
            // ===== SIMPAN BARIS PRODUK =====
            $product_id   = isset($product_arr[$index]) ? (int)$product_arr[$index] : null;
            $batch        = $batch_arr[$index] ?? null;
            $expired_date = $expired_arr[$index] ?? null;
            $lokasi       = $lokasi_arr[$index] ?? '';
            $rute         = $rute_arr[$index] ?? '';
            $dosis        = $dosis_arr[$index] ?? 1;

            if (!$product_id || empty($batch)) continue;

            $insert = $conn->prepare("
                INSERT INTO tatalaksana
                (tindakan_id, booking_id, patient_id, product_id, batch_number,
                 expired_date, lokasi, rute, dosis, staff_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $insert->bind_param("iiiissssii",
                $tindakan_id, $booking_id, $patient_id,
                $product_id, $batch, $expired_date,
                $lokasi, $rute, $dosis, $staff_id
            );
            $insert->execute();
            $insert->close();

            // Kurangi stok
            $kurangi = $conn->prepare("
                UPDATE product_stock SET stock = stock - ?
                WHERE product_id = ? AND batch_number = ?
            ");
            $kurangi->bind_param("iis", $dosis, $product_id, $batch);
            $kurangi->execute();
            $kurangi->close();
        }
    }

    $conn->commit();

    // Update tindakan_selesai
    $update_booking = $conn->prepare("UPDATE bookings SET tindakan_selesai = 1 WHERE id = ?");
    $update_booking->bind_param("i", $booking_id);
    $update_booking->execute();
    $update_booking->close();

    echo json_encode([
        "success" => true,
        "message" => "Tindakan berhasil disimpan"
    ]);

} catch (Throwable $e) {
    $conn->rollback();
    echo json_encode([
        "success" => false,
        "message" => "Gagal simpan tindakan: " . $e->getMessage()
    ]);
}