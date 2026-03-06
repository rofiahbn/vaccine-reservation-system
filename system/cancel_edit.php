<?php
session_start();

// Clear editing mode
unset($_SESSION['editing_mode']);
unset($_SESSION['editing_index']);

// Redirect ke konfirmasi
header('Location: booking_confirmation.php');
exit;
?>