<?php
session_start();
include "../config.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Get form data
    $jenis = isset($_POST['jenis']) ? trim($_POST['jenis']) : null;
    $nama_layanan = trim($_POST['nama_layanan']);
    $kategori = trim($_POST['kategori']);
    $deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : null;
    $harga = floatval($_POST['harga']);
    $harga_special = isset($_POST['harga_special']) && $_POST['harga_special'] !== '' ? floatval($_POST['harga_special']) : null;
    $harga_diskon = isset($_POST['harga_diskon']) && $_POST['harga_diskon'] !== '' ? floatval($_POST['harga_diskon']) : null;
    $stock = isset($_POST['stock']) && $_POST['stock'] !== '' ? intval($_POST['stock']) : null;
    $low_stock = isset($_POST['low_stock']) && $_POST['low_stock'] !== '' ? intval($_POST['low_stock']) : null;
    $batch_number = isset($_POST['batch_number']) ? trim($_POST['batch_number']) : null;
    $expired_date = isset($_POST['expired_date']) && $_POST['expired_date'] !== '' ? $_POST['expired_date'] : null;
    $periode_diskon = isset($_POST['periode_diskon']) ? trim($_POST['periode_diskon']) : null;
    
    // Handle image upload
    $image_path = null;
    if (isset($_POST['image_data']) && !empty($_POST['image_data'])) {
        $image_data = $_POST['image_data'];
        
        // Extract base64 data
        if (preg_match('/^data:image\/(\w+);base64,/', $image_data, $type)) {
            $image_data = substr($image_data, strpos($image_data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif
            
            // Decode base64
            $image_data = base64_decode($image_data);
            
            if ($image_data !== false) {
                // Create uploads directory if not exists
                $upload_dir = '../uploads/products/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $filename = 'product_' . uniqid() . '.' . $type;
                $filepath = $upload_dir . $filename;
                
                // Save file
                if (file_put_contents($filepath, $image_data)) {
                    $image_path = 'uploads/products/' . $filename;
                }
            }
        }
    }

    // Validation
    if (empty($nama_layanan) || empty($kategori) || $harga <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Nama produk, kategori, dan harga wajib diisi dengan benar'
        ]);
        exit;
    }

    try {
        if ($action === 'edit' && $id > 0) {
            // Update existing product
            if ($image_path) {
                // With new image
                $sql = "UPDATE services SET 
                        jenis = ?,
                        nama_layanan = ?,
                        kategori = ?,
                        deskripsi = ?,
                        harga = ?,
                        harga_special = ?,
                        harga_diskon = ?,
                        stock = ?,
                        low_stock = ?,
                        batch_number = ?,
                        expired_date = ?,
                        periode_diskon = ?,
                        image_path = ?
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssddiiissssi', 
                    $jenis,
                    $nama_layanan, 
                    $kategori, 
                    $deskripsi, 
                    $harga, 
                    $harga_special, 
                    $harga_diskon, 
                    $stock,
                    $low_stock,
                    $batch_number, 
                    $expired_date,
                    $periode_diskon,
                    $image_path,
                    $id
                );
            } else {
                // Without new image
                $sql = "UPDATE services SET 
                        jenis = ?,
                        nama_layanan = ?,
                        kategori = ?,
                        deskripsi = ?,
                        harga = ?,
                        harga_special = ?,
                        harga_diskon = ?,
                        stock = ?,
                        low_stock = ?,
                        batch_number = ?,
                        expired_date = ?,
                        periode_diskon = ?
                        WHERE id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssssddiissssi', 
                    $jenis,
                    $nama_layanan, 
                    $kategori, 
                    $deskripsi, 
                    $harga, 
                    $harga_special, 
                    $harga_diskon, 
                    $stock,
                    $low_stock,
                    $batch_number, 
                    $expired_date,
                    $periode_diskon,
                    $id
                );
            }
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Produk berhasil diupdate'
                ]);
            } else {
                throw new Exception('Gagal mengupdate produk');
            }
        } else {
            // Insert new product
            $sql = "INSERT INTO services (
                    jenis,
                    nama_layanan, 
                    kategori, 
                    deskripsi, 
                    harga, 
                    harga_special, 
                    harga_diskon, 
                    stock,
                    low_stock,
                    batch_number, 
                    expired_date,
                    periode_diskon,
                    image_path,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssddiisssss', 
                $jenis,
                $nama_layanan, 
                $kategori, 
                $deskripsi, 
                $harga, 
                $harga_special, 
                $harga_diskon, 
                $stock,
                $low_stock,
                $batch_number, 
                $expired_date,
                $periode_diskon,
                $image_path
            );
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Produk berhasil ditambahkan'
                ]);
            } else {
                throw new Exception('Gagal menambahkan produk');
            }
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>