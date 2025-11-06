<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

// تطبيق ويب بسيط
$response = new Illuminate\Http\Response(
    '<!DOCTYPE html>
    <html>
    <head>
        <title>Enterprise Pro</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .success { color: #28a745; }
            .info { color: #17a2b8; }
        </style>
    </head>
    <body>
        <h1 class="success">🚀 Enterprise Pro Application</h1>
        <p class="info">✅ Laravel is successfully installed and running!</p>
        <p>📅 Server Time: ' . date('Y-m-d H:i:s') . '</p>
        <p>🔧 Status: <strong>Ready for configuration</strong></p>
    </body>
    </html>'
);

$response->send();
