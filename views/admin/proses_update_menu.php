<?php
include "config/database.php";
include "content/auth.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $parent_id = mysqli_real_escape_string($conn, $_POST['parent_id']);
    $link = mysqli_real_escape_string($conn, $_POST['link']);
    $order = mysqli_real_escape_string($conn, $_POST['order_priority']);

    // 1. Bersihkan link untuk jadi slug (buang .php jika ada)
    $new_slug = str_replace('.php', '', $link);

    // 2. Update tabel menus
    $sql_update_menu = "UPDATE menus SET 
                        title = '$title', 
                        link = '$link', 
                        parent_id = '$parent_id', 
                        order_priority = '$order' 
                        WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql_update_menu)) {
        
        // 3. SINKRONISASI OTOMATIS: Update page_title dan slug di tabel pages
        if ($link != "#") {
            $sql_update_page = "UPDATE pages SET 
                                page_title = '$title', 
                                slug = '$new_slug' 
                                WHERE menu_id = '$id'";
            mysqli_query($conn, $sql_update_page);
        }

        echo "<script>alert('Menu dan Slug Halaman berhasil disinkronkan!'); window.location.href='/web-interface';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}