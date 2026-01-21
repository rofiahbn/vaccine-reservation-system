<?php
include "../config.php";

$booking_id = intval($_POST['booking_id']);
$status     = $_POST['status'] ?? '';

if(in_array($status, ['confirmed','cancelled'])){
    $stmt = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
