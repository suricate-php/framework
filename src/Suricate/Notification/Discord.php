<?php

declare(strict_types=1);

namespace Suricate\Notification;

use Suricate\Suricate;

class Discord
{
    public static function send(string $webhookUrl, string $message): array
    {
        $data = ['content' => $message];
        Suricate::Logger()->info('[NOTIFICATION][DISCORD] Sending message via webhook');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($curlErrno) {
            Suricate::Logger()->error('[NOTIFICATION][DISCORD] cURL Error: ' . $curlError);
            return ['error' => $curlError];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            Suricate::Logger()->info('[NOTIFICATION][DISCORD] Message sent');
            return ['success' => true];
        }
        
        Suricate::Logger()->error('[NOTIFICATION][DISCORD] Failed to send: HTTP ' . $httpCode . ' ' . $response);
        return ['error' => 'HTTP error ' . $httpCode];
    }
}