<?php 
include "config.php";

date_default_timezone_set('Asia/Jakarta');

// Cek apakah ada parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID produk tidak ditemukan";
    header("Location: products.php");
    exit;
}

$id = $_GET['id'];

// ================= AMBIL DATA PRODUK =================
// Ambil data untuk keperluan log dan validasi
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    $_SESSION['error'] = "Data produk tidak ditemukan";
    header("Location: products.php");
    exit;
}

$nama_produk = $product['nama_produk'];

// ================= CEK DEPENDENCIES =================
// Cek apakah produk ini digunakan sebagai komponen layanan
$check_layanan = "SELECT COUNT(*) as total FROM service_product_components WHERE product_id = ?";
$stmt_check = $conn->prepare($check_layanan);
$stmt_check->bind_param('i', $id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$used_in_layanan = $result_check->fetch_assoc()['total'] > 0;

if ($used_in_layanan) {
    $_SESSION['error'] = "Produk tidak dapat dihapus karena masih digunakan dalam layanan";
    header("Location: products.php");
    exit;
}

// ================= MULAI TRANSAKSI =================
$conn->begin_transaction();

try {
    // 1. Hapus stok produk (batch) dari tabel product_stock
    $delete_stock = "DELETE FROM product_stock WHERE product_id = ?";
    $stmt_stock = $conn->prepare($delete_stock);
    $stmt_stock->bind_param('i', $id);
    $stmt_stock->execute();
    
    // 2. Hapus data utama dari tabel products
    $delete_product = "DELETE FROM products WHERE id = ?";
    $stmt_product = $conn->prepare($delete_product);
    $stmt_product->bind_param('i', $id);
    $stmt_product->execute();
    
    // Cek apakah berhasil dihapus
    if ($stmt_product->affected_rows > 0) {
        $conn->commit();
        $_SESSION['success'] = "Produk \"$nama_produk\" berhasil dihapus";
    } else {
        throw new Exception("Gagal menghapus data produk");
    }
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Gagal menghapus: " . $e->getMessage();
}

// ================= REDIRECT =================
header("Location: products.php");
exit;
?>