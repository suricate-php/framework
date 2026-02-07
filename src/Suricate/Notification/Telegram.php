<?php

declare(strict_types=1);

namespace Suricate\Notification;

use Suricate\Suricate;

class Telegram
{
    public static function send(string $chatId, string $message, string $token): array
    {
        $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
        $data = ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML'];
        Suricate::Logger()->info('[NOTIFICATION][TELEGRAM] Sending message to ' . $chatId);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($curlErrno) {
            Suricate::Logger()->error('[NOTIFICATION][TELEGRAM] cURL Error: ' . $curlError);
            return ['error' => $curlError];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $result = json_decode($response, true);
            if (isset($result['ok']) && $result['ok']) {
                Suricate::Logger()->info('[NOTIFICATION][TELEGRAM] Message sent to ' . $chatId);
                return ['success' => true];
            } else {
                Suricate::Logger()->error('[NOTIFICATION][TELEGRAM] Failed to send to ' . $chatId . ':' . json_encode($result));
                return ['error' => $result];
            }
        }
        
        Suricate::Logger()->error('[NOTIFICATION][TELEGRAM] Failed to send to ' . $chatId . ': HTTP ' . $httpCode);
        return ['error' => 'HTTP error ' . $httpCode];
    }
}
