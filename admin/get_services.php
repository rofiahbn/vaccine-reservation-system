<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

// Ambil layanan dari tabel services
// Bisa menampilkan pelayanan DAN paket
$sql = "SELECT id, nama_layanan, harga, deskripsi, tipe, kategori_usia 
        FROM services 
        WHERE tipe IN ('pelayanan', 'paket') 
        ORDER BY 
            CASE tipe 
                WHEN 'pelayanan' THEN 1 
                WHEN 'paket' THEN 2 
                ELSE 3 
            END,
            nama_layanan ASC";

$result = mysqli_query($conn, $sql);

if ($result) {
    $services = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Tambah icon berdasarkan tipe
        if ($row['tipe'] == 'pelayanan') {
            $row['icon'] = 'syringe';
        } elseif ($row['tipe'] == 'paket') {
            $row['icon'] = 'box';
        } else {
            $row['icon'] = 'tag';
        }
        
        $services[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $services,
        'total' => count($services)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Query error: ' . mysqli_error($conn)
    ]);
}
?>