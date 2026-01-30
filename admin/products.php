<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

$current_page = basename($_SERVER['PHP_SELF']);

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Build query
$where_conditions = [];
$params = [];
$types = '';

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

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// Get all products
$sql = "SELECT * FROM services $where_sql ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$products = $stmt->get_result();

// Get distinct categories for filter
$sql_categories = "SELECT DISTINCT kategori FROM services WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori";
$categories_result = $conn->query($sql_categories);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/products.css">
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
                <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'active open' : '' ?>" 
                onclick="toggleSubmenu(this)">
                    <i class="fas fa-capsules"></i>
                    <span>Produk</span>
                    <i class="fas fa-chevron-down arrow"></i>
                </a>
            <ul class="submenu <?= in_array($current_page, ['products.php','products_pelayanan.php']) ? 'open' : '' ?>">
                <li>
                    <a href="products.php" 
                    class="<?= $current_page == 'products.php' ? 'active' : '' ?>">
                    Vaksin
                    </a>
                </li>
                <li>
                    <a href="products_pelayanan.php" 
                    class="<?= $current_page == 'products_pelayanan.php' ? 'active' : '' ?>">
                    Pelayanan
                    </a>
                </li>
            </ul>
            <a href="#" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
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
            <h1>Produk</h1>
            <div class="header-actions">
                <button class="btn-add-product" onclick="location.href='add_product.php'">
                    <i class="fas fa-plus"></i> Tambah Produk
                </button>
            </div>
        </header>

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Nama" value="<?= htmlspecialchars($search) ?>" onkeyup="handleSearch()">
            </div>
            <select id="kategoriFilter" class="filter-dropdown" onchange="handleFilter()">
                <option value="">Semua Kategori</option>
                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($cat['kategori']) ?>" 
                            <?= ($kategori_filter == $cat['kategori']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['kategori']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Products Grid -->
        <div class="products-container">
            <?php if ($products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="product-card">
                        <div class="product-icon">
                            <?php 
                            $jenis = strtolower($product['jenis'] ?? '');
                            $kategori = strtolower($product['kategori'] ?? '');
                            $nama = strtolower($product['nama_layanan'] ?? '');

                            // Tentukan icon berdasarkan jenis / nama / kategori
                            if (
                                strpos($jenis, 'vaksin') !== false ||
                                strpos($nama, 'vaksin') !== false ||
                                strpos($kategori, 'vaksinasi') !== false
                            ): ?>
                                <i class="fas fa-syringe"></i>

                            <?php elseif (
                                strpos($kategori, 'paket kesehatan') !== false
                            ): ?>
                                <i class="fas fa-briefcase-medical"></i>

                            <?php elseif (
                                strpos($jenis, 'vitamin') !== false ||
                                strpos($nama, 'vitamin') !== false ||
                                strpos($kategori, 'vitamin') !== false
                            ): ?>
                                <i class="fas fa-pills"></i>

                            <?php elseif (
                                strpos($jenis, 'obat') !== false ||
                                strpos($kategori, 'obat') !== false
                            ): ?>
                                <i class="fas fa-prescription-bottle"></i>

                            <?php elseif (
                                strpos($kategori, 'swab') !== false
                            ): ?>
                                <i class="fas fa-vial"></i>

                            <?php else: ?>
                                <i class="fas fa-capsules"></i>
                            <?php endif; ?>
                        </div>

                        <div class="product-content">
                            <h3 class="product-name"><?= htmlspecialchars($product['nama_layanan']) ?></h3>
                            <?php if (!empty($product['deskripsi'])): ?>
                                <p class="product-description">
                                    <?= htmlspecialchars($product['deskripsi']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="product-details">
                                <?php if (!empty($product['jenis'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Jenis</span>
                                    <span class="detail-separator">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($product['jenis']) ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="detail-row">
                                    <span class="detail-label">Kategori</span>
                                    <span class="detail-separator">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($product['kategori']) ?></span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Expired</span>
                                    <span class="detail-separator">:</span>
                                    <span class="detail-value">
                                        <?= !empty($product['expired_date']) ? date('d - m - Y', strtotime($product['expired_date'])) : '-' ?>
                                    </span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Harga Standard :</span>
                                    <span class="detail-separator">:</span>
                                    <span class="detail-value price">Rp <?= number_format($product['harga'], 0, ',', '.') ?></span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Batch number</span>
                                    <span class="detail-separator">:</span>
                                    <span class="detail-value">
                                        <?= !empty($product['batch_number']) ? htmlspecialchars($product['batch_number']) : '-' ?>
                                    </span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Harga Special</span>
                                    <span class="detail-separator">:</span>
                                    <span class="detail-value price">
                                        <?= !empty($product['harga_special']) ? 'Rp ' . number_format($product['harga_special'], 0, ',', '.') : '-' ?>
                                    </span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Stock Tersedia :</span>
                                    <span class="detail-separator">:</span>
                                    <?php 
                                    $current_stock = isset($product['stock']) ? intval($product['stock']) : 0;
                                    $low_stock_threshold = isset($product['low_stock']) ? intval($product['low_stock']) : 10;
                                    
                                    $stock_class = '';
                                    if ($current_stock == 0) {
                                        $stock_class = 'out-of-stock';
                                    } elseif ($current_stock <= $low_stock_threshold) {
                                        $stock_class = 'low-stock';
                                    }
                                    ?>
                                    <span class="detail-value stock <?= $stock_class ?>">
                                        <?= $current_stock ?> <?= $current_stock == 0 ? '(Habis)' : '' ?>
                                    </span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Harga Diskon</span>
                                    <span class="detail-separator">:</span>
                                    <span class="detail-value">
                                        <?= !empty($product['harga_diskon']) ? 'Rp ' . number_format($product['harga_diskon'], 0, ',', '.') : '-' ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($product['periode_diskon'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Periode Diskon</span>
                                    <span class="detail-separator">:</span>
                                    <span class="detail-value">
                                        <?= htmlspecialchars($product['periode_diskon']) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="product-actions">
                                <button class="btn-edit" onclick="location.href='edit_product.php?id=<?= $product['id'] ?>'">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn-delete" onclick="deleteProduct(<?= $product['id'] ?>)">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-capsules"></i>
                    </div>
                    <h3>Belum ada Vaksin</h3>
                    <button class="btn-add-product" onclick="location.href='add_product.php'">
                        Tambahkan Vaksin
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        let searchTimeout;

        function handleSearch() {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                const search = document.getElementById('searchInput').value;
                const kategori = document.getElementById('kategoriFilter').value;

                window.location.href = `products.php?search=${encodeURIComponent(search)}&kategori=${encodeURIComponent(kategori)}`;
            }, 500); // 0.5 detik
        }

        function handleFilter() {
            const search = document.getElementById('searchInput').value;
            const kategori = document.getElementById('kategoriFilter').value;

            window.location.href = `products.php?search=${encodeURIComponent(search)}&kategori=${encodeURIComponent(kategori)}`;
        }
        </script>          
    <script src="js/products.js"></script>
    <script src="js/sidebar-toggle.js"></script>
</body>
</html>