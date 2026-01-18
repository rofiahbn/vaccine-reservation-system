<?php
session_start();
include "config.php";
include "calendar_helper.php";

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

// Jika form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi data
    $errors = [];
    
    $service_type = $_POST['service_type'] ?? '';
    $pelayanan = $_POST['pelayanan'] ?? '';
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tanggal_booking = $_POST['tanggal_booking'] ?? '';
    $waktu_booking = $_POST['waktu_booking'] ?? '';
    $action = $_POST['action'] ?? ''; // 'add_more' atau 'finish'
    
    if (empty($service_type)) $errors[] = 'Tipe layanan harus dipilih';
    if (empty($pelayanan)) $errors[] = 'Pelayanan harus dipilih';
    if (empty($nama_lengkap)) $errors[] = 'Nama lengkap harus diisi';
    if (empty($tanggal_lahir)) $errors[] = 'Tanggal lahir harus diisi';
    if (empty($jenis_kelamin)) $errors[] = 'Jenis kelamin harus dipilih';
    if (empty($tanggal_booking)) $errors[] = 'Tanggal booking harus dipilih';
    if (empty($waktu_booking)) $errors[] = 'Waktu booking harus dipilih';
    
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
    $emails = array_filter($_POST['emails'] ?? []);
    $phones = array_filter($_POST['phones'] ?? []);
    
    if (count($emails) < 1) $errors[] = 'Minimal harus ada 1 email';
    if (count($phones) < 1) $errors[] = 'Minimal harus ada 1 nomor HP';
    
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
        
        $_SESSION['participants'][] = $participant_data;
        
        // Cek action button mana yang diklik
        if ($action === 'add_more') {
            // Redirect ke add_participant.php lagi (form baru)
            $_SESSION['success_message'] = 'Peserta berhasil ditambahkan! Silakan tambah peserta lagi.';
            header('Location: add_participant.php');
            exit;
        } else if ($action === 'finish') {
            // Redirect ke halaman konfirmasi
            header('Location: booking_confirmation.php');
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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="layout.css">
    <link rel="stylesheet" href="calender.css">
    <link rel="stylesheet" href="calendar_styles.css">
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
    <nav class="navbar">
        <div class="nav-logo">
            <img src="logo-vaksinin.jpeg" alt="Vaksinin">
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
        <a href="order.php" class="back-button">
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

        <h1>Formulir Data Peserta</h1>
        <p class="subtitle">Isi dan lengkapi data peserta tambahan</p>

        <form id="addParticipantForm" method="POST" action="">

            <!-- PILIH TIPE LAYANAN -->
            <div class="form-section">
                <div class="form-group">
                    <label>Tipe Layanan <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-card">
                            <input type="radio" name="service_type" value="Home Service" required>
                            <div class="radio-card-content">
                                <i class="fas fa-home"></i>
                                <strong>Home Service</strong>
                                <small>Layanan ke rumah Anda</small>
                            </div>
                        </label>
                        <label class="radio-card">
                            <input type="radio" name="service_type" value="In Clinic" required>
                            <div class="radio-card-content">
                                <i class="fas fa-hospital"></i>
                                <strong>In Clinic</strong>
                                <small>Kunjungi klinik kami</small>
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
                    <div id="emailContainer">
                        <div class="dynamic-field">
                            <input type="email" name="emails[]" required placeholder="contoh@email.com">
                        </div>
                    </div>
                    <button type="button" class="btn btn-add" onclick="addField('email')">+ Tambah Email</button>
                </div>
                
                <div class="form-group">
                    <label>Nomor HP <span class="required">*</span></label>
                    <div id="phoneContainer">
                        <div class="dynamic-field">
                            <input type="tel" name="phones[]" required placeholder="08123456789">
                        </div>
                    </div>
                    <button type="button" class="btn btn-add" onclick="addField('phone')">+ Tambah Nomor HP</button>
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

            <!-- KALENDER BOOKING -->
            <div class="form-section">
                <h2 class="section-title">Pilih Jadwal untuk Peserta Ini</h2>
                
                <div class="calendar-header">
                    <button type="button" onclick="changeMonth(-1)">&lt;</button>
                    <h2 id="calendarTitle"><?php echo $nama_bulan[$bulan] . ' ' . $tahun; ?></h2>
                    <button type="button" onclick="changeMonth(1)">&gt;</button>
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
                    for ($tgl = 1; $tgl <= $jumlah_hari; $tgl++) {
                        $tanggal_full = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tgl);
                        $is_today = ($tgl == $hari_ini);
                        
                        // Cek status tanggal (libur, tutup, penuh)
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
                <button type="button" class="btn btn-secondary" onclick="window.location.href='order.php'">
                    <i class="fas fa-times"></i> Batal
                </button>
                
                <button type="submit" name="action" value="add_more" class="btn btn-secondary" id="btnAddMore" disabled>
                    <i class="fas fa-user-plus"></i> Tambah Peserta Lagi
                </button>
                
                <button type="submit" name="action" value="finish" class="btn btn-primary" id="btnFinish" disabled>
                    <i class="fas fa-check"></i> Selesai
                </button>
            </div>
        </form>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="nav-logo-footer">
                <img src="logo-vaksinin.jpeg" alt="Vaksinin">
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
    <script src="script.js"></script>
    <script>
        // Override button submit untuk enable/disable KEDUA button
        function selectTime(element, time) {
            document.querySelectorAll('.time-slot').forEach(s => {
                s.classList.remove('selected');
            });

            element.classList.add('selected');
            document.getElementById('selectedTime').value = time;

            // Enable KEDUA button
            document.getElementById('btnAddMore').disabled = false;
            document.getElementById('btnFinish').disabled = false;
        }
        
        // Load provinsi saat halaman load
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof loadProvinsi === 'function') {
                loadProvinsi();
            }
        });
    </script>
</body>
</html>