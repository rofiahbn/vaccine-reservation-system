<?php
session_start();
include "../config.php";

date_default_timezone_set('Asia/Jakarta');

// ================= CEK TIPE =================
$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'pelayanan';
if (!in_array($tipe, ['pelayanan', 'paket'])) {
    $tipe = 'pelayanan';
}

// ================= AMBIL DATA UNTUK DROPDOWN =================
// Ambil daftar layanan (untuk pilihan item paket) - dari tabel services tipe pelayanan
$sql_services = "SELECT id, nama_layanan, harga FROM services WHERE tipe = 'pelayanan' ORDER BY nama_layanan ASC";
$services_result = $conn->query($sql_services);

// Ambil daftar produk (vaksin/obat) dari tabel products
$sql_products = "SELECT id, nama_produk, harga, satuan FROM products ORDER BY nama_produk ASC";
$products_result = $conn->query($sql_products);

// Ambil daftar jasa dari tabel services tipe jasa
$sql_jasa = "SELECT id, nama_layanan, harga FROM services WHERE tipe = 'jasa' ORDER BY nama_layanan ASC";
$jasa_result = $conn->query($sql_jasa);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tipe == 'pelayanan' ? 'Tambah Layanan' : 'Tambah Paket' ?> - Vaksinin</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar-toggle.css">
    <link rel="stylesheet" href="css/add-pelayanan.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .component-row, .package-row {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .component-select, .package-select {
            flex: 2;
            min-width: 250px;
        }
        
        .component-qty, .package-qty {
            flex: 1;
            min-width: 100px;
        }
        
        .component-visit {
            flex: 1;
            min-width: 120px;
        }
        
        .component-price, .package-price {
            flex: 1;
            min-width: 120px;
            font-weight: 600;
            color: #27ae60;
        }
        
        .btn-remove {
            background: #e74c3c;
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-remove:hover {
            background: #c0392b;
        }
        
        .component-badge {
            background: #3498db;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-left: 8px;
        }
        
        .jasa-badge {
            background: #f39c12;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin-left: 8px;
        }
        
        .info-box {
            background: #e8f4fc;
            border: 1px solid #b8e0f5;
            border-radius: 8px;
            padding: 12px;
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #2c3e50;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo">
        <img src="vaksinin-logo.png" class="logo-full">
        <img src="v-logo.png" class="logo-icon">
    </div>
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-th-large"></i><span>Dashboard</span>
        </a>
        <a href="javascript:void(0)" class="nav-item has-submenu active open" onclick="toggleSubmenu(this)">
            <i class="fas fa-capsules"></i><span>Produk</span>
            <i class="fas fa-chevron-down arrow"></i>
        </a>
        <ul class="submenu open">
            <li><a href="products.php">Vaksin & Obat</a></li>
            <li><a href="products_jasa.php">Jasa</a></li>
            <li><a href="products_pelayanan.php" class="active">Pelayanan/Paket</a></li>
        </ul>
        <a href="patients.php" class="nav-item">
            <i class="fas fa-users"></i><span>Pasien</span>
        </a>
        <a href="calendar_setting.php" class="nav-item">
            <i class="fas fa-calendar"></i><span>Kalender</span>
        </a>
    </nav>
</div>

<!-- Main Content -->
<div class="main-content">

    <!-- Header -->
    <div class="page-header">
        <a href="products_pelayanan.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <h1><?= $tipe == 'pelayanan' ? 'Tambah Layanan' : 'Tambah Paket' ?></h1>
    </div>

    <!-- Info Note -->
    <div class="info-note info-note-<?= $tipe ?>">
        <i class="fas fa-info-circle"></i>
        <?php if ($tipe == 'pelayanan'): ?>
            <span><strong>Layanan</strong> = 1x kunjungan (vaksin/obat + jasa) dalam 1 harga.</span>
        <?php else: ?>
            <span><strong>Paket</strong> = Bundling beberapa layanan untuk beberapa kali kunjungan.</span>
        <?php endif; ?>
    </div>

    <!-- Form -->
    <form id="formTambah" action="proses_simpan_pelayanan.php" method="POST">
        <input type="hidden" name="tipe" value="<?= $tipe ?>">
        
        <!-- Informasi Dasar -->
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-info-circle"></i>
                <h3>Informasi Dasar</h3>
            </div>
            <div class="form-card-body">
                <!-- Nama Layanan/Paket -->
                <div class="form-group">
                    <label>Nama <?= $tipe == 'pelayanan' ? 'Layanan' : 'Paket' ?> <span class="required">*</span></label>
                    <input type="text" 
                           name="nama_layanan" 
                           class="form-control"
                           placeholder="Contoh: <?= $tipe == 'pelayanan' ? 'Vaksinasi Influenza' : 'Paket Vaksinasi HPV 3 Dosis' ?>"
                           required>
                </div>
                
                <!-- Kategori Usia -->
                <div class="form-group">
                    <label>Kategori Usia <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="kategori_usia" value="Anak" required>
                            <span>Anak (0-18 tahun)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="kategori_usia" value="Dewasa">
                            <span>Dewasa (>18 tahun)</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="kategori_usia" value="Semua Usia">
                            <span>Semua Usia</span>
                        </label>
                    </div>
                </div>
                
                <!-- Harga -->
                <div class="form-group">
                    <label>Harga <?= $tipe == 'pelayanan' ? 'Layanan' : 'Paket' ?> <span class="required">*</span></label>
                    <div class="price-input">
                        <span class="price-prefix">Rp</span>
                        <input type="number" 
                               name="harga" 
                               id="harga_paket"
                               class="form-control"
                               placeholder="0"
                               min="0"
                               step="1000"
                               required
                               onchange="hitungHargaPaket()">
                    </div>
                </div>
                
                <!-- Kode Paket (khusus paket) -->
                <?php if ($tipe == 'paket'): ?>
                <div class="form-group">
                    <label>Kode Paket (Opsional)</label>
                    <input type="text" 
                           name="kode_paket" 
                           class="form-control"
                           placeholder="Contoh: HPV-3X, FLU-2X">
                </div>
                <?php endif; ?>
                
                <!-- Deskripsi -->
                <div class="form-group">
                    <label>Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" 
                              class="form-control"
                              rows="3"
                              placeholder="<?= $tipe == 'pelayanan' ? 'Contoh: Vaksin influenza untuk perlindungan musiman' : 'Contoh: Paket 3 dosis vaksin HPV dengan harga hemat' ?>"></textarea>
                </div>
            </div>
        </div>
        
        <!-- Komposisi Layanan (khusus layanan) -->
        <?php if ($tipe == 'pelayanan'): ?>
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-puzzle-piece"></i>
                <h3>Komposisi Layanan</h3>
            </div>
            <div class="form-card-body">
                <p class="form-description">
                    Pilih produk (vaksin/obat) dan jasa yang digunakan dalam layanan ini
                </p>
                
                <div id="components-container" class="components-container"></div>
                
                <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                    <button type="button" class="btn-add" id="btnAddProduk">
                        <i class="fas fa-plus"></i> Tambah Produk
                    </button>
                    <button type="button" class="btn-add" id="btnAddJasa" style="background: #f39c12;">
                        <i class="fas fa-plus"></i> Tambah Jasa
                    </button>
                </div>
                
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <span>Anda dapat menambahkan produk (vaksin/obat) dan jasa medis ke dalam layanan ini.</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Isi Paket (khusus paket) -->
        <?php if ($tipe == 'paket'): ?>
        <div class="form-card">
            <div class="form-card-header">
                <i class="fas fa-boxes"></i>
                <h3>Isi Paket</h3>
            </div>
            <div class="form-card-body">
                <p class="form-description">
                    Pilih layanan yang termasuk dalam paket ini
                </p>
                
                <div id="package-items-container" class="package-items-container"></div>
                
                <button type="button" class="btn-add" id="btnAddPackageItem">
                    <i class="fas fa-plus"></i> Tambah Item Paket
                </button>
                
                <div class="price-summary">
                    <div class="summary-row">
                        <span>Total Harga Normal:</span>
                        <span class="summary-value" id="totalNormalPrice">Rp 0</span>
                    </div>
                    <div class="summary-row">
                        <span>Harga Paket:</span>
                        <span class="summary-value highlight" id="hargaPaketDisplay">Rp 0</span>
                    </div>
                    <div class="summary-row" id="hematDisplay" style="color: #27ae60; display: none;">
                        <span>Hemat:</span>
                        <span class="summary-value" id="hematValue">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Action Buttons -->
        <div class="form-actions">
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="products_pelayanan.php" class="btn-cancel">
                Batal
            </a>
        </div>
    </form>

</div>

<!-- Data untuk JavaScript -->
<script id="product-options-data" type="application/json">
<?php
$product_options = [];
if ($products_result && $products_result->num_rows > 0) {
    $products_result->data_seek(0);
    while($prod = $products_result->fetch_assoc()) {
        $product_options[] = [
            'id' => $prod['id'],
            'name' => $prod['nama_produk'],
            'price' => $prod['harga'],
            'satuan' => $prod['satuan'] ?? 'unit'
        ];
    }
}
echo json_encode($product_options);
?>
</script>

<script id="jasa-options-data" type="application/json">
<?php
$jasa_options = [];
if ($jasa_result && $jasa_result->num_rows > 0) {
    $jasa_result->data_seek(0);
    while($jasa = $jasa_result->fetch_assoc()) {
        $jasa_options[] = [
            'id' => $jasa['id'],
            'name' => $jasa['nama_layanan'],
            'price' => $jasa['harga']
        ];
    }
}
echo json_encode($jasa_options);
?>
</script>

<script id="service-options-data" type="application/json">
<?php
$service_options = [];
if ($services_result && $services_result->num_rows > 0) {
    $services_result->data_seek(0);
    while($service = $services_result->fetch_assoc()) {
        $service_options[] = [
            'id' => $service['id'],
            'name' => $service['nama_layanan'],
            'price' => $service['harga']
        ];
    }
}
echo json_encode($service_options);
?>
</script>

<script>
    const CURRENT_TIPE = '<?= $tipe ?>';
    let componentCounter = 0;
    let packageCounter = 0;
</script>

<!-- JavaScript -->
<script src="js/sidebar-toggle.js"></script>
<script>
    // Load data dari JSON
    const productOptions = JSON.parse(document.getElementById('product-options-data').textContent || '[]');
    const jasaOptions = JSON.parse(document.getElementById('jasa-options-data').textContent || '[]');
    const serviceOptions = JSON.parse(document.getElementById('service-options-data').textContent || '[]');

    // Fungsi untuk LAYANAN - Tambah Produk
    document.getElementById('btnAddProduk')?.addEventListener('click', function() {
        addComponentRow('produk');
    });

    // Fungsi untuk LAYANAN - Tambah Jasa
    document.getElementById('btnAddJasa')?.addEventListener('click', function() {
        addComponentRow('jasa');
    });

    // Fungsi untuk PAKET - Tambah Item
    document.getElementById('btnAddPackageItem')?.addEventListener('click', function() {
        addPackageRow();
    });

    // Tambah baris komponen (untuk layanan)
    function addComponentRow(tipe) {
        const container = document.getElementById('components-container');
        if (!container) return;
        
        const rowId = 'comp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
        const options = tipe === 'jasa' ? jasaOptions : productOptions;
        const badgeText = tipe === 'jasa' ? 'JASA' : 'PRODUK';
        const badgeColor = tipe === 'jasa' ? '#f39c12' : '#3498db';
        
        let optionsHtml = '<option value="">-- Pilih --</option>';
        options.forEach(item => {
            const priceFormatted = new Intl.NumberFormat('id-ID').format(item.price || 0);
            const satuanText = item.satuan ? ` (${item.satuan})` : '';
            optionsHtml += `<option value="${item.id}" data-price="${item.price || 0}">${item.name}${satuanText} - Rp ${priceFormatted}</option>`;
        });
        
        const rowHtml = `
            <div class="component-row" id="${rowId}" data-tipe="${tipe}">
                <div class="component-select">
                    <select name="components[${componentCounter}][id]" class="form-control component-select-input" required onchange="updateComponentPrice(this, '${rowId}')">
                        ${optionsHtml}
                    </select>
                    <span class="${tipe === 'jasa' ? 'jasa-badge' : 'component-badge'}">${badgeText}</span>
                </div>
                <div class="component-qty">
                    <input type="number" name="components[${componentCounter}][qty]" class="form-control" value="1" min="1" required onchange="updateComponentPrice(this, '${rowId}')">
                </div>
                <div class="component-price" id="${rowId}_price">Rp 0</div>
                <button type="button" class="btn-remove" onclick="removeRow('${rowId}')">
                    <i class="fas fa-trash"></i>
                </button>
                <input type="hidden" name="components[${componentCounter}][tipe]" value="${tipe}">
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', rowHtml);
        componentCounter++;
    }

    // Tambah baris item paket
    function addPackageRow() {
        const container = document.getElementById('package-items-container');
        if (!container) return;
        
        const rowId = 'pkg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
        
        let optionsHtml = '<option value="">-- Pilih Layanan --</option>';
        serviceOptions.forEach(item => {
            const priceFormatted = new Intl.NumberFormat('id-ID').format(item.price || 0);
            optionsHtml += `<option value="${item.id}" data-price="${item.price || 0}">${item.name} - Rp ${priceFormatted}</option>`;
        });
        
        const rowHtml = `
            <div class="package-row" id="${rowId}">
                <div class="package-select">
                    <select name="package_items[${packageCounter}][id]" class="form-control" required onchange="updatePackagePrice(this, '${rowId}')">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="package-visit">
                    <input type="text" 
                        name="package_items[${packageCounter}][visit_order]" 
                        class="form-control" 
                        placeholder="Kunjungan Ke -"
                        pattern="[0-9]*"
                        inputmode="numeric"
                        required
                        title="Masukkan angka untuk urutan kunjungan"
                        onchange="validateNumber(this)">
                </div>
                <div class="package-qty">
                    <input type="text" 
                        name="package_items[${packageCounter}][qty]" 
                        class="form-control" 
                        placeholder="Jumlah"
                        pattern="[0-9]*"
                        inputmode="numeric"
                        required 
                        title="Masukkan jumlah"
                        onchange="validateNumber(this); updatePackagePrice(this, '${rowId}')">
                </div>
                <div class="package-price" id="${rowId}_price">Rp 0</div>
                <button type="button" class="btn-remove" onclick="removeRow('${rowId}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', rowHtml);
        packageCounter++;
        hitungTotalPaket();
    }

    // Fungsi untuk memastikan input hanya angka
    function validateNumber(input) {
        input.value = input.value.replace(/[^0-9]/g, '');
        if (input.value === '' || parseInt(input.value) < 1) {
            input.value = '1';
        }
    }

    // Update harga komponen
    function updateComponentPrice(element, rowId) {
        const row = document.getElementById(rowId);
        if (!row) return;
        
        const select = row.querySelector('.component-select-input');
        const qtyInput = row.querySelector('input[name*="[qty]"]');
        const priceDisplay = document.getElementById(rowId + '_price');
        
        if (!select || !qtyInput || !priceDisplay) return;
        
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption ? parseInt(selectedOption.dataset.price || 0) : 0;
        const qty = parseInt(qtyInput.value) || 1;
        const total = price * qty;
        
        priceDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }

    // Update harga item paket
    function updatePackagePrice(element, rowId) {
        const row = document.getElementById(rowId);
        if (!row) return;
        
        const select = row.querySelector('select[name*="[id]"]');
        const qtyInput = row.querySelector('input[name*="[qty]"]');
        const priceDisplay = document.getElementById(rowId + '_price');
        
        if (!select || !qtyInput || !priceDisplay) return;
        
        const selectedOption = select.options[select.selectedIndex];
        const price = selectedOption ? parseInt(selectedOption.dataset.price || 0) : 0;
        const qty = parseInt(qtyInput.value) || 1;
        const total = price * qty;
        
        priceDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        hitungTotalPaket();
    }

    // Hitung total paket
    function hitungTotalPaket() {
        const hargaPaket = parseInt(document.getElementById('harga_paket')?.value) || 0;
        let totalNormal = 0;
        
        document.querySelectorAll('.package-row').forEach(row => {
            const priceElement = row.querySelector('.package-price');
            if (priceElement) {
                const priceText = priceElement.textContent.replace(/[^0-9]/g, '');
                totalNormal += parseInt(priceText) || 0;
            }
        });
        
        document.getElementById('totalNormalPrice').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalNormal);
        document.getElementById('hargaPaketDisplay').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(hargaPaket);
        
        const hemat = totalNormal - hargaPaket;
        const hematDisplay = document.getElementById('hematDisplay');
        if (hemat > 0) {
            document.getElementById('hematValue').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(hemat);
            hematDisplay.style.display = 'flex';
        } else {
            hematDisplay.style.display = 'none';
        }
    }

    // Remove row
    function removeRow(rowId) {
        document.getElementById(rowId)?.remove();
        if (CURRENT_TIPE === 'paket') {
            hitungTotalPaket();
        }
    }

    // Event listener untuk harga paket
    document.getElementById('harga_paket')?.addEventListener('input', hitungTotalPaket);
</script>

</body>
</html>