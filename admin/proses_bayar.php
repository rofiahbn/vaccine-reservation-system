<?php 
session_start();
include "../config.php";

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$metode     = $_GET['metode'] ?? '';

if ($booking_id == 0 || empty($metode)) {
    echo "Data tidak valid";
    exit;
}

/* Ambil layanan & hitung ulang total */
$sql = "SELECT harga FROM booking_services WHERE booking_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error SELECT: " . $conn->error);
}

$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

$subtotal = 0;
while ($row = $result->fetch_assoc()) {
    $subtotal += $row['harga'];
}

$diskon = 0;
$total  = $subtotal - $diskon;

/* Simpan ke tabel payments */
$sql_pay = "INSERT INTO payments (booking_id, metode, subtotal, diskon, total, status)
            VALUES (?, ?, ?, ?, ?, 'paid')";

$stmt_p = $conn->prepare($sql_pay);
if (!$stmt_p) {
    die("SQL Error INSERT PAYMENT: " . $conn->error);
}

$stmt_p->bind_param("isiii", $booking_id, $metode, $subtotal, $diskon, $total);
$stmt_p->execute();

/* Update status booking */
$sql_up = "UPDATE bookings 
           SET payment_status = 'paid'
           WHERE id = ?";

$stmt_u = $conn->prepare($sql_up);
if (!$stmt_u) {
    die("SQL Error UPDATE BOOKING: " . $conn->error);
}

$stmt_u->bind_param("i", $booking_id);
$stmt_u->execute();

/* Redirect balik ke halaman pembayaran */
header("Location: pembayaran.php?id=$booking_id&success=1");
exit;
