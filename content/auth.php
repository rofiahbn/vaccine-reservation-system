<?php
// ==============================
// START SESSION
// ==============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==============================
// CEK LOGIN
// ==============================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: admin");
    exit;
}

// ==============================
// GLOBAL USER DATA (TETAP ADA)
// ==============================
$current_user_id   = $_SESSION['id'] ?? null;
$current_username  = $_SESSION['username'] ?? null;
$current_nama      = $_SESSION['nama'] ?? null;
$current_role      = $_SESSION['role'] ?? null;
$current_privileges_raw = $_SESSION['privileges'] ?? '';

// ==============================
// FUNCTION CEK PRIVILEGE (AMAN)
// ==============================
function hasPrivilege($privilege) {

    if (empty($_SESSION['privileges'])) {
        return false;
    }

    $privileges = array_map('trim', explode(',', $_SESSION['privileges']));

    return in_array(trim($privilege), $privileges);
}
?>