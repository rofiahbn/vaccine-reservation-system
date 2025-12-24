<?php
if (!isset($_GET['id_pasien'])) {
    echo "Pasien tidak ditemukan";
    exit;
}

$id_pasien = $_GET['id_pasien'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Jadwal Vaksin</title>
</head>
<body>

<h2>Pilih Jadwal Vaksin</h2>
<p>ID Pasien: <?php echo $id_pasien; ?></p>

<form action="save_schedule.php" method="POST">
    <input type="hidden" name="id_pasien" value="<?php echo $id_pasien; ?>">

    <label>Tanggal Vaksin</label>
    <input type="date" name="tanggal_vaksin" required>

    <button type="submit">Simpan Jadwal</button>
</form>

</body>
</html>
