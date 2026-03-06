<?php 
include "config.php";
require_once "system/vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

/* ================= AMBIL BOOKING ID ================= */
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id == 0) {
    die("Booking ID tidak valid. Silakan kembali ke halaman pembayaran.");
}

/* ================= CEK JENIS BOOKING ================= */
$sql_jenis = "SELECT parent_id, service_type, tanggal_booking FROM bookings WHERE id = ?";
$stmt_jenis = $conn->prepare($sql_jenis);
$stmt_jenis->bind_param("i", $booking_id);
$stmt_jenis->execute();
$result_jenis = $stmt_jenis->get_result();
$jenis_data = $result_jenis->fetch_assoc();

if (!$jenis_data) {
    die("Data booking tidak ditemukan!");
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

// Ambil tanggal pesanan
$sql_tgl_pesanan = "SELECT created_at FROM bookings WHERE id = ?";
$stmt_tgl = $conn->prepare($sql_tgl_pesanan);
$stmt_tgl->bind_param("i", $parent_booking_id);
$stmt_tgl->execute();
$result_tgl = $stmt_tgl->get_result();
$tgl_pesanan_row = $result_tgl->fetch_assoc();
$tgl_pesanan = $tgl_pesanan_row ? $tgl_pesanan_row['created_at'] : date('Y-m-d H:i:s');

$jatuh_tempo = hitungJatuhTempo($service_type, $tgl_pesanan, $tanggal_layanan);

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
    die("Data booking tidak ditemukan!");
}

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

while ($row = $peserta_result->fetch_assoc()) {
    $semua_peserta[] = $row;
    $jumlah_peserta++;
}

/* ================= AMBIL NO HP UTAMA ================= */
$sql_phone = "SELECT phone FROM patient_phones 
              WHERE patient_id = ? 
              ORDER BY is_primary DESC 
              LIMIT 1";
$stmt_ph = $conn->prepare($sql_phone);
$stmt_ph->bind_param("i", $booking['patient_id']);
$stmt_ph->execute();
$phone = $stmt_ph->get_result()->fetch_assoc()['phone'] ?? '-';

/* ================= AMBIL ALAMAT UTAMA ================= */
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

$riwayat_data = [];
$total_sudah_dibayar = 0;
$total_diskon_global = 0;

while ($row = $result_riwayat->fetch_assoc()) {
    $riwayat_data[] = $row;
    if ($row['status'] == 'paid' || $row['status'] == 'partial') {
        $total_sudah_dibayar += $row['amount_paid'];
    }
    $total_diskon_global += floatval($row['diskon'] ?? 0);
}

// Ambil payment terakhir untuk info di faktur
$payment_terakhir = !empty($riwayat_data) ? $riwayat_data[0] : null;

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

$subtotal = 0;
$total_tagihan = 0;
$data_services = [];
$total_diskon_item = 0;

while ($row = $result_services->fetch_assoc()) {
    $row['jumlah'] = 1;
    $diskon = $row['diskon'] ?? 0;
    $row['total'] = $row['harga'] - $diskon;
    
    $subtotal += $row['harga'];
    $total_tagihan += $row['total'];
    $total_diskon_item += $diskon;
    $data_services[] = $row;
}

// 🔥 PERBAIKAN: Hitung total tagihan final dengan diskon global
$total_tagihan_final = $total_tagihan - $total_diskon_global;
if ($total_tagihan_final < 0) $total_tagihan_final = 0;

$sisa_tagihan = $total_tagihan_final - $total_sudah_dibayar;
if ($sisa_tagihan < 0) $sisa_tagihan = 0;

/* ================= DISKON TOTAL (jika ada di payment terakhir) ================= */
$diskon_total = $total_diskon_global; // Gunakan total diskon global

/* ================= FORMAT TANGGAL ================= */
function formatTanggalIndo($date) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $parts = explode('-', $date);
    return $parts[2] . ' ' . $bulan[(int)$parts[1]] . ' ' . $parts[0];
}

$tanggal_pelayanan = formatTanggalIndo($booking['tanggal_booking']);
$tanggal_faktur = $payment_terakhir ? formatTanggalIndo(date('Y-m-d', strtotime($payment_terakhir['created_at']))) : formatTanggalIndo(date('Y-m-d'));
$jatuh_tempo_format = formatTanggalIndo($jatuh_tempo);

/* ================= METODE PEMBAYARAN ================= */
$metode_bayar = 'TUNAI';
if ($payment_terakhir) {
    $metode_raw = $payment_terakhir['metode_detail'] ?? $payment_terakhir['metode'];
    $metode_bayar = strtoupper($metode_raw);
}

// Format metode bayar lebih readable
if (strpos($metode_bayar, 'TUNAI') !== false) {
    $payment_text = 'TUNAI';
} else if (strpos($metode_bayar, 'TRANSFER') !== false) {
    $payment_text = 'TRANSFER BANK';
} else if (strpos($metode_bayar, 'QRIS') !== false) {
    $payment_text = 'QRIS';
} else if (strpos($metode_bayar, 'DEBIT') !== false) {
    $payment_text = 'KARTU DEBIT';
} else if (strpos($metode_bayar, 'KREDIT') !== false || strpos($metode_bayar, 'CREDIT') !== false) {
    $payment_text = 'KARTU KREDIT';
} else {
    $payment_text = $metode_bayar;
}

/* ================= LOGO ================= */
$logo_path = __DIR__ . '/../../img/vaksinin-logo-orange-no-bg.png';
$logo_src = '';

if (file_exists($logo_path)) {
    $logo_src = 'file:///' . str_replace('\\', '/', realpath($logo_path));
}

/* ================= GENERATE HTML ================= */
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 20mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            background: #f5a623;
            padding: 15px 20px;
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
        }

        .header-left img {
            height: 40px;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 50%;
        }

        .header-right h1 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .info-section {
            padding: 15px 20px;
            background: #f9f9f9;
            display: table;
            width: 100%;
        }

        .info-left {
            display: table-cell;
            vertical-align: top;
            width: 60%;
        }

        .info-left p {
            margin: 2px 0;
            font-size: 10px;
            line-height: 1.4;
        }

        .info-right {
            display: table-cell;
            vertical-align: top;
            width: 40%;
        }

        .info-table {
            width: 100%;
            font-size: 11px;
        }

        .info-table td {
            padding: 3px 0;
        }

        .info-table td:nth-child(2) {
            width: 15px;
            text-align: center;
        }

        .kepada-section {
            padding: 15px 20px;
        }

        .kepada-section p {
            margin: 4px 0;
            font-size: 11px;
        }

        .pembayaran-section {
            padding: 15px 25px;
            border-top: 1px solid #ddd;
        }

        .pembayaran-section p {
            margin: 4px 0;
            font-size: 11px;
        }

        .layanan-table {
            width: calc(100% - 50px);
            margin: 15px 25px;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .layanan-table th {
            padding: 10px 8px;
            border: 1px solid #333;
            font-weight: 600;
            text-align: left;
            background: #f5f5f5;
            font-size: 11px;
        }

        .layanan-table td {
            padding: 10px 8px;
            border: 1px solid #333;
            font-size: 11px;
        }

        .empty-row {
            height: 80px;
        }

        .footer-section {
            padding: 15px 20px;
            display: table;
            width: 100%;
        }

        .keterangan {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .keterangan p {
            margin: 4px 0;
            font-size: 11px;
        }

        .total-section {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .total-table {
            width: 100%;
            font-size: 12px;
            float: right;
            max-width: 300px;
        }

        .total-table td {
            padding: 5px 0;
        }

        .total-table td:nth-child(2) {
            width: 15px;
            text-align: center;
        }

        .total-table td:last-child {
            text-align: right;
        }

        .total-row td {
            border-top: 2px solid #333;
            padding-top: 8px !important;
            font-weight: bold;
            font-size: 14px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
            margin-top: 5px;
        }
        
        .status-lunas {
            background: #10b981;
            color: white;
        }
        
        .status-sebagian {
            background: #f59e0b;
            color: white;
        }
        
        .status-belum {
            background: #ef4444;
            color: white;
        }
        
        .payment-history {
            padding: 15px 25px;
            background: #f9f9f9;
            margin: 10px 25px;
            border-radius: 5px;
        }
        
        .payment-history h3 {
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .payment-item {
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <img src="' . $logo_src . '" alt="Vaksinin">
        </div>
        <div class="header-right">
            <h1>Faktur Pembayaran</h1>
        </div>
    </div>

    <div class="info-section">
        <div class="info-left">
            <p>Ruko Sentra Menteng Blok MN 88 I</p>
            <p>Pondok Jaya, Pondok Aren, Tangerang Selatan, Banten 15220</p>
            <p>+62 821 3737 2757 / (021) 2221 4342</p>
            <p>vaksinin.id@gmail.com / hellovaksinin@gmail.com</p>
            <p>vaksinin.id</p>
        </div>
        <div class="info-right">
            <table class="info-table">
                <tr>
                    <td><strong>No. Antrian</strong></td>
                    <td>:</td>
                    <td>' . htmlspecialchars($booking['nomor_antrian']) . '</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Faktur</strong></td>
                    <td>:</td>
                    <td>' . $tanggal_faktur . '</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Pelayanan</strong></td>
                    <td>:</td>
                    <td>' . $tanggal_pelayanan . '</td>
                </tr>
                <tr>
                    <td><strong>Jatuh Tempo</strong></td>
                    <td>:</td>
                    <td>' . $jatuh_tempo_format . '</td>
                </tr>
                <tr>
                    <td><strong>Jenis Layanan</strong></td>
                    <td>:</td>
                    <td>' . htmlspecialchars($service_type) . '</td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td>:</td>
                    <td>';

// Status badge
if ($sisa_tagihan <= 0) {
    $html .= '<span class="status-badge status-lunas">LUNAS</span>';
} elseif ($total_sudah_dibayar > 0) {
    $percentage = $total_tagihan > 0 ? round(($total_sudah_dibayar / $total_tagihan) * 100) : 0;
    $html .= '<span class="status-badge status-sebagian">SEBAGIAN (' . $percentage . '%)</span>';
} else {
    $html .= '<span class="status-badge status-belum">BELUM BAYAR</span>';
}

$html .= '
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="kepada-section">
        <p><strong>Kepada:</strong></p>
        <p>' . htmlspecialchars($booking['nama_lengkap']) . '</p>
        <p>Telp: ' . htmlspecialchars($phone) . '</p>';

if ($address) {
    $html .= '<p>' . htmlspecialchars($address['alamat']) . ', ' . 
             htmlspecialchars($address['kota']) . ', ' . 
             htmlspecialchars($address['provinsi']) . '</p>';
}

$html .= '
    </div>';

// Info jumlah peserta
if ($jumlah_peserta > 1) {
    $html .= '
    <div style="padding: 0 25px 10px 25px;">
        <p><strong>Jumlah Peserta:</strong> ' . $jumlah_peserta . ' orang</p>
    </div>';
}

// Riwayat pembayaran (jika ada lebih dari 1 payment)
if (count($riwayat_data) > 0) {
    $html .= '
    <div class="payment-history">
        <h3>Riwayat Pembayaran:</h3>';
    
    foreach ($riwayat_data as $idx => $riwayat) {
        $html .= '
        <div class="payment-item">
            <strong>' . ($idx + 1) . '. ' . date('d M Y H:i', strtotime($riwayat['created_at'])) . '</strong> - 
            ' . strtoupper($riwayat['metode_detail'] ?? $riwayat['metode']) . ' - 
            Rp. ' . number_format($riwayat['amount_paid'], 0, ',', '.') . ' 
            <em>(' . strtoupper($riwayat['status']) . ')</em>
        </div>';
    }
    
    $html .= '
    </div>';
}

$html .= '
    <table class="layanan-table">
        <thead>
            <tr>
                <th style="width: 40px;">No.</th>';

if ($jumlah_peserta > 1) {
    $html .= '<th>Peserta</th>';
}

$html .= '
                <th>Deskripsi Layanan</th>
                <th style="width: 60px;">Jml</th>
                <th style="width: 100px;">Harga</th>
                <th style="width: 100px;">Diskon</th>
                <th style="width: 120px;">Total</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
foreach ($data_services as $srv) {
    $harga = $srv['harga'];
    $diskon_item = $srv['diskon'] ?? 0;
    $total_per_item = $harga - $diskon_item;
    $diskon_tipe = $srv['diskon_tipe'] ?? '';
    $diskon_persen = $diskon_item > 0 ? round(($diskon_item / $harga) * 100) : 0;
    
    $peserta_nama = isset($srv['nama_lengkap']) ? $srv['nama_lengkap'] : $booking['nama_lengkap'];
    
    $html .= '
            <tr>
                <td style="text-align: center;">' . $no++ . '</td>';
    
    if ($jumlah_peserta > 1) {
        $html .= '<td>' . htmlspecialchars($peserta_nama) . '</td>';
    }
    
    $html .= '
                <td>' . htmlspecialchars($srv['nama_layanan']) . '</td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;">Rp. ' . number_format($harga, 0, ',', '.') . '</td>
                <td style="text-align: right;">';
    
    if ($diskon_item > 0) {
        if ($diskon_tipe === 'persen') {
            $html .= $diskon_persen . '% (Rp. ' . number_format($diskon_item, 0, ',', '.') . ')';
        } else {
            $html .= 'Rp. ' . number_format($diskon_item, 0, ',', '.');
        }
    } else {
        $html .= '-';
    }
    
    $html .= '
                </td>
                <td style="text-align: right;">Rp. ' . number_format($total_per_item, 0, ',', '.') . '</td>
            </tr>';
}

// Baris kosong
$html .= '
            <tr class="empty-row">
                <td colspan="' . ($jumlah_peserta > 1 ? 7 : 6) . '">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-section">
        <div class="keterangan">
            <p><strong>Keterangan:</strong></p>
            <p>Terima kasih telah menggunakan layanan Vaksinin.</p>';

if ($sisa_tagihan > 0) {
    $html .= '
            <p style="margin-top: 10px; color: #f59e0b; font-weight: 600;">
                * Masih ada sisa tagihan sebesar Rp. ' . number_format($sisa_tagihan, 0, ',', '.') . '
            </p>
            <p>Mohon segera melunasi sebelum ' . $jatuh_tempo_format . '</p>';
}

$html .= '
            <p style="margin-top: 30px;">
                <strong>Staf Administrasi,</strong><br>
                <br><br><br>
                _________________________
            </p>
        </div>
        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td>:</td>
                    <td>Rp. ' . number_format($subtotal, 0, ',', '.') . '</td>
                </tr>
                <tr>
                    <td><strong>Diskon Item</strong></td>
                    <td>:</td>
                    <td>- Rp. ' . number_format($total_diskon_item, 0, ',', '.') . '</td>
                </tr>';

if ($diskon_total > 0) {
    $html .= '
                <tr>
                    <td><strong>Diskon Tambahan</strong></td>
                    <td>:</td>
                    <td>- Rp. ' . number_format($diskon_total, 0, ',', '.') . '</td>
                </tr>';
}

$html .= '
                <tr>
                    <td><strong>Total Tagihan</strong></td>
                    <td>:</td>
                    <td>Rp. ' . number_format($total_tagihan_final, 0, ',', '.') . '</td>
                </tr>
                <tr>
                    <td><strong>Sudah Dibayar</strong></td>
                    <td>:</td>
                    <td>Rp. ' . number_format($total_sudah_dibayar, 0, ',', '.') . '</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Sisa Tagihan</strong></td>
                    <td>:</td>
                    <td><strong>Rp. ' . number_format($sisa_tagihan, 0, ',', '.') . '</strong></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div style="padding: 20px; text-align: center; font-size: 10px; color: #666; border-top: 1px solid #ddd; margin-top: 20px;">
        <p>Faktur ini dicetak secara otomatis pada ' . date('d/m/Y H:i:s') . '</p>
        <p>Simpan faktur ini sebagai bukti pembayaran yang sah.</p>
        <p style="margin-top: 5px;">Untuk pertanyaan, hubungi: vaksinin.id@gmail.com atau +62 821 3737 2757</p>
    </div>

</body>
</html>';

/* ================= GENERATE PDF ================= */
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* ================= SIMPAN FILE (OPTIONAL) ================= */
$savePath = __DIR__ . '/../uploads/invoice';
if (!is_dir($savePath)) {
    mkdir($savePath, 0777, true);
}

$filename = 'Faktur_' . $booking['nomor_antrian'] . '_' . date('Ymd_His') . '.pdf';
file_put_contents($savePath . '/' . $filename, $dompdf->output());

/* ================= OUTPUT PDF ================= */
$dompdf->stream($filename, [
    "Attachment" => false // false = tampilkan di browser, true = download
]);

exit;