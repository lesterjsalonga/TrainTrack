<?php
session_start();

// Clear all session variables
session_unset();
session_destroy();

// Clear remember cookie
setcookie("remember_admin", "", time() - 3600, "/");

header("Location: admin_login.php");
exit;
?>
