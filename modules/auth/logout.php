<?php
session_start();
session_destroy();
header('Location: /laundry_lvl1/modules/auth/login.php');
exit;