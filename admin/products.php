<?php
session_start();
include "../config.php";

$current_page = 'products.php';

// ================= FILTER =================
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// ================= BUILD QUERY =================
$where_conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where_conditions[] = "(p.nama_produk LIKE ? OR p.kode_produk LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if (!empty($kategori_filter)) {
    $where_conditions[] = "p.kategori = ?";
    $params[] = $kategori_filter;
    $types .= 's';
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// ================= GET PRODUCTS WITH STOCK =================
$sql = "SELECT 
            p.*,  
            COALESCE(SUM(ps.stock), 0) as total_stock,
            GROUP_CONCAT(
                CONCAT(ps.batch_number, '||', ps.expired_date, '||', ps.stock) 
                SEPARATOR ';;'
            ) as batch_info
        FROM products p
        LEFT JOIN product_stock ps ON p.id = ps.product_id
        $where_sql
        GROUP BY p.id
        ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// ================= GET KATEGORI UNTUK FILTER =================
$sql_categories = "SELECT DISTINCT kategori FROM products WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori";
$categories_result = $conn->query($sql_categories);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Vaksin & Obat - Vaksinin</title>
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
                    Stok
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
            <h1>Produk Vaksin & Obat</h1>
            <div class="header-actions">
                <button class="btn-add-product" onclick="location.href='add_product.php'">
                    <i class="fas fa-plus"></i> Tambah Produk
                </button>
            </div>
        </header>

        <!-- Search and Filter Section -->
        <div class="filter-section-wrapper">
            <div class="search-filter-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama atau kode produk..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <select id="kategoriFilter" class="filter-dropdown">
                    <option value="">Semua Kategori</option>
                    <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                        <?php while ($cat = $categories_result->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($cat['kategori']) ?>" 
                                    <?= ($kategori_filter == $cat['kategori']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['kategori']) ?>
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

        <!-- Products Grid -->
        <div class="products-container">
            <?php if ($products && $products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): 
                    // Parse batch info
                    $batch_list = [];
                    if (!empty($product['batch_info'])) {
                        $batches = explode(';;', $product['batch_info']);
                        foreach ($batches as $batch) {
                            $parts = explode('||', $batch);
                            if (count($parts) == 3) {
                                $batch_list[] = [
                                    'batch' => $parts[0],
                                    'expired' => $parts[1],
                                    'stock' => $parts[2]
                                ];
                            }
                        }
                    }
                    
                    $total_stock = $product['total_stock'] ?? 0;
                    
                    // Stock status
                    $stock_class = '';
                    $stock_text = $total_stock . ' stok';
                    if ($total_stock == 0) {
                        $stock_class = 'out-of-stock';
                        $stock_text = 'Habis';
                    } elseif ($total_stock <= ($product['minimal_stok'] ?? 10)) {
                        $stock_class = 'low-stock';
                        $stock_text = $total_stock . ' stok (Menipis)';
                    }
                ?>
                    <div class="product-card">
                        <!-- Card Header -->
                        <div class="card-header">
                            <div class="card-icon">
                                <?php 
                                $jenis = strtolower($product['jenis'] ?? '');
                                $nama = strtolower($product['nama_produk'] ?? '');

                                if (strpos($jenis, 'vaksin') !== false || strpos($nama, 'vaksin') !== false): ?>
                                    <i class="fas fa-syringe"></i>
                                <?php elseif (strpos($jenis, 'vitamin') !== false || strpos($nama, 'vitamin') !== false): ?>
                                    <i class="fas fa-pills"></i>
                                <?php elseif (strpos($jenis, 'obat') !== false): ?>
                                    <i class="fas fa-prescription-bottle"></i>
                                <?php else: ?>
                                    <i class="fas fa-capsules"></i>
                                <?php endif; ?>
                            </div>
                            <div class="card-title">
                                <h3 class="product-name"><?= htmlspecialchars($product['nama_produk']) ?></h3>
                                <?php if (!empty($product['kode_produk'])): ?>
                                    <span class="badge"><?= htmlspecialchars($product['kode_produk']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="card-body">
                            <?php if (!empty($product['deskripsi'])): ?>
                                <div class="description-section">
                                    <div class="description-text" style="margin-bottom: 16px; font-size: 13px; color: #64748b;">
                                        <?= htmlspecialchars($product['deskripsi']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="info-row">
                                <span class="info-label">Jenis</span>
                                <span class="info-value"><?= htmlspecialchars($product['jenis'] ?? '-') ?></span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">Kategori</span>
                                <span class="info-value">
                                    <span class="category-tag"><?= htmlspecialchars($product['kategori'] ?? '-') ?></span>
                                </span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Satuan</span>
                                <span class="info-value"><?= htmlspecialchars($product['satuan'] ?? 'dosis') ?></span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Harga</span>
                                <span class="info-value price">Rp <?= number_format($product['harga'] ?? 0, 0, ',', '.') ?></span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Total Stok</span>
                                <span class="info-value stock <?= $stock_class ?>">
                                    <?= $stock_text ?>
                                </span>
                            </div>

                            <!-- Batch Information -->
                            <?php if (!empty($batch_list)): ?>
                            <div class="batch-info" style="margin-top: 16px; padding: 16px; background: #fafbfc; border-radius: 12px;">
                                <div class="components-title" style="margin-bottom: 12px;">
                                    <i class="fas fa-boxes"></i> Batch Stok
                                </div>
                                <div class="batch-list">
                                    <?php foreach ($batch_list as $batch): ?>
                                        <?php 
                                        $batch_stock = intval($batch['stock']);
                                        $is_empty = $batch_stock == 0;
                                        ?>
                                        <div class="info-row" style="<?= $is_empty ? 'opacity: 0.5;' : '' ?>">
                                            <span class="info-label">
                                                <span class="badge"><?= htmlspecialchars($batch['batch']) ?></span>
                                                <small style="display: block; margin-top: 2px; font-size: 11px;">
                                                    Exp: <?= date('d/m/Y', strtotime($batch['expired'])) ?>
                                                </small>
                                            </span>
                                            <span class="info-value">
                                                <?= $batch_stock ?> stok
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Card Footer -->
                        <div class="card-footer">
                            <div class="product-actions">
                                <button class="btn-edit" onclick="location.href='edit_product.php?id=<?= $product['id'] ?>'">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn-delete" onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['nama_produk']) ?>')">
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
                    <h3>Belum ada Vaksin atau Obat</h3>
                    <p>Tambahkan produk vaksin atau obat untuk mulai mengelola stok</p>
                    <button class="btn-add-product" onclick="location.href='add_product.php'">
                        <i class="fas fa-plus"></i> Tambah Produk
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
                handleFilter();
            }, 500);
        }

        function handleFilter() {
            const search = document.getElementById('searchInput').value;
            const kategori = document.getElementById('kategoriFilter').value;
            window.location.href = `products.php?search=${encodeURIComponent(search)}&kategori=${encodeURIComponent(kategori)}`;
        }

        function resetFilters() {
            window.location.href = 'products.php';
        }

        function deleteProduct(id, name) {
            if (confirm(`Hapus produk "${name}"?\nData yang dihapus tidak dapat dikembalikan.`)) {
                window.location.href = `delete_product.php?id=${id}`;
            }
        }

        // Auto-filter saat ketik
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleFilter();
            }
        });

        document.getElementById('searchInput')?.addEventListener('input', handleSearch);
    </script>          

    <script src="js/sidebar-toggle.js"></script>
</body>
</html>