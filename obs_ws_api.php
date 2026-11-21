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

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/obs_websocket.php';

$config = ObsConfig::load();
$rawInput = file_get_contents('php://input');
$jsonBody = !empty($rawInput) ? json_decode($rawInput, true) : [];
if (!is_array($jsonBody)) {
    $jsonBody = [];
}

$action = $_GET['action'] ?? $jsonBody['action'] ?? 'get_config';

$host = (string)($jsonBody['host'] ?? $_GET['host'] ?? $config['host'] ?? '192.168.0.13');
$port = (int)($jsonBody['port'] ?? $_GET['port'] ?? $config['port'] ?? 4455);
$password = (string)($jsonBody['password'] ?? $_GET['password'] ?? $config['password'] ?? 'rDT8g8GU37oiRY2C');

try {
    switch ($action) {
        case 'get_config':
            echo json_encode([
                'ok' => true,
                'config' => $config,
                'serverTime' => date('c'),
                'phpVersion' => PHP_VERSION
            ]);
            break;

        case 'save_config':
            $allowedKeys = [
                'host', 'port', 'password', 'previewFps', 'previewWidth',
                'previewFormat', 'connectionMode', 'showGrid', 'showSafeZones',
                'showBoxes', 'showOsd', 'macros'
            ];
            $updates = [];
            foreach ($allowedKeys as $key) {
                if (array_key_exists($key, $jsonBody)) {
                    $updates[$key] = $jsonBody[$key];
                }
            }
            $saved = ObsConfig::save($updates);
            echo json_encode([
                'ok' => true,
                'config' => $saved
            ]);
            break;

        case 'compute_auth':
            $salt = (string)($jsonBody['salt'] ?? $_GET['salt'] ?? '');
            $challenge = (string)($jsonBody['challenge'] ?? $_GET['challenge'] ?? '');
            echo json_encode([
                'ok' => true,
                'auth' => ObsWebSocketClient::computeAuthResponse($password, $salt, $challenge)
            ]);
            break;

        case 'ping':
            $start = microtime(true);
            $client = new ObsWebSocketClient($host, $port, $password, 2.0);
            $handshake = $client->connect();
            $versionResp = $client->request('GetVersion');
            $client->close();
            $latencyMs = round((microtime(true) - $start) * 1000, 1);

            echo json_encode([
                'ok' => true,
                'latencyMs' => $latencyMs,
                'handshake' => $handshake,
                'version' => $versionResp['responseData'] ?? []
            ]);
            break;

        case 'request':
            $requestType = (string)($jsonBody['requestType'] ?? $_GET['requestType'] ?? '');
            $requestData = is_array($jsonBody['requestData'] ?? null) ? $jsonBody['requestData'] : [];
            if ($requestType === '') {
                throw new InvalidArgumentException('Missing requestType');
            }
            $client = new ObsWebSocketClient($host, $port, $password, 3.0);
            $client->connect();
            $resp = $client->request($requestType, $requestData);
            $client->close();

            echo json_encode([
                'ok' => (bool)($resp['requestStatus']['result'] ?? false),
                'status' => $resp['requestStatus'] ?? [],
                'data' => $resp['responseData'] ?? []
            ]);
            break;

        case 'batch':
            $requests = is_array($jsonBody['requests'] ?? null) ? $jsonBody['requests'] : [];
            if (empty($requests)) {
                throw new InvalidArgumentException('Empty batch requests');
            }
            $client = new ObsWebSocketClient($host, $port, $password, 3.5);
            $client->connect();
            $results = $client->requestBatch($requests, false);
            $client->close();

            echo json_encode([
                'ok' => true,
                'results' => $results
            ]);
            break;

        case 'full_state':
            $includeScreenshot = !empty($jsonBody['includeScreenshot']) || !empty($_GET['includeScreenshot']);
            $previewWidth = (int)($jsonBody['previewWidth'] ?? $config['previewWidth'] ?? 960);

            $client = new ObsWebSocketClient($host, $port, $password, 3.0);
            $client->connect();

            $batch = [
                ['requestType' => 'GetVersion', 'requestId' => 'version'],
                ['requestType' => 'GetStats', 'requestId' => 'stats'],
                ['requestType' => 'GetVideoSettings', 'requestId' => 'video'],
                ['requestType' => 'GetSceneList', 'requestId' => 'scenes'],
                ['requestType' => 'GetSceneTransitionList', 'requestId' => 'transitions'],
                ['requestType' => 'GetStudioModeEnabled', 'requestId' => 'studioMode'],
                ['requestType' => 'GetStreamStatus', 'requestId' => 'stream'],
                ['requestType' => 'GetRecordStatus', 'requestId' => 'record'],
                ['requestType' => 'GetVirtualCamStatus', 'requestId' => 'vcam'],
                ['requestType' => 'GetReplayBufferStatus', 'requestId' => 'replay'],
                ['requestType' => 'GetInputList', 'requestId' => 'inputs'],
                ['requestType' => 'GetSpecialInputs', 'requestId' => 'specialInputs'],
                ['requestType' => 'GetProfileList', 'requestId' => 'profiles'],
                ['requestType' => 'GetSceneCollectionList', 'requestId' => 'collections']
            ];

            $results = $client->requestBatch($batch, false);
            $map = [];
            foreach ($results as $item) {
                $id = $item['requestId'] ?? '';
                if ($id !== '' && !empty($item['requestStatus']['result'])) {
                    $map[$id] = $item['responseData'] ?? [];
                }
            }

            $currentProgramScene = $map['scenes']['currentProgramSceneName'] ?? '';
            $currentPreviewScene = $map['scenes']['currentPreviewSceneName'] ?? $currentProgramScene;

            $secondBatch = [];
            if ($currentProgramScene !== '') {
                $secondBatch[] = [
                    'requestType' => 'GetSceneItemList',
                    'requestId' => 'sceneItems',
                    'requestData' => ['sceneName' => $currentProgramScene]
                ];
                if ($includeScreenshot) {
                    $secondBatch[] = [
                        'requestType' => 'GetSourceScreenshot',
                        'requestId' => 'programScreenshot',
                        'requestData' => [
                            'sourceName' => $currentProgramScene,
                            'imageFormat' => 'jpg',
                            'imageWidth' => $previewWidth,
                            'imageCompressionQuality' => 75
                        ]
                    ];
                }
            }

            if (!empty($map['studioMode']['studioModeEnabled']) && $currentPreviewScene !== '' && $currentPreviewScene !== $currentProgramScene && $includeScreenshot) {
                $secondBatch[] = [
                    'requestType' => 'GetSourceScreenshot',
                    'requestId' => 'previewScreenshot',
                    'requestData' => [
                        'sourceName' => $currentPreviewScene,
                        'imageFormat' => 'jpg',
                        'imageWidth' => $previewWidth,
                        'imageCompressionQuality' => 72
                    ]
                ];
            }

            // Also fetch volume/mute for inputs
            $inputs = $map['inputs']['inputs'] ?? [];
            foreach ($inputs as $idx => $inp) {
                $iName = $inp['inputName'] ?? '';
                if ($iName !== '') {
                    $secondBatch[] = [
                        'requestType' => 'GetInputVolume',
                        'requestId' => 'vol_' . $idx,
                        'requestData' => ['inputName' => $iName]
                    ];
                    $secondBatch[] = [
                        'requestType' => 'GetInputMute',
                        'requestId' => 'mute_' . $idx,
                        'requestData' => ['inputName' => $iName]
                    ];
                }
            }

            if (!empty($secondBatch)) {
                $res2 = $client->requestBatch($secondBatch, false);
                foreach ($res2 as $item) {
                    $id = $item['requestId'] ?? '';
                    if ($id === 'sceneItems' && !empty($item['requestStatus']['result'])) {
                        $map['sceneItems'] = $item['responseData']['sceneItems'] ?? [];
                    } elseif ($id === 'programScreenshot' && !empty($item['requestStatus']['result'])) {
                        $map['programScreenshot'] = $item['responseData']['imageData'] ?? null;
                    } elseif ($id === 'previewScreenshot' && !empty($item['requestStatus']['result'])) {
                        $map['previewScreenshot'] = $item['responseData']['imageData'] ?? null;
                    } elseif (str_starts_with($id, 'vol_') && !empty($item['requestStatus']['result'])) {
                        $i = (int)substr($id, 4);
                        if (isset($inputs[$i])) {
                            $inputs[$i]['inputVolumeDb'] = $item['responseData']['inputVolumeDb'] ?? 0.0;
                            $inputs[$i]['inputVolumeMul'] = $item['responseData']['inputVolumeMul'] ?? 1.0;
                            $inputs[$i]['hasAudio'] = true;
                        }
                    } elseif (str_starts_with($id, 'mute_') && !empty($item['requestStatus']['result'])) {
                        $i = (int)substr($id, 5);
                        if (isset($inputs[$i])) {
                            $inputs[$i]['inputMuted'] = (bool)($item['responseData']['inputMuted'] ?? false);
                        }
                    }
                }
                $map['inputs']['inputs'] = $inputs;
            }

            $client->close();

            echo json_encode([
                'ok' => true,
                'state' => $map
            ]);
            break;

        case 'save_canvas_snapshot':
            $imageData = (string)($jsonBody['imageData'] ?? '');
            if (!str_starts_with($imageData, 'data:image/')) {
                throw new InvalidArgumentException('Invalid image data URI');
            }
            $parts = explode(',', $imageData, 2);
            $binary = base64_decode($parts[1] ?? '', true);
            if ($binary === false) {
                throw new InvalidArgumentException('Base64 decode failed');
            }
            $dir = __DIR__ . '/snapshots';
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $filename = 'obs_canvas_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(2)), 0, 4) . '.png';
            $filepath = $dir . '/' . $filename;
            file_put_contents($filepath, $binary);

            echo json_encode([
                'ok' => true,
                'filename' => $filename,
                'url' => 'snapshots/' . $filename,
                'bytes' => strlen($binary)
            ]);
            break;

        default:
            throw new InvalidArgumentException("Unknown API action: {$action}");
    }
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'host' => $host,
        'port' => $port
    ]);
}
