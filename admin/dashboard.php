<?php
session_start();
include "../config.php";

// Get current date info
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$current_week = isset($_GET['week']) ? intval($_GET['week']) : 1;

// Nama bulan
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Calculate week date range
$start_date = ($current_week - 1) * 7 + 1;
$end_date = min($start_date + 6, cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year));

// Get statistics for dashboard cards
$today = date('Y-m-d');

// Total layanan hari ini
$sql_today = "SELECT COUNT(*) as total FROM bookings WHERE DATE(tanggal_booking) = ?";
$stmt = $conn->prepare($sql_today);
$stmt->bind_param('s', $today);
$stmt->execute();
$total_today = $stmt->get_result()->fetch_assoc()['total'];

/// Pesanan selesai hari ini
$sql_done = "SELECT COUNT(*) as total 
             FROM bookings 
             WHERE status = 'completed' 
             AND DATE(tanggal_booking) = ?";
$stmt = $conn->prepare($sql_done);
$stmt->bind_param('s', $today);
$stmt->execute();
$total_done = $stmt->get_result()->fetch_assoc()['total'];

// Pesanan dibatalkan hari ini
$sql_cancelled = "SELECT COUNT(*) as total 
                  FROM bookings 
                  WHERE status = 'cancelled' 
                  AND DATE(tanggal_booking) = ?";
$stmt = $conn->prepare($sql_cancelled);
$stmt->bind_param('s', $today);
$stmt->execute();
$total_cancelled = $stmt->get_result()->fetch_assoc()['total'];

// Pesanan belum diproses hari ini
$sql_pending = "SELECT COUNT(*) as total 
                FROM bookings 
                WHERE status = 'pending' 
                AND DATE(tanggal_booking) = ?";
$stmt = $conn->prepare($sql_pending);
$stmt->bind_param('s', $today);
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
    GROUP BY b.id
    ORDER BY 
        FIELD(b.status, 'confirmed', 'pending'),
        b.waktu_booking ASC
    LIMIT 1
";

$stmt = $conn->prepare($sql_now_serving);
$stmt->bind_param('s', $today);
$stmt->execute();
$now_serving = $stmt->get_result()->fetch_assoc();

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'latest';

$where = "";
$order = "";

if ($filter == 'pending') {
    $where = "WHERE b.status = 'pending'";
    $order = "ORDER BY b.tanggal_booking ASC, b.waktu_booking ASC";
} 
elseif ($filter == 'confirmed') {
    $where = "WHERE b.status = 'confirmed'";
    $order = "ORDER BY b.tanggal_booking ASC, b.waktu_booking ASC";
}
elseif ($filter == 'completed') {
    $where = "WHERE b.status = 'completed'";
    $order = "ORDER BY b.tanggal_booking DESC, b.waktu_booking DESC";
}
elseif ($filter == 'cancelled') {
    $where = "WHERE b.status = 'cancelled'";
    $order = "ORDER BY b.tanggal_booking DESC, b.waktu_booking DESC";
}
else { // latest
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

$all_bookings = $conn->query($sql_all);

// Get bookings for calendar view (current week)
$week_start = sprintf('%04d-%02d-%02d', $current_year, $current_month, $start_date);
$week_end = sprintf('%04d-%02d-%02d', $current_year, $current_month, $end_date);

$sql_bookings = "SELECT b.*, p.nama_lengkap 
                 FROM bookings b 
                 JOIN patients p ON b.patient_id = p.id 
                 WHERE b.tanggal_booking BETWEEN ? AND ?
                 ORDER BY b.tanggal_booking, b.waktu_booking";
$stmt_bookings = $conn->prepare($sql_bookings);
$stmt_bookings->bind_param('ss', $week_start, $week_end);
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

// Get all bookings for list (latest first)
$sql_all = "
    SELECT 
        b.*, 
        p.nama_lengkap,
        s.nama_lengkap AS dokter_nama,
        s.gelar AS dokter_gelar
    FROM bookings b
    JOIN patients p ON b.patient_id = p.id
    LEFT JOIN staff s ON b.doctor_id = s.id
    ORDER BY 
        b.tanggal_booking DESC,
        b.waktu_booking DESC
    LIMIT 50
";

$all_bookings = $conn->query($sql_all);

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
        <header class="page-header">
            <h1>Kalender</h1>
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

        <?php if ($now_serving): ?>
        <div class="now-serving-card">

            <!-- KIRI: NOMOR ANTRIAN -->
            <div class="now-number">
                <div class="label">Nomor Antrian</div>
                <div class="number"><?= htmlspecialchars($now_serving['nomor_antrian']) ?></div>
                <div class="now-status">Sedang Dilayani</div>
            </div>

            <!-- GARIS PEMISAH -->
            <div class="divider"></div>

            <!-- TENGAH: NAMA -->
            <div class="now-info">
                <div class="info-row">
                    <i class="fas fa-user"></i>
                    <span class="info-label">Nama :</span>
                    <span class="info-value"><?= htmlspecialchars($now_serving['nama_lengkap']) ?></span>
                </div>
            </div>

            <!-- KANAN: LAYANAN -->
            <div class="now-info">
                <div class="info-row">
                    <i class="fas fa-check-circle"></i>
                    <span class="info-label">Layanan :</span>
                </div>
                <div class="service-list">
                    <?= $now_serving['layanan'] ?>
                </div>
            </div>

            <!-- ICON KANAN -->
            <div class="now-icon" onclick="location.reload()" title="Perbarui Tampilan">
                <i class="fas fa-sync-alt"></i>
            </div>
        </div>
        <?php endif; ?>


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
                        <?php for ($w = 1; $w <= $total_weeks; $w++): ?>
                            <option value="<?php echo $w; ?>" <?php echo ($w == $current_week) ? 'selected' : ''; ?>>
                                Minggu <?php echo $w; ?>
                            </option>
                        <?php endfor; ?>
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

                        <td><?= implode(', ', $service_names) ?></td>

                        <!-- WAKTU -->
                        <td>
                            <?= date('d/m/Y', strtotime($booking['tanggal_booking'])) ?>
                            - <?= substr($booking['waktu_booking'], 0, 5) ?> WIB
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

</body>
</html>