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
 * Standalone Preset Manager API for MegaOBS Church HUD
 * Manages:
 * - scripturePresets (Verse)
 * - sermonPresets (Sermon & Speaker)
 * - tickerPresets (Marquee Ticker)
 * Stored locally in: C:\xampp\htdocs\megaOBS\data\church_presets.json
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0777, true);
}
$presetsFile = $dataDir . '/church_presets.json';

function getDefaultChurchPresets(): array {
    return [
        'scripturePresets' => [
            [
                'id' => 'sc_1',
                'ref' => 'Mikha 5:1â€“3',
                'version' => 'Alkitab TB',
                'text' => 'Tetapi engkau, hai Betlehem Efrata, hai yang terkecil di antara kaum-kaum Yehuda, dari padamu akan bangkit bagi-Ku seorang yang akan memerintah Israel, yang permulaannya sudah sejak purbakala, sejak dahulu kala.'
            ],
            [
                'id' => 'sc_2',
                'ref' => 'Matius 19:14',
                'version' => 'Alkitab TB',
                'text' => "Tetapi Yesus berkata: 'Biarkanlah anak-anak itu, janganlah menghalang-halangi mereka datang kepada-Ku; sebab orang-orang yang seperti itulah yang empunya Kerajaan Sorga.'"
            ],
            [
                'id' => 'sc_3',
                'ref' => 'Lukas 2:10â€“11',
                'version' => 'Alkitab TB',
                'text' => "Lalu kata malaikat itu kepada mereka: 'Jangan takut, sebab sesungguhnya aku memberitakan kepadamu kesukaan besar untuk seluruh bangsa: Hari ini telah lahir bagimu Juruselamat, yaitu Kristus, Tuhan, di kota Daud.'"
            ],
            [
                'id' => 'sc_4',
                'ref' => 'Amsal 22:6',
                'version' => 'Alkitab TB',
                'text' => 'Didiklah orang muda menurut jalan yang patut baginya, maka pada masa tuanya pun ia tidak akan menyimpang dari pada jalan itu.'
            ],
            [
                'id' => 'sc_5',
                'ref' => 'Mazmur 139:13â€“14',
                'version' => 'Alkitab TB',
                'text' => 'Sebab Engkaulah yang membentuk buah pinggangku, menenun aku dalam kandungan ibuku. Aku bersyukur kepada-Mu oleh karena kejadianku dahsyat dan ajaib; ajaib apa yang Kaubuat, dan jiwaku benar-benar menyadarinya.'
            ],
            [
                'id' => 'sc_6',
                'ref' => 'Yesaya 9:5',
                'version' => 'Alkitab TB',
                'text' => 'Sebab seorang anak telah lahir untuk kita, seorang putera telah diberikan untuk kita; lambang pemerintahan ada di atas bahunya, dan namanya disebutkan orang: Penasihat Ajaib, Allah yang Perkasa, Bapa yang Kekal, Raja Damai.'
            ],
            [
                'id' => 'sc_7',
                'ref' => 'Bilangan 6:24â€“26',
                'version' => 'Alkitab TB',
                'text' => 'TUHAN memberkati engkau dan melindungi engkau; TUHAN menyinari engkau dengan wajah-Nya dan memberi engkau kasih karunia; TUHAN menghadapkan wajah-Nya kepadamu dan memberi engkau damai sejahtera.'
            ]
        ],
        'sermonPresets' => [
            [
                'id' => 'sr_1',
                'title' => 'Aku Kecil, Aku Berharga',
                'speaker' => 'Pta. Tinggih Sulog',
                'role' => 'Pengkhotbah Kebaktian Natal & Graduasi PKK',
                'scripture' => 'Mikha 5:1â€“3',
                'series' => 'Sambutan Natal & Graduasi PKK 2026'
            ],
            [
                'id' => 'sr_2',
                'title' => 'Ucapan & Amanat Graduasi PKK',
                'speaker' => 'Welter Lainor',
                'role' => 'Pengerusi PKK SIB Kawasan Papar',
                'scripture' => 'Amsal 22:6',
                'series' => 'Graduasi Kanak-Kanak 2026'
            ],
            [
                'id' => 'sr_3',
                'title' => 'Kanak-Kanak di Mata Yesus',
                'speaker' => 'Pta. Tinggih Sulog',
                'role' => 'Pengkhotbah Jemputan',
                'scripture' => 'Matius 19:13â€“15',
                'series' => 'Siri Kasih Kristus'
            ],
            [
                'id' => 'sr_4',
                'title' => 'Terang Bintang Betlehem',
                'speaker' => 'Pemimpin Ibadah',
                'role' => 'Pelayanan Kanak-Kanak SIB',
                'scripture' => 'Lukas 2:10â€“14',
                'series' => 'Khabar Sukacita Natal'
            ]
        ],
        'tickerPresets' => [
            [
                'id' => 'tk_1',
                'title' => 'Pengumuman Utama Natal & Graduasi PKK 2026',
                'badge' => 'INFO NATAL & PKK',
                'text' => 'Shalom! Selamat Datang ke Kebaktian Natal & Graduasi PKK SIB Kawasan Papar 2026 â€¢ Tema: "Aku Kecil, Aku Berharga" (Mikha 5:1â€“3) â€¢ Tempat: Gereja SIB Kayau â€¢ Pengkhotbah: Pta. Tinggih Sulog â€¢ Tarikh: 22 Nov 2026 (Ahad 9.30 Pagi) â€¢ Warna Baju: Biru â€¢ Pengerusi PKK: Welter Lainor â€¢ Tuhan Yesus Memberkati!'
            ],
            [
                'id' => 'tk_2',
                'title' => 'Ucapan Selamat Datang Jemaat',
                'badge' => 'SELAMAT DATANG',
                'text' => 'Shalom & Selamat Datang kepada semua Ibu Bapa, Guru-Guru Sekolah Minggu, dan Kanak-Kanak PKK SIB Kawasan Papar â€¢ Sila pastikan telefon bimbit anda berada dalam mod senyap sepanjang ibadah berlangsung.'
            ],
            [
                'id' => 'tk_3',
                'title' => 'Info Persembahan Kasih & Perpuluhan',
                'badge' => 'PERSEMBAHAN KASIH',
                'text' => 'Anda boleh menyalurkan Persembahan Kasih & Perpuluhan melalui Public Bank: 3235424420 (SIB PEKAN PAPAR) atau imbas kod QR DuitNow yang tertera di skrin â€¢ Tuhan memberkati pemberian anda!'
            ],
            [
                'id' => 'tk_4',
                'title' => 'Tahniah Graduan PKK 2026',
                'badge' => 'TAHNIAH GRADUAN',
                'text' => 'Tahniah & Syabas kepada semua Graduan Pelayanan Kanak-Kanak (PKK) SIB Kawasan Papar Tahun 2026! â€¢ "Didiklah orang muda menurut jalan yang patut baginya..." (Amsal 22:6) â€¢ Tuhan Yesus Memberkati!'
            ]
        ]
    ];
}

function loadChurchPresets(): array {
    global $presetsFile;
    $defaults = getDefaultChurchPresets();
    if (!file_exists($presetsFile)) {
        file_put_contents($presetsFile, json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $defaults;
    }
    $raw = @file_get_contents($presetsFile);
    $data = $raw ? json_decode($raw, true) : null;
    if (!is_array($data)) {
        return $defaults;
    }
    return [
        'scripturePresets' => isset($data['scripturePresets']) && is_array($data['scripturePresets']) ? array_values($data['scripturePresets']) : $defaults['scripturePresets'],
        'sermonPresets'    => isset($data['sermonPresets']) && is_array($data['sermonPresets']) ? array_values($data['sermonPresets']) : $defaults['sermonPresets'],
        'tickerPresets'    => isset($data['tickerPresets']) && is_array($data['tickerPresets']) ? array_values($data['tickerPresets']) : $defaults['tickerPresets'],
    ];
}

function saveChurchPresets(array $presets): void {
    global $presetsFile;
    $tmp = $presetsFile . '.tmp';
    file_put_contents($tmp, json_encode($presets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    @rename($tmp, $presetsFile);
}

$rawInput = file_get_contents('php://input');
$body = $rawInput !== '' ? (json_decode($rawInput, true) ?: []) : [];
$req = array_merge($_GET, $_POST, $body);
$action = $req['action'] ?? 'get_presets';

$presets = loadChurchPresets();

$categoryMap = [
    'scripture' => 'scripturePresets',
    'verse'     => 'scripturePresets',
    'sermon'    => 'sermonPresets',
    'ticker'    => 'tickerPresets',
];

switch ($action) {
    case 'get_presets': {
        echo json_encode([
            'ok' => true,
            'presets' => $presets
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    case 'save_preset': {
        $cat = strtolower(trim((string)($req['category'] ?? 'scripture')));
        $key = $categoryMap[$cat] ?? null;
        if (!$key) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid category']);
            exit;
        }

        $item = is_array($req['item'] ?? null) ? $req['item'] : [];
        $id = trim((string)($item['id'] ?? ''));
        if ($id === '') {
            $id = substr($cat, 0, 2) . '_' . bin2hex(random_bytes(4));
        }

        if ($key === 'scripturePresets') {
            $cleanItem = [
                'id'      => $id,
                'ref'     => trim((string)($item['ref'] ?? '')),
                'version' => trim((string)($item['version'] ?? 'Alkitab TB')),
                'text'    => trim((string)($item['text'] ?? '')),
            ];
            if ($cleanItem['ref'] === '' || $cleanItem['text'] === '') {
                echo json_encode(['ok' => false, 'error' => 'Bible Reference and Verse Text are required.']);
                exit;
            }
        } elseif ($key === 'sermonPresets') {
            $cleanItem = [
                'id'        => $id,
                'title'     => trim((string)($item['title'] ?? '')),
                'speaker'   => trim((string)($item['speaker'] ?? '')),
                'role'      => trim((string)($item['role'] ?? '')),
                'scripture' => trim((string)($item['scripture'] ?? '')),
                'series'    => trim((string)($item['series'] ?? '')),
            ];
            if ($cleanItem['title'] === '') {
                echo json_encode(['ok' => false, 'error' => 'Sermon Title is required.']);
                exit;
            }
        } else {
            $cleanItem = [
                'id'    => $id,
                'title' => trim((string)($item['title'] ?? ($item['badge'] ?? 'Ticker Preset'))),
                'badge' => trim((string)($item['badge'] ?? 'INFO GEREJA')),
                'text'  => trim((string)($item['text'] ?? '')),
            ];
            if ($cleanItem['text'] === '') {
                echo json_encode(['ok' => false, 'error' => 'Ticker Announcement Text is required.']);
                exit;
            }
        }

        $list = $presets[$key];
        $found = false;
        foreach ($list as $idx => $existing) {
            if (($existing['id'] ?? '') === $id) {
                $list[$idx] = $cleanItem;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $list[] = $cleanItem;
        }
        $presets[$key] = array_values($list);
        saveChurchPresets($presets);

        echo json_encode([
            'ok' => true,
            'message' => 'Preset saved successfully!',
            'item' => $cleanItem,
            'presets' => $presets
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    case 'delete_preset': {
        $cat = strtolower(trim((string)($req['category'] ?? 'scripture')));
        $key = $categoryMap[$cat] ?? null;
        $id = trim((string)($req['id'] ?? ''));
        if (!$key || $id === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Missing category or preset id']);
            exit;
        }
        $presets[$key] = array_values(array_filter($presets[$key], fn($it) => ($it['id'] ?? '') !== $id));
        saveChurchPresets($presets);

        echo json_encode([
            'ok' => true,
            'message' => 'Preset deleted.',
            'presets' => $presets
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    case 'reset_defaults': {
        $defaults = getDefaultChurchPresets();
        saveChurchPresets($defaults);
        echo json_encode([
            'ok' => true,
            'message' => 'Restored default presets.',
            'presets' => $defaults
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => "Unknown action: {$action}"]);
}
