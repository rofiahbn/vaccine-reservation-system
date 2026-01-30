<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/product-form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="vaksinin-logo.png" alt="Vaksinin">
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="products.php" class="nav-item active">
                <i class="fas fa-capsules"></i>
                <span>Produk</span>
            </a>
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
        <header class="page-header-form">
            <a href="products.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Tambah Produk</h1>
            <div class="notification-icon">
                <i class="fas fa-bell"></i>
            </div>
        </header>

        <div class="form-container">
            <div class="form-layout">
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

                <!-- Right Side - Form -->
                <div class="form-section">
                    <form id="productForm" method="POST" action="save_product.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="image_data" id="imageData">

                        <div class="form-group">
                            <label>Pilih Jenis</label>
                            <select name="jenis" id="jenis" required>
                                <option value="">Vaksin</option>
                                <option value="Vaksin">Vaksin</option>
                                <option value="Obat">Obat</option>
                                <option value="Vitamin">Vitamin</option>
                                <option value="Alat Kesehatan">Alat Kesehatan</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Vaksin</label>
                                <input type="text" name="nama_layanan" placeholder="Nama" required>
                            </div>
                            <div class="form-group">
                                <label>Expired</label>
                                <input type="date" name="expired_date" placeholder="Expired">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Kategori</label>
                                <input type="text" name="kategori" placeholder="Kategori" required>
                            </div>
                            <div class="form-group">
                                <label>Batch Number</label>
                                <input type="text" name="batch_number" placeholder="Batch Number">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Harga Standard</label>
                                <input type="number" name="harga" placeholder="Harga" required>
                            </div>
                            <div class="form-group">
                                <label>Jumlah Stok</label>
                                <input type="number" name="stock" placeholder="100">
                            </div>
                            <div class="form-group">
                                <label>Set Low Stock</label>
                                <input type="number" name="low_stock" placeholder="10">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Harga Spesial</label>
                                <input type="number" name="harga_special" placeholder="Harga">
                            </div>
                            <div class="form-group">
                                <label>Diskon</label>
                                <input type="number" name="harga_diskon" placeholder="Harga">
                            </div>
                            <div class="form-group">
                                <label>Periode</label>
                                <input type="text" name="periode_diskon" placeholder="Periode">
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Selesai</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="js/product-form.js"></script>
</body>
</html>