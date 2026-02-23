<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

// Ambil daftar kategori usia untuk dropdown
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
    <title>Tambah Jasa - Vaksinin</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="css/product-form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Style tambahan khusus untuk halaman add jasa */
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
                <li><a href="products.php">Stok</a></li>
                <li><a href="products_pelayanan.php">Pelayanan/Paket</a></li>
                <li><a href="products_jasa.php" class="active">Jasa</a></li>
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
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="product-container">
            <!-- Header -->
            <div class="product-header-wrapper">
                <a href="products_jasa.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <div class="product-title-section">
                    <h1>Tambah Jasa</h1>
                    <p class="product-subtitle">
                        <i class="fas fa-hand-holding-medical"></i>
                        Tambah layanan jasa baru
                    </p>
                </div>
            </div>

            <!-- Form Tambah Jasa -->
            <div class="product-form">
                <form method="POST" action="save_service.php" id="jasaForm">
                    <input type="hidden" name="tipe" value="jasa">
                    
                    <div class="form-grid">
                        <!-- Kode Jasa -->
                        <div class="form-group">
                            <label>Kode Jasa</label>
                            <input type="text" 
                                   name="kode_layanan" 
                                   placeholder="Contoh: JSA-001">
                            <small class="form-text">Kode unik untuk jasa (opsional)</small>
                        </div>

                        <!-- Nama Jasa -->
                        <div class="form-group">
                            <label>
                                Nama Jasa 
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_layanan" 
                                   required
                                   placeholder="Contoh: Konsultasi Dokter, Pemeriksaan Kesehatan">
                        </div>

                        <!-- Kategori Usia -->
                        <div class="form-group">
                            <label>Kategori Usia</label>
                            <select name="kategori_usia">
                                <option value="">-- Pilih Kategori --</option>
                                
                                <?php 
                                // Kategori default
                                $default_categories = [
                                    'Anak',
                                    'Dewasa',
                                    'Semua Usia'
                                ];
                                
                                // Gabungkan kategori dari database
                                $all_categories = $default_categories;
                                
                                if ($categories_result && $categories_result->num_rows > 0) {
                                    while ($cat = $categories_result->fetch_assoc()) {
                                        $kategori_db = $cat['kategori_usia'];
                                        if (!in_array($kategori_db, $all_categories) && !empty($kategori_db)) {
                                            $all_categories[] = $kategori_db;
                                        }
                                    }
                                }
                                
                                // Tampilkan semua kategori (unique)
                                foreach ($all_categories as $kategori): 
                                ?>
                                    <option value="<?= htmlspecialchars($kategori) ?>">
                                        <?= htmlspecialchars($kategori) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text">Target usia layanan jasa</small>
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
                                       name="harga_display" 
                                       id="harga_display"
                                       placeholder="0"
                                       onkeyup="formatRupiah(this)"
                                       required>
                                <input type="hidden" name="harga" id="harga">
                            </div>
                            <small class="form-text">Harga layanan jasa</small>
                        </div>

                        <!-- Kode Paket -->
                        <div class="form-group">
                            <label>Kode Paket</label>
                            <input type="text" 
                                   name="kode_paket" 
                                   placeholder="Contoh: PKT-001">
                            <small class="form-text">Kode paket (opsional)</small>
                        </div>

                        <div class="form-group">
                            <!-- Empty untuk spacing -->
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group full-width">
                            <label>Deskripsi Jasa</label>
                            <textarea name="deskripsi" 
                                      rows="4" 
                                      placeholder="Masukkan deskripsi layanan jasa..."></textarea>
                            <small class="form-text">Informasi tambahan tentang layanan jasa</small>
                        </div>
                    </div>

                    <!-- Informasi Tambahan -->
                    <div class="info-box">
                        <div class="info-box-header">
                            <i class="fas fa-info-circle"></i>
                            <span>Informasi Layanan Jasa</span>
                        </div>
                        <div class="info-box-body">
                            <p>Layanan jasa adalah layanan yang tidak memerlukan stok seperti konsultasi dokter, pemeriksaan kesehatan, atau layanan medis lainnya.</p>
                            <p class="text-muted">Jasa yang ditambahkan akan muncul di halaman booking sebagai pilihan layanan yang dapat dipesan oleh pasien.</p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="products_jasa.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Jasa
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
            
            // Set hidden input dengan nilai asli (tanpa format)
            document.getElementById('harga').value = value.replace(/\./g, '');
        }

        // Form submission handler
        document.getElementById('jasaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Hapus harga_display (field untuk tampilan)
            delete data.harga_display;
            
            // Convert harga ke integer
            data.harga = parseInt(data.harga) || 0;
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
            
            // Send data via AJAX
            fetch('save_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('✅ Jasa berhasil ditambahkan!');
                    // Redirect ke halaman products_jasa
                    window.location.href = 'products_jasa.php';
                } else {
                    alert('❌ Gagal menyimpan data: ' + (data.message || ''));
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menyimpan data');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Auto-format harga saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const hargaInput = document.getElementById('harga_display');
            if (hargaInput && hargaInput.value) {
                formatRupiah(hargaInput);
            }
        });
    </script>

    <script src="js/sidebar-toggle.js"></script>
</body>
</html>