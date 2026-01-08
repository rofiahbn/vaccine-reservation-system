<?php
session_start();
include "config.php";

// validasi awal
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['id_pasien'], $_POST['tanggal'], $_POST['waktu_booking'])
) {
    header('Location: order.php');
    exit;
}

$waktu = $_POST['waktu_booking'] ?? null;

$waktu_format = $waktu
    ? date('H:i', strtotime($waktu))
    : '-';

$patient_id = (int) $_POST['id_pasien'];
$tanggal = $_POST['tanggal'];
$waktu   = $_POST['waktu_booking'];

// ================= VALIDASI SLOT =================
$cek = $conn->prepare("
    SELECT id FROM bookings
    WHERE tanggal_booking = ?
      AND waktu_booking = ?
      AND status != 'cancelled'
    LIMIT 1
");
$cek->bind_param("ss", $tanggal, $waktu);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    echo "<script>
        alert('Jam ini sudah dibooking, silakan pilih jam lain.');
        window.history.back();
    </script>";
    exit;
}
$cek->close();

// ================= DATA PASIEN =================
$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

if (!$patient) {
    die("Data pasien tidak ditemukan");
}

// email
$stmt = $conn->prepare("
    SELECT email FROM patient_emails
    WHERE patient_id = ? AND is_primary = 1 LIMIT 1
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$email = $stmt->get_result()->fetch_assoc()['email'] ?? '-';
$stmt->close();

// phone
$stmt = $conn->prepare("
    SELECT phone FROM patient_phones
    WHERE patient_id = ? AND is_primary = 1 LIMIT 1
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$phone = $stmt->get_result()->fetch_assoc()['phone'] ?? '-';
$stmt->close();

// address
$stmt = $conn->prepare("
    SELECT alamat FROM patient_addresses
    WHERE patient_id = ? AND is_primary = 1 LIMIT 1
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$address = $stmt->get_result()->fetch_assoc()['alamat'] ?? '-';
$stmt->close();

// format tanggal
$tanggal_obj = new DateTime($tanggal);
$hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$bulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$tanggal_format =
    $hari[$tanggal_obj->format('w')] . ', ' .
    $tanggal_obj->format('d') . ' ' .
    $bulan[$tanggal_obj->format('n')] . ' ' .
    $tanggal_obj->format('Y');

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Data Reservasi</title>
    <link rel="stylesheet" href="confirmation.css">

    <link rel="stylesheet" href="layout.css">
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
        <div class="header">
            <div class="icon">✓</div>
            <h1>Konfirmasi Ulang Data</h1>
            <p class="subtitle">Pastikan data Anda sudah benar sebelum melanjutkan</p>
        </div>

        <div class="card">
            <h2 class="card-title">📋 Data Pasien</h2>
            
            <div class="data-row">
                <span class="label">No. Rekam Medis</span>
                <span class="value"><?php echo htmlspecialchars($patient['no_rekam_medis']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Nama Lengkap</span>
                <span class="value"><?php echo htmlspecialchars($patient['nama_lengkap']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Nama Panggilan</span>
                <span class="value"><?php echo htmlspecialchars($patient['nama_panggilan']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Tanggal Lahir</span>
                <span class="value"><?php echo date('d/m/Y', strtotime($patient['tanggal_lahir'])); ?> (<?php echo $patient['usia']; ?> tahun)</span>
            </div>

            <div class="data-row">
                <span class="label">Jenis Kelamin</span>
                <span class="value"><?php echo $patient['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
            </div>

            <div class="data-row">
                <span class="label">NIK/Paspor</span>
                <span class="value"><?php echo htmlspecialchars($patient['nik_paspor']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Kebangsaan</span>
                <span class="value"><?php echo htmlspecialchars($patient['kebangsaan']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Pekerjaan</span>
                <span class="value"><?php echo htmlspecialchars($patient['pekerjaan']); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Nama Wali</span>
                <span class="value"><?php echo htmlspecialchars($patient['nama_wali']); ?></span>
            </div>
        </div>

        <div class="card">
            <h2 class="card-title">📞 Informasi Kontak</h2>
            
            <div class="data-row">
                <span class="label">Email</span>
                <span class="value"><?php echo htmlspecialchars($email); ?></span>
            </div>

            <div class="data-row">
                <span class="label">No. HP</span>
                <span class="value"><?php echo htmlspecialchars($phone); ?></span>
            </div>

            <div class="data-row">
                <span class="label">Alamat</span>
                <span class="value"><?php echo htmlspecialchars($address); ?></span>
            </div>
        </div>

        <div class="card highlight">
            <h2 class="card-title">📅 Jadwal Reservasi</h2>
                
                <div class="data-row">
                    <span class="label">Tanggal</span>
                    <span class="value bold"><?php echo $tanggal_format; ?></span>
                </div>

                <div class="data-row">
                    <span class="label">Waktu</span>
                    <span class="value bold"><?php echo $waktu_format; ?></span>
                </div>
            </div>

        <?php if ($patient['riwayat_alergi'] || $patient['riwayat_penyakit']): ?>
        <div class="card warning">
            <h2 class="card-title">⚠️ Riwayat Kesehatan</h2>
            
            <?php if ($patient['riwayat_alergi']): ?>
            <div class="data-row">
                <span class="label">Riwayat Alergi</span>
                <span class="value"><?php echo htmlspecialchars($patient['riwayat_alergi']); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($patient['riwayat_penyakit']): ?>
            <div class="data-row">
                <span class="label">Riwayat Penyakit</span>
                <span class="value"><?php echo htmlspecialchars($patient['riwayat_penyakit']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="actions">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='order.php?id_pasien=<?php echo $patient_id; ?>'">
                ← Edit
            </button>

            <form method="POST" action="save_booking.php" style="flex: 1;">
                <input type="hidden" name="id_pasien" value="<?php echo $patient_id; ?>">
                <input type="hidden" name="tanggal" value="<?php echo $tanggal; ?>">
                <input type="hidden" name="waktu_booking" value="<?= $waktu ?>">
                
                <button type="submit" class="btn btn-primary">
                    Konfirmasi & Lanjutkan →
                </button>
            </form>
        </div>

        <div class="note">
            <p><strong>Catatan:</strong> Setelah konfirmasi, Anda akan menerima nomor antrian dan detail reservasi via email/WhatsApp.</p>
        </div>
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