<?php
session_start();
include "../config.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Booking ID tidak ditemukan.");
}

$booking_id = intval($_GET['id']);

/* Ambil booking + pasien */
$sql = "
SELECT b.*, 
       p.id AS patient_id,
       p.no_rekam_medis,
       p.nama_lengkap,
       p.tanggal_lahir,
       p.jenis_kelamin,
       p.nik,
       p.paspor
FROM bookings b
JOIN patients p ON b.patient_id = p.id
WHERE b.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    die("Data booking tidak ditemukan.");
}

// ================= AMBIL DATA TINDAKAN JIKA SUDAH ADA =================
$sql_tindakan = "SELECT * FROM tindakan WHERE booking_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt_t = $conn->prepare($sql_tindakan);
$stmt_t->bind_param("i", $booking_id);
$stmt_t->execute();
$tindakan = $stmt_t->get_result()->fetch_assoc();

function hitungUsia($tanggal_lahir) {
    $lahir = new DateTime($tanggal_lahir);
    $sekarang = new DateTime();
    $diff = $sekarang->diff($lahir);

    return $diff->y . " tahun " . $diff->m . " bulan";
}

$usia = hitungUsia($booking['tanggal_lahir']);

function formatTanggalIndo($tanggal) {
    if (!$tanggal) return "";

    $bulan = [
        1 => 'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];

    $exp = explode('-', $tanggal);

    if (count($exp) != 3) return $tanggal;

    return $exp[2] . ' ' . $bulan[(int)$exp[1]] . ' ' . $exp[0];
}

$tgl_lahir_indo = formatTanggalIndo($booking['tanggal_lahir']);

/* Ambil layanan */
$sql_services = "SELECT nama_layanan FROM booking_services WHERE booking_id = ?";
$stmt_s = $conn->prepare($sql_services);
$stmt_s->bind_param("i", $booking_id);
$stmt_s->execute();
$services = $stmt_s->get_result();

/* Ambil dokter */
$sql_staff = "
SELECT s.id, s.nama_lengkap, s.gelar
FROM booking_staff bs
JOIN staff s ON bs.staff_id = s.id
WHERE bs.booking_id = ?
";
$stmt_d = $conn->prepare($sql_staff);
$stmt_d->bind_param("i", $booking_id);
$stmt_d->execute();
$dokters = $stmt_d->get_result();

$tanggal_surat = date("Y-m-d");
$tanggal_surat_indo = formatTanggalIndo($tanggal_surat);

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Proses / Tindakan</title>

<link rel="stylesheet" href="css/proses_tindakan.css">
<link rel="stylesheet" href="css/surat.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidebar">
    <div class="logo">
        <img src="vaksinin-logo.png" alt="Vaksinin">
    </div>
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
    </nav>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">

    <!-- HEADER -->
    <div class="detail-header">
        <button onclick="window.location.href='booking_detail.php?id=<?= $booking_id ?>'" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </button>
        <h1>Proses / Tindakan Pasien</h1>
    </div>

    <div class="detail-layout">

        <!-- ================= FORM KIRI ================= -->
        <div class="detail-container">

        <form id="formTindakan">

        <input type="hidden" name="booking_id" value="<?= $booking_id ?>">
        <input type="hidden" name="patient_id" value="<?= $booking['patient_id'] ?>">

        <div class="proses-container">

        <div class="detail-grid">

            <!-- ================= DATA DASAR ================= -->
            <div class="detail-item full-width">
                <label>Layanan</label>
                <?php while($s = $services->fetch_assoc()): ?>
                    <input type="text" value="<?= htmlspecialchars($s['nama_layanan']) ?>" readonly>
                <?php endwhile; ?>
            </div>

            <div class="detail-item">
                <label>No. Rekam Medis</label>
                <input type="text" value="<?= htmlspecialchars($booking['no_rekam_medis']) ?>" readonly>
            </div>

            <div class="detail-item">
                <label>Nama Lengkap</label>
                <input type="text" value="<?= htmlspecialchars($booking['nama_lengkap']) ?>" readonly>
            </div>

            <div class="detail-item">
                <label>Tanggal Vaksinasi</label>
                <input type="date" value="<?= $booking['tanggal_booking'] ?>" readonly>
            </div>

            <div class="detail-item">
                <label>No Identitas</label>
                <input type="text" value="<?= htmlspecialchars($booking['nik'] ?: $booking['paspor']) ?>" readonly>
            </div>

            <!-- ================= DATA VAKSIN ================= -->
            <div class="detail-item">
                <label>Jenis Vaksinasi</label>
                <input type="text" name="jenis_vaksin"
                    value="<?= htmlspecialchars($tindakan['jenis_vaksin'] ?? '') ?>">
            </div>

            <div class="detail-item">
                <label>No. Batch Vaksin</label>
                <input type="text" name="batch_vaksin"
                    value="<?= htmlspecialchars($tindakan['batch_vaksin'] ?? '') ?>">
            </div>

            <div class="detail-item">
                <label>Tanggal Kadaluarsa Vaksin</label>
                <input type="date" name="expired_vaksin"
                    value="<?= $tindakan['expired_vaksin'] ?? '' ?>">
            </div>

            <!-- ================= KEDATANGAN ================= -->
            <div class="detail-item">
                <label>Kedatangan ke</label>
                <select name="kedatangan_ke">
                    <option value="1" <?= ($tindakan['kedatangan_ke'] ?? '') == '1' ? 'selected' : '' ?>>1</option>
                    <option value="2" <?= ($tindakan['kedatangan_ke'] ?? '') == '2' ? 'selected' : '' ?>>2</option>
                    <option value="3" <?= ($tindakan['kedatangan_ke'] ?? '') == '3' ? 'selected' : '' ?>>3</option>
                </select>
            </div>

            <div class="detail-item">
                <label>Kedatangan Selanjutnya</label>
                <select name="kedatangan_selanjutnya">
                    <option value="1" <?= ($tindakan['kedatangan_selanjutnya'] ?? '') == '1' ? 'selected' : '' ?>>1</option>
                    <option value="2" <?= ($tindakan['kedatangan_selanjutnya'] ?? '') == '2' ? 'selected' : '' ?>>2</option>
                    <option value="3" <?= ($tindakan['kedatangan_selanjutnya'] ?? '') == '3' ? 'selected' : '' ?>>3</option>
                </select>
            </div>

            <div class="detail-item">
                <label>Status</label>
                <select name="status">
                    <option value="Aktif" <?= ($tindakan['status'] ?? '') == 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="Selesai" <?= ($tindakan['status'] ?? '') == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>

            <!-- ================= ANAMNESIS ================= -->
            <div class="detail-item full-width">
                <label>Anamnesis</label>
                <textarea name="anamnesis"><?= htmlspecialchars($tindakan['anamnesis'] ?? '') ?></textarea>
            </div>

            <div class="detail-item full-width">
                <label>Pemeriksaan Fisik</label>
                <textarea name="pemeriksaan_fisik"><?= htmlspecialchars($tindakan['pemeriksaan_fisik'] ?? '') ?></textarea>
            </div>

            <div class="detail-item full-width">
                <label>Diagnosis</label>
                <textarea name="diagnosis"><?= htmlspecialchars($tindakan['diagnosis'] ?? '') ?></textarea>
            </div>

            <div class="detail-item full-width">
                <label>Tatalaksana</label>
                <textarea name="tatalaksana"><?= htmlspecialchars($tindakan['tatalaksana'] ?? '') ?></textarea>
            </div>

            <!-- ================= VITAL SIGNS ================= -->
            <div class="detail-item">
                <label>Suhu (°C)</label>
                <input type="number" step="0.1" name="suhu"
                    value="<?= $tindakan['suhu'] ?? '' ?>">
            </div>

            <div class="detail-item">
                <label>Tekanan Darah (mmHg)</label>
                <input type="text" name="tekanan_darah"
                    value="<?= htmlspecialchars($tindakan['tekanan_darah'] ?? '') ?>"
                    placeholder="120/80">
            </div>

            <div class="detail-item">
                <label>Respirasi (/menit)</label>
                <input type="number" name="respirasi"
                    value="<?= $tindakan['respirasi'] ?? '' ?>">
            </div>

            <div class="detail-item">
                <label>Nadi (/menit)</label>
                <input type="number" name="nadi"
                    value="<?= $tindakan['nadi'] ?? '' ?>">
            </div>

        </div>

        <!-- ACTION BUTTON -->
        <div class="action-buttons">
            <button type="button" class="btn-secondary" onclick="window.history.back()">Batal</button>
            <button type="submit" class="btn-save">Simpan Tindakan</button>
        </div>

        </div>
        </form>

        </div>

        <!-- ================= PANEL KANAN (PREVIEW NANTI) ================= -->
        <div class="detail-right">

            <!-- PREVIEW SURAT -->
            <div class="preview-panel" id="previewPanel">

                <!-- TOMBOL MAXIMIZE -->
                <button class="btn-maximize" onclick="openFullPreview()">
                    <i class="fas fa-expand"></i>
                </button>

                <div class="preview-placeholder" id="previewPlaceholder">
                    Isi kelengkapan data dan pilih jenis surat terlebih dahulu. Preview surat akan muncul di sini
                </div>

                <div class="preview-content" id="previewContent" style="display:none;"></div>
            </div>

            <div class="surat-control-panel">

                <div class="panel-title">
                    <i class="fas fa-file-medical"></i>
                    Pengaturan Surat
                </div>

                <!-- PILIH JENIS SURAT -->
                <div class="control-group">
                    <label class="group-label">Jenis Surat</label>

                    <div class="radio-group modern-radio">
                        <label class="radio-card">
                            <input type="radio" name="surat" value="sehat">
                            <span>Surat Sehat</span>
                        </label>

                        <label class="radio-card">
                            <input type="radio" name="surat" value="sakit">
                            <span>Surat Sakit</span>
                        </label>

                        <label class="radio-card">
                            <input type="radio" name="surat" value="vaksin">
                            <span>Sertifikat Vaksin</span>
                        </label>
                    </div>
                </div>

                <!-- FORM ISTIRAHAT (KHUSUS SURAT SAKIT) -->
                <div class="control-group" id="form-istirahat" style="display:none;">

                    <label class="group-label">Keterangan Istirahat</label>

                    <div class="istirahat-grid">

                        <div class="istirahat-item">
                            <label>Lama (hari)</label>
                            <input type="number" id="input_lama" placeholder="Contoh: 2">
                        </div>

                        <div class="istirahat-item">
                            <label>Tanggal Awal</label>
                            <input type="date" id="input_tgl_awal">
                        </div>

                        <div class="istirahat-item">
                            <label>Tanggal Akhir</label>
                            <input type="date" id="input_tgl_akhir">
                        </div>

                    </div>
                </div>

                <!-- PEMERIKSAAN FISIK LAIN (KHUSUS SURAT SEHAT) -->
                <div class="control-group" id="form-pf-lain" style="display:none;">

                    <label class="group-label">Pemeriksaan Fisik Lain</label>

                    <textarea id="input_pf_lain"
                            class="modern-input"
                            rows="3"
                            placeholder="Kosongkan jika dalam batas normal"></textarea>

                </div>

                <!-- DOKTER PENANDATANGAN -->
                <div class="control-group">
                    <label class="group-label">Dokter Penandatangan</label>
                    <select name="dokter_id" class="modern-select">
                        <?php 
                        $dokters->data_seek(0);
                        while($d = $dokters->fetch_assoc()): ?>
                            <option value="<?= $d['id'] ?>">
                                <?= htmlspecialchars($d['gelar'].' '.$d['nama_lengkap']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- POSISI -->
                <div class="control-group">
                    <label class="group-label">Posisi</label>
                    <input type="text" name="posisi" class="modern-input"
                        value="Dokter Penanggung Jawab">
                </div>

            </div>

            <!-- BUTTON ACTION -->
            <div class="preview-actions">

                <button type="button" class="btn-print-preview" id="btnCetakSurat">
                    Cetak Surat
                </button>

                <button class="btn-send-preview">
                    Kirim Surat
                </button>

            </div>

        </div>
    </div>

</div>
    <!-- MODAL FULL PREVIEW -->
    <div id="modalPreview" class="modal-preview" style="display:none;">
        <div class="modal-content">

            <button class="btn-close" onclick="closePreview()">
                ✕
            </button>

            <div id="modalPreviewContent"></div>
        </div>
    </div>
                        
    <script>
        const PV_RM = "<?= $booking['no_rekam_medis'] ?>";
        const PV_NAMA = "<?= addslashes($booking['nama_lengkap']) ?>";
        const PV_TGL_LAHIR = "<?= $tgl_lahir_indo ?>";
        const PV_USIA = "<?= $usia ?>";
        const PV_JK = "<?= $booking['jenis_kelamin'] ?>";
        const PV_IDENTITAS = "<?= $booking['nik'] ?: $booking['paspor'] ?>";
        const PV_TGL_VAKSIN = "<?= $booking['tanggal_booking'] ?>";
        const PV_DOKTER = "";
        const PV_TANGGAL_SURAT = "<?= $tanggal_surat_indo ?>";
    </script>

    <script src="js/preview_surat.js"></script>
    <script src="js/simpan_tindakan.js"></script>
    <script src="js/cetak_surat.js"></script>                        

</body>
</html>
