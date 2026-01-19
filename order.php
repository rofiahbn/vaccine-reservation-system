<?php
session_start();
include "config.php";
include "calendar_helper.php";

if (!isset($_SESSION['booking_active'])) {
    $_SESSION['participants'] = [];
    $_SESSION['booking_active'] = true;
}

// Ambil data peserta dari session (untuk multi participant)
$participants = isset($_SESSION['participants']) ? $_SESSION['participants'] : [];
$participant_count = count($participants);

// Set bulan dan tahun untuk kalender
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('n');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pasien Vaksinasi</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="layout.css">
    <link rel="stylesheet" href="calender.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-logo">
            <img src="vaksinin-logo.png" alt="Vaksinin">
        </div>
        <ul class="nav-menu">
            <li><a href="order.php">Home</a></li>
            <li><a href="#">Layanan</a></li>
            <li><a href="#">Jadwal Vaksin</a></li>
            <li><a href="#">Dokter</a></li>
            <li><a href="#">Profil</a></li>
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Kontak</a></li>
        </ul>
    </nav>

    <header class="main-header">
        <div class="hero">
            <div class="hero-content">
                <span class="hero-badge">Pendaftaran online resmi melalui Vaksinin.id</span>
                <h1>Lindungi Diri dan<br>Keluarga dengan Vaksinasi</h1>
            </div>
        </div>
    </header>

    <div class="container">
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

        <h1>Formulir Pendaftaran Pasien</h1>
        <p class="subtitle">Isi dan lengkapi data dibawah ini untuk melanjutkan proses pendaftaran</p>

        <form id="registrationForm" method="POST" action="save_booking.php">

            <!-- PILIH TIPE LAYANAN -->
            <div class="form-section">
                <div class="form-group">
                    <label>Tipe Layanan <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="service_type" value="In Clinic" checked required>
                            <div class="radio-card-content">
                                <i class="fas fa-hospital"></i>
                                <strong>In Clinic</strong>
                                <small>Kunjungi klinik kami</small>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="service_type" value="Home Service" required>
                            <div class="radio-card-content">
                                <i class="fas fa-home"></i>
                                <strong>Home Service</strong>
                                <small>Layanan ke rumah Anda</small>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
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

                <input type="hidden" name="is_umroh" id="isUmroh" value="0">
            </div>

            <!-- DATA DIRI -->
            <div class="form-section">
                <div class="form-group">
                    <label id="labelNama">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="nama_lengkap" id="namaLengkap" required placeholder="Masukkan nama lengkap">
                </div>

                <div class="row">
                    <div class="form-group">
                        <label>Nama Panggilan</label>
                        <input type="text" name="nama_panggilan" placeholder="Nama Panggilan">
                    </div>

                    <div class="form-group">
                        <label>Tanggal Lahir <span class="required">*</span></label>
                        <input type="date" name="tanggal_lahir" id="tanggalLahir" required onchange="hitungUsia()">
                    </div>
                </div>

                <div class="info-box" id="usiaInfo" style="display:none;">
                    Usia: <strong id="usiaText">-</strong> tahun (<span id="kategoriText">-</span>)
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label>Jenis Kelamin <span class="required">*</span></label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="jenis_kelamin" value="L" required> Laki-laki 
                            </label>
                            <label>
                                <input type="radio" name="jenis_kelamin" value="P" required> Perempuan 
                            </label>
                        </div>
                    </div>
                </div>

                <!-- IDENTITAS DINAMIS -->
                <div class="row">
                    <div class="form-group" id="fieldNIK">
                        <label>NIK <span class="required" id="nikRequired">*</span></label>
                        <input type="text" name="nik" id="inputNIK" placeholder="16 digit NIK" maxlength="16">
                    </div>
                    
                    <div class="form-group" id="fieldPaspor" style="display:none;">
                        <label>No. Paspor <span class="required" id="pasporRequired">*</span></label>
                        <input type="text" name="paspor" id="inputPaspor" placeholder="Nomor Paspor">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group">
                        <label>Kebangsaan</label>
                        <input type="text" name="kebangsaan" value="Indonesia" placeholder="Kebangsaan">
                    </div>
                    
                    <div class="form-group">
                        <label>Pekerjaan</label>
                        <input type="text" name="pekerjaan" placeholder="Pekerjaan saat ini">
                    </div>
                </div>

                <div class="form-group" id="fieldNamaWali" style="display:none;">
                    <label>Nama Wali <span class="required">*</span></label>
                    <input type="text" name="nama_wali" id="inputNamaWali" placeholder="Nama orang tua/wali">
                </div>
            </div>

            <!-- KONTAK -->
            <div class="form-section">
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="emails[]" required placeholder="contoh@email.com">
                </div>
                
                <div class="form-group">
                    <label>Nomor HP <span class="required">*</span></label>
                    <input type="tel" name="phones[]" required placeholder="08123456789">
                </div>
                
                <div class="form-group">
                    <label>Alamat Lengkap <span class="required">*</span></label>
                    <textarea name="alamat" required placeholder="Jalan, RT/RW, Kelurahan, Kecamatan"></textarea>
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

            <!-- KALENDER BOOKING -->
            <div class="form-section">
                <h2 class="section-title">Pilih Jadwal</h2>

                <div class="calendar-wrapper">
                    <div class="calendar-header">
                        <button type="button" onclick="prevMonth()">&lt;</button>
                        <h2><?php echo $nama_bulan[$bulan] . ' ' . $tahun; ?></h2>
                        <button type="button" onclick="nextMonth()">&gt;</button>
                    </div>

                    <div class="calendar-days">
                        <div class="day-header">M</div>
                        <div class="day-header">S</div>
                        <div class="day-header">S</div>
                        <div class="day-header">R</div>
                        <div class="day-header">K</div>
                        <div class="day-header">J</div>
                        <div class="day-header">S</div>

                        <?php
                        // Kosongkan hari sebelum tanggal 1
                        for ($i = 0; $i < $hari_awal; $i++) {
                            echo '<div class="day empty"></div>';
                        }

                        // Tampilkan tanggal
                        $today_date = date('Y-m-d');

                        for ($tgl = 1; $tgl <= $jumlah_hari; $tgl++) {
                        $tanggal_full = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tgl);
                        $is_today = ($tanggal_full === $today_date);

                        // ❌ JIKA TANGGAL SUDAH LEWAT → DISABLE
                        if ($tanggal_full < $today_date) {
                            echo "<div class='day disabled past-date' title='Tanggal sudah lewat'>$tgl</div>";
                            continue;
                        }

                        // Cek status dari DB
                        $status = checkDateStatus($conn, $tanggal_full);
                        $class = getDateClass($status, $is_today);
                        $title = getDateTitle($status);
                        $clickable = isDateClickable($status);

                        if ($clickable) {
                            echo "<div class='$class' onclick='selectDate(this, $tgl)' title='$title'>$tgl</div>";
                        } else {
                            echo "<div class='$class' title='$title'>$tgl</div>";
                        }
                    }

                        ?>
                    </div>

                    <div class="legend">
                        <div class="legend-item">
                            <div class="legend-box tersedia"></div>
                            <span>Tersedia</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box penuh"></div>
                            <span>Jadwal Penuh</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box holiday"></div>
                            <span>Libur</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-box closed"></div>
                            <span>Tutup</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="tanggal_booking" id="selectedDate" value="">
                
                <div class="selected-date" id="dateDisplay" style="display:none;">
                    Tanggal yang dipilih: <strong id="dateText"></strong>
                </div>

                <input type="hidden" name="waktu_booking" id="selectedTime">

                <div class="time-slots" id="timeSlots" style="display:none;">
                    <h3>Pilih Jam</h3>
                    <div class="slots-container" id="slotsContainer"></div>
                </div>
            </div>

            <!-- BUTTONS -->
            <div class="form-actions">
                <button type="submit" name="action" value="add_more" class="btn btn-secondary" id="btnTambahPeserta" disabled>
                    <i class="fas fa-user-plus"></i> Tambah Peserta
                </button>
                
                <button type="submit" name="action" value="finish" class="btn btn-primary" id="btnSelesai" disabled>
                    <i class="fas fa-check"></i> Selesai
                </button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="nav-logo-footer">
                <img src="vaksinin-logo.png" alt="Vaksinin">
            </div>
            <div class="footer-section">
                <h3>Jam Operasional</h3>
                <h4>Home Service</h4>
                <p>Dengan bantuan helper</p>
                <h4>Klinik</h4>
                <p>Senin - Sabtu : 08:00 – 17:00</p>
                <p>Minggu : 08:00 – 18:30</p>
                <div class="note-footer">
                    <p>Hari libur nasional dan hari besar lainnya : <b>Tutup</b></p>
                </div>
            </div>
            <div class="footer-section">
                <h3>Hubungi Kami</h3>
                <h4>Klinik Vaksinin</h4>
                <p>Komplek Ruko Sentra Menteng Blok M No. 981<br>
                Jl. MH. Thamrin, Bintaro Sektor 7<br>
                Kel. Pondok Jaya, Kec. Pondok Aren,<br>
                Kota Tangerang Selatan, Banten 15220</p>
                <a href="https://goo.gl/maps/f2suTc2vR7JC1Me47" class="map-link" target="_blank">
                    Lihat di Google Maps
                </a>
            </div>
        </div>
        
        <div class="contact-icons-wrapper">
            <div class="contact-icons-container">
                <div class="contact-icons-row">
                    <div class="contact-icon-item">
                        <i class="fab fa-whatsapp"></i>
                        <span class="number">082137372757</span>
                    </div>
                    <div class="contact-icon-item">
                        <i class="fas fa-phone"></i>
                        <span class="number">02122214342</span>
                    </div>
                    <div class="contact-icon-item">
                        <i class="fab fa-instagram"></i>
                        <a href="https://instagram.com/vaksinin.id" target="_blank">@vaksinin.id</a>
                    </div>
                    <div class="contact-icon-item">
                        <i class="fab fa-facebook"></i>
                        <a href="https://facebook.com/vaksinin.id" target="_blank">vaksinin.id</a>
                    </div>
                    <div class="contact-icon-item">
                        <i class="fas fa-envelope"></i>
                        <a href="mailto:vaksinin.id@gmail.com">vaksinin.id@gmail.com</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="copyright">
            <p>© 2024 Vaksinin.id - Seluruh hak cipta dilindungi undang-undang</p>
            <p>Lindungi Diri dan Keluarga dengan Vaksinasi</p>
        </div>
    </footer>

    <script>
        const bulanNow = <?php echo $bulan; ?>;
        const tahunNow = <?php echo $tahun; ?>;
        const namaBulanNow = '<?php echo $nama_bulan[$bulan]; ?>';
    </script>
    <script src="provinces.js"></script>
    <script src="script.js?v=<?php echo time(); ?>"></script>
    <script src="service.js"></script>
</body>
</html>