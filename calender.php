<?php
session_start();

// Ambil patient_id dari URL
$patient_id = isset($_GET['id_pasien']) ? intval($_GET['id_pasien']) : 0;

if (!$patient_id) {
    header('Location: order.php');
    exit;
}

// Set bulan dan tahun (default: bulan ini)
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
$hari_pertama = date('w', strtotime("$tahun-$bulan-01"));

// Hari ini
$hari_ini = ($bulan == date('n') && $tahun == date('Y')) ? date('j') : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tanggal dan Jadwalkan</title>
    <link rel="stylesheet" href="calender.css">

    <link rel="stylesheet" href="layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        <h1>Pilih Tanggal dan Jadwalkan</h1>

        <form method="GET" action="" id="monthForm">
            <input type="hidden" name="id_pasien" value="<?php echo $patient_id; ?>">
            <input type="hidden" name="bulan" id="inputBulan" value="<?php echo $bulan; ?>">
            <input type="hidden" name="tahun" id="inputTahun" value="<?php echo $tahun; ?>">
        </form>

        <div class="calendar-header">
            <button onclick="prevMonth()">&lt;</button>
            <h2><?php echo $nama_bulan[$bulan] . ' ' . $tahun; ?></h2>
            <button onclick="nextMonth()">&gt;</button>
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
            $hari_awal = ($hari_pertama == 0) ? 6 : $hari_pertama - 1; // Konversi ke Senin = 0
            for ($i = 0; $i < $hari_awal; $i++) {
                echo '<div class="day empty"></div>';
            }

            // Tampilkan tanggal
            for ($tgl = 1; $tgl <= $jumlah_hari; $tgl++) {
                $class = 'day';
                
                // Hari ini
                if ($tgl == $hari_ini) {
                    $class .= ' today';
                }
                
                // Contoh: tanggal 15 sudah penuh (bisa diganti dengan cek database)
                if ($tgl == 15) {
                    $class .= ' full';
                    echo "<div class='$class'>$tgl</div>";
                } else {
                    echo "<div class='$class' onclick='selectDate(this, $tgl)'>$tgl</div>";
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
        </div>

        <form method="POST" action="booking_confirmation.php" id="bookingForm">
            <input type="hidden" name="id_pasien" value="<?php echo $patient_id; ?>">
            <input type="hidden" name="tanggal" id="selectedDate" value="">
            
            <div class="selected-date" id="dateDisplay" style="display:none;">
                Tanggal yang dipilih: <strong id="dateText"></strong>
            </div>

            <input type="hidden" name="waktu_booking" id="selectedTime">

            <div class="time-slots" id="timeSlots" style="display:none;">
                <h3>Pilih Jam</h3>
                <div class="slots-container" id="slotsContainer"></div>
            </div>


            <button type="submit" class="btn-submit" id="btnSubmit" disabled>
                Selesai
            </button>
        </form>
    </div>

    <script>
        // Pass PHP variables to JavaScript
        const bulanNow = <?php echo $bulan; ?>;
        const tahunNow = <?php echo $tahun; ?>;
        const namaBulanNow = '<?php echo $nama_bulan[$bulan]; ?>';
    </script>
    <script src="calender.js"></script>

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
            <div class="note">
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
    
    <!-- CONTACT ICONS DI TENGAH BAWAH -->
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
</body>
</html>