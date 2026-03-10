<?php
date_default_timezone_set('Asia/Jakarta');
include "config.php";

// Fungsi hitung usia tahun dan bulan
function hitungUsiaLengkap($tanggal_lahir) {
    $lahir = new DateTime($tanggal_lahir);
    $sekarang = new DateTime();
    $diff = $sekarang->diff($lahir);
    
    $tahun = $diff->y;
    $bulan = $diff->m;
    
    if ($tahun > 0 && $bulan > 0) {
        return $tahun . " thn " . $bulan . " bln";
    } elseif ($tahun > 0) {
        return $tahun . " thn";
    } elseif ($bulan > 0) {
        return $bulan . " bln";
    } else {
        $hari = $diff->d;
        return $hari . " hr";
    }
}

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($booking_id == 0) die("Booking ID tidak valid.");

// Pastikan parent booking
$sql_parent = "SELECT parent_id FROM bookings WHERE id = ?";
$stmt = $conn->prepare($sql_parent);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$row_parent = $stmt->get_result()->fetch_assoc();
if ($row_parent && $row_parent['parent_id']) {
    $booking_id = $row_parent['parent_id'];
}

// Ambil semua peserta
$sql = "
    SELECT b.id AS booking_id, b.nomor_antrian,
           p.no_rekam_medis, p.nama_lengkap, p.jenis_kelamin,
           p.tanggal_lahir, p.usia, p.kategori_usia, p.nama_wali
    FROM bookings b
    JOIN patients p ON b.patient_id = p.id
    WHERE b.id = ? OR b.parent_id = ?
    ORDER BY CASE WHEN b.id = ? THEN 0 ELSE 1 END
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $booking_id, $booking_id, $booking_id);
$stmt->execute();
$participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($participants)) die("Data tidak ditemukan.");

function formatTanggalLabel($date) {
    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    $parts = explode('-', $date);
    return $parts[2] . ' ' . $bulan[(int)$parts[1] - 1] . ' ' . $parts[0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Label - <?= htmlspecialchars($participants[0]['nomor_antrian']) ?></title>
    <style>
        @page {
            size: 76mm 297mm;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            width: 76mm;
            background: white;
        }

        .label {
            width: 76mm;
            min-height: 40mm;
            padding: 4mm 5mm;
            border-bottom: 1px dashed #aaa;
            page-break-inside: avoid;
        }

        .label:last-child {
            border-bottom: none;
        }

        .label-nomor {
            font-size: 7pt;
            color: #666;
            margin-bottom: 2mm;
            border-bottom: 0.5px solid #ddd;
            padding-bottom: 1mm;
        }

        .label-wali {
            font-size: 8pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 1mm;
        }

        .label-wali span {
            font-weight: normal;
            font-size: 7.5pt;
            color: #555;
        }

        .label-rekam {
            font-size: 8pt;
            color: #444;
            margin-bottom: 1mm;
        }

        .label-nama {
            font-size: 10pt;
            font-weight: bold;
            color: #111;
            line-height: 1.3;
        }

        .label-detail {
            font-size: 8pt;
            color: #444;
            margin-top: 1mm;
        }

        /* ===== PRINT PREVIEW SCREEN STYLE ===== */
        @media screen {
            body {
                background: #e5e5e5;
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 20px;
                gap: 0;
            }

            .print-toolbar {
                width: 76mm;
                margin-top: 16px;
                display: flex;
                gap: 8px;
            }

            .btn-print-now {
                flex: 1;
                background: #f5a623;
                color: white;
                border: none;
                padding: 10px;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
            }

            .btn-close {
                flex: 1;
                background: #6b7280;
                color: white;
                border: none;
                padding: 10px 16px;
                border-radius: 6px;
                font-size: 14px;
                cursor: pointer;
            }

            .label-wrapper {
                width: 76mm;
                background: white;
                box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            }

            .label {
                background: white;
            }
        }

        @media print {
            .print-toolbar { display: none; }
            .label-wrapper { box-shadow: none; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="label-wrapper">
    <?php foreach ($participants as $p): ?>
    <div class="label">

        <?php if ($p['kategori_usia'] === 'Anak' && !empty($p['nama_wali'])): ?>
            <!-- FORMAT LABEL ANAK -->
            <div class="label-nomor">No. Antrian: <?= htmlspecialchars($p['nomor_antrian']) ?></div>
            <div class="label-wali">
                Wali: <span><?= htmlspecialchars($p['nama_wali']) ?></span>
            </div>
            <div class="label-rekam">
                <?= htmlspecialchars($p['no_rekam_medis']) ?>
            </div>
            <div class="label-nama">
                <?= htmlspecialchars($p['nama_lengkap']) ?>
                (<?= $p['jenis_kelamin'] == 'L' ? 'L' : 'P' ?>)
            </div>
            <div class="label-detail">
                <?= formatTanggalLabel($p['tanggal_lahir']) ?>
                &nbsp;·&nbsp;
                <?= hitungUsiaLengkap($p['tanggal_lahir']) ?>
            </div>

        <?php else: ?>
            <!-- FORMAT LABEL DEWASA -->
            <div class="label-nomor">No. Antrian: <?= htmlspecialchars($p['nomor_antrian']) ?></div>
            <div class="label-rekam">
                <?= htmlspecialchars($p['no_rekam_medis']) ?>
            </div>
            <div class="label-nama">
                <?= htmlspecialchars($p['nama_lengkap']) ?>
                (<?= $p['jenis_kelamin'] == 'L' ? 'L' : 'P' ?>)
            </div>
            <div class="label-detail">
                <?= formatTanggalLabel($p['tanggal_lahir']) ?>
                &nbsp;·&nbsp;
                <?= hitungUsiaLengkap($p['tanggal_lahir']) ?>
            </div>

        <?php endif; ?>

    </div>
    <?php endforeach; ?>
</div>

<div class="print-toolbar">
    <button class="btn-print-now" onclick="window.print()">
        <i class="fas fa-print"></i>
    </button>
    <button class="btn-close" onclick="window.close()">
        ✕ Tutup
    </button>
</div>

</body>
</html>