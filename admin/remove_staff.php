<?php
include "../config.php";

$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$staff_id   = isset($_POST['staff_id']) ? intval($_POST['staff_id']) : 0;

if($booking_id && $staff_id){
    $stmt = $conn->prepare("DELETE FROM booking_staff WHERE booking_id = ? AND staff_id = ?");
    $stmt->bind_param('ii', $booking_id, $staff_id);
    if($stmt->execute()){
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false]);
    }
} else {
    echo json_encode(['success'=>false]);
}
