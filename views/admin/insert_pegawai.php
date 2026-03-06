<?php
include "config/database.php";
include "content/auth.php";

// Cek privilege lagi (ANTI BYPASS)
if (!hasPrivilege('manage_staff')) {
    die("Anda tidak punya akses.");
}

$nama      = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
$gelar     = mysqli_real_escape_string($conn, $_POST['gelar']);
$sip       = mysqli_real_escape_string($conn, $_POST['sip']);
$username  = mysqli_real_escape_string($conn, $_POST['username']);
$password  = $_POST['password'];
$role      = mysqli_real_escape_string($conn, $_POST['role']);
$gaji      = intval($_POST['gaji_pokok']);
$fee       = intval($_POST['fee_per_pasien']);

// Cek username duplicate
$cek = mysqli_query($conn, "SELECT id FROM staff WHERE username='$username'");
if(mysqli_num_rows($cek) > 0){
    die("Username sudah digunakan!");
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert
$sql = "INSERT INTO staff
        (nama_lengkap, gelar, sip, username, password, role, gaji_pokok, fee_per_pasien)
        VALUES
        ('$nama', '$gelar', '$sip', '$username', '$password_hash', '$role', '$gaji', '$fee')";

if(mysqli_query($conn, $sql)){
    echo "Data staff berhasil ditambahkan";
}else{
    echo "Gagal menambahkan data";
}