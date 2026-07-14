<?php
// ============================================
// config/app.php
// ============================================

return [
    'name' => getenv('APP_NAME') ?: 'LaundryKu',
    'version' => '5.0.0',
    'debug' => getenv('APP_DEBUG') ?: true,
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'timezone' => 'Asia/Jakarta',
    'locale' => 'id',
    
    'features' => [
        'whatsapp' => true,
        'loyalty' => true,
        'ai_chatbot' => false,
        'voice_command' => false,
        'dark_mode' => true,
        'blockchain' => false,
        'api' => true,
    ],
];
?>