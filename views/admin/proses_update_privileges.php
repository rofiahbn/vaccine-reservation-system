<?php
include "config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_id = mysqli_real_escape_string($conn, $_POST['role_id']);
    
    // Jika ada yang dicentang, gabung jadi string. Jika tidak, kosongkan.
    if (isset($_POST['fitur'])) {
        $privileges = implode(',', $_POST['fitur']);
    } else {
        $privileges = '';
    }

    $sql = "UPDATE roles SET privileges = '$privileges' WHERE id = '$role_id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Hak akses berhasil diperbarui!'); window.location.href='/role-list';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}