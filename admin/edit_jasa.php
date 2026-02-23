<?php
session_start();
include "../config.php";

// ✅ Set timezone Jakarta
date_default_timezone_set('Asia/Jakarta');

// Tambahkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$current_page = 'products_jasa.php';

// Cek parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products_jasa.php?error=ID jasa tidak ditemukan");
    exit;
}

$jasa_id = $_GET['id'];

// ================= AMBIL DATA JASA =================
$sql = "SELECT * FROM services WHERE id = ? AND tipe = 'jasa'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $jasa_id);
$stmt->execute();
$jasa = $stmt->get_result()->fetch_assoc();

if (!$jasa) {
    header("Location: products_jasa.php?error=Jasa tidak ditemukan");
    exit;
}

// ================= PROSES UPDATE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_jasa') {
        $nama_layanan = trim($_POST['nama_layanan']);
        $kode_layanan = trim($_POST['kode_layanan']);
        $kategori_usia = $_POST['kategori_usia'];
        $harga = str_replace('.', '', $_POST['harga']);
        $harga = is_numeric($harga) ? $harga : 0;
        $deskripsi = trim($_POST['deskripsi']);

        // Validasi
        if (empty($nama_layanan)) {
            $error = "Nama jasa harus diisi";
        } elseif (empty($kategori_usia)) {
            $error = "Kategori usia harus dipilih";
        } elseif ($harga <= 0) {
            $error = "Harga harus diisi dengan benar";
        } else {
            $sql_update = "UPDATE services SET 
                nama_layanan = ?, 
                kode_layanan = ?,
                kategori_usia = ?, 
                harga = ?, 
                deskripsi = ?,
                updated_at = NOW()
                WHERE id = ? AND tipe = 'jasa'";
            
            $stmt = $conn->prepare($sql_update);
            
            if ($stmt) {
                $stmt->bind_param('sssisi', 
                    $nama_layanan, 
                    $kode_layanan, 
                    $kategori_usia, 
                    $harga, 
                    $deskripsi, 
                    $jasa_id
                );
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Jasa berhasil diperbarui";
                    header("Location: edit_jasa.php?id=$jasa_id&success=update");
                    exit;
                } else {
                    $error = "Gagal memperbarui jasa: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Error dalam prepared statement: " . $conn->error;
            }
        }
    }
}

// Ambil pesan session
$success_message = $_SESSION['success'] ?? $_GET['success'] ?? '';
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jasa - <?= htmlspecialchars($jasa['nama_layanan']) ?></title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="css/product-form.css">
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
                <li>
                    <a href="products.php">
                        <i class="fas fa-box"></i>
                        Stok
                    </a>
                </li>
                <li>
                    <a href="products_pelayanan.php">
                        <i class="fas fa-package"></i>
                        Pelayanan/Paket
                    </a>
                </li>
                <li>
                    <a href="products_jasa.php" class="active">
                        <i class="fas fa-hand-holding-medical"></i>
                        Jasa
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
            <a href="settings.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
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
                    <h1>Edit Jasa</h1>
                    <p class="product-subtitle">
                        <i class="fas fa-hand-holding-medical"></i> 
                        <?= htmlspecialchars($jasa['nama_layanan']) ?>
                        <?php if (!empty($jasa['kode_layanan'])): ?>
                            <span class="product-code"><?= htmlspecialchars($jasa['kode_layanan']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                        $messages = [
                            'update' => 'Jasa berhasil diperbarui'
                        ];
                        echo $messages[$success_message] ?? 'Data berhasil disimpan';
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Form Edit Jasa -->
            <div class="product-form">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_jasa">
                    
                    <div class="form-grid">
                        <!-- Nama Jasa -->
                        <div class="form-group full-width">
                            <label>
                                Nama Jasa 
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_layanan" 
                                   required 
                                   value="<?= htmlspecialchars($jasa['nama_layanan']) ?>"
                                   placeholder="Contoh: Konsultasi Dokter, Suntik Vaksin">
                        </div>

                        <!-- Kode Jasa -->
                        <div class="form-group">
                            <label>Kode Jasa (Opsional)</label>
                            <input type="text" 
                                   name="kode_layanan" 
                                   value="<?= htmlspecialchars($jasa['kode_layanan'] ?? '') ?>"
                                   placeholder="Contoh: JSA-001">
                        </div>

                        <!-- Kategori Usia -->
                        <div class="form-group">
                            <label>
                                Kategori Usia 
                                <span class="required">*</span>
                            </label>
                            <select name="kategori_usia" required>
                                <option value="">Pilih Kategori Usia</option>
                                <option value="Anak" <?= $jasa['kategori_usia'] == 'Anak' ? 'selected' : '' ?>>Anak (0-18 tahun)</option>
                                <option value="Dewasa" <?= $jasa['kategori_usia'] == 'Dewasa' ? 'selected' : '' ?>>Dewasa (>18 tahun)</option>
                                <option value="Semua Usia" <?= $jasa['kategori_usia'] == 'Semua Usia' ? 'selected' : '' ?>>Semua Usia</option>
                            </select>
                        </div>

                        <!-- Harga -->
                        <div class="form-group">
                            <label>
                                Harga Jasa 
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="harga" 
                                   id="harga" 
                                   value="<?= number_format($jasa['harga'] ?? 0, 0, ',', '.') ?>"
                                   placeholder="Rp 0"
                                   onkeyup="formatRupiah(this)"
                                   required>
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group full-width">
                            <label>Deskripsi (Opsional)</label>
                            <textarea name="deskripsi" 
                                      rows="4" 
                                      placeholder="Contoh: Jasa konsultasi dokter umum, Jasa penyuntikan vaksin"><?= htmlspecialchars($jasa['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="products_jasa.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Informasi Tambahan -->
            <div class="batch-management" style="margin-top: 32px;">
                <div class="batch-header">
                    <h2 class="section-title">
                        <i class="fas fa-info-circle"></i> 
                        Informasi Jasa
                    </h2>
                    <span class="badge" style="background: #e8f4fc; color: #2980b9; padding: 8px 16px; border-radius: 20px; font-size: 14px;">
                        <i class="fas fa-tag"></i> Tipe: Jasa Medis
                    </span>
                </div>

                <div style="background: #f8fafc; border-radius: 12px; padding: 24px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                        <div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Dibuat Pada</div>
                            <div style="font-weight: 500; color: #1e293b;">
                                <?= date('d/m/Y H:i', strtotime($jasa['created_at'] ?? date('Y-m-d H:i:s'))) ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Terakhir Update</div>
                            <div style="font-weight: 500; color: #1e293b;">
                                <?= $jasa['updated_at'] ? date('d/m/Y H:i', strtotime($jasa['updated_at'])) : '-' ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">Kategori Usia</div>
                            <div style="font-weight: 500; color: #1e293b;">
                                <span class="category-tag" style="background: #e8f4fc; color: #2980b9; padding: 4px 12px; border-radius: 20px;">
                                    <?= htmlspecialchars($jasa['kategori_usia'] ?? '-') ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Note -->
                    <div style="background: #e8f4fc; border-left: 4px solid #3498db; padding: 16px; margin-top: 24px; border-radius: 8px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <i class="fas fa-info-circle" style="color: #3498db; font-size: 20px;"></i>
                            <div>
                                <strong style="color: #2c3e50; display: block; margin-bottom: 4px;">Informasi</strong>
                                <p style="color: #64748b; margin: 0; font-size: 14px;">
                                    Jasa adalah layanan medis yang dapat ditambahkan ke dalam layanan pelayanan. 
                                    Harga jasa akan dihitung secara terpisah atau dapat digabung dengan produk dalam satu layanan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
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
            if (hargaInput.value) {
                formatRupiah(hargaInput);
            }
        });
    </script>

    <script src="js/sidebar-toggle.js"></script>
</body>
</html>