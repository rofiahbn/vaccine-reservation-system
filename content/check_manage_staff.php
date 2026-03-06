<?php 

// Jika user tidak punya privilege manage_staff, redirect
if (!hasPrivilege('manage_staff')) {
    echo "<script>
        alert('Anda tidak punya akses ke halaman ini!');
        window.location.href = 'dashboard';
    </script>";
    exit;
}
?>