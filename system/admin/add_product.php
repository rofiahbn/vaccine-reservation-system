<?php 
include "config.php";

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
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="system/admin/css/admin.css"> 
    <link rel="stylesheet" href="system/admin/css/product-form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Style tambahan khusus untuk halaman add product */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .header-left h1 {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .btn-back {
            background: white;
            border: 1.5px solid #e1e8ed;
            padding: 10px 20px;
            border-radius: 8px;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background: #f8fafc;
            border-color: #b0c4ce;
        }
        
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .form-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e1e8ed;
            background: #f8fafc;
        }
        
        .form-card-header h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-card-header i {
            color: #3498db;
        }
        
        .form-card-body {
            padding: 32px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group label .required {
            color: #e74c3c;
            margin-left: 4px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 16px;
            border: 1.5px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            font-family: inherit;
            width: 100%;
        }
        
        .form-group input:hover,
        .form-group select:hover,
        .form-group textarea:hover {
            border-color: #b0c4ce;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-text {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 4px;
        }
        
        .price-input-wrapper {
            display: flex;
            align-items: center;
            border: 1.5px solid #e1e8ed;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .price-input-wrapper:focus-within {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        
        .price-prefix {
            padding: 12px 16px;
            background: #f8fafc;
            border-right: 1.5px solid #e1e8ed;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .price-input-wrapper input {
            border: none;
            border-radius: 0;
            flex: 1;
        }
        
        .price-input-wrapper input:focus {
            box-shadow: none;
        }
        
        .info-box {
            background: #e8f4fc;
            border: 1px solid #b8e0f5;
            border-radius: 12px;
            padding: 20px;
            margin: 32px 0 24px 0;
        }
        
        .info-box-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-weight: 600;
            color: #2980b9;
        }
        
        .info-box-header i {
            font-size: 18px;
        }
        
        .info-box-body p {
            margin: 8px 0;
            color: #2c3e50;
            line-height: 1.5;
        }
        
        .info-box-body .text-muted {
            color: #7f8c8d;
            font-size: 13px;
        }
        
        .btn-save {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-save:hover {
            background: #2980b9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52,152,219,0.2);
        }
        
        .btn-cancel {
            background: white;
            border: 1.5px solid #e1e8ed;
            padding: 12px 32px;
            border-radius: 8px;
            color: #2c3e50;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-cancel:hover {
            background: #f8fafc;
            border-color: #b0c4ce;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 16px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e1e8ed;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .form-card-body {
                padding: 24px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn-save,
            .btn-cancel {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
      <!-- Sidebar -->
  <?php include "content/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="product-container">
            <!-- Header -->
            <div class="product-header-wrapper">
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <div class="product-title-section">
                    <h1>Tambah Produk</h1>
                    <p class="product-subtitle">
                        <i class="fas fa-box"></i>
                        Tambah produk vaksin atau obat baru
                    </p>
                </div>
            </div>

            <!-- Form Tambah Produk -->
            <div class="product-form">
                <form method="POST" action="save_product.php">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-grid">
                        <!-- Kode Produk -->
                        <div class="form-group">
                            <label>
                                Kode Produk 
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="kode_produk" 
                                   required
                                   placeholder="Contoh: FLU-001, HPV-001">
                            <small class="form-text">Kode unik untuk produk</small>
                        </div>

                        <!-- Nama Produk -->
                        <div class="form-group">
                            <label>
                                Nama Produk 
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_produk" 
                                   required
                                   placeholder="Contoh: Vaksin Influenza">
                        </div>

                        <!-- Merk (BARU) -->
                        <div class="form-group">
                            <label>Merk</label>
                            <input type="text" 
                                   name="merk" 
                                   placeholder="Contoh: BioFarma, Sanofi, GSK">
                            <small class="form-text">Merk atau pabrikan produk</small>
                        </div>

                        <!-- Jenis -->
                        <div class="form-group">
                            <label>
                                Jenis 
                                <span class="required">*</span>
                            </label>
                            <select name="jenis" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="Vaksin">Vaksin</option>
                                <option value="Obat">Obat</option>
                                <option value="Vitamin">Vitamin</option>
                                <option value="Alat Kesehatan">Alat Kesehatan</option>
                                <option value="Konsumsi">Konsumsi</option>
                            </select>
                        </div>

                        <!-- Kategori Usia -->
                        <div class="form-group">
                            <label>
                                Kategori Usia 
                                <span class="required">*</span>
                            </label>
                            <select name="kategori" required>
                                <option value="">-- Pilih Kategori Usia --</option>
                                <option value="Anak" <?= ($product['kategori'] ?? '') == 'Anak' ? 'selected' : '' ?>>Anak (0-18 tahun)</option>
                                <option value="Dewasa" <?= ($product['kategori'] ?? '') == 'Dewasa' ? 'selected' : '' ?>>Dewasa (>18 tahun)</option>
                                <option value="Semua Usia" <?= ($product['kategori'] ?? '') == 'Semua Usia' ? 'selected' : '' ?>>Semua Usia</option>
                            </select>
                        </div>

                        <!-- Satuan -->
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

                        <!-- Harga -->
                        <div class="form-group">
                            <label>
                                Harga 
                                <span class="required">*</span>
                            </label>
                            <div class="price-input-wrapper">
                                <span class="price-prefix">Rp</span>
                                <input type="text" 
                                       name="harga" 
                                       id="harga"
                                       placeholder="0"
                                       onkeyup="formatRupiah(this)"
                                       required>
                            </div>
                            <small class="form-text">Harga jual produk</small>
                        </div>

                        <!-- Minimal Stok -->
                        <div class="form-group">
                            <label>Minimal Stok</label>
                            <input type="number" 
                                   name="minimal_stok" 
                                   placeholder="10" 
                                   value="10" 
                                   min="1">
                            <small class="form-text">Peringatan stok menipis jika di bawah nilai ini</small>
                        </div>

                        <div class="form-group">
                            <!-- Empty untuk spacing -->
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group full-width">
                            <label>Deskripsi Produk</label>
                            <textarea name="deskripsi" 
                                      rows="4" 
                                      placeholder="Masukkan deskripsi produk..."></textarea>
                            <small class="form-text">Informasi tambahan tentang produk</small>
                        </div>
                    </div>

                    <!-- Informasi Batch & Stok -->
                    <div class="info-box" style="margin: 24px 0;">
                        <div class="info-box-header">
                            <i class="fas fa-info-circle"></i>
                            <span>Informasi Batch dan Stok</span>
                        </div>
                        <div class="info-box-body">
                            <p>Batch number, expired date, dan jumlah stok dapat ditambahkan setelah produk tersimpan melalui halaman <strong>Edit Produk</strong>.</p>
                            <p class="text-muted">Setelah produk tersimpan, Anda dapat menambahkan beberapa batch dengan nomor batch dan tanggal expired yang berbeda.</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Format Rupiah
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
            
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            input.value = rupiah;
        }

        // Auto-format harga saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const hargaInput = document.getElementById('harga');
            if (hargaInput && hargaInput.value) {
                formatRupiah(hargaInput);
            }
        });
    </script>

    <script src="system/admin/js/sidebar-toggle.js"></script>
</body>
</html>