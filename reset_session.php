<?php
session_start();

// Clear semua session booking
unset($_SESSION['participants']);
unset($_SESSION['booking_active']);
unset($_SESSION['selected_products_raw']);
unset($_SESSION['editing_mode']);
unset($_SESSION['editing_index']);
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// Atau destroy semua session
session_destroy();

// Redirect ke order.php
header('Location: order.php');
exit;
?>