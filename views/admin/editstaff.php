 <?php
include "config/database.php";
include "content/auth.php"; // wajib

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil session user
$current_user_id = $_SESSION['id'] ?? 0;

// Cek privilege manage_staff
if (!hasPrivilege('manage_staff')) {
    // Kalau user gak punya manage_staff, ID harus sama dengan dirinya sendiri
     if ($id != $current_user_id) {  // pakai != atau pastikan tipe sama
        echo "<script>
            alert('Anda tidak punya akses untuk mengedit data pegawai lain!');
            window.location.href = 'dashboard';
        </script>";
        exit;
    }
}

// Ambil data staff dari DB
$query = mysqli_query($conn, "SELECT * FROM staff WHERE id=$id");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$data) {
    echo "<script>
        alert('Data pegawai tidak ditemukan!');
        window.location.href = 'dashboard';
    </script>";
    exit;
}

// Sekarang $data aman untuk ditampilkan di form
?>

<?php include "content/sidebar.php"; ?> 

<section id="header">
    Edit Data Staff
    <a href="stafflist"><div class="add-btn">Kembali</div></a>
</section>

<section class="main-page">
    <form method="POST" action="">

        <input type="hidden" name="id" value="<?= $data['id'] ?>"> 
        <div class="form-grid">

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" 
                       value="<?= htmlspecialchars($data['nama_lengkap']) ?>" required>
            </div>

            <div class="form-group">
                <label>Gelar</label>
                <input type="text" name="gelar" 
                       value="<?= htmlspecialchars($data['gelar']) ?>">
            </div>

            <div class="form-group">
                <label>Nomor SIP</label>
                <input type="text" name="sip" 
                       value="<?= htmlspecialchars($data['sip']) ?>">
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" 
                       value="<?= htmlspecialchars($data['username']) ?>" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password"
                       placeholder="Kosongkan jika tidak ingin ganti password">
                <small style="color: gray; font-size: 11px;">
                    *Kosongkan jika tidak ingin mengubah password
                </small>
            </div>

            

             <?php if (hasPrivilege('manage_staff')): ?>
<div class="form-group">
                <label>Role</label>
                <select name="role" required>
					<option value="Super Admin" <?= ($data['role']=='Super Admin')?'selected':'' ?>>Super Admin</option>
                    <option value="Admin" <?= ($data['role']=='admin')?'selected':'' ?>>Admin</option>
                    <option value="Dokter" <?= ($data['role']=='dokter')?'selected':'' ?>>Dokter</option>
                    <option value="Perawat" <?= ($data['role']=='perawat')?'selected':'' ?>>Perawat</option>
                    <option value="Kasir" <?= ($data['role']=='Kasir')?'selected':'' ?>>Kasir</option>
                </select>
            </div>
<div class="form-group">
    <label>Gaji Pokok</label>
    <input type="number" name="gaji_pokok"
           value="<?= $data['gaji_pokok'] ?>" required>
</div>

<div class="form-group">
    <label>Fee Per Pasien</label>
    <input type="number" name="fee_per_pasien"
           value="<?= $data['fee_per_pasien'] ?>" required>
</div>

<?php endif; ?>

            <div class="form-group full" style="margin-top:20px;">
                <button type="submit" name="update" class="btn-submit">
                    Simpan Perubahan
                </button>  
            </div>

        </div>
    </form>
</section>

<?php
// ==============================
// PROSES UPDATE STAFF
// ==============================
if (isset($_POST['update'])) {

    $id             = mysqli_real_escape_string($conn, $_POST['id']);
    $nama_lengkap   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $gelar          = mysqli_real_escape_string($conn, $_POST['gelar']);
    $sip            = mysqli_real_escape_string($conn, $_POST['sip']);
    $username       = mysqli_real_escape_string($conn, $_POST['username']);
    $password       = $_POST['password'];
    $role           = mysqli_real_escape_string($conn, $_POST['role']);
    $gaji_pokok     = mysqli_real_escape_string($conn, $_POST['gaji_pokok']);
    $fee_per_pasien = mysqli_real_escape_string($conn, $_POST['fee_per_pasien']);

    // Cek username duplikat
    $cek = mysqli_query($conn, "SELECT id FROM staff WHERE username='$username' AND id != '$id'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Username sudah digunakan!');</script>";
    } else {

        // Jika password diisi → hash password
        if (!empty($password)) {

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    if (hasPrivilege('manage_staff')) {

        $sql = "UPDATE staff SET
                nama_lengkap='$nama_lengkap',
                gelar='$gelar',
                sip='$sip',
                username='$username',
                password='$password_hash',
                role='$role',
                gaji_pokok='$gaji_pokok',
                fee_per_pasien='$fee_per_pasien'
                WHERE id='$id'";

    } else {

        $sql = "UPDATE staff SET
                nama_lengkap='$nama_lengkap',
                gelar='$gelar',
                sip='$sip',
                username='$username',
                password='$password_hash',
                role='$role'
                WHERE id='$id'";
    }
}

        if (mysqli_query($conn, $sql)) {
            echo "<script>
                    alert('Data berhasil diperbarui');
                    window.location='stafflist';
                  </script>";
        } else {
            echo "<script>alert('Gagal memperbarui data');</script>";
        }
    }
}
?>