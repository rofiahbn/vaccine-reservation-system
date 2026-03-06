<?php
include "config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$nama_layanan = trim($_POST['nama_layanan'] ?? '');

if ($id == 0 || empty($nama_layanan)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Ambil harga dari services pakai CONVERT untuk handle collation berbeda
$harga_baru = null;
$stmt_harga = $conn->prepare("SELECT harga FROM services WHERE CONVERT(nama_layanan USING utf8mb4) = CONVERT(? USING utf8mb4) LIMIT 1");
$stmt_harga->bind_param("s", $nama_layanan);
$stmt_harga->execute();
$row = $stmt_harga->get_result()->fetch_assoc();
if ($row) {
    $harga_baru = $row['harga'];
}

if ($harga_baru !== null) {
    $stmt = $conn->prepare("UPDATE booking_services SET nama_layanan = ?, harga = ?, total = ?, diskon = 0, diskon_tipe = 'nilai' WHERE id = ?");
    $stmt->bind_param("siii", $nama_layanan, $harga_baru, $harga_baru, $id);
} else {
    $stmt = $conn->prepare("UPDATE booking_services SET nama_layanan = ? WHERE id = ?");
    $stmt->bind_param("si", $nama_layanan, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Berhasil diperbarui', 'harga' => $harga_baru]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}