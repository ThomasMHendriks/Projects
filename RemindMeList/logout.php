<?php
// Log the currently logged in user out
session_start();
session_destroy();
header("location: login.php");
exit;