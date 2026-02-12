<?php
session_start();
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$action = $_POST['action'] ?? 'add';

// ========== VALIDASI INPUT ==========
$kode_produk = trim($_POST['kode_produk'] ?? '');
$nama_produk = trim($_POST['nama_produk'] ?? ''); 
$jenis = $_POST['jenis'] ?? '';
$kategori = $_POST['kategori'] ?? '';
$satuan = $_POST['satuan'] ?? 'dosis';
$harga = $_POST['harga'] ?? 0;
$minimal_stok = $_POST['minimal_stok'] ?? 10;
$deskripsi = $_POST['deskripsi'] ?? '';

// Validasi wajib
if (empty($kode_produk) || empty($nama_produk) || empty($jenis) || empty($kategori) || empty($harga)) {
    $_SESSION['error'] = 'Kode produk, nama produk, jenis, kategori, dan harga wajib diisi';
    header('Location: add_product.php');
    exit;
}

// ========== INSERT / UPDATE ==========
if ($action == 'add') {
    // Cek duplikasi kode produk
    $check = $conn->prepare("SELECT id FROM products WHERE kode_produk = ?");
    $check->bind_param("s", $kode_produk);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $_SESSION['error'] = 'Kode produk sudah digunakan';
        header('Location: add_product.php');
        exit;
    }
    $check->close();
    
    // Insert produk baru - 8 parameter
    $sql = "INSERT INTO products (
        kode_produk, 
        nama_produk, 
        jenis, 
        kategori, 
        satuan, 
        harga, 
        minimal_stok, 
        deskripsi, 
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    // Format: s = string, i = integer
    // kode_produk(s), nama_produk(s), jenis(s), kategori(s), satuan(s), harga(i), minimal_stok(i), deskripsi(s)
    $stmt->bind_param("sssssiis", 
        $kode_produk, 
        $nama_produk, 
        $jenis, 
        $kategori, 
        $satuan, 
        $harga, 
        $minimal_stok, 
        $deskripsi
    );
    
} else {
    // Update produk (untuk nanti di edit_product.php)
    $id = $_POST['id'] ?? 0;
    
    $sql = "UPDATE products SET 
        kode_produk = ?,
        nama_produk = ?,
        jenis = ?,
        kategori = ?,
        satuan = ?,
        harga = ?,
        minimal_stok = ?,
        deskripsi = ?
        WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    // 8 parameter + 1 where id = 9 parameter
    $stmt->bind_param("sssssiisi", 
        $kode_produk, 
        $nama_produk, 
        $jenis, 
        $kategori, 
        $satuan, 
        $harga, 
        $minimal_stok, 
        $deskripsi, 
        $id
    );
}

if (!$stmt->execute()) {
    $_SESSION['error'] = 'Gagal menyimpan: ' . $conn->error;
    header('Location: ' . ($action == 'add' ? 'add_product.php' : 'edit_product.php?id=' . $id));
    exit;
}

// ========== REDIRECT ==========
$_SESSION['success'] = 'Produk berhasil ' . ($action == 'add' ? 'ditambahkan' : 'diperbarui');
header('Location: products.php');
exit;