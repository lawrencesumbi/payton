<?php
session_start();
// Remove the pending email session so the modal disappears
unset($_SESSION['pending_email']);
// Redirect back to register.php
header("Location: register.php");
exit();
?>