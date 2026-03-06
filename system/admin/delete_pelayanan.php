<?php 
include "config.php";

date_default_timezone_set('Asia/Jakarta');

// Cek apakah ada parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID tidak ditemukan";
    header("Location: products_pelayanan.php");
    exit;
}

$id = $_GET['id'];

// ================= AMBIL DATA SERVICE =================
// Ambil data untuk keperluan log dan validasi
$sql = "SELECT * FROM services WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service) {
    $_SESSION['error'] = "Data tidak ditemukan";
    header("Location: products_pelayanan.php");
    exit;
}

$tipe = $service['tipe'];
$nama = $service['nama_layanan'];

// ================= MULAI TRANSAKSI =================
$conn->begin_transaction();

try {
    // 1. Hapus komponen terkait berdasarkan tipe
    if ($tipe === 'pelayanan') {
        // Hapus komponen PRODUK
        $delete_produk = "DELETE FROM service_product_components WHERE service_id = ?";
        $stmt_produk = $conn->prepare($delete_produk);
        $stmt_produk->bind_param('i', $id);
        $stmt_produk->execute();
        
        // Hapus komponen JASA
        $delete_jasa = "DELETE FROM service_jasa_components WHERE service_id = ?";
        $stmt_jasa = $conn->prepare($delete_jasa);
        $stmt_jasa->bind_param('i', $id);
        $stmt_jasa->execute();
        
    } elseif ($tipe === 'paket') {
        // Hapus item paket
        $delete_items = "DELETE FROM service_package_items WHERE package_id = ?";
        $stmt_items = $conn->prepare($delete_items);
        $stmt_items->bind_param('i', $id);
        $stmt_items->execute();
    }
    
    // 2. Hapus data utama dari tabel services
    $delete_service = "DELETE FROM services WHERE id = ?";
    $stmt_service = $conn->prepare($delete_service);
    $stmt_service->bind_param('i', $id);
    $stmt_service->execute();
    
    // Cek apakah berhasil dihapus
    if ($stmt_service->affected_rows > 0) {
        $conn->commit();
        $_SESSION['success'] = ucfirst($tipe) . " \"$nama\" berhasil dihapus";
    } else {
        throw new Exception("Gagal menghapus data utama");
    }
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Gagal menghapus: " . $e->getMessage();
}

// ================= REDIRECT =================
header("Location: products_pelayanan.php");
exit;
?>