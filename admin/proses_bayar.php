<?php 
session_start();
include "../config.php";

$booking_id = intval($_POST['id'] ?? 0);
$metode     = $_POST['metode'] ?? '';

$subtotal = intval($_POST['subtotal'] ?? 0);
$diskon   = intval($_POST['diskon'] ?? 0);
$total    = intval($_POST['total'] ?? 0);

$detail_diskon = $_POST['detail_diskon'] ?? '{}';
$diskonItems = json_decode($detail_diskon, true);

$diskon_tipe = 'nilai'; // default
// diskon_tipe di payments hanya untuk display summary invoice
// detail per item tetap di booking_services

foreach ($diskonItems as $d) {
    if ($d['tipe'] === 'persen') {
        $diskon_tipe = 'persen';
        break;
    }
}

if (
    $booking_id <= 0 ||
    !in_array($metode, ['tunai','transfer','qris']) ||
    $subtotal <= 0 ||
    $total <= 0 ||
    $total > $subtotal
) {
    die("Data pembayaran tidak valid");
}

/* Simpan ke tabel payments */
$sql_pay = "INSERT INTO payments 
    (booking_id, metode, subtotal, diskon, diskon_tipe, total, status)
    VALUES (?, ?, ?, ?, ?, ?, 'paid')";

$stmt_p = $conn->prepare($sql_pay);
$stmt_p->bind_param(
    "isissi",
    $booking_id,
    $metode,
    $subtotal,
    $diskon,
    $diskon_tipe,
    $total
);
$stmt_p->execute();

/* Update status booking */
$sql_up = "UPDATE bookings 
           SET 
               payment_status = 'paid',
               status = 'completed'
           WHERE id = ?";

$stmt_u = $conn->prepare($sql_up);
if (!$stmt_u) {
    die("SQL Error UPDATE BOOKING: " . $conn->error);
}

$stmt_u->bind_param("i", $booking_id);
$stmt_u->execute();

/* ================= SIMPAN DISKON PER ITEM ================= */

if (!empty($diskonItems)) {

    foreach ($diskonItems as $data) {

        $service_id = intval($data['service_id']);
        $diskonNominal = intval($data['nominal']);
        $tipeDiskon = $data['tipe'];

        $q = $conn->prepare("
            SELECT harga 
            FROM booking_services 
            WHERE id = ? AND booking_id = ?
        ");
        $q->bind_param("ii", $service_id, $booking_id);
        $q->execute();
        $srv = $q->get_result()->fetch_assoc();

        if ($srv) {
            $totalItem = $srv['harga'] - $diskonNominal;

            $up = $conn->prepare("
                UPDATE booking_services 
                SET diskon = ?, diskon_tipe = ?, total = ?
                WHERE id = ?
            ");
            $up->bind_param("isii", $diskonNominal, $tipeDiskon, $totalItem, $service_id);
            $up->execute();
        }
    }

}

/* Redirect balik ke halaman pembayaran */
header("Location: pembayaran.php?id=$booking_id&success=1");
exit;
