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
 */?>
<!DOCTYPE html>
<html lang="ms">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>SIB Kawasan Papar 2026 - Kebaktian Natal & Graduasi PKK</title>

  <!-- Google Fonts: Cinzel & Plus Jakarta Sans & Outfit -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="./css/church-hud.css">

  <!-- Immediate Transparent Mode Detector for OBS Studio -->
  <script>
    (function () {
      try {
        const p = new URLSearchParams(window.location.search);
        const s = (p.get('scene') || '').toLowerCase();
        const isTrans = p.get('transparent') === '1' || p.get('transparent') === 'true' || s === 'overlay' || s === 'lowerthird' || s === 'hud' || s === 'camera';
        if (isTrans) {
          document.documentElement.classList.add('transparent-bg');
          document.documentElement.setAttribute('data-transparent', 'true');
          document.documentElement.style.backgroundColor = 'transparent';
          if (s === 'overlay' || s === 'lowerthird' || s === 'hud' || s === 'camera') {
            document.documentElement.classList.add('scene-is-overlay');
          }
        }
      } catch (e) {}
    })();
  </script>
</head>
<body data-theme="natal-blue">

  <!-- Master 1080p Viewport Locked to 1920x1080 for OBS -->
  <div id="church-viewport">

    <!-- 60FPS Hardware-Accelerated Sacred Canvas Layers -->
    <canvas id="church-bg-canvas" class="canvas-layer"></canvas>
    <canvas id="church-fx-canvas" class="canvas-layer"></canvas>

    <!-- Ambient Glow & Vignette -->
    <div class="ambient-lighting"></div>

    <!-- Sacred Golden Corner Frame Accents -->
    <div class="sacred-frame">
      <div class="sacred-corner tl"></div>
      <div class="sacred-corner tr"></div>
      <div class="sacred-corner bl"></div>
      <div class="sacred-corner br"></div>
    </div>

    <!-- Scene Shutter Crossfade Wipe -->
    <div id="scene-shutter" class="scene-shutter"></div>

    <!-- =========================================================
         TOP HEADER HUD BAR
         ========================================================= -->
    <header id="churchTopBar" class="church-top-bar">
      <div class="church-brand-group">
        <div class="church-logo-badge" title="SIB Pekan Papar">
          <img src="./assets/logo-sib.png" alt="Logo SIB Pekan Papar" class="church-logo-img data-church-logo">
        </div>
        <div class="church-title-wrap">
          <span class="church-name-text data-church-name">SIB KAWASAN PAPAR 2026</span>
          <span class="church-sub-text data-service-subtitle">GEREJA SIB KAYAU &bull; 22 NOV 2026</span>
        </div>
      </div>

      <div class="header-center-info" id="headerCenterPillWrap">
        <div class="header-service-pill">
          <span class="data-service-title">NATAL &amp; GRADUASI PKK</span>
        </div>
      </div>

      <div class="header-right-telemetry">
        <div class="live-badge-glow" id="headerLiveBadgeWrap">
          <span class="pulse-dot-red"></span>
          <span class="data-live-badge">LIVE</span>
        </div>
        <div class="clock-telemetry" id="headerClockWrap">
          <span class="clock-time-val data-clock-time">09:30:00</span>
          <span class="clock-date-val data-clock-date">AHAD, 22 NOV 2026</span>
        </div>
      </div>
    </header>

    <!-- =========================================================
         BOTTOM SOCIALS & MOTTO BAR
         ========================================================= -->
    <footer class="church-bottom-bar">
      <div class="bottom-socials">
        <div class="social-item">
          <!-- Facebook SVG -->
          <svg viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg>
          <span class="data-social-fb">PKK SIB Kawasan Papar</span>
        </div>
        <div class="social-item">
          <!-- YouTube SVG -->
          <svg viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
          <span class="data-social-yt">SIB Kawasan Papar</span>
        </div>
        <div class="social-item">
          <!-- Shirt Icon / Dress Code Pill -->
          <span style="display: inline-flex; align-items: center; gap: 6px; color: #93c5fd; font-weight: 700; font-size: 0.8rem; background: rgba(29, 78, 216, 0.35); padding: 3px 10px; border-radius: 12px; border: 1px solid #38bdf8;">
            ðŸ‘” WARNA BAJU: BIRU
          </span>
        </div>
      </div>

      <div class="bottom-motto data-church-motto">
        "Tema: Aku Kecil, Aku Berharga &bull; Mikha 5:1â€“3"
      </div>
    </footer>

    <!-- Running Marquee Ticker -->
    <div class="church-ticker-wrap">
      <div class="ticker-label-badge">
        <span class="data-ticker-badge">INFO NATAL &amp; PKK</span>
      </div>
      <div class="ticker-content-track">
        <span class="ticker-inner-text data-ticker-text">
          Shalom! Selamat Datang ke Kebaktian Natal &amp; Graduasi PKK SIB Kawasan Papar 2026 â€¢ Tema: "Aku Kecil, Aku Berharga" (Mikha 5:1â€“3) â€¢ Tempat: Gereja SIB Kayau â€¢ Pengkhotbah: Pta. Tinggih Sulog â€¢ Tarikh: 22 Nov 2026 (Ahad 9.30 Pagi) â€¢ Warna Baju: Biru â€¢ Pengerusi PKK: Welter Lainor â€¢ Tuhan Yesus Memberkati!
        </span>
      </div>
    </div>

    <!-- =========================================================
         SCENE 1: LANDING / STARTING SOON
         ========================================================= -->
    <div id="scene-landing" class="scene-container scene-landing">
      <div class="landing-center-deck">
        <div class="festive-ribbon-tag">
          <span>ðŸŒŸ PELAYANAN KANAK-KANAK SIB KAWASAN PAPAR ðŸŒŸ</span>
        </div>

        <h1 class="landing-hero-title data-landing-title">KEBAKTIAN NATAL &amp; GRADUASI PKK</h1>
        <div class="landing-hero-subtitle data-landing-subtitle">"AKU KECIL, AKU BERHARGA" &bull; GEREJA SIB KAYAU</div>

        <!-- Event Detail Quick Pills -->
        <div class="event-pills-row">
          <div class="event-meta-pill highlight-gold">
            <span>ðŸ“… 22 NOV 2026 (AHAD)</span>
          </div>
          <div class="event-meta-pill highlight-gold">
            <span>â° 9.30 PAGI</span>
          </div>
          <div class="event-meta-pill highlight-red">
            <span>ðŸ“ GEREJA SIB KAYAU</span>
          </div>
          <div class="event-meta-pill highlight-blue">
            <span>ðŸ‘” WARNA BAJU: BIRU</span>
          </div>
          <div class="event-meta-pill highlight-gold">
            <span>ðŸŽ¤ PTA. TINGGIH SULOG</span>
          </div>
        </div>

        <!-- Glowing Circular Countdown Timer -->
        <div class="countdown-box">
          <svg class="countdown-svg" viewBox="0 0 260 260">
            <defs>
              <linearGradient id="goldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#f5c242" />
                <stop offset="50%" stop-color="#ffeaa7" />
                <stop offset="100%" stop-color="#d4af37" />
              </linearGradient>
            </defs>
            <circle class="countdown-circle-bg" cx="130" cy="130" r="120"></circle>
            <circle id="countdownRingCircle" class="countdown-circle-glow" cx="130" cy="130" r="120"></circle>
          </svg>
          <div class="countdown-inner-content">
            <div id="landingCountdownText" class="countdown-number">05:00</div>
            <div class="countdown-subtext">COUNTDOWN</div>
          </div>
        </div>

        <!-- Scripture Spotlight Card -->
        <div class="scripture-spotlight-card">
          <div class="scripture-verse-text data-scripture-text">
            "Tetapi engkau, hai Betlehem Efrata, hai yang terkecil di antara kaum-kaum Yehuda, dari padamu akan bangkit bagi-Ku seorang yang akan memerintah Israel, yang permulaannya sudah sejak purbakala, sejak dahulu kala."
          </div>
          <div class="scripture-reference-badge data-scripture-ref">
            MIKHA 5:1â€“3 (ALKITAB TB)
          </div>
        </div>
      </div>
    </div>

    <!-- =========================================================
         SCENE 2: BREAK & OFFERING / INTERMISSION
         ========================================================= -->
    <div id="scene-break" class="scene-container scene-break">
      <div class="break-layout-grid">
        <div class="break-left-deck">
          <div class="festive-ribbon-tag" style="margin-bottom: 12px;">
            <span>ðŸŽ„ REHAT SEKETIKA &bull; PERSIAPAN ACARA ðŸŽ“</span>
          </div>

          <h1 class="break-hero-title data-break-title">IBADAH AKAN BERSAMBUNG</h1>
          <p class="break-message-desc data-break-desc">
            Sesi Sambutan Natal &amp; Upacara Graduasi PKK 2026 akan bersambung sebentar lagi. Sediakan hati &amp; sukacita memuji Tuhan.
          </p>

          <div class="break-timer-cards-row">
            <div class="break-mini-card">
              <span class="mini-card-label">BERSAMBUNG DALAM</span>
              <span id="breakCountdownVal" class="mini-card-value">03:00</span>
            </div>
            <div class="break-mini-card">
              <span class="mini-card-label">TEMPAT KEBAKTIAN</span>
              <span class="mini-card-value" style="font-size: 1.4rem;">GEREJA SIB KAYAU</span>
            </div>
          </div>

          <div class="event-meta-pill highlight-blue" style="width: fit-content;">
            <span>ðŸ‘” KOD WARNA BAJU: BIRU &bull; SIB KAWASAN PAPAR</span>
          </div>
        </div>

        <!-- Right Side: Offering & Tithes Card -->
        <div class="giving-card-deck">
          <div class="giving-card-header">
            <div class="giving-title-group">
              <div class="giving-logo-badge" title="SIB Pekan Papar">
                <img src="./assets/logo-sib.png" alt="Logo SIB Pekan Papar" class="giving-logo-img data-church-logo">
              </div>
              <div>
                <h3 class="giving-h3">PERSEMBAHAN KASIH &amp; PERPULUHAN</h3>
                <span class="giving-subtitle">SIB PEKAN PAPAR &bull; PUBLIC BANK</span>
              </div>
            </div>
          </div>

          <div class="giving-card-content-grid">
            <div class="giving-details-list">
              <div class="giving-detail-item">
                <span class="giving-item-label">BANK</span>
                <span class="giving-item-val data-giving-bank">PUBLIC BANK</span>
              </div>
              <div class="giving-detail-item">
                <span class="giving-item-label">NOMBOR AKAUN</span>
                <span class="giving-item-val highlight data-giving-account">3235424420</span>
              </div>
              <div class="giving-detail-item">
                <span class="giving-item-label">NAMA AKAUN</span>
                <span class="giving-item-val data-giving-name">SIB PEKAN PAPAR</span>
              </div>
              <div class="giving-detail-item">
                <span class="giving-item-label">RUJUKAN</span>
                <span class="giving-item-val data-giving-ref">Persembahan / Perpuluhan</span>
              </div>
            </div>

            <!-- DuitNow QR Code Frame -->
            <div class="giving-qr-col">
              <div class="giving-qr-card">
                <div class="qr-glow-frame">
                  <img src="./assets/qr-offering.jpg" alt="DuitNow QR Code" class="giving-qr-img data-giving-qr">
                </div>
                <div class="qr-brand-label">
                  <span class="qr-dot"></span>
                  <span>IMBAS QR DUITNOW</span>
                </div>
              </div>
            </div>
          </div>

          <div class="giving-scripture-quote">
            "Biarkanlah anak-anak itu datang kepada-Ku... sebab orang-orang seperti itulah yang empunya Kerajaan Sorga." &bull; Matius 19:14
          </div>
        </div>
      </div>
    </div>

    <!-- =========================================================
         SCENE 3: TITLE / SERMON PRESENTATION SCENE
         ========================================================= -->
    <div id="scene-title" class="scene-container scene-title">
      <div class="title-center-deck">
        <div class="festive-ribbon-tag data-sermon-series">
          SAMBUTAN NATAL &amp; GRADUASI PKK SIB KAWASAN PAPAR 2026
        </div>

        <h1 class="title-sermon-hero data-sermon-title">"AKU KECIL, AKU BERHARGA"</h1>

        <div class="title-scripture-badge">
          <span>PETIKAN FIRMAN: </span>
          <strong class="data-sermon-scripture">MIKHA 5:1â€“3</strong>
        </div>

        <div class="speaker-profile-card">
          <div class="speaker-avatar-frame">
            <!-- Pulpit / Mic SVG -->
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
          </div>
          <div class="speaker-info-col">
            <span class="speaker-role-label data-speaker-role">PENGKHOTBAH &bull; PENATUA SIB</span>
            <span class="speaker-name-hero data-speaker-name">PTA. TINGGIH SULOG</span>
          </div>
        </div>

        <div style="margin-top: 24px; font-size: 0.84rem; color: #93c5fd; letter-spacing: 1.5px; text-transform: uppercase;">
          TEMPAT: <strong>GEREJA SIB KAYAU</strong> &bull; PENGERUSI PKK: <strong>WELTER LAINOR</strong>
        </div>
      </div>
    </div>

    <!-- =========================================================
         SCENE 4: TRANSPARENT LOWER-THIRD & HUD OVERLAY (FOR CAMERA)
         ========================================================= -->
    <div id="scene-overlay" class="scene-container scene-overlay">
      <!-- Speaker Lower-Third Banner -->
      <div id="speakerLowerThird" class="church-lower-third-speaker">
        <div class="lt-speaker-avatar">
          <svg viewBox="0 0 24 24"><path d="M12 2a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3zm5 9a5 5 0 0 1-10 0H5a7 7 0 0 0 6 6.92V21h2v-3.08A7 7 0 0 0 19 11h-2z"/></svg>
        </div>
        <div class="lt-speaker-details">
          <div class="lt-sermon-header-row">
            <span class="lt-sermon-badge data-sermon-title">Aku Kecil, Aku Berharga</span>
            <span class="lt-sermon-ref-pill data-sermon-scripture">Mikha 5:1-3</span>
          </div>
          <span class="lt-speaker-name data-speaker-name">Pta. Tinggih Sulog</span>
          <span class="lt-speaker-title data-speaker-role">Pengkhotbah &bull; Mikha 5:1â€“3</span>
        </div>
      </div>

      <!-- Scripture HUD Banner -->
      <div id="scriptureHud" class="church-scripture-hud">
        <div class="scripture-hud-header">
          <span class="scripture-ref-pill data-scripture-ref">MIKHA 5:1â€“3</span>
          <span class="scripture-version-tag data-scripture-version">ALKITAB TB</span>
        </div>
        <div class="scripture-body-text data-scripture-text">
          "Tetapi engkau, hai Betlehem Efrata, hai yang terkecil di antara kaum-kaum Yehuda, dari padamu akan bangkit bagi-Ku seorang yang akan memerintah Israel, yang permulaannya sudah sejak purbakala, sejak dahulu kala."
        </div>
      </div>

      <!-- Worship Song Lyrics Prompter -->
      <div id="worshipHud" class="church-lyrics-hud">
        <div class="lyrics-song-meta">
          <span class="data-worship-title">MALAM KUDUS (SILENT NIGHT)</span> &bull; <span class="data-worship-artist">LAGU NATAL</span>
        </div>
        <div class="lyrics-active-line-1 data-worship-line-1">
          Malam kudus, sunyi senyap, bintang-Mu gemerlap
        </div>
        <div class="lyrics-active-line-2 data-worship-line-2">
          Jurus'lamat manusia ada datang di dunia
        </div>
      </div>

      <!-- Offering & QR Lower-Third Banner -->
      <div id="givingLowerThird" class="church-lower-third-giving">
        <div class="lt-giving-left">
          <div class="lt-giving-logo" title="SIB Pekan Papar">
            <img src="./assets/logo-sib.png" alt="Logo SIB" class="data-church-logo">
          </div>
          <div class="lt-giving-info">
            <span class="lt-giving-title">PERSEMBAHAN &bull; PUBLIC BANK</span>
            <span class="lt-giving-acc data-giving-account">3235424420</span>
            <span class="lt-giving-name data-giving-name">SIB PEKAN PAPAR</span>
          </div>
        </div>
        <div class="lt-giving-qr-wrap">
          <img src="./assets/qr-offering.jpg" alt="DuitNow QR Code" class="lt-giving-qr-img data-giving-qr">
        </div>
      </div>
    </div>

    <!-- =========================================================
         SCENE 5: ENDING / CLOSING BENEDICTION
         ========================================================= -->
    <div id="scene-ending" class="scene-container scene-ending">
      <div class="ending-center-deck">
        <div class="festive-ribbon-tag">
          <span>ðŸŒŸ TAHNIAH KEPADA SEMUA GRADUAN KANAK-KANAK PKK 2026! ðŸŽ“</span>
        </div>

        <h1 class="landing-hero-title" style="font-size: 3.2rem; margin-bottom: 20px;">TERIMA KASIH ATAS KEHADIRAN ANDA</h1>

        <!-- Benediction Scripture Card -->
        <div class="ending-benediction-card">
          <div class="benediction-header">DOA BERKAT &amp; AMANAT ANAK-ANAK TUHAN</div>
          <div class="benediction-text">
            "Didiklah orang muda menurut jalan yang patut baginya, maka pada masa tuanya pun ia tidak akan menyimpang dari pada jalan itu. TUHAN memberkati engkau dan melindungi engkau; TUHAN menyinari engkau dengan wajah-Nya dan memberi engkau kasih karunia."
          </div>
          <div class="benediction-ref">AMSAL 22:6 &bull; BILANGAN 6:24â€“26</div>
        </div>

        <!-- Weekly Service Schedule -->
        <div class="schedule-grid-row">
          <div class="schedule-card-item">
            <span class="schedule-day-badge">TARIKH</span>
            <span class="schedule-event-name">22 Nov 2026</span>
            <span class="schedule-event-time">Ahad 9.30 AM</span>
          </div>
          <div class="schedule-card-item">
            <span class="schedule-day-badge">TEMPAT</span>
            <span class="schedule-event-name">Gereja SIB Kayau</span>
            <span class="schedule-event-time">Kawasan Papar</span>
          </div>
          <div class="schedule-card-item">
            <span class="schedule-day-badge">TEMA</span>
            <span class="schedule-event-name">Aku Kecil, Berharga</span>
            <span class="schedule-event-time">Mikha 5:1â€“3</span>
          </div>
          <div class="schedule-card-item">
            <span class="schedule-day-badge">ANJURAN</span>
            <span class="schedule-event-name">Pelayanan Kanak-Kanak</span>
            <span class="schedule-event-time">Welter Lainor</span>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Scripts -->
  <script src="./js/presets.js"></script>
  <script src="./js/church-canvas.js"></script>
  <script src="./js/church-core.js"></script>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      window.churchApp = new window.ChurchCore();
    });
  </script>
</body>
</html>
