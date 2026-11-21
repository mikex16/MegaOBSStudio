<?php
declare(strict_types=1);
/**
 * ============================================================================
 * MEGA OBS STUDIO — Unified One-Screen Church Broadcast, HUD & Lyric System
 * ============================================================================
 * Author / Creator : mikex16 (https://github.com/mikex16/MegaOBSStudio)
 * Co-Developer     : Antigravity AI
 * Ministry Purpose : Dibangunkan secara sukarela untuk kegunaan gereja supaya
 *                    urusan siaran langsung ("LIVE") lebih mudah dikawal dalam
 *                    satu skrin oleh seorang operator.
 * License          : MIT License with Attribution Requirement.
 *                    Sesiapa sahaja bebas menggunakan aplikasi/alatan ini selagi
 *                    bersetuju untuk TIDAK membuang nama pencipta asal (mikex16)
 *                    di dalam setiap skrip yang ditulis.
 * Disclaimer       : Aplikasi ini dihasilkan SEADANYA ("AS-IS") tanpa sebarang
 *                    jaminan ia akan berfungsi 100%. Gunakan atas risiko sendiri.
 * ============================================================================
 */
require_once __DIR__ . '/obs_websocket.php';
require_once __DIR__ . '/config.php';

$obsWsConfig = ObsConfig::load();
$dbs = getAvailableDatabases();
$defaultDb = DEFAULT_DB_FILE;

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$hostHeader = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/megaOBS/index.php')), '/');
$baseUrl = "{$scheme}://{$hostHeader}{$basePath}";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>MEGA OBS STUDIO — Unified One-Screen Broadcast, Church HUD & Easyslides Lyric Deck</title>
<script src="./js/presets.js"></script>
<style>
  :root {
    --bg-root: #070b14;
    --bg-panel: #0f1626;
    --bg-panel-alt: #151f33;
    --bg-card: #1b263d;
    --bg-card-hover: #23324f;
    --bg-input: #0a101d;
    --border: #22304a;
    --border-bright: #364b70;
    --text-main: #f1f5f9;
    --text-muted: #94a3b8;
    --text-dim: #64748b;
    --accent: #3b82f6;
    --gold: #f5c242;
    --gold-glow: rgba(245, 194, 66, 0.28);
    --pgm-red: #ef4444;
    --pvw-green: #10b981;
    --warn-amber: #f59e0b;
    --orange: #f97316;
    --purple: #8b5cf6;
    --cyan: #06b6d4;
    --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --font-mono: 'JetBrains Mono', 'Cascadia Code', 'Consolas', monospace;
  }

  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    user-select: none;
    -webkit-user-select: none;
  }
  input, textarea, select {
    user-select: text;
    -webkit-user-select: text;
  }

  html, body {
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    background: var(--bg-root);
    color: var(--text-main);
    font-family: var(--font-sans);
    font-size: 11.5px;
    line-height: 1.28;
  }

  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-track { background: rgba(10, 16, 29, 0.6); }
  ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
  ::-webkit-scrollbar-thumb:hover { background: #475569; }

  /* ==================== MASTER ONE-SCREEN SHELL ==================== */
  .app-shell {
    width: 100vw;
    height: 100vh;
    display: grid;
    grid-template-rows: 46px calc(100vh - 46px);
    overflow: hidden;
  }

  /* ==================== TOP BROADCAST HEADER ==================== */
  .topbar {
    background: linear-gradient(180deg, #131d31 0%, #0b111e 100%);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 8px;
    gap: 6px;
    height: 46px;
    overflow: hidden;
  }

  .brand-group {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-shrink: 0;
  }

  .brand-logo {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    background: linear-gradient(135deg, #3b82f6 0%, #f5c242 100%);
    color: #070b14;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 10px;
    letter-spacing: 0.3px;
    box-shadow: 0 0 12px rgba(59, 130, 246, 0.4);
  }

  .brand-title {
    font-weight: 800;
    font-size: 12.5px;
    letter-spacing: 0.4px;
    color: #fff;
    display: flex;
    flex-direction: column;
  }
  .brand-sub {
    font-size: 9.5px;
    color: var(--gold);
    font-weight: 600;
  }

  .conn-strip {
    display: flex;
    align-items: center;
    gap: 4px;
    background: rgba(10, 16, 29, 0.9);
    border: 1px solid var(--border);
    padding: 3px 6px;
    border-radius: 6px;
    flex-shrink: 0;
  }

  .conn-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 9.5px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
  }
  .conn-badge.connected {
    background: rgba(16, 185, 129, 0.16);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.35);
  }
  .conn-badge.connecting {
    background: rgba(245, 158, 11, 0.16);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.35);
  }
  .conn-badge.disconnected {
    background: rgba(239, 68, 68, 0.16);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.35);
  }
  .conn-badge.sim {
    background: rgba(139, 92, 246, 0.22);
    color: #c4b5fd;
    border: 1px solid rgba(139, 92, 246, 0.45);
  }

  .status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    box-shadow: 0 0 6px currentColor;
  }

  .conn-input {
    background: var(--bg-input);
    border: 1px solid #28354a;
    color: #e2e8f0;
    border-radius: 4px;
    padding: 3px 5px;
    font-family: var(--font-mono);
    font-size: 10.5px;
    outline: none;
  }
  .conn-input:focus { border-color: var(--accent); }
  .conn-input.ip { width: 92px; }
  .conn-input.port { width: 46px; }
  .conn-input.pass { width: 92px; }

  .tally-strip {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
  }

  .timer-pill {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #0b1120;
    border: 1px solid var(--border);
    padding: 3px 7px;
    border-radius: 5px;
    font-family: var(--font-mono);
    font-size: 10.5px;
    white-space: nowrap;
  }
  .timer-pill.live-active {
    background: rgba(239, 68, 68, 0.2);
    border-color: #ef4444;
    color: #fecaca;
    box-shadow: 0 0 10px rgba(239, 68, 68, 0.25);
  }
  .timer-pill.rec-active {
    background: rgba(245, 158, 11, 0.2);
    border-color: #f59e0b;
    color: #fde68a;
  }
  .timer-label {
    font-size: 8.5px;
    font-weight: 800;
    color: var(--text-muted);
  }

  /* ==================== BUTTONS & INPUTS ==================== */
  .btn {
    background: var(--bg-card);
    border: 1px solid var(--border-bright);
    color: var(--text-main);
    padding: 4px 8px;
    border-radius: 5px;
    font-size: 10.5px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: all 0.12s ease;
    white-space: nowrap;
    text-decoration: none;
  }
  .btn:hover { background: var(--bg-card-hover); border-color: #546e99; }
  .btn:active { transform: translateY(1px); }
  .btn-primary { background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%); border-color: #60a5fa; color: #fff; }
  .btn-danger { background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%); border-color: #f87171; color: #fff; }
  .btn-success { background: linear-gradient(180deg, #10b981 0%, #059669 100%); border-color: #34d399; color: #fff; }
  .btn-amber { background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%); border-color: #fbbf24; color: #fff; }
  .btn-gold { background: linear-gradient(135deg, #f5c242 0%, #d4af37 100%); border-color: #fde047; color: #090d16; font-weight: 800; }
  .btn-gold:hover { background: linear-gradient(135deg, #fde047 0%, #f5c242 100%); color: #000; }
  .btn-orange { background: linear-gradient(180deg, #f97316 0%, #ea580c 100%); border-color: #fb923c; color: #fff; }
  .btn-purple { background: linear-gradient(180deg, #8b5cf6 0%, #6d28d9 100%); border-color: #a78bfa; color: #fff; }
  .btn-sm { padding: 2.5px 6px; font-size: 10px; }
  .btn-xs { padding: 2px 5px; font-size: 9.5px; }
  .btn.active-toggle {
    background: rgba(59, 130, 246, 0.28);
    border-color: #60a5fa;
    color: #93c5fd;
  }

  .select-compact, .input-compact, .textarea-compact {
    background: var(--bg-input);
    border: 1px solid var(--border-bright);
    color: var(--text-main);
    border-radius: 4px;
    padding: 3px 6px;
    font-size: 10.5px;
    font-family: inherit;
    outline: none;
    width: 100%;
  }
  .select-compact:focus, .input-compact:focus, .textarea-compact:focus {
    border-color: var(--gold);
  }
  .textarea-compact {
    resize: none;
    line-height: 1.3;
  }

  /* ==================== 4-COLUMN ONE-SCREEN MASTER GRID ==================== */
  .workspace {
    display: grid;
    grid-template-columns: 235px minmax(360px, 1fr) 355px 345px;
    gap: 5px;
    padding: 5px;
    height: calc(100vh - 46px);
    overflow: hidden;
  }

  .column {
    display: flex;
    flex-direction: column;
    gap: 5px;
    height: 100%;
    min-width: 0;
    overflow: hidden;
  }

  .panel {
    background: var(--bg-panel);
    border: 1px solid var(--border);
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-height: 0;
  }

  .panel-header {
    background: linear-gradient(180deg, #172238 0%, #11192b 100%);
    border-bottom: 1px solid var(--border);
    padding: 5px 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 5px;
    flex-shrink: 0;
  }

  .panel-title {
    font-size: 10.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #e2e8f0;
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
  }

  .panel-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--accent);
    flex-shrink: 0;
  }

  .panel-body {
    padding: 5px;
    overflow-y: auto;
    flex: 1;
    min-height: 0;
  }

  /* ==================== COL 1: OBS SCENES, TRANSITIONS, OUTPUTS ==================== */
  .scene-meta-bar {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px;
    padding: 4px 5px;
    background: #0a101d;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
  }

  .scenes-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 4px;
  }

  .scene-btn {
    position: relative;
    background: linear-gradient(180deg, #1b2538 0%, #131c2e 100%);
    border: 1.5px solid #283650;
    border-radius: 5px;
    padding: 6px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 45px;
    transition: all 0.12s ease;
  }
  .scene-btn:hover { background: #223049; border-color: #486087; }
  .scene-btn.is-program {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.26) 0%, rgba(185, 28, 28, 0.12) 100%);
    border-color: var(--pgm-red);
    box-shadow: inset 0 0 10px rgba(239, 68, 68, 0.22);
  }
  .scene-btn.is-preview {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.22) 0%, rgba(5, 150, 105, 0.1) 100%);
    border-color: var(--pvw-green);
  }
  .scene-index {
    font-family: var(--font-mono);
    font-size: 8.5px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .scene-tally-tag {
    font-size: 7.5px;
    font-weight: 800;
    padding: 1px 3px;
    border-radius: 2px;
  }
  .tag-pgm { background: var(--pgm-red); color: #fff; }
  .tag-pvw { background: var(--pvw-green); color: #052e16; }
  .scene-name {
    font-weight: 700;
    font-size: 10.5px;
    color: #f8fafc;
    margin-top: 3px;
    word-break: break-word;
    line-height: 1.15;
  }

  .transition-box {
    padding: 5px;
    display: flex;
    flex-direction: column;
    gap: 5px;
  }
  .trans-row {
    display: grid;
    grid-template-columns: 1fr 64px;
    gap: 4px;
  }
  .trans-actions {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 4px;
  }
  #tbarCanvas {
    width: 100%;
    height: 26px;
    display: block;
    cursor: ew-resize;
    border-radius: 3px;
  }

  .output-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 4px;
    margin-bottom: 5px;
  }
  .output-btn {
    background: #162034;
    border: 1.5px solid #283856;
    border-radius: 5px;
    padding: 5px 6px;
    color: var(--text-main);
    font-weight: 700;
    font-size: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .output-btn.active-stream { background: rgba(239, 68, 68, 0.24); border-color: #ef4444; color: #fecaca; }
  .output-btn.active-record { background: rgba(245, 158, 11, 0.24); border-color: #f59e0b; color: #fde68a; }
  .output-btn.active-vcam { background: rgba(16, 185, 129, 0.24); border-color: #10b981; color: #a7f3d0; }
  .output-btn.active-replay { background: rgba(59, 130, 246, 0.24); border-color: #3b82f6; color: #bfdbfe; }

  #telemetryCanvas {
    width: 100%;
    height: 62px;
    display: block;
    background: #080d17;
    border: 1px solid #1c2940;
    border-radius: 4px;
  }

  /* ==================== COL 2: HTML5 CANVAS MONITOR + COMPOSITE OVERLAY + MIXER/SOURCES ==================== */
  .stage-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
  }

  .stage-toolbar {
    background: #0c1322;
    border-bottom: 1px solid var(--border);
    padding: 4px 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
    flex-wrap: wrap;
    flex-shrink: 0;
  }

  .toolbar-group {
    display: flex;
    align-items: center;
    gap: 3px;
  }

  .canvas-viewport {
    position: relative;
    flex: 1;
    min-height: 0;
    background: radial-gradient(circle at center, #0f172a 0%, #050810 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  #studioCanvas {
    width: 100%;
    height: 100%;
    display: block;
    cursor: crosshair;
  }

  /* Composite Live Iframe Overlay layered directly over the OBS Monitor Viewport */
  .composite-overlay-wrap {
    position: absolute;
    pointer-events: none;
    overflow: hidden;
    display: none;
    z-index: 15;
  }
  .composite-overlay-wrap.visible {
    display: block;
  }
  .composite-iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 1920px;
    height: 1080px;
    border: none;
    transform-origin: top left;
    pointer-events: none;
    background: transparent !important;
  }

  .transform-strip {
    background: #0d1525;
    border-top: 1px solid var(--border);
    padding: 4px 7px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 5px;
    flex-shrink: 0;
    font-size: 10px;
  }

  .coord-readout {
    font-family: var(--font-mono);
    font-size: 9.5px;
    color: #93c5fd;
    background: #080d18;
    border: 1px solid #1e2d47;
    padding: 2px 5px;
    border-radius: 3px;
  }

  /* Bottom Split Row under Center Monitor: Sources + Audio Mixer + Macros */
  .center-bottom-split {
    height: 205px;
    flex-shrink: 0;
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    gap: 5px;
    min-height: 0;
  }

  .sources-list, .mixer-list {
    display: flex;
    flex-direction: column;
    gap: 3px;
  }

  .source-row {
    background: #151f32;
    border: 1px solid #23314a;
    border-radius: 4px;
    padding: 3px 6px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
    cursor: pointer;
  }
  .source-row:hover { background: #1c2940; }
  .source-row.selected {
    background: rgba(59, 130, 246, 0.2);
    border-color: var(--accent);
  }
  .source-left {
    display: flex;
    align-items: center;
    gap: 5px;
    min-width: 0;
    flex: 1;
  }
  .source-name-text {
    font-weight: 600;
    font-size: 10.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .source-type-badge {
    font-size: 8.5px;
    color: var(--text-muted);
    background: #0b111e;
    padding: 1px 4px;
    border-radius: 3px;
    font-family: var(--font-mono);
  }
  .source-controls {
    display: flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
  }
  .icon-btn {
    background: #0d1424;
    border: 1px solid #263550;
    color: var(--text-muted);
    width: 20px;
    height: 20px;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 10px;
  }
  .icon-btn:hover { color: #fff; border-color: #4b638d; }
  .icon-btn.on { color: #38bdf8; border-color: rgba(56, 189, 248, 0.45); background: rgba(14, 165, 233, 0.15); }
  .icon-btn.locked { color: #f59e0b; border-color: rgba(245, 158, 11, 0.45); background: rgba(245, 158, 11, 0.15); }

  .mixer-channel {
    background: #141d2f;
    border: 1px solid #223049;
    border-radius: 4px;
    padding: 3px 6px;
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .mixer-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
  }
  .mixer-name {
    font-weight: 700;
    font-size: 10px;
    color: #e2e8f0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .mixer-db {
    font-family: var(--font-mono);
    font-size: 9.5px;
    color: #38bdf8;
  }
  .vu-canvas {
    width: 100%;
    height: 10px;
    display: block;
    background: #070b13;
    border-radius: 2px;
  }
  .mixer-controls {
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .vol-slider {
    flex: 1;
    accent-color: var(--accent);
    height: 4px;
    cursor: pointer;
  }

  .macro-strip {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 3px;
    padding: 4px 5px;
    background: #0a101d;
    border-top: 1px solid var(--border);
    flex-shrink: 0;
  }
  .macro-chip {
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 4px;
    padding: 3px 4px;
    font-size: 9px;
    font-weight: 800;
    color: #fff;
    text-align: center;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .macro-chip:hover { filter: brightness(1.15); }

  /* ==================== COL 3: CHURCH HUD & LOWER-THIRDS CONTROLLER ==================== */
  .hud-scenes-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 3px;
  }
  .hud-scene-pad {
    background: #151f33;
    border: 1.5px solid #263654;
    border-radius: 5px;
    padding: 5px 3px;
    text-align: center;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    color: var(--text-main);
    transition: all 0.12s;
  }
  .hud-scene-pad:hover {
    border-color: var(--gold);
    background: #1c2942;
  }
  .hud-scene-pad.active {
    background: rgba(245, 194, 66, 0.18);
    border-color: var(--gold);
    box-shadow: 0 0 10px rgba(245, 194, 66, 0.25);
  }
  .hud-scene-icon { font-size: 13px; }
  .hud-scene-label { font-size: 8.5px; font-weight: 800; letter-spacing: 0.3px; color: #fff; }
  .hud-scene-key { font-size: 7.5px; color: var(--gold); font-family: var(--font-mono); }

  /* Lower-Thirds Quick Matrix */
  .lt-matrix {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4px;
  }
  .lt-pad {
    background: #141e31;
    border: 1.5px solid #263652;
    border-radius: 5px;
    padding: 6px 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
    color: var(--text-main);
    font-size: 10px;
    font-weight: 700;
    transition: all 0.12s;
  }
  .lt-pad:hover { border-color: var(--gold); background: #1a2740; }
  .lt-pad.on {
    background: linear-gradient(135deg, rgba(245, 194, 66, 0.25), rgba(217, 119, 6, 0.15));
    border-color: var(--gold);
    color: #fef08a;
    box-shadow: 0 0 10px rgba(245, 194, 66, 0.22);
  }
  .lt-status-tag {
    font-size: 8px;
    font-family: var(--font-mono);
    padding: 1px 4px;
    border-radius: 3px;
    background: rgba(0,0,0,0.4);
    color: var(--text-muted);
  }
  .lt-pad.on .lt-status-tag {
    background: var(--gold);
    color: #070b14;
    font-weight: 900;
  }

  /* Countdown Compact Strip */
  .hud-timer-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
    background: #0a101d;
    border: 1px solid rgba(245, 194, 66, 0.35);
    border-radius: 5px;
    padding: 4px 7px;
  }
  .hud-timer-val {
    font-family: var(--font-mono);
    font-size: 15px;
    font-weight: 900;
    color: var(--gold);
    letter-spacing: 1px;
  }

  /* Sub-Tabs for Church HUD Content Editors */
  .subtabs-nav {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 3px;
    background: #0a101d;
    padding: 4px;
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
  }
  .subtab-btn {
    background: #141e30;
    border: 1px solid #24334f;
    color: var(--text-muted);
    padding: 4px 3px;
    border-radius: 4px;
    font-size: 9.5px;
    font-weight: 700;
    cursor: pointer;
    text-align: center;
    white-space: nowrap;
  }
  .subtab-btn:hover { color: #fff; }
  .subtab-btn.active {
    background: rgba(245, 194, 66, 0.18);
    border-color: var(--gold);
    color: var(--gold);
  }
  .subtab-pane {
    display: none;
    flex-direction: column;
    gap: 6px;
  }
  .subtab-pane.active {
    display: flex;
  }

  .field-group {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .field-label {
    font-size: 9px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }
  .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
  }

  /* ==================== COL 4: LYRIC DATABASE & EASYSLIDES MULTICAST PROJECTOR ==================== */
  .slides-deck-grid {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .lyric-slide-card {
    background: #131c2e;
    border: 1.5px solid #243452;
    border-radius: 5px;
    padding: 6px 8px;
    cursor: pointer;
    transition: all 0.12s;
    text-align: left;
    color: var(--text-main);
  }
  .lyric-slide-card:hover {
    background: #1b2842;
    border-color: #3b82f6;
  }
  .lyric-slide-card.active {
    background: rgba(249, 115, 22, 0.2);
    border-color: var(--orange);
    box-shadow: inset 0 0 10px rgba(249, 115, 22, 0.2);
  }
  .lyric-slide-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 9.5px;
    font-weight: 800;
    color: #fb923c;
    margin-bottom: 2px;
  }
  .lyric-slide-body {
    font-size: 11px;
    font-weight: 600;
    color: #f8fafc;
    white-space: pre-line;
    line-height: 1.3;
  }

  /* ==================== MODALS & FULL-SCREEN DRAWERS ==================== */
  .modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(4, 7, 15, 0.82);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 3000;
  }
  .modal-backdrop.open { display: flex; }

  .modal-card {
    background: var(--bg-panel);
    border: 1px solid var(--border-bright);
    border-radius: 8px;
    width: 680px;
    max-width: 95vw;
    max-height: 90vh;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.75);
    overflow-y: auto;
  }

  .drawer-card {
    background: var(--bg-panel);
    border: 1px solid var(--border-bright);
    border-radius: 8px;
    width: 96vw;
    height: 93vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 20px 70px rgba(0, 0, 0, 0.85);
  }

  .url-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background: #0a101d;
    border: 1px solid var(--border);
    padding: 7px 10px;
    border-radius: 5px;
  }
  .url-code {
    font-family: var(--font-mono);
    font-size: 11px;
    color: #38bdf8;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .toast-box {
    position: fixed;
    bottom: 10px;
    right: 10px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 4px;
    pointer-events: none;
  }
  .toast {
    background: rgba(15, 23, 42, 0.96);
    border: 1.5px solid var(--accent);
    color: #f8fafc;
    padding: 6px 11px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 700;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.55);
  }
</style>
</head>
<body>

<div class="app-shell">
  <!-- ==================== TOP MASTER BROADCAST HEADER ==================== -->
  <header class="topbar">
    <div class="brand-group" style="cursor:pointer;" onclick="openAboutUsModal()" title="Klik untuk info About Us, pprz.net, Lesen & Sumber Projek">
      <div class="brand-logo">MEGA</div>
      <div class="brand-title">
        <span>MEGA OBS STUDIO <span style="font-size:9.5px; color:var(--gold); font-weight:700;">by MIKE • pprz.net</span></span>
        <span class="brand-sub" id="obsVersionText">OBS WS v5 • Church HUD • 28 Lyric DBs</span>
      </div>
    </div>

    <!-- OBS WebSocket v5 Connection Bar -->
    <div class="conn-strip">
      <span id="connBadge" class="conn-badge connecting">
        <span class="status-dot"></span>
        <span id="connStateText">CONNECTING</span>
      </span>
      <input type="text" id="wsHost" class="conn-input ip" value="<?= htmlspecialchars((string)$obsWsConfig['host']) ?>" title="OBS IP">
      <input type="number" id="wsPort" class="conn-input port" value="<?= (int)$obsWsConfig['port'] ?>" title="OBS Port">
      <input type="password" id="wsPass" class="conn-input pass" value="<?= htmlspecialchars((string)$obsWsConfig['password']) ?>" title="OBS Password">
      <button class="btn btn-xs" id="btnTogglePass" title="Show/Hide Password">👁</button>
      <select id="connModeSelect" class="select-compact" style="width:82px;" title="Transport Mode">
        <option value="auto" <?= ($obsWsConfig['connectionMode'] ?? 'auto') === 'auto' ? 'selected' : '' ?>>Auto WS+PHP</option>
        <option value="direct" <?= ($obsWsConfig['connectionMode'] ?? '') === 'direct' ? 'selected' : '' ?>>Direct WS</option>
        <option value="php" <?= ($obsWsConfig['connectionMode'] ?? '') === 'php' ? 'selected' : '' ?>>PHP Bridge</option>
      </select>
      <button class="btn btn-primary btn-xs" id="btnConnect">CONNECT</button>
      <button class="btn btn-purple btn-xs" id="btnSimMode" title="Offline Interactive Simulation Mode">SIM</button>
    </div>

    <!-- Status Pills: Church HUD + Easyslides Multicast -->
    <div class="conn-strip">
      <span class="conn-badge connected" id="hudSyncBadge" title="Church HUD Local Sync Status">
        <span class="status-dot"></span>
        <span id="hudSyncText">HUD: OVERLAY</span>
      </span>
      <span class="conn-badge disconnected" id="multicastTopBadge" style="cursor:pointer;" onclick="openMulticastModal()" title="Click to configure Easyslides 5.2 Multicast (Port 9701)">
        <span class="status-dot"></span>
        <span id="multicastTopText">MC: IDLE</span>
      </span>
    </div>

    <!-- Broadcast Timers & Master Shortcuts -->
    <div class="tally-strip">
      <div class="timer-pill" id="pillStream">
        <span class="timer-label">LIVE</span>
        <span id="streamTimeText">OFFLINE</span>
      </div>
      <div class="timer-pill" id="pillRecord">
        <span class="timer-label">REC</span>
        <span id="recordTimeText">STANDBY</span>
      </div>
      <div class="timer-pill">
        <span class="timer-label">FPS</span>
        <span id="fpsTopText">60.0</span>
      </div>
      <button class="btn btn-purple btn-sm" onclick="openPresetStudioDrawer('scripture')" title="Open Verse, Sermon & Ticker Presets Editor Studio">📋 PRESET STUDIO</button>
      <button class="btn btn-orange btn-sm" onclick="openLyricStudioDrawer()" title="Open Full Song & Lyric Database Editor + Multi-Site Scraper">🎵 DB STUDIO</button>
      <button class="btn btn-gold btn-sm" onclick="openObsUrlsModal()" title="OBS Browser Source URLs & 1-Click Auto-Inject into OBS">🔗 OBS URLs</button>
      <button class="btn btn-sm" style="border-color:var(--gold); color:var(--gold);" onclick="openAboutUsModal()" title="About Us, Open Source License, Credits & Disclaimer">ℹ️ ABOUT</button>
      <button class="btn btn-sm" id="btnFullscreen" title="Toggle Fullscreen One-Screen Workstation">⛶</button>
    </div>
  </header>

  <!-- ==================== 4-COLUMN ONE-SCREEN WORKSPACE ==================== -->
  <main class="workspace">

    <!-- =========================================================================
         COLUMN 1: OBS SCENES, TRANSITIONS & T-BAR, BROADCAST OUTPUTS & TELEMETRY
         ========================================================================= -->
    <section class="column">
      <!-- OBS Scenes Panel -->
      <div class="panel" style="flex: 1;">
        <div class="panel-header">
          <div class="panel-title">
            <span class="panel-dot" style="background: var(--pgm-red);"></span>
            <span>1. OBS SCENES (<span id="sceneCountText">0</span>)</span>
          </div>
          <div style="display:flex; gap:3px;">
            <button class="btn btn-xs" id="btnAddScene" title="Create New Scene in OBS">+ SCENE</button>
            <button class="btn btn-xs" id="btnRefreshState" title="Sync Full State from OBS">↻</button>
          </div>
        </div>
        <div class="scene-meta-bar">
          <select id="selectCollection" class="select-compact" title="OBS Scene Collection"><option value="">Collection</option></select>
          <select id="selectProfile" class="select-compact" title="OBS Profile"><option value="">Profile</option></select>
        </div>
        <div class="panel-body">
          <div id="scenesGrid" class="scenes-grid"></div>
        </div>
      </div>

      <!-- OBS Transitions & Studio T-Bar Canvas -->
      <div class="panel" style="flex-shrink: 0;">
        <div class="panel-header">
          <div class="panel-title">
            <span class="panel-dot" style="background: var(--warn-amber);"></span>
            <span>TRANSITION & T-BAR</span>
          </div>
          <button class="btn btn-xs" id="btnStudioMode">STUDIO MODE</button>
        </div>
        <div class="transition-box">
          <div class="trans-row">
            <select id="selectTransition" class="select-compact"><option value="Fade">Fade</option><option value="Cut">Cut</option></select>
            <input type="number" id="inputTransDuration" class="conn-input" value="300" min="50" max="10000" step="50" style="width:100%;" title="Duration (ms)">
          </div>
          <div class="trans-actions">
            <button class="btn btn-danger btn-sm" id="btnCut">✂ CUT</button>
            <button class="btn btn-primary btn-sm" id="btnAutoTrans">⚡ AUTO</button>
            <button class="btn btn-success btn-sm" id="btnQuickFade">FADE</button>
          </div>
          <div style="background:#080d18; border:1px solid var(--border); border-radius:4px; padding:3px;">
            <div style="display:flex; justify-content:space-between; font-size:8.5px; color:var(--text-muted); margin-bottom:2px;">
              <span>MANUAL T-BAR</span>
              <span id="tbarValueText" style="font-family:var(--font-mono); color:#38bdf8;">0%</span>
            </div>
            <canvas id="tbarCanvas" width="215" height="26"></canvas>
          </div>
        </div>
      </div>

      <!-- OBS Broadcast Outputs & Telemetry Sparkline Canvas -->
      <div class="panel" style="flex-shrink: 0;">
        <div class="panel-header">
          <div class="panel-title">
            <span class="panel-dot" style="background: var(--pgm-red);"></span>
            <span>OUTPUTS & TELEMETRY</span>
          </div>
          <span id="bitrateTopText" style="font-family:var(--font-mono); font-size:9.5px; color:#38bdf8;">0 kbps</span>
        </div>
        <div class="panel-body" style="overflow:hidden;">
          <div class="output-grid">
            <button class="output-btn" id="btnToggleStream"><span>📡 STREAM</span><span id="badgeStream" style="font-size:8.5px;">OFF</span></button>
            <button class="output-btn" id="btnToggleRecord"><span>⏺ RECORD</span><span id="badgeRecord" style="font-size:8.5px;">OFF</span></button>
            <button class="output-btn" id="btnToggleVCam"><span>🎥 V-CAM</span><span id="badgeVCam" style="font-size:8.5px;">OFF</span></button>
            <button class="output-btn" id="btnToggleReplay"><span>↺ REPLAY</span><span id="badgeReplay" style="font-size:8.5px;">OFF</span></button>
          </div>
          <canvas id="telemetryCanvas" width="220" height="62"></canvas>
        </div>
      </div>
    </section>

    <!-- =========================================================================
         COLUMN 2: INTERACTIVE HTML5 STUDIO CANVAS + LIVE COMPOSITE OVERLAY + MIXER
         ========================================================================= -->
    <section class="column">
      <div class="panel stage-panel">
        <div class="stage-toolbar">
          <div class="toolbar-group">
            <span style="font-weight:800; font-size:10.5px; color:#fff;">STAGE:</span>
            <button class="btn btn-xs active-toggle" id="btnToggleComposite" title="Overlay Live Church HUD + Lyric Browser Sources directly on top of the OBS Monitor Canvas">⛪+🎵 LIVE HUD OVERLAY</button>
            <button class="btn btn-xs active-toggle" id="btnToggleBoxes" title="Show/Drag OBS Scene Item Bounding Boxes">🔲 LAYERS</button>
            <button class="btn btn-xs active-toggle" id="btnToggleGrid" title="Rule-of-Thirds Grid">⊞ GRID</button>
            <button class="btn btn-xs active-toggle" id="btnToggleSafe" title="Safe Zones">🎯 SAFE</button>
            <button class="btn btn-xs active-toggle" id="btnToggleOsd" title="Telemetry OSD">📊 OSD</button>
          </div>
          <div class="toolbar-group">
            <select id="selectPreviewFps" class="select-compact" style="width:58px;" title="OBS Canvas Preview Refresh Rate">
              <option value="0">PAUSE</option>
              <option value="2">2 FPS</option>
              <option value="5">5 FPS</option>
              <option value="8" selected>8 FPS</option>
              <option value="15">15 FPS</option>
            </select>
            <button class="btn btn-xs" id="btnSnapDownload" title="Download PNG Snapshot">📷 PNG</button>
            <button class="btn btn-xs" id="btnSnapServer" title="Save Snapshot to PHP Server">💾 SAVE</button>
          </div>
        </div>

        <!-- Primary Interactive HTML5 Canvas + Live Transparent HUD/Lyric Composite Preview -->
        <div class="canvas-viewport" id="canvasContainer">
          <canvas id="studioCanvas" width="1280" height="720"></canvas>
          <!-- Live Transparent Composite Preview of hud.php + obs.php -->
          <div class="composite-overlay-wrap visible" id="compositeOverlayWrap">
            <iframe src="hud.php" class="composite-iframe" id="compositeHudIframe" allowtransparency="true"></iframe>
            <iframe src="obs.php" class="composite-iframe" id="compositeLyricIframe" allowtransparency="true"></iframe>
          </div>
        </div>

        <!-- Selected OBS Layer Transform Strip -->
        <div class="transform-strip">
          <div style="display:flex; align-items:center; gap:5px; min-width:0;">
            <span style="color:var(--text-muted); font-weight:700;">LAYER:</span>
            <span id="selectedLayerText" style="font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">None</span>
            <span id="selectedCoordText" class="coord-readout">X:0 Y:0 1.0x</span>
          </div>
          <div style="display:flex; align-items:center; gap:3px; flex-shrink:0;">
            <button class="btn btn-xs" id="btnAutoInjectObs" style="border-color:var(--gold); color:var(--gold);" title="Auto-Inject Church HUD & Lyric Browser Sources into current OBS Scene">⚡ INJECT HUD TO OBS</button>
            <button class="btn btn-xs" id="btnCenterItem" title="Center Selected Source">⌖ CENTER</button>
            <button class="btn btn-xs" id="btnFitItem" title="Fit Selected Source to 1920x1080">⛶ FIT</button>
            <button class="btn btn-xs" id="btnResetItem" title="Reset Source">↺</button>
            <button class="btn btn-xs" id="btnEditSource" title="Edit Source Text / URL">✎ EDIT</button>
          </div>
        </div>

        <!-- One-Touch Broadcast Macro Strip -->
        <div class="macro-strip" id="macroGrid"></div>
      </div>

      <!-- Bottom Split: Scene Sources List (Left) + Canvas Audio Mixer (Right) -->
      <div class="center-bottom-split">
        <!-- OBS Scene Sources -->
        <div class="panel">
          <div class="panel-header">
            <div class="panel-title">
              <span class="panel-dot" style="background: var(--cyan);"></span>
              <span>SOURCES (<span id="activeSceneLabel">---</span>)</span>
            </div>
            <span style="font-size:9.5px; color:var(--text-muted);" id="sourceCountText">0</span>
          </div>
          <div class="panel-body">
            <div id="sourcesList" class="sources-list"></div>
          </div>
        </div>

        <!-- OBS Audio Mixer with HTML5 Canvas VU Meters -->
        <div class="panel">
          <div class="panel-header">
            <div class="panel-title">
              <span class="panel-dot" style="background: var(--pvw-green);"></span>
              <span>AUDIO MIXER (dB VU)</span>
            </div>
            <span style="font-size:8.5px; font-family:var(--font-mono); color:var(--text-muted);">-60 ── -20 ── 0dB</span>
          </div>
          <div class="panel-body">
            <div id="mixerList" class="mixer-list"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- =========================================================================
         COLUMN 3: CHURCH HUD & LOWER-THIRDS CONTROLLER (SIB PEKAN PAPAR / NATAL)
         ========================================================================= -->
    <section class="column">
      <div class="panel" style="flex: 1;">
        <div class="panel-header">
          <div class="panel-title">
            <span class="panel-dot" style="background: var(--gold);"></span>
            <span>2. CHURCH HUD & LOWER-THIRDS</span>
          </div>
          <div style="display:flex; gap:3px;">
            <button class="btn btn-gold btn-xs" onclick="pushQuickNatalPreset()" title="Load & Push Natal & Graduasi PKK 2026 Preset">⚡ NATAL PRESET</button>
            <a href="hud.php" target="_blank" class="btn btn-xs" title="Open 1080p Church HUD in New Tab">↗ HUD</a>
          </div>
        </div>

        <div class="panel-body" style="display:flex; flex-direction:column; gap:6px;">
          <!-- 5 Church HUD Scenes (Hotkeys 1..5) -->
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
              <span class="field-label">HUD SCENE SWITCHER (KEYS 1–5)</span>
              <select id="hudThemeSelect" class="select-compact" style="width:145px; padding:1px 4px; font-size:9.5px;" onchange="changeHudTheme(this.value)" title="Sacred Color Theme">
                <option value="natal-blue">Theme: Natal Blue & Gold</option>
                <option value="celestial-gold">Theme: Celestial Gold</option>
                <option value="royal-purple">Theme: Royal Purple</option>
                <option value="heavenly-blue">Theme: Heavenly Blue</option>
                <option value="sacred-crimson">Theme: Sacred Crimson</option>
                <option value="minimal-slate">Theme: Minimal Slate</option>
              </select>
            </div>
            <div class="hud-scenes-grid">
              <div class="hud-scene-pad" data-hud-scene="landing" onclick="switchHudScene('landing')">
                <span class="hud-scene-icon">🚀</span>
                <span class="hud-scene-label">START</span>
                <span class="hud-scene-key">[1]</span>
              </div>
              <div class="hud-scene-pad" data-hud-scene="title" onclick="switchHudScene('title')">
                <span class="hud-scene-icon">📖</span>
                <span class="hud-scene-label">SERMON</span>
                <span class="hud-scene-key">[2]</span>
              </div>
              <div class="hud-scene-pad active" data-hud-scene="overlay" onclick="switchHudScene('overlay')">
                <span class="hud-scene-icon">🎥</span>
                <span class="hud-scene-label">CAM HUD</span>
                <span class="hud-scene-key">[3]</span>
              </div>
              <div class="hud-scene-pad" data-hud-scene="break" onclick="switchHudScene('break')">
                <span class="hud-scene-icon">☕</span>
                <span class="hud-scene-label">BREAK/QR</span>
                <span class="hud-scene-key">[4]</span>
              </div>
              <div class="hud-scene-pad" data-hud-scene="ending" onclick="switchHudScene('ending')">
                <span class="hud-scene-icon">🏁</span>
                <span class="hud-scene-label">ENDING</span>
                <span class="hud-scene-key">[5]</span>
              </div>
            </div>
          </div>

          <!-- 4 Mutually Exclusive Lower-Third Toggles (Hotkeys L, S, O, T) -->
          <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3px;">
              <span class="field-label">LIVE LOWER-THIRD OVERLAYS (HOTKEYS: L, S, O, T)</span>
            </div>
            <div class="lt-matrix" style="grid-template-columns: repeat(2, 1fr);">
              <button class="lt-pad" id="ltBtnSpeaker" onclick="toggleHudLt('speaker')">
                <span>🎤 Speaker (L)</span>
                <span class="lt-status-tag" id="ltTagSpeaker">OFF</span>
              </button>
              <button class="lt-pad" id="ltBtnScripture" onclick="toggleHudLt('scripture')">
                <span>📜 Verse (S)</span>
                <span class="lt-status-tag" id="ltTagScripture">OFF</span>
              </button>
              <button class="lt-pad" id="ltBtnGiving" onclick="toggleHudLt('giving')">
                <span>💰 QR Info (O)</span>
                <span class="lt-status-tag" id="ltTagGiving">OFF</span>
              </button>
              <button class="lt-pad" id="ltBtnTicker" onclick="toggleHudLt('ticker')">
                <span>📢 Ticker (T)</span>
                <span class="lt-status-tag" id="ltTagTicker">OFF</span>
              </button>
              <button class="lt-pad" style="grid-column: span 2; justify-content:center; border-color:rgba(239,68,68,0.45); color:#fca5a5;" onclick="hideAllHudLt()">
                <span>🚫 Hide All Church HUD Lower-Thirds</span>
                <span class="lt-status-tag">CLR</span>
              </button>
            </div>
          </div>

          <!-- Countdown Timer Strip -->
          <div class="hud-timer-bar">
            <div style="display:flex; align-items:center; gap:6px;">
              <span style="font-size:9px; font-weight:800; color:var(--text-muted);">TIMER:</span>
              <span class="hud-timer-val" id="hudTimerDisplay">05:00</span>
            </div>
            <div style="display:flex; gap:3px; flex-wrap:wrap;">
              <button class="btn btn-gold btn-xs" onclick="hudTimerAction('START')" title="Start Timer">▶</button>
              <button class="btn btn-xs" onclick="hudTimerAction('PAUSE')" title="Pause Timer">⏸</button>
              <button class="btn btn-xs" onclick="hudTimerAction('RESET', 180)">3m</button>
              <button class="btn btn-xs" onclick="hudTimerAction('RESET', 300)">5m</button>
              <button class="btn btn-xs" onclick="hudTimerAction('RESET', 600)">10m</button>
              <button class="btn btn-xs" onclick="hudTimerAction('ADD_TIME', 60)">+1m</button>
              <button class="btn btn-xs" onclick="hudTimerAction('ADD_TIME', -60)">-1m</button>
            </div>
          </div>

          <!-- Tabbed Content Editors (Scripture | Sermon/Speaker | Ticker | Giving/QR) -->
          <div class="panel" style="flex:1; background:#0b111e; border-color:#1e2c46;">
            <div class="subtabs-nav" style="grid-template-columns: repeat(4, 1fr);">
              <button class="subtab-btn active" onclick="switchHudSubtab('scripture', this)">📜 Verse</button>
              <button class="subtab-btn" onclick="switchHudSubtab('sermon', this)">🎤 Sermon</button>
              <button class="subtab-btn" onclick="switchHudSubtab('ticker', this)">📢 Ticker</button>
              <button class="subtab-btn" onclick="switchHudSubtab('giving', this)">💰 QR/Bank</button>
            </div>

            <div class="panel-body">
              <!-- SUBTAB 1: SCRIPTURE VERSE PROMPTER -->
              <div class="subtab-pane active" id="hudPane-scripture">
                <div class="field-group">
                  <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label class="field-label">Quick Bible Verse Preset</label>
                    <button class="btn btn-xs" style="border-color:var(--gold); color:var(--gold);" onclick="openPresetStudioDrawer('scripture')" title="Add, Edit or Remove Verse Presets">✎ Edit Presets</button>
                  </div>
                  <select id="hudScripturePresetSel" class="select-compact" onchange="loadHudScripturePreset(this.value)">
                    <option value="">-- Select Scripture Preset --</option>
                  </select>
                </div>
                <div class="grid-2">
                  <div class="field-group">
                    <label class="field-label">Reference</label>
                    <input type="text" id="hudScriptureRef" class="input-compact" value="Mikha 5:1–3">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Version</label>
                    <input type="text" id="hudScriptureVer" class="input-compact" value="Alkitab TB">
                  </div>
                </div>
                <div class="field-group">
                  <label class="field-label">Verse Text (Ayat Firman)</label>
                  <textarea id="hudScriptureText" class="textarea-compact" rows="3">Tetapi engkau, hai Betlehem Efrata, hai yang terkecil di antara kaum-kaum Yehuda, dari padamu akan bangkit bagi-Ku seorang yang akan memerintah Israel, yang permulaannya sudah sejak purbakala, sejak dahulu kala.</textarea>
                </div>

                <!-- Verse Phone Readability: Text Size, Font Family & Style Controls -->
                <div style="background:#080c15; border:1px solid var(--border); border-radius:6px; padding:7px 8px; display:flex; flex-direction:column; gap:6px;">
                  <div class="field-group">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <label class="field-label">Verse Text Size (Phone Readability): <span id="scriptureSizeValText" style="color:var(--gold); font-family:var(--font-mono);">32px</span></label>
                      <div style="display:flex; gap:3px;">
                        <button type="button" class="btn btn-xs" onclick="setQuickScriptureSize(24)" title="Small (24px)">S</button>
                        <button type="button" class="btn btn-xs" onclick="setQuickScriptureSize(32)" title="Medium Default (32px)">M</button>
                        <button type="button" class="btn btn-xs" onclick="setQuickScriptureSize(40)" title="Large Phone-Friendly (40px)">L</button>
                        <button type="button" class="btn btn-xs" onclick="setQuickScriptureSize(50)" title="Extra Large (50px)">XL</button>
                      </div>
                    </div>
                    <input type="range" id="hudScriptureFontSize" min="20" max="64" step="1" value="32" oninput="onScriptureStyleChange()" style="width:100%; accent-color:var(--gold); cursor:pointer;">
                  </div>

                  <div class="grid-2">
                    <div class="field-group">
                      <label class="field-label">Jenis Tulisan (Font Family)</label>
                      <select id="hudScriptureFontFamily" class="select-compact" onchange="onScriptureStyleChange()">
                        <option value="sans" selected>Modern Sans (Jelas di Telefon)</option>
                        <option value="display">Broadcast Bold (Outfit)</option>
                        <option value="classic">Classic Serif (Georgia)</option>
                        <option value="serif">Sacred Cinzel (Klasik)</option>
                      </select>
                    </div>
                    <div class="field-group">
                      <label class="field-label">Gaya Paparan (Verse Style)</label>
                      <select id="hudScriptureStyle" class="select-compact" onchange="onScriptureStyleChange()">
                        <option value="bold-glass" selected>Bold Glass Card (Tegak &amp; Tebal)</option>
                        <option value="italic-glass">Classic Italic Glass</option>
                        <option value="royal-banner">Royal Gold Banner (Lebar Penuh)</option>
                        <option value="cinema-outline">Cinema Subtitle (Tanpa Kotak)</option>
                        <option value="caps-bold">ALL CAPS Broadcast Bold</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div style="display:flex; gap:4px;">
                  <button class="btn btn-gold" style="flex:1;" onclick="pushHudScriptureLive()">⚡ Push &amp; Show Verse Live</button>
                  <button class="btn btn-danger btn-sm" onclick="toggleHudLt('scripture', false)">Hide</button>
                </div>
              </div>

              <!-- SUBTAB 2: SERMON & SPEAKER MANAGER -->
              <div class="subtab-pane" id="hudPane-sermon">
                <div class="field-group">
                  <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label class="field-label">Quick Sermon / Speaker Preset</label>
                    <button class="btn btn-xs" style="border-color:var(--gold); color:var(--gold);" onclick="openPresetStudioDrawer('sermon')" title="Add, Edit or Remove Sermon Presets">✎ Edit Presets</button>
                  </div>
                  <select id="hudSermonPresetSel" class="select-compact" onchange="loadHudSermonPreset(this.value)">
                    <option value="">-- Select Sermon Preset --</option>
                  </select>
                </div>
                <div class="field-group">
                  <label class="field-label">Sermon Title (Tajuk Khutbah)</label>
                  <input type="text" id="hudSermonTitle" class="input-compact" value="Aku Kecil, Aku Berharga">
                </div>
                <div class="grid-2">
                  <div class="field-group">
                    <label class="field-label">Speaker Name</label>
                    <input type="text" id="hudSpeakerName" class="input-compact" value="Pta. Tinggih Sulog">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Scripture Ref</label>
                    <input type="text" id="hudSermonScripture" class="input-compact" value="Mikha 5:1–3">
                  </div>
                </div>
                <div class="field-group">
                  <label class="field-label">Speaker Role / Title</label>
                  <input type="text" id="hudSpeakerRole" class="input-compact" value="Pengkhotbah Kebaktian Natal & Graduasi PKK">
                </div>
                <div class="field-group">
                  <label class="field-label">Event / Series Subtitle</label>
                  <input type="text" id="hudSermonSeries" class="input-compact" value="Sambutan Natal & Graduasi PKK SIB Kawasan Papar 2026">
                </div>
                <div style="display:flex; gap:4px;">
                  <button class="btn btn-gold" style="flex:1;" onclick="pushHudSermonInfo(false)">💾 Update Info</button>
                  <button class="btn btn-primary" style="flex:1;" onclick="pushHudSermonInfo(true)">🎤 Push & Show Speaker LT</button>
                </div>
              </div>

              <!-- SUBTAB 3: MARQUEE TICKER -->
              <div class="subtab-pane" id="hudPane-ticker">
                <div class="field-group">
                  <div style="display:flex; justify-content:space-between; align-items:center;">
                    <label class="field-label">Quick Marquee Ticker Preset</label>
                    <button class="btn btn-xs" style="border-color:var(--gold); color:var(--gold);" onclick="openPresetStudioDrawer('ticker')" title="Add, Edit or Remove Ticker Presets">✎ Edit Presets</button>
                  </div>
                  <select id="hudTickerPresetSel" class="select-compact" onchange="loadHudTickerPreset(this.value)">
                    <option value="">-- Select Ticker Announcement Preset --</option>
                  </select>
                </div>

                <!-- Ticker Size & Speed Controls -->
                <div class="grid-2" style="background:#070b14; padding:5px 7px; border-radius:5px; border:1px solid var(--border);">
                  <div class="field-group">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <label class="field-label">Text Size: <span id="tickerSizeValText" style="color:var(--gold);">26px</span></label>
                      <div style="display:flex; gap:2px;">
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickTickerSize(18)">S</button>
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickTickerSize(26)">M</button>
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickTickerSize(34)">L</button>
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickTickerSize(44)">XL</button>
                      </div>
                    </div>
                    <input type="range" id="hudTickerFontSize" min="14" max="56" step="2" value="26" class="vol-slider" style="margin-top:4px;" oninput="onTickerStyleChange()">
                  </div>

                  <div class="field-group">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <label class="field-label">Speed: <span id="tickerSpeedValText" style="color:var(--gold);">28s</span></label>
                      <div style="display:flex; gap:2px;">
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickTickerSpeed(48)" title="Slow (48s)">Slow</button>
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickTickerSpeed(28)" title="Normal (28s)">Norm</button>
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickTickerSpeed(18)" title="Fast (18s)">Fast</button>
                      </div>
                    </div>
                    <input type="range" id="hudTickerDuration" min="8" max="75" step="2" value="28" class="vol-slider" style="margin-top:4px;" oninput="onTickerStyleChange()" title="Scroll Duration in Seconds (Lower = Faster, Higher = Slower)">
                  </div>
                </div>

                <div class="field-group">
                  <label class="field-label">Ticker Badge Title</label>
                  <div style="display:flex; gap:4px;">
                    <input type="text" id="hudTickerBadge" class="input-compact" value="INFO NATAL & PKK">
                    <button class="btn btn-sm" onclick="pushHudTickerBadge()">Set Badge</button>
                  </div>
                </div>
                <div class="field-group">
                  <label class="field-label">Marquee Running Announcement Text</label>
                  <textarea id="hudTickerText" class="textarea-compact" rows="3">Shalom! Selamat Datang ke Kebaktian Natal & Graduasi PKK SIB Kawasan Papar 2026 • Tema: "Aku Kecil, Aku Berharga" (Mikha 5:1–3) • Tempat: Gereja SIB Kayau • Pengkhotbah: Pta. Tinggih Sulog • Tarikh: 22 Nov 2026 (Ahad 9.30 Pagi) • Warna Baju: Biru • Pengerusi PKK: Welter Lainor • Tuhan Yesus Memberkati!</textarea>
                </div>
                <div style="display:flex; gap:4px;">
                  <button class="btn btn-gold" style="flex:1;" onclick="pushHudTickerText(true)">⚡ Push & Show Ticker</button>
                  <button class="btn btn-danger btn-sm" onclick="toggleHudLt('ticker', false)">Hide Ticker</button>
                </div>
              </div>

              <!-- SUBTAB 4: GIVING & DUITNOW QR (WITH SIZE & SCREEN POSITION) -->
              <div class="subtab-pane" id="hudPane-giving">
                <div class="grid-2">
                  <div class="field-group">
                    <label class="field-label">QR Screen Position</label>
                    <select id="hudGivingPosMode" class="select-compact" onchange="onGivingLayoutChange()">
                      <option value="bottom-right">Bottom-Right (Default LT)</option>
                      <option value="center">Middle / Center of Screen</option>
                      <option value="bottom-left">Bottom-Left</option>
                      <option value="top-right">Top-Right</option>
                      <option value="top-left">Top-Left</option>
                      <option value="custom">Anywhere (Custom X% / Y%)</option>
                    </select>
                  </div>
                  <div class="field-group">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <label class="field-label">QR Size: <span id="qrSizeValText" style="color:var(--gold);">96px</span></label>
                      <div style="display:flex; gap:2px;">
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickQrSize(96)">S</button>
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickQrSize(150)">M</button>
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickQrSize(220)">L</button>
                        <button class="btn btn-xs" style="padding:1px 4px; font-size:8.5px;" onclick="setQuickQrSize(300)">XL</button>
                      </div>
                    </div>
                    <input type="range" id="hudGivingQrSize" min="70" max="380" step="4" value="96" class="vol-slider" style="margin-top:4px;" oninput="onGivingLayoutChange()">
                  </div>
                </div>

                <!-- Custom X% and Y% Anywhere Sliders -->
                <div class="grid-2" id="customQrCoordsRow" style="background:#070b14; padding:5px 7px; border-radius:5px; border:1px solid var(--border); display:none;">
                  <div class="field-group">
                    <label class="field-label">X Horizontal: <span id="qrPosXText" style="color:var(--gold);">50%</span></label>
                    <input type="range" id="hudGivingPosX" min="8" max="92" step="1" value="50" class="vol-slider" oninput="onGivingCustomCoordInput()">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Y Vertical: <span id="qrPosYText" style="color:var(--gold);">50%</span></label>
                    <input type="range" id="hudGivingPosY" min="10" max="90" step="1" value="50" class="vol-slider" oninput="onGivingCustomCoordInput()">
                  </div>
                </div>

                <div class="grid-2">
                  <div class="field-group">
                    <label class="field-label">Bank Name</label>
                    <input type="text" id="hudGivingBank" class="input-compact" value="Public Bank">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Account Number</label>
                    <input type="text" id="hudGivingAcc" class="input-compact" value="3235424420">
                  </div>
                </div>
                <div class="grid-2">
                  <div class="field-group">
                    <label class="field-label">Account Name</label>
                    <input type="text" id="hudGivingName" class="input-compact" value="SIB PEKAN PAPAR">
                  </div>
                  <div class="field-group">
                    <label class="field-label">Reference</label>
                    <input type="text" id="hudGivingRef" class="input-compact" value="Persembahan / Perpuluhan">
                  </div>
                </div>
                <div style="display:flex; gap:4px;">
                  <button class="btn btn-gold" style="flex:1;" onclick="pushHudGivingInfo(true)">⚡ Update & Show QR Live</button>
                  <button class="btn btn-sm" onclick="centerQrSpotlight()" title="One-click show large QR in the middle of the screen">🎯 Middle Spotlight</button>
                  <button class="btn btn-danger btn-sm" onclick="toggleHudLt('giving', false)">Hide</button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- =========================================================================
         COLUMN 4: LYRIC DATABASE (28 SQLITE DBs) & EASYSLIDES MULTICAST PROJECTOR
         ========================================================================= -->
    <section class="column">
      <div class="panel" style="flex: 1;">
        <div class="panel-header">
          <div class="panel-title">
            <span class="panel-dot" style="background: var(--orange);"></span>
            <span>3. LYRIC DB & MULTICAST PROJECTOR</span>
          </div>
          <div style="display:flex; gap:3px;">
            <button class="btn btn-xs" onclick="openLyricStudioDrawer()" title="Edit Songs, Strip Chords, or Scrape">✎ DB EDITOR</button>
            <a href="obs.php" target="_blank" class="btn btn-orange btn-xs" title="Open 1080p Lyric Overlay Tab">↗ LYRIC</a>
          </div>
        </div>

        <!-- Database & Song Search Bar -->
        <div style="padding:5px; background:#0a101d; border-bottom:1px solid var(--border); display:flex; flex-direction:column; gap:4px; flex-shrink:0;">
          <div style="display:flex; gap:4px; align-items:center;">
            <span style="font-size:9px; font-weight:800; color:var(--text-muted);">DB:</span>
            <select id="lyricDbSelect" class="select-compact" style="flex:1; font-weight:700;" onchange="onLyricDbChange()">
              <?php foreach ($dbs as $d): ?>
                <option value="<?= htmlspecialchars($d['filename']) ?>" <?= strcasecmp($d['filename'], $defaultDb) === 0 ? 'selected' : '' ?>>
                  <?= htmlspecialchars($d['filename']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display:flex; gap:4px;">
            <input type="search" id="lyricQuickSearch" class="input-compact" placeholder="🔍 Search 3,250+ songs (title, artist, lyrics)..." oninput="debounceLyricQuickSearch()">
          </div>

          <select id="lyricSongResults" class="select-compact" onchange="loadSelectedLyricSong(this.value)">
            <option value="">— Select song to project live —</option>
          </select>
        </div>

        <!-- Active Song Header & Transport Controls -->
        <div style="padding:5px 6px; background:#121b2d; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:4px; flex-shrink:0;">
          <div style="min-width:0; flex:1;">
            <div id="activeProjectedSongTitle" style="font-size:10.5px; font-weight:800; color:var(--orange); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">No Song Loaded</div>
            <div id="activeProjectedSlideMeta" style="font-size:9px; color:var(--text-muted);">Click any slide below or use ◀ / ▶</div>
          </div>
          <div style="display:flex; gap:3px; flex-shrink:0;">
            <button class="btn btn-xs" onclick="stepLyricSlide(-1)" title="Previous Slide (Left/Up Arrow)">◀</button>
            <button class="btn btn-xs" onclick="stepLyricSlide(1)" title="Next Slide (Right/Down Arrow)">▶</button>
            <button class="btn btn-xs" id="btnLyricBlack" onclick="toggleLyricBlack()" title="Black Screen">⬛ BLK</button>
            <button class="btn btn-xs" id="btnLyricClear" onclick="toggleLyricClear()" title="Clear Lyrics">🧹 CLR</button>
          </div>
        </div>

        <!-- Slide Cards Grid (Scrollable internal list) -->
        <div class="panel-body">
          <div id="lyricSlidesGrid" class="slides-deck-grid"></div>
        </div>

        <!-- Lyric Overlay Styling & Stage Alert Footer -->
        <div style="padding:5px; background:#0a101d; border-top:1px solid var(--border); display:flex; flex-direction:column; gap:4px; flex-shrink:0;">
          <div style="display:grid; grid-template-columns: 1.2fr 0.9fr 54px 54px; gap:3px;">
            <select id="cfgLyricTheme" class="select-compact" onchange="saveLyricOverlayStyle()" title="Lyric Overlay Theme">
              <option value="glass">Glass Card</option>
              <option value="cinema">Cinema Outline</option>
              <option value="gradient">Gradient Bar</option>
              <option value="stage">Stage Monitor</option>
            </select>
            <select id="cfgLyricPos" class="select-compact" onchange="saveLyricOverlayStyle()" title="Vertical Position">
              <option value="bottom">Bottom</option>
              <option value="center">Center</option>
              <option value="top">Top</option>
            </select>
            <input type="number" id="cfgLyricFontSize" class="input-compact" value="42" min="20" max="90" onchange="saveLyricOverlayStyle()" title="Font Size (px)">
            <input type="number" id="cfgLyricOpacity" class="input-compact" value="0.78" step="0.05" min="0" max="1" onchange="saveLyricOverlayStyle()" title="Box Opacity">
          </div>

          <div style="display:flex; gap:3px; align-items:center;">
            <input type="text" id="lyricAlertInput" class="input-compact" placeholder="🔔 Stage Alert Message..." style="flex:1;">
            <button class="btn btn-xs btn-orange" onclick="sendLyricAlert()">Alert</button>
            <button class="btn btn-xs" onclick="clearLyricAlert()">✕</button>
            <button class="btn btn-xs" onclick="openMulticastModal()" title="Easyslides 5.2 Multicast TCP Settings">🔌 MC</button>
          </div>
        </div>
      </div>
    </section>

  </main>
</div>

<!-- ============================================================================
     MODAL 1: OBS BROWSER SOURCE URLS & 1-CLICK AUTO-INJECT INTO OBS
     ============================================================================ -->
<div class="modal-backdrop" id="obsUrlsModal">
  <div class="modal-card">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); padding-bottom:8px;">
      <div>
        <strong style="font-size:13px; color:var(--gold);">🔗 OBS Studio Browser Source URLs & 1-Click Auto-Setup</strong>
        <div style="font-size:10.5px; color:var(--text-muted);">Recommended OBS Browser Source Resolution: Width <code>1920</code> × Height <code>1080</code> (60 FPS)</div>
      </div>
      <button class="btn btn-sm" onclick="closeModalById('obsUrlsModal')">✕</button>
    </div>

    <div style="background:rgba(245,194,66,0.1); border:1px solid rgba(245,194,66,0.4); border-radius:6px; padding:10px; display:flex; align-items:center; justify-content:space-between; gap:10px;">
      <div>
        <div style="font-weight:800; color:var(--gold); font-size:12px;">⚡ 1-Click Auto-Inject Overlays into Active OBS Scene</div>
        <div style="font-size:10.5px; color:var(--text-muted);">Automatically creates/updates the 1920×1080 Browser Sources in your currently active OBS scene via WebSocket v5.</div>
      </div>
      <button class="btn btn-gold" onclick="autoInjectOverlaysToObs()">⚡ Auto-Inject to OBS Now</button>
    </div>

    <div style="display:flex; flex-direction:column; gap:6px;">
      <div class="url-row" style="border-color:var(--gold);">
        <div style="min-width:0; flex:1;">
          <div style="font-weight:800; color:var(--gold); font-size:11px;">🌟 All-in-One Master Combined Overlay (Church HUD + Lyrics in 1 Browser Source):</div>
          <div class="url-code" id="urlMasterAll"><?= htmlspecialchars($baseUrl . '/overlay_all.php') ?></div>
        </div>
        <button class="btn btn-gold btn-sm" onclick="copyTextById('urlMasterAll')">Copy URL</button>
      </div>

      <div class="url-row">
        <div style="min-width:0; flex:1;">
          <div style="font-weight:700; font-size:11px;">🎥 Church HUD Transparent Camera Lower-Thirds Overlay:</div>
          <div class="url-code" id="urlHudOverlay"><?= htmlspecialchars($baseUrl . '/lowerthird.html') ?></div>
        </div>
        <button class="btn btn-sm" onclick="copyTextById('urlHudOverlay')">Copy URL</button>
      </div>

      <div class="url-row">
        <div style="min-width:0; flex:1;">
          <div style="font-weight:700; font-size:11px;">⛪ Church HUD Full Multi-Scene Overlay (Starting / Sermon / Camera / Break / Ending):</div>
          <div class="url-code" id="urlHudFull"><?= htmlspecialchars($baseUrl . '/hud.php') ?></div>
        </div>
        <button class="btn btn-sm" onclick="copyTextById('urlHudFull')">Copy URL</button>
      </div>

      <div class="url-row">
        <div style="min-width:0; flex:1;">
          <div style="font-weight:700; font-size:11px;">🎵 Dedicated Easyslides & SQLite DB Lyric Lower-Third Overlay:</div>
          <div class="url-code" id="urlLyricObs"><?= htmlspecialchars($baseUrl . '/obs.php') ?></div>
        </div>
        <button class="btn btn-sm" onclick="copyTextById('urlLyricObs')">Copy URL</button>
      </div>

      <div class="url-row">
        <div style="min-width:0; flex:1;">
          <div style="font-weight:700; font-size:11px;">🖥 Stage Confidence Monitor (Full-Screen Clock + Current & Next Lyric Slide):</div>
          <div class="url-code" id="urlLyricStage"><?= htmlspecialchars($baseUrl . '/obs.php?theme=stage') ?></div>
        </div>
        <button class="btn btn-sm" onclick="copyTextById('urlLyricStage')">Copy URL</button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================================
     MODAL 2: EASYSLIDES 5.2 MULTICAST TCP CONNECTION SETTINGS
     ============================================================================ -->
<div class="modal-backdrop" id="multicastModal">
  <div class="modal-card" style="width:440px;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); padding-bottom:8px;">
      <strong style="font-size:13px;">🔌 Easyslides 5.2 Multicast TCP Listener</strong>
      <button class="btn btn-sm" onclick="closeModalById('multicastModal')">✕</button>
    </div>
    <div id="mcModalStatusMsg" style="font-size:11px; color:var(--text-muted); background:#0a101d; padding:8px; border-radius:5px; border:1px solid var(--border);">
      In Easyslides 5.2, open the <strong>Multicast</strong> tab and click <strong>Start Multicast</strong> (Port 9701).
    </div>
    <div class="grid-2">
      <div class="field-group">
        <label class="field-label">Easyslides Server IP</label>
        <input type="text" id="cfgMcHost" class="input-compact" value="127.0.0.1">
      </div>
      <div class="field-group">
        <label class="field-label">Multicast Port</label>
        <input type="number" id="cfgMcPort" class="input-compact" value="9701">
      </div>
    </div>
    <div class="grid-2">
      <div class="field-group">
        <label class="field-label">Service Mode</label>
        <select id="cfgMcService" class="select-compact">
          <option value="1">Live Screen (Recommended)</option>
          <option value="0">Stage Display</option>
        </select>
      </div>
      <div class="field-group">
        <label class="field-label">Multicast Password</label>
        <input type="password" id="cfgMcPass" class="input-compact" placeholder="Optional">
      </div>
    </div>
    <div class="grid-2">
      <div class="field-group">
        <label class="field-label">Text Color</label>
        <input type="text" id="cfgLyricTextColor" class="input-compact" value="#ffffff" onchange="saveLyricOverlayStyle()">
      </div>
      <div class="field-group">
        <label class="field-label">Accent Color</label>
        <input type="text" id="cfgLyricAccentColor" class="input-compact" value="#f97316" onchange="saveLyricOverlayStyle()">
      </div>
    </div>
    <div style="display:flex; gap:12px; padding:4px 0;">
      <label style="font-size:11px; display:flex; align-items:center; gap:5px; cursor:pointer;">
        <input type="checkbox" id="cfgLyricShowSection" checked onchange="saveLyricOverlayStyle()"> Show Section Badge
      </label>
      <label style="font-size:11px; display:flex; align-items:center; gap:5px; cursor:pointer;">
        <input type="checkbox" id="cfgLyricShowTitle" checked onchange="saveLyricOverlayStyle()"> Show Song Title
      </label>
    </div>
    <div style="display:flex; gap:6px; justify-content:flex-end; margin-top:4px;">
      <button class="btn btn-danger" onclick="stopMulticastDaemon()">⏹ Stop Listener</button>
      <button class="btn btn-success" onclick="startMulticastDaemon()">🔌 Connect & Start Listener</button>
    </div>
  </div>
</div>

<!-- ============================================================================
     MODAL 3: FULL SONG & LYRIC DATABASE STUDIO + MULTI-SITE SCRAPER DRAWER
     ============================================================================ -->
<div class="modal-backdrop" id="lyricStudioDrawer">
  <div class="drawer-card">
    <div style="background:#131d31; border-bottom:1px solid var(--border); padding:8px 14px; display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:8px;">
        <strong style="font-size:13px; color:#fff;">🎵 Song & Lyric SQLite Database Studio + Chord Stripper & Scraper</strong>
        <span style="font-size:11px; color:var(--gold);">Standalone Local Databases (<?= count($dbs) ?> .db files)</span>
      </div>
      <div style="display:flex; gap:6px;">
        <a href="lyrics_editor.php" target="_blank" class="btn btn-sm">↗ Open in New Tab</a>
        <button class="btn btn-danger btn-sm" onclick="closeLyricStudioDrawer()">✕ Close & Return to Live Console</button>
      </div>
    </div>
    <iframe id="lyricStudioIframe" src="about:blank" style="flex:1; width:100%; border:none; background:#0f172a;"></iframe>
  </div>
</div>

<!-- ============================================================================
     MODAL 3B: CHURCH HUD PRESETS STUDIO DRAWER (VERSE, SERMON & TICKER EDITOR)
     ============================================================================ -->
<div class="modal-backdrop" id="presetStudioDrawer">
  <div class="drawer-card">
    <div style="background:#131d31; border-bottom:1px solid var(--border); padding:8px 14px; display:flex; align-items:center; justify-content:space-between;">
      <div style="display:flex; align-items:center; gap:8px;">
        <strong style="font-size:13px; color:#fff;">📋 Church HUD Presets Studio — Verse, Sermon & Ticker Editor</strong>
        <span style="font-size:11px; color:var(--gold);">Add, Edit, or Remove Presets (Standalone JSON Storage)</span>
      </div>
      <div style="display:flex; gap:6px;">
        <a href="presets_editor.php" target="_blank" id="presetStudioNewTabBtn" class="btn btn-sm">↗ Open in New Tab</a>
        <button class="btn btn-danger btn-sm" onclick="closePresetStudioDrawer()">✕ Close & Return to Live Console</button>
      </div>
    </div>
    <iframe id="presetStudioIframe" src="about:blank" style="flex:1; width:100%; border:none; background:#0b111e;"></iframe>
  </div>
</div>

<!-- ============================================================================
     MODAL 4: GENERIC OBS SOURCE / SCENE QUICK MODAL
     ============================================================================ -->
<div class="modal-backdrop" id="modalBackdrop">
  <div class="modal-card" style="width:420px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <strong id="modalTitle" style="font-size:13px;">Edit Source Settings</strong>
      <button class="btn btn-sm" id="btnCloseModal">✕</button>
    </div>
    <div id="modalBody" style="display:flex; flex-direction:column; gap:8px;"></div>
    <div style="display:flex; justify-content:flex-end; gap:6px; margin-top:4px;">
      <button class="btn" id="btnCancelModal">Cancel</button>
      <button class="btn btn-primary" id="btnSaveModal">Apply to OBS</button>
    </div>
  </div>
</div>

<!-- ============================================================================
     MODAL 5: ABOUT US, CONTACT US (MIKE), SERVICES, LICENSE & SOURCES (BM + EN)
     ============================================================================ -->
<div class="modal-backdrop" id="aboutUsModal">
  <div class="modal-card" style="width:780px; max-width:96vw; max-height:92vh; overflow-y:auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); padding-bottom:10px;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div class="brand-logo" style="width:36px; height:36px; font-size:11px;">MEGA</div>
        <div>
          <strong style="font-size:14px; color:#fff;">MEGA OBS STUDIO — Unified One-Screen Church Broadcast System</strong>
          <div style="font-size:11px; color:var(--gold); font-weight:700;">Created by MIKE (mikex16) • Official Website: <a href="https://pprz.net" target="_blank" style="color:#38bdf8; text-decoration:underline;">pprz.net</a> • Co-developed with Antigravity AI</div>
        </div>
      </div>
      <button class="btn btn-sm" onclick="closeModalById('aboutUsModal')">✕</button>
    </div>

    <!-- 1. Tujuan Projek Sukarela / About Us & Ministry Purpose (BM + EN) -->
    <div style="background:linear-gradient(135deg, rgba(30,58,138,0.28), rgba(15,23,42,0.85)); border:1px solid rgba(245,194,66,0.4); border-radius:7px; padding:11px 14px; display:flex; flex-direction:column; gap:6px;">
      <div style="font-size:11.5px; font-weight:800; color:var(--gold); text-transform:uppercase; letter-spacing:0.5px;">⛪ Tentang Projek Ini / About Us &amp; Ministry Purpose</div>
      <p style="font-size:11.8px; color:#e2e8f0; line-height:1.5;">
        🇲🇾 <strong>BM:</strong> Aplikasi <strong>MegaOBS Studio</strong> ini dibangunkan secara <strong>sukarela</strong> untuk kegunaan gereja supaya urusan siaran langsung (<strong>"LIVE"</strong>) menjadi lebih mudah, kemas, dan dapat dikawal sepenuhnya dalam <strong>satu skrin oleh seorang operator sahaja</strong> (menggabungkan Kawalan Jauh OBS Studio, HUD Lower-Third Gereja, serta Pangkalan Data Lirik Lagu &amp; Easyslides Multicast).
      </p>
      <p style="font-size:11.3px; color:#94a3b8; line-height:1.5; border-top:1px dashed rgba(148,163,184,0.2); padding-top:5px;">
        🇬🇧 <strong>EN:</strong> <strong>MegaOBS Studio</strong> was created <strong>voluntarily</strong> for church media ministry to make <strong>"LIVE"</strong> broadcasting easier, cleaner, and fully controllable on a <strong>single screen by a single operator</strong> (unifying OBS Studio Remote Control, Church Broadcast HUD &amp; Lower-Thirds, and the 28-SQLite Lyric Database &amp; Easyslides Multicast engine).
      </p>
      <div style="display:flex; align-items:center; gap:8px; margin-top:2px; flex-wrap:wrap;">
        <span style="font-size:11px; color:var(--text-muted);">GitHub Repository:</span>
        <a href="https://github.com/mikex16/MegaOBSStudio" target="_blank" style="color:#38bdf8; font-family:var(--font-mono); font-size:11.5px; font-weight:700; text-decoration:none;">https://github.com/mikex16/MegaOBSStudio.git</a>
      </div>
    </div>

    <!-- 2. Hubungi Kami & Tempahan Projek / Contact Us & Custom Development Services (MIKE) -->
    <div style="background:linear-gradient(135deg, rgba(16,185,129,0.14), rgba(15,23,42,0.92)); border:1px solid rgba(16,185,129,0.45); border-left:4px solid #10b981; border-radius:7px; padding:12px 14px; display:flex; flex-direction:column; gap:8px;">
      <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px;">
        <div style="font-size:11.5px; font-weight:800; color:#34d399; text-transform:uppercase; letter-spacing:0.5px;">📞 Hubungi Kami &amp; Tempahan Projek / Contact Us &amp; Custom Services</div>
        <span style="background:rgba(245,194,66,0.18); border:1px solid var(--gold); color:var(--gold); font-size:10.5px; font-weight:800; padding:2px 8px; border-radius:99px;">👋 Just Call Me "MIKE"</span>
      </div>

      <!-- Contact Details Bar (Website pprz.net, WhatsApp, Email) -->
      <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:8px;">
        <div style="background:linear-gradient(135deg, rgba(245,194,66,0.16), #080d18); border:1px solid rgba(245,194,66,0.55); border-radius:6px; padding:8px 10px; display:flex; align-items:center; justify-content:space-between; gap:6px;">
          <div>
            <div style="font-size:9.5px; font-weight:800; color:var(--gold); text-transform:uppercase;">🌐 Official Website</div>
            <div style="font-size:13.5px; font-weight:900; color:#fff; font-family:var(--font-mono);">pprz.net</div>
          </div>
          <a href="https://pprz.net" target="_blank" class="btn btn-gold btn-xs" style="text-decoration:none;">Visit ↗</a>
        </div>
        <div style="background:#080d18; border:1px solid rgba(16,185,129,0.35); border-radius:6px; padding:8px 10px; display:flex; align-items:center; justify-content:space-between; gap:6px;">
          <div>
            <div style="font-size:9.5px; font-weight:800; color:#34d399; text-transform:uppercase;">💬 WhatsApp Only</div>
            <div style="font-size:12.5px; font-weight:800; color:#fff; font-family:var(--font-mono);">0166841604</div>
          </div>
          <a href="https://wa.me/60166841604" target="_blank" class="btn btn-success btn-xs" style="text-decoration:none;">Chat ↗</a>
        </div>
        <div style="background:#080d18; border:1px solid rgba(59,130,246,0.35); border-radius:6px; padding:8px 10px; display:flex; align-items:center; justify-content:space-between; gap:6px;">
          <div>
            <div style="font-size:9.5px; font-weight:800; color:#60a5fa; text-transform:uppercase;">✉️ Email Address</div>
            <div style="font-size:11.5px; font-weight:800; color:#fff; font-family:var(--font-mono);">mikex16@gmail.com</div>
          </div>
          <a href="mailto:mikex16@gmail.com" class="btn btn-primary btn-xs" style="text-decoration:none;">Email ↗</a>
        </div>
      </div>

      <!-- Custom Development Services Ad (BM + EN) -->
      <div style="background:rgba(8,13,24,0.75); border:1px solid rgba(255,255,255,0.08); border-radius:6px; padding:9px 11px; display:flex; flex-direction:column; gap:5px;">
        <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:2px;">
          <a href="https://pprz.net" target="_blank" style="background:rgba(245,194,66,0.2); border:1px solid var(--gold); color:var(--gold); font-size:10px; font-weight:800; padding:2px 8px; border-radius:4px; text-decoration:none;">🚀 Layari Website Baru: pprz.net ↗</a>
          <span style="background:rgba(59,130,246,0.18); border:1px solid rgba(59,130,246,0.45); color:#93c5fd; font-size:10px; font-weight:800; padding:2px 8px; border-radius:4px;">🌐 Website Design &amp; Web Systems</span>
          <span style="background:rgba(139,92,246,0.18); border:1px solid rgba(139,92,246,0.45); color:#c4b5fd; font-size:10px; font-weight:800; padding:2px 8px; border-radius:4px;">📱 Custom App &amp; Software Dev</span>
          <span style="background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.45); color:#fcd34d; font-size:10px; font-weight:800; padding:2px 8px; border-radius:4px;">🔌 Electronic &amp; IoT Remote Control Projects</span>
        </div>
        <p style="font-size:11.5px; color:#e2e8f0; line-height:1.5;">
          🇲🇾 <strong>BM:</strong> Jemput layari laman web rasmi baru saya di <a href="https://pprz.net" target="_blank" style="color:var(--gold); font-weight:800; text-decoration:underline;">pprz.net</a>! Saya menerima tempahan pembangunan projek kustom — bermula daripada <strong>reka bentuk laman web (Website Design)</strong>, <strong>membuat aplikasi (Web / Mobile / Desktop App)</strong>, sehinggalah <strong>membangunkan projek elektronik untuk mengawal sesuatu peralatan atau sistem secara IoT (Internet of Things)</strong>. Hubungi saya, <strong>MIKE</strong>!
        </p>
        <p style="font-size:11.2px; color:#94a3b8; line-height:1.45; border-top:1px dashed rgba(148,163,184,0.2); padding-top:4px;">
          🇬🇧 <strong>EN:</strong> Check out my new official website at <a href="https://pprz.net" target="_blank" style="color:var(--gold); font-weight:800; text-decoration:underline;">pprz.net</a>! Open for custom project commissions &amp; freelance orders — ranging from <strong>Website Design</strong> and <strong>Custom Application Development</strong> to <strong>Electronic Hardware Engineering &amp; IoT (Internet of Things) Remote Control Systems</strong>. Feel free to reach out — just call me <strong>MIKE</strong>!
        </p>
      </div>
    </div>

    <!-- 3. Syarat Lesen Open Source / Open Source License & Attribution (BM + EN) -->
    <div style="background:#0a101d; border:1px solid var(--border); border-left:4px solid #3b82f6; border-radius:6px; padding:10px 14px; display:flex; flex-direction:column; gap:4px;">
      <div style="font-size:11px; font-weight:800; color:#60a5fa; text-transform:uppercase; letter-spacing:0.5px;">📜 Lesen Penggunaan Terbuka / Open Source License &amp; Attribution</div>
      <p style="font-size:11.3px; color:#cbd5e1; line-height:1.45;">
        🇲🇾 <strong>BM:</strong> Sesiapa sahaja bebas menggunakan, mengubah suai, dan berkongsi aplikasi/tools ini secara percuma di bawah <em>MIT License with Attribution Requirement</em>, <strong>selagi mereka bersetuju untuk TIDAK membuang nama pencipta asal (<code>MIKE</code> / <code>mikex16</code> / <code>MegaOBSStudio</code>)</strong> di dalam setiap skrip yang kami tulis serta pada ruangan kredit ini.
      </p>
      <p style="font-size:11px; color:#94a3b8; line-height:1.45; border-top:1px dashed rgba(148,163,184,0.18); padding-top:4px;">
        🇬🇧 <strong>EN:</strong> Anyone is free to use, modify, and distribute this software/tool free of charge under the <em>MIT License with Attribution Requirement</em>, <strong>provided that they agree NOT to remove the original creator's name and attribution headers (<code>MIKE</code> / <code>mikex16</code> / <code>MegaOBSStudio</code>)</strong> from any script or credit section.
      </p>
    </div>

    <!-- 4. Penafian / Disclaimer (BM + EN) -->
    <div style="background:rgba(245, 158, 11, 0.08); border:1px solid rgba(245, 158, 11, 0.35); border-left:4px solid var(--warn-amber); border-radius:6px; padding:10px 14px; display:flex; flex-direction:column; gap:4px;">
      <div style="font-size:11px; font-weight:800; color:#fbbf24; text-transform:uppercase; letter-spacing:0.5px;">⚠️ Penafian / Disclaimer (Gunakan Atas Risiko Sendiri / Use At Your Own Risk)</div>
      <p style="font-size:11.3px; color:#fde68a; line-height:1.45;">
        🇲🇾 <strong>BM:</strong> Aplikasi ini dihasilkan <strong>seadanya ("AS-IS")</strong> dan tiada jaminan ia akan berfungsi 100% sempurna dalam semua keadaan. <strong>Sila uji terlebih dahulu dan gunakan atas risiko sendiri.</strong>
      </p>
      <p style="font-size:11px; color:#fcd34d; opacity:0.88; line-height:1.45; border-top:1px dashed rgba(245,158,11,0.25); padding-top:4px;">
        🇬🇧 <strong>EN:</strong> This software is provided <strong>"AS-IS"</strong> without any warranty of 100% uninterrupted functionality across all hardware or network environments. <strong>Please test thoroughly before live broadcast and use at your own risk.</strong>
      </p>
    </div>

    <!-- 5. Senarai Sumber & Teknologi (Sources & Credits) -->
    <div style="background:#0a101d; border:1px solid var(--border); border-radius:6px; padding:10px 14px; display:flex; flex-direction:column; gap:5px;">
      <div style="font-size:11px; font-weight:800; color:#34d399; text-transform:uppercase; letter-spacing:0.5px;">📚 Sumber-Sumber &amp; Rujukan Teknologi / Sources &amp; Acknowledgments</div>
      <ul style="font-size:11px; color:#cbd5e1; line-height:1.55; padding-left:18px; display:grid; grid-template-columns:1fr 1fr; gap:3px 16px;">
        <li><strong>OBS Studio &amp; OBS WebSocket v5</strong> (<code>obsproject.com</code>) — Remote scene, camera &amp; audio mixer control.</li>
        <li><strong>Easyslides 5.2</strong> (<code>easyslides.com</code>) — SQLite <code>items</code> schema &amp; TCP Multicast Port 9701 protocol.</li>
        <li><strong>JRChord.com</strong> (<code>jrchord.com</code>) — Christian worship song lyrics collection (chord-stripped).</li>
        <li><strong>LaguRohani.id</strong> (<code>lagurohani.id</code>) — Praise &amp; worship lyrics database reference.</li>
        <li><strong>Alkitab.app Songbooks</strong> (<code>alkitab.app/songs</code>) — 23 Christian Hymnal databases (KJ, PKJ, NKB, NKI, BN, etc.).</li>
        <li><strong>Alkitab Terjemahan Baru (TB)</strong> — Scripture verse presets &amp; lower-third references.</li>
        <li><strong>Google Fonts &amp; HTML5 Canvas</strong> — Broadcast typography (<em>Cinzel, Outfit, Plus Jakarta Sans, Inter</em>) &amp; 60FPS graphics.</li>
        <li><strong>XAMPP Apache + PHP 8 SQLite</strong> — 100% standalone local server engine without external dependencies.</li>
      </ul>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; padding-top:4px; flex-wrap:wrap; gap:8px;">
      <span style="font-size:10.5px; color:var(--text-muted);">© 2026 <strong>MIKE (mikex16)</strong> • 🌐 <a href="https://pprz.net" target="_blank" style="color:var(--gold); font-weight:800; text-decoration:none;">pprz.net</a> • WhatsApp: <strong>0166841604</strong> • Email: <strong>mikex16@gmail.com</strong> • Tuhan Yesus Memberkati!</span>
      <button class="btn btn-gold" onclick="closeModalById('aboutUsModal')">Tutup / Close</button>
    </div>
  </div>
</div>

<div class="toast-box" id="toastBox"></div>

<script>
/**
 * ============================================================================
 * MEGA OBS STUDIO — UNIFIED ONE-SCREEN CONTROLLER
 * Integrates:
 * 1. OBS Studio WebSocket v5 + PHP Bridge + HTML5 Canvas Deck
 * 2. SIB Pekan Papar Church Broadcast HUD & Lower-Thirds
 * 3. Easyslides 28 SQLite Lyric Databases + Multicast 9701 + Lyric Overlay
 * ============================================================================
 */
const INITIAL_CONFIG = <?= json_encode($obsWsConfig, JSON_UNESCAPED_SLASHES) ?>;
const BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES) ?>;

/* -------------------- PURE JS SHA-256 + BASE64 FOR LAN HTTP -------------------- */
const Sha256Auth = (() => {
  const K = new Uint32Array([
    0x428a2f98,0x71374491,0xb5c0fbcf,0xe9b5dba5,0x3956c25b,0x59f111f1,0x923f82a4,0xab1c5ed5,
    0xd807aa98,0x12835b01,0x243185be,0x550c7dc3,0x72be5d74,0x80deb1fe,0x9bdc06a7,0xc19bf174,
    0xe49b69c1,0xefbe4786,0x0fc19dc6,0x240ca1cc,0x2de92c6f,0x4a7484aa,0x5cb0a9dc,0x76f988da,
    0x983e5152,0xa831c66d,0xb00327c8,0xbf597fc7,0xc6e00bf3,0xd5a79147,0x06ca6351,0x14292967,
    0x27b70a85,0x2e1b2138,0x4d2c6dfc,0x53380d13,0x650a7354,0x766a0abb,0x81c2c92e,0x92722c85,
    0xa2bfe8a1,0xa81a664b,0xc24b8b70,0xc76c51a3,0xd192e819,0xd6990624,0xf40e3585,0x106aa070,
    0x19a4c116,0x1e376c08,0x2748774c,0x34b0bcb5,0x391c0cb3,0x4ed8aa4a,0x5b9cca4f,0x682e6ff3,
    0x748f82ee,0x78a5636f,0x84c87814,0x8cc70208,0x90befffa,0xa4506ceb,0xbef9a3f7,0xc67178f2
  ]);
  function rotr(n, x) { return (x >>> n) | (x << (32 - n)); }
  function hashBytes(msgBytes) {
    const l = msgBytes.length;
    const bitLenHi = Math.floor((l * 8) / 0x100000000);
    const bitLenLo = (l * 8) >>> 0;
    const padLen = ((l + 9 + 63) & ~63);
    const buf = new Uint8Array(padLen);
    buf.set(msgBytes);
    buf[l] = 0x80;
    const dv = new DataView(buf.buffer);
    dv.setUint32(padLen - 8, bitLenHi, false);
    dv.setUint32(padLen - 4, bitLenLo, false);
    let H0 = 0x6a09e667, H1 = 0xbb67ae85, H2 = 0x3c6ef372, H3 = 0xa54ff53a;
    let H4 = 0x510e527f, H5 = 0x9b05688c, H6 = 0x1f83d9ab, H7 = 0x5be0cd19;
    const W = new Uint32Array(64);
    for (let offset = 0; offset < padLen; offset += 64) {
      for (let t = 0; t < 16; t++) W[t] = dv.getUint32(offset + t * 4, false);
      for (let t = 16; t < 64; t++) {
        const s0 = rotr(7, W[t - 15]) ^ rotr(18, W[t - 15]) ^ (W[t - 15] >>> 3);
        const s1 = rotr(17, W[t - 2]) ^ rotr(19, W[t - 2]) ^ (W[t - 2] >>> 10);
        W[t] = (W[t - 16] + s0 + W[t - 7] + s1) >>> 0;
      }
      let a = H0, b = H1, c = H2, d = H3, e = H4, f = H5, g = H6, h = H7;
      for (let t = 0; t < 64; t++) {
        const S1 = rotr(6, e) ^ rotr(11, e) ^ rotr(25, e);
        const ch = (e & f) ^ (~e & g);
        const temp1 = (h + S1 + ch + K[t] + W[t]) >>> 0;
        const S0 = rotr(2, a) ^ rotr(13, a) ^ rotr(22, a);
        const maj = (a & b) ^ (a & c) ^ (b & c);
        const temp2 = (S0 + maj) >>> 0;
        h = g; g = f; f = e; e = (d + temp1) >>> 0;
        d = c; c = b; b = a; a = (temp1 + temp2) >>> 0;
      }
      H0 = (H0 + a) >>> 0; H1 = (H1 + b) >>> 0; H2 = (H2 + c) >>> 0; H3 = (H3 + d) >>> 0;
      H4 = (H4 + e) >>> 0; H5 = (H5 + f) >>> 0; H6 = (H6 + g) >>> 0; H7 = (H7 + h) >>> 0;
    }
    const out = new Uint8Array(32);
    const outDv = new DataView(out.buffer);
    [H0, H1, H2, H3, H4, H5, H6, H7].forEach((h, i) => outDv.setUint32(i * 4, h, false));
    return out;
  }
  function bytesToBase64(bytes) {
    let bin = '';
    for (let i = 0; i < bytes.length; i++) bin += String.fromCharCode(bytes[i]);
    return btoa(bin);
  }
  function strToBytes(str) { return new TextEncoder().encode(str); }
  return {
    computeObsAuth(password, salt, challenge) {
      const secretHash = hashBytes(strToBytes(password + salt));
      const secretB64 = bytesToBase64(secretHash);
      const authHash = hashBytes(strToBytes(secretB64 + challenge));
      return bytesToBase64(authHash);
    }
  };
})();

/* -------------------- CENTRAL APPLICATION STATE -------------------- */
const state = {
  connected: false,
  connecting: false,
  simMode: false,
  activeTransport: 'none',
  host: INITIAL_CONFIG.host || '192.168.0.13',
  port: Number(INITIAL_CONFIG.port || 4455),
  password: INITIAL_CONFIG.password || 'tfe73rv1jpVWKqVF',
  connectionMode: INITIAL_CONFIG.connectionMode || 'auto',

  obsVersion: '5.x',
  studioMode: false,
  baseWidth: 1920,
  baseHeight: 1080,

  currentProgramScene: 'Main Camera',
  currentPreviewScene: 'Starting Soon',
  scenes: [],
  sceneItems: [],
  selectedSceneItemId: null,

  transitions: ['Fade', 'Cut', 'Swipe', 'Slide', 'Stinger'],
  currentTransition: 'Fade',
  transitionDuration: 300,
  tbarPosition: 0.0,

  collections: ['Default'],
  currentCollection: 'Default',
  profiles: ['Untitled'],
  currentProfile: 'Untitled',

  inputs: [],
  audioLevels: {},

  stream: { active: false, timecode: '00:00:00', bytes: 0, kbps: 0 },
  record: { active: false, paused: false, timecode: '00:00:00' },
  vcam: { active: false },
  replay: { active: false },

  stats: { fps: 60.0, cpuUsage: 3.2, memoryUsage: 410.0, renderMissed: 0, outputSkipped: 0, kbps: 0 },
  telemetryHistory: [],

  canvasOpts: {
    showComposite: true,
    showBoxes: INITIAL_CONFIG.showBoxes !== false,
    showGrid: INITIAL_CONFIG.showGrid !== false,
    showSafeZones: INITIAL_CONFIG.showSafeZones !== false,
    showOsd: INITIAL_CONFIG.showOsd !== false,
    previewFps: Number(INITIAL_CONFIG.previewFps ?? 8)
  },

  programImage: null,
  previewImage: null,
  lastBytesTotal: 0,
  lastBytesTimestamp: 0,
  macros: INITIAL_CONFIG.macros || []
};

function showToast(msg, borderColor = '#3b82f6') {
  const box = document.getElementById('toastBox');
  const el = document.createElement('div');
  el.className = 'toast';
  el.style.borderColor = borderColor;
  el.textContent = msg;
  box.appendChild(el);
  setTimeout(() => el.remove(), 3000);
}

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function updateConnBadge(status, label) {
  const badge = document.getElementById('connBadge');
  const text = document.getElementById('connStateText');
  badge.className = 'conn-badge ' + status;
  text.textContent = label;
}

/* ============================================================================
 * PART 1: OBS WEBSOCKET V5 CLIENT (DIRECT + PHP BRIDGE + SIMULATION)
 * ============================================================================ */
let ws = null;
let wsPending = new Map();
let reqCounter = 1;
let reconnectTimer = null;
let previewLoopTimer = null;
let statsLoopTimer = null;
let screenshotInFlight = false;

function connectObs() {
  clearTimeout(reconnectTimer);
  state.host = document.getElementById('wsHost').value.trim() || '192.168.0.13';
  state.port = Number(document.getElementById('wsPort').value) || 4455;
  state.password = document.getElementById('wsPass').value;
  state.connectionMode = document.getElementById('connModeSelect').value;

  fetch('obs_ws_api.php?action=save_config', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      host: state.host,
      port: state.port,
      password: state.password,
      connectionMode: state.connectionMode
    })
  }).catch(() => {});

  if (state.simMode) {
    state.simMode = false;
    document.getElementById('btnSimMode').classList.remove('active-toggle');
  }

  if (ws) {
    try { ws.onclose = null; ws.close(); } catch (e) {}
    ws = null;
  }

  if (state.connectionMode === 'php') {
    connectViaPhpBridge();
    return;
  }

  state.connecting = true;
  updateConnBadge('connecting', `WS ${state.host}:${state.port}`);

  try {
    ws = new WebSocket(`ws://${state.host}:${state.port}`, 'obswebsocket.json');
  } catch (err) {
    handleDirectConnectFail();
    return;
  }

  const connTimeout = setTimeout(() => {
    if (!state.connected && ws && ws.readyState !== WebSocket.OPEN) {
      try { ws.close(); } catch (e) {}
      handleDirectConnectFail();
    }
  }, 3200);

  ws.onmessage = async (ev) => {
    let msg;
    try { msg = JSON.parse(ev.data); } catch (e) { return; }

    if (msg.op === 0) {
      const d = msg.d || {};
      state.obsVersion = d.obsWebSocketVersion || '5.x';
      const identifyPayload = {
        rpcVersion: 1,
        eventSubscriptions: 2047 | (1 << 16)
      };
      if (d.authentication && d.authentication.challenge && d.authentication.salt) {
        identifyPayload.authentication = Sha256Auth.computeObsAuth(
          state.password,
          d.authentication.salt,
          d.authentication.challenge
        );
      }
      ws.send(JSON.stringify({ op: 1, d: identifyPayload }));
    } else if (msg.op === 2) {
      clearTimeout(connTimeout);
      state.connected = true;
      state.connecting = false;
      state.activeTransport = 'direct-ws';
      updateConnBadge('connected', 'LIVE WS v5');
      showToast(`Connected to OBS ${state.host}:${state.port}`, '#10b981');
      await fetchFullObsState();
      startBackgroundLoops();
    } else if (msg.op === 5) {
      handleObsEvent(msg.d);
    } else if (msg.op === 7) {
      const d = msg.d || {};
      const pending = wsPending.get(d.requestId);
      if (pending) {
        wsPending.delete(d.requestId);
        pending.resolve(d);
      }
    }
  };

  ws.onerror = () => clearTimeout(connTimeout);

  ws.onclose = (ev) => {
    clearTimeout(connTimeout);
    const wasConnected = state.connected;
    state.connected = false;
    state.connecting = false;
    if (ev.code === 4009) {
      updateConnBadge('disconnected', 'AUTH FAIL');
      showToast('OBS WebSocket Authentication Failed (Check password)', '#ef4444');
      return;
    }
    if (!wasConnected && state.connectionMode === 'auto') {
      handleDirectConnectFail();
    } else {
      updateConnBadge('disconnected', 'OFFLINE');
      reconnectTimer = setTimeout(() => {
        if (!state.connected && !state.simMode) connectObs();
      }, 4500);
    }
  };
}

async function handleDirectConnectFail() {
  if (state.connectionMode === 'auto') {
    await connectViaPhpBridge();
  } else {
    state.connected = false;
    state.connecting = false;
    updateConnBadge('disconnected', 'OFFLINE');
  }
}

async function connectViaPhpBridge() {
  updateConnBadge('connecting', 'PHP BRIDGE...');
  try {
    const resp = await fetch('obs_ws_api.php?action=full_state&includeScreenshot=1', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ host: state.host, port: state.port, password: state.password })
    });
    const json = await resp.json();
    if (json && json.ok && json.state) {
      state.connected = true;
      state.connecting = false;
      state.activeTransport = 'php-bridge';
      updateConnBadge('connected', 'PHP BRIDGE');
      showToast(`Connected via PHP WebSocket Bridge (${state.host}:${state.port})`, '#10b981');
      applyPhpStateSnapshot(json.state);
      startBackgroundLoops();
      return;
    }
    throw new Error(json.error || 'OBS unreachable');
  } catch (err) {
    state.connected = false;
    state.connecting = false;
    updateConnBadge('disconnected', 'OBS OFFLINE');
    reconnectTimer = setTimeout(() => {
      if (!state.connected && !state.simMode) connectObs();
    }, 5500);
  }
}

async function obsRequest(requestType, requestData = {}) {
  if (state.simMode) return simulateObsRequest(requestType, requestData);

  if (state.connected && state.activeTransport === 'direct-ws' && ws && ws.readyState === WebSocket.OPEN) {
    const requestId = 'req_' + (reqCounter++);
    return new Promise((resolve) => {
      const timer = setTimeout(() => {
        wsPending.delete(requestId);
        resolve({ requestStatus: { result: false, comment: 'Timeout' }, responseData: {} });
      }, 3000);
      wsPending.set(requestId, {
        resolve: (d) => {
          clearTimeout(timer);
          resolve(d);
        }
      });
      ws.send(JSON.stringify({ op: 6, d: { requestType, requestId, requestData } }));
    });
  }

  try {
    const resp = await fetch('obs_ws_api.php?action=request', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        host: state.host,
        port: state.port,
        password: state.password,
        requestType,
        requestData
      })
    });
    const json = await resp.json();
    return { requestStatus: { result: !!json.ok }, responseData: json.data || {} };
  } catch (e) {
    return { requestStatus: { result: false }, responseData: {} };
  }
}

async function fetchFullObsState() {
  if (state.activeTransport === 'php-bridge') {
    const resp = await fetch('obs_ws_api.php?action=full_state&includeScreenshot=1', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ host: state.host, port: state.port, password: state.password })
    });
    const json = await resp.json();
    if (json.ok && json.state) applyPhpStateSnapshot(json.state);
    return;
  }

  const [
    ver, video, scenes, trans, studio, stream, record, vcam, replay, inputs, collections, profiles, stats
  ] = await Promise.all([
    obsRequest('GetVersion'),
    obsRequest('GetVideoSettings'),
    obsRequest('GetSceneList'),
    obsRequest('GetSceneTransitionList'),
    obsRequest('GetStudioModeEnabled'),
    obsRequest('GetStreamStatus'),
    obsRequest('GetRecordStatus'),
    obsRequest('GetVirtualCamStatus'),
    obsRequest('GetReplayBufferStatus'),
    obsRequest('GetInputList'),
    obsRequest('GetSceneCollectionList'),
    obsRequest('GetProfileList'),
    obsRequest('GetStats')
  ]);

  if (ver.responseData?.obsVersion) {
    document.getElementById('obsVersionText').textContent =
      `OBS ${ver.responseData.obsVersion} • Church HUD • 28 Lyric DBs`;
  }
  if (video.responseData?.baseWidth) {
    state.baseWidth = video.responseData.baseWidth;
    state.baseHeight = video.responseData.baseHeight;
  }
  if (scenes.responseData?.scenes) {
    state.scenes = [...scenes.responseData.scenes].reverse();
    state.currentProgramScene = scenes.responseData.currentProgramSceneName || '';
    state.currentPreviewScene = scenes.responseData.currentPreviewSceneName || state.currentProgramScene;
  }
  if (trans.responseData?.transitions) {
    state.transitions = trans.responseData.transitions.map(t => t.transitionName);
    state.currentTransition = trans.responseData.currentSceneTransitionName || state.transitions[0] || 'Fade';
    state.transitionDuration = trans.responseData.currentSceneTransitionDuration ?? 300;
  }
  if (studio.responseData !== undefined) state.studioMode = !!studio.responseData.studioModeEnabled;
  if (stream.responseData) {
    state.stream.active = !!stream.responseData.outputActive;
    state.stream.timecode = (stream.responseData.outputTimecode || '00:00:00').split('.')[0];
  }
  if (record.responseData) {
    state.record.active = !!record.responseData.outputActive;
    state.record.paused = !!record.responseData.outputPaused;
    state.record.timecode = (record.responseData.outputTimecode || '00:00:00').split('.')[0];
  }
  if (vcam.responseData) state.vcam.active = !!vcam.responseData.outputActive;
  if (replay.responseData) state.replay.active = !!replay.responseData.outputActive;
  if (collections.responseData?.sceneCollections) {
    state.collections = collections.responseData.sceneCollections;
    state.currentCollection = collections.responseData.currentSceneCollectionName || '';
  }
  if (profiles.responseData?.profiles) {
    state.profiles = profiles.responseData.profiles;
    state.currentProfile = profiles.responseData.currentProfileName || '';
  }
  if (stats.responseData) applyObsStats(stats.responseData);

  await fetchSceneItems(state.currentProgramScene);

  if (inputs.responseData?.inputs) {
    const audioInputs = [];
    for (const inp of inputs.responseData.inputs) {
      const [volResp, muteResp] = await Promise.all([
        obsRequest('GetInputVolume', { inputName: inp.inputName }),
        obsRequest('GetInputMute', { inputName: inp.inputName })
      ]);
      if (volResp.requestStatus?.result) {
        audioInputs.push({
          inputName: inp.inputName,
          inputKind: inp.inputKind,
          inputVolumeDb: volResp.responseData.inputVolumeDb ?? 0,
          inputVolumeMul: volResp.responseData.inputVolumeMul ?? 1,
          inputMuted: muteResp.responseData?.inputMuted ?? false
        });
        if (!state.audioLevels[inp.inputName]) {
          state.audioLevels[inp.inputName] = { leftDb: -60, rightDb: -60, peakLeftDb: -60, peakRightDb: -60, clip: false };
        }
      }
    }
    state.inputs = audioInputs;
  }

  renderAllDomPanels();
  fetchCanvasScreenshots();
}

async function fetchSceneItems(sceneName) {
  if (!sceneName) return;
  const resp = await obsRequest('GetSceneItemList', { sceneName });
  if (resp.responseData?.sceneItems) {
    const items = [...resp.responseData.sceneItems].reverse();
    state.sceneItems = items;
    if (!items.some(i => i.sceneItemId === state.selectedSceneItemId)) {
      state.selectedSceneItemId = items[0]?.sceneItemId ?? null;
    }
    renderSourcesPanel();
    updateSelectedTransformBar();
  }
}

function applyPhpStateSnapshot(map) {
  if (map.version?.obsVersion) {
    document.getElementById('obsVersionText').textContent = `OBS ${map.version.obsVersion} • PHP Bridge`;
  }
  if (map.video?.baseWidth) {
    state.baseWidth = map.video.baseWidth;
    state.baseHeight = map.video.baseHeight;
  }
  if (map.scenes?.scenes) {
    state.scenes = [...map.scenes.scenes].reverse();
    state.currentProgramScene = map.scenes.currentProgramSceneName || '';
    state.currentPreviewScene = map.scenes.currentPreviewSceneName || state.currentProgramScene;
  }
  if (map.transitions?.transitions) {
    state.transitions = map.transitions.transitions.map(t => t.transitionName);
    state.currentTransition = map.transitions.currentSceneTransitionName || 'Fade';
    state.transitionDuration = map.transitions.currentSceneTransitionDuration ?? 300;
  }
  if (map.studioMode) state.studioMode = !!map.studioMode.studioModeEnabled;
  if (map.stream) {
    state.stream.active = !!map.stream.outputActive;
    state.stream.timecode = (map.stream.outputTimecode || '00:00:00').split('.')[0];
  }
  if (map.record) {
    state.record.active = !!map.record.outputActive;
    state.record.timecode = (map.record.outputTimecode || '00:00:00').split('.')[0];
  }
  if (map.vcam) state.vcam.active = !!map.vcam.outputActive;
  if (map.replay) state.replay.active = !!map.replay.outputActive;
  if (map.sceneItems) {
    state.sceneItems = [...map.sceneItems].reverse();
    if (!state.sceneItems.some(i => i.sceneItemId === state.selectedSceneItemId)) {
      state.selectedSceneItemId = state.sceneItems[0]?.sceneItemId ?? null;
    }
  }
  if (map.inputs?.inputs) {
    state.inputs = map.inputs.inputs.filter(i => i.hasAudio);
    state.inputs.forEach(inp => {
      if (!state.audioLevels[inp.inputName]) {
        state.audioLevels[inp.inputName] = { leftDb: -60, rightDb: -60, peakLeftDb: -60, peakRightDb: -60, clip: false };
      }
    });
  }
  if (map.stats) applyObsStats(map.stats);
  if (map.programScreenshot) loadBase64IntoImage(map.programScreenshot, 'program');
  if (map.previewScreenshot) loadBase64IntoImage(map.previewScreenshot, 'preview');

  renderAllDomPanels();
}

function handleObsEvent(ev) {
  if (!ev) return;
  const type = ev.eventType;
  const d = ev.eventData || {};

  switch (type) {
    case 'CurrentProgramSceneChanged':
      state.currentProgramScene = d.sceneName;
      fetchSceneItems(d.sceneName);
      renderScenesPanel();
      fetchCanvasScreenshots();
      break;
    case 'CurrentPreviewSceneChanged':
      state.currentPreviewScene = d.sceneName;
      renderScenesPanel();
      fetchCanvasScreenshots();
      break;
    case 'SceneListChanged':
      fetchFullObsState();
      break;
    case 'StudioModeStateChanged':
      state.studioMode = !!d.studioModeEnabled;
      renderTransitionsPanel();
      fetchCanvasScreenshots();
      break;
    case 'SceneItemEnableStateChanged': {
      const item = state.sceneItems.find(i => i.sceneItemId === d.sceneItemId);
      if (item) { item.sceneItemEnabled = !!d.sceneItemEnabled; renderSourcesPanel(); }
      break;
    }
    case 'SceneItemLockStateChanged': {
      const item = state.sceneItems.find(i => i.sceneItemId === d.sceneItemId);
      if (item) { item.sceneItemLocked = !!d.sceneItemLocked; renderSourcesPanel(); }
      break;
    }
    case 'SceneItemTransformChanged': {
      const item = state.sceneItems.find(i => i.sceneItemId === d.sceneItemId);
      if (item && d.sceneItemTransform) {
        item.sceneItemTransform = d.sceneItemTransform;
        if (item.sceneItemId === state.selectedSceneItemId) updateSelectedTransformBar();
      }
      break;
    }
    case 'InputMuteStateChanged': {
      const inp = state.inputs.find(i => i.inputName === d.inputName);
      if (inp) { inp.inputMuted = !!d.inputMuted; renderAudioMixerPanel(); }
      break;
    }
    case 'InputVolumeChanged': {
      const inp = state.inputs.find(i => i.inputName === d.inputName);
      if (inp) {
        inp.inputVolumeDb = d.inputVolumeDb ?? inp.inputVolumeDb;
        inp.inputVolumeMul = d.inputVolumeMul ?? inp.inputVolumeMul;
        const dbEl = document.querySelector(`[data-mixer-db="${CSS.escape(inp.inputName)}"]`);
        if (dbEl) dbEl.textContent = `${inp.inputVolumeDb.toFixed(1)} dB`;
      }
      break;
    }
    case 'InputVolumeMeters': {
      if (Array.isArray(d.inputs)) {
        for (const meter of d.inputs) {
          const name = meter.inputName;
          const channels = meter.inputLevelsMul || [];
          const ch0 = channels[0] || [0, 0, 0];
          const ch1 = channels[1] || ch0;
          const mulToDb = (m) => (m > 0.00001 ? Math.max(-60, Math.min(0, 20 * Math.log10(m))) : -60);
          const leftDb = mulToDb(ch0[1] ?? ch0[0] ?? 0);
          const rightDb = mulToDb(ch1[1] ?? ch1[0] ?? 0);
          const prev = state.audioLevels[name] || { leftDb: -60, rightDb: -60, peakLeftDb: -60, peakRightDb: -60, clip: false };
          state.audioLevels[name] = {
            leftDb,
            rightDb,
            peakLeftDb: Math.max(leftDb, prev.peakLeftDb - 0.35),
            peakRightDb: Math.max(rightDb, prev.peakRightDb - 0.35),
            clip: leftDb > -0.5 || rightDb > -0.5
          };
        }
      }
      break;
    }
    case 'StreamStateChanged':
      state.stream.active = !!d.outputActive;
      renderOutputsPanel();
      break;
    case 'RecordStateChanged':
      state.record.active = !!d.outputActive;
      state.record.paused = !!d.outputPaused;
      renderOutputsPanel();
      break;
    case 'VirtualcamStateChanged':
      state.vcam.active = !!d.outputActive;
      renderOutputsPanel();
      break;
    case 'ReplayBufferStateChanged':
      state.replay.active = !!d.outputActive;
      renderOutputsPanel();
      break;
  }
}

function startBackgroundLoops() {
  clearInterval(previewLoopTimer);
  clearInterval(statsLoopTimer);
  const fps = state.canvasOpts.previewFps;
  if (fps > 0) {
    previewLoopTimer = setInterval(fetchCanvasScreenshots, Math.max(50, Math.round(1000 / fps)));
  }
  statsLoopTimer = setInterval(pollTelemetryStats, 1000);
}

async function fetchCanvasScreenshots() {
  if (screenshotInFlight || (!state.connected && !state.simMode)) return;
  if (state.simMode || state.canvasOpts.previewFps <= 0 || !state.currentProgramScene) return;

  screenshotInFlight = true;
  try {
    const pgmResp = await obsRequest('GetSourceScreenshot', {
      sourceName: state.currentProgramScene,
      imageFormat: 'jpg',
      imageWidth: 960,
      imageCompressionQuality: 75
    });
    if (pgmResp.responseData?.imageData) loadBase64IntoImage(pgmResp.responseData.imageData, 'program');

    if (state.studioMode && state.currentPreviewScene) {
      const pvwResp = await obsRequest('GetSourceScreenshot', {
        sourceName: state.currentPreviewScene,
        imageFormat: 'jpg',
        imageWidth: 640,
        imageCompressionQuality: 70
      });
      if (pvwResp.responseData?.imageData) loadBase64IntoImage(pvwResp.responseData.imageData, 'preview');
    }
  } catch (e) {
  } finally {
    screenshotInFlight = false;
  }
}

function loadBase64IntoImage(dataUri, target) {
  const img = new Image();
  img.onload = () => {
    if (target === 'program') state.programImage = img;
    else state.previewImage = img;
  };
  img.src = dataUri;
}

async function pollTelemetryStats() {
  if (state.simMode) { tickSimulationStats(); return; }
  if (!state.connected) return;

  const [statsResp, streamResp, recResp] = await Promise.all([
    obsRequest('GetStats'),
    obsRequest('GetStreamStatus'),
    obsRequest('GetRecordStatus')
  ]);
  if (statsResp.responseData) applyObsStats(statsResp.responseData);
  if (streamResp.responseData) {
    state.stream.active = !!streamResp.responseData.outputActive;
    state.stream.timecode = (streamResp.responseData.outputTimecode || '00:00:00').split('.')[0];
    const bytes = streamResp.responseData.outputBytes || 0;
    const now = performance.now();
    if (state.lastBytesTimestamp > 0 && bytes >= state.lastBytesTotal) {
      const dt = (now - state.lastBytesTimestamp) / 1000;
      if (dt > 0) state.stats.kbps = Math.round(((bytes - state.lastBytesTotal) * 8) / 1000 / dt);
    }
    state.lastBytesTotal = bytes;
    state.lastBytesTimestamp = now;
  }
  if (recResp.responseData) {
    state.record.active = !!recResp.responseData.outputActive;
    state.record.timecode = (recResp.responseData.outputTimecode || '00:00:00').split('.')[0];
  }
  pushTelemetryPoint();
  renderOutputsPanel();
}

function applyObsStats(s) {
  state.stats.fps = Number(s.activeFps || 60);
  state.stats.cpuUsage = Number(s.cpuUsage || 0);
  state.stats.memoryUsage = Number(s.memoryUsage || 0);
  state.stats.renderMissed = Number(s.renderMissedFrames || 0);
  state.stats.outputSkipped = Number(s.outputSkippedFrames || 0);
}

function pushTelemetryPoint() {
  state.telemetryHistory.push({ fps: state.stats.fps, cpu: state.stats.cpuUsage, kbps: state.stats.kbps });
  if (state.telemetryHistory.length > 60) state.telemetryHistory.shift();
}

/* -------------------- RENDER OBS DOM PANELS -------------------- */
function renderAllDomPanels() {
  renderScenesPanel();
  renderTransitionsPanel();
  renderSourcesPanel();
  renderAudioMixerPanel();
  renderOutputsPanel();
  renderMacroDeck();
  updateSelectedTransformBar();
}

function renderScenesPanel() {
  document.getElementById('sceneCountText').textContent = state.scenes.length;
  const grid = document.getElementById('scenesGrid');
  grid.innerHTML = '';

  state.scenes.forEach((sc, idx) => {
    const name = sc.sceneName;
    const isPgm = name === state.currentProgramScene;
    const isPvw = state.studioMode && name === state.currentPreviewScene;

    const btn = document.createElement('div');
    btn.className = `scene-btn ${isPgm ? 'is-program' : ''} ${isPvw ? 'is-preview' : ''}`;
    btn.innerHTML = `
      <div class="scene-index">
        <span>#${idx + 1}</span>
        <div style="display:flex; gap:2px;">
          ${isPvw ? '<span class="scene-tally-tag tag-pvw">PVW</span>' : ''}
          ${isPgm ? '<span class="scene-tally-tag tag-pgm">PGM</span>' : ''}
        </div>
      </div>
      <div class="scene-name">${escapeHtml(name)}</div>
    `;

    btn.addEventListener('click', async (ev) => {
      if (state.studioMode && !ev.shiftKey) {
        state.currentPreviewScene = name;
        renderScenesPanel();
        await obsRequest('SetCurrentPreviewScene', { sceneName: name });
        fetchCanvasScreenshots();
        showToast(`Preview CUE: ${name}`, '#10b981');
      } else {
        state.currentProgramScene = name;
        renderScenesPanel();
        await obsRequest('SetCurrentProgramScene', { sceneName: name });
        await fetchSceneItems(name);
        fetchCanvasScreenshots();
        showToast(`Program LIVE: ${name}`, '#ef4444');
      }
    });

    btn.addEventListener('dblclick', async () => {
      state.currentProgramScene = name;
      renderScenesPanel();
      await obsRequest('SetCurrentProgramScene', { sceneName: name });
      await fetchSceneItems(name);
      fetchCanvasScreenshots();
      showToast(`Cut to Program: ${name}`, '#ef4444');
    });

    grid.appendChild(btn);
  });

  document.getElementById('selectCollection').innerHTML = state.collections.map(c =>
    `<option value="${escapeHtml(c)}" ${c === state.currentCollection ? 'selected' : ''}>${escapeHtml(c)}</option>`
  ).join('');

  document.getElementById('selectProfile').innerHTML = state.profiles.map(p =>
    `<option value="${escapeHtml(p)}" ${p === state.currentProfile ? 'selected' : ''}>${escapeHtml(p)}</option>`
  ).join('');
}

function renderTransitionsPanel() {
  const studioBtn = document.getElementById('btnStudioMode');
  studioBtn.classList.toggle('active-toggle', state.studioMode);
  studioBtn.textContent = state.studioMode ? 'STUDIO: ON' : 'STUDIO MODE';

  document.getElementById('selectTransition').innerHTML = state.transitions.map(t =>
    `<option value="${escapeHtml(t)}" ${t === state.currentTransition ? 'selected' : ''}>${escapeHtml(t)}</option>`
  ).join('');
  document.getElementById('inputTransDuration').value = state.transitionDuration;
}

function renderSourcesPanel() {
  document.getElementById('activeSceneLabel').textContent = state.currentProgramScene || 'None';
  document.getElementById('sourceCountText').textContent = `${state.sceneItems.length}`;
  const list = document.getElementById('sourcesList');
  list.innerHTML = '';

  state.sceneItems.forEach((item) => {
    const id = item.sceneItemId;
    const name = item.sourceName;
    const enabled = item.sceneItemEnabled !== false;
    const locked = !!item.sceneItemLocked;
    const selected = id === state.selectedSceneItemId;
    const kind = (item.inputKind || item.sourceType || 'src').replace(/_v\d+$/, '');

    const row = document.createElement('div');
    row.className = `source-row ${selected ? 'selected' : ''}`;
    row.innerHTML = `
      <div class="source-left">
        <span class="source-name-text" title="${escapeHtml(name)}">${escapeHtml(name)}</span>
        <span class="source-type-badge">${escapeHtml(kind)}</span>
      </div>
      <div class="source-controls">
        <button class="icon-btn ${enabled ? 'on' : ''}" data-act="vis">${enabled ? '👁' : '🚫'}</button>
        <button class="icon-btn ${locked ? 'locked' : ''}" data-act="lock">${locked ? '🔒' : '🔓'}</button>
        <button class="icon-btn" data-act="up">▲</button>
        <button class="icon-btn" data-act="down">▼</button>
      </div>
    `;

    row.addEventListener('click', (ev) => {
      if (ev.target.closest('button')) return;
      state.selectedSceneItemId = id;
      renderSourcesPanel();
      updateSelectedTransformBar();
    });

    row.querySelector('[data-act="vis"]').addEventListener('click', async (ev) => {
      ev.stopPropagation();
      const next = !enabled;
      item.sceneItemEnabled = next;
      renderSourcesPanel();
      await obsRequest('SetSceneItemEnabled', { sceneName: state.currentProgramScene, sceneItemId: id, sceneItemEnabled: next });
      fetchCanvasScreenshots();
    });

    row.querySelector('[data-act="lock"]').addEventListener('click', async (ev) => {
      ev.stopPropagation();
      const next = !locked;
      item.sceneItemLocked = next;
      renderSourcesPanel();
      await obsRequest('SetSceneItemLocked', { sceneName: state.currentProgramScene, sceneItemId: id, sceneItemLocked: next });
    });

    row.querySelector('[data-act="up"]').addEventListener('click', async (ev) => {
      ev.stopPropagation();
      const newIdx = Math.min(state.sceneItems.length - 1, (item.sceneItemIndex ?? 0) + 1);
      await obsRequest('SetSceneItemIndex', { sceneName: state.currentProgramScene, sceneItemId: id, sceneItemIndex: newIdx });
      await fetchSceneItems(state.currentProgramScene);
    });

    row.querySelector('[data-act="down"]').addEventListener('click', async (ev) => {
      ev.stopPropagation();
      const newIdx = Math.max(0, (item.sceneItemIndex ?? 0) - 1);
      await obsRequest('SetSceneItemIndex', { sceneName: state.currentProgramScene, sceneItemId: id, sceneItemIndex: newIdx });
      await fetchSceneItems(state.currentProgramScene);
    });

    list.appendChild(row);
  });
}

function renderAudioMixerPanel() {
  const list = document.getElementById('mixerList');
  list.innerHTML = '';

  state.inputs.forEach((inp) => {
    const name = inp.inputName;
    const db = Number(inp.inputVolumeDb ?? 0);
    const muted = !!inp.inputMuted;

    const div = document.createElement('div');
    div.className = 'mixer-channel';
    div.innerHTML = `
      <div class="mixer-top">
        <span class="mixer-name" title="${escapeHtml(name)}">${escapeHtml(name)}</span>
        <span class="mixer-db" data-mixer-db="${escapeHtml(name)}">${muted ? 'MUTED' : db.toFixed(1) + ' dB'}</span>
      </div>
      <canvas class="vu-canvas" data-vu-canvas="${escapeHtml(name)}" width="240" height="10"></canvas>
      <div class="mixer-controls">
        <button class="btn btn-xs ${muted ? 'btn-danger' : ''}" data-mute-btn="${escapeHtml(name)}" style="min-width:42px;">
          ${muted ? '🔇' : '🔊'}
        </button>
        <input type="range" class="vol-slider" data-mixer-slider="${escapeHtml(name)}"
               min="-60" max="0" step="0.5" value="${Math.max(-60, Math.min(0, db))}">
      </div>
    `;

    div.querySelector('[data-mute-btn]').addEventListener('click', async () => {
      const next = !inp.inputMuted;
      inp.inputMuted = next;
      renderAudioMixerPanel();
      await obsRequest('SetInputMute', { inputName: name, inputMuted: next });
    });

    div.querySelector('[data-mixer-slider]').addEventListener('input', async (ev) => {
      const valDb = parseFloat(ev.target.value);
      inp.inputVolumeDb = valDb;
      const dbLabel = div.querySelector('[data-mixer-db]');
      if (dbLabel && !inp.inputMuted) dbLabel.textContent = `${valDb.toFixed(1)} dB`;
      await obsRequest('SetInputVolume', { inputName: name, inputVolumeDb: valDb });
    });

    list.appendChild(div);
  });
}

function renderOutputsPanel() {
  document.getElementById('pillStream').classList.toggle('live-active', state.stream.active);
  document.getElementById('streamTimeText').textContent = state.stream.active ? state.stream.timecode : 'OFFLINE';

  document.getElementById('pillRecord').classList.toggle('rec-active', state.record.active);
  document.getElementById('recordTimeText').textContent = state.record.active ? state.record.timecode : 'STANDBY';

  document.getElementById('fpsTopText').textContent = state.stats.fps.toFixed(1);
  document.getElementById('bitrateTopText').textContent = `${state.stats.kbps} kbps`;

  document.getElementById('btnToggleStream').classList.toggle('active-stream', state.stream.active);
  document.getElementById('badgeStream').textContent = state.stream.active ? 'LIVE' : 'OFF';

  document.getElementById('btnToggleRecord').classList.toggle('active-record', state.record.active);
  document.getElementById('badgeRecord').textContent = state.record.active ? 'REC' : 'OFF';

  document.getElementById('btnToggleVCam').classList.toggle('active-vcam', state.vcam.active);
  document.getElementById('badgeVCam').textContent = state.vcam.active ? 'ON' : 'OFF';

  document.getElementById('btnToggleReplay').classList.toggle('active-replay', state.replay.active);
  document.getElementById('badgeReplay').textContent = state.replay.active ? 'ON' : 'OFF';
}

function renderMacroDeck() {
  const grid = document.getElementById('macroGrid');
  grid.innerHTML = '';
  state.macros.forEach((m) => {
    const btn = document.createElement('div');
    btn.className = 'macro-chip';
    btn.style.background = `linear-gradient(145deg, ${m.color}cc 0%, ${m.color}55 100%)`;
    btn.style.borderColor = m.color;
    btn.textContent = m.label;
    btn.title = `Execute Macro: ${m.label}`;
    btn.addEventListener('click', () => runMacro(m));
    grid.appendChild(btn);
  });
}

async function runMacro(macro) {
  showToast(`Macro: ${macro.label}`, macro.color);
  for (const act of (macro.actions || [])) {
    if (act.type === 'CustomMuteAll') {
      for (const inp of state.inputs) {
        inp.inputMuted = !!act.data.mute;
        await obsRequest('SetInputMute', { inputName: inp.inputName, inputMuted: !!act.data.mute });
      }
      renderAudioMixerPanel();
    } else {
      await obsRequest(act.type, act.data || {});
    }
  }
  setTimeout(fetchFullObsState, 350);
}

/* ============================================================================
 * HTML5 CANVAS STUDIO STAGE + COMPOSITE HUD/LYRIC OVERLAY ALIGNMENT
 * ============================================================================ */
const studioCanvas = document.getElementById('studioCanvas');
const studioCtx = studioCanvas.getContext('2d');
let dragState = { active: false };

function resizeStudioCanvas() {
  const container = document.getElementById('canvasContainer');
  const dpr = window.devicePixelRatio || 1;
  const w = container.clientWidth;
  const h = container.clientHeight;
  if (studioCanvas.width !== Math.floor(w * dpr) || studioCanvas.height !== Math.floor(h * dpr)) {
    studioCanvas.width = Math.floor(w * dpr);
    studioCanvas.height = Math.floor(h * dpr);
  }
}

function getStageViewportRect(W, H, isDualStudio, side = 'program') {
  const pad = 10;
  const aspect = (state.baseWidth || 1920) / (state.baseHeight || 1080);

  if (!isDualStudio) {
    let availW = W - pad * 2;
    let availH = H - pad * 2;
    let drawW = availW;
    let drawH = drawW / aspect;
    if (drawH > availH) {
      drawH = availH;
      drawW = drawH * aspect;
    }
    return {
      x: (W - drawW) / 2,
      y: (H - drawH) / 2,
      w: drawW,
      h: drawH,
      scale: drawW / (state.baseWidth || 1920)
    };
  } else {
    const halfW = (W - pad * 3) / 2;
    const availH = H - pad * 2 - 12;
    let drawW = halfW;
    let drawH = drawW / aspect;
    if (drawH > availH) {
      drawH = availH;
      drawW = drawH * aspect;
    }
    const y = (H - drawH) / 2 + 4;
    const xLeft = pad + (halfW - drawW) / 2;
    const xRight = pad * 2 + halfW + (halfW - drawW) / 2;
    return {
      x: side === 'preview' ? xLeft : xRight,
      y,
      w: drawW,
      h: drawH,
      scale: drawW / (state.baseWidth || 1920)
    };
  }
}

function syncCompositeOverlayPosition() {
  const wrap = document.getElementById('compositeOverlayWrap');
  if (!state.canvasOpts.showComposite) {
    wrap.classList.remove('visible');
    return;
  }
  wrap.classList.add('visible');
  const container = document.getElementById('canvasContainer');
  const cw = container.clientWidth;
  const ch = container.clientHeight;
  const vp = getStageViewportRect(cw, ch, state.studioMode, 'program');

  wrap.style.left = `${vp.x}px`;
  wrap.style.top = `${vp.y}px`;
  wrap.style.width = `${vp.w}px`;
  wrap.style.height = `${vp.h}px`;

  const scale = vp.w / 1920;
  document.getElementById('compositeHudIframe').style.transform = `scale(${scale})`;
  document.getElementById('compositeLyricIframe').style.transform = `scale(${scale})`;
}

function getItemScreenBox(item, vp) {
  const t = item.sceneItemTransform || {};
  const posX = Number(t.positionX ?? 0);
  const posY = Number(t.positionY ?? 0);
  const width = Number(t.width ?? ((t.sourceWidth || 640) * (t.scaleX || 1)));
  const height = Number(t.height ?? ((t.sourceHeight || 360) * (t.scaleY || 1)));
  return {
    x: vp.x + posX * vp.scale,
    y: vp.y + posY * vp.scale,
    w: Math.max(24, width * vp.scale),
    h: Math.max(18, height * vp.scale)
  };
}

function drawStudioStageFrame(timestamp) {
  resizeStudioCanvas();
  syncCompositeOverlayPosition();

  const W = studioCanvas.width;
  const H = studioCanvas.height;
  const ctx = studioCtx;
  ctx.clearRect(0, 0, W, H);
  ctx.fillStyle = '#060a12';
  ctx.fillRect(0, 0, W, H);

  if (state.studioMode) {
    const pvwRect = getStageViewportRect(W, H, true, 'preview');
    const pgmRect = getStageViewportRect(W, H, true, 'program');
    drawMonitorFrame(ctx, pvwRect, 'PREVIEW (CUE)', state.currentPreviewScene, '#10b981', state.previewImage, false, timestamp);
    drawMonitorFrame(ctx, pgmRect, 'PROGRAM (LIVE)', state.currentProgramScene, '#ef4444', state.programImage, true, timestamp);
  } else {
    const pgmRect = getStageViewportRect(W, H, false, 'program');
    drawMonitorFrame(ctx, pgmRect, 'PROGRAM (LIVE)', state.currentProgramScene, '#ef4444', state.programImage, true, timestamp);
  }

  drawAudioVuCanvases();
  drawTbarCanvas();
  drawTelemetryCanvas();

  requestAnimationFrame(drawStudioStageFrame);
}

function drawMonitorFrame(ctx, vp, tallyTitle, sceneName, tallyColor, liveImg, isInteractiveProgram, timestamp) {
  ctx.save();
  ctx.strokeStyle = tallyColor;
  ctx.lineWidth = 2;
  ctx.strokeRect(vp.x - 1, vp.y - 1, vp.w + 2, vp.h + 2);

  ctx.beginPath();
  ctx.rect(vp.x, vp.y, vp.w, vp.h);
  ctx.clip();

  if (liveImg && liveImg.complete && liveImg.naturalWidth > 0) {
    ctx.drawImage(liveImg, vp.x, vp.y, vp.w, vp.h);
  } else {
    drawSyntheticBroadcastStage(ctx, vp, sceneName, timestamp);
  }

  if (state.canvasOpts.showGrid) {
    ctx.strokeStyle = 'rgba(148, 163, 184, 0.22)';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(vp.x + vp.w / 3, vp.y); ctx.lineTo(vp.x + vp.w / 3, vp.y + vp.h);
    ctx.moveTo(vp.x + (vp.w * 2) / 3, vp.y); ctx.lineTo(vp.x + (vp.w * 2) / 3, vp.y + vp.h);
    ctx.moveTo(vp.x, vp.y + vp.h / 3); ctx.lineTo(vp.x + vp.w, vp.y + vp.h / 3);
    ctx.moveTo(vp.x, vp.y + (vp.h * 2) / 3); ctx.lineTo(vp.x + vp.w, vp.y + (vp.h * 2) / 3);
    ctx.stroke();
  }

  if (state.canvasOpts.showSafeZones) {
    ctx.setLineDash([4, 4]);
    ctx.strokeStyle = 'rgba(56, 189, 248, 0.25)';
    ctx.strokeRect(vp.x + vp.w * 0.05, vp.y + vp.h * 0.05, vp.w * 0.9, vp.h * 0.9);
    ctx.strokeStyle = 'rgba(250, 204, 21, 0.22)';
    ctx.strokeRect(vp.x + vp.w * 0.1, vp.y + vp.h * 0.1, vp.w * 0.8, vp.h * 0.8);
    ctx.setLineDash([]);
  }

  if (isInteractiveProgram && state.canvasOpts.showBoxes) {
    const ordered = [...state.sceneItems].reverse();
    ordered.forEach((item) => {
      if (item.sceneItemEnabled === false) return;
      const box = getItemScreenBox(item, vp);
      const isSel = item.sceneItemId === state.selectedSceneItemId;
      const locked = !!item.sceneItemLocked;

      ctx.strokeStyle = isSel ? '#38bdf8' : 'rgba(148, 163, 184, 0.5)';
      ctx.lineWidth = isSel ? 2 : 1;
      ctx.strokeRect(box.x, box.y, box.w, box.h);

      const tagText = `${locked ? '🔒 ' : ''}${item.sourceName}`;
      ctx.font = 'bold 10px Inter, sans-serif';
      const tm = ctx.measureText(tagText);
      ctx.fillStyle = isSel ? 'rgba(2, 132, 199, 0.9)' : 'rgba(15, 23, 42, 0.8)';
      ctx.fillRect(box.x, box.y, tm.width + 10, 16);
      ctx.fillStyle = '#fff';
      ctx.fillText(tagText, box.x + 5, box.y + 11.5);

      if (isSel && !locked) {
        ctx.fillStyle = '#38bdf8';
        ctx.fillRect(box.x + box.w - 9, box.y + box.h - 9, 9, 9);
      }
    });
  }

  if (state.canvasOpts.showOsd) {
    ctx.fillStyle = 'rgba(7, 11, 20, 0.82)';
    ctx.fillRect(vp.x + 6, vp.y + 6, Math.min(vp.w - 12, 290), 22);
    ctx.fillStyle = tallyColor;
    ctx.fillRect(vp.x + 6, vp.y + 6, 5, 22);
    ctx.font = 'bold 10.5px Inter, sans-serif';
    ctx.fillStyle = '#f8fafc';
    ctx.fillText(`${tallyTitle}: ${sceneName || 'No Scene'}`, vp.x + 16, vp.y + 21);
  }

  ctx.restore();
}

function drawSyntheticBroadcastStage(ctx, vp, sceneName, timestamp) {
  const t = (timestamp || 0) * 0.001;
  const grad = ctx.createLinearGradient(vp.x, vp.y, vp.x + vp.w, vp.y + vp.h);
  grad.addColorStop(0, '#0f172a');
  grad.addColorStop(0.5, '#131d33');
  grad.addColorStop(1, '#090e1a');
  ctx.fillStyle = grad;
  ctx.fillRect(vp.x, vp.y, vp.w, vp.h);

  const ordered = [...state.sceneItems].reverse();
  ordered.forEach((item, idx) => {
    if (item.sceneItemEnabled === false) return;
    const box = getItemScreenBox(item, vp);
    const hues = [215, 160, 265, 345, 35];
    const hue = hues[idx % hues.length];
    ctx.fillStyle = `hsla(${hue}, 65%, 22%, 0.55)`;
    ctx.fillRect(box.x, box.y, box.w, box.h);

    ctx.strokeStyle = `hsla(${hue}, 80%, 60%, 0.3)`;
    ctx.lineWidth = 1.2;
    ctx.beginPath();
    const midY = box.y + box.h / 2;
    for (let px = 0; px <= box.w; px += 10) {
      const py = midY + Math.sin(px * 0.04 + t * 3 + idx) * Math.min(14, box.h * 0.2);
      if (px === 0) ctx.moveTo(box.x + px, py);
      else ctx.lineTo(box.x + px, py);
    }
    ctx.stroke();
  });
}

/* -------------------- CANVAS DRAG & SCALE -------------------- */
function getCanvasMousePos(ev) {
  const rect = studioCanvas.getBoundingClientRect();
  const dpr = studioCanvas.width / rect.width;
  return { x: (ev.clientX - rect.left) * dpr, y: (ev.clientY - rect.top) * dpr };
}

studioCanvas.addEventListener('mousedown', (ev) => {
  const pos = getCanvasMousePos(ev);
  const vp = getStageViewportRect(studioCanvas.width, studioCanvas.height, state.studioMode, 'program');
  for (const item of state.sceneItems) {
    if (item.sceneItemEnabled === false) continue;
    const box = getItemScreenBox(item, vp);
    if (pos.x >= box.x && pos.x <= box.x + box.w && pos.y >= box.y && pos.y <= box.y + box.h) {
      state.selectedSceneItemId = item.sceneItemId;
      renderSourcesPanel();
      updateSelectedTransformBar();
      if (item.sceneItemLocked) {
        showToast(`${item.sourceName} is locked`, '#f59e0b');
        return;
      }
      const t = item.sceneItemTransform || {};
      const isCorner = (pos.x >= box.x + box.w - 15 && pos.y >= box.y + box.h - 15);
      dragState = {
        active: true,
        mode: isCorner ? 'scale-br' : 'move',
        itemId: item.sceneItemId,
        startMouseX: pos.x,
        startMouseY: pos.y,
        origPosX: Number(t.positionX ?? 0),
        origPosY: Number(t.positionY ?? 0),
        origW: Number(t.width ?? 640),
        origH: Number(t.height ?? 360),
        sourceW: Number(t.sourceWidth || 640),
        sourceH: Number(t.sourceHeight || 360),
        vpScale: vp.scale,
        lastSentTime: 0
      };
      return;
    }
  }
});

window.addEventListener('mousemove', (ev) => {
  if (!dragState.active) return;
  const pos = getCanvasMousePos(ev);
  const item = state.sceneItems.find(i => i.sceneItemId === dragState.itemId);
  if (!item) return;
  if (!item.sceneItemTransform) item.sceneItemTransform = {};
  const dxObs = (pos.x - dragState.startMouseX) / dragState.vpScale;
  const dyObs = (pos.y - dragState.startMouseY) / dragState.vpScale;

  if (dragState.mode === 'move') {
    item.sceneItemTransform.positionX = Math.round(dragState.origPosX + dxObs);
    item.sceneItemTransform.positionY = Math.round(dragState.origPosY + dyObs);
  } else {
    const newW = Math.max(40, dragState.origW + dxObs);
    const newH = Math.max(30, dragState.origH + dyObs);
    item.sceneItemTransform.width = Math.round(newW);
    item.sceneItemTransform.height = Math.round(newH);
    item.sceneItemTransform.scaleX = Number((newW / dragState.sourceW).toFixed(4));
    item.sceneItemTransform.scaleY = Number((newH / dragState.sourceH).toFixed(4));
  }
  updateSelectedTransformBar();

  const now = performance.now();
  if (now - dragState.lastSentTime > 65) {
    dragState.lastSentTime = now;
    obsRequest('SetSceneItemTransform', {
      sceneName: state.currentProgramScene,
      sceneItemId: item.sceneItemId,
      sceneItemTransform: {
        positionX: item.sceneItemTransform.positionX,
        positionY: item.sceneItemTransform.positionY,
        scaleX: item.sceneItemTransform.scaleX ?? 1,
        scaleY: item.sceneItemTransform.scaleY ?? 1
      }
    });
  }
});

window.addEventListener('mouseup', () => {
  if (!dragState.active) return;
  const item = state.sceneItems.find(i => i.sceneItemId === dragState.itemId);
  dragState.active = false;
  if (item && item.sceneItemTransform) {
    obsRequest('SetSceneItemTransform', {
      sceneName: state.currentProgramScene,
      sceneItemId: item.sceneItemId,
      sceneItemTransform: {
        positionX: item.sceneItemTransform.positionX ?? 0,
        positionY: item.sceneItemTransform.positionY ?? 0,
        scaleX: item.sceneItemTransform.scaleX ?? 1,
        scaleY: item.sceneItemTransform.scaleY ?? 1
      }
    });
    fetchCanvasScreenshots();
  }
});

function updateSelectedTransformBar() {
  const item = state.sceneItems.find(i => i.sceneItemId === state.selectedSceneItemId);
  const nameEl = document.getElementById('selectedLayerText');
  const coordEl = document.getElementById('selectedCoordText');
  if (!item) {
    nameEl.textContent = 'None';
    coordEl.textContent = 'X:0 Y:0 1.0x';
    return;
  }
  const t = item.sceneItemTransform || {};
  nameEl.textContent = item.sourceName;
  coordEl.textContent = `X:${Math.round(t.positionX ?? 0)} Y:${Math.round(t.positionY ?? 0)} ${(t.scaleX ?? 1).toFixed(2)}x`;
}

/* -------------------- T-BAR, VU METERS & TELEMETRY CANVASES -------------------- */
const tbarCanvas = document.getElementById('tbarCanvas');
const tbarCtx = tbarCanvas.getContext('2d');
let tbarDragging = false;

function drawTbarCanvas() {
  const W = tbarCanvas.width, H = tbarCanvas.height, ctx = tbarCtx;
  ctx.clearRect(0, 0, W, H);
  ctx.fillStyle = '#070b13';
  ctx.fillRect(0, 0, W, H);

  const fillW = Math.max(0, Math.min(W, state.tbarPosition * W));
  const grad = ctx.createLinearGradient(0, 0, W, 0);
  grad.addColorStop(0, '#10b981');
  grad.addColorStop(0.5, '#3b82f6');
  grad.addColorStop(1, '#ef4444');
  ctx.fillStyle = grad;
  ctx.fillRect(0, 5, fillW, H - 10);

  const hx = Math.max(6, Math.min(W - 6, fillW));
  ctx.fillStyle = '#f8fafc';
  ctx.fillRect(hx - 5, 2, 10, H - 4);
}

function updateTbarFromEvent(ev, release = false) {
  const rect = tbarCanvas.getBoundingClientRect();
  const pos = Math.max(0, Math.min(1, (ev.clientX - rect.left) / rect.width));
  state.tbarPosition = pos;
  document.getElementById('tbarValueText').textContent = `${Math.round(pos * 100)}%`;
  obsRequest('SetTBarPosition', { position: Number(pos.toFixed(3)), release });
  if (release && (pos > 0.96 || pos < 0.04)) {
    setTimeout(() => { state.tbarPosition = 0; document.getElementById('tbarValueText').textContent = '0%'; }, 220);
  }
}
tbarCanvas.addEventListener('mousedown', (ev) => { tbarDragging = true; updateTbarFromEvent(ev, false); });
window.addEventListener('mousemove', (ev) => { if (tbarDragging) updateTbarFromEvent(ev, false); });
window.addEventListener('mouseup', (ev) => { if (tbarDragging) { tbarDragging = false; updateTbarFromEvent(ev, true); } });

function drawAudioVuCanvases() {
  document.querySelectorAll('[data-vu-canvas]').forEach((cv) => {
    const name = cv.getAttribute('data-vu-canvas');
    const inp = state.inputs.find(i => i.inputName === name);
    const muted = inp ? inp.inputMuted : false;
    const levels = state.audioLevels[name] || { leftDb: -60, rightDb: -60, peakLeftDb: -60, peakRightDb: -60, clip: false };
    const ctx = cv.getContext('2d'), W = cv.width, H = cv.height;
    ctx.clearRect(0, 0, W, H);
    const dbToNorm = (db) => Math.max(0, Math.min(1, (db + 60) / 60));
    const lW = muted ? 0 : dbToNorm(levels.leftDb) * (W - 6);
    const rW = muted ? 0 : dbToNorm(levels.rightDb) * (W - 6);
    const grad = ctx.createLinearGradient(0, 0, W, 0);
    grad.addColorStop(0, '#10b981');
    grad.addColorStop(0.7, '#10b981');
    grad.addColorStop(0.86, '#f59e0b');
    grad.addColorStop(1.0, '#ef4444');
    ctx.fillStyle = muted ? '#334155' : grad;
    ctx.fillRect(0, 1, lW, 3.5);
    ctx.fillRect(0, 5.5, rW, 3.5);
  });
}

const telemetryCanvas = document.getElementById('telemetryCanvas');
const telemetryCtx = telemetryCanvas.getContext('2d');
function drawTelemetryCanvas() {
  const W = telemetryCanvas.width, H = telemetryCanvas.height, ctx = telemetryCtx;
  ctx.clearRect(0, 0, W, H);
  const hist = state.telemetryHistory;
  if (hist.length > 1) {
    drawSparkline(ctx, hist.map(p => p.fps / 60), W, H, '#10b981');
    drawSparkline(ctx, hist.map(p => p.cpu / 100), W, H, '#f59e0b');
    drawSparkline(ctx, hist.map(p => Math.min(1, p.kbps / 8000)), W, H, '#38bdf8');
  }
  ctx.font = 'bold 9px Consolas, monospace';
  ctx.fillStyle = '#10b981'; ctx.fillText(`FPS:${state.stats.fps.toFixed(0)}`, 5, 11);
  ctx.fillStyle = '#f59e0b'; ctx.fillText(`CPU:${state.stats.cpuUsage.toFixed(1)}%`, 58, 11);
  ctx.fillStyle = '#38bdf8'; ctx.fillText(`MEM:${Math.round(state.stats.memoryUsage)}M`, 125, 11);
  ctx.fillStyle = state.stats.outputSkipped > 0 ? '#ef4444' : '#94a3b8';
  ctx.fillText(`DRP:${state.stats.outputSkipped}`, 182, 11);
}
function drawSparkline(ctx, normValues, W, H, color) {
  ctx.strokeStyle = color;
  ctx.lineWidth = 1.4;
  ctx.beginPath();
  const step = W / Math.max(1, normValues.length - 1);
  normValues.forEach((v, i) => {
    const x = i * step;
    const y = H - 4 - Math.max(0, Math.min(1, v)) * (H - 18);
    if (i === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
  });
  ctx.stroke();
}

/* -------------------- OFFLINE SIMULATION ENGINE -------------------- */
function enableSimulationMode() {
  state.simMode = true;
  state.connected = false;
  state.activeTransport = 'simulation';
  updateConnBadge('sim', 'SIMULATION');
  document.getElementById('btnSimMode').classList.add('active-toggle');

  state.scenes = [
    { sceneName: 'Starting Soon' },
    { sceneName: 'Main Camera + HUD' },
    { sceneName: 'Pulpit Close-Up' },
    { sceneName: 'Worship Stage' },
    { sceneName: 'Break & Offering' },
    { sceneName: 'Stream Ending' }
  ];
  state.currentProgramScene = 'Main Camera + HUD';
  state.currentPreviewScene = 'Worship Stage';

  state.sceneItems = [
    {
      sceneItemId: 101,
      sourceName: 'MegaOBS Church HUD (1080p)',
      inputKind: 'browser_source',
      sceneItemEnabled: true,
      sceneItemLocked: true,
      sceneItemIndex: 2,
      sceneItemTransform: { positionX: 0, positionY: 0, width: 1920, height: 1080, sourceWidth: 1920, sourceHeight: 1080, scaleX: 1, scaleY: 1 }
    },
    {
      sceneItemId: 102,
      sourceName: 'MegaOBS Lyric Overlay (1080p)',
      inputKind: 'browser_source',
      sceneItemEnabled: true,
      sceneItemLocked: false,
      sceneItemIndex: 1,
      sceneItemTransform: { positionX: 0, positionY: 0, width: 1920, height: 1080, sourceWidth: 1920, sourceHeight: 1080, scaleX: 1, scaleY: 1 }
    },
    {
      sceneItemId: 103,
      sourceName: 'Sony Camera Capture Card',
      inputKind: 'dshow_input',
      sceneItemEnabled: true,
      sceneItemLocked: false,
      sceneItemIndex: 0,
      sceneItemTransform: { positionX: 0, positionY: 0, width: 1920, height: 1080, sourceWidth: 1920, sourceHeight: 1080, scaleX: 1, scaleY: 1 }
    }
  ];
  state.selectedSceneItemId = 103;

  state.inputs = [
    { inputName: 'Pulpit Mic (Pastor)', inputVolumeDb: -3.5, inputMuted: false },
    { inputName: 'Worship Mixer L/R', inputVolumeDb: -8.0, inputMuted: false },
    { inputName: 'Ambient Room Mic', inputVolumeDb: -18.0, inputMuted: false }
  ];

  renderAllDomPanels();
  startBackgroundLoops();
}

function tickSimulationStats() {
  state.stats.fps = 60.0;
  state.stats.cpuUsage = 3.8 + Math.sin(performance.now() * 0.002) * 1.2;
  state.stats.kbps = state.stream.active ? Math.round(5800 + Math.random() * 350) : 0;
  pushTelemetryPoint();
  renderOutputsPanel();
}

setInterval(() => {
  if (!state.simMode) return;
  const t = performance.now() * 0.005;
  state.inputs.forEach((inp, idx) => {
    if (inp.inputMuted) {
      state.audioLevels[inp.inputName] = { leftDb: -60, rightDb: -60, peakLeftDb: -60, peakRightDb: -60, clip: false };
      return;
    }
    const base = inp.inputVolumeDb - 6;
    const leftDb = Math.max(-60, Math.min(0, base + Math.sin(t * (1.7 + idx * 0.5)) * 7 + Math.random() * 3));
    const rightDb = Math.max(-60, Math.min(0, base + Math.cos(t * (1.5 + idx * 0.6)) * 7 + Math.random() * 3));
    const prev = state.audioLevels[inp.inputName] || { peakLeftDb: -60, peakRightDb: -60 };
    state.audioLevels[inp.inputName] = {
      leftDb, rightDb,
      peakLeftDb: Math.max(leftDb, prev.peakLeftDb - 0.3),
      peakRightDb: Math.max(rightDb, prev.peakRightDb - 0.3),
      clip: leftDb > -1.0
    };
  });
}, 50);

async function simulateObsRequest(type) {
  if (type === 'ToggleStream' || type === 'StartStream' || type === 'StopStream') {
    state.stream.active = type === 'StartStream' ? true : (type === 'StopStream' ? false : !state.stream.active);
    state.stream.timecode = state.stream.active ? '00:24:15' : '00:00:00';
    renderOutputsPanel();
  } else if (type === 'ToggleRecord' || type === 'StartRecord' || type === 'StopRecord') {
    state.record.active = type === 'StartRecord' ? true : (type === 'StopRecord' ? false : !state.record.active);
    state.record.timecode = state.record.active ? '00:24:15' : '00:00:00';
    renderOutputsPanel();
  } else if (type === 'ToggleVirtualCam') {
    state.vcam.active = !state.vcam.active;
    renderOutputsPanel();
  } else if (type === 'ToggleReplayBuffer') {
    state.replay.active = !state.replay.active;
    renderOutputsPanel();
  } else if (type === 'TriggerStudioModeTransition' && state.studioMode) {
    const oldPgm = state.currentProgramScene;
    state.currentProgramScene = state.currentPreviewScene;
    state.currentPreviewScene = oldPgm;
    renderScenesPanel();
  }
  return { requestStatus: { result: true }, responseData: {} };
}

/* ============================================================================
 * PART 2: CHURCH HUD & LOWER-THIRDS CONTROLLER (SYNC.PHP + BROADCASTCHANNEL)
 * ============================================================================ */
let hudChannel = ('BroadcastChannel' in window) ? new BroadcastChannel('sib_church_hud_sync') : null;
let hudState = {
  scene: 'overlay',
  theme: 'natal-blue',
  tickerVisible: false,
  speakerLtVisible: false,
  scriptureVisible: false,
  worshipVisible: false,
  givingLtVisible: false
};
let hudLocalTimerSec = 300;
let hudLocalTimerRunning = false;

if (hudChannel) {
  hudChannel.onmessage = (e) => {
    if (e.data && e.data.action === 'PRESETS_UPDATED') {
      initHudPresets();
      return;
    }
    applyIncomingHudState(e.data);
  };
}

async function sendHudCommand(msg) {
  if (hudChannel) hudChannel.postMessage(msg);
  try {
    localStorage.setItem('sib_church_hud_sync_event', JSON.stringify({ ...msg, _t: Date.now() }));
  } catch (e) {}

  try {
    const res = await fetch('sync.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(msg)
    });
    const data = await res.json();
    if (data && data.state) applyIncomingHudState(data.state);
  } catch (e) {}
}

function switchHudScene(sceneName) {
  hudState.scene = sceneName;
  renderHudControlsUi();
  sendHudCommand({ action: 'SWITCH_SCENE', scene: sceneName });
  showToast(`Church HUD Scene: ${sceneName.toUpperCase()}`, '#f5c242');
}

function changeHudTheme(themeName) {
  hudState.theme = themeName;
  sendHudCommand({ action: 'SET_THEME', theme: themeName });
  showToast(`HUD Theme: ${themeName}`, '#f5c242');
}

function toggleHudLt(type, forceVal) {
  const map = {
    speaker: { key: 'speakerLtVisible', action: 'TOGGLE_SPEAKER_LT', label: 'Speaker Lower-Third' },
    scripture: { key: 'scriptureVisible', action: 'TOGGLE_SCRIPTURE_HUD', label: 'Scripture Verse HUD' },
    worship: { key: 'worshipVisible', action: 'TOGGLE_WORSHIP_HUD', label: 'Worship Lyrics HUD' },
    giving: { key: 'givingLtVisible', action: 'TOGGLE_GIVING_LT', label: 'Offering & QR Lower-Third' },
    ticker: { key: 'tickerVisible', action: 'TOGGLE_TICKER', label: 'Marquee Info Ticker' }
  };
  const cfg = map[type];
  if (!cfg) return;
  const next = (forceVal !== undefined) ? !!forceVal : !hudState[cfg.key];

  // Mutually exclusive in local UI state
  ['speakerLtVisible', 'scriptureVisible', 'worshipVisible', 'givingLtVisible', 'tickerVisible'].forEach(k => {
    hudState[k] = false;
  });
  hudState[cfg.key] = next;
  renderHudControlsUi();
  sendHudCommand({ action: cfg.action, visible: next });
  showToast(`${cfg.label}: ${next ? 'SHOWN' : 'HIDDEN'}`, next ? '#f5c242' : '#64748b');
}

function hideAllHudLt() {
  ['speakerLtVisible', 'scriptureVisible', 'worshipVisible', 'givingLtVisible', 'tickerVisible'].forEach(k => {
    hudState[k] = false;
  });
  renderHudControlsUi();
  sendHudCommand({ action: 'HIDE_ALL_LT' });
  showToast('Cleared all Church HUD Lower-Thirds', '#ef4444');
}

function hudTimerAction(command, seconds) {
  if (command === 'START') hudLocalTimerRunning = true;
  else if (command === 'PAUSE') hudLocalTimerRunning = false;
  else if (command === 'RESET' && seconds) { hudLocalTimerRunning = false; hudLocalTimerSec = seconds; }
  else if (command === 'ADD_TIME' && seconds) { hudLocalTimerSec = Math.max(0, hudLocalTimerSec + seconds); }
  updateHudTimerReadout();
  sendHudCommand({ action: 'TIMER_ACTION', command, seconds });
}

setInterval(() => {
  if (hudLocalTimerRunning && hudLocalTimerSec > 0) {
    hudLocalTimerSec--;
    updateHudTimerReadout();
    if (hudLocalTimerSec === 0) hudLocalTimerRunning = false;
  }
}, 1000);

function updateHudTimerReadout() {
  const m = Math.floor(hudLocalTimerSec / 60);
  const s = hudLocalTimerSec % 60;
  document.getElementById('hudTimerDisplay').textContent =
    `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
}

function switchHudSubtab(paneId, btnEl) {
  document.querySelectorAll('.subtab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.subtab-pane').forEach(p => p.classList.remove('active'));
  btnEl.classList.add('active');
  const pane = document.getElementById('hudPane-' + paneId);
  if (pane) pane.classList.add('active');
}

let scriptureStyleDebounce = null;

function pushHudScriptureLive() {
  const ref = document.getElementById('hudScriptureRef').value;
  const version = document.getElementById('hudScriptureVer').value;
  const text = document.getElementById('hudScriptureText').value;
  const scriptureFontSize = Number(document.getElementById('hudScriptureFontSize')?.value || 32);
  const scriptureFontFamily = document.getElementById('hudScriptureFontFamily')?.value || 'sans';
  const scriptureStyle = document.getElementById('hudScriptureStyle')?.value || 'bold-glass';

  ['speakerLtVisible', 'scriptureVisible', 'worshipVisible', 'givingLtVisible', 'tickerVisible'].forEach(k => hudState[k] = false);
  hudState.scriptureVisible = true;
  renderHudControlsUi();
  sendHudCommand({
    action: 'PUSH_SCRIPTURE',
    ref,
    version,
    text,
    scriptureFontSize,
    scriptureFontFamily,
    scriptureStyle
  });
  showToast(`Scripture Live: ${ref}`, '#f5c242');
}

function onScriptureStyleChange() {
  const fontSize = Number(document.getElementById('hudScriptureFontSize')?.value || 32);
  const fontFamily = document.getElementById('hudScriptureFontFamily')?.value || 'sans';
  const style = document.getElementById('hudScriptureStyle')?.value || 'bold-glass';

  const valEl = document.getElementById('scriptureSizeValText');
  if (valEl) valEl.textContent = `${fontSize}px`;

  const payload = {
    scriptureFontSize: fontSize,
    scriptureFontFamily: fontFamily,
    scriptureStyle: style
  };
  if (hudChannel) hudChannel.postMessage({ action: 'UPDATE_BULK', payload });

  clearTimeout(scriptureStyleDebounce);
  scriptureStyleDebounce = setTimeout(() => {
    sendHudCommand({ action: 'UPDATE_BULK', payload });
  }, 120);
}

function setQuickScriptureSize(px) {
  const el = document.getElementById('hudScriptureFontSize');
  if (el) el.value = px;
  onScriptureStyleChange();
}

function pushHudSermonInfo(andShowSpeakerLt = false) {
  const payload = {
    sermonTitle: document.getElementById('hudSermonTitle').value,
    speakerName: document.getElementById('hudSpeakerName').value,
    speakerRole: document.getElementById('hudSpeakerRole').value,
    sermonScripture: document.getElementById('hudSermonScripture').value,
    sermonSeries: document.getElementById('hudSermonSeries').value
  };
  sendHudCommand({ action: 'UPDATE_BULK', payload });
  if (andShowSpeakerLt) {
    toggleHudLt('speaker', true);
  } else {
    showToast('Updated Sermon & Speaker Info', '#10b981');
  }
}

let tickerStyleDebounce = null;

function pushHudTickerBadge() {
  const badge = document.getElementById('hudTickerBadge').value;
  sendHudCommand({ action: 'UPDATE_TICKER_BADGE', badge });
  showToast(`Ticker Badge: ${badge}`, '#f5c242');
}

function pushHudTickerText(andShow = true) {
  const text = document.getElementById('hudTickerText').value;
  const badge = document.getElementById('hudTickerBadge').value;
  const fontSize = Number(document.getElementById('hudTickerFontSize').value || 26);
  const duration = Number(document.getElementById('hudTickerDuration').value || 28);
  sendHudCommand({
    action: 'UPDATE_BULK',
    payload: {
      tickerText: text,
      tickerBadge: badge,
      tickerFontSize: fontSize,
      tickerDuration: duration
    }
  });
  if (andShow) toggleHudLt('ticker', true);
  else showToast('Updated Ticker Text', '#10b981');
}

function onTickerStyleChange() {
  const fontSize = Number(document.getElementById('hudTickerFontSize').value || 26);
  const duration = Number(document.getElementById('hudTickerDuration').value || 28);

  document.getElementById('tickerSizeValText').textContent = `${fontSize}px`;
  document.getElementById('tickerSpeedValText').textContent = `${duration}s`;

  const payload = { tickerFontSize: fontSize, tickerDuration: duration };
  if (hudChannel) hudChannel.postMessage({ action: 'UPDATE_BULK', payload });

  clearTimeout(tickerStyleDebounce);
  tickerStyleDebounce = setTimeout(() => {
    sendHudCommand({ action: 'UPDATE_BULK', payload });
  }, 120);
}

function setQuickTickerSize(px) {
  document.getElementById('hudTickerFontSize').value = px;
  onTickerStyleChange();
}

function setQuickTickerSpeed(sec) {
  document.getElementById('hudTickerDuration').value = sec;
  onTickerStyleChange();
}

let givingLayoutDebounce = null;

function pushHudGivingInfo(andShow = true) {
  const payload = {
    givingBank: document.getElementById('hudGivingBank').value,
    givingAccount: document.getElementById('hudGivingAcc').value,
    givingName: document.getElementById('hudGivingName').value,
    givingRef: document.getElementById('hudGivingRef').value,
    givingPosMode: document.getElementById('hudGivingPosMode').value,
    givingQrSize: Number(document.getElementById('hudGivingQrSize').value || 96),
    givingPosX: Number(document.getElementById('hudGivingPosX').value || 50),
    givingPosY: Number(document.getElementById('hudGivingPosY').value || 50)
  };
  sendHudCommand({ action: 'UPDATE_BULK', payload });
  if (andShow) toggleHudLt('giving', true);
  else showToast('Updated Offering & QR Info', '#10b981');
}

function onGivingLayoutChange() {
  const mode = document.getElementById('hudGivingPosMode').value;
  const size = Number(document.getElementById('hudGivingQrSize').value || 96);
  const posX = Number(document.getElementById('hudGivingPosX').value || 50);
  const posY = Number(document.getElementById('hudGivingPosY').value || 50);

  document.getElementById('qrSizeValText').textContent = `${size}px`;
  document.getElementById('qrPosXText').textContent = `${posX}%`;
  document.getElementById('qrPosYText').textContent = `${posY}%`;
  document.getElementById('customQrCoordsRow').style.display = (mode === 'custom') ? 'grid' : 'none';

  // Instant local BroadcastChannel preview update
  const payload = { givingPosMode: mode, givingQrSize: size, givingPosX: posX, givingPosY: posY };
  if (hudChannel) hudChannel.postMessage({ action: 'UPDATE_BULK', payload });

  clearTimeout(givingLayoutDebounce);
  givingLayoutDebounce = setTimeout(() => {
    sendHudCommand({ action: 'UPDATE_BULK', payload });
  }, 120);
}

function onGivingCustomCoordInput() {
  document.getElementById('hudGivingPosMode').value = 'custom';
  onGivingLayoutChange();
}

function setQuickQrSize(px) {
  document.getElementById('hudGivingQrSize').value = px;
  onGivingLayoutChange();
}

function centerQrSpotlight() {
  document.getElementById('hudGivingPosMode').value = 'center';
  if (Number(document.getElementById('hudGivingQrSize').value) < 180) {
    document.getElementById('hudGivingQrSize').value = 230;
  }
  onGivingLayoutChange();
  pushHudGivingInfo(true);
}

function pushQuickNatalPreset() {
  const payload = {
    churchName: "SIB KAWASAN PAPAR 2026",
    serviceTitle: "KEBAKTIAN NATAL & GRADUASI PKK",
    serviceSubtitle: "SAMBUTAN NATAL & GRADUASI KANAK-KANAK 2026",
    churchMotto: "Aku Kecil, Aku Berharga (Mikha 5:1–3)",
    landingTitle: "KEBAKTIAN NATAL & GRADUASI PKK",
    landingSubtitle: "AKU KECIL, AKU BERHARGA • GEREJA SIB KAYAU",
    sermonTitle: "Aku Kecil, Aku Berharga",
    sermonSeries: "Sambutan Natal & Graduasi PKK SIB Kawasan Papar 2026",
    speakerName: "Pta. Tinggih Sulog",
    speakerRole: "Pengkhotbah Kebaktian Natal & Graduasi PKK",
    sermonScripture: "Mikha 5:1–3",
    scriptureRef: "Mikha 5:1–3",
    scriptureVersion: "Alkitab TB",
    scriptureText: "Tetapi engkau, hai Betlehem Efrata, hai yang terkecil di antara kaum-kaum Yehuda, dari padamu akan bangkit bagi-Ku seorang yang akan memerintah Israel, yang permulaannya sudah sejak purbakala, sejak dahulu kala.",
    tickerText: "Shalom! Selamat Datang ke Kebaktian Natal & Graduasi PKK SIB Kawasan Papar 2026 • Tema: 'Aku Kecil, Aku Berharga' (Mikha 5:1–3) • Tempat: Gereja SIB Kayau • Pengkhotbah: Pta. Tinggih Sulog • Tarikh: 22 Nov 2026 (Ahad 9.30 Pagi) • Warna Baju: Biru • Pengerusi PKK: Welter Lainor • Tuhan Yesus Memberkati!",
    tickerBadge: "INFO NATAL & PKK",
    theme: "natal-blue",
    givingBank: "Public Bank",
    givingAccount: "3235424420",
    givingName: "SIB PEKAN PAPAR",
    givingRef: "Persembahan / Perpuluhan"
  };

  document.getElementById('hudSermonTitle').value = payload.sermonTitle;
  document.getElementById('hudSermonSeries').value = payload.sermonSeries;
  document.getElementById('hudSpeakerName').value = payload.speakerName;
  document.getElementById('hudSpeakerRole').value = payload.speakerRole;
  document.getElementById('hudSermonScripture').value = payload.sermonScripture;
  document.getElementById('hudScriptureRef').value = payload.scriptureRef;
  document.getElementById('hudScriptureText').value = payload.scriptureText;
  document.getElementById('hudTickerBadge').value = payload.tickerBadge;
  document.getElementById('hudTickerText').value = payload.tickerText;
  document.getElementById('hudThemeSelect').value = 'natal-blue';

  sendHudCommand({ action: 'UPDATE_BULK', payload });
  sendHudCommand({ action: 'SET_THEME', theme: 'natal-blue' });
  showToast('Natal & Graduasi PKK 2026 Preset Pushed Live!', '#f5c242');
}

let loadedHudPresets = {
  scripturePresets: [],
  sermonPresets: [],
  tickerPresets: []
};

async function initHudPresets() {
  try {
    const res = await fetch('presets_api.php?action=get_presets&_=' + Date.now()).then(r => r.json());
    if (res && res.ok && res.presets) {
      loadedHudPresets = res.presets;
    } else if (window.SIBChurchPresets) {
      loadedHudPresets = {
        scripturePresets: window.SIBChurchPresets.scripturePresets || [],
        sermonPresets: window.SIBChurchPresets.sermonPresets || [],
        tickerPresets: []
      };
    }
  } catch (e) {
    if (window.SIBChurchPresets) {
      loadedHudPresets = {
        scripturePresets: window.SIBChurchPresets.scripturePresets || [],
        sermonPresets: window.SIBChurchPresets.sermonPresets || [],
        tickerPresets: []
      };
    }
  }

  const scSel = document.getElementById('hudScripturePresetSel');
  if (scSel) {
    scSel.innerHTML = `<option value="">-- Select Verse Preset (${loadedHudPresets.scripturePresets.length}) --</option>` +
      loadedHudPresets.scripturePresets.map((sc, i) =>
        `<option value="${i}">${escapeHtml(sc.ref)} — ${escapeHtml((sc.text || '').substring(0, 34))}...</option>`
      ).join('');
  }

  const srSel = document.getElementById('hudSermonPresetSel');
  if (srSel) {
    srSel.innerHTML = `<option value="">-- Select Sermon Preset (${loadedHudPresets.sermonPresets.length}) --</option>` +
      loadedHudPresets.sermonPresets.map((s, i) =>
        `<option value="${i}">${escapeHtml(s.title)} (${escapeHtml(s.speaker)})</option>`
      ).join('');
  }

  const tkSel = document.getElementById('hudTickerPresetSel');
  if (tkSel) {
    tkSel.innerHTML = `<option value="">-- Select Ticker Preset (${loadedHudPresets.tickerPresets.length}) --</option>` +
      loadedHudPresets.tickerPresets.map((t, i) =>
        `<option value="${i}">[${escapeHtml(t.badge)}] ${escapeHtml(t.title || (t.text || '').substring(0, 32))}</option>`
      ).join('');
  }
}

function loadHudScripturePreset(idx) {
  if (idx === '') return;
  const sc = loadedHudPresets.scripturePresets?.[idx];
  if (!sc) return;
  document.getElementById('hudScriptureRef').value = sc.ref || '';
  document.getElementById('hudScriptureVer').value = sc.version || 'Alkitab TB';
  document.getElementById('hudScriptureText').value = sc.text || '';
}

function loadHudSermonPreset(idx) {
  if (idx === '') return;
  const s = loadedHudPresets.sermonPresets?.[idx];
  if (!s) return;
  document.getElementById('hudSermonTitle').value = s.title || '';
  document.getElementById('hudSpeakerName').value = s.speaker || '';
  document.getElementById('hudSpeakerRole').value = s.role || '';
  document.getElementById('hudSermonScripture').value = s.scripture || '';
  document.getElementById('hudSermonSeries').value = s.series || '';
}

function loadHudTickerPreset(idx) {
  if (idx === '') return;
  const t = loadedHudPresets.tickerPresets?.[idx];
  if (!t) return;
  document.getElementById('hudTickerBadge').value = t.badge || 'INFO GEREJA';
  document.getElementById('hudTickerText').value = t.text || '';
}

function applyIncomingHudState(st) {
  if (!st) return;
  const s = st.state || st;
  if (s.scene) hudState.scene = s.scene;
  if (s.theme) hudState.theme = s.theme;
  if (s.tickerVisible !== undefined) hudState.tickerVisible = !!s.tickerVisible;
  if (s.speakerLtVisible !== undefined) hudState.speakerLtVisible = !!s.speakerLtVisible;
  if (s.scriptureVisible !== undefined) hudState.scriptureVisible = !!s.scriptureVisible;
  if (s.worshipVisible !== undefined) hudState.worshipVisible = !!s.worshipVisible;
  if (s.givingLtVisible !== undefined) hudState.givingLtVisible = !!s.givingLtVisible;

  if (s.givingPosMode && document.activeElement !== document.getElementById('hudGivingPosMode')) {
    document.getElementById('hudGivingPosMode').value = s.givingPosMode;
    document.getElementById('customQrCoordsRow').style.display = (s.givingPosMode === 'custom') ? 'grid' : 'none';
  }
  if (s.givingQrSize && document.activeElement !== document.getElementById('hudGivingQrSize')) {
    document.getElementById('hudGivingQrSize').value = s.givingQrSize;
    document.getElementById('qrSizeValText').textContent = `${s.givingQrSize}px`;
  }
  if (s.givingPosX !== undefined && document.activeElement !== document.getElementById('hudGivingPosX')) {
    document.getElementById('hudGivingPosX').value = s.givingPosX;
    document.getElementById('qrPosXText').textContent = `${s.givingPosX}%`;
  }
  if (s.givingPosY !== undefined && document.activeElement !== document.getElementById('hudGivingPosY')) {
    document.getElementById('hudGivingPosY').value = s.givingPosY;
    document.getElementById('qrPosYText').textContent = `${s.givingPosY}%`;
  }
  if (s.tickerFontSize && document.activeElement !== document.getElementById('hudTickerFontSize')) {
    document.getElementById('hudTickerFontSize').value = s.tickerFontSize;
    document.getElementById('tickerSizeValText').textContent = `${s.tickerFontSize}px`;
  }
  if (s.tickerDuration && document.activeElement !== document.getElementById('hudTickerDuration')) {
    document.getElementById('hudTickerDuration').value = s.tickerDuration;
    document.getElementById('tickerSpeedValText').textContent = `${s.tickerDuration}s`;
  }
  if (s.scriptureFontSize && document.activeElement !== document.getElementById('hudScriptureFontSize')) {
    const scSizeEl = document.getElementById('hudScriptureFontSize');
    if (scSizeEl) scSizeEl.value = s.scriptureFontSize;
    const scTxtEl = document.getElementById('scriptureSizeValText');
    if (scTxtEl) scTxtEl.textContent = `${s.scriptureFontSize}px`;
  }
  if (s.scriptureFontFamily && document.activeElement !== document.getElementById('hudScriptureFontFamily')) {
    const scFamEl = document.getElementById('hudScriptureFontFamily');
    if (scFamEl) scFamEl.value = s.scriptureFontFamily;
  }
  if (s.scriptureStyle && document.activeElement !== document.getElementById('hudScriptureStyle')) {
    const scStEl = document.getElementById('hudScriptureStyle');
    if (scStEl) scStEl.value = s.scriptureStyle;
  }

  renderHudControlsUi();
}

function renderHudControlsUi() {
  document.querySelectorAll('[data-hud-scene]').forEach(el => {
    el.classList.toggle('active', el.getAttribute('data-hud-scene') === hudState.scene);
  });
  document.getElementById('hudSyncText').textContent = `HUD: ${(hudState.scene || 'overlay').toUpperCase()}`;

  const bindLt = (btnId, tagId, active) => {
    const b = document.getElementById(btnId);
    const t = document.getElementById(tagId);
    if (b) b.classList.toggle('on', !!active);
    if (t) t.textContent = active ? 'LIVE' : 'OFF';
  };
  bindLt('ltBtnSpeaker', 'ltTagSpeaker', hudState.speakerLtVisible);
  bindLt('ltBtnScripture', 'ltTagScripture', hudState.scriptureVisible);
  bindLt('ltBtnGiving', 'ltTagGiving', hudState.givingLtVisible);
  bindLt('ltBtnTicker', 'ltTagTicker', hudState.tickerVisible);
}

/* ============================================================================
 * PART 3: LYRIC DATABASE (28 SQLITE DBs) & EASYSLIDES MULTICAST CONTROLLER
 * ============================================================================ */
let lyricCurrentDb = <?= json_encode($defaultDb) ?>;
let lyricLoadedSlides = [];
let lyricLoadedTitle = '';
let lyricLoadedAuthor = '';
let lyricActiveSlideNo = 0;
let lyricSearchTimer = null;
let lyricObsState = {};

async function lyricObsApi(action, data = {}) {
  const res = await fetch('obs_api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action, ...data })
  });
  return await res.json();
}

function onLyricDbChange() {
  lyricCurrentDb = document.getElementById('lyricDbSelect').value;
  searchLyricDatabaseSongs(true);
}

function debounceLyricQuickSearch() {
  clearTimeout(lyricSearchTimer);
  lyricSearchTimer = setTimeout(() => searchLyricDatabaseSongs(false), 180);
}

async function searchLyricDatabaseSongs(autoLoadFirst = false) {
  const q = document.getElementById('lyricQuickSearch').value.trim();
  try {
    const res = await fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'list_songs', db: lyricCurrentDb, q, per_page: 60 })
    }).then(r => r.json());

    if (!res.ok) return;
    const sel = document.getElementById('lyricSongResults');
    sel.innerHTML = `<option value="">— ${res.total} songs in ${escapeHtml(lyricCurrentDb)} —</option>` +
      res.items.map(it => `<option value="${it.rowid}">${escapeHtml(it.title1)}</option>`).join('');

    if (autoLoadFirst && res.items.length > 0) {
      sel.value = res.items[0].rowid;
      loadSelectedLyricSong(res.items[0].rowid, false);
    }
  } catch (e) {}
}

async function loadSelectedLyricSong(rowid, autoProjectSlide1 = true) {
  rowid = Number(rowid);
  if (!rowid) return;
  try {
    const res = await fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'get_song', db: lyricCurrentDb, rowid })
    }).then(r => r.json());

    if (!res.ok || !res.song) return;
    const s = res.song;
    lyricLoadedTitle = s.title1 || '';
    lyricLoadedAuthor = s.author || '';
    lyricLoadedSlides = parseEasyslidesContentsToSlides(s.contents || '');
    if (lyricLoadedSlides.length > 0) {
      if (autoProjectSlide1) {
        projectLyricSlideToObs(1);
      } else {
        renderLyricSlideCards(0);
      }
    }
  } catch (e) {}
}

function parseEasyslidesContentsToSlides(raw) {
  const lines = raw.replace(/\r\n/g, '\n').split('\n');
  const slides = [];
  let curSection = 'Slide';
  let curLines = [];
  let slideNo = 1;

  const flush = () => {
    const text = curLines.join('\n').trim();
    if (text !== '') {
      const blocks = text.split(/\n\s*\n/);
      blocks.forEach(b => {
        if (b.trim() !== '') {
          slides.push({
            slide_no: slideNo++,
            section: curSection,
            region1: b.trim(),
            region2: ''
          });
        }
      });
    }
    curLines = [];
  };

  for (const line of lines) {
    const m = line.trim().match(/^\[([^\]]+)\]$/);
    if (m) {
      flush();
      const tag = m[1].toLowerCase();
      curSection = /^\d+$/.test(tag) ? `Verse ${tag}` : tag.charAt(0).toUpperCase() + tag.slice(1);
    } else {
      curLines.push(line);
    }
  }
  flush();
  return slides;
}

async function projectLyricSlideToObs(slideNo) {
  if (!lyricLoadedSlides.length) return;
  lyricActiveSlideNo = Math.max(1, Math.min(lyricLoadedSlides.length, Number(slideNo)));
  const res = await lyricObsApi('push_slide', {
    title1: lyricLoadedTitle,
    author: lyricLoadedAuthor,
    slides: lyricLoadedSlides,
    slide_no: lyricActiveSlideNo
  });
  if (res.ok) {
    applyLyricObsState(res.state);
    renderLyricSlideCards(lyricActiveSlideNo);
  }
}

function stepLyricSlide(delta) {
  if (!lyricLoadedSlides.length) return;
  const next = Math.max(1, Math.min(lyricLoadedSlides.length, (lyricActiveSlideNo || 1) + delta));
  projectLyricSlideToObs(next);
}

function renderLyricSlideCards(activeNo) {
  document.getElementById('activeProjectedSongTitle').textContent = lyricLoadedTitle || 'No Song Loaded';
  document.getElementById('activeProjectedSlideMeta').textContent =
    lyricLoadedSlides.length > 0 ? `Slide ${activeNo} of ${lyricLoadedSlides.length}` : '0 slides';

  const grid = document.getElementById('lyricSlidesGrid');
  if (!lyricLoadedSlides.length) {
    grid.innerHTML = `<div style="padding:18px; text-align:center; color:var(--text-muted);">Select a song above or start Easyslides Multicast to project slides.</div>`;
    return;
  }

  grid.innerHTML = lyricLoadedSlides.map(sl => {
    const isAct = Number(sl.slide_no) === Number(activeNo);
    return `
      <div class="lyric-slide-card ${isAct ? 'active' : ''}" id="lyric-card-${sl.slide_no}" onclick="projectLyricSlideToObs(${sl.slide_no})">
        <div class="lyric-slide-head">
          <span>${escapeHtml(sl.section || 'Slide')}</span>
          <span>#${sl.slide_no}</span>
        </div>
        <div class="lyric-slide-body">${escapeHtml(sl.region1)}</div>
      </div>
    `;
  }).join('');

  const activeCard = document.getElementById(`lyric-card-${activeNo}`);
  if (activeCard) activeCard.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
}

async function toggleLyricBlack() {
  const next = !lyricObsState.is_black;
  const res = await lyricObsApi('toggle_state', { is_black: next });
  if (res.ok) applyLyricObsState(res.state);
}

async function toggleLyricClear() {
  const next = !lyricObsState.is_clear;
  const res = await lyricObsApi('toggle_state', { is_clear: next });
  if (res.ok) applyLyricObsState(res.state);
}

async function sendLyricAlert() {
  const txt = document.getElementById('lyricAlertInput').value.trim();
  const res = await lyricObsApi('toggle_state', { alert_text: txt });
  if (res.ok) showToast(txt ? `Stage Alert: ${txt}` : 'Alert Cleared', '#f97316');
}

async function clearLyricAlert() {
  document.getElementById('lyricAlertInput').value = '';
  await lyricObsApi('toggle_state', { alert_text: '' });
  showToast('Stage Alert Cleared');
}

function broadcastLyricCollisionToHud(st) {
  if (!hudChannel) return;
  const s = st || lyricObsState || {};
  const slide = s.active_slide || {};
  const r1 = (slide.region1 || '').trim();
  const r2 = (slide.region2 || '').trim();
  const hasText = (r1 !== '' || r2 !== '');
  const lineCount = (r1 !== '' ? r1.split('\n').length : 0) + (r2 !== '' ? r2.split('\n').length : 0);
  const lyricOnScreen = !!s.show_running && !s.is_black && !s.is_clear && hasText;
  const lyricPosition = (document.getElementById('cfgLyricPos')?.value || 'bottom').toLowerCase();
  const lyricFontSize = Number(document.getElementById('cfgLyricFontSize')?.value || 42);

  hudChannel.postMessage({
    action: 'LYRIC_COLLISION_STATE',
    lyricOnScreen,
    lyricPosition,
    lyricFontSize,
    lyricLineCount: Math.max(1, lineCount)
  });
}

async function saveLyricOverlayStyle() {
  const payload = {
    host: document.getElementById('cfgMcHost').value,
    port: Number(document.getElementById('cfgMcPort').value),
    service_type: Number(document.getElementById('cfgMcService').value),
    password: document.getElementById('cfgMcPass').value,
    theme: document.getElementById('cfgLyricTheme').value,
    position: document.getElementById('cfgLyricPos').value,
    font_size: Number(document.getElementById('cfgLyricFontSize').value),
    bg_opacity: Number(document.getElementById('cfgLyricOpacity').value),
    text_color: document.getElementById('cfgLyricTextColor').value,
    accent_color: document.getElementById('cfgLyricAccentColor').value,
    show_section: document.getElementById('cfgLyricShowSection').checked,
    show_title: document.getElementById('cfgLyricShowTitle').checked,
    sync_church_hud: false
  };
  broadcastLyricCollisionToHud(lyricObsState);
  await lyricObsApi('save_config', payload);
  showToast('Updated Lyric Overlay Configuration', '#f97316');
}

async function startMulticastDaemon() {
  await saveLyricOverlayStyle();
  const res = await lyricObsApi('start_daemon');
  if (res.ok) {
    showToast(res.message, '#10b981');
    applyLyricObsState(res.state);
  }
}

async function stopMulticastDaemon() {
  const res = await lyricObsApi('stop_daemon');
  if (res.ok) {
    showToast(res.message, '#ef4444');
    applyLyricObsState(res.state);
  }
}

function applyLyricObsState(st) {
  if (!st) return;
  lyricObsState = st;
  broadcastLyricCollisionToHud(st);

  const status = (st.connection_status || 'idle').toLowerCase();
  const mcBadge = document.getElementById('multicastTopBadge');
  const mcText = document.getElementById('multicastTopText');
  mcText.textContent = `MC: ${status.toUpperCase()}`;
  mcBadge.className = 'conn-badge ' + (
    status === 'connected' ? 'connected' :
    (status === 'connecting' || status === 'starting') ? 'connecting' : 'disconnected'
  );

  if (st.connection_message) {
    document.getElementById('mcModalStatusMsg').textContent = st.connection_message;
  }

  document.getElementById('btnLyricBlack').classList.toggle('btn-danger', !!st.is_black);
  document.getElementById('btnLyricClear').classList.toggle('btn-orange', !!st.is_clear);

  if (st.item && Array.isArray(st.item.slides) && st.item.slides.length > 0) {
    const incomingSig = `${st.item.title1}||${st.item.slides.length}`;
    const currentSig = `${lyricLoadedTitle}||${lyricLoadedSlides.length}`;
    if (st.mode === 'multicast' || !lyricLoadedSlides.length || incomingSig !== currentSig) {
      lyricLoadedSlides = st.item.slides;
      lyricLoadedTitle = st.item.title1 || '';
      lyricLoadedAuthor = st.item.author || '';
      lyricActiveSlideNo = st.cur_slide_no || 1;
      renderLyricSlideCards(lyricActiveSlideNo);
    } else if (st.cur_slide_no && st.cur_slide_no !== lyricActiveSlideNo) {
      lyricActiveSlideNo = st.cur_slide_no;
      renderLyricSlideCards(lyricActiveSlideNo);
    }
  }
}

async function loadInitialLyricAndHudState() {
  try {
    const res = await lyricObsApi('state');
    if (res.ok) {
      const c = res.config || {};
      document.getElementById('cfgMcHost').value = c.host || '127.0.0.1';
      document.getElementById('cfgMcPort').value = c.port || 9701;
      document.getElementById('cfgMcService').value = c.service_type ?? 1;
      document.getElementById('cfgMcPass').value = c.password || '';
      document.getElementById('cfgLyricTheme').value = c.theme || 'glass';
      document.getElementById('cfgLyricPos').value = c.position || 'bottom';
      document.getElementById('cfgLyricFontSize').value = c.font_size || 42;
      document.getElementById('cfgLyricOpacity').value = c.bg_opacity ?? 0.78;
      document.getElementById('cfgLyricTextColor').value = c.text_color || '#ffffff';
      document.getElementById('cfgLyricAccentColor').value = c.accent_color || '#f97316';
      document.getElementById('cfgLyricShowSection').checked = c.show_section !== false;
      document.getElementById('cfgLyricShowTitle').checked = c.show_title !== false;
      applyLyricObsState(res.state);
    }
  } catch (e) {}

  try {
    const hRes = await fetch('sync.php').then(r => r.json());
    if (hRes && hRes.state) applyIncomingHudState(hRes.state);
  } catch (e) {}
}

setInterval(async () => {
  try {
    const res = await lyricObsApi('state');
    if (res.ok) applyLyricObsState(res.state);
  } catch (e) {}
}, 1200);

/* ============================================================================
 * PART 4: 1-CLICK AUTO-INJECT OVERLAYS INTO OBS SCENE VIA WEBSOCKET V5
 * ============================================================================ */
async function autoInjectOverlaysToObs() {
  const sceneName = state.currentProgramScene;
  if (!sceneName) {
    showToast('No active OBS scene selected!', '#ef4444');
    return;
  }

  const hudUrl = `${BASE_URL}/hud.php`;
  const lyricUrl = `${BASE_URL}/obs.php`;

  if (state.simMode) {
    if (!state.sceneItems.some(i => i.sourceName === 'MegaOBS Church HUD (1080p)')) {
      state.sceneItems.unshift({
        sceneItemId: Date.now(),
        sourceName: 'MegaOBS Church HUD (1080p)',
        inputKind: 'browser_source',
        sceneItemEnabled: true,
        sceneItemLocked: false,
        sceneItemTransform: { positionX: 0, positionY: 0, width: 1920, height: 1080, sourceWidth: 1920, sourceHeight: 1080, scaleX: 1, scaleY: 1 }
      });
    }
    renderSourcesPanel();
    showToast(`Injected MegaOBS Overlays into "${sceneName}" (Simulation)`, '#f5c242');
    return;
  }

  showToast(`Injecting 1080p Overlays into OBS Scene "${sceneName}"...`, '#f5c242');

  // Create or update Church HUD Browser Source
  await obsRequest('CreateInput', {
    sceneName,
    inputName: 'MegaOBS Church HUD (1080p)',
    inputKind: 'browser_source',
    inputSettings: { url: hudUrl, width: 1920, height: 1080, fps: 60, reroute_audio: false },
    sceneItemEnabled: true
  });
  await obsRequest('SetInputSettings', {
    inputName: 'MegaOBS Church HUD (1080p)',
    inputSettings: { url: hudUrl, width: 1920, height: 1080, fps: 60 },
    overlay: true
  });

  // Create or update Lyric Overlay Browser Source
  await obsRequest('CreateInput', {
    sceneName,
    inputName: 'MegaOBS Lyric Overlay (1080p)',
    inputKind: 'browser_source',
    inputSettings: { url: lyricUrl, width: 1920, height: 1080, fps: 60, reroute_audio: false },
    sceneItemEnabled: true
  });
  await obsRequest('SetInputSettings', {
    inputName: 'MegaOBS Lyric Overlay (1080p)',
    inputSettings: { url: lyricUrl, width: 1920, height: 1080, fps: 60 },
    overlay: true
  });

  await fetchSceneItems(sceneName);
  fetchCanvasScreenshots();
  showToast(`MegaOBS Church HUD & Lyric Overlays active in "${sceneName}"!`, '#10b981');
}

/* ============================================================================
 * PART 5: MODALS, DRAWERS & GLOBAL HOTKEYS
 * ============================================================================ */
function openObsUrlsModal() {
  document.getElementById('obsUrlsModal').classList.add('open');
}
function openMulticastModal() {
  document.getElementById('multicastModal').classList.add('open');
}
function openAboutUsModal() {
  document.getElementById('aboutUsModal').classList.add('open');
}
function closeModalById(id) {
  document.getElementById(id).classList.remove('open');
}
function openLyricStudioDrawer() {
  const iframe = document.getElementById('lyricStudioIframe');
  if (iframe.src === 'about:blank' || !iframe.src.includes('lyrics_editor.php')) {
    iframe.src = 'lyrics_editor.php';
  }
  document.getElementById('lyricStudioDrawer').classList.add('open');
}
function closeLyricStudioDrawer() {
  document.getElementById('lyricStudioDrawer').classList.remove('open');
  searchLyricDatabaseSongs(false);
}

function openPresetStudioDrawer(tab = 'scripture') {
  const iframe = document.getElementById('presetStudioIframe');
  const newTabBtn = document.getElementById('presetStudioNewTabBtn');
  if (newTabBtn) newTabBtn.href = `presets_editor.php?tab=${encodeURIComponent(tab)}`;
  if (iframe.src === 'about:blank' || !iframe.src.includes('presets_editor.php')) {
    iframe.src = `presets_editor.php?tab=${encodeURIComponent(tab)}`;
  } else {
    try {
      if (iframe.contentWindow && typeof iframe.contentWindow.switchCategory === 'function') {
        iframe.contentWindow.switchCategory(tab);
      }
    } catch (e) {
      iframe.src = `presets_editor.php?tab=${encodeURIComponent(tab)}`;
    }
  }
  document.getElementById('presetStudioDrawer').classList.add('open');
}
function closePresetStudioDrawer() {
  document.getElementById('presetStudioDrawer').classList.remove('open');
  initHudPresets();
}

function fallbackCopyText(txt) {
  const ta = document.createElement('textarea');
  ta.value = txt;
  ta.setAttribute('readonly', '');
  ta.style.position = 'fixed';
  ta.style.top = '-9999px';
  ta.style.left = '-9999px';
  document.body.appendChild(ta);
  ta.focus();
  ta.select();
  ta.setSelectionRange(0, txt.length);
  let ok = false;
  try {
    ok = document.execCommand('copy');
  } catch (e) {}
  document.body.removeChild(ta);
  return ok;
}

function copyTextById(id) {
  const el = document.getElementById(id);
  if (!el) return;
  const txt = el.textContent.trim();
  if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function' && window.isSecureContext) {
    navigator.clipboard.writeText(txt).then(() => {
      showToast(`Copied: ${txt}`, '#10b981');
    }).catch(() => {
      if (fallbackCopyText(txt)) showToast(`Copied: ${txt}`, '#10b981');
      else prompt('Copy this OBS URL:', txt);
    });
  } else {
    if (fallbackCopyText(txt)) {
      showToast(`Copied: ${txt}`, '#10b981');
    } else {
      prompt('Copy this OBS URL:', txt);
    }
  }
}

// Generic OBS Modal
let modalSaveHandler = null;
function openModal(title, bodyHtml, onSave) {
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalBody').innerHTML = bodyHtml;
  document.getElementById('modalBackdrop').classList.add('open');
  modalSaveHandler = onSave;
}
function closeModal() {
  document.getElementById('modalBackdrop').classList.remove('open');
  modalSaveHandler = null;
}
document.getElementById('btnCloseModal').addEventListener('click', closeModal);
document.getElementById('btnCancelModal').addEventListener('click', closeModal);
document.getElementById('btnSaveModal').addEventListener('click', () => { if (modalSaveHandler) modalSaveHandler(); });

// UI Event Listeners
document.getElementById('btnConnect').addEventListener('click', connectObs);
document.getElementById('btnAutoInjectObs').addEventListener('click', autoInjectOverlaysToObs);

document.getElementById('btnTogglePass').addEventListener('click', () => {
  const inp = document.getElementById('wsPass');
  inp.type = inp.type === 'password' ? 'text' : 'password';
});

document.getElementById('btnSimMode').addEventListener('click', () => {
  if (state.simMode) {
    state.simMode = false;
    document.getElementById('btnSimMode').classList.remove('active-toggle');
    connectObs();
  } else {
    enableSimulationMode();
    showToast('Simulation Mode Active', '#8b5cf6');
  }
});

document.getElementById('btnRefreshState').addEventListener('click', () => {
  if (state.simMode) renderAllDomPanels();
  else fetchFullObsState();
  showToast('Synced Full Broadcast State');
});

document.getElementById('btnFullscreen').addEventListener('click', () => {
  if (!document.fullscreenElement) document.documentElement.requestFullscreen().catch(() => {});
  else document.exitFullscreen().catch(() => {});
});

document.getElementById('btnStudioMode').addEventListener('click', async () => {
  state.studioMode = !state.studioMode;
  renderTransitionsPanel();
  renderScenesPanel();
  await obsRequest('SetStudioModeEnabled', { studioModeEnabled: state.studioMode });
  fetchCanvasScreenshots();
});

document.getElementById('selectTransition').addEventListener('change', async (ev) => {
  state.currentTransition = ev.target.value;
  await obsRequest('SetCurrentSceneTransition', { transitionName: state.currentTransition });
});

document.getElementById('inputTransDuration').addEventListener('change', async (ev) => {
  state.transitionDuration = Math.max(50, Number(ev.target.value) || 300);
  await obsRequest('SetCurrentSceneTransitionDuration', { transitionDuration: state.transitionDuration });
});

document.getElementById('btnCut').addEventListener('click', async () => {
  await obsRequest('SetCurrentSceneTransition', { transitionName: 'Cut' });
  await obsRequest('TriggerStudioModeTransition');
  showToast('CUT Transition', '#ef4444');
});

document.getElementById('btnAutoTrans').addEventListener('click', async () => {
  await obsRequest('TriggerStudioModeTransition');
  showToast(`${state.currentTransition} Transition`, '#3b82f6');
});

document.getElementById('btnQuickFade').addEventListener('click', async () => {
  await obsRequest('SetCurrentSceneTransition', { transitionName: 'Fade' });
  await obsRequest('SetCurrentSceneTransitionDuration', { transitionDuration: 500 });
  await obsRequest('TriggerStudioModeTransition');
  showToast('500ms Fade Transition', '#10b981');
});

function bindCanvasToggle(btnId, optKey) {
  const btn = document.getElementById(btnId);
  btn.classList.toggle('active-toggle', !!state.canvasOpts[optKey]);
  btn.addEventListener('click', () => {
    state.canvasOpts[optKey] = !state.canvasOpts[optKey];
    btn.classList.toggle('active-toggle', state.canvasOpts[optKey]);
  });
}
bindCanvasToggle('btnToggleComposite', 'showComposite');
bindCanvasToggle('btnToggleBoxes', 'showBoxes');
bindCanvasToggle('btnToggleGrid', 'showGrid');
bindCanvasToggle('btnToggleSafe', 'showSafeZones');
bindCanvasToggle('btnToggleOsd', 'showOsd');

document.getElementById('selectPreviewFps').addEventListener('change', (ev) => {
  state.canvasOpts.previewFps = Number(ev.target.value);
  startBackgroundLoops();
});

document.getElementById('btnSnapDownload').addEventListener('click', () => {
  const a = document.createElement('a');
  a.href = studioCanvas.toDataURL('image/png');
  a.download = `megaobs_${Date.now()}.png`;
  a.click();
  showToast('Downloaded Canvas PNG Snapshot', '#10b981');
});

document.getElementById('btnSnapServer').addEventListener('click', async () => {
  const imageData = studioCanvas.toDataURL('image/png');
  try {
    const resp = await fetch('obs_ws_api.php?action=save_canvas_snapshot', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ imageData })
    });
    const json = await resp.json();
    if (json.ok) showToast(`Saved Snapshot: ${json.url}`, '#10b981');
  } catch (e) {}
});

document.getElementById('btnCenterItem').addEventListener('click', async () => {
  const item = state.sceneItems.find(i => i.sceneItemId === state.selectedSceneItemId);
  if (!item) return;
  const t = item.sceneItemTransform || {};
  const posX = Math.round((state.baseWidth - Number(t.width || 640)) / 2);
  const posY = Math.round((state.baseHeight - Number(t.height || 360)) / 2);
  item.sceneItemTransform = { ...t, positionX: posX, positionY: posY };
  updateSelectedTransformBar();
  await obsRequest('SetSceneItemTransform', { sceneName: state.currentProgramScene, sceneItemId: item.sceneItemId, sceneItemTransform: { positionX: posX, positionY: posY } });
  fetchCanvasScreenshots();
});

document.getElementById('btnFitItem').addEventListener('click', async () => {
  const item = state.sceneItems.find(i => i.sceneItemId === state.selectedSceneItemId);
  if (!item) return;
  const t = item.sceneItemTransform || {};
  const scaleX = Number((state.baseWidth / Number(t.sourceWidth || state.baseWidth)).toFixed(4));
  const scaleY = Number((state.baseHeight / Number(t.sourceHeight || state.baseHeight)).toFixed(4));
  item.sceneItemTransform = { ...t, positionX: 0, positionY: 0, width: state.baseWidth, height: state.baseHeight, scaleX, scaleY };
  updateSelectedTransformBar();
  await obsRequest('SetSceneItemTransform', { sceneName: state.currentProgramScene, sceneItemId: item.sceneItemId, sceneItemTransform: { positionX: 0, positionY: 0, scaleX, scaleY } });
  fetchCanvasScreenshots();
});

document.getElementById('btnResetItem').addEventListener('click', async () => {
  const item = state.sceneItems.find(i => i.sceneItemId === state.selectedSceneItemId);
  if (!item) return;
  const t = item.sceneItemTransform || {};
  item.sceneItemTransform = { ...t, positionX: 0, positionY: 0, width: Number(t.sourceWidth || 640), height: Number(t.sourceHeight || 360), scaleX: 1, scaleY: 1 };
  updateSelectedTransformBar();
  await obsRequest('SetSceneItemTransform', { sceneName: state.currentProgramScene, sceneItemId: item.sceneItemId, sceneItemTransform: { positionX: 0, positionY: 0, scaleX: 1, scaleY: 1 } });
  fetchCanvasScreenshots();
});

document.getElementById('btnToggleStream').addEventListener('click', async () => { await obsRequest('ToggleStream'); setTimeout(pollTelemetryStats, 250); });
document.getElementById('btnToggleRecord').addEventListener('click', async () => { await obsRequest('ToggleRecord'); setTimeout(pollTelemetryStats, 250); });
document.getElementById('btnToggleVCam').addEventListener('click', async () => { await obsRequest('ToggleVirtualCam'); if (!state.simMode) setTimeout(fetchFullObsState, 250); });
document.getElementById('btnToggleReplay').addEventListener('click', async () => { await obsRequest('ToggleReplayBuffer'); if (!state.simMode) setTimeout(fetchFullObsState, 250); });

document.getElementById('btnAddScene').addEventListener('click', () => {
  openModal(
    'Create New OBS Scene',
    `<div class="field-group"><label class="field-label">Scene Name</label><input type="text" id="newSceneNameInput" class="input-compact" placeholder="e.g. Camera 2 Pulpit"></div>`,
    async () => {
      const name = document.getElementById('newSceneNameInput').value.trim();
      if (!name) return;
      closeModal();
      if (state.simMode) { state.scenes.push({ sceneName: name }); renderScenesPanel(); }
      else { await obsRequest('CreateScene', { sceneName: name }); await fetchFullObsState(); }
      showToast(`Created Scene: ${name}`, '#10b981');
    }
  );
});

document.getElementById('btnEditSource').addEventListener('click', async () => {
  const item = state.sceneItems.find(i => i.sceneItemId === state.selectedSceneItemId);
  if (!item) { showToast('Select a source first', '#f59e0b'); return; }
  const resp = await obsRequest('GetInputSettings', { inputName: item.sourceName });
  const cur = resp.responseData?.inputSettings || { text: item.sourceName, url: `${BASE_URL}/hud.php` };

  openModal(
    `Edit Source: ${item.sourceName}`,
    `<div class="field-group"><label class="field-label">Text Content (Text GDI+)</label><input type="text" id="editSrcText" class="input-compact" value="${escapeHtml(String(cur.text ?? ''))}"></div>
     <div class="field-group"><label class="field-label">Browser URL (Browser Source)</label><input type="text" id="editSrcUrl" class="input-compact" value="${escapeHtml(String(cur.url ?? ''))}"></div>`,
    async () => {
      const patch = {};
      const t = document.getElementById('editSrcText').value;
      const u = document.getElementById('editSrcUrl').value;
      if (t !== '') patch.text = t;
      if (u !== '') patch.url = u;
      closeModal();
      await obsRequest('SetInputSettings', { inputName: item.sourceName, inputSettings: patch, overlay: true });
      fetchCanvasScreenshots();
      showToast(`Updated ${item.sourceName}`, '#10b981');
    }
  );
});

// Single-Operator Unified Keyboard Shortcuts
window.addEventListener('keydown', (e) => {
  if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
  if (document.querySelector('.modal-backdrop.open')) return;

  if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
    e.preventDefault();
    stepLyricSlide(1);
  } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
    e.preventDefault();
    stepLyricSlide(-1);
  } else if (e.key === 'l' || e.key === 'L') {
    toggleHudLt('speaker');
  } else if (e.key === 's' || e.key === 'S') {
    toggleHudLt('scripture');
  } else if (e.key === 'o' || e.key === 'O') {
    toggleHudLt('giving');
  } else if (e.key === 't' || e.key === 'T') {
    toggleHudLt('ticker');
  } else if (['1', '2', '3', '4', '5'].includes(e.key)) {
    const hudScenes = ['landing', 'title', 'overlay', 'break', 'ending'];
    switchHudScene(hudScenes[parseInt(e.key, 10) - 1]);
  }
});

/* -------------------- BOOT MASTER ONE-SCREEN SYSTEM -------------------- */
initHudPresets();
loadInitialLyricAndHudState();
searchLyricDatabaseSongs(true);
enableSimulationMode();
state.simMode = false;
document.getElementById('btnSimMode').classList.remove('active-toggle');
requestAnimationFrame(drawStudioStageFrame);
connectObs();
</script>
</body>
</html>
