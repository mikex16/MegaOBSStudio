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
/**
 * SIB Pekan Papar - Tablet / Remote Control Cross-Network Sync Gateway
 * Handles live sync for Apache / XAMPP environments
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0777, true);
}
$stateFile = $dataDir . '/church_state.json';

function attachLyricCollisionMeta(array $state): array {
    $obsStateFile = __DIR__ . '/obs_state.json';
    $obsConfigFile = __DIR__ . '/obs_config.json';

    $obsSt = file_exists($obsStateFile) ? (json_decode((string)@file_get_contents($obsStateFile), true) ?: []) : [];
    $obsCfg = file_exists($obsConfigFile) ? (json_decode((string)@file_get_contents($obsConfigFile), true) ?: []) : [];

    $slide = $obsSt['active_slide'] ?? [];
    $r1 = trim((string)($slide['region1'] ?? ''));
    $r2 = trim((string)($slide['region2'] ?? ''));
    $hasText = ($r1 !== '' || $r2 !== '');
    $lineCount = ($r1 !== '' ? count(preg_split('/\r\n|\r|\n/', $r1)) : 0) + ($r2 !== '' ? count(preg_split('/\r\n|\r|\n/', $r2)) : 0);

    $lyricOnScreen = !empty($obsSt['show_running']) && empty($obsSt['is_black']) && empty($obsSt['is_clear']) && $hasText;
    $lyricPosition = strtolower(trim((string)($obsCfg['position'] ?? 'bottom')));
    $lyricFontSize = (int)($obsCfg['font_size'] ?? 42);
    $lyricTheme    = strtolower(trim((string)($obsCfg['theme'] ?? 'glass')));

    $state['lyricOnScreen']  = $lyricOnScreen;
    $state['lyricPosition']  = $lyricPosition;
    $state['lyricFontSize']  = $lyricFontSize;
    $state['lyricLineCount'] = max(1, $lineCount);
    $state['lyricTheme']     = $lyricTheme;
    $state['lyricUpdatedMs'] = (int)($obsSt['updated_at_ms'] ?? 0);
    return $state;
}

// Handle POST actions from Tablet / Controller
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $state = file_exists($stateFile) ? json_decode(file_get_contents($stateFile), true) : [];
    if (!is_array($state)) $state = [];

    if (isset($data['action'])) {
        $state['version'] = ($state['version'] ?? 0) + 1;
        $state['lastUpdated'] = time();

        if ($data['action'] === 'SWITCH_SCENE' && isset($data['scene'])) {
            $state['scene'] = $data['scene'];
        } elseif ($data['action'] === 'SET_THEME' && isset($data['theme'])) {
            $state['theme'] = $data['theme'];
        } elseif ($data['action'] === 'TOGGLE_TICKER') {
            $nextVal = isset($data['visible']) ? (bool)$data['visible'] : !($state['tickerVisible'] ?? true);
            $state['tickerVisible'] = $nextVal;
            if ($nextVal) {
                $state['speakerLtVisible'] = false;
                $state['scriptureVisible'] = false;
                $state['worshipVisible'] = false;
                $state['givingLtVisible'] = false;
            }
        } elseif ($data['action'] === 'TOGGLE_SPEAKER_LT') {
            $nextVal = isset($data['visible']) ? (bool)$data['visible'] : !($state['speakerLtVisible'] ?? false);
            $state['speakerLtVisible'] = $nextVal;
            if ($nextVal) {
                $state['tickerVisible'] = false;
                $state['scriptureVisible'] = false;
                $state['worshipVisible'] = false;
                $state['givingLtVisible'] = false;
            }
        } elseif ($data['action'] === 'TOGGLE_SCRIPTURE_HUD') {
            $nextVal = isset($data['visible']) ? (bool)$data['visible'] : !($state['scriptureVisible'] ?? false);
            $state['scriptureVisible'] = $nextVal;
            if ($nextVal) {
                $state['tickerVisible'] = false;
                $state['speakerLtVisible'] = false;
                $state['worshipVisible'] = false;
                $state['givingLtVisible'] = false;
            }
        } elseif ($data['action'] === 'TOGGLE_WORSHIP_HUD') {
            $nextVal = isset($data['visible']) ? (bool)$data['visible'] : !($state['worshipVisible'] ?? false);
            $state['worshipVisible'] = $nextVal;
            if ($nextVal) {
                $state['tickerVisible'] = false;
                $state['speakerLtVisible'] = false;
                $state['scriptureVisible'] = false;
                $state['givingLtVisible'] = false;
            }
        } elseif ($data['action'] === 'TOGGLE_GIVING_LT') {
            $nextVal = isset($data['visible']) ? (bool)$data['visible'] : !($state['givingLtVisible'] ?? false);
            $state['givingLtVisible'] = $nextVal;
            if ($nextVal) {
                $state['tickerVisible'] = false;
                $state['speakerLtVisible'] = false;
                $state['scriptureVisible'] = false;
                $state['worshipVisible'] = false;
            }
        } elseif ($data['action'] === 'PUSH_SCRIPTURE') {
            if (isset($data['ref'])) $state['scriptureRef'] = $data['ref'];
            if (isset($data['text'])) $state['scriptureText'] = $data['text'];
            if (isset($data['version'])) $state['scriptureVersion'] = $data['version'];
            if (isset($data['scriptureFontSize'])) $state['scriptureFontSize'] = (int)$data['scriptureFontSize'];
            if (isset($data['scriptureFontFamily'])) $state['scriptureFontFamily'] = (string)$data['scriptureFontFamily'];
            if (isset($data['scriptureStyle'])) $state['scriptureStyle'] = (string)$data['scriptureStyle'];
            $state['scriptureVisible'] = true;
            $state['tickerVisible'] = false;
            $state['speakerLtVisible'] = false;
            $state['worshipVisible'] = false;
            $state['givingLtVisible'] = false;
        } elseif ($data['action'] === 'PUSH_WORSHIP_LYRIC') {
            if (isset($data['songTitle'])) $state['worshipSongTitle'] = $data['songTitle'];
            if (isset($data['artist'])) $state['worshipArtist'] = $data['artist'];
            if (isset($data['l1'])) $state['worshipLine1'] = $data['l1'];
            if (isset($data['l2'])) $state['worshipLine2'] = $data['l2'];
            $state['worshipVisible'] = true;
            $state['tickerVisible'] = false;
            $state['speakerLtVisible'] = false;
            $state['scriptureVisible'] = false;
            $state['givingLtVisible'] = false;
        } elseif ($data['action'] === 'TIMER_ACTION') {
            $state['timerCommand'] = $data['command'];
            $state['timerSeconds'] = $data['seconds'] ?? null;
            $state['timerTimestamp'] = round(microtime(true) * 1000);
        } elseif ($data['action'] === 'UPDATE_TICKER_BADGE' && isset($data['badge'])) {
            $state['tickerBadge'] = $data['badge'];
        } elseif ($data['action'] === 'HIDE_ALL_LT') {
            $state['tickerVisible'] = false;
            $state['speakerLtVisible'] = false;
            $state['scriptureVisible'] = false;
            $state['worshipVisible'] = false;
            $state['givingLtVisible'] = false;
        } elseif ($data['action'] === 'UPDATE_FIELD' && isset($data['field'])) {
            $state[$data['field']] = $data['value'] ?? '';
        } elseif ($data['action'] === 'UPDATE_BULK' && isset($data['payload']) && is_array($data['payload'])) {
            $state = array_merge($state, $data['payload']);
        }
    }

    file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT));
    $stateOut = attachLyricCollisionMeta($state);
    echo json_encode(['success' => true, 'action' => $data['action'] ?? 'none', 'state' => $stateOut]);
    exit;
}

// GET request: return state
$state = file_exists($stateFile) ? json_decode(file_get_contents($stateFile), true) : [];
if (!is_array($state)) $state = [];
$stateOut = attachLyricCollisionMeta($state);
echo json_encode(['success' => true, 'state' => $stateOut]);
