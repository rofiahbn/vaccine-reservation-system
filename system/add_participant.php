<?php
session_start();
include "config.php";
include "system/calendar_helper.php";

// ================== AMBIL DATA SERVICES ==================
$services_data = [];

// 🔥 QUERY BARU - sesuai dengan database services yang baru
$sql = "SELECT 
            id, 
            nama_layanan, 
            tipe,
            kategori_usia, 
            harga,
            kode_paket,
            kode_layanan,
            deskripsi
        FROM services 
        WHERE tipe IN ('pelayanan', 'paket')
        ORDER BY 
            CASE tipe 
                WHEN 'pelayanan' THEN 1 
                WHEN 'paket' THEN 2 
            END,
            kategori_usia, 
            nama_layanan ASC";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $services_data[] = $row;
}

// Pisahkan berdasarkan tipe
$layanan_data = array_filter($services_data, function($item) {
    return $item['tipe'] === 'pelayanan';
});

$paket_data = array_filter($services_data, function($item) {
    return $item['tipe'] === 'paket';
});

// Set bulan dan tahun untuk kalender
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('n');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// CEK MODE EDIT
$is_edit_mode = isset($_SESSION['editing_mode']) && $_SESSION['editing_mode'] === true;
$editing_index = isset($_SESSION['editing_index']) ? intval($_SESSION['editing_index']) : -1;
$edit_data = [];

if ($is_edit_mode && $editing_index >= 0 && isset($_SESSION['participants'][$editing_index])) {
    $edit_data = $_SESSION['participants'][$editing_index];
}

// Nama bulan dalam bahasa Indonesia
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Hitung jumlah hari dalam bulan
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Hari pertama bulan (0=Minggu, 6=Sabtu)
$hari_awal = date('w', strtotime("$tahun-$bulan-01"));

// Hari ini
$hari_ini = ($bulan == date('n') && $tahun == date('Y')) ? date('j') : 0;

// Jika form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi data
    $errors = [];
    
    $first = $_SESSION['participants'][0];

    $service_type = $first['service_type'];
    $pelayanan = $first['pelayanan'];
    $tanggal_booking = $first['tanggal_booking'];
    $waktu_booking = $first['waktu_booking'];

    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $action = $_POST['action'] ?? ''; // 'add_more' atau 'finish'

    // ===== Batasi maksimal 5 peserta =====
    if (isset($_SESSION['participants']) && count($_SESSION['participants']) >= 5) {
        $errors[] = 'Maksimal 5 peserta dalam satu antrian';
    }

    if (empty($nama_lengkap)) $errors[] = 'Nama lengkap harus diisi';
    if (empty($tanggal_lahir)) $errors[] = 'Tanggal lahir harus diisi';
    if (empty($jenis_kelamin)) $errors[] = 'Jenis kelamin harus dipilih';
    
    // Validasi identitas sesuai layanan
    if ($pelayanan === 'Umroh/Haji/Luar Negeri') {
        if (empty($_POST['paspor'])) {
            $errors[] = 'Nomor Paspor harus diisi untuk layanan Umroh/Haji/Luar Negeri';
        }
    } else if ($pelayanan === 'Vaksinasi Umum/Infus Vitamin') {
        if (empty($_POST['nik'])) {
            $errors[] = 'NIK harus diisi untuk layanan Vaksinasi Umum/Infus Vitamin';
        } else if (strlen($_POST['nik']) !== 16) {
            $errors[] = 'NIK harus 16 digit';
        }
    }
    
    // Validasi kontak
    $emails = $_POST['emails'] ?? [];
    $phones = $_POST['phones'] ?? [];

    if (empty($emails[0])) $errors[] = 'Email harus diisi';
    if (empty($phones[0])) $errors[] = 'Nomor HP harus diisi';

    // Filter array (hapus yang kosong)
    $emails = array_filter($emails);
    $phones = array_filter($phones);
    
    // Validasi alamat
    if (empty($_POST['alamat'])) $errors[] = 'Alamat harus diisi';
    if (empty($_POST['provinsi'])) $errors[] = 'Provinsi harus dipilih';
    if (empty($_POST['kota'])) $errors[] = 'Kota harus dipilih';
    
    if (empty($errors)) {
        // Simpan data peserta ke session
        if (!isset($_SESSION['participants'])) {
            $_SESSION['participants'] = [];
        }
        
        // Hitung usia
        $birthDate = new DateTime($tanggal_lahir);
        $today = new DateTime();
        $usia = $today->diff($birthDate)->y;
        $kategori_usia = ($usia < 18) ? 'Anak' : 'Dewasa';
        
        $participant_data = [
            'service_type' => $service_type,
            'pelayanan' => $pelayanan,
            'nama_lengkap' => $nama_lengkap,
            'nama_panggilan' => $_POST['nama_panggilan'] ?? '',
            'tanggal_lahir' => $tanggal_lahir,
            'usia' => $usia,
            'kategori_usia' => $kategori_usia,
            'jenis_kelamin' => $jenis_kelamin,
            'nik' => $_POST['nik'] ?? '',
            'paspor' => $_POST['paspor'] ?? '',
            'kebangsaan' => $_POST['kebangsaan'] ?? 'Indonesia',
            'pekerjaan' => $_POST['pekerjaan'] ?? '',
            'nama_wali' => $_POST['nama_wali'] ?? '',
            'emails' => $emails,
            'phones' => $phones,
            'alamat' => $_POST['alamat'],
            'provinsi' => $_POST['provinsi'],
            'kota' => $_POST['kota'],
            'riwayat_alergi' => $_POST['riwayat_alergi'] ?? '',
            'riwayat_penyakit' => $_POST['riwayat_penyakit'] ?? '',
            'riwayat_obat' => $_POST['riwayat_obat'] ?? '',
            'tanggal_booking' => $tanggal_booking,
            'waktu_booking' => $waktu_booking
        ];

        // Simpan selected products ke participant data
        if (isset($_POST['selected_products']) && !empty($_POST['selected_products'])) {
            $selected_products = json_decode($_POST['selected_products'], true);
            $participant_data['selected_products'] = $selected_products;
        } else {
            $participant_data['selected_products'] = [];
        }
        
        // Cek apakah mode edit atau tambah baru
        if ($is_edit_mode && $editing_index >= 0) {
            // MODE EDIT: Update data peserta yang sudah ada
            $_SESSION['participants'][$editing_index] = $participant_data;
            
            // Clear editing mode
            unset($_SESSION['editing_mode']);
            unset($_SESSION['editing_index']);
            
            // Set success message
            $_SESSION['success_message'] = 'Data peserta berhasil diupdate!';
            
            // Redirect langsung ke konfirmasi (tidak peduli tombol apa yang diklik)
            header('Location: booking_confirmation');
            exit;
        } else {
            // MODE TAMBAH: Tambah peserta baru
            $_SESSION['participants'][] = $participant_data;
        }

        // Cek action button mana yang diklik
        if ($action === 'add_more') {
            // Redirect ke add_participant lagi (form baru)
            $_SESSION['success_message'] = 'Peserta berhasil ditambahkan! Silakan tambah peserta lagi.';
            header('Location: add_participant');
            exit;
        } else if ($action === 'finish') {
            // Redirect ke halaman konfirmasi
            header('Location: booking_confirmation');
            exit;
        }
    }
}

// Jika ada error dari validasi sebelumnya
$error_message = '';
if (isset($errors) && count($errors) > 0) {
    $error_message = implode('<br>', $errors);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Peserta - Vaksinin</title>
    <link rel="stylesheet" href="system/style.css">
    <link rel="stylesheet" href="system/layout.css">
    <link rel="stylesheet" href="system/calender.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #2563eb;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .back-button:hover {
            text-decoration: underline;
        }
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            padding: 15px;
            border-radius: 8px;
            color: #c33;
            margin-bottom: 20px;
        }
        .info-banner {
            background: #e0f2fe;
            border-left: 4px solid #0284c7;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-banner i {
            color: #0284c7;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include "content/header.php"; ?> 

    <div class="container">
        <a href="order" class="back-button">
            <i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama
        </a>

        <div class="info-banner">
            <i class="fas fa-info-circle"></i>
            <strong>Informasi:</strong> Isi data peserta dan pilih jadwal untuk peserta ini. Setelah selesai, Anda bisa menambah peserta lain atau selesai.
        </div>

        <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <strong><i class="fas fa-exclamation-triangle"></i> Terjadi Kesalahan:</strong><br>
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <!-- Search Section -->
        <div class="search-section">
            <h2>Cari dan Temukan Datamu</h2>
            <p>Cukup masukkan nama dan NIK Anda. Jika sudah pernah mendaftar, sistem akan menemukan data Anda secara otomatis agar proses lebih cepat dan praktis</p>
            
            <div class="search-simple">
                <input type="text" id="searchName" class="search-input-main" placeholder="Nama">
                <input type="text" id="searchNIK" class="search-input-main" placeholder="NIK">
                <button type="button" class="btn-search-main" onclick="searchPatient()">Cari</button>
            </div>
            <div id="searchResults" style="display:none;"></div>
        </div>

        <?php if ($is_edit_mode): ?>
            <h1><i class="fas fa-edit"></i> Edit Data Peserta</h1>
            <p class="subtitle">Perbarui data peserta yang sudah ada</p>
            
            <div class="info-banner">
                <i class="fas fa-info-circle"></i>
                <strong>Mode Edit:</strong> Anda sedang mengedit data peserta. Klik "Simpan Perubahan" untuk menyimpan.
            </div>
        <?php else: ?>
            <h1>Formulir Data Peserta</h1>
            <p class="subtitle">Isi dan lengkapi data peserta tambahan</p>
        <?php endif; ?>

        <?php if(isset($_SESSION['participants'][0])): 
            $first = $_SESSION['participants'][0];
        ?>

        <?php 
        $tgl = new DateTime($first['tanggal_booking']);
        ?>

        <div class="booking-info-banner">

            <div class="booking-info-title">
                Antrian yang dipilih
            </div>

            <div class="booking-info-item">
                <i class="fas fa-calendar-day"></i>
                <span><?= $tgl->format('d F Y'); ?></span>
            </div>

            <div class="booking-info-item">
                <i class="fas fa-clock"></i>
                <span><?= $first['waktu_booking']; ?> WIB</span>
            </div>

            <div class="booking-info-item">
                <i class="fas fa-stethoscope"></i>
                <span><?= $first['service_type']; ?></span>
            </div>

        </div>

        <?php endif; ?>

        <form id="addParticipantForm" method="POST" action="">
            <!-- HIDDEN INPUT CONSISTENT WITH ORDER PAGE -->
            <input type="hidden" name="nama_panggilan" value="<?= htmlspecialchars($edit_data['nama_panggilan'] ?? '') ?>">
            <input type="hidden" name="kebangsaan" value="<?= htmlspecialchars($edit_data['kebangsaan'] ?? 'Indonesia') ?>">
            <input type="hidden" name="pekerjaan" value="<?= htmlspecialchars($edit_data['pekerjaan'] ?? '') ?>">
            
            <!-- PILIH LAYANAN DULU -->
            <div class="form-section">
                <div class="form-group">
                    <label>Pilih Layanan <span class="required">*</span></label>
                    <select name="pelayanan" id="pelayananSelect" required onchange="updateFormByService()">
                        <option value="">-- Pilih Layanan --</option>
                        <option value="Umroh/Haji/Luar Negeri">Layanan Umroh/Haji/Luar Negeri</option>
                        <option value="Vaksinasi Umum/Infus Vitamin">Layanan Vaksinasi Umum/Infus Vitamin</option>
                    </select>
                </div>
            </div>

            <!-- DATA DIRI -->
            <div class="form-section">
                <div class="form-group">
                    <label id="labelNama">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="nama_lengkap" id="namaLengkap" required placeholder="Masukkan nama lengkap" value="<?php echo htmlspecialchars($edit_data['nama_lengkap'] ?? ''); ?>">
                </div>

                <div class="row">
                    <!--
                    <div class="form-group">
                        <label>Nama Panggilan</label>
                        <input type="text" name="nama_panggilan" placeholder="Nama Panggilan" value="<?php echo htmlspecialchars($edit_data['nama_panggilan'] ?? ''); ?>">
                    </div>
                    -->

                    <div class="form-group">
                        <label>Tanggal Lahir <span class="required">*</span></label>
                        <input type="date" name="tanggal_lahir" id="tanggalLahir" required onchange="hitungUsia()" value="<?php echo htmlspecialchars($edit_data['tanggal_lahir'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Jenis Kelamin <span class="required">*</span></label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="jenis_kelamin" value="L" required <?php echo (isset($edit_data['jenis_kelamin']) && $edit_data['jenis_kelamin'] === 'L') ? 'checked' : ''; ?>> Laki-laki 
                            </label>
                            <label>
                                <input type="radio" name="jenis_kelamin" value="P" required <?php echo (isset($edit_data['jenis_kelamin']) && $edit_data['jenis_kelamin'] === 'P') ? 'checked' : ''; ?>> Perempuan 
                            </label>
                        </div>
                    </div>
                </div>

                    <div class="info-box" id="usiaInfo" style="display:none;">
                        Usia: <strong id="usiaText">-</strong> tahun (<span id="kategoriText">-</span>)
                    </div>

                <!-- IDENTITAS DINAMIS -->
                <div class="row">
                    <div class="form-group" id="fieldNIK">
                        <label>NIK <span class="required" id="nikRequired">*</span></label>
                        <input type="text" name="nik" id="inputNIK" placeholder="16 digit NIK" maxlength="16" value="<?php echo htmlspecialchars($edit_data['nik'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group" id="fieldPaspor" style="display:none;">
                        <label>No. Paspor <span class="required" id="pasporRequired">*</span></label>
                        <input type="text" name="paspor" id="inputPaspor" placeholder="Nomor Paspor" value="<?php echo htmlspecialchars($edit_data['paspor'] ?? ''); ?>">
                    </div>
                </div>

                <!--
                <div class="row">
                    <div class="form-group">
                        <label>Kebangsaan</label>
                        <input type="text" name="kebangsaan" placeholder="Kebangsaan" value="<?php echo htmlspecialchars($edit_data['kebangsaan'] ?? 'Indonesia'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Pekerjaan</label>
                        <input type="text" name="pekerjaan" placeholder="Pekerjaan saat ini" value="<?php echo htmlspecialchars($edit_data['pekerjaan'] ?? ''); ?>">
                    </div>
                </div>
                -->

                <div class="form-group" id="fieldNamaWali" style="display:none;">
                    <label>Nama Wali <span class="required">*</span></label>
                    <input type="text" name="nama_wali" id="inputNamaWali" placeholder="Nama orang tua/wali" value="<?php echo htmlspecialchars($edit_data['nama_wali'] ?? ''); ?>">
                </div>
            </div>

            <!-- KONTAK -->
            <div class="form-section">
                <label>
                    <input type="checkbox" id="copyContact">
                    Gunakan data kontak peserta pertama
                </label>
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="emails[]" required placeholder="contoh@email.com" value="<?php echo htmlspecialchars($edit_data['emails'][0] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Nomor HP <span class="required">*</span></label>
                    <input type="tel" name="phones[]" required placeholder="08123456789" value="<?php echo htmlspecialchars($edit_data['phones'][0] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Alamat Lengkap <span class="required">*</span></label>
                    <div id="addressContainer">
                        <div class="dynamic-field">
                            <textarea name="alamat" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label>Provinsi <span class="required">*</span></label>
                        <select name="provinsi" id="provinsiSelect" required onchange="loadKota()">
                            <option value="">-- Pilih Provinsi --</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Kota/Kabupaten <span class="required">*</span></label>
                        <select name="kota" id="kotaSelect" required>
                            <option value="">-- Pilih Kota --</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- RIWAYAT KESEHATAN -->
            <div class="form-section">
                <h2 class="section-title">Riwayat Kesehatan</h2>
                
                <div class="form-group">
                    <label>Riwayat Alergi</label>
                    <textarea name="riwayat_alergi" placeholder="Contoh: Alergi obat penisilin, alergi makanan laut, dll. Kosongkan jika tidak ada."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Riwayat Penyakit Dahulu</label>
                    <textarea name="riwayat_penyakit" placeholder="Contoh: Diabetes, hipertensi, asma, dll. Kosongkan jika tidak ada."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Riwayat Pemakaian Obat</label>
                    <textarea name="riwayat_obat" placeholder="Obat yang sedang dikonsumsi rutin. Kosongkan jika tidak ada."></textarea>
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-secondary" onclick="alert('Fitur cek riwayat vaksinasi')">
                        <i class="fas fa-syringe"></i> Cek Riwayat Vaksinasi
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="alert('Fitur cek rekam medis')">
                        <i class="fas fa-file-medical"></i> Cek Rekam Medis
                    </button>
                </div>
            </div>

            <!-- PILIH PRODUK/LAYANAN -->
            <div class="form-section">
                <h2 class="section-title">Pilih Layanan Tambahan</h2>
                <p class="subtitle">Pilih opsi layanan yang ingin Anda pesan</p>
                
                <!-- Selected Products Badge -->
                <div class="selected-badges" id="selectedBadges" style="display:none;">
                    <!-- Badge akan muncul di sini -->
                </div>
                
                <!-- Search Box -->
                <div class="form-group">
                    <input type="text" class="search-box-layanan" id="searchLayanan" placeholder="🔍 Ketik nama layanan...">
                </div>

                <!-- Tabs Container -->
                <div id="productTabsContainer"></div>
                
                <!-- Category Accordion -->
                <div class="category-accordion" id="categoryAccordion">
                    <!-- Categories akan di-load via JavaScript -->
                </div>
                
                <!-- Hidden input untuk submit -->
                <input type="hidden" name="selected_products" id="selectedProductsInput">
                
                <!-- Info total -->
                <div class="total-info" id="totalInfo" style="display:none;">
                    Total dipilih: <strong id="totalCount">0</strong> layanan
                </div>
            </div>

            <!-- BUTTONS -->
            <div class="form-actions">
                <?php if ($is_edit_mode): ?>
                    <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    
                    <button type="submit" name="action" value="finish" class="btn btn-primary" id="btnFinish" disabled>
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='booking_confirmation'">
                        <i class="fas fa-arrow-left"></i> Kembali ke Konfirmasi
                    </button>
                    
                    <button 
                        type="submit" 
                        name="action" 
                        value="add_more"
                        <?= (isset($_SESSION['participants']) && count($_SESSION['participants']) >= 5) ? 'disabled' : '' ?>
                        class="btn btn-secondary" 
                        id="btnAddMore">
                        <i class="fas fa-user-plus"></i> Tambah Peserta Lagi
                    </button>
                    
                    <button type="submit" name="action" value="finish" class="btn btn-primary" id="btnFinish">
                        <i class="fas fa-check"></i> Selesai
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
 

    <script>
        const bulanNow = <?php echo $bulan; ?>;
        const tahunNow = <?php echo $tahun; ?>;
        const namaBulanNow = '<?php echo $nama_bulan[$bulan]; ?>';
    </script>
    <script>
    const editSelectedProducts = <?php 
        echo json_encode($edit_data['selected_products'] ?? []); 
    ?>;
    </script>
    <script>
    // Data mentah dari database
    const rawServices = <?= json_encode($services_data) ?>;
    const rawLayanan = <?= json_encode(array_values($layanan_data)) ?>;
    const rawPaket = <?= json_encode(array_values($paket_data)) ?>;

    console.log('Raw Services:', rawServices);
    console.log('Layanan:', rawLayanan);
    console.log('Paket:', rawPaket);

    // Susun per kategori untuk Layanan
    const productDataLayanan = {};
    rawLayanan.forEach(item => {
        const kategori = item.kategori_usia || 'Layanan Lainnya';
        if (!productDataLayanan[kategori]) {
            productDataLayanan[kategori] = [];
        }
        productDataLayanan[kategori].push({
            id: item.id,
            name: item.nama_layanan,
            price: item.harga,
            kode_layanan: item.kode_layanan,
            tipe: 'pelayanan'
        });
    });

    // Susun per kategori untuk Paket
    const productDataPaket = {};
    rawPaket.forEach(item => {
        const kategori = item.kategori_usia || 'Paket Lainnya';
        if (!productDataPaket[kategori]) {
            productDataPaket[kategori] = [];
        }
        productDataPaket[kategori].push({
            id: item.id,
            name: item.nama_layanan,
            price: item.harga,
            kode_paket: item.kode_paket,
            tipe: 'paket'
        });
    });

    // Gabungkan untuk keperluan global (backward compatibility)
    const productData = {...productDataLayanan, ...productDataPaket};
    </script>
    <script src="system/provinces.js"></script>
    <script src="system/script.js"></script>
    <script src="system/service.js"></script>
    <script>
        
        // Load provinsi saat halaman load
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof loadProvinsi === 'function') {
                loadProvinsi();
            }
        });

        // Cancel edit mode
        function cancelEdit() {
            if (confirm('Batalkan edit? Perubahan tidak akan disimpan.')) {
                window.location.href = 'cancel_edit.php';
            }
        }

    document.getElementById('copyContact')?.addEventListener('change', function() {

        if (this.checked) {

            fetch('get_first_participant.php')
            .then(res => res.json())
            .then(data => {

                document.querySelector('input[name="emails[]"]').value = data.email;
                document.querySelector('input[name="phones[]"]').value = data.phone;
                document.querySelector('textarea[name="alamat"]').value = data.alamat;

                document.getElementById('provinsiSelect').value = data.provinsi;
                loadKota(data.provinsi, data.kota);

            });
        }
    });
    </script>
	
<?php include "content/footer.php"; ?>
</body>
</html>