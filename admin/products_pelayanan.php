<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

$current_page = basename($_SERVER['PHP_SELF']);

// ================= FILTER =================
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// ================= BUILD QUERY =================
$where_conditions = [];
$params = [];
$types = '';

// WAJIB: hanya pelayanan & paket
$where_conditions[] = "product_category IN ('pelayanan','paket')";

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

$where_sql = "WHERE " . implode(" AND ", $where_conditions);

// ================= GET DATA =================
$sql = "SELECT * FROM services $where_sql ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$products = $stmt->get_result();

// ================= CATEGORY FILTER =================
$sql_categories = "
    SELECT DISTINCT kategori 
    FROM services
    WHERE product_category IN ('pelayanan','paket')
      AND kategori IS NOT NULL 
      AND kategori != ''
    ORDER BY kategori
";
$categories_result = $conn->query($sql_categories);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Pelayanan</title>

    <!-- GLOBAL CSS -->
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">

    <!-- KHUSUS PELAYANAN -->
    <link rel="stylesheet" href="css/products.css">

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
           class="nav-item has-submenu <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'active open' : '' ?>"
           onclick="toggleSubmenu(this)">
            <i class="fas fa-capsules"></i>
            <span>Produk</span>
            <i class="fas fa-chevron-down arrow"></i>
        </a>

        <ul class="submenu <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'open' : '' ?>">
            <li><a href="products.php">Vaksin</a></li>
            <li><a href="products_pelayanan.php" class="active">Pelayanan</a></li>
        </ul>
    </nav>
</div>

<!-- ================= MAIN ================= -->
<div class="main-content">

<header class="page-header">
    <h1>Produk – Pelayanan</h1>
    <button class="btn-add-product" onclick="location.href='add_pelayanan.php'">
        <i class="fas fa-plus"></i> Tambah Pelayanan / Paket
    </button>
</header>

<!-- ================= FILTER ================= -->
<div class="search-filter-section">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Nama layanan"
               value="<?= htmlspecialchars($search) ?>" onkeyup="handleSearch()">
    </div>

    <select id="kategoriFilter"
        class="filter-dropdown"
        onchange="handleFilter()">
        <option value="">Semua Kategori</option>
        <?php while ($cat = $categories_result->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($cat['kategori']) ?>"
                <?= $kategori_filter == $cat['kategori'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['kategori']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<!-- ================= CARD GRID ================= -->
<div class="products-container">
<?php if ($products->num_rows > 0): ?>
<?php while ($product = $products->fetch_assoc()): ?>
<?php
$package_items = [];

if ($product['product_category'] === 'paket') {
    $stmtItems = $conn->prepare("
        SELECT s.nama_layanan
        FROM service_package_items spi
        JOIN services s ON s.id = spi.service_id
        WHERE spi.package_id = ?
        ORDER BY s.nama_layanan
    ");
    $stmtItems->bind_param("i", $product['id']);
    $stmtItems->execute();
    $package_items = $stmtItems->get_result();
}
?>

<div class="product-card">
    <div class="product-icon">
        <?php if ($product['product_category'] === 'paket'): ?>
            <i class="fas fa-briefcase-medical"></i>
        <?php else: ?>
            <i class="fas fa-stethoscope"></i>
        <?php endif; ?>
    </div>

    <div class="product-content">

        <h3 class="product-name">
            <?= htmlspecialchars($product['nama_layanan']) ?>
        </h3>

        <span class="badge <?= $product['product_category'] === 'paket' ? 'badge-paket' : 'badge-pelayanan' ?>">
            <?= ucfirst($product['product_category']) ?>
        </span>

        <div class="product-details">

        <!-- ================= PELAYANAN ================= -->
        <?php if ($product['product_category'] === 'pelayanan'): ?>

            <div class="detail-row">
                <span class="detail-label">Kategori</span>
                <span class="detail-separator">:</span>
                <span class="detail-value">
                    <?= htmlspecialchars($product['kategori']) ?>
                </span>
            </div>

            <?php if (!empty($product['durasi_layanan'])): ?>
            <div class="detail-row">
                <span class="detail-label">Durasi</span>
                <span class="detail-separator">:</span>
                <span class="detail-value">
                    <?= intval($product['durasi_layanan']) ?> menit
                </span>
            </div>
            <?php endif; ?>

            <div class="detail-row">
                <span class="detail-label">Harga Standard</span>
                <span class="detail-separator">:</span>
                <span class="detail-value price">
                    Rp <?= number_format($product['harga'], 0, ',', '.') ?>
                </span>
            </div>

        <?php endif; ?>


        <!-- ================= PAKET ================= -->
        <?php if ($product['product_category'] === 'paket'): ?>

            <?php if (!empty($product['kode_paket'])): ?>
            <div class="detail-row">
                <span class="detail-label">Kode Paket</span>
                <span class="detail-separator">:</span>
                <span class="detail-value">
                    <?= htmlspecialchars($product['kode_paket']) ?>
                </span>
            </div>
            <?php endif; ?>

            <div class="detail-row">
                <span class="detail-label">Kategori</span>
                <span class="detail-separator">:</span>
                <span class="detail-value">
                    <?= htmlspecialchars($product['kategori']) ?>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Harga Paket</span>
                <span class="detail-separator">:</span>
                <span class="detail-value price">
                    Rp <?= number_format($product['harga'], 0, ',', '.') ?>
                </span>
            </div>
            <?php if (!empty($package_items) && $package_items->num_rows > 0): ?>
                <div class="detail-row">
                    <span class="detail-label">Isi Paket</span>
                    <span class="detail-separator">:</span>
                    <span class="detail-value">
                        <ul class="package-list">
                            <?php while ($item = $package_items->fetch_assoc()): ?>
                                <li><?= htmlspecialchars($item['nama_layanan']) ?></li>
                            <?php endwhile; ?>
                        </ul>
                    </span>
                </div>
                <?php endif; ?>

        <?php endif; ?>

    </div>


        <div class="product-actions">
            <button class="btn-edit"
                onclick="location.href='edit_product.php?id=<?= $product['id'] ?>'">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn-delete"
                onclick="deleteProduct(<?= $product['id'] ?>)">
                <i class="fas fa-trash"></i> Hapus
            </button>
        </div>

    </div>
</div>

<?php endwhile; ?>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-stethoscope"></i>
        <h3>Belum ada Pelayanan / Paket</h3>
    </div>
<?php endif; ?>
</div>

</div>

<!-- ================= JS ================= -->
<script src="js/sidebar-toggle.js"></script>
<script src="js/products_pelayanan.js"></script>

</body>
</html>
