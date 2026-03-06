<?php 
include "config/database.php";  
include "content/auth.php";

// Proteksi halaman
if (!hasPrivilege('manage_staff')) {
    echo "<script>
        alert('Anda tidak punya akses ke halaman ini!');
        window.location.href='dashboard';
    </script>";
    exit;
}
?>

<?php include "content/sidebar.php"; ?>

<section id="header">
    Tambah Staff
    <a href="stafflist" class="btn-back">Kembali</a>
</section>

<section class="main-page">

<form id="formStaff">

<div class="form-grid">

<div class="form-group">
<label>Nama Lengkap</label>
<input type="text" name="nama_lengkap" required>
</div>

<div class="form-group">
<label>Gelar</label>
<input type="text" name="gelar">
</div>

<div class="form-group">
<label>SIP</label>
<input type="text" name="sip">
</div>

<div class="form-group">
<label>Username</label>
<input type="text" name="username" required>
</div>

<div class="form-group">
<label>Password</label>
<input type="password" name="password" required>
</div>
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
<input type="number" name="gaji_pokok" required>
</div>

<div class="form-group">
<label>Fee Per Pasien</label>
<input type="number" name="fee_per_pasien" required>
</div>

<div class="form-group full">
<button type="submit">Simpan</button>
</div>

</div>
</form>

</section>

<script>
document.getElementById("formStaff").addEventListener("submit", function(e){
    e.preventDefault();

    let formData = new FormData(this);

    fetch("insert_pegawai", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        alert(data);
        if(data.includes("berhasil")){
            window.location.href = "stafflist";
        }
    })
    .catch(err => {
        alert("Terjadi kesalahan!");
        console.error(err);
    });
});
</script>