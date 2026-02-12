<?php
session_start();
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products_pelayanan.php');
    exit;
}

$tipe = $_POST['tipe'] ?? 'pelayanan';
$nama_layanan = trim($_POST['nama_layanan'] ?? '');
$kategori = $_POST['kategori'] ?? '';
$harga = $_POST['harga'] ?? 0;
$deskripsi = $_POST['deskripsi'] ?? '';

// Validasi
if (empty($nama_layanan) || empty($kategori) || empty($harga)) {
    $_SESSION['error'] = 'Nama, kategori, dan harga wajib diisi';
    header('Location: add_pelayanan.php?tipe=' . $tipe);
    exit;
}

// ========== INSERT KE TABEL SERVICES ==========
if ($tipe == 'pelayanan') {
    $durasi_layanan = $_POST['durasi_layanan'] ?? null;
    $sql = "INSERT INTO services (
        nama_layanan, 
        product_category, 
        kategori, 
        harga, 
        durasi_layanan, 
        deskripsi,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiss", 
        $nama_layanan, 
        $tipe, 
        $kategori, 
        $harga, 
        $durasi_layanan, 
        $deskripsi
    );
    
} else { // paket
    $kode_paket = $_POST['kode_paket'] ?? null;
    $sql = "INSERT INTO services (
        nama_layanan, 
        product_category, 
        kategori, 
        harga, 
        kode_paket, 
        deskripsi,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiss", 
        $nama_layanan, 
        $tipe, 
        $kategori, 
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

// ========== INSERT KOMPONEN LAYANAN ==========
if ($tipe == 'pelayanan' && $service_id) {
    $component_ids = $_POST['component_id'] ?? [];
    $component_customs = $_POST['component_custom'] ?? [];
    $component_qty = $_POST['component_qty'] ?? [];
    $component_type = $_POST['component_type'] ?? [];
    
    $comp_sql = "INSERT INTO service_components 
                  (service_id, component_name, component_type, quantity) 
                  VALUES (?, ?, ?, ?)";
    $comp_stmt = $conn->prepare($comp_sql);
    
    for ($i = 0; $i < count($component_qty); $i++) {
        $qty = $component_qty[$i] ?? 1;
        $type = $component_type[$i] ?? 'vaksin';
        $name = '';
        
        // Cek apakah pilih dari dropdown atau manual
        if (isset($component_ids[$i]) && !empty($component_ids[$i]) && $component_ids[$i] !== 'custom') {
            // Ambil nama produk dari database
            $prod_id = $component_ids[$i];
            $prod_query = "SELECT nama_layanan FROM services WHERE id = ?";
            $prod_stmt = $conn->prepare($prod_query);
            $prod_stmt->bind_param("i", $prod_id);
            $prod_stmt->execute();
            $prod_result = $prod_stmt->get_result();
            $prod = $prod_result->fetch_assoc();
            $name = $prod['nama_layanan'] ?? 'Produk';
        } else {
            // Manual input
            $name = $component_customs[$i] ?? '';
        }
        
        if (!empty($name)) {
            $comp_stmt->bind_param("issi", $service_id, $name, $type, $qty);
            $comp_stmt->execute();
        }
    }
}

// ========== INSERT ITEM PAKET ==========
if ($tipe == 'paket' && $service_id) {
    $package_service_ids = $_POST['package_service_id'] ?? [];
    $package_visit_order = $_POST['package_visit_order'] ?? [];
    $package_qty = $_POST['package_qty'] ?? [];
    
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