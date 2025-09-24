<?php

// Laravel email test script
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('This is a test email from Alchy Inventory System to verify email functionality.', function($message) {
        $message->to('system.intelligence2025@gmail.com')
                ->subject('Test Email from Alchy Inventory System');
    });

    echo "Email sent successfully to: system.intelligence2025@gmail.com\n";
    echo "Check storage/logs/laravel.log for the email content.\n";
} catch (Exception $e) {
    echo "Failed to send email: " . $e->getMessage() . "\n";
}