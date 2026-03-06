<?php
session_start();

// Cek apakah ada peserta di session
if (!isset($_SESSION['participants']) || empty($_SESSION['participants'])) {
    header('Location: order');
    exit;
}

$participants = $_SESSION['participants'];
$total_peserta = count($participants);

$firstBooking = $participants[0];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pendaftaran - Vaksinin</title>
    <link rel="stylesheet" href="system/style.css">
    <link rel="stylesheet" href="system/layout.css">
    <link rel="stylesheet" href="system/confirmation_styles.css">
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

       <?php include "content/header.php"; ?> 

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

        <div class="booking-schedule" style="margin-bottom:30px;">
            <h3><i class="fas fa-calendar-check"></i> Jadwal Booking</h3>

            <div class="schedule-info">
                <div class="schedule-item">
                    <i class="fas fa-calendar-day"></i>
                    <span>
                        <?php 
                        $booking_date = new DateTime($firstBooking['tanggal_booking']);
                        echo $booking_date->format('d F Y'); 
                        ?>
                    </span>
                </div>

                <div class="schedule-item">
                    <i class="fas fa-clock"></i>
                    <span><?= $firstBooking['waktu_booking']; ?> WIB</span>
                </div>

                <div class="schedule-item">
                    <i class="fas fa-stethoscope"></i>
                    <span><?= htmlspecialchars($firstBooking['service_type']); ?></span>
                </div>
            </div>
        </div>

        <?php foreach ($participants as $index => $p): ?>
        <div class="participant-card">
            <div class="participant-header">
                <div style="display: flex; align-items: center; flex: 1;">
                    <div class="participant-number"><?php echo $index + 1; ?></div>
                    <div class="participant-name"><?php echo htmlspecialchars($p['nama_lengkap']); ?></div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="participant-badge">
                        <?php echo htmlspecialchars($p['pelayanan']); ?>
                    </div>
                    <button type="button" class="btn-edit-small" onclick="editParticipant(<?php echo $index; ?>)" title="Edit Peserta">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn-delete-small" onclick="deleteParticipant(<?php echo $index; ?>)" title="Hapus Peserta">
                        <i class="fas fa-trash"></i>
                    </button>
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

            <?php if (!empty($p['selected_products']) && is_array($p['selected_products'])): ?>
            <div class="selected-services">
                <h4><i class="fas fa-list-check"></i> Layanan yang Dipilih</h4>
                <div class="services-list">
                    <?php foreach ($p['selected_products'] as $product): ?>
                    <div class="service-badge">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($product['name']); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>

        <form action="final_submit" method="POST" id="confirmForm">

            <div class="action-buttons">
                <input type="hidden" name="action" value="finish">
                <button type="button" class="btn btn-add" onclick="window.location.href='add_participant'">
                    <i class="fas fa-user-plus"></i> Tambah Peserta Lagi
                </button>
                
                <button type="button" class="btn btn-danger" onclick="resetAll()">
                    <i class="fas fa-trash"></i> Reset Semua
                </button>

                <button type="submit" class="btn btn-confirm" id="confirmBtn">
                    <i class="fas fa-check-circle"></i> Konfirmasi & Simpan
                </button>
            </div>

        </form>

    </div>

<?php include "content/footer.php"; ?>

    <script>
    // Confirm sebelum submit
    document.getElementById('confirmForm').addEventListener('submit', function (e) {
        if (!confirm('Apakah Anda yakin semua data sudah benar dan ingin melanjutkan?')) {
            e.preventDefault();
            return;
        }

        const btn = document.getElementById('confirmBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    });

    // Edit Peserta
    function editParticipant(index) {
        if (confirm('Edit data peserta ini? Data akan dimuat di form edit.')) {
            window.location.href = 'edit_participant?index=' + index;
        }
    }

    // Hapus Peserta
    function deleteParticipant(index) {
        if (confirm('Yakin ingin menghapus peserta ini?')) {
            window.location.href = 'delete_participant?index=' + index;
        }
    }

    // Reset Semua
    function resetAll() {
        if (confirm('PERHATIAN! Ini akan menghapus SEMUA peserta dan kembali ke halaman awal. Lanjutkan?')) {
            window.location.href = 'reset_session';
        }
    }
    </script>

</body>
</html>