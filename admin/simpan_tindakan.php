<?php
session_start();
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Method not allowed");
}

$booking_id = intval($_POST['booking_id']);
$parent_booking_id = intval($_POST['parent_booking_id']);
$patient_id = intval($_POST['patient_id']);

// Cek apakah sudah ada data tindakan untuk booking ini
$check_sql = "SELECT id FROM tindakan WHERE booking_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $booking_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // UPDATE data yang sudah ada
    $row = $check_result->fetch_assoc();
    $tindakan_id = $row['id'];
    
    $update_sql = "UPDATE tindakan SET 
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
        kedatangan_selanjutnya = ?,
        updated_at = NOW()
        WHERE id = ?";
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param(
        "sssssssissssssi",
        $_POST['anamnesis'],
        $_POST['pemeriksaan_fisik'],
        $_POST['diagnosis'],
        $_POST['tatalaksana'],
        $_POST['suhu'],
        $_POST['tekanan_darah'],
        $_POST['respirasi'],
        $_POST['nadi'],
        $_POST['status'],
        $_POST['jenis_vaksin'],
        $_POST['batch_vaksin'],
        $_POST['expired_vaksin'],
        $_POST['kedatangan_ke'],
        $_POST['kedatangan_selanjutnya'],
        $tindakan_id
    );
} else {
    // INSERT data baru
    $insert_sql = "INSERT INTO tindakan (
        booking_id,
        patient_id,
        anamnesis,
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
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param(
        "iissssssisssssss",
        $booking_id,
        $patient_id,
        $_POST['anamnesis'],
        $_POST['pemeriksaan_fisik'],
        $_POST['diagnosis'],
        $_POST['tatalaksana'],
        $_POST['suhu'],
        $_POST['tekanan_darah'],
        $_POST['respirasi'],
        $_POST['nadi'],
        $_POST['status'],
        $_POST['jenis_vaksin'],
        $_POST['batch_vaksin'],
        $_POST['expired_vaksin'],
        $_POST['kedatangan_ke'],
        $_POST['kedatangan_selanjutnya']
    );
}

// Eksekusi query
if (isset($update_stmt)) {
    $success = $update_stmt->execute();
    $action = 'updated';
} else {
    $success = $insert_stmt->execute();
    $action = 'inserted';
}

if ($success) {
    // Redirect kembali ke halaman proses_tindakan untuk peserta yang sama
    header("Location: proses_tindakan.php?id=$parent_booking_id&participant_id=$patient_id&saved=1");
    exit;
} else {
    echo "Error: " . $conn->error;
}
?>