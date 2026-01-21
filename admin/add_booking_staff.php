<?php
include "../config.php";

$booking_id = $_POST['booking_id'];
$staff_id   = $_POST['staff_id'];

$sql = "INSERT INTO booking_staff (booking_id, staff_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $staff_id);
$stmt->execute();

header("Location: detail.php?id=$booking_id");
exit;
