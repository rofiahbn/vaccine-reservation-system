<?php 
session_start();
include "../config.php";

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$metode     = $_GET['metode'] ?? '';

$subtotal   = isset($_GET['subtotal']) ? intval($_GET['subtotal']) : 0;
$diskon     = isset($_GET['diskon']) ? intval($_GET['diskon']) : 0;
$total      = isset($_GET['total']) ? intval($_GET['total']) : 0;

$detail_diskon = $_GET['detail_diskon'] ?? '{}';
$diskonItems = json_decode($detail_diskon, true);

if ($booking_id == 0 || empty($metode) || $total <= 0) {
    echo "Data pembayaran tidak valid";
    exit;
}

/* Simpan ke tabel payments */
$sql_pay = "INSERT INTO payments 
            (booking_id, metode, subtotal, diskon, total, status)
            VALUES (?, ?, ?, ?, ?, 'paid')";

$stmt_p = $conn->prepare($sql_pay);
if (!$stmt_p) {
    die("SQL Error INSERT PAYMENT: " . $conn->error);
}

$stmt_p->bind_param("isiii", $booking_id, $metode, $subtotal, $diskon, $total);
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

    foreach ($diskonItems as $index => $data) {

        // ambil layanan sesuai urutan
        $q = $conn->prepare("
            SELECT id, harga 
            FROM booking_services 
            WHERE booking_id = ? 
            ORDER BY id ASC 
            LIMIT 1 OFFSET ?
        ");
        $q->bind_param("ii", $booking_id, $index);
        $q->execute();
        $srv = $q->get_result()->fetch_assoc();

        if ($srv) {

            $diskonNominal = $data['nominal'];
            $tipeDiskon    = $data['tipe'];
            $totalItem     = $srv['harga'] - $diskonNominal;

            // update ke booking_services
            $up = $conn->prepare("
                UPDATE booking_services 
                SET 
                    diskon = ?, 
                    diskon_tipe = ?, 
                    total = ?
                WHERE id = ?
            ");
            $up->bind_param(
                "isii", 
                $diskonNominal, 
                $tipeDiskon, 
                $totalItem, 
                $srv['id']
            );
            $up->execute();
        }
    }
}

/* Redirect balik ke halaman pembayaran */
header("Location: pembayaran.php?id=$booking_id&success=1");
exit;
