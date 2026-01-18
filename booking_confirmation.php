<?php
session_start();

// Cek apakah ada peserta di session
if (!isset($_SESSION['participants']) || empty($_SESSION['participants'])) {
    header('Location: order.php');
    exit;
}

$participants = $_SESSION['participants'];
$total_peserta = count($participants);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pendaftaran - Vaksinin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="layout.css">
    <link rel="stylesheet" href="confirmation_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (isset($_SESSION['error_message'])): ?>
    <div style="
        background:#fee2e2;
        border:1px solid #ef4444;
        padding:15px;
        border-radius:8px;
        margin:20px;
        color:#991b1b;
        font-weight:600;
    ">
        ERROR: <?= htmlspecialchars($_SESSION['error_message']); ?>
    </div>
<?php unset($_SESSION['error_message']); endif; ?>

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

    <div class="confirmation-container">
        <div class="summary-box">
            <h1><i class="fas fa-clipboard-check"></i> Konfirmasi Data Pendaftaran</h1>
            <p>Pastikan semua data sudah benar sebelum melanjutkan</p>
        </div>

        <div style="background: #e0f2fe; border-left: 4px solid #0284c7; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
            <strong><i class="fas fa-info-circle" style="color: #0284c7;"></i> Total Peserta:</strong> 
            <span style="font-size: 18px; font-weight: 700; color: #0c4a6e;"><?php echo $total_peserta; ?> orang</span>
        </div>

        <?php foreach ($participants as $index => $p): ?>
        <div class="participant-card">
            <div class="participant-header">
                <div style="display: flex; align-items: center; flex: 1;">
                    <div class="participant-number"><?php echo $index + 1; ?></div>
                    <div class="participant-name"><?php echo htmlspecialchars($p['nama_lengkap']); ?></div>
                </div>
                <div class="participant-badge">
                    <?php echo htmlspecialchars($p['pelayanan']); ?>
                </div>
            </div>

            <div class="participant-details">
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="detail-content">
                        <div class="detail-label">Tanggal Lahir</div>
                        <div class="detail-value">
                            <?php 
                            $tgl = new DateTime($p['tanggal_lahir']);
                            echo $tgl->format('d M Y'); 
                            ?>
                        </div>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div class="detail-content">
                        <div class="detail-label">Usia</div>
                        <div class="detail-value"><?php echo $p['usia']; ?> tahun (<?php echo $p['kategori_usia']; ?>)</div>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-venus-mars"></i>
                    </div>
                    <div class="detail-content">
                        <div class="detail-label">Jenis Kelamin</div>
                        <div class="detail-value"><?php echo $p['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                    </div>
                </div>

                <?php if (!empty($p['nik'])): ?>
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="detail-content">
                        <div class="detail-label">NIK</div>
                        <div class="detail-value"><?php echo htmlspecialchars($p['nik']); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($p['paspor'])): ?>
                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-passport"></i>
                    </div>
                    <div class="detail-content">
                        <div class="detail-label">No. Paspor</div>
                        <div class="detail-value"><?php echo htmlspecialchars($p['paspor']); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="detail-content">
                        <div class="detail-label">Telepon</div>
                        <div class="detail-value"><?php echo htmlspecialchars($p['phones'][0] ?? '-'); ?></div>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="detail-content">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?php echo htmlspecialchars($p['emails'][0] ?? '-'); ?></div>
                    </div>
                </div>

                <div class="detail-item" style="grid-column: 1 / -1;">
                    <div class="detail-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="detail-content">
                        <div class="detail-label">Alamat</div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($p['alamat']); ?>, 
                            <?php echo htmlspecialchars($p['kota']); ?>, 
                            <?php echo htmlspecialchars($p['provinsi']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="booking-schedule">
                <h4><i class="fas fa-calendar-check"></i> Jadwal Booking</h4>
                <div class="schedule-info">
                    <div class="schedule-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>
                            <?php 
                            $booking_date = new DateTime($p['tanggal_booking']);
                            echo $booking_date->format('d F Y'); 
                            ?>
                        </span>
                    </div>
                    <div class="schedule-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo $p['waktu_booking']; ?> WIB</span>
                    </div>
                    <div class="schedule-item">
                        <i class="fas fa-stethoscope"></i>
                        <span>
                            <?php echo htmlspecialchars($p['service_type'] ?? '-'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <form action="final_submit.php" method="POST" id="confirmForm">

            <div class="action-buttons">
                <button type="button" class="btn btn-back" onclick="window.location.href='order.php'">
                    Kembali & Edit
                </button>

                <button type="submit" class="btn btn-confirm" id="confirmBtn">
                    Konfirmasi & Simpan
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
        document.getElementById('confirmForm').addEventListener('submit', function (e) {

            if (!confirm('Apakah Anda yakin semua data sudah benar dan ingin melanjutkan?')) {
                e.preventDefault(); // batal submit
                return;
            }

            const btn = document.getElementById('confirmBtn');
            btn.disabled = true;
            btn.innerHTML = 'Menyimpan...';

        });
        </script>

</body>
</html>