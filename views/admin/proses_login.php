<?php
session_start();
include "config/database.php";

header('Content-Type: application/json');

// Ambil input
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

// Query ke tabel staff
$query = mysqli_query($conn, "SELECT * FROM staff WHERE username='$username'");

if(mysqli_num_rows($query) == 1){

    $user = mysqli_fetch_assoc($query);

    // Verifikasi password hash
    if(password_verify($password, $user['password'])){

        // Set session
        $_SESSION['login'] = true;
        $_SESSION['id'] = $user['id'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];   // sekarang pakai role string
 

		// Ambil privileges dari tabel roles
		$roleQuery = mysqli_query($conn, 
			"SELECT privileges FROM roles WHERE role_name='".$user['role']."'"
		);

		$roleData = mysqli_fetch_assoc($roleQuery);

		$_SESSION['privileges'] = $roleData['privileges'];
		
		

        // Redirect (sementara semua ke dashboard)
        $redirect = "dashboard";

        echo json_encode([
            "status" => "success",
            "message" => "Login berhasil",
            "redirect" => $redirect
        ]);

    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Password salah"
        ]);
    }

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Username tidak ditemukan"
    ]);
}
?>