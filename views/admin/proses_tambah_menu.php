<?php
include "config/database.php";
include "content/auth.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $parent_id = mysqli_real_escape_string($conn, $_POST['parent_id']);
    $link = mysqli_real_escape_string($conn, $_POST['link']);
    $order = mysqli_real_escape_string($conn, $_POST['order_priority']);

    // 1. Simpan ke tabel menus
    $sql_menu = "INSERT INTO menus (title, link, parent_id, order_priority) 
                 VALUES ('$title', '$link', '$parent_id', '$order')";
    
    if (mysqli_query($conn, $sql_menu)) {
        $new_menu_id = mysqli_insert_id($conn);

        // 2. Jika link bukan '#' (artinya ini halaman konten)
        if ($link != "#") {
            // Bersihkan slug (buang .php jika user mengetiknya)
            $slug = str_replace('.php', '', $link);
            
            // Buat baris halaman kosong di tabel pages
            $sql_page = "INSERT INTO pages (menu_id, page_title, slug, content) 
                         VALUES ('$new_menu_id', '$title', '$slug', 'Konten baru sedang disiapkan...')";
            mysqli_query($conn, $sql_page);
        }

        echo "<script>alert('Menu dan Halaman berhasil dibuat!'); window.location.href='/web-interface';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}