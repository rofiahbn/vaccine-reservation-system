<?php
session_start();
echo "<h1>Debug Session</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "<hr>";
echo "<a href='order.php'>Kembali ke Order</a><br>";
echo "<a href='add_participant.php'>Ke Add Participant</a><br>";
echo "<a href='booking_confirmation.php'>Ke Konfirmasi</a><br>";
echo "<hr>";
echo "<form method='post' action='clear_session.php'>";
echo "<button type='submit'>Clear Session</button>";
echo "</form>";
?>