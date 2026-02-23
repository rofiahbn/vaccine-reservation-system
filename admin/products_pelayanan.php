<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

$current_page = basename($_SERVER['PHP_SELF']);

// ================= FILTER =================
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';
$tipe_filter = isset($_GET['tipe']) ? $_GET['tipe'] : '';

// ================= BUILD QUERY =================
$where_conditions = [];
$params = [];
$types = '';

$where_conditions[] = "tipe IN ('pelayanan','paket')";

if (!empty($search)) {
    $where_conditions[] = "nama_layanan LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if (!empty($kategori_filter)) {
    $where_conditions[] = "kategori_usia = ?";
    $params[] = $kategori_filter;
    $types .= 's';
}

if (!empty($tipe_filter) && $tipe_filter != 'semua') {
    $where_conditions[] = "tipe = ?";
    $params[] = $tipe_filter;
    $types .= 's';
}

$where_sql = "WHERE " . implode(" AND ", $where_conditions);

// ================= GET DATA SERVICES =================
$sql = "SELECT * FROM services $where_sql ORDER BY 
        CASE tipe 
            WHEN 'pelayanan' THEN 1 
            WHEN 'paket' THEN 2 
        END, 
        created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$services = $stmt->get_result();

// ================= GET KATEGORI UNTUK FILTER =================
$sql_categories = "SELECT DISTINCT kategori_usia as kategori FROM services WHERE tipe IN ('pelayanan','paket') AND kategori_usia IS NOT NULL AND kategori_usia != '' ORDER BY kategori_usia";
$categories_result = $conn->query($sql_categories);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelayanan & Paket - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="css/products_pelayanan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge-produk { background: #3498db; color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-left: 6px; }
        .badge-jasa { background: #f39c12; color: white; padding: 2px 8px; border-radius: 4px; font-size: 10px; margin-left: 6px; }
        .debug-info {
            background: #f0f0f0;
            padding: 5px;
            margin: 5px 0;
            font-size: 11px;
            color: #333;
            border-left: 3px solid #f39c12;
        }
    </style>
</head>
<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidebar">
    <div class="logo">
        <img src="vaksinin-logo.png" class="logo-full">
        <img src="v-logo.png" class="logo-icon">
    </div>
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
        <a href="javascript:void(0)" class="nav-item has-submenu active open" onclick="toggleSubmenu(this)">
            <i class="fas fa-capsules"></i><span>Produk</span><i class="fas fa-chevron-down arrow"></i>
        </a>
        <ul class="submenu open">
            <li><a href="products.php">Vaksin & Obat</a></li>
            <li>
                    <a href="products_jasa.php" 
                    class="<?= $current_page == 'products_jasa.php' ? 'active' : '' ?>">
                    Jasa
                    </a>
                </li>
            <li><a href="products_pelayanan.php" class="active">Pelayanan/Paket</a></li>
        </ul>
        <a href="patients.php" class="nav-item"><i class="fas fa-users"></i><span>Pasien</span></a>
        <a href="staff.php" class="nav-item">
            <i class="fas fa-user-md"></i>
            <span>Staff</span>
        </a>
        <a href="calendar_setting.php" class="nav-item"><i class="fas fa-calendar"></i><span>Kalender</span></a>
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
                <input type="text" id="searchInput" placeholder="Cari nama layanan atau paket..." value="<?= htmlspecialchars($search) ?>">
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
                        <option value="<?= htmlspecialchars($cat['kategori']) ?>" <?= $kategori_filter == $cat['kategori'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['kategori']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
            <button class="btn-filter-apply" onclick="applyFilters()"><i class="fas fa-filter"></i> Terapkan</button>
            <button class="btn-filter-reset" onclick="resetFilters()"><i class="fas fa-undo"></i> Reset</button>
        </div>
        <div class="filter-info">
            <i class="fas fa-info-circle"></i>
            <span><strong>Layanan</strong> = 1x kunjungan (vaksin/obat + jasa)</span>
            <span class="separator">|</span>
            <span><strong>Paket</strong> = Beberapa kali kunjungan (bundling hemat)</span>
        </div>
    </div>

    <!-- ================= CARD GRID ================= -->
    <div class="products-container">
    <?php if ($services && $services->num_rows > 0): ?>
        <?php while ($service = $services->fetch_assoc()): ?>
            <?php
            // ========== UNTUK LAYANAN ==========
            $produk_components = [];
            $jasa_components = [];
            $total_harga_komponen = 0;
            
            if ($service['tipe'] === 'pelayanan') {
                // 1. Ambil komponen PRODUK dari tabel service_product_components
                $comp_products_sql = "
                    SELECT spc.*,
                           p.nama_produk as nama_komponen,
                           p.kategori as kategori_komponen,
                           'produk' as tipe_komponen,
                           p.harga as harga_komponen,
                           p.satuan
                    FROM service_product_components spc
                    JOIN products p ON p.id = spc.product_id
                    WHERE spc.service_id = ?
                    ORDER BY p.nama_produk
                ";
                $comp_products_stmt = $conn->prepare($comp_products_sql);
                if ($comp_products_stmt) {
                    $comp_products_stmt->bind_param("i", $service['id']);
                    $comp_products_stmt->execute();
                    $produk_result = $comp_products_stmt->get_result();
                    while ($row = $produk_result->fetch_assoc()) {
                        $produk_components[] = $row;
                        $total_harga_komponen += ($row['harga_komponen'] ?? 0) * $row['quantity'];
                    }
                }
                
                // 2. Ambil komponen JASA dari tabel service_jasa_components
                $comp_jasa_sql = "
                    SELECT sjc.*,
                           s.nama_layanan as nama_komponen,
                           s.kategori_usia as kategori_komponen,
                           'jasa' as tipe_komponen,
                           s.harga as harga_komponen
                    FROM service_jasa_components sjc
                    JOIN services s ON s.id = sjc.jasa_id
                    WHERE sjc.service_id = ?
                    ORDER BY s.nama_layanan
                ";
                $comp_jasa_stmt = $conn->prepare($comp_jasa_sql);
                if ($comp_jasa_stmt) {
                    $comp_jasa_stmt->bind_param("i", $service['id']);
                    $comp_jasa_stmt->execute();
                    $jasa_result = $comp_jasa_stmt->get_result();
                    while ($row = $jasa_result->fetch_assoc()) {
                        $jasa_components[] = $row;
                        $total_harga_komponen += ($row['harga_komponen'] ?? 0) * $row['quantity'];
                    }
                }
            }
            
            // ========== UNTUK PAKET ==========
            $package_items = [];
            $total_harga_item = 0;
            
            if ($service['tipe'] === 'paket') {
                $item_sql = "
                    SELECT spi.*, 
                           s.nama_layanan,
                           s.kategori_usia as kategori,
                           s.harga,
                           'layanan' as tipe_item
                    FROM service_package_items spi
                    JOIN services s ON s.id = spi.service_id
                    WHERE spi.package_id = ?
                    ORDER BY spi.visit_order, spi.id
                ";
                $item_stmt = $conn->prepare($item_sql);
                if ($item_stmt) {
                    $item_stmt->bind_param("i", $service['id']);
                    $item_stmt->execute();
                    $package_items = $item_stmt->get_result();
                    
                    if ($package_items && $package_items->num_rows > 0) {
                        $package_items->data_seek(0);
                        while ($item = $package_items->fetch_assoc()) {
                            $total_harga_item += ($item['harga'] ?? 0) * ($item['quantity'] ?? 1);
                        }
                        $package_items->data_seek(0);
                    }
                }
            }
            ?>
            
            <div class="product-card <?= $service['tipe'] === 'paket' ? 'card-package' : 'card-service' ?>">
                
                <!-- HEADER CARD -->
                <div class="card-header">
                    <div class="card-icon">
                        <?= $service['tipe'] === 'paket' ? '<i class="fas fa-cubes"></i>' : '<i class="fas fa-stethoscope"></i>' ?>
                    </div>
                    <div class="card-title">
                        <h3 class="product-name"><?= htmlspecialchars($service['nama_layanan']) ?></h3>
                        <span class="badge <?= $service['tipe'] === 'paket' ? 'badge-paket' : 'badge-pelayanan' ?>">
                            <?= $service['tipe'] === 'paket' ? '📦 PAKET' : '🩺 LAYANAN' ?>
                        </span>
                    </div>
                    <?php if (!empty($service['kode_paket']) && $service['tipe'] === 'paket'): ?>
                        <div class="package-code"><i class="fas fa-tag"></i> <?= htmlspecialchars($service['kode_paket']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- BODY CARD -->
                <div class="card-body">
                    <!-- Kategori -->
                    <div class="info-row">
                        <span class="info-label">Kategori</span>
                        <span class="info-value"><span class="category-tag"><i class="fas fa-folder"></i> <?= htmlspecialchars($service['kategori_usia'] ?: 'Umum') ?></span></span>
                    </div>

                    <!-- ========== LAYANAN ========== -->
                    <?php if ($service['tipe'] === 'pelayanan'): ?>
                        <?php if (!empty($service['durasi_layanan'])): ?>
                        <div class="info-row">
                            <span class="info-label">Durasi</span>
                            <span class="info-value"><i class="fas fa-clock"></i> <?= intval($service['durasi_layanan']) ?> menit</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-row price-row">
                            <span class="info-label">Harga</span>
                            <span class="info-value price">Rp <?= number_format($service['harga'], 0, ',', '.') ?></span>
                        </div>
                        
                        <!-- KOMPONEN LAYANAN (PRODUK + JASA) -->
                        <div class="components-section">
                            <div class="components-title"><i class="fas fa-puzzle-piece"></i> Komponen:</div>
                            
                            <?php if (!empty($jasa_components) || !empty($produk_components)): ?>
                                <ul class="components-list">
                                    <!-- Tampilkan JASA dulu -->
                                    <?php foreach ($jasa_components as $comp): ?>
                                        <li>
                                            <i class="fas fa-user-md" style="color: #f39c12;"></i>
                                            <span class="component-name">
                                                <?= htmlspecialchars($comp['nama_komponen']) ?>
                                                <span class="badge-jasa">JASA</span>
                                            </span>
                                            <?php if ($comp['quantity'] > 1): ?>
                                                <span class="component-qty">x<?= $comp['quantity'] ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                    
                                    <!-- Tampilkan PRODUK -->
                                    <?php foreach ($produk_components as $comp): ?>
                                        <li>
                                            <?php if (stripos($comp['kategori_komponen'], 'vaksin') !== false): ?>
                                                <i class="fas fa-syringe" style="color: #3498db;"></i>
                                            <?php elseif (stripos($comp['kategori_komponen'], 'obat') !== false): ?>
                                                <i class="fas fa-prescription-bottle" style="color: #3498db;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-box" style="color: #3498db;"></i>
                                            <?php endif; ?>
                                            <span class="component-name">
                                                <?= htmlspecialchars($comp['nama_komponen']) ?>
                                                <?php if (!empty($comp['satuan'])): ?>
                                                    <small>(<?= $comp['satuan'] ?>)</small>
                                                <?php endif; ?>
                                                <span class="badge-produk">PRODUK</span>
                                            </span>
                                            <?php if ($comp['quantity'] > 1): ?>
                                                <span class="component-qty">x<?= $comp['quantity'] ?></span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if ($total_harga_komponen > 0): ?>
                                    <div style="font-size:12px; color:#7f8c8d; margin-top:8px; text-align:right;">
                                        Total komponen: Rp <?= number_format($total_harga_komponen, 0, ',', '.') ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p style="color:#7f8c8d; font-style:italic; padding:8px;">Belum ada komponen</p>
                            <?php endif; ?>
                        </div>

                    <!-- ========== PAKET ========== -->
                    <?php else: ?>
                        <div class="info-row price-row">
                            <span class="info-label">Harga Paket</span>
                            <span class="info-value price">Rp <?= number_format($service['harga'], 0, ',', '.') ?></span>
                        </div>
                        
                        <?php if ($package_items && $package_items->num_rows > 0): ?>
                            <div class="package-items-section">
                                <div class="package-items-title">
                                    <i class="fas fa-list-check"></i> Isi Paket (<?= $package_items->num_rows ?> item):
                                </div>
                                
                                <div class="package-items-grid">
                                    <?php 
                                    $package_items->data_seek(0);
                                    while ($item = $package_items->fetch_assoc()): 
                                    ?>
                                        <div class="package-item">
                                            <div class="package-item-header">
                                                <span class="item-visit">
                                                    <i class="fas fa-calendar-check"></i> Kunjungan #<?= $item['visit_order'] ?>
                                                </span>
                                                <span class="item-qty-badge">x<?= $item['quantity'] ?></span>
                                            </div>
                                            <div class="package-item-name">
                                                <?= htmlspecialchars($item['nama_layanan']) ?>
                                                <span style="display: block; font-size: 11px; color: #7f8c8d; margin-top: 2px;">
                                                    <?= htmlspecialchars($item['kategori'] ?? 'Umum') ?>
                                                </span>
                                            </div>
                                            <?php if (!empty($item['harga'])): ?>
                                            <div style="font-size: 12px; color: #27ae60; text-align: right; margin-top: 4px;">
                                                Rp <?= number_format($item['harga'] * $item['quantity'], 0, ',', '.') ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                
                                <?php if ($total_harga_item > $service['harga']): ?>
                                <div class="package-saving">
                                    <i class="fas fa-tag"></i> 
                                    <strong>Hemat Rp <?= number_format($total_harga_item - $service['harga'], 0, ',', '.') ?></strong>
                                    <span class="saving-detail">(Harga normal: Rp <?= number_format($total_harga_item, 0, ',', '.') ?>)</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="package-items-section empty">
                                <i class="fas fa-box-open"></i> Belum ada item paket
                            </div>
                        <?php endif; ?>
                        
                        <div class="package-note">
                            <i class="fas fa-clock"></i> 
                            <span>Berlaku untuk <?= $package_items ? $package_items->num_rows : 'beberapa' ?>x kunjungan</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($service['deskripsi'])): ?>
                    <div class="description-section">
                        <div class="description-title"><i class="fas fa-align-left"></i> Keterangan:</div>
                        <div class="description-text"><?= nl2br(htmlspecialchars($service['deskripsi'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- FOOTER CARD -->
                <div class="card-footer">
                    <div class="product-actions">
                        <button class="btn-edit" onclick="location.href='edit_pelayanan.php?id=<?= $service['id'] ?>&tipe=<?= $service['tipe'] ?>'"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn-delete" onclick="deleteService(<?= $service['id'] ?>, '<?= $service['nama_layanan'] ?>')"><i class="fas fa-trash"></i> Hapus</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-clinic-medical"></i></div>
            <h3>Belum ada Pelayanan atau Paket</h3>
            <p>Mulai dengan menambahkan layanan vaksinasi atau paket kesehatan</p>
            <div class="empty-actions">
                <button class="btn-add-product" onclick="location.href='add_pelayanan.php?tipe=pelayanan'"><i class="fas fa-plus-circle"></i> Tambah Layanan</button>
                <button class="btn-add-package" onclick="location.href='add_pelayanan.php?tipe=paket'"><i class="fas fa-cubes"></i> Tambah Paket</button>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>

<script>
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const kategori = document.getElementById('kategoriFilter').value;
    const tipe = document.getElementById('tipeFilter').value;
    let url = `products_pelayanan.php?search=${encodeURIComponent(search)}&kategori=${encodeURIComponent(kategori)}`;
    if (tipe && tipe !== 'semua') url += `&tipe=${encodeURIComponent(tipe)}`;
    window.location.href = url;
}
function resetFilters() { window.location.href = 'products_pelayanan.php'; }
document.getElementById('searchInput')?.addEventListener('keypress', function(e) { if (e.key === 'Enter') applyFilters(); });
function deleteService(id, name) { if (confirm(`Hapus "${name}"?\nData yang dihapus tidak dapat dikembalikan.`)) window.location.href = `delete_pelayanan.php?id=${id}`; }
</script>
<script src="js/sidebar-toggle.js"></script>
</body>
</html>