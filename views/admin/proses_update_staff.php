<?php
include "config/database.php"; 
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $nip = mysqli_real_escape_string($conn, $_POST['nip']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $pangkat = mysqli_real_escape_string($conn, $_POST['pangkat']);
    $tempat_lahir = mysqli_real_escape_string($conn, $_POST['tempat_lahir']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $role_id = $_POST['role_id'];

    // Cek apakah password diisi (artinya ingin ganti password)
    $password_query = "";
    if(!empty($_POST['password'])){
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $password_query = ", password='$pass'";
    }

    $sql = "UPDATE pegawai SET 
            nama='$nama', 
            username='$username', 
            email='$email', 
            nip='$nip', 
            jabatan='$jabatan', 
            pangkat='$pangkat', 
            tempat_lahir='$tempat_lahir', 
            tanggal_lahir='$tanggal_lahir', 
            alamat='$alamat', 
            no_hp='$no_hp', 
            role_id='$role_id' 
            $password_query
            WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Data pegawai berhasil diperbarui']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
}
?>