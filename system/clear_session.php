<?php
session_start();
session_destroy();
header('Location: debug_session.php');
exit;
?>