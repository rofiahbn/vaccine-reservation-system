<?php 
include "config.php";

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input'
    ]);
    exit;
}

// Validasi data
$nama_layanan = trim($data['nama_layanan'] ?? '');
$kode_layanan = trim($data['kode_layanan'] ?? '');
$kategori_usia = trim($data['kategori_usia'] ?? '');
$deskripsi = trim($data['deskripsi'] ?? '');
$harga = isset($data['harga']) ? intval($data['harga']) : 0;
$kode_paket = trim($data['kode_paket'] ?? '');
$tipe = trim($data['tipe'] ?? 'jasa'); // Default jasa
$id = isset($data['id']) && !empty($data['id']) ? intval($data['id']) : null;

// Validasi required fields
if (empty($nama_layanan)) {
    echo json_encode([
        'success' => false,
        'message' => 'Nama layanan harus diisi'
    ]);
    exit;
}

if ($harga <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Harga harus lebih dari 0'
    ]);
    exit;
}

try {
    if ($id) {
        // UPDATE
        $sql = "UPDATE services 
                SET nama_layanan = ?, 
                    kode_layanan = ?, 
                    kategori_usia = ?, 
                    deskripsi = ?, 
                    harga = ?, 
                    kode_paket = ?,
                    tipe = ?,
                    updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!$stmt) {
            throw new Exception('Prepare error: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'ssssissi', 
            $nama_layanan, 
            $kode_layanan, 
            $kategori_usia, 
            $deskripsi, 
            $harga, 
            $kode_paket,
            $tipe,
            $id
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Execute error: ' . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true,
            'message' => 'Service berhasil diupdate',
            'data' => [
                'id' => $id,
                'nama_layanan' => $nama_layanan,
                'kode_layanan' => $kode_layanan,
                'kategori_usia' => $kategori_usia,
                'harga' => $harga,
                'tipe' => $tipe
            ]
        ]);
        
    } else {
        // INSERT
        $sql = "INSERT INTO services (nama_layanan, kode_layanan, kategori_usia, deskripsi, harga, kode_paket, tipe, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!$stmt) {
            throw new Exception('Prepare error: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'ssssiss', 
            $nama_layanan, 
            $kode_layanan, 
            $kategori_usia, 
            $deskripsi, 
            $harga, 
            $kode_paket,
            $tipe
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Execute error: ' . mysqli_stmt_error($stmt));
        }
        
        $new_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true,
            'message' => 'Service baru berhasil ditambahkan',
            'data' => [
                'id' => $new_id,
                'nama_layanan' => $nama_layanan,
                'kode_layanan' => $kode_layanan,
                'kategori_usia' => $kategori_usia,
                'harga' => $harga,
                'tipe' => $tipe
            ]
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);
?>