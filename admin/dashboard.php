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

// Pesanan selesai
$sql_done = "SELECT COUNT(*) as total FROM bookings WHERE status = 'completed'";
$total_done = $conn->query($sql_done)->fetch_assoc()['total'];

// Pesanan dibatalkan
$sql_cancelled = "SELECT COUNT(*) as total FROM bookings WHERE status = 'cancelled'";
$total_cancelled = $conn->query($sql_cancelled)->fetch_assoc()['total'];

// Pesanan belum diproses
$sql_pending = "SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'";
$total_pending = $conn->query($sql_pending)->fetch_assoc()['total'];

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
    $time = substr($row['waktu_booking'], 0, 5); // HH:MM
    
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
    ORDER BY b.created_at DESC
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
                                                        ($status == 'confirmed') ? 'status-confirmed' :
                                                        (($status == 'cancelled') ? 'status-cancelled' : 'status-pending');
                                                    ?>

                                                    <div class="booking-item <?php echo $color_class; ?>">
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
            </div>
        </div>

        <!-- Booking List -->
        <div class="booking-list">
            <div class="section-header">
                <h2>Booking List</h2>
                <select class="filter-select">
                    <option>Terbaru</option>
                    <option>Terlama</option>
                    <option>Selesai</option>
                    <option>Pending</option>
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
                            <?= $booking['dokter_nama']
                                ? htmlspecialchars($booking['dokter_gelar'].' '.$booking['dokter_nama'])
                                : '-' ?>
                        </td>

                        <!-- STATUS -->
                        <td>
                            <span class="status-badge <?= $booking['status'] ?>">
                                <?= $booking['status'] == 'pending' ? 'Sedang Berlangsung' :
                                ($booking['status'] == 'completed' ? 'Selesai' : 'Dibatalkan') ?>
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
        const currentYear = <?php echo $current_year; ?>;
        
        function changeMonth() {
            const month = document.getElementById('monthSelect').value;
            window.location.href = `dashboard.php?month=${month}&year=${currentYear}&week=1`;
        }
        
        function changeWeek() {
            const month = document.getElementById('monthSelect').value;
            const week = document.getElementById('weekSelect').value;
            window.location.href = `dashboard.php?month=${month}&year=${currentYear}&week=${week}`;
        }
        
        function toggleView() {
            alert('Fitur toggle view akan dikembangkan');
        }
        
        function showBookingDetail(bookings) {
            console.log('Booking detail:', bookings);
            // Next step: show modal/detail
        }
    </script>
</body>
</html>