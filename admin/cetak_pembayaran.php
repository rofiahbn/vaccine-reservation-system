<?php
session_start();
include "../config.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id == 0) {
    die("Booking ID tidak valid");
}

/* Ambil data booking + pasien */
$sql = "SELECT b.*, 
               p.nama_lengkap, 
               p.no_rekam_medis,
               p.id as patient_id
        FROM bookings b 
        JOIN patients p ON b.patient_id = p.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

/* Ambil data pembayaran */
$sql_pay = "SELECT * FROM payments 
            WHERE booking_id = ? 
            AND status = 'paid' 
            ORDER BY id DESC 
            LIMIT 1";
$stmt_pay = $conn->prepare($sql_pay);
$stmt_pay->bind_param("i", $booking_id);
$stmt_pay->execute();
$payment = $stmt_pay->get_result()->fetch_assoc();

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

/* Ambil layanan */
$sql_services = "SELECT * FROM booking_services WHERE booking_id = ?";
$stmt_s = $conn->prepare($sql_services);
$stmt_s->bind_param("i", $booking_id);
$stmt_s->execute();
$services = $stmt_s->get_result();

$data_services = [];
while ($row = $services->fetch_assoc()) {
    $data_services[] = $row;
}

// Format tanggal
function formatTanggalIndo($date) {
    $bulan = [
        1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    $parts = explode('-', $date);
    return $parts[2] . ' ' . $bulan[(int)$parts[1]] . ' ' . $parts[0];
}

$tanggal_pelayanan = formatTanggalIndo($booking['tanggal_booking']);
$tanggal_payment = $payment ? formatTanggalIndo(date('Y-m-d', strtotime($payment['created_at']))) : formatTanggalIndo(date('Y-m-d'));

// Hitung jatuh tempo (misal 7 hari dari tanggal)
$jatuh_tempo_date = date('Y-m-d', strtotime($booking['tanggal_booking'] . ' +7 days'));
$jatuh_tempo = formatTanggalIndo($jatuh_tempo_date);

// Metode pembayaran
$metode_bayar = $payment ? strtoupper($payment['metode']) : 'TUNAI';
if ($metode_bayar == 'TUNAI') {
    $payment_text = 'TUNAI';
} else if ($metode_bayar == 'TRANSFER') {
    $payment_text = 'BCA - PT Sinar Kesehatan Jaya Sejahtera - 229 7777 111';
} else if ($metode_bayar == 'QRIS') {
    $payment_text = 'QRIS';
}

// Hitung total
$subtotal = 0;
$total_diskon = 0;
$grand_total = 0;

foreach ($data_services as $srv) {
    $subtotal += $srv['harga'];
    $total_diskon += $srv['diskon'];
    $grand_total += $srv['total'];
}

// Convert logo ke base64
$logo_path = __DIR__ . '/vaksinin-logo-bw.png';
$logo_base64 = '';
if (file_exists($logo_path)) {
    $logo_data = file_get_contents($logo_path);
    $logo_base64 = 'data:image/png;base64,' . base64_encode($logo_data);
}

// HTML Content
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 20mm 15mm;  /* ✅ Margin halaman: top/bottom 20mm, left/right 15mm */
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
            height: 120px;
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
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <img src="' . $logo_base64 . '" alt="Vaksinin">
        </div>
        <div class="header-right">
            <h1>Faktur</h1>
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
                    <td><strong>No.</strong></td>
                    <td>:</td>
                    <td>' . htmlspecialchars($booking['nomor_antrian']) . '</td>
                </tr>
                <tr>
                    <td><strong>Tanggal</strong></td>
                    <td>:</td>
                    <td>' . $tanggal_payment . '</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Pelayanan</strong></td>
                    <td>:</td>
                    <td>' . $tanggal_pelayanan . '</td>
                </tr>
                <tr>
                    <td><strong>Tanggal Jatuh Tempo</strong></td>
                    <td>:</td>
                    <td>' . $jatuh_tempo . '</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="kepada-section">
        <p><strong>Kepada :</strong></p>
        <p>' . htmlspecialchars($booking['nama_lengkap']) . ' (' . htmlspecialchars($phone) . ')</p>';

if ($address) {
    $html .= '<p>' . htmlspecialchars($address['alamat']) . ', ' . htmlspecialchars($address['kota']) . ', ' . htmlspecialchars($address['provinsi']) . '</p>';
}

$html .= '
    </div>

    <div class="pembayaran-section">
        <p><strong>Pembayaran :</strong></p>
        <p>' . $payment_text . '</p>
    </div>

    <table class="layanan-table">
        <thead>
            <tr>
                <th style="width: 40px;">No.</th>
                <th>Deskripsi</th>
                <th style="width: 60px;">Jml</th>
                <th style="width: 100px;">Harga</th>
                <th style="width: 100px;">Diskon</th>
                <th style="width: 120px;">Total</th>
            </tr>
        </thead>
        <tbody>';

$no = 1;
foreach ($data_services as $srv) {
    $html .= '
            <tr>
                <td style="text-align: center;">' . $no++ . '</td>
                <td>' . htmlspecialchars($srv['nama_layanan']) . '</td>
                <td style="text-align: center;">1</td>
                <td style="text-align: right;">Rp. ' . number_format($srv['harga'], 0, ',', '.') . '</td>
                <td style="text-align: right;">
                ';

                if ($srv['diskon'] > 0) {
                    if ($srv['diskon_tipe'] === 'persen') {
                        $html .= round(($srv['diskon'] / $srv['harga']) * 100) . '%';
                    } else {
                        $html .= 'Rp. ' . number_format($srv['diskon'], 0, ',', '.');
                    }
                } else {
                    $html .= '-';
                }

                $html .= '
                </td>

                <td style="text-align: right;">Rp. ' . number_format($srv['total'], 0, ',', '.') . '</td>
            </tr>';
}

$html .= '
            <tr class="empty-row">
                <td colspan="6">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-section">
        <div class="keterangan">
            <p><strong>Keterangan:</strong></p>
            <p>-</p>
        </div>
        <div class="total-section">
            <table class="total-table">
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td>:</td>
                    <td>Rp. ' . number_format($subtotal, 0, ',', '.') . '</td>
                </tr>
                <tr>
                    <td><strong>Diskon Final</strong></td>
                    <td>:</td>
                    <td>
                    ';

                    if ($payment['diskon'] > 0) {
                        if ($payment['diskon_tipe'] === 'persen') {
                            $html .= $payment['diskon'] . '%';
                        } else {
                            $html .= 'Rp. ' . number_format($payment['diskon'], 0, ',', '.');
                        }
                    } else {
                        $html .= '-';
                    }

                    $html .= '
                    </td>

                </tr>
                <tr class="total-row">
                    <td><strong>Total</strong></td>
                    <td>:</td>
                    <td><strong>Rp. ' . number_format($grand_total, 0, ',', '.') . '</strong></td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>
';

// Generate PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ambil output PDF (binary)
$pdfOutput = $dompdf->output();

// pastikan folder ada
$savePath = __DIR__ . '/../uploads/invoice';
if (!is_dir($savePath)) {
    mkdir($savePath, 0777, true);
}

// nama file
$filename = 'Faktur_' . $booking['nomor_antrian'] . '.pdf';

// simpan ke folder
file_put_contents($savePath . '/' . $filename, $pdfOutput);

// Output PDF
$dompdf->stream($filename, ["Attachment" => false]);
