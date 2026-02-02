<?php
session_start();
include "../config.php";
date_default_timezone_set('Asia/Jakarta');
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pelayanan - Vaksinin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- WAJIB SAMA -->
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/add-pelayanan.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- ================= SIDEBAR (SAMA 100%) ================= -->
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
            <li>
                <a href="products.php">Vaksin</a>
            </li>
            <li>
                <a href="products_pelayanan.php" class="active">Pelayanan</a>
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

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">

<header class="page-header-form">
            <a href="products_pelayanan.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Tambah Pelayanan</h1>
        </header>

<div class="form-container">
<div class="form-layout">

<!-- ================= KIRI (UPLOAD + KETERANGAN) ================= -->
<!-- Left Side - Image Upload -->
                <div class="image-upload-section">
                    <div class="upload-box" id="uploadBox">
                        <div class="upload-placeholder" id="uploadPlaceholder">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Tambahkan<br>gambar</p>
                        </div>
                        <img id="imagePreview" style="display: none; width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                        <input type="file" id="productImage" accept="image/*" style="display: none;">
                    </div>
                    <div class="image-info">
                        <h3>Keterangan Produk</h3>
                        <textarea id="keterangan" name="keterangan" rows="10" placeholder="Tambahkan keterangan produk..."></textarea>
                    </div>
                </div>

<!-- ================= KANAN (FORM) ================= -->
<div class="form-section">
<form method="POST" action="save_pelayanan.php">

    <input type="hidden" name="product_category" value="pelayanan">

    <div class="form-group">
        <label>Pilih Jenis<span class="required">*</span></label>
        <select name="jenis" required>
            <option value="">Pilih Jenis</option>
            <option value="Pelayanan">Pelayanan</option>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Nama Layanan<span class="required">*</span></label>
            <input type="text" name="nama_layanan" placeholder="Nama" required>
        </div>
        <div class="form-group">
            <label>Durasi Layanan</label>
            <input type="number" name="durasi" placeholder="Menit">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Harga Standard<span class="required">*</span></label>
            <input type="number" name="harga" placeholder="Harga" required>
        </div>
        <div class="form-group">
            <label>Diskon</label>
            <input type="number" name="harga_diskon" placeholder="Harga">
        </div>
        <div class="form-group">
            <label>Periode</label>
            <input type="text" name="periode_diskon" placeholder="Periode">
        </div>
        <div class="form-group">
        <label>Harga Spesial</label>
        <input type="number" name="harga_special" placeholder="Harga">
        </div>
    </div>

    <button type="submit" class="btn-submit" submit-bottom>
        Selesai
    </button>

</form>
</div>

</div>
</div>
</div>

<!-- ================= SCRIPT ================= -->
<script src="js/sidebar-toggle.js"></script>
</body>
</html>
