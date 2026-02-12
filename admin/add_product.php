<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

// Ambil daftar kategori untuk dropdown
$sql_categories = "SELECT DISTINCT kategori FROM products WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori";
$categories_result = $conn->query($sql_categories);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/product-form.css">
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
                class="nav-item has-submenu active open" 
                onclick="toggleSubmenu(this)">
                <i class="fas fa-capsules"></i>
                <span>Produk</span>
                <i class="fas fa-chevron-down arrow"></i>
            </a>
            <ul class="submenu open">
                <li><a href="products.php" class="active">Vaksin & Obat</a></li>
                <li><a href="products_pelayanan.php">Pelayanan/Paket</a></li>
            </ul>
            <a href="patients.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
            </a>
            <a href="calendar_setting.php" class="nav-item">
                <i class="fas fa-calendar"></i>
                <span>Kalender</span>
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
            <div class="header-left">
                <a href="products.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1>Tambah Produk</h1>
            </div>
        </header>

        <div class="form-container">
            <div class="form-card">
                <div class="form-card-header">
                    <h3><i class="fas fa-info-circle"></i> Informasi Produk</h3>
                </div>
                <div class="form-card-body">
                    <form id="productForm" method="POST" action="save_product.php">
                        <input type="hidden" name="action" value="add">

                        <!-- Baris 1: Kode Produk & Nama Produk -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Kode Produk <span class="required">*</span></label>
                                <input type="text" name="kode_produk" placeholder="Contoh: FLU-001, HPV-001" required>
                                <small class="form-text">Kode unik untuk produk</small>
                            </div>
                            <div class="form-group">
                                <label>Nama Produk <span class="required">*</span></label>
                                <input type="text" name="nama_produk" placeholder="Contoh: Vaksin Influenza" required>
                            </div>
                        </div>

                        <!-- Baris 2: Jenis & Kategori -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Jenis <span class="required">*</span></label>
                                <select name="jenis" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="Vaksin">Vaksin</option>
                                    <option value="Obat">Obat</option>
                                    <option value="Vitamin">Vitamin</option>
                                    <option value="Alat Kesehatan">Alat Kesehatan</option>
                                    <option value="Konsumsi">Konsumsi</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Kategori <span class="required">*</span></label>
                                <select name="kategori" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Influenza">Influenza</option>
                                    <option value="HPV">HPV</option>
                                    <option value="Hepatitis">Hepatitis</option>
                                    <option value="COVID-19">COVID-19</option>
                                    <option value="Antibiotik">Antibiotik</option>
                                    <option value="Antipiretik">Antipiretik</option>
                                    <option value="Vitamin">Vitamin</option>
                                    <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                                        <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                            <option value="<?= htmlspecialchars($cat['kategori']) ?>"><?= htmlspecialchars($cat['kategori']) ?></option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Baris 3: Satuan & Harga -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Satuan</label>
                                <select name="satuan">
                                    <option value="dosis">Dosis</option>
                                    <option value="tablet">Tablet</option>
                                    <option value="kapsul">Kapsul</option>
                                    <option value="botol">Botol</option>
                                    <option value="ampul">Ampul</option>
                                    <option value="vial">Vial</option>
                                    <option value="buah">Buah</option>
                                </select>
                                <small class="form-text">Satuan produk</small>
                            </div>
                            <div class="form-group">
                                <label>Harga <span class="required">*</span></label>
                                <div class="price-input-wrapper">
                                    <span class="price-prefix">Rp</span>
                                    <input type="number" name="harga" placeholder="0" min="0" step="1000" required>
                                </div>
                                <small class="form-text">Harga jual produk</small>
                            </div>
                        </div>

                        <!-- Baris 4: Minimal Stok -->
                        <div class="form-row">
                            <div class="form-group">
                                <label>Minimal Stok</label>
                                <input type="number" name="minimal_stok" placeholder="10" value="10" min="1">
                                <small class="form-text">Peringatan stok menipis jika di bawah nilai ini</small>
                            </div>
                            <div class="form-group">
                                <!-- Empty for spacing -->
                            </div>
                        </div>

                        <!-- Baris 5: Deskripsi -->
                        <div class="form-group full-width">
                            <label>Deskripsi Produk</label>
                            <textarea name="deskripsi" rows="4" placeholder="Masukkan deskripsi produk..."></textarea>
                            <small class="form-text">Informasi tambahan tentang produk</small>
                        </div>

                        <!-- Informasi Batch & Stok -->
                        <div class="info-box">
                            <div class="info-box-header">
                                <i class="fas fa-info-circle"></i>
                                <span>Informasi Batch dan Stok</span>
                            </div>
                            <div class="info-box-body">
                                <p>Batch number, expired date, dan jumlah stok dapat ditambahkan setelah produk tersimpan melalui halaman <strong>Edit Produk</strong>.</p>
                                <p class="text-muted">Setelah produk tersimpan, Anda dapat menambahkan beberapa batch dengan nomor batch dan tanggal expired yang berbeda.</p>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> Simpan Produk
                            </button>
                            <a href="products.php" class="btn-cancel">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="js/sidebar-toggle.js"></script>
</body>
</html>