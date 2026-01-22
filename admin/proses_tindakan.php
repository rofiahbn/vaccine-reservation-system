<?php
session_start();
include "../config.php";

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
                <input type="text" name="jenis_vaksin" required>
            </div>

            <div class="detail-item">
                <label>No. Batch Vaksin</label>
                <input type="text" name="batch_vaksin" required>
            </div>

            <div class="detail-item">
                <label>Tanggal Kadaluarsa Vaksin</label>
                <input type="date" name="expired_vaksin" required>
            </div>

            <!-- ================= KEDATANGAN ================= -->
            <div class="detail-item">
                <label>Kedatangan ke</label>
                <select name="kedatangan_ke">
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                </select>
            </div>

            <div class="detail-item">
                <label>Kedatangan Selanjutnya</label>
                <select name="kedatangan_selanjutnya">
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                </select>
            </div>

            <div class="detail-item">
                <label>Status</label>
                <select name="status">
                    <option value="Aktif">Aktif</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </div>

            <!-- ================= ANAMNESIS ================= -->
            <div class="detail-item full-width">
                <label>Anamnesis</label>
                <textarea name="anamnesis"></textarea>
            </div>

            <div class="detail-item full-width">
                <label>Pemeriksaan Fisik</label>
                <textarea name="pemeriksaan_fisik"></textarea>
            </div>

            <div class="detail-item full-width">
                <label>Diagnosis</label>
                <textarea name="diagnosis"></textarea>
            </div>

            <div class="detail-item full-width">
                <label>Tatalaksana</label>
                <textarea name="tatalaksana"></textarea>
            </div>

            <!-- ================= VITAL SIGNS ================= -->
            <div class="detail-item">
                <label>Suhu (°C)</label>
                <input type="number" step="0.1" name="suhu">
            </div>

            <div class="detail-item">
                <label>Tekanan Darah (mmHg)</label>
                <input type="text" name="tekanan_darah" placeholder="120/80">
            </div>

            <div class="detail-item">
                <label>Respirasi (/menit)</label>
                <input type="number" name="respirasi">
            </div>

            <div class="detail-item">
                <label>Nadi (/menit)</label>
                <input type="number" name="nadi">
            </div>

            <!-- ================= SURAT ================= -->
            <div class="detail-item full-width">
                <label>Buat Surat</label>
                <div class="checkbox-group">
                    <label>
                        <input type="radio" name="surat" value="sehat">
                        Surat Sehat
                    </label>
                    <label>
                        <input type="radio" name="surat" value="sakit">
                        Surat Sakit
                    </label>
                    <label>
                        <input type="radio" name="surat" value="vaksin">
                        Sertifikat Vaksin
                    </label>
                </div>
            </div>

            <div class="detail-item">
                <label>Tanggal Surat</label>
                <input type="date" name="tanggal_surat">
            </div>

            <div class="detail-item">
                <label>Tanda Tangan Oleh</label>
                <select name="dokter_id">
                    <?php while($d = $dokters->fetch_assoc()): ?>
                        <option value="<?= $d['id'] ?>">
                            <?= htmlspecialchars($d['gelar'].' '.$d['nama_lengkap']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="detail-item">
                <label>Posisi</label>
                <input type="text" name="posisi" placeholder="Dokter Penanggung Jawab">
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
                    Isi kelengkapan data terlebih dahulu  
                    <br><br>
                    Preview surat akan muncul di sini
                </div>

                <div class="preview-content" id="previewContent" style="display:none;"></div>
            </div>

            <!-- BUTTON ACTION -->
            <div class="preview-actions">

                <button class="btn-print-preview">
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
        const PV_TGL_LAHIR = "<?= $booking['tanggal_lahir'] ?>";
        const PV_JK = "<?= $booking['jenis_kelamin'] ?>";
        const PV_IDENTITAS = "<?= $booking['nik'] ?: $booking['paspor'] ?>";
        const PV_TGL_VAKSIN = "<?= $booking['tanggal_booking'] ?>";
        const PV_DOKTER = "";
        const PV_TGL_SURAT = "";
    </script>

    <script src="js/preview_surat.js"></script>

</body>
</html>
