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
 * MegaOBS Combined 1920x1080 Master Broadcast Overlay
 * Combines Church HUD (Scenes + Lower-Thirds) & Easyslides/DB Lyric Overlay in one 1080p OBS Browser Source
 */
$qs = $_SERVER['QUERY_STRING'] ?? '';
$hudUrl = 'hud.php' . ($qs !== '' ? ('?' . $qs) : '');
$lyricUrl = 'obs.php' . ($qs !== '' ? ('?' . $qs) : '');
?>
<!DOCTYPE html>
<html lang="ms" style="background:transparent !important;">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=1920, height=1080, initial-scale=1.0">
  <title>MegaOBS Unified 1080p Master Broadcast Overlay (Church HUD + Lyric Studio)</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      background: transparent !important;
      position: relative;
    }
    .overlay-layer {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      border: none;
      background: transparent !important;
      pointer-events: none;
    }
    #layerChurchHud { z-index: 10; }
    #layerLyricObs  { z-index: 20; }
  </style>
</head>
<body>
  <iframe id="layerChurchHud" class="overlay-layer" src="<?= htmlspecialchars($hudUrl) ?>" allowtransparency="true"></iframe>
  <iframe id="layerLyricObs" class="overlay-layer" src="<?= htmlspecialchars($lyricUrl) ?>" allowtransparency="true"></iframe>
</body>
</html>
