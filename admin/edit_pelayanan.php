<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products_pelayanan.php?error=ID tidak ditemukan");
    exit;
}

$id = $_GET['id'];
$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : '';

// ================= AMBIL DATA SERVICE =================
$sql = "SELECT * FROM services WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$service = $stmt->get_result()->fetch_assoc();

if (!$service) {
    header("Location: products_pelayanan.php?error=Data tidak ditemukan");
    exit;
}

// ================= AMBIL KATEGORI_USIA =================
$sql_categories = "SELECT DISTINCT kategori_usia FROM services WHERE tipe IN ('pelayanan','paket','jasa') AND kategori_usia IS NOT NULL AND kategori_usia != '' ORDER BY kategori_usia";
$categories_result = $conn->query($sql_categories);

// ================= AMBIL KOMPONEN LAYANAN =================
$produk_components = [];
$jasa_components = [];

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
            $row['tipe'] = 'produk';
            $row['icon'] = 'fa-box';
            $row['color'] = '#3498db';
            $row['badge'] = 'badge-produk';
            $row['badge_text'] = 'PRODUK';
            $produk_components[] = $row;
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
            $row['tipe'] = 'jasa';
            $row['icon'] = 'fa-user-md';
            $row['color'] = '#f39c12';
            $row['badge'] = 'badge-jasa';
            $row['badge_text'] = 'JASA';
            $jasa_components[] = $row;
        }
    }
    
    // Gabungkan semua komponen
    $all_components = array_merge($jasa_components, $produk_components);
}

// ================= AMBIL ITEM PAKET =================
// PAKET (paket) → komponen dari SERVICES (layanan tipe='pelayanan')
$package_items = [];
if ($service['tipe'] === 'paket') {
    $item_sql = "
        SELECT spi.*, 
               s.nama_layanan,
               s.kategori_usia as kategori,
               s.harga,
               'pelayanan' as tipe_item
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
    }
}

// ================= AMBIL SEMUA PRODUK (untuk dropdown komponen layanan) =================
$sql_products = "SELECT id, nama_produk, kategori, harga, satuan FROM products ORDER BY nama_produk ASC";
$products_list = $conn->query($sql_products);

// ================= AMBIL SEMUA JASA (untuk dropdown komponen layanan) =================
$sql_jasa = "SELECT id, nama_layanan, kategori_usia, harga FROM services WHERE tipe = 'jasa' ORDER BY nama_layanan ASC";
$jasa_list = $conn->query($sql_jasa);

// ================= AMBIL SEMUA LAYANAN (untuk dropdown item paket) =================
$sql_layanan = "SELECT id, nama_layanan, kategori_usia, harga FROM services WHERE tipe = 'pelayanan' ORDER BY nama_layanan ASC";
$layanan_list = $conn->query($sql_layanan);

// ================= PROSES POST =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // UPDATE DATA UTAMA
    if ($action === 'update_main') {
        $nama_layanan = $_POST['nama_layanan'];
        $kategori_usia = $_POST['kategori_usia'];
        $harga = str_replace('.', '', $_POST['harga']);
        $deskripsi = $_POST['deskripsi'];
        $kode_paket = $_POST['kode_paket'] ?? '';
        
        $sql_update = "UPDATE services SET 
            nama_layanan = ?,
            kategori_usia = ?,
            harga = ?,
            deskripsi = ?,
            kode_paket = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param('ssissi', 
            $nama_layanan, 
            $kategori_usia,
            $harga, 
            $deskripsi, 
            $kode_paket, 
            $service['id']
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data berhasil diperbarui";
            header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe'] . "&success=update");
            exit;
        } else {
            $error = "Gagal: " . $conn->error;
        }
    }
    
    // TAMBAH KOMPONEN LAYANAN
    if ($action === 'add_component' && $service['tipe'] === 'pelayanan') {
        $component_id = $_POST['component_id'];
        $quantity = $_POST['quantity'] ?? 1;
        $tipe_komponen = $_POST['tipe_komponen'] ?? '';
        
        if (empty($component_id) || empty($tipe_komponen)) {
            $_SESSION['error'] = "Pilih komponen terlebih dahulu";
            header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
            exit;
        }
        
        if ($tipe_komponen === 'jasa') {
            // Insert ke tabel service_jasa_components
            $check_sql = "SELECT id FROM service_jasa_components WHERE service_id = ? AND jasa_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('ii', $service['id'], $component_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $_SESSION['error'] = "Jasa sudah ada dalam layanan ini";
            } else {
                $add_sql = "INSERT INTO service_jasa_components (service_id, jasa_id, quantity) VALUES (?, ?, ?)";
                $add_stmt = $conn->prepare($add_sql);
                $add_stmt->bind_param('iii', $service['id'], $component_id, $quantity);
                
                if ($add_stmt->execute()) {
                    $_SESSION['success'] = "Jasa berhasil ditambahkan";
                } else {
                    $_SESSION['error'] = "Gagal menambahkan jasa: " . $add_stmt->error;
                }
            }
            
        } else {
            // Insert ke tabel service_product_components
            $check_sql = "SELECT id FROM service_product_components WHERE service_id = ? AND product_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param('ii', $service['id'], $component_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $_SESSION['error'] = "Produk sudah ada dalam layanan ini";
            } else {
                $add_sql = "INSERT INTO service_product_components (service_id, product_id, quantity) VALUES (?, ?, ?)";
                $add_stmt = $conn->prepare($add_sql);
                $add_stmt->bind_param('iii', $service['id'], $component_id, $quantity);
                
                if ($add_stmt->execute()) {
                    $_SESSION['success'] = "Produk berhasil ditambahkan";
                } else {
                    $_SESSION['error'] = "Gagal menambahkan produk: " . $add_stmt->error;
                }
            }
        }
        header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
        exit;
    }
    
    // HAPUS KOMPONEN
    if ($action === 'delete_component' && $service['tipe'] === 'pelayanan') {
        // Ambil data dari form
        $component_id = $_POST['component_id'];
        $component_tipe = isset($_POST['component_tipe']) ? $_POST['component_tipe'] : '';
        
        // Validasi
        if (empty($component_id)) {
            $_SESSION['error'] = "ID komponen tidak ditemukan";
            header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
            exit;
        }
        
        if (empty($component_tipe)) {
            $_SESSION['error'] = "Tipe komponen tidak ditemukan";
            header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
            exit;
        }
        
        // Pilih tabel berdasarkan tipe
        if ($component_tipe === 'jasa') {
            $delete_sql = "DELETE FROM service_jasa_components WHERE id = ? AND service_id = ?";
        } elseif ($component_tipe === 'produk') {
            $delete_sql = "DELETE FROM service_product_components WHERE id = ? AND service_id = ?";
        } else {
            $_SESSION['error'] = "Tipe komponen tidak dikenal: " . $component_tipe;
            header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
            exit;
        }
        
        $delete_stmt = $conn->prepare($delete_sql);
        if (!$delete_stmt) {
            $_SESSION['error'] = "Error prepare: " . $conn->error;
            header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
            exit;
        }
        
        $delete_stmt->bind_param('ii', $component_id, $service['id']);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Komponen berhasil dihapus";
        } else {
            $_SESSION['error'] = "Gagal menghapus komponen: " . $delete_stmt->error;
        }
        
        header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
        exit;
    }
    
    // TAMBAH ITEM PAKET (dari layanan)
    if ($action === 'add_package_item' && $service['tipe'] === 'paket') {
        $service_id = $_POST['service_id'];
        $quantity = $_POST['quantity'] ?? 1;
        $visit_order = $_POST['visit_order'] ?? 1;
        
        $check_sql = "SELECT id FROM service_package_items WHERE package_id = ? AND service_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('ii', $service['id'], $service_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $_SESSION['error'] = "Layanan sudah ada dalam paket ini";
        } else {
            $add_sql = "INSERT INTO service_package_items (package_id, service_id, quantity, visit_order) VALUES (?, ?, ?, ?)";
            $add_stmt = $conn->prepare($add_sql);
            $add_stmt->bind_param('iiii', $service['id'], $service_id, $quantity, $visit_order);
            
            if ($add_stmt->execute()) {
                $_SESSION['success'] = "Item paket berhasil ditambahkan";
            } else {
                $_SESSION['error'] = "Gagal menambahkan item paket";
            }
        }
        header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
        exit;
    }
    
    // HAPUS ITEM PAKET
    if ($action === 'delete_package_item' && $service['tipe'] === 'paket') {
        $item_id = $_POST['item_id'];
        
        $delete_sql = "DELETE FROM service_package_items WHERE id = ? AND package_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('ii', $item_id, $service['id']);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Item paket berhasil dihapus";
        } else {
            $_SESSION['error'] = "Gagal menghapus item paket";
        }
        header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
        exit;
    }
    
    // UPDATE VISIT ORDER
    if ($action === 'update_visit_order' && $service['tipe'] === 'paket') {
        $item_id = $_POST['item_id'];
        $visit_order = $_POST['visit_order'];
        
        $update_sql = "UPDATE service_package_items SET visit_order = ? WHERE id = ? AND package_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('iii', $visit_order, $item_id, $service['id']);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = "Urutan kunjungan diperbarui";
        }
        header("Location: edit_pelayanan.php?id=" . $service['id'] . "&tipe=" . $service['tipe']);
        exit;
    }
}

$success_message = $_SESSION['success'] ?? $_GET['success'] ?? '';
$error_message = $_SESSION['error'] ?? $_GET['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?= $service['tipe'] == 'pelayanan' ? 'Layanan' : 'Paket' ?> - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="css/product-form.css">
    <link rel="stylesheet" href="css/edit_pelayanan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .badge-produk { background: #3498db; color: white; }
        .badge-jasa { background: #f39c12; color: white; }
        .badge-layanan { background: #27ae60; color: white; }
        .icon-produk { color: #3498db; }
        .icon-jasa { color: #f39c12; }
        .icon-layanan { color: #27ae60; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="vaksinin-logo.png" alt="Vaksinin" class="logo-full">
            <img src="v-logo.png" alt="V" class="logo-icon">
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
            <a href="javascript:void(0)" class="nav-item has-submenu active open" onclick="toggleSubmenu(this)">
                <i class="fas fa-capsules"></i><span>Produk</span><i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu open">
                <li><a href="products.php">Stok</a></li>
                <li><a href="products_pelayanan.php" class="active">Pelayanan/Paket</a></li>
            </ul>
            <a href="patients.php" class="nav-item"><i class="fas fa-users"></i><span>Pasien</span></a>
            <a href="calendar_setting.php" class="nav-item"><i class="fas fa-calendar"></i><span>Kalender</span></a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="product-container">
            <!-- HEADER -->
            <div class="product-header-wrapper">
                <a href="products_pelayanan.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                <div class="product-title-section">
                    <h1>Edit <?= $service['tipe'] == 'pelayanan' ? 'Layanan' : 'Paket' ?></h1>
                    <p class="product-subtitle">
                        <i class="fas fa-<?= $service['tipe'] == 'pelayanan' ? 'stethoscope' : 'cubes' ?>"></i>
                        <?= htmlspecialchars($service['nama_layanan']) ?>
                        <?php if (!empty($service['kode_paket']) && $service['tipe'] == 'paket'): ?>
                            <span class="product-code"><?= htmlspecialchars($service['kode_paket']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- ALERT -->
            <?php if ($success_message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $success_message ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error_message ?></div>
            <?php endif; ?>

            <!-- FORM EDIT UTAMA -->
            <div class="product-form">
                <form method="POST">
                    <input type="hidden" name="action" value="update_main">
                    <div class="form-grid">
                        <!-- Nama Layanan/Paket -->
                        <div class="form-group">
                            <label>Nama <?= $service['tipe'] == 'pelayanan' ? 'Layanan' : 'Paket' ?> <span class="required">*</span></label>
                            <input type="text" name="nama_layanan" required value="<?= htmlspecialchars($service['nama_layanan']) ?>">
                        </div>

                        <!-- Kode Paket (khusus paket) -->
                        <?php if ($service['tipe'] == 'paket'): ?>
                        <div class="form-group">
                            <label>Kode Paket</label>
                            <input type="text" name="kode_paket" value="<?= htmlspecialchars($service['kode_paket'] ?? '') ?>">
                        </div>
                        <?php endif; ?>

                        <!-- KATEGORI_USIA -->
                        <div class="form-group">
                            <label>Kategori Usia</label>
                            <select name="kategori_usia" class="form-control">
                                <option value="">Pilih Kategori Usia</option>
                                <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                                    <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($cat['kategori_usia']) ?>" 
                                        <?= $service['kategori_usia'] == $cat['kategori_usia'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['kategori_usia']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                                <option value="Anak" <?= $service['kategori_usia'] == 'Anak' ? 'selected' : '' ?>>Anak</option>
                                <option value="Dewasa" <?= $service['kategori_usia'] == 'Dewasa' ? 'selected' : '' ?>>Dewasa</option>
                                <option value="Semua Usia" <?= $service['kategori_usia'] == 'Semua Usia' ? 'selected' : '' ?>>Semua Usia</option>
                            </select>
                        </div>

                        <!-- Harga -->
                        <div class="form-group">
                            <label>Harga <span class="required">*</span></label>
                            <input type="text" name="harga" id="harga" value="<?= number_format($service['harga'] ?? 0, 0, ',', '.') ?>" onkeyup="formatRupiah(this)">
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group full-width">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" rows="4"><?= htmlspecialchars($service['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <a href="products_pelayanan.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <!-- ===== MANAJEMEN KOMPONEN / ITEM ===== -->
            <div class="batch-management">
                <div class="batch-header">
                    <h2 class="section-title">
                        <i class="fas fa-<?= $service['tipe'] == 'pelayanan' ? 'puzzle-piece' : 'layer-group' ?>"></i>
                        <?= $service['tipe'] == 'pelayanan' ? 'Komponen Layanan' : 'Item Paket' ?>
                    </h2>
                </div>

                <!-- ===== UNTUK LAYANAN (pelayanan) ===== -->
                <?php if ($service['tipe'] === 'pelayanan'): ?>
                    
                    <!-- DAFTAR KOMPONEN -->
                    <?php if (!empty($all_components)): ?>
                        <div class="batch-table-container">
                            <table class="batch-table">
                                <thead>
                                    <tr>
                                        <th>Nama Komponen</th>
                                        <th>Tipe</th>
                                        <th>Kategori</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total_komponen = 0;
                                    foreach ($all_components as $comp): 
                                        $total_komponen += ($comp['harga_komponen'] ?? 0) * $comp['quantity'];
                                    ?>
                                    <tr>
                                        <td>
                                            <i class="fas <?= $comp['icon'] ?>" style="color: <?= $comp['color'] ?>; margin-right: 8px;"></i>
                                            <strong><?= htmlspecialchars($comp['nama_komponen']) ?></strong>
                                            <?php if (!empty($comp['satuan'])): ?>
                                                <small style="color: #7f8c8d;">(<?= $comp['satuan'] ?>)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $comp['badge'] ?>"><?= $comp['badge_text'] ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($comp['kategori_komponen'] ?? '-') ?></td>
                                        <td><span class="stock-badge">x <?= $comp['quantity'] ?></span></td>
                                        <td class="price">Rp <?= number_format(($comp['harga_komponen'] ?? 0) * $comp['quantity'], 0, ',', '.') ?></td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('Hapus komponen ini?')">
                                                <input type="hidden" name="action" value="delete_component">
                                                <input type="hidden" name="component_id" value="<?= $comp['id'] ?>">
                                                <input type="hidden" name="component_tipe" value="<?= $comp['tipe'] ?>">
                                                <button type="submit" class="btn-small btn-delete-batch"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" style="text-align:right; padding:12px;">
                                            <span style="color:#7f8c8d;">Total komponen: </span>
                                            <span style="font-weight:600; color:#27ae60;">Rp <?= number_format($total_komponen, 0, ',', '.') ?></span>
                                            <?php if ($total_komponen > $service['harga']): ?>
                                                <span style="color:#e74c3c; margin-left:12px;">
                                                    (Selisih: Rp <?= number_format($total_komponen - $service['harga'], 0, ',', '.') ?>)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-batch">
                            <i class="fas fa-puzzle-piece"></i>
                            <h3>Belum ada komponen</h3>
                            <p>Tambahkan produk (vaksin/obat) atau jasa ke layanan ini</p>
                        </div>
                    <?php endif; ?>

                    <!-- FORM TAMBAH KOMPONEN -->
                    <div class="add-batch-form">
                        <h3 class="add-batch-title"><i class="fas fa-plus-circle"></i> Tambah Komponen</h3>
                        
                        <form method="POST" class="batch-form-grid" id="formTambahKomponen">
                            <input type="hidden" name="action" value="add_component">
                            <input type="hidden" name="tipe_komponen" id="tipe_komponen" value="">
                            
                            <div class="form-group" style="grid-column: span 2;">
                                <label>Pilih <span class="required">*</span></label>
                                <select name="component_id" id="component_select" class="form-control" required onchange="updateTipeKomponen(this)">
                                    <option value="">-- Pilih Komponen --</option>
                                    
                                    <!-- PRODUK -->
                                    <optgroup label="💊 PRODUK (Vaksin & Obat)">
                                        <?php 
                                        if ($products_list && $products_list->num_rows > 0) {
                                            $products_list->data_seek(0);
                                            while($prod = $products_list->fetch_assoc()): 
                                        ?>
                                            <option value="<?= $prod['id'] ?>" data-tipe="produk">
                                                <?= htmlspecialchars($prod['nama_produk']) ?> 
                                                (<?= $prod['satuan'] ?? 'unit' ?> - Rp <?= number_format($prod['harga'] ?? 0, 0, ',', '.') ?>)
                                            </option>
                                        <?php 
                                            endwhile;
                                        }
                                        ?>
                                    </optgroup>
                                    
                                    <!-- JASA -->
                                    <optgroup label="🧑‍⚕️ JASA MEDIS">
                                        <?php 
                                        if ($jasa_list && $jasa_list->num_rows > 0) {
                                            $jasa_list->data_seek(0);
                                            while($jasa = $jasa_list->fetch_assoc()): 
                                        ?>
                                            <option value="<?= $jasa['id'] ?>" data-tipe="jasa">
                                                <?= htmlspecialchars($jasa['nama_layanan']) ?> 
                                                (Rp <?= number_format($jasa['harga'] ?? 0, 0, ',', '.') ?>)
                                            </option>
                                        <?php 
                                            endwhile;
                                        }
                                        ?>
                                    </optgroup>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Jumlah</label>
                                <input type="number" name="quantity" min="1" value="1" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </form>
                    </div>

                <!-- ===== UNTUK PAKET (paket) ===== -->
                <?php else: ?>
                    
                    <!-- DAFTAR ITEM PAKET (dari services tipe='pelayanan') -->
                    <?php if ($package_items && $package_items->num_rows > 0): ?>
                        <div class="batch-table-container">
                            <table class="batch-table">
                                <thead>
                                    <tr>
                                        <th>Nama Layanan</th>
                                        <th>Kategori Usia</th>
                                        <th>Kunjungan</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $package_items->data_seek(0); 
                                    $total_harga_normal = 0; 
                                    while ($item = $package_items->fetch_assoc()): 
                                        $total_harga_normal += ($item['harga'] ?? 0) * ($item['quantity'] ?? 1);
                                    ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-stethoscope icon-layanan" style="margin-right: 8px;"></i>
                                            <strong><?= htmlspecialchars($item['nama_layanan']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($item['kategori'] ?? 'Semua Usia') ?></td>
                                        <td>
                                            <form method="POST" style="display:flex; gap:4px;">
                                                <input type="hidden" name="action" value="update_visit_order">
                                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                                <input type="number" name="visit_order" value="<?= $item['visit_order'] ?>" 
                                                       style="width:50px; padding:4px; border:1px solid #ddd; border-radius:4px;" 
                                                       min="1" onchange="this.form.submit()">
                                            </form>
                                        </td>
                                        <td><span class="stock-badge">x <?= $item['quantity'] ?></span></td>
                                        <td class="price">Rp <?= number_format(($item['harga'] ?? 0) * $item['quantity'], 0, ',', '.') ?></td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('Hapus item ini?')">
                                                <input type="hidden" name="action" value="delete_package_item">
                                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                                <button type="submit" class="btn-small btn-delete-batch"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <?php if ($total_harga_normal > $service['harga']): ?>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" style="text-align:right; padding:12px;">
                                            <span style="color:#7f8c8d;">Harga normal: </span>
                                            <span style="text-decoration:line-through;">Rp <?= number_format($total_harga_normal, 0, ',', '.') ?></span>
                                            <span style="color:#27ae60; font-weight:600; margin-left:12px;">
                                                Hemat Rp <?= number_format($total_harga_normal - $service['harga'], 0, ',', '.') ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state-batch">
                            <i class="fas fa-cubes"></i>
                            <h3>Belum ada item paket</h3>
                            <p>Tambahkan layanan ke dalam paket ini</p>
                        </div>
                    <?php endif; ?>

                    <!-- FORM TAMBAH ITEM PAKET -->
                    <div class="add-batch-form">
                        <h3 class="add-batch-title"><i class="fas fa-plus-circle"></i> Tambah Item Paket</h3>
                        <form method="POST" class="batch-form-grid">
                            <input type="hidden" name="action" value="add_package_item">
                            
                            <div class="form-group" style="grid-column: span 2;">
                                <label>Pilih Layanan <span class="required">*</span></label>
                                <select name="service_id" class="form-control" required>
                                    <option value="">-- Pilih Layanan --</option>
                                    
                                    <!-- KELOMPOK LAYANAN VAKSINASI (tipe='pelayanan') -->
                                    <?php 
                                    $layanan_list->data_seek(0);
                                    while($layanan = $layanan_list->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $layanan['id'] ?>">
                                            <?= htmlspecialchars($layanan['nama_layanan']) ?> 
                                            (Rp <?= number_format($layanan['harga'] ?? 0, 0, ',', '.') ?>)
                                            - <?= htmlspecialchars($layanan['kategori_usia'] ?? 'Semua Usia') ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Kunjungan ke-</label>
                                <input type="number" name="visit_order" value="<?= ($package_items ? $package_items->num_rows + 1 : 1) ?>" min="1" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Jumlah</label>
                                <input type="number" name="quantity" value="1" min="1" class="form-control" required>
                            </div>
                            
                            <div class="form-group" style="grid-column:1/-1;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah ke Paket
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- INFO SISTEM & TOMBOL HAPUS -->
            <div style="margin-top:32px; display:flex; justify-content:flex-end;">
                <div style="background:#f8fafc; padding:16px 24px; border-radius:12px; display:flex; gap:24px; align-items:center;">
                    <div>
                        <small>Dibuat</small>
                        <strong><?= date('d/m/Y H:i', strtotime($service['created_at'])) ?></strong>
                    </div>
                    <div style="width:1px; height:30px; background:#e1e8ed;"></div>
                    <div>
                        <small>Update</small>
                        <strong><?= $service['updated_at'] ? date('d/m/Y H:i', strtotime($service['updated_at'])) : '-' ?></strong>
                    </div>
                    <button class="btn btn-danger" onclick="if(confirm('Hapus <?= $service['tipe'] ?> ini?')) location.href='delete_pelayanan.php?id=<?= $service['id'] ?>'">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formatRupiah(input) {
            let value = input.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let remainder = split[0].length % 3;
            let rupiah = split[0].substr(0, remainder);
            let thousand = split[0].substr(remainder).match(/\d{3}/gi);
            if (thousand) {
                let separator = remainder ? '.' : '';
                rupiah += separator + thousand.join('.');
            }
            input.value = rupiah;
        }
        document.addEventListener('DOMContentLoaded', function() {
            const hargaInput = document.getElementById('harga');
            if (hargaInput && hargaInput.value) formatRupiah(hargaInput);
        });
    </script>
    
    <script>
    function updateTipeKomponen(select) {
        const selectedOption = select.options[select.selectedIndex];
        const tipe = selectedOption.getAttribute('data-tipe');
        document.getElementById('tipe_komponen').value = tipe || '';
    }
    </script>
    <script src="js/sidebar-toggle.js"></script>
</body>
</html>