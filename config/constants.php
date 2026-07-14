<?php
define('APP_NAME', 'LaundryKu');
define('APP_VERSION', '5.0.0');
define('POINTS_PER_KG', 100);
define('POINTS_VALUE', 100);
define('MIN_POINTS_REDEEM', 500);
define('WHATSAPP_API_URL', 'https://api.fonnte.com/send');
define('WHATSAPP_TOKEN', getenv('WHATSAPP_TOKEN') ?: '');
define('BLOCKCHAIN_DIFFICULTY', 4);