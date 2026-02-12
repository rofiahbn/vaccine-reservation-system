<?php
session_start();
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products_pelayanan.php');
    exit;
}

$tipe = $_POST['tipe'] ?? 'pelayanan';
$nama_layanan = trim($_POST['nama_layanan'] ?? '');
$kategori_usia = $_POST['kategori_usia'] ?? ''; // Anak, Dewasa, Semua Usia
$harga = $_POST['harga'] ?? 0;
$deskripsi = $_POST['deskripsi'] ?? '';

// Validasi
if (empty($nama_layanan) || empty($kategori_usia) || empty($harga)) {
    $_SESSION['error'] = 'Nama, kategori usia, dan harga wajib diisi';
    header('Location: add_pelayanan.php?tipe=' . $tipe);
    exit;
}

// ========== INSERT KE TABEL SERVICES ==========
if ($tipe == 'pelayanan') {
    $durasi_layanan = $_POST['durasi_layanan'] ?? null;
    
    $sql = "INSERT INTO services (
        nama_layanan, 
        tipe, 
        kategori_usia, 
        harga, 
        durasi_layanan, 
        deskripsi, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiss", 
        $nama_layanan, 
        $tipe,           // 'pelayanan'
        $kategori_usia, 
        $harga, 
        $durasi_layanan, 
        $deskripsi
    );
    
} else { // paket
    $kode_paket = $_POST['kode_paket'] ?? null;
    
    $sql = "INSERT INTO services (
        nama_layanan, 
        tipe, 
        kategori_usia, 
        harga, 
        kode_paket, 
        deskripsi, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiss", 
        $nama_layanan, 
        $tipe,           // 'paket'
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

// ========== INSERT KOMPONEN LAYANAN (UNTUK PELAYANAN) ==========
// Komponen layanan merujuk ke tabel products (bukan services)
if ($tipe == 'pelayanan' && $service_id) {
    $component_ids = $_POST['component_id'] ?? [];
    $component_customs = $_POST['component_custom'] ?? [];
    $component_qty = $_POST['component_qty'] ?? [];
    $component_type = $_POST['component_type'] ?? [];
    
    $comp_sql = "INSERT INTO service_components 
                  (service_id, product_id, quantity) 
                  VALUES (?, ?, ?)";
    $comp_stmt = $conn->prepare($comp_sql);
    
    for ($i = 0; $i < count($component_qty); $i++) {
        $qty = $component_qty[$i] ?? 1;
        $product_id = null;
        
        if (isset($component_ids[$i]) && !empty($component_ids[$i]) && $component_ids[$i] !== 'custom') {
            // Ambil product_id dari dropdown
            $product_id = $component_ids[$i];
            
            $comp_stmt->bind_param("iii", $service_id, $product_id, $qty);
            $comp_stmt->execute();
        }
    }
    
    // Jasa tenaga medis otomatis ditambahkan?
    // Jika tidak dikirim dari form, bisa ditambahkan default di sini
}

// ========== INSERT ITEM PAKET (UNTUK PAKET) ==========
// Item paket merujuk ke layanan (services dengan tipe 'pelayanan')
if ($tipe == 'paket' && $service_id) {
    $package_service_ids = $_POST['package_service_id'] ?? [];
    $package_qty = $_POST['package_qty'] ?? [];
    $package_visit_order = $_POST['package_visit_order'] ?? [];
    
    $item_sql = "INSERT INTO service_package_items 
                  (package_id, service_id, quantity, visit_order) 
                  VALUES (?, ?, ?, ?)";
    $item_stmt = $conn->prepare($item_sql);
    
    for ($i = 0; $i < count($package_service_ids); $i++) {
        $service_id_item = $package_service_ids[$i] ?? 0;
        $qty = $package_qty[$i] ?? 1;
        $visit_order = $package_visit_order[$i] ?? ($i + 1);
        
        if (!empty($service_id_item)) {
            $item_stmt->bind_param("iiii", $service_id, $service_id_item, $qty, $visit_order);
            $item_stmt->execute();
        }
    }
}

// ========== REDIRECT ==========
$_SESSION['success'] = ucfirst($tipe) . ' berhasil ditambahkan';
header('Location: products_pelayanan.php');
exit;