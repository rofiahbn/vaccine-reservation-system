<?php
session_start();
include "../config.php";

$service_mode = isset($_GET['service']) ? $_GET['service'] : 'In Clinic';
// default: in_clinic

// Get current date info
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year  = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$today = new DateTime();   // hari ini asli

// kalau user belum pilih minggu → cari minggu sekarang
if (!isset($_GET['week'])) {

    // cari semua minggu dalam bulan ini (Senin - Minggu)
    $weeks = [];

    $firstDayOfMonth = new DateTime("$current_year-$current_month-01");
    $firstMonday = clone $firstDayOfMonth;
    $firstMonday->modify('monday this week');

    $weekIndex = 1;

    while (true) {
        $weekStart = clone $firstMonday;
        $weekStart->modify('+' . (($weekIndex - 1) * 7) . ' days');

        $weekEnd = clone $weekStart;
        $weekEnd->modify('+6 days');

        // stop kalau minggu sudah full di bulan berikutnya
        if ((int)$weekStart->format('m') > $current_month &&
            (int)$weekEnd->format('m') > $current_month) {
            break;
        }

        $weeks[] = [
            'start' => clone $weekStart,
            'end'   => clone $weekEnd
        ];

        $weekIndex++;
    }

    // tentukan hari ini masuk minggu ke berapa
    $current_week = 1;
    foreach ($weeks as $index => $w) {
        if ($today >= $w['start'] && $today <= $w['end']) {
            $current_week = $index + 1;
            break;
        }
    }

} else {
    $current_week = intval($_GET['week']);
}

// Nama bulan
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Calculate week date range
// Ambil semua minggu dalam bulan (berdasarkan hari Senin)
$weeks = [];

$first_day = new DateTime("$current_year-$current_month-01");

// mundur ke Senin pertama minggu itu
$start = clone $first_day;
$start->modify('monday this week');

while (true) {
    $week_start = clone $start;
    $week_end   = clone $start;
    $week_end->modify('+6 days');

    // kalau minggu sudah lewat dari bulan, stop
    if ((int)$week_start->format('m') > $current_month &&
        (int)$week_end->format('m') > $current_month) {
        break;
    }

    $weeks[] = [
        'start' => $week_start->format('Y-m-d'),
        'end'   => $week_end->format('Y-m-d'),
        'label' => $week_start->format('d') . ' - ' . $week_end->format('d')
    ];

    $start->modify('+1 week');
}

// Get statistics for dashboard cards
$today = date('Y-m-d');

// Total layanan hari ini
$sql_today = "SELECT COUNT(*) as total 
              FROM bookings 
              WHERE DATE(tanggal_booking) = ? 
              AND service_type = ?";
$stmt = $conn->prepare($sql_today);
$stmt->bind_param('ss', $today, $service_mode);
$stmt->execute();
$total_today = $stmt->get_result()->fetch_assoc()['total'];

/// Pesanan selesai hari ini
$sql_done = "SELECT COUNT(*) as total 
             FROM bookings 
             WHERE status = 'completed' 
             AND DATE(tanggal_booking) = ?
             AND service_type = ?";
$stmt = $conn->prepare($sql_done);
$stmt->bind_param('ss', $today, $service_mode);
$stmt->execute();
$total_done = $stmt->get_result()->fetch_assoc()['total'];

// Pesanan dibatalkan hari ini
$sql_cancelled = "SELECT COUNT(*) as total 
                  FROM bookings 
                  WHERE status = 'cancelled' 
                  AND DATE(tanggal_booking) = ?
                  AND service_type = ?";
$stmt = $conn->prepare($sql_cancelled);
$stmt->bind_param('ss', $today, $service_mode);
$stmt->execute();
$total_cancelled = $stmt->get_result()->fetch_assoc()['total'];

// Pesanan belum diproses hari ini
$sql_pending = "SELECT COUNT(*) as total 
                FROM bookings 
                WHERE status = 'pending' 
                AND DATE(tanggal_booking) = ?
                AND service_type = ?";
$stmt = $conn->prepare($sql_pending);
$stmt->bind_param('ss', $today, $service_mode);
$stmt->execute();
$total_pending = $stmt->get_result()->fetch_assoc()['total'];

// Ambil antrian yang sedang berjalan (display only)
$sql_now_serving = "
    SELECT 
        b.id,
        b.nomor_antrian,
        p.nama_lengkap,
        GROUP_CONCAT(bs.nama_layanan SEPARATOR '<br>') AS layanan,
        b.status
    FROM bookings b
    JOIN patients p ON b.patient_id = p.id
    LEFT JOIN booking_services bs ON bs.booking_id = b.id
    WHERE DATE(b.tanggal_booking) = ?
      AND b.status IN ('confirmed', 'pending')
      AND b.service_type = ?
    GROUP BY b.id
    ORDER BY 
        FIELD(b.status, 'confirmed', 'pending'),
        b.waktu_booking ASC
    LIMIT 1
";

$stmt = $conn->prepare($sql_now_serving);
$stmt->bind_param('ss', $today, $service_mode);
$stmt->execute();
$now_serving = $stmt->get_result()->fetch_assoc();

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'latest';

$where = "WHERE DATE(b.tanggal_booking) = ? AND b.service_type = ?";
$order = "";

if ($filter == 'pending') {
    $where = "WHERE b.status = 'pending' 
              AND DATE(b.tanggal_booking) = ? 
              AND b.service_type = ?";
    $order = "ORDER BY b.tanggal_booking ASC, b.waktu_booking ASC";
} 
elseif ($filter == 'confirmed') {
    $where = "WHERE b.status = 'confirmed' 
              AND DATE(b.tanggal_booking) = ? 
              AND b.service_type = ?";
    $order = "ORDER BY b.tanggal_booking ASC, b.waktu_booking ASC";
}
elseif ($filter == 'completed') {
    $where = "WHERE b.status = 'completed' 
              AND DATE(b.tanggal_booking) = ? 
              AND b.service_type = ?";
    $order = "ORDER BY b.tanggal_booking DESC, b.waktu_booking DESC";
}
elseif ($filter == 'cancelled') {
    $where = "WHERE b.status = 'cancelled' 
              AND DATE(b.tanggal_booking) = ? 
              AND b.service_type = ?";
    $order = "ORDER BY b.tanggal_booking DESC, b.waktu_booking DESC";
}
else {
    $where = "WHERE DATE(b.tanggal_booking) = ? 
              AND b.service_type = ?";
    $order = "ORDER BY b.tanggal_booking DESC, b.waktu_booking DESC";
}

$sql_all = "
    SELECT 
        b.*, 
        p.nama_lengkap,
        s.nama_lengkap AS dokter_nama,
        s.gelar AS dokter_gelar
    FROM bookings b
    JOIN patients p ON b.patient_id = p.id
    LEFT JOIN staff s ON b.doctor_id = s.id
    $where
    $order
    LIMIT 50
";

$stmt_all = $conn->prepare($sql_all);
$stmt_all->bind_param('ss', $today, $service_mode);
$stmt_all->execute();
$all_bookings = $stmt_all->get_result();

// Get bookings for calendar view (current week)
// pastikan index minggu valid
if (!isset($weeks[$current_week - 1])) {
    $current_week = 1;
}

$week_start = $weeks[$current_week - 1]['start'];
$week_end   = $weeks[$current_week - 1]['end'];

$sql_bookings = "SELECT b.*, p.nama_lengkap 
                 FROM bookings b 
                 JOIN patients p ON b.patient_id = p.id 
                 WHERE b.tanggal_booking BETWEEN ? AND ?
                 AND b.service_type = ?
                 ORDER BY b.tanggal_booking, b.waktu_booking";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param('sss', $week_start, $week_end, $service_mode);
$stmt_bookings->execute();
$bookings_result = $stmt_bookings->get_result();

// Organize bookings by day and time
$bookings_grid = [];
while ($row = $bookings_result->fetch_assoc()) {
    $day = date('N', strtotime($row['tanggal_booking'])); // 1=Monday, 6=Saturday
    $time = date('H:i', strtotime($row['waktu_booking']));
    
    // DEBUG: Print data
    echo "<!-- DEBUG: Date={$row['tanggal_booking']}, Day=$day, Time=$time, Status={$row['status']} -->";
    
    if (!isset($bookings_grid[$time])) {
        $bookings_grid[$time] = [];
    }
    if (!isset($bookings_grid[$time][$day])) {
        $bookings_grid[$time][$day] = [];
    }
    $bookings_grid[$time][$day][] = $row;
}

// DEBUG: Print entire grid
echo "<!-- DEBUG GRID: " . print_r($bookings_grid, true) . " -->";

// Calculate total weeks in current month
$total_days = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);
$total_weeks = ceil($total_days / 7);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Vaksinin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="vaksinin-logo.png" alt="Vaksinin">
        </div>
        <nav class="nav-menu">
            <a href="dashboard.php" class="nav-item active">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Kalender</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Pasien</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header class="page-header <?= ($service_mode == 'Home Service') ? 'home_service' : 'in_clinic' ?>"
        style="display:flex; justify-content:space-between; align-items:center;">
            <h1>
                Kalender 
                <?php if ($service_mode == 'Home Service'): ?>
                    <span style="font-size:14px; color:#ff7a00;">(Home Service)</span>
                <?php else: ?>
                    <span style="font-size:14px; color:#2ecc71;">(In Clinic)</span>
                <?php endif; ?>
            </h1>

            <div style="display:flex; align-items:center; gap:20px;">

                <!-- TOGGLE SERVICE -->
                <div class="service-toggle">
                    <button 
                        onclick="switchService('In Clinic')" 
                        class="toggle-btn <?= $service_mode=='In Clinic'?'active':'' ?>">
                        In Clinic
                    </button>

                    <button 
                        onclick="switchService('Home Service')" 
                        class="toggle-btn <?= $service_mode=='Home Service'?'active':'' ?>">
                        Home Service
                    </button>
                </div>

                <!-- TANGGAL & JAM -->
                <div class="today-info">
                    <div class="today-date" id="todayDate"></div>
                    <div class="today-time" id="todayTime"></div>
                </div>
            </div>
        </header>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-icon green">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="card-content">
                    <div class="card-label">Total Layanan Hari ini</div>
                    <div class="card-value"><?php echo $total_today; ?></div>
                    <div class="card-subtitle">Layanan</div>
                </div>
            </div>

            <div class="card">
                <div class="card-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="card-content">
                    <div class="card-label">Pesanan Selesai</div>
                    <div class="card-value"><?php echo $total_done; ?></div>
                    <div class="card-subtitle">Layanan</div>
                </div>
            </div>

            <div class="card">
                <div class="card-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="card-content">
                    <div class="card-label">Pesanan dibatalkan</div>
                    <div class="card-value"><?php echo $total_cancelled; ?></div>
                    <div class="card-subtitle">Layanan</div>
                </div>
            </div>

            <div class="card">
                <div class="card-icon blue">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-content">
                    <div class="card-label">Pesanan belum diproses</div>
                    <div class="card-value"><?php echo $total_pending; ?></div>
                    <div class="card-subtitle">Layanan</div>
                </div>
            </div>
        </div>

        <div class="now-serving-card">

            <!-- KIRI: NOMOR ANTRIAN -->
            <div class="now-number">
                <div class="label">Nomor Antrian</div>
                <div class="number">
                    <?= $now_serving ? htmlspecialchars($now_serving['nomor_antrian']) : '-' ?>
                </div>
                <div class="now-status">
                    <?= $now_serving ? 'Sedang Dilayani' : 'Belum ada pasien hari ini' ?>
                </div>
            </div>

            <!-- GARIS PEMISAH -->
            <div class="divider"></div>

            <!-- TENGAH: NAMA -->
            <div class="now-info">
                <div class="info-row">
                    <i class="fas fa-user"></i>
                    <span class="info-label">Nama :</span>
                    <span class="info-value">
                        <?= $now_serving ? htmlspecialchars($now_serving['nama_lengkap']) : '-' ?>
                    </span>
                </div>
            </div>

            <!-- KANAN: LAYANAN -->
            <div class="now-info">
                <div class="info-row">
                    <i class="fas fa-check-circle"></i>
                    <span class="info-label">Layanan :</span>
                </div>
                <div class="service-list">
                    <?= $now_serving ? $now_serving['layanan'] : '<span style="color:#999;">-</span>' ?>
                </div>
            </div>

            <!-- ICON KANAN -->
            <div class="now-icon" onclick="location.reload()" title="Perbarui Tampilan">
                <i class="fas fa-sync-alt"></i>
            </div>
        </div>

        <!-- Booking View -->
        <div class="booking-view">
            <div class="section-header">
                <h2>Booking View</h2>
                <div class="filters">
                    <select id="monthSelect" onchange="changeMonth()">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($m == $current_month) ? 'selected' : ''; ?>>
                                <?php echo $nama_bulan[$m]; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select id="weekSelect" onchange="changeWeek()">
                        <?php foreach ($weeks as $i => $w): ?>
                            <option value="<?= $i+1 ?>" <?= (($i+1)==$current_week)?'selected':'' ?>>
                                <?= "Minggu ".($i+1)." ({$w['label']})" ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Calendar Grid -->
             <div class="calendar-wrapper">
                <div class="calendar-grid">
                    <table class="booking-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Senin</th>
                                <th>Selasa</th>
                                <th>Rabu</th>
                                <th>Kamis</th>
                                <th>Jumat</th>
                                <th>Sabtu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $start_time = strtotime('09:00');
                                $end_time   = strtotime('17:00');

                                for ($time = $start_time; $time <= $end_time; $time += 15 * 60):
                                    $time_slot = date('H:i', $time);
                                ?>
                                    <tr>
                                        <td class="time-cell" data-time="<?php echo $time_slot; ?>">
                                            <?php echo $time_slot; ?>
                                        </td>
                                        <?php for ($day = 1; $day <= 6; $day++): ?>
                                            <td class="booking-cell"
                                                data-time="<?php echo $time_slot; ?>"
                                                data-day="<?php echo $day; ?>">

                                                <?php if (isset($bookings_grid[$time_slot][$day])): ?>
                                                    <?php
                                                    $bookings = $bookings_grid[$time_slot][$day];
                                                    $status = $bookings[0]['status'];

                                                    $color_class =
                                                        ($status === 'confirmed') ? 'status-confirmed' :
                                                        (($status === 'completed') ? 'status-completed' :
                                                        (($status === 'cancelled') ? 'status-cancelled' :
                                                        (($status === 'pending') ? 'status-pending' : '')));
                                                    ?>

                                                    <div class="booking-item <?php echo $color_class; ?>" 
                                                        onclick="showBookingDetail(<?php echo $bookings[0]['id']; ?>)">
                                                        <?php foreach ($bookings as $index => $b): ?>
                                                            <div class="booking-row">
                                                                <span class="queue-no">
                                                                    <?php echo htmlspecialchars($b['nomor_antrian']); ?>
                                                                </span>
                                                                <span class="patient-name">
                                                                    <?php echo htmlspecialchars($b['nama_lengkap']); ?>
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>

                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="booking-legend">
                <div class="legend-item">
                    <span class="legend-dot pending"></span>
                    <span>Menunggu</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot confirmed"></span>
                    <span>Dalam Antrian</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot cancelled"></span>
                    <span>Dibatalkan</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot completed"></span>
                    <span>Selesai</span>
                </div>                                                 
            </div>
        </div>

        <!-- Booking List -->
        <div class="booking-list">
            <div class="section-header">
                <h2>Booking List</h2>
                <select class="filter-select" id="bookingFilter" onchange="filterBookingList()">
                    <option value="latest" <?= ($filter=='latest')?'selected':'' ?>>Terbaru</option>
                    <option value="pending" <?= ($filter=='pending')?'selected':'' ?>>Pending</option>
                    <option value="confirmed" <?= ($filter=='confirmed')?'selected':'' ?>>Dalam Antrian</option>
                    <option value="completed" <?= ($filter=='completed')?'selected':'' ?>>Selesai</option>
                    <option value="cancelled" <?= ($filter=='cancelled')?'selected':'' ?>>Dibatalkan</option>
                </select>
            </div>

            <table class="list-table">
                <thead>
                    <tr>
                        <th>Nama Pasien</th>
                        <th>Layanan</th>
                        <th>Produk</th>
                        <th>Waktu</th>
                        <th>Dokter</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $all_bookings->fetch_assoc()): ?>
                        <?php
                        // Ambil semua dokter untuk booking ini
                        $booking_id = $booking['id'];
                        $sql_staff = "
                            SELECT s.gelar, s.nama_lengkap
                            FROM booking_staff bs
                            JOIN staff s ON bs.staff_id = s.id
                            WHERE bs.booking_id = ?
                        ";
                        $stmt_staff = $conn->prepare($sql_staff);
                        $stmt_staff->bind_param("i", $booking_id);
                        $stmt_staff->execute();
                        $result_staff = $stmt_staff->get_result();

                        $dokters = [];
                        while ($row = $result_staff->fetch_assoc()) {
                            $dokters[] = htmlspecialchars($row['gelar'].' '.$row['nama_lengkap']);
                        }
                        ?>

                        <?php
                        // Get services for this booking
                        $sql_services = "SELECT nama_layanan FROM booking_services WHERE booking_id = ?";
                        $stmt_s = $conn->prepare($sql_services);
                        $stmt_s->bind_param('i', $booking['id']);
                        $stmt_s->execute();
                        $services = $stmt_s->get_result();
                        $service_names = [];
                        while ($s = $services->fetch_assoc()) {
                            $service_names[] = $s['nama_layanan'];
                        }
                        ?>
                        <tr>
                        <td><?= htmlspecialchars($booking['nama_lengkap']) ?></td>

                        <td><?= htmlspecialchars($booking['service_type']) ?></td>

                        <td>
                            <?php if (!empty($service_names)): ?>
                                <?php foreach ($service_names as $prod): ?>
                                    <div><?= htmlspecialchars($prod) ?></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <!-- WAKTU -->
                        <td>
                            <?= substr($booking['waktu_booking'], 0, 5) ?> WIB
                        </td>

                        <!-- DOKTER -->
                        <td>
                            <?php if (!empty($dokters)): ?>
                                <?php foreach ($dokters as $dokter): ?>
                                    <div><?= $dokter ?></div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>


                        <!-- STATUS -->
                        <td>
                            <span class="status-badge <?= $booking['status'] ?>">
                                <?php 
                                    if ($booking['status'] == 'pending') {
                                        echo 'Menunggu Konfirmasi';
                                    } elseif ($booking['status'] == 'confirmed') {
                                        echo 'Pasien Dalam Antrian';
                                    } elseif ($booking['status'] == 'completed') {
                                        echo 'Pesanan Selesai';
                                    } elseif ($booking['status'] == 'cancelled') {
                                        echo 'Pesanan Dibatalkan';
                                    }
                                ?>
                            </span>
                        </td>

                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="js/admin.js"></script>
    <script>
        document.querySelector('.now-icon').addEventListener('click', function() {

            fetch('get_now_serving.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('.number').innerText = data.nomor_antrian;
                        document.querySelector('.info-value').innerText = data.nama_lengkap;
                        document.querySelector('.service-list').innerHTML = data.layanan;
                    } else {
                        // mode kosong
                        location.reload(); 
                    }
                });

        });
    </script>

    <script>
        function switchService(mode) {
            const month = document.getElementById('monthSelect')?.value || '<?= $current_month ?>';
            const week  = document.getElementById('weekSelect')?.value || '<?= $current_week ?>';

            window.location.href = 
                "dashboard.php?service=" + mode + "&month=" + month + "&week=" + week;
        }

        function switchService(mode) {
            const month = document.getElementById('monthSelect')?.value || '<?= $current_month ?>';
            const week  = document.getElementById('weekSelect')?.value || '<?= $current_week ?>';

            window.location.href = 
                "dashboard.php?service=" + mode + "&month=" + month + "&week=" + week;
        }

        function changeWeek() {
            const month = document.getElementById('monthSelect').value;
            const week  = document.getElementById('weekSelect').value;
            const service = "<?= $service_mode ?>";

            window.location.href = 
                "dashboard.php?service=" + service + "&month=" + month + "&week=" + week;
        }

        function updateDateTime() {
            const now = new Date();

            const days = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
            const months = [
                "Januari","Februari","Maret","April","Mei","Juni",
                "Juli","Agustus","September","Oktober","November","Desember"
            ];

            const dayName = days[now.getDay()];
            const date = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();

            const hours = String(now.getHours()).padStart(2,'0');
            const minutes = String(now.getMinutes()).padStart(2,'0');
            const seconds = String(now.getSeconds()).padStart(2,'0');

            document.getElementById('todayDate').innerText =
                `${dayName}, ${date} ${month} ${year}`;

            document.getElementById('todayTime').innerText =
                `${hours}:${minutes}:${seconds}`;
        }

        // jalankan pertama kali
        updateDateTime();

        // update tiap 1 detik
        setInterval(updateDateTime, 1000);
        </script>

</body>
</html>