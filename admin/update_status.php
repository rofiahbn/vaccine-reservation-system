<?php
include "../config.php";

$booking_id = intval($_POST['booking_id'] ?? 0);
$status     = $_POST['status'] ?? '';

if ($booking_id == 0 || $status == '') {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$allowed = ['confirmed', 'cancelled', 'completed'];

if (in_array($status, $allowed)) {

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $booking_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }

    $stmt->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Status tidak diizinkan']);
}
