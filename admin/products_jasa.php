<?php
session_start();
include "../config.php";

$current_page = 'products_jasa.php';

// ================= FILTER =================
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// ================= BUILD QUERY =================
$where_conditions = ["tipe = 'jasa'"];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(nama_layanan LIKE ? OR kode_layanan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if (!empty($kategori_filter)) {
    $where_conditions[] = "kategori_usia = ?";
    $params[] = $kategori_filter;
    $types .= 's';
}

$where_sql = "WHERE " . implode(" AND ", $where_conditions);

// ================= GET JASA =================
$sql = "SELECT * FROM services
        $where_sql
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$services = $stmt->get_result();

// ================= GET KATEGORI UNTUK FILTER =================
$sql_categories = "SELECT DISTINCT kategori_usia FROM services 
                   WHERE tipe = 'jasa' AND kategori_usia IS NOT NULL AND kategori_usia != '' 
                   ORDER BY kategori_usia";
$categories_result = $conn->query($sql_categories);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Jasa - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/products_pelayanan.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="vaksinin-logo.png" alt="Vaksinin" class="logo-full">
            <img src="v-logo.png" alt="V" class="logo-icon">
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="javascript:void(0)" 
                class="nav-item has-submenu 
                <?= in_array($current_page, ['products.php','products_pelayanan.php','products_jasa.php']) ? 'active open' : '' ?>" 
                onclick="toggleSubmenu(this)">
                    <i class="fas fa-capsules"></i>
                    <span>Produk</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
            <ul class="submenu <?= in_array($current_page, ['products.php','products_pelayanan.php','products_jasa.php']) ? 'open' : '' ?>">
                <li>
                    <a href="products.php" 
                    class="<?= $current_page == 'products.php' ? 'active' : '' ?>">
                    Stok
                    </a>
                </li>
                <li>
                    <a href="products_jasa.php" 
                    class="<?= $current_page == 'products_jasa.php' ? 'active' : '' ?>">
                    Jasa
                    </a>
                </li>
                <li>
                    <a href="products_pelayanan.php" 
                    class="<?= $current_page == 'products_pelayanan.php' ? 'active' : '' ?>">
                    Pelayanan/Paket
                    </a>
                </li>
            </ul>
            <a href="patients.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
            </a>
            <a href="staff.php" class="nav-item">
                <i class="fas fa-user-md"></i>
                <span>Staff</span>
            </a>
            <a href="calendar_setting.php" class="nav-item">
                <i class="fas fa-calendar"></i>
                <span>Kalender</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="#" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header class="page-header">
            <h1>Layanan Jasa</h1>
            <div class="header-actions">
                <button class="btn-add-product" onclick="location.href='add_jasa.php'">
                    <i class="fas fa-plus"></i> Tambah Jasa
                </button>
            </div>
        </header>

        <!-- Search and Filter Section -->
        <div class="filter-section-wrapper">
            <div class="search-filter-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama atau kode jasa..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <select id="kategoriFilter" class="filter-dropdown">
                    <option value="">Semua Kategori Usia</option>
                    <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                        <?php while ($cat = $categories_result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($cat['kategori_usia']) ?>" 
                                    <?= ($kategori_filter == $cat['kategori_usia']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['kategori_usia']) ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
                <button class="btn-filter-apply" onclick="handleFilter()">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
                <button class="btn-filter-reset" onclick="resetFilters()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </div>

        <!-- Services Grid -->
        <div class="products-container">
            <?php if ($services && $services->num_rows > 0): ?>
                <?php while ($service = $services->fetch_assoc()): ?>
                    <div class="product-card">
                        <!-- Card Header -->
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-hand-holding-medical"></i>
                            </div>
                            <div class="card-title">
                                <h3 class="product-name"><?= htmlspecialchars($service['nama_layanan']) ?></h3>
                                <?php if (!empty($service['kode_layanan'])): ?>
                                    <span class="badge"><?= htmlspecialchars($service['kode_layanan']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <?php if (!empty($service['deskripsi'])): ?>
                                <div class="description-section">
                                    <div class="description-text" style="margin-bottom: 16px; font-size: 13px; color: #64748b;">
                                        <?= htmlspecialchars($service['deskripsi']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="info-row">
                                <span class="info-label">Kategori Usia</span>
                                <span class="info-value">
                                    <span class="category-tag"><?= htmlspecialchars($service['kategori_usia'] ?? '-') ?></span>
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Harga</span>
                                <span class="info-value price">Rp <?= number_format($service['harga'] ?? 0, 0, ',', '.') ?></span>
                            </div>

                            <?php if (!empty($service['kode_paket'])): ?>
                            <div class="info-row">
                                <span class="info-label">Kode Paket</span>
                                <span class="info-value">
                                    <span class="badge"><?= htmlspecialchars($service['kode_paket']) ?></span>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer">
                            <div class="product-actions">
                                <button class="btn-edit" onclick="editJasa(<?= $service['id'] ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn-delete" onclick="deleteJasa(<?= $service['id'] ?>, '<?= htmlspecialchars($service['nama_layanan']) ?>')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-hand-holding-medical"></i>
                    </div>
                    <h3>Belum ada Layanan Jasa</h3>
                    <p>Tambahkan layanan jasa untuk mulai mengelola</p>
                    <button class="btn-add-product" onclick="location.href='add_jasa.php'">
                        <i class="fas fa-plus"></i> Tambah Jasa
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/sidebar-toggle.js"></script>
    <script src="js/products_jasa.js"></script>
</body>
</html>