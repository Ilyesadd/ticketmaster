<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();


session_start();
session_destroy();
header("Location: admin_login.php");
exit();





?>