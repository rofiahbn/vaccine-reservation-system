<?php
include "config/database.php";
include "content/auth.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $page_title = mysqli_real_escape_string($conn, $_POST['page_title']);
    $content = $_POST['content']; // Jangan di-escape agar HTML tidak rusak (gunakan prepared statement jika ingin lebih aman)

    $sql = "UPDATE pages SET 
            page_title = '$page_title', 
            content = '$content' 
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Halaman berhasil diupdate!'); window.location.href='/web-interface';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}