<?php
include "../config.php";

$booking_id = intval($_POST['booking_id']);
$doctor_ids = isset($_POST['doctor_ids']) ? explode(',', $_POST['doctor_ids']) : [];

if($booking_id && !empty($doctor_ids)) {
    foreach($doctor_ids as $doctor_id) {
        $doctor_id = intval($doctor_id);
        // cek dulu kalau belum ada di booking_staff
        $check = $conn->prepare("SELECT * FROM booking_staff WHERE booking_id=? AND staff_id=?");
        $check->bind_param("ii", $booking_id, $doctor_id);
        $check->execute();
        if($check->get_result()->num_rows == 0){
            $stmt = $conn->prepare("INSERT INTO booking_staff (booking_id, staff_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $booking_id, $doctor_id);
            $stmt->execute();
        }
    }
    echo "success";
} else {
    echo "failed";
}
