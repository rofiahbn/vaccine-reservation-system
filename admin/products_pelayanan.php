<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

$current_page = basename($_SERVER['PHP_SELF']);

// ================= FILTER =================
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$tipe_filter = isset($_GET['tipe']) ? $_GET['tipe'] : ''; // 'pelayanan', 'paket', 'semua'

// ================= BUILD QUERY =================
$where_conditions = [];
$params = [];
$types = '';

// WAJIB: hanya pelayanan & paket
$where_conditions[] = "tipe IN ('pelayanan','paket')";

if (!empty($search)) {
    $where_conditions[] = "nama_layanan LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if (!empty($kategori_filter)) {
    $where_conditions[] = "kategori = ?";
    $params[] = $kategori_filter;
    $types .= 's';
}

if (!empty($tipe_filter) && $tipe_filter != 'semua') {
    $where_conditions[] = "tipe = ?";
    $params[] = $tipe_filter;
    $types .= 's';
}

$where_sql = "WHERE " . implode(" AND ", $where_conditions);

// ================= CEK KONEKSI =================
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// ================= GET DATA =================
$sql = "SELECT 
            services.*,
            kategori_usia AS kategori
        FROM services
        $where_sql ORDER BY 
        CASE tipe 
            WHEN 'pelayanan' THEN 1 
            WHEN 'paket' THEN 2 
        END, 
        created_at DESC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error prepare statement: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$products = $stmt->get_result();

// ================= GET KATEGORI UNTUK FILTER =================
$sql_categories = "
    SELECT DISTINCT kategori 
    FROM services
    WHERE tipe IN ('pelayanan','paket')
      AND kategori IS NOT NULL 
      AND kategori != ''
    ORDER BY kategori
";
$categories_result = $conn->query($sql_categories);
if ($categories_result === false) {
    $categories_result = new stdClass();
    $categories_result->num_rows = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelayanan & Paket - Vaksinin</title>

    <!-- GLOBAL CSS -->
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">

    <!-- CSS KHUSUS -->
    <link rel="stylesheet" href="css/products_pelayanan.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidebar">
    <div class="logo">
        <img src="vaksinin-logo.png" class="logo-full">
        <img src="v-logo.png" class="logo-icon">
    </div>

    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-th-large"></i><span>Dashboard</span>
        </a>

        <a href="javascript:void(0)"
           class="nav-item has-submenu active open"
           onclick="toggleSubmenu(this)">
            <i class="fas fa-capsules"></i>
            <span>Produk</span>
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

<!-- ================= MAIN ================= -->
<div class="main-content">

<div class="page-header-wrapper">
    <header class="page-header">
        <h1>Pelayanan & Paket Kesehatan</h1>
        <div class="header-actions">
            <button class="btn-add-product" onclick="location.href='add_pelayanan.php?tipe=pelayanan'">
                <i class="fas fa-plus-circle"></i> Tambah Layanan
            </button>
            <button class="btn-add-package" onclick="location.href='add_pelayanan.php?tipe=paket'">
                <i class="fas fa-cubes"></i> Tambah Paket
            </button>
        </div>
    </header>
</div>

<!-- ================= FILTER ================= -->
<div class="filter-section-wrapper">
    <div class="search-filter-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Cari nama layanan atau paket..."
                   value="<?= htmlspecialchars($search) ?>">
        </div>

        <select id="tipeFilter" class="filter-dropdown">
            <option value="semua" <?= $tipe_filter == 'semua' || empty($tipe_filter) ? 'selected' : '' ?>>Semua Tipe</option>
            <option value="pelayanan" <?= $tipe_filter == 'pelayanan' ? 'selected' : '' ?>>Layanan</option>
            <option value="paket" <?= $tipe_filter == 'paket' ? 'selected' : '' ?>>Paket</option>
        </select>

        <select id="kategoriFilter" class="filter-dropdown">
            <option value="">Semua Kategori</option>
            <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($cat['kategori']) ?>"
                        <?= $kategori_filter == $cat['kategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['kategori']) ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
        
        <button class="btn-filter-apply" onclick="applyFilters()">
            <i class="fas fa-filter"></i> Terapkan
        </button>
        <button class="btn-filter-reset" onclick="resetFilters()">
            <i class="fas fa-undo"></i> Reset
        </button>
    </div>
    
    <div class="filter-info">
        <i class="fas fa-info-circle"></i>
        <span><strong>Layanan</strong> = 1x kunjungan (sudah termasuk vaksin/obat + jasa)</span>
        <span class="separator">|</span>
        <span><strong>Paket</strong> = Beberapa kali kunjungan (bundling hemat)</span>
    </div>
</div>

<!-- ================= CARD GRID ================= -->
<div class="products-container">
<?php if ($products && $products->num_rows > 0): ?>
    <?php while ($product = $products->fetch_assoc()): ?>
        <?php
        // AMBIL KOMPONEN LAYANAN (jika ini pelayanan)
        $service_components = [];
        if ($product['tipe'] === 'pelayanan') {
            $comp_sql = "
                SELECT sc.quantity,
                    s.nama_layanan,
                    s.kategori
                FROM service_components sc
                JOIN services s ON s.id = sc.product_id
                WHERE sc.service_id = ?
                ORDER BY sc.id
            ";
            $comp_stmt = $conn->prepare($comp_sql);
            if ($comp_stmt) {
                $comp_stmt->bind_param("i", $product['id']);
                $comp_stmt->execute();
                $service_components = $comp_stmt->get_result();
            }
        }
        
        // AMBIL ITEM PAKET (jika ini paket)
        $package_items = [];
        if ($product['tipe'] === 'paket') {
            $item_sql = "
                SELECT spi.*, s.nama_layanan, s.harga 
                FROM service_package_items spi
                JOIN services s ON s.id = spi.service_id
                WHERE spi.package_id = ?
                ORDER BY spi.visit_order, spi.id
            ";
            $item_stmt = $conn->prepare($item_sql);
            if ($item_stmt) {
                $item_stmt->bind_param("i", $product['id']);
                $item_stmt->execute();
                $package_items = $item_stmt->get_result();
            }
        }
        ?>
        
        <div class="product-card <?= $product['tipe'] === 'paket' ? 'card-package' : 'card-service' ?>">
            
            <!-- HEADER CARD -->
            <div class="card-header">
                <div class="card-icon">
                    <?php if ($product['tipe'] === 'paket'): ?>
                        <i class="fas fa-cubes"></i>
                    <?php else: ?>
                        <i class="fas fa-stethoscope"></i>
                    <?php endif; ?>
                </div>
                <div class="card-title">
                    <h3 class="product-name"><?= htmlspecialchars($product['nama_layanan']) ?></h3>
                    <span class="badge <?= $product['tipe'] === 'paket' ? 'badge-paket' : 'badge-pelayanan' ?>">
                        <?= $product['tipe'] === 'paket' ? '📦 PAKET' : '🩺 LAYANAN' ?>
                    </span>
                </div>
                <?php if (!empty($product['kode_paket']) && $product['tipe'] === 'paket'): ?>
                    <div class="package-code">
                        <i class="fas fa-tag"></i> <?= htmlspecialchars($product['kode_paket']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- BODY CARD -->
            <div class="card-body">
                <!-- Kategori -->
                <div class="info-row">
                    <span class="info-label">Kategori</span>
                    <span class="info-value">
                        <span class="category-tag">
                            <i class="fas fa-folder"></i> <?= htmlspecialchars($product['kategori'] ?: 'Umum') ?>
                        </span>
                    </span>
                </div>

                <!-- ========== LAYANAN (SERVICE) ========== -->
                <?php if ($product['tipe'] === 'pelayanan'): ?>
                    
                    <!-- Durasi -->
                    <?php if (!empty($product['durasi_layanan'])): ?>
                    <div class="info-row">
                        <span class="info-label">Durasi</span>
                        <span class="info-value">
                            <i class="fas fa-clock"></i> <?= intval($product['durasi_layanan']) ?> menit
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Harga -->
                    <div class="info-row price-row">
                        <span class="info-label">Harga</span>
                        <span class="info-value price">
                            Rp <?= number_format($product['harga'], 0, ',', '.') ?>
                        </span>
                    </div>
                    
                    <!-- KOMPOSISI LAYANAN -->
                    <div class="components-section">
                        <div class="components-title">
                            <i class="fas fa-puzzle-piece"></i> Komponen Layanan:
                        </div>
                        
                        <?php if ($service_components && $service_components->num_rows > 0): ?>
                            <ul class="components-list">
                                <?php while($comp = $service_components->fetch_assoc()): ?>

                                    <li>

                                        <?php if (stripos($comp['kategori'], 'vaksin') !== false): ?>
                                            <i class="fas fa-syringe"></i>

                                        <?php elseif (stripos($comp['kategori'], 'obat') !== false): ?>
                                            <i class="fas fa-prescription-bottle"></i>

                                        <?php else: ?>
                                            <i class="fas fa-cog"></i>
                                        <?php endif; ?>

                                        <span class="component-name">
                                            <?= htmlspecialchars($comp['nama_layanan']) ?>
                                        </span>

                                        <?php if ($comp['quantity'] > 1): ?>
                                            <span class="component-qty">x<?= $comp['quantity'] ?></span>
                                        <?php endif; ?>

                                    </li>

                                <?php endwhile; ?>
                                </ul>
                        <?php else: ?>
                            <!-- Default komposisi -->
                            <ul class="components-list default">
                                <li>
                                    <i class="fas fa-syringe"></i>
                                    <span class="component-name">Vaksin <?= htmlspecialchars($product['nama_layanan']) ?></span>
                                </li>
                                <li>
                                    <i class="fas fa-hand-holding-medical"></i>
                                    <span class="component-name">Jasa Tenaga Medis</span>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

                <!-- ========== PAKET (PACKAGE) ========== -->
                <?php if ($product['tipe'] === 'paket'): ?>
                    
                    <!-- Harga Paket -->
                    <div class="info-row price-row">
                        <span class="info-label">Harga Paket</span>
                        <span class="info-value price">
                            Rp <?= number_format($product['harga'], 0, ',', '.') ?>
                        </span>
                    </div>
                    
                    <!-- ISI PAKET -->
                    <?php if ($package_items && $package_items->num_rows > 0): ?>
                        <div class="package-items-section">
                            <div class="package-items-title">
                                <i class="fas fa-list-check"></i> Isi Paket (<?= $package_items->num_rows ?> item):
                            </div>
                            
                            <div class="package-items-grid">
                                <?php 
                                $total_harga_item = 0;
                                $visit_counter = 1;
                                while ($item = $package_items->fetch_assoc()): 
                                    $total_harga_item += ($item['harga'] ?? 0) * ($item['quantity'] ?? 1);
                                ?>
                                    <div class="package-item">
                                        <div class="package-item-header">
                                            <span class="item-visit">
                                                <i class="fas fa-calendar-check"></i> Kunjungan #<?= $item['visit_order'] ?: $visit_counter++ ?>
                                            </span>
                                            <span class="item-qty-badge">x<?= $item['quantity'] ?></span>
                                        </div>
                                        <div class="package-item-name">
                                            <?= htmlspecialchars($item['nama_layanan']) ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            
                            <?php if ($total_harga_item > $product['harga']): ?>
                            <div class="package-saving">
                                <i class="fas fa-tag"></i> 
                                <strong>Hemat Rp <?= number_format($total_harga_item - $product['harga'], 0, ',', '.') ?></strong>
                                <span class="saving-detail">(Harga normal: Rp <?= number_format($total_harga_item, 0, ',', '.') ?>)</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="package-items-section empty">
                            <i class="fas fa-box-open"></i>
                            <span>Belum ada item paket</span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Keterangan Paket -->
                    <div class="package-note">
                        <i class="fas fa-clock"></i> 
                        <span>Berlaku untuk <?= $package_items ? $package_items->num_rows : 'beberapa' ?>x kunjungan</span>
                    </div>
                    
                <?php endif; ?>
                
                <!-- Deskripsi (jika ada) -->
                <?php if (!empty($product['deskripsi'])): ?>
                <div class="description-section">
                    <div class="description-title">
                        <i class="fas fa-align-left"></i> Keterangan:
                    </div>
                    <div class="description-text">
                        <?= nl2br(htmlspecialchars($product['deskripsi'])) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- FOOTER CARD -->
            <div class="card-footer">
                <div class="product-actions">
                    <button class="btn-edit" onclick="location.href='edit_pelayanan.php?id=<?= $product['id'] ?>&tipe=<?= $product['tipe'] ?>'">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn-delete" onclick="deleteProduct(<?= $product['id'] ?>, '<?= $product['nama_layanan'] ?>')">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
                <div class="product-meta">
                    <span class="meta-date">
                        <i class="fas fa-clock"></i> Dibuat: <?= date('d/m/Y', strtotime($product['created_at'])) ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">
            <i class="fas fa-clinic-medical"></i>
        </div>
        <h3>Belum ada Pelayanan atau Paket</h3>
        <p>Mulai dengan menambahkan layanan vaksinasi atau paket kesehatan</p>
        <div class="empty-actions">
            <button class="btn-add-product" onclick="location.href='add_pelayanan.php?tipe=pelayanan'">
                <i class="fas fa-plus-circle"></i> Tambah Layanan
            </button>
            <button class="btn-add-package" onclick="location.href='add_pelayanan.php?tipe=paket'">
                <i class="fas fa-cubes"></i> Tambah Paket
            </button>
        </div>
    </div>
<?php endif; ?>
</div>

</div>

<!-- ================= SCRIPT ================= -->
<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const kategori = document.getElementById('kategoriFilter').value;
    const tipe = document.getElementById('tipeFilter').value;
    
    let url = `products_pelayanan.php?search=${encodeURIComponent(search)}&kategori=${encodeURIComponent(kategori)}`;
    
    if (tipe && tipe !== 'semua') {
        url += `&tipe=${encodeURIComponent(tipe)}`;
    }
    
    window.location.href = url;
}

function resetFilters() {
    window.location.href = 'products_pelayanan.php';
}

// Enter key pada search
document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

function deleteProduct(id, name) {
    if (confirm(`Hapus "${name}"?\nData yang dihapus tidak dapat dikembalikan.`)) {
        window.location.href = `delete_pelayanan.php?id=${id}`;
    }
}
</script>

<script src="js/sidebar-toggle.js"></script>

</body>
</html>