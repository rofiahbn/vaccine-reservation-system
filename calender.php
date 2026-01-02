<?php
session_start();

// Ambil patient_id dari URL
$patient_id = isset($_GET['id_pasien']) ? intval($_GET['id_pasien']) : 0;

if (!$patient_id) {
    header('Location: order.php');
    exit;
}

// Set bulan dan tahun (default: bulan ini)
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : date('n');
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : date('Y');

// Nama bulan dalam bahasa Indonesia
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Hitung jumlah hari dalam bulan
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Hari pertama bulan (0=Minggu, 6=Sabtu)
$hari_pertama = date('w', strtotime("$tahun-$bulan-01"));

// Hari ini
$hari_ini = ($bulan == date('n') && $tahun == date('Y')) ? date('j') : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tanggal dan Jadwalkan</title>
    <link rel="stylesheet" href="calender.css">
</head>
<body>
    <div class="container">
        <h1>Pilih Tanggal dan Jadwalkan</h1>

        <form method="GET" action="" id="monthForm">
            <input type="hidden" name="id_pasien" value="<?php echo $patient_id; ?>">
            <input type="hidden" name="bulan" id="inputBulan" value="<?php echo $bulan; ?>">
            <input type="hidden" name="tahun" id="inputTahun" value="<?php echo $tahun; ?>">
        </form>

        <div class="calendar-header">
            <button onclick="prevMonth()">&lt;</button>
            <h2><?php echo $nama_bulan[$bulan] . ' ' . $tahun; ?></h2>
            <button onclick="nextMonth()">&gt;</button>
        </div>

        <div class="calendar-days">
            <div class="day-header">M</div>
            <div class="day-header">S</div>
            <div class="day-header">S</div>
            <div class="day-header">R</div>
            <div class="day-header">K</div>
            <div class="day-header">J</div>
            <div class="day-header">S</div>

            <?php
            // Kosongkan hari sebelum tanggal 1
            $hari_awal = ($hari_pertama == 0) ? 6 : $hari_pertama - 1; // Konversi ke Senin = 0
            for ($i = 0; $i < $hari_awal; $i++) {
                echo '<div class="day empty"></div>';
            }

            // Tampilkan tanggal
            for ($tgl = 1; $tgl <= $jumlah_hari; $tgl++) {
                $class = 'day';
                
                // Hari ini
                if ($tgl == $hari_ini) {
                    $class .= ' today';
                }
                
                // Contoh: tanggal 15 sudah penuh (bisa diganti dengan cek database)
                if ($tgl == 15) {
                    $class .= ' full';
                    echo "<div class='$class'>$tgl</div>";
                } else {
                    echo "<div class='$class' onclick='selectDate($tgl)'>$tgl</div>";
                }
            }
            ?>
        </div>

        <div class="legend">
            <div class="legend-item">
                <div class="legend-box tersedia"></div>
                <span>Tersedia</span>
            </div>
            <div class="legend-item">
                <div class="legend-box penuh"></div>
                <span>Jadwal Penuh</span>
            </div>
        </div>

        <form method="POST" action="booking_confirmation.php" id="bookingForm">
            <input type="hidden" name="id_pasien" value="<?php echo $patient_id; ?>">
            <input type="hidden" name="tanggal" id="selectedDate" value="">
            
            <div class="selected-date" id="dateDisplay" style="display:none;">
                Tanggal yang dipilih: <strong id="dateText"></strong>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit" disabled>
                Selesai
            </button>
        </form>
    </div>

    <script>
        // Pass PHP variables to JavaScript
        const bulanNow = <?php echo $bulan; ?>;
        const tahunNow = <?php echo $tahun; ?>;
        const namaBulanNow = '<?php echo $nama_bulan[$bulan]; ?>';
    </script>
    <script src="calender.js"></script>
</body>
</html>