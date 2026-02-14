<?php
session_start();
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products_pelayanan.php');
    exit;
}

$tipe = $_POST['tipe'] ?? 'pelayanan';
$nama_layanan = trim($_POST['nama_layanan'] ?? '');
$kategori_usia = $_POST['kategori_usia'] ?? '';
$harga = str_replace('.', '', $_POST['harga'] ?? 0); // Hapus titik jika ada format rupiah
$deskripsi = $_POST['deskripsi'] ?? '';
$kode_paket = $_POST['kode_paket'] ?? '';

// Validasi
if (empty($nama_layanan) || empty($kategori_usia) || empty($harga)) {
    $_SESSION['error'] = 'Nama, kategori usia, dan harga wajib diisi';
    header('Location: add_pelayanan.php?tipe=' . $tipe);
    exit;
}

// ========== INSERT KE TABEL SERVICES ==========
if ($tipe == 'pelayanan') {
    // Untuk layanan (pelayanan)
    $sql = "INSERT INTO services (
        nama_layanan, 
        kategori_usia, 
        tipe, 
        harga, 
        deskripsi,
        created_at,
        updated_at
    ) VALUES (?, ?, 'pelayanan', ?, ?, NOW(), NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssis", 
        $nama_layanan, 
        $kategori_usia, 
        $harga, 
        $deskripsi
    );
    
} else { // paket
    $sql = "INSERT INTO services (
        nama_layanan, 
        kategori_usia, 
        tipe, 
        harga, 
        kode_paket, 
        deskripsi,
        created_at,
        updated_at
    ) VALUES (?, ?, 'paket', ?, ?, ?, NOW(), NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiss", 
        $nama_layanan, 
        $kategori_usia, 
        $harga, 
        $kode_paket, 
        $deskripsi
    );
}

if (!$stmt->execute()) {
    $_SESSION['error'] = 'Gagal menyimpan: ' . $conn->error;
    header('Location: add_pelayanan.php?tipe=' . $tipe);
    exit;
}

$service_id = $stmt->insert_id;

// ========== INSERT KOMPONEN LAYANAN (untuk pelayanan) ==========
if ($tipe == 'pelayanan' && $service_id && isset($_POST['components'])) {
    $components = $_POST['components'];
    
    // Pisahkan komponen produk dan jasa
    $produk_components = [];
    $jasa_components = [];
    
    foreach ($components as $comp) {
        $id = $comp['id'] ?? '';
        $qty = $comp['qty'] ?? 1;
        $tipe_komponen = $comp['tipe'] ?? '';
        
        if (empty($id) || empty($tipe_komponen)) continue;
        
        if ($tipe_komponen === 'produk') {
            $produk_components[] = [
                'product_id' => $id,
                'quantity' => $qty
            ];
        } elseif ($tipe_komponen === 'jasa') {
            $jasa_components[] = [
                'jasa_id' => $id,
                'quantity' => $qty
            ];
        }
    }
    
    // Insert komponen PRODUK ke tabel service_product_components
    if (!empty($produk_components)) {
        $produk_sql = "INSERT INTO service_product_components (service_id, product_id, quantity, created_at, updated_at) 
                       VALUES (?, ?, ?, NOW(), NOW())";
        $produk_stmt = $conn->prepare($produk_sql);
        
        foreach ($produk_components as $comp) {
            $produk_stmt->bind_param("iii", $service_id, $comp['product_id'], $comp['quantity']);
            $produk_stmt->execute();
        }
    }
    
    // Insert komponen JASA ke tabel service_jasa_components
    if (!empty($jasa_components)) {
        $jasa_sql = "INSERT INTO service_jasa_components (service_id, jasa_id, quantity, created_at, updated_at) 
                     VALUES (?, ?, ?, NOW(), NOW())";
        $jasa_stmt = $conn->prepare($jasa_sql);
        
        foreach ($jasa_components as $comp) {
            $jasa_stmt->bind_param("iii", $service_id, $comp['jasa_id'], $comp['quantity']);
            $jasa_stmt->execute();
        }
    }
}

// ========== INSERT ITEM PAKET (untuk paket) ==========
if ($tipe == 'paket' && $service_id && isset($_POST['package_items'])) {
    $package_items = $_POST['package_items'];
    
    $item_sql = "INSERT INTO service_package_items 
                  (package_id, service_id, quantity, visit_order, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
    $item_stmt = $conn->prepare($item_sql);
    
    foreach ($package_items as $item) {
        $service_id_item = $item['id'] ?? 0;
        $visit_order = $item['visit_order'] ?? 1;
        $quantity = $item['qty'] ?? 1;
        
        if (!empty($service_id_item)) {
            $item_stmt->bind_param("iiii", $service_id, $service_id_item, $quantity, $visit_order);
            $item_stmt->execute();
        }
    }
}

// ========== REDIRECT ==========
$_SESSION['success'] = ucfirst($tipe) . ' berhasil ditambahkan';
header('Location: products_pelayanan.php');
exit;