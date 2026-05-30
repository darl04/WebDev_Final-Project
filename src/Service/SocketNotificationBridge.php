<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Sends real-time notifications to the Node.js Socket.io server (socket-server/).
 */
class SocketNotificationBridge
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $socketServerUrl = '',
    ) {
    }

    public function notifyUser(
        ?string $username,
        string $title,
        string $message,
        string $type = 'info',
    ): void {
        if ($this->socketServerUrl === '' || $username === null || $username === '') {
            return;
        }

        try {
            $this->httpClient->request(
                'POST',
                rtrim($this->socketServerUrl, '/').'/api/notify',
                [
                    'json' => [
                        'username' => $username,
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                    ],
                    'timeout' => 2,
                ],
            );
        } catch (\Throwable) {
            // Socket server is optional; do not break web/API flows.
        }
    }

    public function broadcast(string $title, string $message, string $type = 'info'): void
    {
        if ($this->socketServerUrl === '') {
            return;
        }

        try {
            $this->httpClient->request(
                'POST',
                rtrim($this->socketServerUrl, '/').'/api/notify',
                [
                    'json' => [
                        'title' => $title,
                        'message' => $message,
                        'type' => $type,
                        'broadcast' => true,
                    ],
                    'timeout' => 2,
                ],
            );
        } catch (\Throwable) {
        }
    }
}
