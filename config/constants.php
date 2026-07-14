<?php
// ============================================
// config/constants.php
// ============================================

// App
define('APP_NAME', getenv('APP_NAME') ?: 'LaundryKu');
define('APP_VERSION', '5.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

// Path - kosongkan untuk Railway
define('BASE_PATH', '');

// Loyalty
define('POINTS_PER_KG', 100);
define('POINTS_VALUE', 100);
define('MIN_POINTS_REDEEM', 500);

// WhatsApp
define('WHATSAPP_API_URL', 'https://api.fonnte.com/send');
define('WHATSAPP_TOKEN', getenv('WHATSAPP_TOKEN') ?: '');

// Blockchain
define('BLOCKCHAIN_DIFFICULTY', 4);
?>