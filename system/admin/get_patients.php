<?php 
include "config.php";

header('Content-Type: application/json');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT id, nama_lengkap, tanggal_lahir, usia, jenis_kelamin, no_rekam_medis 
        FROM patients 
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (nama_lengkap LIKE ? OR no_rekam_medis LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$sql .= " ORDER BY nama_lengkap ASC LIMIT 20";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $patients = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $patients[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $patients
    ]);
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Query error'
    ]);
}
?>