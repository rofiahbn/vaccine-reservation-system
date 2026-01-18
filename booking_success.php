<?php
session_start();

// Cek apakah ada data success booking
if (!isset($_SESSION['success_bookings']) || empty($_SESSION['success_bookings'])) {
    header('Location: order.php');
    exit;
}

$bookings = $_SESSION['success_bookings'];
$total_bookings = count($bookings);

// Format tanggal Indonesia
function formatTanggalIndonesia($tanggal) {
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $date = new DateTime($tanggal);
    $hari = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    
    $nama_hari = $hari[$date->format('l')];
    $tgl = $date->format('d');
    $bln = $bulan[(int)$date->format('n')];
    $thn = $date->format('Y');
    
    return "$nama_hari, $tgl $bln $thn";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Berhasil - Vaksinin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="layout.css">
    <link rel="stylesheet" href="success_styles.css">
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

    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Pendaftaran Berhasil!</h1>
            <p>Terima kasih telah mendaftar. Booking Anda telah dikonfirmasi.</p>
        </div>

        <div class="info-banner">
            <strong><i class="fas fa-info-circle"></i> Informasi Penting:</strong>
            <p>Silakan simpan atau catat nomor antrian Anda. Nomor antrian akan digunakan saat Anda datang ke klinik pada tanggal dan waktu yang telah dipilih.</p>
        </div>

        <div class="booking-cards">
            <h2 style="margin-bottom: 20px; color: #1f2937;">Detail Booking (<?php echo $total_bookings; ?> Peserta)</h2>
            
            <?php foreach ($bookings as $index => $booking): ?>
            <div class="booking-card">
                <div class="booking-header">
                    <div style="display: flex; align-items: center; flex: 1;">
                        <div class="booking-number"><?php echo $index + 1; ?></div>
                        <div class="booking-name"><?php echo htmlspecialchars($booking['nama']); ?></div>
                    </div>
                    <div class="antrian-badge">
                        <i class="fas fa-ticket-alt"></i> 
                        <?php echo htmlspecialchars($booking['nomor_antrian']); ?>
                    </div>
                </div>

                <div class="booking-details">
                    <div class="booking-detail-item">
                        <i class="fas fa-file-medical"></i>
                        <div>
                            <strong>No. Rekam Medis</strong>
                            <span><?php echo htmlspecialchars($booking['no_rekam_medis']); ?></span>
                        </div>
                    </div>

                    <div class="booking-detail-item">
                        <i class="fas fa-<?php echo $booking['service_type'] === 'Home Service' ? 'home' : 'hospital'; ?>"></i>
                        <div>
                            <strong>Tipe Layanan</strong>
                            <span><?php echo htmlspecialchars($booking['service_type']); ?></span>
                        </div>
                    </div>
                    
                    <div class="booking-detail-item">
                        <i class="fas fa-calendar-day"></i>
                        <div>
                            <strong>Tanggal</strong>
                            <span><?php echo formatTanggalIndonesia($booking['tanggal_booking']); ?></span>
                        </div>
                    </div>
                    
                    <div class="booking-detail-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Jam</strong>
                            <span><?php echo htmlspecialchars($booking['waktu_booking']); ?> WIB</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="important-notes">
            <h3><i class="fas fa-exclamation-triangle"></i> Hal yang Perlu Diperhatikan:</h3>
            <ul>
                <li>Harap datang <strong>15 menit lebih awal</strong> dari waktu yang telah ditentukan</li>
                <li>Bawa <strong>KTP/Identitas asli</strong> dan <strong>nomor antrian</strong> Anda</li>
                <li>Jika membawa anak-anak, bawa <strong>Kartu Keluarga</strong> atau <strong>Akta Kelahiran</strong></li>
                <li>Jika berhalangan hadir, silakan hubungi klinik untuk reschedule</li>
                <li>Untuk informasi lebih lanjut, hubungi: <strong>082137372757</strong> (WhatsApp) atau <strong>021-22214342</strong></li>
            </ul>
        </div>

        <div class="action-buttons">
            <button type="button" class="btn btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Bukti Booking
            </button>
            
            <button type="button" class="btn btn-home" onclick="goToHome()">
                <i class="fas fa-home"></i> Kembali ke Beranda
            </button>
        </div>
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
        function goToHome() {
            // Clear session success data
            <?php unset($_SESSION['success_bookings']); ?>
            window.location.href = 'order.php';
        }
    </script>
</body>
</html>