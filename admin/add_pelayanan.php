<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

// ================= CEK TIPE =================
$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'pelayanan';
if (!in_array($tipe, ['pelayanan', 'paket'])) {
    $tipe = 'pelayanan';
}

// ================= AMBIL DATA UNTUK DROPDOWN =================
// Ambil daftar layanan (untuk pilihan item paket) - dari tabel services
$sql_services = "SELECT id, nama_layanan, harga FROM services WHERE tipe = 'pelayanan' ORDER BY nama_layanan ASC";
$services_result = $conn->query($sql_services);

// 🔴 PERBAIKAN: Ambil daftar vaksin/produk dari tabel products (BUKAN dari services)
$sql_products = "SELECT 
    id, 
    nama_produk AS nama_layanan, 
    harga_jual AS harga
    FROM products
    ORDER BY nama_produk ASC";
$products_result = $conn->query($sql_products);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tipe == 'pelayanan' ? 'Tambah Layanan' : 'Tambah Paket' ?> - Vaksinin</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="css/add-pelayanan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <img src="vaksinin-logo.png" class="logo-full">
        <img src="v-logo.png" class="logo-icon">
    </div>
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-th-large"></i><span>Dashboard</span>
        </a>
        <a href="javascript:void(0)" class="nav-item has-submenu active open" onclick="toggleSubmenu(this)">
            <i class="fas fa-capsules"></i><span>Produk</span>
            <i class="fas fa-chevron-down arrow"></i>
        </a>
        <ul class="submenu open">
            <li><a href="products.php">Vaksin & Obat</a></li>
            <li><a href="products_pelayanan.php" class="active">Pelayanan/Paket</a></li>
        </ul>
        <a href="patients.php" class="nav-item">
            <i class="fas fa-users"></i><span>Pasien</span>
        </a>
        <a href="calendar_setting.php" class="nav-item">
            <i class="fas fa-calendar"></i><span>Kalender</span>
        </a>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">

    <!-- Header -->
    <div class="page-header">
        <a href="products_pelayanan.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h1><?= $tipe == 'pelayanan' ? 'Tambah Layanan' : 'Tambah Paket' ?></h1>
    </div>

    <!-- Info Note -->
    <div class="info-note info-note-<?= $tipe ?>">
        <i class="fas fa-info-circle"></i>
        <?php if ($tipe == 'pelayanan'): ?>
            <span><strong>Layanan</strong> = 1x kunjungan (vaksin/obat + jasa) dalam 1 harga.</span>
        <?php else: ?>
            <span><strong>Paket</strong> = Bundling beberapa layanan untuk beberapa kali kunjungan.</span>
        <?php endif; ?>
    </div>

    <!-- Form -->
    <form id="formTambah" action="proses_simpan_pelayanan.php" method="POST">
        <input type="hidden" name="tipe" value="<?= $tipe ?>">
        
        <!-- Informasi Dasar -->
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-info-circle"></i>
                <h3>Informasi Dasar</h3>
            </div>
            <div class="form-card-body">
                <!-- Nama Layanan/Paket -->
                <div class="form-group">
                    <label>Nama <?= $tipe == 'pelayanan' ? 'Layanan' : 'Paket' ?> <span class="required">*</span></label>
                    <input type="text" 
                           name="nama_layanan" 
                           class="form-control"
                           placeholder="Contoh: <?= $tipe == 'pelayanan' ? 'Vaksinasi Influenza' : 'Paket Vaksinasi HPV 3 Dosis' ?>"
                           required>
                </div>
                
                <!-- Kategori Usia -->
                <div class="form-group">
                    <label>Kategori Usia <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="kategori_usia" value="Anak" required>
                            <span>Anak (0-18 tahun)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="kategori_usia" value="Dewasa">
                            <span>Dewasa (>18 tahun)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="kategori_usia" value="Semua Usia">
                            <span>Semua Usia</span>
                        </label>
                    </div>
                </div>
                
                <!-- Harga -->
                <div class="form-group">
                    <label>Harga <?= $tipe == 'pelayanan' ? 'Layanan' : 'Paket' ?> <span class="required">*</span></label>
                    <div class="price-input">
                        <span class="price-prefix">Rp</span>
                        <input type="number" 
                               name="harga" 
                               class="form-control"
                               placeholder="0"
                               min="0"
                               step="1000"
                               required>
                    </div>
                </div>
                
                <!-- Kode Paket (khusus paket) -->
                <?php if ($tipe == 'paket'): ?>
                <div class="form-group">
                    <label>Kode Paket (Opsional)</label>
                    <input type="text" 
                           name="kode_paket" 
                           class="form-control"
                           placeholder="Contoh: HPV-3X, FLU-2X">
                </div>
                <?php endif; ?>
                
                <!-- Deskripsi -->
                <div class="form-group">
                    <label>Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" 
                              class="form-control"
                              rows="3"
                              placeholder="<?= $tipe == 'pelayanan' ? 'Contoh: Vaksin influenza untuk perlindungan musiman' : 'Contoh: Paket 3 dosis vaksin HPV dengan harga hemat' ?>"></textarea>
                </div>
            </div>
        </div>
        
        <!-- Komposisi Layanan (khusus layanan) -->
        <?php if ($tipe == 'pelayanan'): ?>
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-puzzle-piece"></i>
                <h3>Komposisi Layanan</h3>
            </div>
            <div class="form-card-body">
                <p class="form-description">
                    Pilih vaksin/obat yang digunakan dalam layanan ini
                </p>
                
                <div id="components-container" class="components-container"></div>
                
                <button type="button" class="btn-add" id="btnAddComponent">
                    <i class="fas fa-plus"></i> Tambah Komponen
                </button>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <span>Jasa tenaga medis akan otomatis ditambahkan</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Isi Paket (khusus paket) -->
        <?php if ($tipe == 'paket'): ?>
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-boxes"></i>
                <h3>Isi Paket</h3>
            </div>
            <div class="form-card-body">
                <p class="form-description">
                    Pilih layanan yang termasuk dalam paket ini
                </p>
                
                <div id="package-items-container" class="package-items-container"></div>
                
                <button type="button" class="btn-add" id="btnAddPackageItem">
                    <i class="fas fa-plus"></i> Tambah Item Paket
                </button>
                
                <div class="price-summary">
                    <div class="summary-row">
                        <span>Total Harga Normal:</span>
                        <span class="summary-value" id="totalNormalPrice">Rp 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Harga Paket:</span>
                        <span class="summary-value highlight" id="hargaPaketDisplay">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="form-actions">
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="products_pelayanan.php" class="btn-cancel">
                Batal
            </a>
        </div>
    </form>

</div>

<!-- Data untuk JavaScript -->
<script id="product-options-data" type="application/json">
<?php
// Reset pointer
if ($products_result && $products_result->num_rows > 0) {
    $products_result->data_seek(0);
    $product_options = [];
    while($prod = $products_result->fetch_assoc()) {
        $product_options[] = [
            'id' => $prod['id'],
            'name' => $prod['nama_layanan'],
            'price' => $prod['harga']
        ];
    }
    echo json_encode($product_options);
} else {
    echo json_encode([]);
}
?>
</script>

<script id="service-options-data" type="application/json">
<?php
// Reset pointer
if ($services_result && $services_result->num_rows > 0) {
    $services_result->data_seek(0);
    $service_options = [];
    while($service = $services_result->fetch_assoc()) {
        $service_options[] = [
            'id' => $service['id'],
            'name' => $service['nama_layanan'],
            'price' => $service['harga']
        ];
    }
    echo json_encode($service_options);
} else {
    echo json_encode([]);
}
?>
</script>

<script>
    const CURRENT_TIPE = '<?= $tipe ?>';
</script>

<!-- JavaScript -->
<script src="js/sidebar-toggle.js"></script>
<script src="js/add_pelayanan.js"></script>

</body>
</html>