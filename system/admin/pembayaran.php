<?php 
include "config.php";

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id == 0) {
    header('Location: dashboard.php');
    exit;
}

// Ambil data services untuk dropdown edit deskripsi
$sql_services_list = "SELECT id, nama_layanan, tipe, harga FROM services WHERE tipe IN ('pelayanan', 'paket') ORDER BY tipe, nama_layanan ASC";
$result_services_list = $conn->query($sql_services_list);
$services_list = [];
while ($row = $result_services_list->fetch_assoc()) {
    $services_list[] = $row;
}

/* ================= CEK JENIS BOOKING ================= */
$sql_jenis = "SELECT parent_id, service_type, tanggal_booking FROM bookings WHERE id = ?";
$stmt_jenis = $conn->prepare($sql_jenis);
$stmt_jenis->bind_param("i", $booking_id);
$stmt_jenis->execute();
$result_jenis = $stmt_jenis->get_result();
$jenis_data = $result_jenis->fetch_assoc();

// Cek apakah data ditemukan
if (!$jenis_data) {
    header('Location: dashboard.php');
    exit;
}

$is_child = ($jenis_data['parent_id'] != NULL);
$parent_booking_id = $is_child ? $jenis_data['parent_id'] : $booking_id;
$service_type = $jenis_data['service_type'];
$tanggal_layanan = $jenis_data['tanggal_booking'];

/* ================= HITUNG JATUH TEMPO ================= */
function hitungJatuhTempo($service_type, $tanggal_pesanan, $tanggal_layanan) {
    $tgl_pesanan = new DateTime($tanggal_pesanan);
    $tgl_layanan = new DateTime($tanggal_layanan);
    
    if ($service_type == 'In Clinic') {
        return $tgl_layanan->format('Y-m-d');
    } else {
        if ($tgl_pesanan->format('Y-m-d') == $tgl_layanan->format('Y-m-d')) {
            return $tgl_pesanan->format('Y-m-d');
        } else {
            $tgl_layanan->modify('-1 day');
            return $tgl_layanan->format('Y-m-d');
        }
    }
}

// Ambil tanggal pesanan (created_at)
$sql_tgl_pesanan = "SELECT created_at FROM bookings WHERE id = ?";
$stmt_tgl = $conn->prepare($sql_tgl_pesanan);
$stmt_tgl->bind_param("i", $parent_booking_id);
$stmt_tgl->execute();
$result_tgl = $stmt_tgl->get_result();
$tgl_pesanan_row = $result_tgl->fetch_assoc();
$tgl_pesanan = $tgl_pesanan_row ? $tgl_pesanan_row['created_at'] : date('Y-m-d H:i:s');

$jatuh_tempo = hitungJatuhTempo($service_type, $tgl_pesanan, $tanggal_layanan);

/* ================= AMBIL SEMUA PESERTA ================= */
$sql_peserta = "
    SELECT b.*, p.nama_lengkap 
    FROM bookings b 
    JOIN patients p ON b.patient_id = p.id 
    WHERE b.parent_id = ? OR b.id = ?
    ORDER BY b.id
";
$stmt_peserta = $conn->prepare($sql_peserta);
$stmt_peserta->bind_param("ii", $parent_booking_id, $parent_booking_id);
$stmt_peserta->execute();
$peserta_result = $stmt_peserta->get_result();

$semua_peserta = [];
$jumlah_peserta = 0;
$peserta_ids = [];
while ($row = $peserta_result->fetch_assoc()) {
    $semua_peserta[] = $row;
    $peserta_ids[] = $row['id'];
    $jumlah_peserta++;
}

/* ================= AMBIL DATA BOOKING UTAMA ================= */
$sql = "SELECT b.*, b.payment_status, 
               p.nama_lengkap, 
               p.no_rekam_medis
        FROM bookings b 
        JOIN patients p ON b.patient_id = p.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $parent_booking_id);
$stmt->execute();
$result_booking = $stmt->get_result();
$booking = $result_booking->fetch_assoc();

if (!$booking) {
    echo "<script>alert('Data booking tidak ditemukan!'); window.location.href='dashboard.php';</script>";
    exit;
}

/* Ambil no HP utama */
$sql_phone = "SELECT phone FROM patient_phones 
              WHERE patient_id = ? 
              ORDER BY is_primary DESC 
              LIMIT 1";
$stmt_ph = $conn->prepare($sql_phone);
$stmt_ph->bind_param("i", $booking['patient_id']);
$stmt_ph->execute();
$phone = $stmt_ph->get_result()->fetch_assoc()['phone'] ?? '-';

/* Ambil alamat utama */
$sql_addr = "SELECT * FROM patient_addresses 
             WHERE patient_id = ? 
             AND is_primary = 1 
             LIMIT 1";
$stmt_ad = $conn->prepare($sql_addr);
$stmt_ad->bind_param("i", $booking['patient_id']);
$stmt_ad->execute();
$address = $stmt_ad->get_result()->fetch_assoc();

/* ================= AMBIL RIWAYAT PEMBAYARAN ================= */
$sql_riwayat = "
    SELECT p.*, 
           GROUP_CONCAT(pmd.metode SEPARATOR ' + ') as metode_detail,
           GROUP_CONCAT(pmd.amount SEPARATOR ', ') as amount_detail
    FROM payments p
    LEFT JOIN payment_methods_detail pmd ON p.id = pmd.payment_id
    WHERE p.booking_id = ?
    GROUP BY p.id
    ORDER BY p.created_at DESC
";
$stmt_riwayat = $conn->prepare($sql_riwayat);
$stmt_riwayat->bind_param("i", $parent_booking_id);
$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();

// Simpan data riwayat ke array
$riwayat_data = [];
$total_sudah_dibayar = 0;

while ($row = $result_riwayat->fetch_assoc()) {
    $riwayat_data[] = $row;
    if ($row['status'] == 'paid' || $row['status'] == 'partial') {
        $total_sudah_dibayar += $row['amount_paid'];
    }
}

$total_diskon_global = 0;

foreach ($riwayat_data as $rw) {
    $total_diskon_global += floatval($rw['diskon'] ?? 0);
}

/* ================= AMBIL LAYANAN & HITUNG TOTAL ================= */
if ($jumlah_peserta > 1) {
    $sql_services = "
        SELECT bs.*, b.id as booking_id, p.nama_lengkap
        FROM booking_services bs
        JOIN bookings b ON bs.booking_id = b.id
        JOIN patients p ON b.patient_id = p.id
        WHERE b.parent_id = ? OR b.id = ?
        ORDER BY b.id
    ";
    $stmt_s = $conn->prepare($sql_services);
    $stmt_s->bind_param("ii", $parent_booking_id, $parent_booking_id);
} else {
    $sql_services = "
        SELECT id, nama_layanan, harga, diskon, diskon_tipe, total 
        FROM booking_services 
        WHERE booking_id = ?
    ";
    $stmt_s = $conn->prepare($sql_services);
    $stmt_s->bind_param("i", $parent_booking_id);
}

$stmt_s->execute();
$result_services = $stmt_s->get_result();

// Hitung total tagihan
$subtotal = 0;
$total_tagihan = 0;
$data_services = [];
$total_diskon_item = 0;
$total_harga_item = 0;

while ($row = $result_services->fetch_assoc()) {
    $row['jumlah'] = 1;
    $diskon = $row['diskon'] ?? 0;
    $row['total'] = $row['harga'] - $diskon;
    
    $subtotal += $row['harga'];
    $total_tagihan += $row['total'];
    $total_diskon_item += $diskon;
    $total_harga_item += $row['harga'];
    $data_services[] = $row;
}

$total_tagihan_final = $total_tagihan - $total_diskon_global;

if ($total_tagihan_final < 0) {
    $total_tagihan_final = 0;
}

$sisa_tagihan = $total_tagihan_final - $total_sudah_dibayar;

if ($sisa_tagihan < 0) {
    $sisa_tagihan = 0;
}

/* ================= VALIDASI TINDAKAN ================= */
$all_completed = true;
$nama_belum = [];

foreach ($semua_peserta as $peserta) {
    if ($peserta['tindakan_selesai'] == 0) {
        $all_completed = false;
        $nama_belum[] = $peserta['nama_lengkap'];
    }
}

if (!$all_completed) {
    $peserta_list = implode(", ", $nama_belum);
    echo "<script>
        alert('Simpan tindakan terlebih dahulu untuk: $peserta_list');
        window.location.href = 'booking_detail.php?id=$parent_booking_id';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Proses Pembayaran</title>
    <link rel="stylesheet" href="system/admin/css/admin.css">
    <link rel="stylesheet" href="system/admin/css/pembayaran.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
     <!-- Sidebar -->
  <?php include "content/sidebar.php"; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="detail-header">
            <button onclick="window.location.href='booking_detail.php?id=<?= $parent_booking_id ?>'" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </button>
            <h1>Proses Pembayaran</h1>
        </div>

        <!-- SUMMARY PEMBAYARAN -->
        <div class="payment-summary">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="color: white; margin: 0; font-size: 24px;">
                    <i class="fas fa-file-invoice-dollar"></i> Ringkasan Pembayaran
                </h2>
                <?php if ($jumlah_peserta > 1): ?>
                    <div style="background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 14px;">
                        <i class="fas fa-users"></i> <?= $jumlah_peserta ?> Peserta
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="summary-grid">
                <div class="summary-item">
                    <span class="summary-label">Total Tagihan</span>
                    <div class="summary-value" id="totalTagihanSummary">
                        Rp <?= number_format($total_tagihan_final, 0, ',', '.') ?>
                    </div>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Sudah Dibayar</span>
                    <div class="summary-value" id="sudahBayarSummary">
                        Rp <?= number_format($total_sudah_dibayar, 0, ',', '.') ?>
                    </div>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Sisa Tagihan</span>
                    <div class="summary-value" id="sisaTagihanSummary">
                        Rp <?= number_format($sisa_tagihan, 0, ',', '.') ?>
                    </div>
                </div>
                
                <div class="summary-item">
                    <span class="summary-label">Jatuh Tempo</span>
                    <div class="summary-value">
                        <?= date('d M Y', strtotime($jatuh_tempo)) ?>
                    </div>
                    <span class="summary-label"><?= $service_type ?></span>
                </div>
            </div>
            
            <!-- STATUS BADGE -->
            <div class="status-badge-container">
                <?php if ($sisa_tagihan <= 0): ?>
                    <span class="badge-status lunas">
                        <i class="fas fa-check-circle"></i> LUNAS
                    </span>
                <?php elseif ($total_sudah_dibayar > 0): ?>
                    <span class="badge-status sebagian">
                        <i class="fas fa-clock"></i> SEBAGIAN 
                        (<?php echo ($total_tagihan_final > 0 ? round(($total_sudah_dibayar / $total_tagihan_final) * 100) : 0); ?>%)
                    </span>
                <?php else: ?>
                    <span class="badge-status belum">
                        <i class="fas fa-times-circle"></i> BELUM BAYAR
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIWAYAT PEMBAYARAN -->
        <div class="riwayat-pembayaran">
            <h3><i class="fas fa-history"></i> Riwayat Pembayaran</h3>
            <div class="riwayat-card">

                <?php if (count($riwayat_data) > 0): ?>

                    <?php foreach ($riwayat_data as $riwayat): ?>
                    <div class="riwayat-item">
                        <div class="riwayat-info">
                            <div class="riwayat-date">
                                <?= date('d M Y H:i', strtotime($riwayat['created_at'])) ?>
                            </div>
                            <div class="riwayat-methods">
                                <i class="fas fa-credit-card"></i>
                                <?= $riwayat['metode_detail'] ?? $riwayat['metode'] ?>
                            </div>
                        </div>
                        <div class="riwayat-amount">
                            <div class="riwayat-total">
                                Rp <?= number_format($riwayat['amount_paid'], 0, ',', '.') ?>
                            </div>
                            <span class="riwayat-badge <?= $riwayat['status'] == 'paid' ? 'badge-paid' : ($riwayat['status'] == 'partial' ? 'badge-partial' : 'badge-pending') ?>">
                                <?= strtoupper($riwayat['status']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="riwayat-empty">
                        <i class="fas fa-receipt"></i>
                        <p>Belum ada pembayaran</p>
                    </div>

                <?php endif; ?>

            </div>
        </div>

        <div class="payment-layout">
            <!-- KIRI: DETAIL LAYANAN -->
            <div class="payment-left">
                <h3>
                    <?php if ($jumlah_peserta > 1): ?>
                        <i class="fas fa-users"></i> Rincian Layanan
                    <?php else: ?>
                        <i class="fas fa-user"></i> Rincian Layanan
                    <?php endif; ?>
                </h3>
                <div class="data-pasien">
                    <!-- Kolom Kiri -->
                    <span class="label"><b>Nama Pasien</b></span>
                    <span class="colon">:</span>
                    <span class="value"><?= htmlspecialchars($booking['nama_lengkap']) ?></span>

                    <!-- Kolom Kanan -->
                    <span class="label"><b>No. Antrian</b></span>
                    <span class="colon">:</span>
                    <span class="value"><?= $booking['nomor_antrian'] ?></span>

                    <!-- Baris 2 -->
                    <span class="label"><b>Alamat</b></span>
                    <span class="colon">:</span>
                    <span class="value">
                        <?php if ($address): ?>
                            <?= htmlspecialchars($address['alamat']) ?>, 
                            <?= htmlspecialchars($address['kota']) ?>, 
                            <?= htmlspecialchars($address['provinsi']) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </span>

                    <span class="label"><b>Tanggal Pelayanan</b></span>
                    <span class="colon">:</span>
                    <span class="value"><?= date('d F Y', strtotime($booking['tanggal_booking'])) ?></span>

                    <!-- Baris 3 -->
                    <span class="label"><b>No. Telpon</b></span>
                    <span class="colon">:</span>
                    <span class="value"><?= htmlspecialchars($phone) ?></span>

                    <span class="label"><b>Tanggal Jatuh Tempo</b></span>
                    <span class="colon">:</span>
                    <span class="value">
                        <?= date('d F Y', strtotime($jatuh_tempo)) ?>
                    </span>

                    <!-- Baris 4 (hanya kiri) -->
                    <span class="label"></span>
                    <span class="colon"></span>
                    <span class="value"></span>
                </div>
                
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <?php if ($jumlah_peserta > 1): ?>
                                <th>Peserta</th>
                            <?php endif; ?>
                            <th>Deskripsi</th>
                            <th>Jml</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($data_services as $i => $srv): 
                            $id_item = $srv['id'];
                            $harga = $srv['harga'];
                            $diskon_item = $srv['diskon'] ?? 0;
                            $total_per_item = $harga - $diskon_item;
                            $peserta_nama = isset($srv['nama_lengkap']) ? $srv['nama_lengkap'] : $booking['nama_lengkap'];
                            $diskon_tipe = $srv['diskon_tipe'] ?? '';
                            $diskon_persen = $diskon_item > 0 ? round(($diskon_item / $harga) * 100) : 0;
                        ?>
                        <tr data-harga="<?= $harga ?>" data-harga-lama="<?= $harga ?>" data-index="<?= $i ?>">
                            <td><?= $counter++ ?></td>
                            <?php if ($jumlah_peserta > 1): ?>
                                <td>
                                    <span class="peserta-text">
                                        <?= htmlspecialchars($peserta_nama) ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span id="deskripsi-text-<?= $i ?>"><?= htmlspecialchars($srv['nama_layanan']) ?></span>
                                    <button type="button"
                                        class="btn-edit-diskon"
                                        onclick="openEditDeskripsi(<?= $id_item ?>, <?= $i ?>, '<?= htmlspecialchars($srv['nama_layanan'], ENT_QUOTES) ?>')"
                                        title="Edit Deskripsi">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                            <td>1</td>
                            <td class="harga-item">Rp <?= number_format($harga, 0, ',', '.') ?></td>
                            <td class="diskon-cell" id="diskon-cell-<?= $i ?>">

                                <?php if ($diskon_item > 0): ?>
                                    <div class="diskon-applied">

                                        <?php if ($diskon_tipe == 'persen'): ?>
                                            <span class="diskon-badge persen">
                                                <?= $diskon_persen ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="diskon-badge nilai">
                                                - Rp <?= number_format($diskon_item, 0, ',', '.') ?>
                                            </span>
                                        <?php endif; ?>

                                        <div class="diskon-info">
                                            <?= $diskon_tipe == 'persen' ? 
                                                "($diskon_persen% = Rp " . number_format($diskon_item, 0, ',', '.') . ")" : 
                                                "" 
                                            ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="no-diskon">-</span>
                                <?php endif; ?>

                                <!-- 🔥 BUTTON PINDAH KE SINI -->
                                <button type="button"
                                    class="btn-edit-diskon"
                                    onclick="openDiskonItem(<?= $id_item ?>, <?= $i ?>, <?= $harga ?>,
                                            '<?= $diskon_tipe ?>', <?= $diskon_item ?>, 
                                            '<?= htmlspecialchars($srv['nama_layanan']) ?>')"
                                    title="Edit Diskon">
                                    <i class="fas fa-edit"></i>
                                </button>

                            </td>

                            <td class="total-item" id="total-item-<?= $i ?>">
                                Rp <?= number_format($total_per_item, 0, ',', '.') ?>
                            </td>
                            <input type="hidden" name="service_id[]" id="service_id_<?=$i ?>" value="<?= $srv['id'] ?>">
                            <input type="hidden" name="service_diskon[]" id="service_diskon_<?= $i ?>" value="<?= $diskon_item ?>">
                            <input type="hidden" name="service_diskon_tipe[]" id="service_diskon_tipe_<?= $i ?>" value="<?= $diskon_tipe ?>">
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="<?= $jumlah_peserta > 1 ? '4' : '3' ?>" style="text-align: right; font-weight: 600;">
                                Total:
                            </td>
                            <td id="total-diskon-items" style="color: #ef4444; font-weight: 600;">
                                - Rp <?= number_format($total_diskon_item, 0, ',', '.') ?>
                            </td>
                            <td id="total-semua-items" style="font-weight: 700; color: #1e293b;">
                                Rp <?= number_format($total_tagihan_final, 0, ',', '.') ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- KANAN: FORM PEMBAYARAN -->
            <div class="payment-right">
                <div class="summary-card">
                    <h4><i class="fas fa-calculator"></i> Ringkasan Pembayaran</h4>
                    
                    <div class="total-line">
                        <span class="label">Subtotal</span>
                        <span class="value" id="subtotalDisplay">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="total-line">
                        <span class="label">Diskon Item</span>
                        <span class="value" id="diskonItemDisplay" style="color: #ef4444;">
                            - Rp <?= number_format($total_diskon_item, 0, ',', '.') ?>
                        </span>
                    </div>
                    
                    <!-- DISKON TOTAL -->
                    <div class="diskon-total-container">
                        <label>
                            <i class="fas fa-tag"></i> Diskon Tambahan (Total)
                        </label>

                        <!-- SELECT TIPE -->
                        <div class="diskon-type-selector">

                            <div class="type-option active"
                                data-type="persen"
                                onclick="selectDiskonTotalType('persen')">

                                <i class="fas fa-percentage"></i>
                                <span>Persentase (%)</span>

                            </div>

                            <div class="type-option"
                                data-type="nominal"
                                onclick="selectDiskonTotalType('nominal')">

                                <i class="fas fa-money-bill-wave"></i>
                                <span>Nominal (Rp)</span>

                            </div>

                        </div>

                        <!-- INPUT PERSEN -->
                        <div id="diskonPersenContainer">
                            <input type="number"
                                id="diskonTotalPersen"
                                placeholder="Masukkan persen diskon"
                                class="diskon-input"
                                oninput="hitungDiskonTotal('persen')"
                                min="0" max="100">
                        </div>

                        <!-- INPUT NOMINAL -->
                        <div id="diskonNominalContainer" style="display:none;">
                            <input type="number"
                                id="diskonTotalNominal"
                                placeholder="Masukkan nilai diskon"
                                class="diskon-input"
                                oninput="hitungDiskonTotal('nominal')"
                                min="0" step="1000">
                        </div>

                        <button type="button" class="btn-diskon" onclick="applyDiskonTotal()">
                            <i class="fas fa-check"></i> Terapkan Diskon
                        </button>

                        <div id="diskonTotalInfo" class="info-message">
                            <i class="fas fa-info-circle"></i>
                            Diskon akan diterapkan pada total tagihan
                        </div>
                    </div>
                    
                    <div class="total-line">
                        <span class="label">Diskon Total</span>

                        <span class="value" id="diskonTotalDisplay" style="color: #ef4444;">
                            - Rp 0
                        </span>

                        <!-- Tombol Edit -->
                        <button type="button" 
                            id="btnEditDiskonTotal"
                            class="btn-edit-diskon"
                            style="display:none; margin-left:10px;"
                            onclick="editDiskonTotal()">
                            <i class="fas fa-edit"></i>
                        </button>

                        <!-- Tombol Hapus -->
                        <button type="button" 
                            id="btnRemoveDiskonTotal"
                            class="btn-edit-diskon"
                            style="display:none; margin-left:5px;"
                            onclick="removeDiskonTotal()">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    
                    <div class="total-line">
                        <span class="label">Total Tagihan</span>
                        <strong class="value" id="totalTagihan">
                            Rp <?= number_format($total_tagihan_final, 0, ',', '.') ?>
                        </strong>
                    </div>
                    
                    <div class="total-line">
                        <span class="label">Sudah Dibayar</span>
                        <span class="value">Rp <?= number_format($total_sudah_dibayar, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="total-final-line sisa">
                        <span class="label">Sisa Tagihan</span>
                        <strong class="final-value" id="sisaTagihan">
                            Rp <?= number_format($sisa_tagihan, 0, ',', '.') ?>
                        </strong>
                    </div>
                </div>

                <!-- JATUH TEMPO -->
                <div class="jatuh-tempo-card">
                    <h4><i class="fas fa-calendar-alt"></i> Jatuh Tempo</h4>
                    <div class="jatuh-tempo-content">
                        <div class="jatuh-tempo-date">
                            <?= date('d', strtotime($jatuh_tempo)) ?>
                        </div>
                        <div style="font-size: 18px; font-weight: 600; color: #475569;">
                            <?= date('M Y', strtotime($jatuh_tempo)) ?>
                        </div>
                        <div class="jatuh-tempo-info">
                            <span class="info-tag"><?= $service_type ?></span>
                            <?php if ($jumlah_peserta > 1): ?>
                                <span class="info-tag"><?= $jumlah_peserta ?> Peserta</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="pay-action">
                    <?php 
                    // SELALU TAMPILKAN TOMBOL CETAK, TIDAK PERLU KONDISI $sisa_tagihan <= 0
                    ?>
                    
                    <!-- Tombol Cetak - SELALU TAMPIL -->
                    <button class="btn-cetak" 
                            onclick="window.open('cetak_pembayaran.php?id=<?= $parent_booking_id ?>', '_blank')"
                            title="Cetak faktur semua peserta">
                        <i class="fas fa-print"></i> Cetak Pembayaran
                    </button>

                    <?php if ($jumlah_peserta > 1): ?>
                    <!-- Dropdown Cetak Per Peserta -->
                    <div class="dropdown-cetak" style="position: relative; display: inline-block;">
                        <button class="btn-cetak" onclick="toggleDropdownCetak()" 
                                style="background: #0369a1;"
                                title="Cetak per peserta">
                            <i class="fas fa-user-check"></i> Cetak Per Peserta <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                        </button>
                        <div id="dropdownCetakMenu" style="display:none; position:absolute; bottom:110%; left:0;
                                background:white; border:1px solid #e2e8f0; border-radius:8px; 
                                box-shadow:0 4px 12px rgba(0,0,0,0.15); min-width:220px; z-index:999;">
                            <?php foreach ($semua_peserta as $p): ?>
                            <a href="cetak_pembayaran.php?id=<?= $parent_booking_id ?>&peserta_id=<?= $p['id'] ?>"
                            target="_blank"
                            onclick="document.getElementById('dropdownCetakMenu').style.display='none'"
                            style="display:block; padding:10px 15px; color:#1e293b; text-decoration:none;
                                    border-bottom:1px solid #f1f5f9; font-size:13px;"
                            onmouseover="this.style.background='#f0f9ff'"
                            onmouseout="this.style.background='white'">
                                <i class="fas fa-user" style="color:#0369a1; margin-right:6px;"></i>
                                <?= htmlspecialchars($p['nama_lengkap']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Tombol Kirim Invoice - SELALU TAMPIL -->
                    <button class="btn-invoice" onclick="kirimInvoice()">
                        <i class="fas fa-paper-plane"></i> Kirim Invoice
                    </button>
                    
                    <!-- Tombol Bayar (kondisional) -->
                    <?php if ($sisa_tagihan > 0): ?>
                        <?php if ($total_sudah_dibayar == 0): ?>
                            <!-- Belum bayar sama sekali -->
                            <button class="btn-bayar-big" onclick="openMultiplePayment()">
                                <i class="fas fa-money-bill-wave"></i> Proses Pembayaran
                            </button>
                        <?php else: ?>
                            <!-- Sudah bayar sebagian -->
                            <button class="btn-partial" onclick="openBayarLagi()">
                                <i class="fas fa-plus-circle"></i> Tambah Pembayaran
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL MULTIPLE PAYMENT ================= -->
    <div id="popupMultiplePayment" class="popup-overlay" style="display:none;">
        <div class="popup-box" style="width: 600px; max-width: 95vw;">
            <div class="popup-header">
                <h2><i class="fas fa-money-bill-wave"></i> Pembayaran Multiple Metode</h2>
                <button class="popup-close" onclick="closeMultiplePayment()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <p style="color: #64748b; margin-bottom: 25px;">
                Anda dapat membayar dengan beberapa metode sekaligus dalam satu transaksi.
            </p>
            
            <form action="proses_bayar_multiple.php" method="POST" id="formMultiplePayment">
                <input type="hidden" name="booking_id" value="<?= $parent_booking_id ?>">
                <!-- TAMBAHKAN INPUT INI: -->
                <input type="hidden" name="subtotal" id="subtotalInput" value="<?= $subtotal ?>">
                <input type="hidden" name="total_tagihan" id="totalTagihanInput" value="<?= $total_tagihan_final ?>">
                <input type="hidden" name="sisa_tagihan" id="sisaTagihanInput" value="<?= $sisa_tagihan ?>">
                <input type="hidden" name="diskon_total" id="diskonTotalInput" value="0">
                <input type="hidden" name="jatuh_tempo" value="<?= $jatuh_tempo ?>">
                
                <!-- Input untuk service items -->
                <?php foreach($data_services as $i => $srv): ?>
                <input type="hidden" name="service_id[]" value="<?= $srv['id'] ?>">
                <input type="hidden" name="service_diskon[]" id="service_diskon_<?= $i ?>" value="<?= $srv['diskon'] ?? 0 ?>">
                <input type="hidden" name="service_diskon_tipe[]" id="service_diskon_tipe_<?= $i ?>" value="<?= $srv['diskon_tipe'] ?? '' ?>">
                <?php endforeach; ?>
                
                <?php if (!empty($peserta_ids)): ?>
                    <input type="hidden" name="peserta_ids" value="<?= htmlspecialchars(json_encode($peserta_ids)) ?>">
                <?php endif; ?>
                
                <!-- JUMLAH YANG AKAN DIBAYAR -->
                <div class="total-bayar-container" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); 
                        border-radius: 12px; padding: 20px; margin-bottom: 25px;">
                    <label style="display: block; color: #0369a1; font-weight: 600; margin-bottom: 12px;">
                        <i class="fas fa-money-check-alt"></i> Jumlah yang akan dibayar
                    </label>
                    <div style="position: relative;">
                        <div style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #64748b; font-weight: 600;">
                            Rp
                        </div>
                        <input type="text" name="jumlahBayar" id="jumlahBayar"
                            value="<?= number_format($sisa_tagihan, 0, ',', '.') ?>" 
                            readonly
                            style="width: 100%; padding: 15px 15px 15px 40px; 
                                    font-size: 20px; font-weight: 600; text-align: center;
                                    border: 2px solid #cbd5e1; border-radius: 8px;"
                            data-original="<?= $sisa_tagihan ?>">
                    </div>
                </div>
                
                <!-- METODE PEMBAYARAN -->
                <div class="payment-methods-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h4 style="margin: 0; color: #1e293b;">
                            <i class="fas fa-credit-card"></i> Metode Pembayaran
                        </h4>
                        <button type="button" class="btn-add-method" onclick="addPaymentMethod()" 
                                style="background: #3b82f6; color: white; border: none; padding: 8px 16px; 
                                    border-radius: 6px; cursor: pointer; font-size: 14px;">
                            <i class="fas fa-plus"></i> Tambah Metode
                        </button>
                    </div>
                    
                    <div id="methodsContainer">
                        <!-- Method rows akan ditambahkan dinamis -->
                    </div>
                </div>
                
                <!-- SUMMARY -->
                <div class="summary-container" style="background: #f8fafc; border-radius: 12px; padding: 20px; margin: 25px 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 16px;">
                        <span style="color: #475569;">Total Pembayaran:</span>
                        <strong id="totalMethods" style="color: #3b82f6; font-size: 18px;">Rp 0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: #64748b; font-size: 14px;">
                        <span>Jumlah metode:</span>
                        <span id="jumlahMethods">0 metode</span>
                    </div>
                </div>
                
                <!-- KIRIM INVOICE -->
                <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 10px; padding: 20px; margin: 20px 0;">
                    <h4 style="color: #92400e; margin: 0 0 15px 0; font-size: 16px;">
                        <i class="fas fa-envelope"></i> Kirim Invoice ke Pasien
                    </h4>
                    <div style="display: flex; gap: 20px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <div style="width: 24px; height: 24px; border: 2px solid #d1d5db; border-radius: 6px; 
                                        display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                <i class="fas fa-check" style="color: white; font-size: 12px; display: none;"></i>
                            </div>
                            <input type="checkbox" name="invoice_email" id="invoiceEmail" 
                                style="display: none;" 
                                onchange="toggleCheckbox(this, 'email')">
                            <span style="color: #4b5563;">Email</span>
                        </label>
                        
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <div style="width: 24px; height: 24px; border: 2px solid #d1d5db; border-radius: 6px; 
                                        display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                <i class="fas fa-check" style="color: white; font-size: 12px; display: none;"></i>
                            </div>
                            <input type="checkbox" name="invoice_wa" id="invoiceWa" 
                                style="display: none;"
                                onchange="toggleCheckbox(this, 'wa')">
                            <span style="color: #4b5563;">WhatsApp</span>
                        </label>
                    </div>
                </div>
                
                <!-- ACTION BUTTONS -->
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn-primary" id="btnConfirmPayment" disabled
                            style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
                                color: white; border: none; padding: 16px; border-radius: 10px; 
                                font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                        <i class="fas fa-check"></i> Konfirmasi Pembayaran
                    </button>
                    <button type="button" class="btn-danger" onclick="closeMultiplePayment()"
                            style="background: #ef4444; color: white; border: none; padding: 16px 24px; 
                                border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL DISKON PER ITEM ================= -->
    <div id="popupDiskonItem" class="popup-overlay" style="display:none;">
        <div class="popup-box" style="width: 500px;">
            <div class="popup-header">
                <h2><i class="fas fa-percentage"></i> Tambah Diskon Layanan</h2>
                <button class="popup-close" onclick="closeDiskonItemPopup()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="diskonItemInfo" style="background: #f0f9ff; border-radius: 10px; padding: 15px; margin-bottom: 20px;">
                <div style="font-weight: 600; color: #0369a1; margin-bottom: 5px;">
                    <i class="fas fa-info-circle"></i> Informasi Layanan
                </div>
                <div id="itemName" style="font-size: 16px;"></div>
                <div style="color: #64748b; font-size: 14px;">
                    Harga: <span id="itemHarga" style="font-weight: 600;"></span>
                </div>
            </div>
            
            <form id="formDiskonItem">
                <input type="hidden" id="currentItemID">
                <input type="hidden" id="currentItemIndex">
                <input type="hidden" id="currentItemHarga">
                
                <div style="margin-bottom: 25px;">
                    <div style="font-weight: 600; color: #1e293b; margin-bottom: 12px;">
                        Pilih Jenis Diskon
                    </div>
                    
                    <div class="diskon-type-selector">
                        <div class="type-option active" data-type="persen" onclick="selectDiskonType('persen')">
                            <div class="type-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="type-info">
                                <div class="type-title">Persentase (%)</div>
                                <div class="type-desc">Diskon berdasarkan persentase harga</div>
                            </div>
                            <div class="type-check">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        
                        <div class="type-option" data-type="nilai" onclick="selectDiskonType('nilai')">
                            <div class="type-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="type-info">
                                <div class="type-title">Nilai (Rp)</div>
                                <div class="type-desc">Diskon dengan nilai tetap</div>
                            </div>
                            <div class="type-check">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="diskonPersenSection" class="diskon-input-section active">
                    <label style="display: block; color: #475569; font-weight: 600; margin-bottom: 8px;">
                        Masukkan Persentase Diskon
                    </label>
                    <div style="position: relative;">
                        <input type="number" id="inputDiskonPersen" 
                            min="0" max="100" step="1"
                            placeholder="Contoh: 10"
                            style="width: 100%; padding: 12px 15px; padding-right: 50px;
                                    border: 2px solid #cbd5e1; border-radius: 8px; font-size: 16px;"
                            oninput="hitungDiskonFromPersen()">
                        <div style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); 
                                    color: #64748b; font-weight: 600;">%</div>
                    </div>
                    <div style="font-size: 14px; color: #94a3b8; margin-top: 5px;">
                        Nilai diskon: <span id="nilaiDiskonPersen" style="font-weight: 600; color: #ef4444;">Rp 0</span>
                    </div>
                </div>
                
                <div id="diskonNilaiSection" class="diskon-input-section">
                    <label style="display: block; color: #475569; font-weight: 600; margin-bottom: 8px;">
                        Masukkan Nilai Diskon
                    </label>
                    <div style="position: relative;">
                        <div style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); 
                                    color: #64748b; font-weight: 600;">Rp</div>
                        <input type="number" id="inputDiskonNilai" 
                            min="0" step="1000"
                            placeholder="Contoh: 50000"
                            style="width: 100%; padding: 12px 15px; padding-left: 40px;
                                    border: 2px solid #cbd5e1; border-radius: 8px; font-size: 16px;"
                            oninput="hitungPersenFromDiskon()">
                    </div>
                    <div style="font-size: 14px; color: #94a3b8; margin-top: 5px;">
                        Persentase: <span id="persenDiskonNilai" style="font-weight: 600; color: #ef4444;">0%</span>
                    </div>
                </div>
                
                <div style="background: #f8fafc; border-radius: 10px; padding: 20px; margin: 25px 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: #475569;">Harga Asli:</span>
                        <span id="originalPrice" style="font-weight: 600;">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span style="color: #475569;">Diskon:</span>
                        <span id="appliedDiskon" style="color: #ef4444; font-weight: 600;">- Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 10px; border-top: 2px dashed #e2e8f0;">
                        <span style="color: #1e293b; font-weight: 600;">Harga Setelah Diskon:</span>
                        <span id="finalPrice" style="color: #10b981; font-size: 18px; font-weight: 700;">Rp 0</span>
                    </div>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <button type="button" class="btn-primary" onclick="applyDiskonItem()"
                            style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
                                color: white; border: none; padding: 16px; border-radius: 10px; 
                                font-size: 16px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-check"></i> Terapkan Diskon
                    </button>
                    <button type="button" class="btn-danger" onclick="removeDiskonItem()"
                            style="background: #ef4444; color: white; border: none; padding: 16px 24px; 
                                border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-trash"></i> Hapus Diskon
                    </button>
                    <button type="button" class="btn-secondary" onclick="closeDiskonItemPopup()"
                            style="background: #6b7280; color: white; border: none; padding: 16px 24px; 
                                border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!-- ================= MODAL EDIT DESKRIPSI ================= -->
    <div id="popupEditDeskripsi" class="popup-overlay" style="display:none;">
        <div class="popup-box" style="width: 450px;">
            <div class="popup-header">
                <h2><i class="fas fa-edit"></i> Edit Deskripsi Layanan</h2>
                <button class="popup-close" onclick="closeEditDeskripsi()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div style="background: #f0f9ff; border-radius: 10px; padding: 15px; margin-bottom: 20px;">
                <div style="font-weight: 600; color: #0369a1; margin-bottom: 5px;">
                    <i class="fas fa-info-circle"></i> Pilih Nama Layanan
                </div>
                <div style="color: #64748b; font-size: 14px;">
                    Nama layanan saat ini: <span id="currentDeskripsiLabel" style="font-weight:600; color:#1e293b;"></span>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #475569; font-weight: 600; margin-bottom: 8px;">
                    Pilih Layanan Baru
                </label>

                <!-- Search Box -->
                <input type="text" id="searchDeskripsi"
                    placeholder="🔍 Ketik nama layanan..."
                    oninput="filterDeskripsiOptions()"
                    style="width: 100%; padding: 10px 15px; margin-bottom: 8px;
                        border: 2px solid #cbd5e1; border-radius: 8px; font-size: 14px;
                        box-sizing: border-box;">

                <select id="selectDeskripsi" size="6"
                    style="width: 100%; padding: 8px;
                        border: 2px solid #cbd5e1; border-radius: 8px; font-size: 15px;
                        background: white; box-sizing: border-box; height: 180px;">
                    <option value="">-- Pilih Layanan --</option>
                    <?php 
                    $current_tipe = '';
                    foreach ($services_list as $svc): 
                        // Tambah separator per tipe
                        if ($svc['tipe'] !== $current_tipe):
                            if ($current_tipe !== '') echo '</optgroup>';
                            $current_tipe = $svc['tipe'];
                            $label_tipe = $svc['tipe'] === 'pelayanan' ? 'Pelayanan' : 'Paket';
                            echo "<optgroup label='$label_tipe'>";
                        endif;
                    ?>
                        <option value="<?= htmlspecialchars($svc['nama_layanan']) ?>">
                            <?= htmlspecialchars($svc['nama_layanan']) ?> 
                            (Rp <?= number_format($svc['harga'], 0, ',', '.') ?>)
                        </option>
                    <?php endforeach; ?>
                    <?php if ($current_tipe !== '') echo '</optgroup>'; ?>
                </select>
            </div>

            <input type="hidden" id="editDeskripsiID">
            <input type="hidden" id="editDeskripsiIndex">

            <div style="display: flex; gap: 15px;">
                <button type="button"
                    onclick="saveEditDeskripsi()"
                    style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        color: white; border: none; padding: 14px; border-radius: 10px;
                        font-size: 16px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-check"></i> Simpan
                </button>
                <button type="button"
                    onclick="closeEditDeskripsi()"
                    style="background: #6b7280; color: white; border: none; padding: 14px 24px;
                        border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-times"></i> Batal
                </button>
            </div>
        </div>
    </div>
    <script>
    window.paymentData = {
        totalTagihan: <?php echo $total_tagihan_final; ?>,
        sudahDibayar: <?php echo $total_sudah_dibayar; ?>,
        sisaTagihan: <?php echo $sisa_tagihan; ?>,
        subtotal: <?php echo $subtotal; ?>,
        totalDiskonItem: <?php echo $total_diskon_item; ?>,
        diskonItems: <?php echo json_encode($data_services); ?>
    };
    </script>
    <script>
    // ==========================================
// PATCH UNTUK PEMBAYARAN.JS
// Tambahkan kode ini di AKHIR file pembayaran.js
// SEBELUM tag 
// ==========================================

// ✅ FIX 1: Update fungsi applyDiskonItem untuk auto-save
function applyDiskonItem() {
    let tipe = currentDiskonType;
    let diskon = 0;
    let id = currentItemID;
    
    if (tipe === 'persen') {
        const persen = parseFloat(document.getElementById('inputDiskonPersen').value) || 0;
        diskon = Math.round(currentItemHarga * persen / 100);
    } else {
        diskon = parseFloat(document.getElementById('inputDiskonNilai').value) || 0;
    }
    
    // Validasi
    if (diskon > currentItemHarga) {
        alert('Diskon tidak boleh lebih besar dari harga item');
        return;
    }
    
    // Simpan ke diskonItems
    diskonItems[currentItemIndex] = {
        id: currentItemID,
        harga: currentItemHarga,
        diskon: diskon,
        tipe: tipe
    };
    
    // Update UI
    updateDiskonItemUI(id, currentItemIndex, diskon, tipe);
    
    // ✅ UPDATE TO DATABASE VIA AJAX
    updateDiskonToDatabase(id, diskon, tipe);
    
    // Update total
    updateTotalSummary();
    
    // Close modal
    closeDiskonItemPopup();
    
    // Success message
    showToast('Diskon berhasil diterapkan dan tersimpan!', 'success');
}

// ✅ FIX 2: Perbaiki fungsi updateDiskonToDatabase
function updateDiskonToDatabase(id, diskon, tipe) {
    console.log('🔄 Updating diskon to database...');
    console.log('ID:', id, 'Diskon:', diskon, 'Tipe:', tipe);

    const formData = new FormData();
    formData.append('id', id);
    formData.append('diskon', diskon);
    formData.append('tipe_diskon', tipe);
    formData.append('action', 'update_diskon');

    return fetch('update_diskon.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Response:', data);
        
        if (data.success) {
            console.log('✅ Diskon saved to database');
            console.log('Updated data:', data.data);
            return true;
        } else {
            console.error('❌ Failed:', data.message);
            showToast('Gagal menyimpan: ' + data.message, 'error');
            return false;
        }
    })
    .catch(error => {
        console.error('❌ Fetch error:', error);
        showToast('Error: ' + error.message, 'error');
        return false;
    });
}

// ✅ FIX 3: Update fungsi updateDiskonItemUI
function updateDiskonItemUI(id_item, index, diskon, tipe) {
    const diskonCell = document.getElementById(`diskon-cell-${index}`);
    const totalCell = document.getElementById(`total-item-${index}`);
    
    // ✅ Update ALL hidden inputs dengan ID yang sama
    // Cari semua input dengan name yang match
    const allServiceIdInputs = document.querySelectorAll(`input[name="service_id[]"]`);
    const allServiceDiskonInputs = document.querySelectorAll(`input[name="service_diskon[]"]`);
    const allServiceTipeInputs = document.querySelectorAll(`input[name="service_diskon_tipe[]"]`);
    
    // Update berdasarkan index
    if (allServiceDiskonInputs[index]) {
        allServiceDiskonInputs[index].value = diskon;
        console.log(`✅ Updated service_diskon[${index}] = ${diskon}`);
    }
    
    if (allServiceTipeInputs[index]) {
        allServiceTipeInputs[index].value = tipe;
        console.log(`✅ Updated service_diskon_tipe[${index}] = ${tipe}`);
    }
    
    // Update UI display
    if (diskon > 0) {
        const persen = Math.round((diskon / currentItemHarga) * 100);
        
        let diskonHTML = `
            <div class="diskon-applied">
                <div style="display: flex; align-items: center; gap: 8px;">
        `;
        
        if (tipe === 'persen') {
            diskonHTML += `
                <span class="diskon-badge persen">${persen}%</span>
                <span style="font-size: 12px; color: #64748b;">
                    (Rp ${formatNumber(diskon)})
                </span>
            `;
        } else {
            diskonHTML += `
                <span class="diskon-badge nilai">- Rp ${formatNumber(diskon)}</span>
            `;
        }
        
        diskonHTML += `
                </div>
            </div>
        `;
        
        diskonCell.innerHTML = `
            ${diskonHTML}
            <button type="button"
                class="btn-edit-diskon"
                onclick="openDiskonItem(${id_item}, ${index}, ${currentItemHarga}, '${tipe}', ${diskon}, '')"
                title="Edit Diskon">
                <i class="fas fa-edit"></i>
            </button>
        `;
    } else {
        diskonCell.innerHTML = `
            <span class="no-diskon">-</span>
            <button type="button"
                class="btn-edit-diskon"
                onclick="openDiskonItem(${id_item}, ${index}, ${currentItemHarga}, '', 0, '')"
                title="Tambah Diskon">
                <i class="fas fa-edit"></i>
            </button>
        `;
    }
    
    // Update total per item
    const totalPerItem = currentItemHarga - diskon;
    if (totalCell) {
        totalCell.textContent = `Rp ${formatNumber(totalPerItem)}`;
    }
}

// ✅ FIX 4: Override form submit untuk sync data
document.addEventListener('DOMContentLoaded', function() {
    const formMultiple = document.getElementById('formMultiplePayment');
    
    if (formMultiple) {
        // Remove existing listener dan add new one
        const newForm = formMultiple.cloneNode(true);
        formMultiple.parentNode.replaceChild(newForm, formMultiple);
        
        newForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('=== FORM SUBMIT - SYNCING DISKON DATA ===');
            
            // ✅ SYNC diskon dari diskonItems ke hidden inputs
            Object.keys(diskonItems).forEach((index) => {
                const item = diskonItems[index];
                const idx = parseInt(index);
                
                // Get all inputs
                const allDiskonInputs = newForm.querySelectorAll('input[name="service_diskon[]"]');
                const allTipeInputs = newForm.querySelectorAll('input[name="service_diskon_tipe[]"]');
                
                if (allDiskonInputs[idx]) {
                    allDiskonInputs[idx].value = item.diskon || 0;
                    console.log(`✅ Synced service_diskon[${idx}] = ${item.diskon}`);
                }
                
                if (allTipeInputs[idx]) {
                    allTipeInputs[idx].value = item.tipe || '';
                    console.log(`✅ Synced service_diskon_tipe[${idx}] = ${item.tipe}`);
                }
            });
            
            // Log final data
            console.log('=== FINAL FORM DATA ===');
            const formData = new FormData(this);
            for (let pair of formData.entries()) {
                if (pair[0].includes('service')) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
            }
            
            // Validasi
            if (paymentMethods.length === 0) {
                alert('Tambahkan minimal satu metode pembayaran');
                return false;
            }
            
            const jumlahBayar = parseFloat(document.getElementById('jumlahBayar').value) || 0;
            const totalMethods = paymentMethods.reduce((sum, m) => sum + m.amount, 0);
            
            if (totalMethods > jumlahBayar) {
                alert(`Total metode (${formatRupiah(totalMethods)}) melebihi jumlah bayar (${formatRupiah(jumlahBayar)})`);
                return false;
            }
            
            if (totalMethods === 0) {
                alert('Total pembayaran tidak boleh Rp 0');
                return false;
            }
            
            // Konfirmasi
            let konfirmasiText = `Konfirmasi Pembayaran\n\n`;
            konfirmasiText += `Jumlah: ${formatRupiah(totalMethods)}\n`;
            konfirmasiText += `Metode: ${paymentMethods.map(m => m.metode.toUpperCase()).join(', ')}\n\n`;
            
            if (totalMethods === jumlahBayar) {
                konfirmasiText += `Status: LUNAS ✓\n\n`;
            } else {
                const sisaHutang = jumlahBayar - totalMethods;
                konfirmasiText += `Status: PARTIAL\n`;
                konfirmasiText += `Sisa: ${formatRupiah(sisaHutang)}\n\n`;
            }
            
            konfirmasiText += `Lanjutkan?`;
            
            if (!confirm(konfirmasiText)) {
                return false;
            }
            
            // Add payment methods JSON
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'payment_methods';
            input.value = JSON.stringify(paymentMethods);
            this.appendChild(input);
            
            // Show loading
            const btnConfirm = document.getElementById('btnConfirmPayment');
            if (btnConfirm) {
                btnConfirm.disabled = true;
                btnConfirm.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            }
            
            // Submit
            console.log('✅ Submitting form...');
            this.submit();
        });
        
        console.log('✅ Form submit handler installed');
    }
});

// ✅ EDIT DESKRIPSI
function openEditDeskripsi(id, index, namaLayanan) {
    document.getElementById('editDeskripsiID').value = id;
    document.getElementById('editDeskripsiIndex').value = index;
    document.getElementById('currentDeskripsiLabel').textContent = namaLayanan;
    // Reset search saat buka modal
    document.getElementById('searchDeskripsi').value = '';
    filterDeskripsiOptions();

    // Set dropdown ke nilai sekarang jika ada
    const select = document.getElementById('selectDeskripsi');
    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].value === namaLayanan) {
            select.selectedIndex = i;
            break;
        }
    }

    document.getElementById('popupEditDeskripsi').style.display = 'flex';
}

function closeEditDeskripsi() {
    document.getElementById('popupEditDeskripsi').style.display = 'none';
}

function saveEditDeskripsi() {
    const id = document.getElementById('editDeskripsiID').value;
    const index = document.getElementById('editDeskripsiIndex').value;
    const nama = document.getElementById('selectDeskripsi').value;

    if (!nama) {
        alert('Pilih layanan terlebih dahulu');
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('nama_layanan', nama);
    formData.append('action', 'update_deskripsi');

    fetch('update_deskripsi.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update nama di tabel
            document.getElementById(`deskripsi-text-${index}`).textContent = nama;

            // Inisialisasi variabel untuk update paymentData
            let hargaLama = 0;
            let hargaBaru = data.harga || 0;
            let rowDitemukan = false;

            // Update harga di tabel jika ada
            if (data.harga) {
                const row = document.querySelector(`tr[data-index="${index}"]`);
                if (row) {
                    rowDitemukan = true;
                    
                    // AMBIL HARGA LAMA SEBELUM DIUPDATE
                    hargaLama = parseFloat(row.getAttribute('data-harga-lama')) || 
                               parseFloat(row.getAttribute('data-harga')) || 0;
                    
                    // Update atribut row
                    row.setAttribute('data-harga', hargaBaru);
                    row.setAttribute('data-harga-lama', hargaBaru);

                    // Update harga cell
                    const hargaCell = row.querySelector('.harga-item');
                    if (hargaCell) {
                        hargaCell.textContent = 'Rp ' + formatNumber(hargaBaru);
                    }
                    
                    // Reset total cell
                    const totalCell = document.getElementById(`total-item-${index}`);
                    if (totalCell) {
                        totalCell.textContent = 'Rp ' + formatNumber(hargaBaru);
                    }
                    
                    // Reset diskon cell
                    const diskonCell = document.getElementById(`diskon-cell-${index}`);
                    if (diskonCell) {
                        diskonCell.innerHTML = `
                            <span class="no-diskon">-</span>
                            <button type="button" class="btn-edit-diskon"
                                onclick="openDiskonItem(${id}, ${index}, ${hargaBaru}, '', 0, '')"
                                title="Tambah Diskon">
                                <i class="fas fa-edit"></i>
                            </button>
                        `;
                    }

                    // Reset hidden inputs untuk item ini
                    const allServiceDiskonInputs = document.querySelectorAll('input[name="service_diskon[]"]');
                    const allServiceTipeInputs = document.querySelectorAll('input[name="service_diskon_tipe[]"]');
                    
                    if (allServiceDiskonInputs[index]) {
                        allServiceDiskonInputs[index].value = 0;
                    }
                    if (allServiceTipeInputs[index]) {
                        allServiceTipeInputs[index].value = '';
                    }

                    // Reset diskonItems untuk index ini
                    if (window.diskonItems && window.diskonItems[index]) {
                        delete window.diskonItems[index];
                    }
                }
            }

            // UPDATE PAYMENT DATA (hanya jika row ditemukan)
            if (rowDitemukan && window.paymentData) {
                // Kurangi subtotal dengan harga lama, tambah dengan harga baru
                window.paymentData.subtotal = window.paymentData.subtotal - hargaLama + hargaBaru;
                window.paymentData.totalTagihan = window.paymentData.subtotal; // karena diskon item direset
                window.paymentData.sisaTagihan = window.paymentData.totalTagihan - window.paymentData.sudahDibayar;
                window.paymentData.totalDiskonItem = 0; // reset karena diskon dihapus

                // Update hidden inputs di form payment
                const subtotalInput = document.getElementById('subtotalInput');
                const totalTagihanInput = document.getElementById('totalTagihanInput');
                const sisaTagihanInput = document.getElementById('sisaTagihanInput');
                
                if (subtotalInput) subtotalInput.value = window.paymentData.subtotal;
                if (totalTagihanInput) totalTagihanInput.value = window.paymentData.totalTagihan;
                if (sisaTagihanInput) sisaTagihanInput.value = window.paymentData.sisaTagihan;

                // Update summary display
                const subtotalDisplay = document.getElementById('subtotalDisplay');
                const totalTagihan = document.getElementById('totalTagihan');
                const sisaTagihan = document.getElementById('sisaTagihan');
                const totalTagihanSummary = document.getElementById('totalTagihanSummary');
                const sisaTagihanSummary = document.getElementById('sisaTagihanSummary');
                const totalDiskonItems = document.getElementById('total-diskon-items');

                if (subtotalDisplay) subtotalDisplay.textContent = 'Rp ' + formatNumber(window.paymentData.subtotal);
                if (totalTagihan) totalTagihan.textContent = 'Rp ' + formatNumber(window.paymentData.totalTagihan);
                if (sisaTagihan) sisaTagihan.textContent = 'Rp ' + formatNumber(window.paymentData.sisaTagihan);
                if (totalTagihanSummary) totalTagihanSummary.textContent = 'Rp ' + formatNumber(window.paymentData.totalTagihan);
                if (sisaTagihanSummary) sisaTagihanSummary.textContent = 'Rp ' + formatNumber(window.paymentData.sisaTagihan);
                if (totalDiskonItems) totalDiskonItems.textContent = '- Rp 0';
            }

            // ✅ PANGGIL UPDATE TOTAL SUMMARY
            if (typeof updateTotalSummary === 'function') {
                updateTotalSummary();
            }

            closeEditDeskripsi();
            
            // ✅ LANGSUNG RELOAD TANPA TOAST
            setTimeout(() => {
                location.reload();
            }, 500);
            
        } else {
            alert('Gagal: ' + data.message);
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}

function filterDeskripsiOptions() {
    const keyword = document.getElementById('searchDeskripsi').value.toLowerCase();
    const select = document.getElementById('selectDeskripsi');
    const options = select.querySelectorAll('option');

    options.forEach(opt => {
        if (opt.value === '') return; // skip placeholder
        const text = opt.textContent.toLowerCase();
        opt.style.display = text.includes(keyword) ? '' : 'none';
    });

    // Tampilkan/sembunyikan optgroup jika semua anaknya hidden
    const optgroups = select.querySelectorAll('optgroup');
    optgroups.forEach(group => {
        const visibleOptions = [...group.querySelectorAll('option')]
            .filter(o => o.style.display !== 'none');
        group.style.display = visibleOptions.length > 0 ? '' : 'none';
    });
}

console.log('✅ PEMBAYARAN.JS PATCH LOADED');

    function toggleDropdownCetak() {
        const menu = document.getElementById('dropdownCetakMenu');
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
    }

    // Tutup dropdown jika klik di luar
    document.addEventListener('click', function(e) {
        const dropdown = document.querySelector('.dropdown-cetak');
        if (dropdown && !dropdown.contains(e.target)) {
            document.getElementById('dropdownCetakMenu').style.display = 'none';
        }
    });
    </script>
    <script src="system/admin/js/pembayaran.js"></script>
    <script src="system/admin/js/sidebar-toggle.js"></script>
</body>
</html>