<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: /modules/dashboard/');
} else {
    header('Location: /modules/auth/login.php');
}
exit();
?>