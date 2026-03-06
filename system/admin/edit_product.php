<?php 

 error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

// ✅ Set timezone Jakarta
date_default_timezone_set('Asia/Jakarta');

$current_page = 'products.php';

// Cek parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php?error=ID produk tidak ditemukan");
    exit;
}

$product_id = $_GET['id'];

// ================= AMBIL DATA PRODUK =================
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: products.php?error=Produk tidak ditemukan");
    exit;
}

// ================= AMBIL DATA BATCH =================
$sql_batch = "SELECT * FROM product_stock WHERE product_id = ? ORDER BY expired_date ASC";
$stmt_batch = $conn->prepare($sql_batch);
$stmt_batch->bind_param('i', $product_id);
$stmt_batch->execute();
$batches = $stmt_batch->get_result();

// ================= PROSES UPDATE =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // UPDATE PRODUK
    if ($action === 'update_product') {
        $nama_produk = $_POST['nama_produk'];
        $kode_produk = $_POST['kode_produk'];
        $merk = $_POST['merk'] ?? ''; // Tambahkan merk
        $jenis = $_POST['jenis'];
        $kategori = $_POST['kategori'];
        $satuan = $_POST['satuan'];
        $deskripsi = $_POST['deskripsi'];
        $minimal_stok = $_POST['minimal_stok'];

        $sql_update = "UPDATE products SET 
            nama_produk = ?, 
            kode_produk = ?, 
            merk = ?,  -- Tambahkan field merk
            jenis = ?, 
            kategori = ?, 
            satuan = ?, 
            deskripsi = ?,
            minimal_stok = ?,
            updated_at = NOW()
            WHERE id = ?";
        
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param('sssssssii', 
            $nama_produk, 
            $kode_produk, 
            $merk,  // Bind parameter untuk merk
            $jenis, 
            $kategori, 
            $satuan, 
            $deskripsi, 
            $minimal_stok, 
            $product_id
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Produk berhasil diperbarui";
            header("Location: edit_product.php?id=$product_id&success=update");
            exit;
        } else {
            $error = "Gagal memperbarui produk";
        }
    }

    // TAMBAH BATCH
    if ($action === 'add_batch') {
        $batch_number = $_POST['batch_number'];
        $expired_date = $_POST['expired_date'];
        $stock = $_POST['stock'];

        $sql_add_batch = "INSERT INTO product_stock (product_id, batch_number, expired_date, stock, created_at) 
                         VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql_add_batch);
        $stmt->bind_param('issi', $product_id, $batch_number, $expired_date, $stock);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Batch berhasil ditambahkan";
            header("Location: edit_product.php?id=$product_id&success=batch_added");
            exit;
        }
    }

    // UPDATE BATCH
    if ($action === 'update_batch') {
        $batch_id = $_POST['batch_id'];
        $batch_number = $_POST['batch_number'];
        $expired_date = $_POST['expired_date'];
        $stock = $_POST['stock'];

        $sql_update_batch = "UPDATE product_stock SET 
            batch_number = ?, 
            expired_date = ?, 
            stock = ?,
            updated_at = NOW()
            WHERE id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql_update_batch);
        $stmt->bind_param('ssiii', $batch_number, $expired_date, $stock, $batch_id, $product_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Batch berhasil diperbarui";
            header("Location: edit_product.php?id=$product_id&success=batch_updated");
            exit;
        }
    }

    // HAPUS BATCH
    if ($action === 'delete_batch') {
        $batch_id = $_POST['batch_id'];
        
        $sql_delete_batch = "DELETE FROM product_stock WHERE id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql_delete_batch);
        $stmt->bind_param('ii', $batch_id, $product_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Batch berhasil dihapus";
            header("Location: edit_product.php?id=$product_id&success=batch_deleted");
            exit;
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
    <title>Edit Produk - <?= htmlspecialchars($product['nama_produk']) ?></title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="system/admin/css/admin.css"> 
    <link rel="stylesheet" href="system/admin/css/product-form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <h1>Edit Produk</h1>
                    <p class="product-subtitle">
                        <i class="fas fa-box"></i> 
                        <?= htmlspecialchars($product['nama_produk']) ?>
                        <?php if (!empty($product['kode_produk'])): ?>
                            <span class="product-code"><?= htmlspecialchars($product['kode_produk']) ?></span>
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
                            'update' => 'Produk berhasil diperbarui',
                            'batch_added' => 'Batch baru berhasil ditambahkan',
                            'batch_updated' => 'Batch berhasil diperbarui',
                            'batch_deleted' => 'Batch berhasil dihapus'
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

            <!-- Form Edit Produk -->
            <div class="product-form">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_product">
                    
                    <div class="form-grid">
                        <!-- Nama Produk -->
                        <div class="form-group">
                            <label>
                                Nama Produk 
                                <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="nama_produk" 
                                   required 
                                   value="<?= htmlspecialchars($product['nama_produk']) ?>"
                                   placeholder="Contoh: Vaksin COVID-19">
                        </div>

                        <div class="form-group">
                            <label>Merk</label>
                            <input type="text" 
                                name="merk" 
                                value="<?= htmlspecialchars($product['merk'] ?? '') ?>"
                                placeholder="Contoh: BioFarma, Sanofi, GSK">
                        </div>

                        <!-- Kode Produk -->
                        <div class="form-group">
                            <label>Kode Produk</label>
                            <input type="text" 
                                   name="kode_produk" 
                                   value="<?= htmlspecialchars($product['kode_produk'] ?? '') ?>"
                                   placeholder="Contoh: VXN-001">
                        </div>

                        <!-- Jenis -->
                        <div class="form-group">
                            <label>
                                Jenis 
                                <span class="required">*</span>
                            </label>
                            <select name="jenis" required>
                                <option value="">Pilih Jenis</option>
                                <option value="Vaksin" <?= $product['jenis'] == 'Vaksin' ? 'selected' : '' ?>>Vaksin</option>
                                <option value="Obat" <?= $product['jenis'] == 'Obat' ? 'selected' : '' ?>>Obat</option>
                                <option value="Vitamin" <?= $product['jenis'] == 'Vitamin' ? 'selected' : '' ?>>Vitamin</option>
                                <option value="Alat Kesehatan" <?= $product['jenis'] == 'Alat Kesehatan' ? 'selected' : '' ?>>Alat Kesehatan</option>
                                <option value="Lainnya" <?= $product['jenis'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
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
                            <label>
                                Satuan 
                                <span class="required">*</span>
                            </label>
                            <select name="satuan" required>
                                <option value="dosis" <?= ($product['satuan'] ?? '') == 'dosis' ? 'selected' : '' ?>>Dosis</option>
                                <option value="botol" <?= ($product['satuan'] ?? '') == 'botol' ? 'selected' : '' ?>>Botol</option>
                                <option value="tablet" <?= ($product['satuan'] ?? '') == 'tablet' ? 'selected' : '' ?>>Tablet</option>
                                <option value="kaplet" <?= ($product['satuan'] ?? '') == 'kaplet' ? 'selected' : '' ?>>Kaplet</option>
                                <option value="ampul" <?= ($product['satuan'] ?? '') == 'ampul' ? 'selected' : '' ?>>Ampul</option>
                                <option value="vial" <?= ($product['satuan'] ?? '') == 'vial' ? 'selected' : '' ?>>Vial</option>
                                <option value="buah" <?= ($product['satuan'] ?? '') == 'buah' ? 'selected' : '' ?>>Buah</option>
                            </select>
                        </div>

                        <!-- Minimal Stok -->
                        <div class="form-group">
                            <label>Minimal Stok</label>
                            <input type="number" 
                                   name="minimal_stok" 
                                   min="0"
                                   value="<?= htmlspecialchars($product['minimal_stok'] ?? 10) ?>"
                                   placeholder="Contoh: 10">
                            <small style="color: #7f8c8d; font-size: 12px;">
                                <i class="fas fa-info-circle"></i> Peringatan saat stok di bawah jumlah ini
                            </small>
                        </div>

                        <!-- Harga -->
                        <div class="form-group">
                            <label>Harga</label>
                            <input type="text" 
                                   name="harga" 
                                   id="harga" 
                                   value="<?= number_format($product['harga'] ?? 0, 0, ',', '.') ?>"
                                   placeholder="Rp 0"
                                   onkeyup="formatRupiah(this)">
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group full-width">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" 
                                      rows="4" 
                                      placeholder="Deskripsi produk..."><?= htmlspecialchars($product['deskripsi'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Manajemen Batch -->
            <div class="batch-management">
                <div class="batch-header">
                    <h2 class="section-title">
                        <i class="fas fa-boxes"></i> Manajemen Batch / Stok
                    </h2>
                    <span class="badge" style="background: #e8f4fc; color: #2980b9; padding: 8px 16px; border-radius: 20px; font-size: 14px;">
                        <i class="fas fa-cubes"></i> Total Stok: 
                        <?php 
                            $total_stock = 0;
                            $batches->data_seek(0);
                            while ($batch = $batches->fetch_assoc()) $total_stock += $batch['stock'];
                            $batches->data_seek(0);
                            echo $total_stock . ' ' . htmlspecialchars($product['satuan'] ?? 'unit');
                        ?>
                    </span>
                </div>

                <!-- Daftar Batch -->
                <?php if ($batches && $batches->num_rows > 0): ?>
                    <div class="batch-table-container">
                        <table class="batch-table">
                            <thead>
                                <tr>
                                    <th>No. Batch</th>
                                    <th>Tanggal Expired</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                    <th>Terakhir Update</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $batches->data_seek(0);
                                while ($batch = $batches->fetch_assoc()): 
                                    $is_expired = strtotime($batch['expired_date']) < strtotime(date('Y-m-d'));
                                    $expired_date_obj = new DateTime($batch['expired_date']);
                                    $today_obj = new DateTime();
                                    $diff = $today_obj->diff($expired_date_obj);

                                    // Hitung sisa waktu
                                    $is_near_expired = false;
                                    $sisa_waktu = '';
                                    if (!$is_expired) {
                                        $total_days = $diff->days;
                                        
                                        if ($total_days <= 90) { // 3 bulan = ~90 hari
                                            $is_near_expired = true;
                                            
                                            if ($diff->y > 0) {
                                                $sisa_waktu = $diff->y . ' tahun';
                                            } elseif ($diff->m > 0) {
                                                $sisa_waktu = $diff->m . ' bulan';
                                            } elseif ($diff->d > 0) {
                                                $sisa_waktu = $diff->d . ' hari';
                                            } else {
                                                $sisa_waktu = 'Hari ini';
                                            }
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($batch['batch_number']) ?></strong>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($batch['expired_date'])) ?>
                                            <?php if ($is_expired): ?>
                                                <span class="expired-warning">
                                                    <i class="fas fa-exclamation-triangle"></i> Expired
                                                </span>
                                            <?php elseif ($is_near_expired): ?>
                                                <span class="expired-near">
                                                    <i class="fas fa-clock"></i> <?= $sisa_waktu ?> lagi
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="stock-badge <?= $batch['stock'] > 0 ? 'stock-available' : 'stock-empty' ?>">
                                                <?= $batch['stock'] ?> <?= htmlspecialchars($product['satuan'] ?? 'unit') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($batch['stock'] == 0): ?>
                                                <span class="status-badge status-empty">Habis</span>
                                            <?php elseif ($batch['stock'] <= ($product['minimal_stok'] ?? 10)): ?>
                                                <span class="status-badge status-low">Menipis</span>
                                            <?php else: ?>
                                                <span class="status-badge status-available">Tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $batch['updated_at'] ? date('d/m/Y H:i', strtotime($batch['updated_at'])) : date('d/m/Y H:i', strtotime($batch['created_at'])) ?>
                                        </td>
                                        <td>
                                            <div class="batch-actions">
                                                <!-- Edit Batch -->
                                                <button class="btn-small btn-edit-batch" 
                                                        onclick="editBatch(<?= $batch['id'] ?>, 
                                                                         '<?= htmlspecialchars($batch['batch_number']) ?>', 
                                                                         '<?= $batch['expired_date'] ?>', 
                                                                         <?= $batch['stock'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <!-- Hapus Batch -->
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Hapus batch <?= htmlspecialchars($batch['batch_number']) ?>?\nData yang dihapus tidak dapat dikembalikan.')">
                                                    <input type="hidden" name="action" value="delete_batch">
                                                    <input type="hidden" name="batch_id" value="<?= $batch['id'] ?>">
                                                    <button type="submit" class="btn-small btn-delete-batch">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state-batch">
                        <i class="fas fa-boxes"></i>
                        <h3>Belum ada batch</h3>
                        <p>Tambahkan batch pertama untuk produk ini</p>
                    </div>
                <?php endif; ?>

                <!-- Form Tambah Batch -->
                <div class="add-batch-form">
                    <h3 class="add-batch-title">
                        <i class="fas fa-plus-circle"></i>
                        Tambah Batch Baru
                    </h3>
                    
                    <form method="POST" action="" class="batch-form-grid">
                        <input type="hidden" name="action" value="add_batch">
                        
                        <div class="form-group">
                            <label>No. Batch <span class="required">*</span></label>
                            <input type="text" 
                                   name="batch_number" 
                                   required 
                                   placeholder="Contoh: BATCH001">
                        </div>
                        
                        <div class="form-group">
                            <label>Tanggal Expired <span class="required">*</span></label>
                            <input type="date" 
                                   name="expired_date" 
                                   required 
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Stok Awal <span class="required">*</span></label>
                            <input type="number" 
                                   name="stock" 
                                   required 
                                   min="1" 
                                   placeholder="0">
                        </div>
                        
                        <div class="form-group" style="justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Tambah Batch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Batch -->
    <div id="editBatchModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-edit" style="color: #f39c12;"></i>
                    Edit Batch
                </h3>
                <button onclick="closeEditModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <form method="POST" action="" id="editBatchForm">
                    <input type="hidden" name="action" value="update_batch">
                    <input type="hidden" name="batch_id" id="edit_batch_id">
                    
                    <div class="form-group">
                        <label>No. Batch <span class="required">*</span></label>
                        <input type="text" 
                               name="batch_number" 
                               id="edit_batch_number" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label>Tanggal Expired <span class="required">*</span></label>
                        <input type="date" 
                               name="expired_date" 
                               id="edit_expired_date" 
                               required 
                               min="<?= date('Y-m-d') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Stok <span class="required">*</span></label>
                        <input type="number" 
                               name="stock" 
                               id="edit_stock" 
                               required 
                               min="0">
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="submit" form="editBatchForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
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

        // Edit Batch
        function editBatch(id, batchNumber, expiredDate, stock) {
            document.getElementById('edit_batch_id').value = id;
            document.getElementById('edit_batch_number').value = batchNumber;
            document.getElementById('edit_expired_date').value = expiredDate;
            document.getElementById('edit_stock').value = stock;
            document.getElementById('editBatchModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editBatchModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editBatchModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Auto-format harga saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const hargaInput = document.getElementById('harga');
            if (hargaInput.value) {
                formatRupiah(hargaInput);
            }
        });
    </script>

    <script src="system/admin/js/sidebar-toggle.js"></script>
</body>
</html>