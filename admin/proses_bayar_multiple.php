<?php
session_start();
include "../config.php";

// Debug mode
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ambil data
$booking_id = intval($_POST['booking_id'] ?? 0);
$payment_methods = json_decode($_POST['payment_methods'] ?? '[]', true);
$jumlah_bayar = floatval($_POST['jumlahBayar'] ?? 0);
$diskon_total = floatval($_POST['diskon_total'] ?? 0);
$subtotal = floatval($_POST['subtotal'] ?? 0);
$total_tagihan = floatval($_POST['total_tagihan'] ?? 0); // Ini sudah termasuk diskon item
$sisa_tagihan = floatval($_POST['sisa_tagihan'] ?? 0);

// Validasi data dasar
if ($booking_id <= 0) {
    die(json_encode(['error' => 'Booking ID tidak valid']));
}

if (empty($payment_methods) || !is_array($payment_methods)) {
    die(json_encode(['error' => 'Metode pembayaran tidak valid']));
}

// ================= TENTUKAN DISKON_TIPE =================
if ($diskon_total > 0) {
    $diskon_tipe_value = 'total_diskon';
} else {
    // Sesuaikan dengan default di database
    $diskon_tipe_value = 'none'; // atau NULL jika diizinkan
}

// ================= AMBIL DATA BOOKING UTAMA =================
$sql_booking = "SELECT parent_id FROM bookings WHERE id = ?";
$stmt_b = $conn->prepare($sql_booking);
$stmt_b->bind_param("i", $booking_id);
$stmt_b->execute();
$result_b = $stmt_b->get_result();
$booking_data = $result_b->fetch_assoc();

$parent_booking_id = $booking_data['parent_id'] ?? $booking_id;
$target_booking_id = $parent_booking_id; // Gunakan parent ID untuk semua query

// ================= AMBIL TOTAL TAGIHAN AKTUAL DARI DATABASE =================
$sql_total = "
SELECT 
    SUM(bs.harga) as subtotal_db,
    SUM(bs.diskon) as diskon_item_db,
    SUM(bs.harga - bs.diskon) as total_tagihan_db
FROM booking_services bs
JOIN bookings b ON bs.booking_id = b.id
WHERE b.parent_id = ? OR b.id = ?
";

$stmt_t = $conn->prepare($sql_total);
$stmt_t->bind_param("ii", $target_booking_id, $target_booking_id);
$stmt_t->execute();
$result_t = $stmt_t->get_result();
$tagihan_data = $result_t->fetch_assoc();

$subtotal_db = floatval($tagihan_data['subtotal_db'] ?? 0);
$diskon_item_db = floatval($tagihan_data['diskon_item_db'] ?? 0);
$total_tagihan_db = floatval($tagihan_data['total_tagihan_db'] ?? 0);
$total_diskon_transaksi = $diskon_item_db + $diskon_total;

// Gunakan nilai dari form jika valid, jika tidak gunakan dari database
$subtotal_final = ($subtotal > 0) ? $subtotal : $subtotal_db;
$total_tagihan_final = ($total_tagihan > 0) ? $total_tagihan : $total_tagihan_db;

// ================= HITUNG SUDAH DIBAYAR =================
$sql_sudah = "SELECT COALESCE(SUM(amount_paid), 0) as sudah 
              FROM payments 
              WHERE booking_id = ? AND status IN ('paid', 'partial')";

$stmt_s = $conn->prepare($sql_sudah);
$stmt_s->bind_param("i", $target_booking_id);
$stmt_s->execute();
$result_s = $stmt_s->get_result();
$sudah_data = $result_s->fetch_assoc();
$sudah_dibayar = floatval($sudah_data['sudah'] ?? 0);

// ================= HITUNG TOTAL METODE PEMBAYARAN =================
$total_metode = 0;
foreach ($payment_methods as $method) {
    $total_metode += floatval($method['amount'] ?? 0);
}

// Validasi
if ($total_metode <= 0) {
    die(json_encode(['error' => 'Total pembayaran harus lebih dari Rp 0']));
}

if ($total_metode > ($jumlah_bayar + 1000)) { // Toleransi 1000
    die(json_encode(['error' => 'Total metode pembayaran melebihi jumlah yang akan dibayar']));
}

$amount_paid = $total_metode;

// ================= HITUNG SISA =================
// Hitung total setelah diskon total (jika ada)
$total_setelah_diskon_total = $total_tagihan_final - $diskon_total;
if ($total_setelah_diskon_total < 0) $total_setelah_diskon_total = 0;

// Sisa sebelum pembayaran ini
$sisa_sebelum = $total_setelah_diskon_total - $sudah_dibayar;
if ($sisa_sebelum < 0) $sisa_sebelum = 0;

// Sisa setelah pembayaran ini
$sisa_setelah = $sisa_sebelum - $amount_paid;
if ($sisa_setelah < 0) $sisa_setelah = 0;

// ================= TENTUKAN STATUS =================
if ($sisa_setelah <= 100) { // Toleransi 100 rupiah
    $status = 'paid';
    $payment_type = 'full';
    $remaining_balance = 0;
} else {
    $status = 'partial';
    $payment_type = 'partial';
    $remaining_balance = $sisa_setelah;
}

// ================= PREPARE DATA LAINNYA =================
$metode_array = [];
foreach ($payment_methods as $method) {
    $metode_array[] = $method['metode'] ?? 'tunai';
}
$metode_gabungan = implode(' + ', $metode_array);

$jatuh_tempo = $_POST['jatuh_tempo'] ?? date('Y-m-d');

// ================= DEBUG VALUES =================
error_log("=== PAYMENT PROCESSING ===");
error_log("Booking ID: $booking_id");
error_log("Parent ID: $parent_booking_id");
error_log("Target Booking ID: $target_booking_id");
error_log("Subtotal (Form): $subtotal");
error_log("Subtotal (DB): $subtotal_db");
error_log("Subtotal (Final): $subtotal_final");
error_log("Diskon Total: $diskon_total");
error_log("Diskon Tipe: $diskon_tipe_value");
error_log("Total Tagihan (Form): $total_tagihan");
error_log("Total Tagihan (DB): $total_tagihan_db");
error_log("Total Tagihan (Final): $total_tagihan_final");
error_log("Total Setelah Diskon: $total_setelah_diskon_total");
error_log("Sudah Dibayar: $sudah_dibayar");
error_log("Amount Paid Now: $amount_paid");
error_log("Sisa Sebelum: $sisa_sebelum");
error_log("Sisa Setelah: $sisa_setelah");
error_log("Status: $status");
error_log("Payment Type: $payment_type");
error_log("Remaining Balance: $remaining_balance");

// ================= INSERT KE PAYMENTS =================
$sql_payment = "INSERT INTO payments (
    booking_id, 
    metode, 
    subtotal, 
    diskon, 
    diskon_tipe, 
    total, 
    amount_paid, 
    remaining_balance, 
    payment_type, 
    jatuh_tempo, 
    status,
    created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql_payment);

if (!$stmt) {
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}

// Bind parameter
$bind_result = $stmt->bind_param(
    "isddsddssss",
    $target_booking_id,
    $metode_gabungan,
    $subtotal_final,
    $total_diskon_transaksi,
    $diskon_tipe_value,
    $total_setelah_diskon_total,
    $amount_paid,
    $remaining_balance,
    $payment_type,
    $jatuh_tempo,
    $status
);

if (!$bind_result) {
    die(json_encode(['error' => 'Bind parameter gagal: ' . $stmt->error]));
}

// Execute statement
$execute_result = $stmt->execute();
if (!$execute_result) {
    $error_info = [
        'error' => 'Execute payment insert failed',
        'stmt_error' => $stmt->error,
        'errno' => $stmt->errno,
        'values' => [
            'booking_id' => $target_booking_id,
            'metode' => $metode_gabungan,
            'subtotal' => $subtotal_final,
            'diskon' => $diskon_total,
            'diskon_tipe' => $diskon_tipe_value,
            'total' => $total_setelah_diskon_total,
            'amount_paid' => $amount_paid,
            'remaining_balance' => $remaining_balance,
            'payment_type' => $payment_type,
            'jatuh_tempo' => $jatuh_tempo,
            'status' => $status
        ]
    ];
    error_log(print_r($error_info, true));
    die(json_encode($error_info));
}

$payment_id = $conn->insert_id;

// ================= INSERT DETAIL METODE =================
if (!empty($payment_methods)) {
    $sql_detail = "INSERT INTO payment_methods_detail 
                  (payment_id, metode, amount, reference) 
                  VALUES (?, ?, ?, ?)";
    
    $stmt_d = $conn->prepare($sql_detail);
    if (!$stmt_d) {
        error_log("Prepare detail failed: " . $conn->error);
    } else {
        foreach ($payment_methods as $method) {
            $method_metode = $method['metode'] ?? 'tunai';
            $method_amount = floatval($method['amount'] ?? 0);
            $method_reference = $method['reference'] ?? '';
            
            $stmt_d->bind_param("isds", $payment_id, $method_metode, $method_amount, $method_reference);
            $stmt_d->execute();
        }
        $stmt_d->close();
    }
}

// ================= CEK TOTAL BAYAR REAL =================
$sql_check = "SELECT COALESCE(SUM(amount_paid),0) as total_bayar
              FROM payments
              WHERE booking_id = ?
              AND status IN ('paid','partial')";

$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $target_booking_id);
$stmt_check->execute();
$total_bayar_real = $stmt_check->get_result()->fetch_assoc()['total_bayar'] ?? 0;

// Total tagihan final setelah diskon
$total_final = $subtotal_db - $diskon_item_db - $diskon_total;

if ($total_bayar_real >= $total_final) {
    $payment_status = 'paid';
    $booking_status = 'completed';
} else {
    $payment_status = 'partial';
    $booking_status = 'confirmed';
}

// Update parent booking
$sql_update = "UPDATE bookings 
               SET payment_status = ?, status = ? 
               WHERE id = ?";

$stmt_u = $conn->prepare($sql_update);
if ($stmt_u) {
    $stmt_u->bind_param("ssi", $payment_status, $booking_status, $target_booking_id);
    $stmt_u->execute();
    $stmt_u->close();
}

// Update child bookings jika ada
$sql_update_child = "UPDATE bookings 
                     SET payment_status = ?, status = ? 
                     WHERE parent_id = ?";

$stmt_uc = $conn->prepare($sql_update_child);
if ($stmt_uc) {
    $stmt_uc->bind_param("ssi", $payment_status, $booking_status, $target_booking_id);
    $stmt_uc->execute();
    $stmt_uc->close();
}

// ================= UPDATE DISKON PER ITEM (jika ada) =================
// Ambil service_id dari form jika ada
$service_ids = isset($_POST['service_id']) ? (array)$_POST['service_id'] : [];
$service_diskon = isset($_POST['service_diskon']) ? (array)$_POST['service_diskon'] : [];
$service_diskon_tipe = isset($_POST['service_diskon_tipe']) ? (array)$_POST['service_diskon_tipe'] : [];

if (!empty($service_ids) && count($service_ids) == count($service_diskon)) {
    $sql_update_diskon = "UPDATE booking_services 
                         SET diskon = ?, diskon_tipe = ?
                         WHERE id = ?";
    
    $stmt_diskon = $conn->prepare($sql_update_diskon);
    if ($stmt_diskon) {
        foreach ($service_ids as $index => $service_id) {
            if (isset($service_diskon[$index]) && isset($service_diskon_tipe[$index])) {
                $diskon_value = floatval($service_diskon[$index]);
                $tipe_value = trim($service_diskon_tipe[$index]);
                
                $stmt_diskon->bind_param("dsi", $diskon_value, $tipe_value, $service_id);
                $stmt_diskon->execute();
            }
        }
        $stmt_diskon->close();
    }
}

// ================= RESPONSE SUCCESS =================
$response = [
    'success' => true,
    'payment_id' => $payment_id,
    'status' => $status,
    'remaining_balance' => $remaining_balance,
    'message' => $status == 'paid' ? 'Pembayaran lunas' : 'Pembayaran sebagian',
    'redirect_url' => "pembayaran.php?id=$booking_id&success=1&payment_id=$payment_id"
];

// Debug response
error_log("Payment processed successfully. Payment ID: " . $payment_id);
error_log("Response: " . print_r($response, true));

// Close statements
if (isset($stmt)) $stmt->close();
if (isset($stmt_t)) $stmt_t->close();
if (isset($stmt_s)) $stmt_s->close();
if (isset($stmt_b)) $stmt_b->close();

// Return response
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header("Location: " . $response['redirect_url']);
}

exit;
?>