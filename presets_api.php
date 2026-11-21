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

function getAdminAuthFile(): string {
    global $dataDir;
    return $dataDir . '/admin_auth.json';
}

function verifyDataMgrPassword(string $inputPw): bool {
    $authFile = getAdminAuthFile();
    $defaultPw = 'mike16';
    if (!file_exists($authFile)) {
        $init = [
            'password_hash' => password_hash($defaultPw, PASSWORD_DEFAULT),
            'updatedAt'     => date('c')
        ];
        file_put_contents($authFile, json_encode($init, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return ($inputPw === $defaultPw);
    }
    $data = json_decode((string)@file_get_contents($authFile), true);
    $hash = (string)($data['password_hash'] ?? '');
    if ($hash === '') {
        return ($inputPw === $defaultPw);
    }
    return password_verify($inputPw, $hash);
}

function setDataMgrPassword(string $newPw): void {
    $authFile = getAdminAuthFile();
    $init = [
        'password_hash' => password_hash($newPw, PASSWORD_DEFAULT),
        'updatedAt'     => date('c')
    ];
    file_put_contents($authFile, json_encode($init, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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

    case 'get_about_data': {
        $contactFile = $dataDir . '/about_contact.json';
        $inquiriesFile = $dataDir . '/contact_inquiries.json';

        $contact = file_exists($contactFile) ? (json_decode((string)@file_get_contents($contactFile), true) ?: []) : [];
        $inquiries = file_exists($inquiriesFile) ? (json_decode((string)@file_get_contents($inquiriesFile), true) ?: []) : [];

        $dbFiles = glob(__DIR__ . '/databases/*.db') ?: [];
        echo json_encode([
            'ok' => true,
            'contact' => $contact,
            'inquiries' => array_values($inquiries),
            'stats' => [
                'sqliteDbCount' => count($dbFiles),
                'scripturePresetCount' => count($presets['scripturePresets'] ?? []),
                'sermonPresetCount' => count($presets['sermonPresets'] ?? []),
                'tickerPresetCount' => count($presets['tickerPresets'] ?? []),
                'inquiryCount' => count($inquiries)
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    case 'verify_admin_password': {
        $pw = trim((string)($req['adminPassword'] ?? ''));
        if (verifyDataMgrPassword($pw)) {
            echo json_encode(['ok' => true, 'message' => 'Akses Pengurusan Data dibuka!']);
        } else {
            echo json_encode(['ok' => false, 'error' => 'Password salah! Sila cuba lagi.']);
        }
        break;
    }

    case 'change_admin_password': {
        $curPw = trim((string)($req['currentPassword'] ?? ''));
        $newPw = trim((string)($req['newPassword'] ?? ''));
        if (!verifyDataMgrPassword($curPw)) {
            echo json_encode(['ok' => false, 'error' => 'Password semasa tidak sah!']);
            exit;
        }
        if (strlen($newPw) < 4) {
            echo json_encode(['ok' => false, 'error' => 'Password baru mestilah sekurang-kurangnya 4 aksara.']);
            exit;
        }
        setDataMgrPassword($newPw);
        echo json_encode(['ok' => true, 'message' => 'Password Pengurusan Data berjaya ditukar!']);
        break;
    }

    case 'save_contact_profile': {
        $pw = trim((string)($req['adminPassword'] ?? ''));
        if (!verifyDataMgrPassword($pw)) {
            echo json_encode(['ok' => false, 'error' => 'Sesi terkunci atau password tidak sah!']);
            exit;
        }
        $contactFile = $dataDir . '/about_contact.json';
        $existing = file_exists($contactFile) ? (json_decode((string)@file_get_contents($contactFile), true) ?: []) : [];
        $profile = is_array($req['profile'] ?? null) ? $req['profile'] : [];

        $cleanWa = preg_replace('/[^0-9]/', '', (string)($profile['whatsapp'] ?? ($existing['whatsapp'] ?? '0166841604')));
        $waIntl = $cleanWa;
        if (str_starts_with($waIntl, '0')) {
            $waIntl = '6' . $waIntl;
        }

        $web = trim((string)($profile['website'] ?? ($existing['website'] ?? 'pprz.net')));
        $webUrl = $web;
        if ($webUrl !== '' && !preg_match('#^https?://#i', $webUrl)) {
            $webUrl = 'https://' . $webUrl;
        }

        $updated = array_merge($existing, [
            'creatorName'  => trim((string)($profile['creatorName'] ?? ($existing['creatorName'] ?? 'MIKE'))),
            'handle'       => trim((string)($profile['handle'] ?? ($existing['handle'] ?? 'mikex16'))),
            'greetingTag'  => trim((string)($profile['greetingTag'] ?? ($existing['greetingTag'] ?? 'Just Call Me "MIKE"'))),
            'website'      => $web,
            'websiteUrl'   => $webUrl,
            'whatsapp'     => trim((string)($profile['whatsapp'] ?? ($existing['whatsapp'] ?? '0166841604'))),
            'whatsappIntl' => $waIntl,
            'email'        => trim((string)($profile['email'] ?? ($existing['email'] ?? 'mikex16@gmail.com'))),
            'promoBm'      => trim((string)($profile['promoBm'] ?? ($existing['promoBm'] ?? ''))),
            'promoEn'      => trim((string)($profile['promoEn'] ?? ($existing['promoEn'] ?? ''))),
            'updatedAt'    => date('c')
        ]);

        file_put_contents($contactFile, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        echo json_encode([
            'ok' => true,
            'message' => 'Contact & Service Profile saved!',
            'contact' => $updated
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    case 'save_inquiry': {
        $inquiriesFile = $dataDir . '/contact_inquiries.json';
        $inquiries = file_exists($inquiriesFile) ? (json_decode((string)@file_get_contents($inquiriesFile), true) ?: []) : [];
        if (!is_array($inquiries)) $inquiries = [];

        $inq = is_array($req['inquiry'] ?? null) ? $req['inquiry'] : [];
        $newRecord = [
            'id'            => 'inq_' . bin2hex(random_bytes(4)),
            'clientName'    => trim((string)($inq['clientName'] ?? 'Tetamu / Guest')),
            'clientContact' => trim((string)($inq['clientContact'] ?? '-')),
            'category'      => trim((string)($inq['category'] ?? 'Website / App / IoT')),
            'details'       => trim((string)($inq['details'] ?? '')),
            'channel'       => trim((string)($inq['channel'] ?? 'Local Log')),
            'createdAt'     => date('Y-m-d H:i:s')
        ];
        array_unshift($inquiries, $newRecord);
        file_put_contents($inquiriesFile, json_encode(array_values($inquiries), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        echo json_encode([
            'ok' => true,
            'message' => 'Inquiry recorded!',
            'record' => $newRecord,
            'inquiries' => array_values($inquiries)
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    case 'delete_inquiry': {
        $pw = trim((string)($req['adminPassword'] ?? ''));
        if (!verifyDataMgrPassword($pw)) {
            echo json_encode(['ok' => false, 'error' => 'Sesi terkunci atau password tidak sah!']);
            exit;
        }
        $inquiriesFile = $dataDir . '/contact_inquiries.json';
        $inquiries = file_exists($inquiriesFile) ? (json_decode((string)@file_get_contents($inquiriesFile), true) ?: []) : [];
        $id = trim((string)($req['id'] ?? ''));
        $inquiries = array_values(array_filter($inquiries, fn($it) => ($it['id'] ?? '') !== $id));
        file_put_contents($inquiriesFile, json_encode($inquiries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        echo json_encode([
            'ok' => true,
            'inquiries' => $inquiries
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    case 'export_backup': {
        $contactFile   = $dataDir . '/about_contact.json';
        $inquiriesFile = $dataDir . '/contact_inquiries.json';
        $hudStateFile  = $dataDir . '/church_state.json';
        $obsCfgFile    = __DIR__ . '/obs_config.json';

        $bundle = [
            'app'             => 'MegaOBS Studio',
            'author'          => 'MIKE (mikex16) - https://pprz.net',
            'exportedAt'      => date('c'),
            'church_presets'  => $presets,
            'about_contact'   => file_exists($contactFile) ? (json_decode((string)@file_get_contents($contactFile), true) ?: []) : [],
            'inquiries'       => file_exists($inquiriesFile) ? (json_decode((string)@file_get_contents($inquiriesFile), true) ?: []) : [],
            'church_state'    => file_exists($hudStateFile) ? (json_decode((string)@file_get_contents($hudStateFile), true) ?: []) : [],
            'obs_lyric_config'=> file_exists($obsCfgFile) ? (json_decode((string)@file_get_contents($obsCfgFile), true) ?: []) : [],
        ];
        echo json_encode(['ok' => true, 'bundle' => $bundle], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    case 'import_backup': {
        $pw = trim((string)($req['adminPassword'] ?? ''));
        if (!verifyDataMgrPassword($pw)) {
            echo json_encode(['ok' => false, 'error' => 'Sesi terkunci atau password tidak sah!']);
            exit;
        }
        $bundle = is_array($req['bundle'] ?? null) ? $req['bundle'] : [];
        if (empty($bundle)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Invalid backup bundle']);
            exit;
        }
        if (isset($bundle['church_presets']) && is_array($bundle['church_presets'])) {
            saveChurchPresets($bundle['church_presets']);
        }
        if (isset($bundle['about_contact']) && is_array($bundle['about_contact'])) {
            file_put_contents($dataDir . '/about_contact.json', json_encode($bundle['about_contact'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        if (isset($bundle['inquiries']) && is_array($bundle['inquiries'])) {
            file_put_contents($dataDir . '/contact_inquiries.json', json_encode($bundle['inquiries'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        if (isset($bundle['church_state']) && is_array($bundle['church_state'])) {
            file_put_contents($dataDir . '/church_state.json', json_encode($bundle['church_state'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        if (isset($bundle['obs_lyric_config']) && is_array($bundle['obs_lyric_config'])) {
            file_put_contents(__DIR__ . '/obs_config.json', json_encode($bundle['obs_lyric_config'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        echo json_encode(['ok' => true, 'message' => 'Full system backup restored!'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        break;
    }

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => "Unknown action: {$action}"]);
}
