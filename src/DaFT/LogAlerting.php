<?php
namespace DaFT;

/**
 * Sends alerts to WebEx based on /alerting.ini - if the file doesn't exist, then do nothing. Document me better
 */
class LogAlerting {

    public static function sendAlert(string $message) {
        $configPath = $_SERVER['DOCUMENT_ROOT'] . '/../alerting.ini';
        if (!file_exists($configPath)) {
            return; // Exit silently if config doesn't exist
        }

        $config = parse_ini_file($configPath);
        if (empty($config['bot']) || empty($config['space'])) {
            return; // Exit silently if required config values aren't set
        }

        $botToken = $config['bot'];
        $spaceId = $config['space'];

        $payload = [
            'roomId' => $spaceId,
            'text' => $message
        ];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://webexapis.com/v1/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $botToken",
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $return=curl_exec($ch);
        curl_close($ch);
    }
}