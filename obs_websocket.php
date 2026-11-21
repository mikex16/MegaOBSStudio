<?php
/**
 * ============================================================================
 * MEGA OBS STUDIO — Unified One-Screen Church Broadcast, HUD & Lyric System
 * Author / Creator : mikex16 (https://github.com/mikex16/MegaOBSStudio)
 * Ministry Purpose : Created voluntarily for Church "LIVE" broadcasting.
 * License          : MIT License with Attribution Requirement.
 *                    Anyone may use this tool for free as long as the author
 *                    name (mikex16 / MegaOBSStudio) is NOT removed from any script.
 * Disclaimer       : Provided "AS-IS" without warranty of 100% functionality.
 *                    Use at your own risk (Gunakan atas risiko sendiri).
 * ============================================================================
 */
declare(strict_types=1);

/**
 * OBS WebSocket v5 Native PHP Client & Configuration Manager
 * Default target: 192.168.0.13:4455
 */
class ObsConfig
{
    private const CONFIG_FILE = __DIR__ . '/config.json';

    public static function getDefaults(): array
    {
        return [
            'host' => '192.168.0.13',
            'port' => 4455,
            'password' => 'rDT8g8GU37oiRY2C',
            'previewFps' => 8,
            'previewWidth' => 960,
            'previewFormat' => 'jpg',
            'connectionMode' => 'auto', // 'direct', 'php', 'auto'
            'showGrid' => true,
            'showSafeZones' => true,
            'showBoxes' => true,
            'showOsd' => true,
            'macros' => [
                [
                    'id' => 'macro_live_start',
                    'label' => 'GO LIVE + REC',
                    'color' => '#ef4444',
                    'icon' => 'broadcast',
                    'actions' => [
                        ['type' => 'StartStream', 'data' => []],
                        ['type' => 'StartRecord', 'data' => []]
                    ]
                ],
                [
                    'id' => 'macro_cut_transition',
                    'label' => 'QUICK CUT',
                    'color' => '#3b82f6',
                    'icon' => 'cut',
                    'actions' => [
                        ['type' => 'TriggerStudioModeTransition', 'data' => []]
                    ]
                ],
                [
                    'id' => 'macro_save_replay',
                    'label' => 'SAVE REPLAY',
                    'color' => '#10b981',
                    'icon' => 'replay',
                    'actions' => [
                        ['type' => 'SaveReplayBuffer', 'data' => []]
                    ]
                ],
                [
                    'id' => 'macro_mute_all',
                    'label' => 'MUTE MIC/AUX',
                    'color' => '#f59e0b',
                    'icon' => 'mute',
                    'actions' => [
                        ['type' => 'CustomMuteAll', 'data' => ['mute' => true]]
                    ]
                ],
                [
                    'id' => 'macro_unmute_all',
                    'label' => 'UNMUTE ALL',
                    'color' => '#06b6d4',
                    'icon' => 'unmute',
                    'actions' => [
                        ['type' => 'CustomMuteAll', 'data' => ['mute' => false]]
                    ]
                ],
                [
                    'id' => 'macro_stop_all',
                    'label' => 'END STREAM+REC',
                    'color' => '#8b5cf6',
                    'icon' => 'stop',
                    'actions' => [
                        ['type' => 'StopStream', 'data' => []],
                        ['type' => 'StopRecord', 'data' => []]
                    ]
                ]
            ]
        ];
    }

    public static function load(): array
    {
        $defaults = self::getDefaults();
        if (!file_exists(self::CONFIG_FILE)) {
            self::save($defaults);
            return $defaults;
        }
        $raw = @file_get_contents(self::CONFIG_FILE);
        if ($raw === false) {
            return $defaults;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $defaults;
        }
        return array_replace($defaults, $decoded);
    }

    public static function save(array $newConfig): array
    {
        $current = self::exists() ? self::load() : self::getDefaults();
        $merged = array_replace($current, $newConfig);
        $merged['port'] = (int)($merged['port'] ?? 4455);
        @file_put_contents(self::CONFIG_FILE, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return $merged;
    }

    private static function exists(): bool
    {
        return file_exists(self::CONFIG_FILE);
    }
}

class ObsWebSocketClient
{
    private string $host;
    private int $port;
    private string $password;
    /** @var resource|null */
    private $socket = null;
    private float $timeout;
    private array $obsInfo = [];

    public function __construct(string $host = '192.168.0.13', int $port = 4455, string $password = 'rDT8g8GU37oiRY2C', float $timeout = 2.5)
    {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    public static function computeAuthResponse(string $password, string $salt, string $challenge): string
    {
        $secret = base64_encode(hash('sha256', $password . $salt, true));
        return base64_encode(hash('sha256', $secret . $challenge, true));
    }

    public function connect(): array
    {
        $errno = 0;
        $errstr = '';
        $address = sprintf('tcp://%s:%d', $this->host, $this->port);
        $this->socket = @stream_socket_client($address, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            throw new RuntimeException(sprintf('Cannot connect to OBS at %s:%d (%s [%d])', $this->host, $this->port, $errstr ?: 'Connection refused/timeout', $errno));
        }

        $sec_usec = (int)(($this->timeout - floor($this->timeout)) * 1_000_000);
        stream_set_timeout($this->socket, (int)floor($this->timeout), $sec_usec);

        $key = base64_encode(random_bytes(16));
        $headers = [
            "GET / HTTP/1.1",
            "Host: {$this->host}:{$this->port}",
            "Upgrade: websocket",
            "Connection: Upgrade",
            "Sec-WebSocket-Key: {$key}",
            "Sec-WebSocket-Version: 13",
            "Sec-WebSocket-Protocol: obswebsocket.json",
            "User-Agent: OBS-PHP-Canvas-Remote/5.0",
            "\r\n"
        ];
        fwrite($this->socket, implode("\r\n", $headers));

        $responseHeader = '';
        while (!feof($this->socket)) {
            $line = fgets($this->socket, 1024);
            if ($line === false) {
                break;
            }
            $responseHeader .= $line;
            if (rtrim($line) === '') {
                break;
            }
        }

        if (stripos($responseHeader, '101') === false) {
            $this->close();
            throw new RuntimeException('WebSocket HTTP Upgrade failed: ' . trim( strtok($responseHeader, "\r\n") ?: 'Empty response' ));
        }

        // OpCode 0: Hello
        $hello = $this->readJsonMessage();
        if (($hello['op'] ?? null) !== 0) {
            $this->close();
            throw new RuntimeException('Expected OBS OpCode 0 (Hello), got: ' . json_encode($hello));
        }

        $this->obsInfo = $hello['d'] ?? [];
        $identifyData = [
            'rpcVersion' => 1,
            'eventSubscriptions' => 0 // PHP request client doesn't need event stream
        ];

        if (!empty($hello['d']['authentication']['challenge']) && !empty($hello['d']['authentication']['salt'])) {
            $identifyData['authentication'] = self::computeAuthResponse(
                $this->password,
                (string)$hello['d']['authentication']['salt'],
                (string)$hello['d']['authentication']['challenge']
            );
        }

        // Send OpCode 1: Identify
        $this->sendJsonMessage([
            'op' => 1,
            'd' => $identifyData
        ]);

        // Receive OpCode 2: Identified
        $identified = $this->readJsonMessage();
        if (($identified['op'] ?? null) !== 2) {
            $this->close();
            throw new RuntimeException('OBS Authentication failed or rejected (did not receive OpCode 2 Identified). Check password.');
        }

        return [
            'obsWebSocketVersion' => $this->obsInfo['obsWebSocketVersion'] ?? '5.x',
            'rpcVersion' => $identified['d']['negotiatedRpcVersion'] ?? 1
        ];
    }

    public function request(string $requestType, array $requestData = []): array
    {
        if (!$this->socket) {
            $this->connect();
        }

        $requestId = bin2hex(random_bytes(6));
        $payload = [
            'op' => 6,
            'd' => [
                'requestType' => $requestType,
                'requestId' => $requestId,
            ]
        ];
        if (!empty($requestData)) {
            $payload['d']['requestData'] = $requestData;
        }

        $this->sendJsonMessage($payload);

        // Read until OpCode 7 matching requestId
        $maxLoops = 25;
        for ($i = 0; $i < $maxLoops; $i++) {
            $msg = $this->readJsonMessage();
            if (($msg['op'] ?? null) === 7 && ($msg['d']['requestId'] ?? '') === $requestId) {
                return $msg['d'];
            }
        }

        throw new RuntimeException("Timed out waiting for response to {$requestType}");
    }

    public function requestBatch(array $requests, bool $haltOnFailure = false): array
    {
        if (!$this->socket) {
            $this->connect();
        }

        $requestId = 'batch_' . bin2hex(random_bytes(5));
        $formatted = [];
        foreach ($requests as $idx => $req) {
            $item = [
                'requestType' => $req['requestType'],
                'requestId' => $req['requestId'] ?? ('req_' . $idx)
            ];
            if (!empty($req['requestData'])) {
                $item['requestData'] = $req['requestData'];
            }
            $formatted[] = $item;
        }

        $this->sendJsonMessage([
            'op' => 8,
            'd' => [
                'requestId' => $requestId,
                'haltOnFailure' => $haltOnFailure,
                'executionType' => 0,
                'requests' => $formatted
            ]
        ]);

        $maxLoops = 35;
        for ($i = 0; $i < $maxLoops; $i++) {
            $msg = $this->readJsonMessage();
            if (($msg['op'] ?? null) === 9 && ($msg['d']['requestId'] ?? '') === $requestId) {
                return $msg['d']['results'] ?? [];
            }
        }

        throw new RuntimeException('Timed out waiting for OBS RequestBatchResponse');
    }

    private function sendJsonMessage(array $data): void
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        $this->writeFrame($json, 0x1);
    }

    private function writeFrame(string $payload, int $opcode = 0x1): void
    {
        if (!$this->socket) {
            throw new RuntimeException('Socket not connected');
        }
        $length = strlen($payload);
        $frame = chr(0x80 | ($opcode & 0x0F));

        // Client frames MUST be masked per RFC 6455
        if ($length <= 125) {
            $frame .= chr(0x80 | $length);
        } elseif ($length <= 65535) {
            $frame .= chr(0x80 | 126) . pack('n', $length);
        } else {
            $frame .= chr(0x80 | 127) . pack('J', $length);
        }

        $mask = random_bytes(4);
        $frame .= $mask;

        $maskedPayload = '';
        for ($i = 0; $i < $length; $i++) {
            $maskedPayload .= $payload[$i] ^ $mask[$i % 4];
        }

        fwrite($this->socket, $frame . $maskedPayload);
    }

    private function readJsonMessage(): array
    {
        while (true) {
            $frame = $this->readFrame();
            if ($frame['opcode'] === 0x8) { // Close frame
                $code = strlen($frame['payload']) >= 2 ? unpack('n', substr($frame['payload'], 0, 2))[1] : 0;
                $reason = strlen($frame['payload']) > 2 ? substr($frame['payload'], 2) : 'Closed by OBS';
                $this->close();
                throw new RuntimeException("OBS WebSocket closed connection (Code {$code}: {$reason})");
            }
            if ($frame['opcode'] === 0x9) { // Ping -> Pong
                $this->writeFrame($frame['payload'], 0xA);
                continue;
            }
            if ($frame['opcode'] === 0xA) { // Pong
                continue;
            }
            $decoded = json_decode($frame['payload'], true);
            if (!is_array($decoded)) {
                throw new RuntimeException('Invalid JSON payload from OBS WebSocket');
            }
            return $decoded;
        }
    }

    private function readFrame(): array
    {
        $header = $this->readExactBytes(2);
        $firstByte = ord($header[0]);
        $secondByte = ord($header[1]);

        $opcode = $firstByte & 0x0F;
        $isMasked = ($secondByte & 0x80) !== 0;
        $length = $secondByte & 0x7F;

        if ($length === 126) {
            $ext = $this->readExactBytes(2);
            $length = unpack('n', $ext)[1];
        } elseif ($length === 127) {
            $ext = $this->readExactBytes(8);
            $length = unpack('J', $ext)[1];
        }

        $maskKey = $isMasked ? $this->readExactBytes(4) : '';
        $payload = $length > 0 ? $this->readExactBytes((int)$length) : '';

        if ($isMasked && $length > 0) {
            for ($i = 0; $i < $length; $i++) {
                $payload[$i] = $payload[$i] ^ $maskKey[$i % 4];
            }
        }

        return [
            'opcode' => $opcode,
            'payload' => $payload
        ];
    }

    private function readExactBytes(int $length): string
    {
        if (!$this->socket) {
            throw new RuntimeException('Socket not open');
        }
        $buffer = '';
        while (strlen($buffer) < $length) {
            $chunk = fread($this->socket, $length - strlen($buffer));
            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->socket);
                if (!empty($meta['timed_out'])) {
                    throw new RuntimeException('OBS WebSocket read timed out');
                }
                throw new RuntimeException('OBS WebSocket connection closed unexpectedly');
            }
            $buffer .= $chunk;
        }
        return $buffer;
    }

    public function close(): void
    {
        if (is_resource($this->socket)) {
            @fclose($this->socket);
        }
        $this->socket = null;
    }

    public function __destruct()
    {
        $this->close();
    }
}
