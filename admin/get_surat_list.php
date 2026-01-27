<?php
include "../config.php";

header('Content-Type: application/json');

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if ($booking_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit;
}

$sql = "
SELECT s.*, 
       CONCAT(st.gelar, ' ', st.nama_lengkap) AS dokter_nama,
       DATE_FORMAT(s.tanggal_surat, '%d %M %Y') AS tanggal_surat
FROM surat s
LEFT JOIN staff st ON s.dokter_id = st.id
WHERE s.booking_id = ?
ORDER BY s.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

$surat = [];
while ($row = $result->fetch_assoc()) {
    $surat[] = $row;
}

echo json_encode([
    'success' => true,
    'surat' => $surat
]);