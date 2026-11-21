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
 * SIB Pekan Papar - Church Live Stream Core Controller
 * Manages scenes, countdown timer, Web Audio chime synthesis,
 * BroadcastChannel real-time sync, and lower-third HUD animations
 */

(function (window) {
  'use strict';

  class ChurchCore {
    constructor() {
      this.canvasEngine = null;
      this.channel = null;
      this.audioCtx = null;
      this.chimeEnabled = true;

      // Presets reference
      const presets = window.SIBChurchPresets || {};
      const church = presets.churchInfo || {};
      const defSermon = (presets.sermonPresets && presets.sermonPresets[0]) || {};
      const defScripture = (presets.scripturePresets && presets.scripturePresets[0]) || {};
      const defSong = (presets.worshipSongs && presets.worshipSongs[0]) || { lines: [{ l1: '', l2: '' }] };

      // Master State
      this.state = {
        scene: 'landing', // 'landing', 'break', 'title', 'overlay', 'ending'
        theme: 'natal-blue',
        transparentBg: false,

        churchName: church.name || 'SIB KAWASAN PAPAR 2026',
        churchMotto: church.motto || 'Tema: "Aku Kecil, Aku Berharga" • Mikha 5:1–3',
        serviceTitle: church.serviceTitle || 'KEBAKTIAN NATAL & GRADUASI PKK',
        serviceSubtitle: church.serviceSubtitle || 'GEREJA SIB KAYAU • 22 NOV 2026',

        // Top Header Bar (LIVE Badge, Clock & Branding)
        headerVisible: true,
        headerFontSize: 24,
        headerOpacity: 0.94,
        headerStyle: 'solid-gold',
        liveBadgeText: 'LIVE',
        headerShowClock: true,
        headerShowLive: true,
        headerShowCenterPill: true,

        // Landing Scene
        landingTitle: 'KEBAKTIAN NATAL & GRADUASI PKK',
        landingSubtitle: '"AKU KECIL, AKU BERHARGA" â€¢ GEREJA SIB KAYAU',

        // Sermon & Speaker
        sermonTitle: defSermon.title || 'Aku Kecil, Aku Berharga',
        sermonSeries: defSermon.series || 'Sambutan Natal & Graduasi PKK 2026',
        speakerName: defSermon.speaker || 'Pta. Tinggih Sulog',
        speakerRole: defSermon.role || 'Pengkhotbah Kebaktian Natal & Graduasi PKK',
        sermonScripture: defSermon.scripture || 'Mikha 5:1â€“3',

        // Scripture HUD Overlay
        scriptureRef: defScripture.ref || 'Mikha 5:1â€“3',
        scriptureVersion: defScripture.version || 'Alkitab TB',
        scriptureText: defScripture.text || 'Tetapi engkau, hai Betlehem Efrata, hai yang terkecil di antara kaum-kaum Yehuda, dari padamu akan bangkit bagi-Ku seorang yang akan memerintah Israel, yang permulaannya sudah sejak purbakala, sejak dahulu kala.',
        scriptureVisible: false,
        scriptureFontSize: 32,
        scriptureFontFamily: 'sans',
        scriptureStyle: 'bold-glass',

        // Worship Lyrics HUD Overlay
        worshipSongTitle: defSong.title || 'Malam Kudus (Silent Night)',
        worshipArtist: defSong.artist || 'Lagu Natal Tradisional',
        worshipLine1: (defSong.lines && defSong.lines[0] && defSong.lines[0].l1) || 'Malam kudus, sunyi senyap, bintang-Mu gemerlap',
        worshipLine2: (defSong.lines && defSong.lines[0] && defSong.lines[0].l2) || 'Jurus\'lamat manusia ada datang di dunia',
        worshipVisible: false,

        // Speaker Lower-Third
        speakerLtVisible: false,
        speakerFontSize: 32,
        speakerFontFamily: 'sans',
        speakerStyle: 'glass-card',
        speakerPos: 'bottom-left',
        masterScalePreset: 'M',

        // Break Scene
        breakTitle: 'IBADAH AKAN BERSAMBUNG SEBENTAR LAGI',
        breakDesc: 'Sesi Sambutan Natal & Upacara Graduasi PKK 2026 akan bersambung sebentar lagi. Sediakan hati & sukacita memuji Tuhan.',
        breakTotalSeconds: 180,
        breakRemainingSeconds: 180,
        breakTimerRunning: false,

        // Giving / Offering
        givingBank: (church.giving && church.giving.bankName) || 'Public Bank',
        givingAccount: (church.giving && church.giving.accountNumber) || '3235424420',
        givingName: (church.giving && church.giving.accountName) || 'SIB PEKAN PAPAR',
        givingRef: (church.giving && church.giving.reference) || 'Persembahan / Perpuluhan',
        givingQrUrl: (church.giving && church.giving.qrCodeUrl) || './assets/qr-offering.jpg',
        givingLtVisible: false,
        givingQrSize: 96,
        givingPosMode: 'bottom-right', // 'bottom-right', 'bottom-left', 'center', 'top-right', 'top-left', 'custom'
        givingPosX: 50,
        givingPosY: 50,

        // Smart Collision Avoidance with Easyslides Lyric Overlay
        lyricOnScreen: false,
        lyricPosition: 'bottom',
        lyricFontSize: 42,
        lyricLineCount: 2,

        // Socials & Contacts
        socialFb: (church.socials && church.socials.facebook) || 'PKK SIB Kawasan Papar',
        socialYt: (church.socials && church.socials.youtube) || 'SIB Kawasan Papar',
        socialWa: (church.socials && church.socials.whatsapp) || '+60 1x-xxx xxxx',

        // Running Ticker
        tickerText: church.ticker || 'Shalom! Selamat Datang ke Sambutan Natal & Graduasi PKK SIB Kawasan Papar 2026 â€¢ Tarikh: 22 Nov 2026 (Ahad 9.30 Pagi) â€¢ Tempat: Gereja SIB Kayau â€¢ Pengkhotbah: Pta. Tinggih Sulog â€¢ Tema: "Aku Kecil, Aku Berharga" (Mikha 5:1â€“3) â€¢ Warna Baju: Biru â€¢ Pengerusi PKK: Welter Lainor â€¢ Tuhan Yesus Memberkati!',
        tickerBadge: 'INFO NATAL & PKK',
        tickerVisible: true,
        tickerFontSize: 26,
        tickerDuration: 28,

        // Landing Countdown Timer
        timer: {
          totalSeconds: 300,
          remainingSeconds: 300,
          isRunning: false
        }
      };

      this.timerInterval = null;
      this.breakTimerInterval = null;
      this.clockInterval = null;

      this.init();
    }

    init() {
      this.parseUrlParams();
      this.loadStoredState();
      this.applyTheme(this.state.theme);
      this.setupAutoFitScaling();
      this.applyDomBindings();
      this.setupBroadcastChannel();
      this.setupNetworkSync();
      this.setupKeybindings();
      this.startClock();

      // Initialize Canvas
      if (window.ChurchCanvasEngine) {
        this.canvasEngine = new window.ChurchCanvasEngine();
      }

      // Activate Initial Scene
      this.switchScene(this.state.scene, false);

      // Start Countdown if indicated
      if (this.state.timer.isRunning) {
        this.startTimer();
      } else {
        this.updateTimerDisplay();
      }

      window.addEventListener('resize', () => this.setupAutoFitScaling());
    }

    parseUrlParams() {
      const params = new URLSearchParams(window.location.search);

      if (params.has('scene')) {
        const s = params.get('scene').toLowerCase();
        if (s === 'landing' || s === 'starting' || s === 'welcome') this.state.scene = 'landing';
        else if (s === 'break' || s === 'offering' || s === 'brb') this.state.scene = 'break';
        else if (s === 'title' || s === 'sermon' || s === 'intro') this.state.scene = 'title';
        else if (s === 'overlay' || s === 'hud' || s === 'camera' || s === 'lowerthird') this.state.scene = 'overlay';
        else if (s === 'ending' || s === 'closing' || s === 'offline') this.state.scene = 'ending';
      }

      if (params.has('theme')) {
        this.state.theme = params.get('theme');
      }

      const isTransParam = params.has('transparent') && (params.get('transparent') === '1' || params.get('transparent') === 'true');
      const isOverlayScene = (this.state.scene === 'overlay');

      if (isTransParam || isOverlayScene) {
        this.state.transparentBg = true;
        document.documentElement.classList.add('transparent-bg');
        document.documentElement.setAttribute('data-transparent', 'true');
        document.documentElement.style.backgroundColor = 'transparent';
        if (document.body) {
          document.body.classList.add('transparent-bg');
          document.body.setAttribute('data-transparent', 'true');
          document.body.style.backgroundColor = 'transparent';
        }
        if (isOverlayScene) {
          document.documentElement.classList.add('scene-is-overlay');
          if (document.body) document.body.classList.add('scene-is-overlay');
        }
      }

      if (params.has('timer')) {
        const t = parseInt(params.get('timer'), 10);
        if (!isNaN(t) && t > 0) {
          this.state.timer.totalSeconds = t;
          this.state.timer.remainingSeconds = t;
          this.state.timer.isRunning = true;
        }
      }

      if (params.has('sermon')) {
        this.state.sermonTitle = params.get('sermon');
      }

      if (params.has('speaker')) {
        this.state.speakerName = params.get('speaker');
      }
    }

    loadStoredState() {
      try {
        const saved = localStorage.getItem('sib_church_hud_state');
        if (saved) {
          const parsed = JSON.parse(saved);
          const hasSceneInUrl = new URLSearchParams(window.location.search).has('scene');

          if (!hasSceneInUrl && parsed.scene) {
            this.state.scene = parsed.scene;
          }

          // Merge fields safely
          const allowedFields = [
            'theme', 'churchName', 'churchMotto', 'serviceTitle', 'serviceSubtitle',
            'headerVisible', 'headerFontSize', 'headerOpacity', 'headerStyle',
            'liveBadgeText', 'headerShowClock', 'headerShowLive', 'headerShowCenterPill',
            'sermonTitle', 'sermonSeries',
            'speakerName', 'speakerRole', 'sermonScripture', 'scriptureRef',
            'scriptureVersion', 'scriptureText', 'scriptureVisible',
            'scriptureFontSize', 'scriptureFontFamily', 'scriptureStyle',
            'speakerFontSize', 'speakerFontFamily', 'speakerStyle', 'speakerPos', 'masterScalePreset',
            'worshipSongTitle', 'worshipArtist', 'worshipLine1', 'worshipLine2',
            'worshipVisible', 'speakerLtVisible', 'givingLtVisible', 'tickerText', 'tickerBadge',
            'tickerVisible', 'tickerFontSize', 'tickerDuration', 'givingBank', 'givingAccount', 'givingName',
            'givingRef', 'givingQrSize', 'givingPosMode', 'givingPosX', 'givingPosY',
            'breakTitle', 'breakDesc'
          ];

          for (let f of allowedFields) {
            if (parsed[f] !== undefined) {
              this.state[f] = parsed[f];
            }
          }
        }
      } catch (e) {
        console.warn('Could not read stored church HUD state:', e);
      }
    }

    saveState() {
      try {
        localStorage.setItem('sib_church_hud_state', JSON.stringify(this.state));
      } catch (e) {}
    }

    setupAutoFitScaling() {
      const viewport = document.getElementById('church-viewport');
      if (!viewport) return;

      const windowWidth = window.innerWidth;
      const windowHeight = window.innerHeight;

      // In OBS standard browser source (1920x1080), scale is 1.0
      // In smaller preview windows, scale down proportionally without distorting coordinates
      const scaleX = windowWidth / 1920;
      const scaleY = windowHeight / 1080;
      const scale = Math.min(scaleX, scaleY);

      if (scale < 0.999 || scale > 1.001) {
        viewport.style.transform = `scale(${scale})`;
        viewport.style.left = `${(windowWidth - 1920 * scale) / 2}px`;
        viewport.style.top = `${(windowHeight - 1080 * scale) / 2}px`;
      } else {
        viewport.style.transform = 'none';
        viewport.style.left = '0px';
        viewport.style.top = '0px';
      }
    }

    setupBroadcastChannel() {
      if ('BroadcastChannel' in window) {
        this.channel = new BroadcastChannel('sib_church_hud_sync');
        this.channel.onmessage = (event) => {
          this.handleRemoteMessage(event.data);
        };
      }

      // Storage event fallback
      window.addEventListener('storage', (e) => {
        if (e.key === 'sib_church_hud_sync_event' && e.newValue) {
          try {
            this.handleRemoteMessage(JSON.parse(e.newValue));
          } catch (err) {}
        }
      });
    }

    setupNetworkSync() {
      this.lastServerVersion = 0;
      this.lastLyricUpdatedMs = -1;
      this.lastTimerTimestamp = 0;

      // Standalone HTTP State Polling via local sync.php
      const fetchState = async () => {
        try {
          const syncUrl = window.MEGA_OBS_SYNC_URL || './sync.php';
          const res = await fetch(syncUrl + '?_=' + Date.now(), { cache: 'no-store' }).catch(() => null);
          if (res && res.ok) {
            const data = await res.json();
            const serverState = data.state || data;
            if (serverState) {
              const verChanged = serverState.version && serverState.version !== this.lastServerVersion;
              const lyricChanged = serverState.lyricUpdatedMs !== undefined && serverState.lyricUpdatedMs !== this.lastLyricUpdatedMs;
              if (verChanged || lyricChanged) {
                if (serverState.version) this.lastServerVersion = serverState.version;
                if (serverState.lyricUpdatedMs !== undefined) this.lastLyricUpdatedMs = serverState.lyricUpdatedMs;
                this.syncWithServerState(serverState);
              }
            }
          }
        } catch (e) {}
      };

      fetchState();
      setInterval(fetchState, 350);
    }

    syncWithServerState(serverState) {
      if (!serverState) return;

      const hasSceneInUrl = new URLSearchParams(window.location.search).has('scene');
      if (!hasSceneInUrl && serverState.scene && serverState.scene !== this.state.scene) {
        this.switchScene(serverState.scene, true);
      }

      if (serverState.theme && serverState.theme !== this.state.theme) {
        this.applyTheme(serverState.theme);
      }

      // Mutually Exclusive Lower-Thirds & Ticker Synchronization
      if (serverState.tickerVisible) {
        this.activateLowerThird('ticker');
      } else if (serverState.speakerLtVisible) {
        this.activateLowerThird('speaker');
      } else if (serverState.scriptureVisible) {
        this.activateLowerThird('scripture');
      } else if (serverState.worshipVisible) {
        this.activateLowerThird('worship');
      } else if (serverState.givingLtVisible) {
        this.activateLowerThird('giving');
      } else if (serverState.tickerVisible === false &&
                 serverState.speakerLtVisible === false &&
                 serverState.scriptureVisible === false &&
                 serverState.worshipVisible === false &&
                 serverState.givingLtVisible === false) {
        if (this.state.tickerVisible || this.state.speakerLtVisible || this.state.scriptureVisible || this.state.worshipVisible || this.state.givingLtVisible) {
          this.activateLowerThird(null);
        }
      }

      // Textual & layout fields
      const textFields = [
        'churchName', 'churchMotto', 'serviceTitle', 'serviceSubtitle',
        'headerVisible', 'headerFontSize', 'headerOpacity', 'headerStyle',
        'liveBadgeText', 'headerShowClock', 'headerShowLive', 'headerShowCenterPill',
        'tickerText', 'tickerBadge', 'tickerFontSize', 'tickerDuration',
        'sermonTitle', 'sermonSeries', 'speakerName',
        'speakerRole', 'sermonScripture', 'scriptureRef', 'scriptureVersion',
        'scriptureText', 'scriptureFontSize', 'scriptureFontFamily', 'scriptureStyle',
        'speakerFontSize', 'speakerFontFamily', 'speakerStyle', 'speakerPos', 'masterScalePreset',
        'worshipSongTitle', 'worshipArtist', 'worshipLine1', 'worshipLine2',
        'breakTitle', 'breakDesc', 'givingBank', 'givingAccount', 'givingName', 'givingRef',
        'givingQrSize', 'givingPosMode', 'givingPosX', 'givingPosY',
        'lyricOnScreen', 'lyricPosition', 'lyricFontSize', 'lyricLineCount'
      ];
      let needsDom = false;
      for (let f of textFields) {
        if (serverState[f] !== undefined && serverState[f] !== this.state[f]) {
          this.state[f] = serverState[f];
          needsDom = true;
        }
      }
      if (needsDom) {
        this.applyDomBindings();
      }

      // Timer commands from tablet
      if (serverState.timerCommand && serverState.timerTimestamp && serverState.timerTimestamp !== this.lastTimerTimestamp) {
        this.lastTimerTimestamp = serverState.timerTimestamp;
        if (serverState.timerCommand === 'START') this.startTimer();
        else if (serverState.timerCommand === 'PAUSE') this.pauseTimer();
        else if (serverState.timerCommand === 'RESET') this.resetTimer(serverState.timerSeconds);
        else if (serverState.timerCommand === 'ADD_TIME') this.addTimerSeconds(serverState.timerSeconds);
      }
    }

    handleRemoteMessage(msg) {
      if (!msg || !msg.action) return;

      switch (msg.action) {
        case 'SWITCH_SCENE':
          if (msg.scene) this.switchScene(msg.scene, true);
          break;

        case 'UPDATE_FIELD':
          if (msg.field && msg.value !== undefined) {
            this.state[msg.field] = msg.value;
            this.applyDomBindings();
            this.saveState();
          }
          break;

        case 'UPDATE_BULK':
          if (msg.payload && typeof msg.payload === 'object') {
            Object.assign(this.state, msg.payload);
            this.applyDomBindings();
            this.saveState();
          }
          break;

        case 'SET_THEME':
          if (msg.theme) {
            this.applyTheme(msg.theme);
            this.saveState();
          }
          break;

        case 'TOGGLE_TICKER':
          this.setTickerVisibility(msg.visible !== undefined ? !!msg.visible : !this.state.tickerVisible);
          break;

        case 'UPDATE_TICKER_BADGE':
          if (msg.badge) {
            this.state.tickerBadge = msg.badge;
            this.applyDomBindings();
            this.saveState();
          }
          break;

        case 'TIMER_ACTION':
          if (msg.command === 'START') this.startTimer();
          else if (msg.command === 'PAUSE') this.pauseTimer();
          else if (msg.command === 'RESET') this.resetTimer(msg.seconds);
          else if (msg.command === 'ADD_TIME') this.addTimerSeconds(msg.seconds);
          break;

        case 'TOGGLE_SPEAKER_LT':
          this.setSpeakerLtVisibility(msg.visible !== undefined ? msg.visible : !this.state.speakerLtVisible);
          break;

        case 'TOGGLE_GIVING_LT':
          this.setGivingLtVisibility(msg.visible !== undefined ? msg.visible : !this.state.givingLtVisible);
          break;

        case 'TOGGLE_SCRIPTURE_HUD':
          this.setScriptureHudVisibility(msg.visible !== undefined ? msg.visible : !this.state.scriptureVisible);
          break;

        case 'TOGGLE_WORSHIP_HUD':
          this.setWorshipHudVisibility(msg.visible !== undefined ? msg.visible : !this.state.worshipVisible);
          break;

        case 'TOGGLE_HEADER_BAR':
          this.state.headerVisible = (msg.visible !== undefined) ? !!msg.visible : (this.state.headerVisible === false);
          this.syncVisibilityClasses();
          this.saveState();
          break;

        case 'PUSH_SCRIPTURE':
          if (msg.ref) this.state.scriptureRef = msg.ref;
          if (msg.text) this.state.scriptureText = msg.text;
          if (msg.version) this.state.scriptureVersion = msg.version;
          if (msg.scriptureFontSize) this.state.scriptureFontSize = Number(msg.scriptureFontSize);
          if (msg.scriptureFontFamily) this.state.scriptureFontFamily = msg.scriptureFontFamily;
          if (msg.scriptureStyle) this.state.scriptureStyle = msg.scriptureStyle;
          this.applyDomBindings();
          this.activateLowerThird('scripture');
          break;

        case 'PUSH_WORSHIP_LYRIC':
          if (msg.songTitle) this.state.worshipSongTitle = msg.songTitle;
          if (msg.artist) this.state.worshipArtist = msg.artist;
          if (msg.l1 !== undefined) this.state.worshipLine1 = msg.l1;
          if (msg.l2 !== undefined) this.state.worshipLine2 = msg.l2;
          this.applyDomBindings();
          this.activateLowerThird('worship');
          break;

        case 'HIDE_ALL_LT':
          this.activateLowerThird(null);
          break;

        case 'LYRIC_COLLISION_STATE':
          if (msg.lyricOnScreen !== undefined) this.state.lyricOnScreen = !!msg.lyricOnScreen;
          if (msg.lyricPosition) this.state.lyricPosition = String(msg.lyricPosition).toLowerCase();
          if (msg.lyricFontSize) this.state.lyricFontSize = Number(msg.lyricFontSize);
          if (msg.lyricLineCount) this.state.lyricLineCount = Number(msg.lyricLineCount);
          this.syncVisibilityClasses();
          break;
      }
    }

    applyTheme(themeName) {
      this.state.theme = themeName;
      document.body.setAttribute('data-theme', themeName);
      if (this.canvasEngine) {
        this.canvasEngine.updateThemeColors();
      }
    }

    switchScene(targetScene, withShutter = true) {
      if (!targetScene) return;

      const shutter = document.getElementById('scene-shutter');
      const allScenes = document.querySelectorAll('.scene-container');
      const targetElement = document.getElementById(`scene-${targetScene}`);

      if (!targetElement) return;

      const isOverlay = (targetScene === 'overlay');
      const hasTransParam = new URLSearchParams(window.location.search).has('transparent');

      if (isOverlay || hasTransParam || this.state.transparentBg) {
        document.documentElement.classList.add('transparent-bg');
        document.documentElement.setAttribute('data-transparent', 'true');
        document.documentElement.style.backgroundColor = 'transparent';
        if (document.body) {
          document.body.classList.add('transparent-bg');
          document.body.setAttribute('data-transparent', 'true');
          document.body.style.backgroundColor = 'transparent';
        }
        if (isOverlay) {
          document.documentElement.classList.add('scene-is-overlay');
          if (document.body) document.body.classList.add('scene-is-overlay');
        } else {
          document.documentElement.classList.remove('scene-is-overlay');
          if (document.body) document.body.classList.remove('scene-is-overlay');
        }
      } else {
        document.documentElement.classList.remove('transparent-bg', 'scene-is-overlay');
        document.documentElement.removeAttribute('data-transparent');
        document.documentElement.style.backgroundColor = '';
        if (document.body) {
          document.body.classList.remove('transparent-bg', 'scene-is-overlay');
          document.body.removeAttribute('data-transparent');
          document.body.style.backgroundColor = '';
        }
      }

      if (withShutter && shutter && !isOverlay) {
        shutter.classList.add('shutter-active');
        setTimeout(() => {
          allScenes.forEach(s => s.classList.remove('active'));
          targetElement.classList.add('active');
          this.state.scene = targetScene;
          this.saveState();
          this.updateSceneTopBarState(targetScene);

          setTimeout(() => {
            shutter.classList.remove('shutter-active');
          }, 150);
        }, 300);
      } else {
        allScenes.forEach(s => s.classList.remove('active'));
        targetElement.classList.add('active');
        this.state.scene = targetScene;
        this.saveState();
        this.updateSceneTopBarState(targetScene);
      }
    }

    updateSceneTopBarState(scene) {
      const bottomBar = document.querySelector('.church-bottom-bar');

      // In camera overlay scene, hide bottom bar while top header is controlled by headerVisible
      if (scene === 'overlay') {
        if (bottomBar) bottomBar.style.transform = 'translateY(100px)';
      } else {
        if (bottomBar) bottomBar.style.transform = 'none';
      }
    }

    applyDomBindings() {
      // Church Name
      document.querySelectorAll('.data-church-name').forEach(el => {
        el.textContent = this.state.churchName;
      });
      document.querySelectorAll('.data-live-badge').forEach(el => {
        el.textContent = this.state.liveBadgeText || 'LIVE';
      });

      // Church Motto
      document.querySelectorAll('.data-church-motto').forEach(el => {
        el.textContent = this.state.churchMotto;
      });

      // Service Title & Subtitle
      document.querySelectorAll('.data-service-title').forEach(el => {
        el.textContent = this.state.serviceTitle;
      });
      document.querySelectorAll('.data-service-subtitle').forEach(el => {
        el.textContent = this.state.serviceSubtitle;
      });

      // Landing Titles
      document.querySelectorAll('.data-landing-title').forEach(el => {
        el.textContent = this.state.landingTitle;
      });
      document.querySelectorAll('.data-landing-subtitle').forEach(el => {
        el.textContent = this.state.landingSubtitle;
      });

      // Sermon Titles & Speaker
      document.querySelectorAll('.data-sermon-title').forEach(el => {
        el.textContent = this.state.sermonTitle;
      });
      document.querySelectorAll('.data-sermon-series').forEach(el => {
        el.textContent = this.state.sermonSeries;
      });
      document.querySelectorAll('.data-speaker-name').forEach(el => {
        el.textContent = this.state.speakerName;
      });
      document.querySelectorAll('.data-speaker-role').forEach(el => {
        el.textContent = this.state.speakerRole;
      });
      document.querySelectorAll('.data-sermon-scripture').forEach(el => {
        el.textContent = this.state.sermonScripture;
      });

      // Scripture HUD
      document.querySelectorAll('.data-scripture-ref').forEach(el => {
        el.textContent = this.state.scriptureRef;
      });
      document.querySelectorAll('.data-scripture-version').forEach(el => {
        el.textContent = this.state.scriptureVersion;
      });
      document.querySelectorAll('.data-scripture-text').forEach(el => {
        el.textContent = this.state.scriptureText;
      });

      // Worship Lyrics HUD
      document.querySelectorAll('.data-worship-title').forEach(el => {
        el.textContent = this.state.worshipSongTitle;
      });
      document.querySelectorAll('.data-worship-artist').forEach(el => {
        el.textContent = this.state.worshipArtist;
      });
      document.querySelectorAll('.data-worship-line-1').forEach(el => {
        el.textContent = this.state.worshipLine1;
      });
      document.querySelectorAll('.data-worship-line-2').forEach(el => {
        el.textContent = this.state.worshipLine2;
      });

      // Break Scene
      document.querySelectorAll('.data-break-title').forEach(el => {
        el.textContent = this.state.breakTitle;
      });
      document.querySelectorAll('.data-break-desc').forEach(el => {
        el.textContent = this.state.breakDesc;
      });

      // Giving Info
      document.querySelectorAll('.data-giving-bank').forEach(el => {
        el.textContent = this.state.givingBank;
      });
      document.querySelectorAll('.data-giving-account').forEach(el => {
        el.textContent = this.state.givingAccount;
      });
      document.querySelectorAll('.data-giving-name').forEach(el => {
        el.textContent = this.state.givingName;
      });
      document.querySelectorAll('.data-giving-ref').forEach(el => {
        el.textContent = this.state.givingRef;
      });

      // QR Code and Logo Images
      document.querySelectorAll('.data-giving-qr').forEach(el => {
        if (el.tagName === 'IMG') el.src = this.state.givingQrUrl || './assets/qr-offering.jpg';
      });
      document.querySelectorAll('.data-church-logo').forEach(el => {
        if (el.tagName === 'IMG') el.src = './assets/logo-sib.png';
      });

      // Socials
      document.querySelectorAll('.data-social-fb').forEach(el => {
        el.textContent = this.state.socialFb;
      });
      document.querySelectorAll('.data-social-yt').forEach(el => {
        el.textContent = this.state.socialYt;
      });
      document.querySelectorAll('.data-social-wa').forEach(el => {
        el.textContent = this.state.socialWa;
      });

      // Ticker
      document.querySelectorAll('.data-ticker-text').forEach(el => {
        el.textContent = this.state.tickerText;
      });
      document.querySelectorAll('.data-ticker-badge').forEach(el => {
        el.textContent = this.state.tickerBadge || 'INFO GEREJA';
      });

      // Visibility toggles for overlays
      this.syncVisibilityClasses();
    }

    syncVisibilityClasses() {
      const topBar = document.querySelector('.church-top-bar');
      if (topBar) {
        topBar.classList.toggle('hidden', this.state.headerVisible === false);
        const hSize = Math.max(14, Math.min(54, Number(this.state.headerFontSize || 24)));
        const hOpac = Math.max(0.2, Math.min(1, Number(this.state.headerOpacity ?? 0.94)));
        topBar.style.setProperty('--header-font-size', `${hSize}px`);
        topBar.style.setProperty('--header-bg-opacity', String(hOpac));
        topBar.setAttribute('data-header-style', this.state.headerStyle || 'solid-gold');

        const centerPillWrap = document.getElementById('headerCenterPillWrap');
        if (centerPillWrap) centerPillWrap.style.display = (this.state.headerShowCenterPill === false) ? 'none' : 'flex';
        const liveWrap = document.getElementById('headerLiveBadgeWrap');
        if (liveWrap) liveWrap.style.display = (this.state.headerShowLive === false) ? 'none' : 'inline-flex';
        const clockWrap = document.getElementById('headerClockWrap');
        if (clockWrap) clockWrap.style.display = (this.state.headerShowClock === false) ? 'none' : 'flex';
      }

      const speakerLt = document.getElementById('speakerLowerThird');
      if (speakerLt) {
        speakerLt.classList.toggle('visible', !!this.state.speakerLtVisible);
        const spSize = Math.max(18, Math.min(72, Number(this.state.speakerFontSize || 32)));
        speakerLt.style.setProperty('--speaker-font-size', `${spSize}px`);
        speakerLt.setAttribute('data-speaker-font', this.state.speakerFontFamily || 'sans');
        speakerLt.setAttribute('data-speaker-style', this.state.speakerStyle || 'glass-card');
        speakerLt.setAttribute('data-speaker-pos', this.state.speakerPos || 'bottom-left');
      }

      const scriptureHud = document.getElementById('scriptureHud');
      if (scriptureHud) {
        scriptureHud.classList.toggle('visible', !!this.state.scriptureVisible);
        const scSize = Math.max(18, Math.min(72, Number(this.state.scriptureFontSize || 32)));
        scriptureHud.style.setProperty('--scripture-font-size', `${scSize}px`);
        scriptureHud.setAttribute('data-verse-font', this.state.scriptureFontFamily || 'sans');
        scriptureHud.setAttribute('data-verse-style', this.state.scriptureStyle || 'bold-glass');
      }

      const worshipHud = document.getElementById('worshipHud');
      if (worshipHud) {
        worshipHud.classList.toggle('visible', !!this.state.worshipVisible);
      }

      const givingLt = document.getElementById('givingLowerThird');
      if (givingLt) {
        givingLt.classList.toggle('visible', !!this.state.givingLtVisible);
        const qrSize = Math.max(60, Math.min(480, Number(this.state.givingQrSize || 96)));
        const posMode = this.state.givingPosMode || 'bottom-right';
        const posX = Math.max(5, Math.min(95, Number(this.state.givingPosX ?? 50)));
        const posY = Math.max(5, Math.min(95, Number(this.state.givingPosY ?? 50)));

        givingLt.style.setProperty('--qr-size', `${qrSize}px`);
        givingLt.style.setProperty('--qr-pos-x', `${posX}%`);
        givingLt.style.setProperty('--qr-pos-y', `${posY}%`);
        givingLt.setAttribute('data-qr-pos', posMode);
        givingLt.classList.toggle('qr-is-large', qrSize >= 170);

        // If custom position is near the bottom (Y >= 70%) and Easyslides Lyric is active at the bottom, lift it up
        const isBottomLyric = !!this.state.lyricOnScreen && (this.state.lyricPosition || 'bottom') === 'bottom';
        const customAvoidPx = (posMode === 'custom' && posY >= 70 && isBottomLyric) ? 185 : 0;
        givingLt.style.setProperty('--qr-custom-avoid', `${customAvoidPx}px`);
      }

      const tickerWrap = document.querySelector('.church-ticker-wrap');
      if (tickerWrap) {
        tickerWrap.classList.toggle('hidden', this.state.tickerVisible === false);
        const tFont = Math.max(12, Math.min(72, Number(this.state.tickerFontSize || 26)));
        const tDur = Math.max(6, Math.min(120, Number(this.state.tickerDuration || 28)));
        tickerWrap.style.setProperty('--ticker-font-size', `${tFont}px`);
        tickerWrap.style.setProperty('--ticker-duration', `${tDur}s`);
      }

      // Smart Collision Avoidance on #church-viewport based on Easyslides Lyric state & position
      const viewport = document.getElementById('church-viewport');
      if (viewport) {
        const lActive = !!this.state.lyricOnScreen;
        const lPos = (this.state.lyricPosition || 'bottom').toLowerCase();
        const lFont = Math.max(24, Number(this.state.lyricFontSize || 42));
        const lLines = Math.max(1, Math.min(4, Number(this.state.lyricLineCount || 2)));

        // Calculate dynamic safe height clearance above bottom Easyslides lyrics
        const avoidOffsetPx = Math.round(135 + (lFont * 1.42 * lLines));
        viewport.style.setProperty('--lyric-avoid-offset', `${avoidOffsetPx}px`);

        viewport.classList.toggle('lyric-active-bottom', lActive && lPos === 'bottom');
        viewport.classList.toggle('lyric-active-center', lActive && lPos === 'center');
        viewport.classList.toggle('lyric-active-top', lActive && lPos === 'top');
      }
    }

    activateLowerThird(ltName) {
      this.state.tickerVisible = (ltName === 'ticker');
      this.state.speakerLtVisible = (ltName === 'speaker');
      this.state.scriptureVisible = (ltName === 'scripture');
      this.state.worshipVisible = (ltName === 'worship');
      this.state.givingLtVisible = (ltName === 'giving');
      this.syncVisibilityClasses();
      this.saveState();
    }

    setGivingLtVisibility(visible) {
      if (visible) {
        this.activateLowerThird('giving');
      } else {
        this.state.givingLtVisible = false;
        this.syncVisibilityClasses();
        this.saveState();
      }
    }

    setTickerVisibility(visible) {
      if (visible) {
        this.activateLowerThird('ticker');
      } else {
        this.state.tickerVisible = false;
        this.syncVisibilityClasses();
        this.saveState();
      }
    }

    setSpeakerLtVisibility(visible) {
      if (visible) {
        this.activateLowerThird('speaker');
      } else {
        this.state.speakerLtVisible = false;
        this.syncVisibilityClasses();
        this.saveState();
      }
    }

    setScriptureHudVisibility(visible) {
      if (visible) {
        this.activateLowerThird('scripture');
      } else {
        this.state.scriptureVisible = false;
        this.syncVisibilityClasses();
        this.saveState();
      }
    }

    setWorshipHudVisibility(visible) {
      if (visible) {
        this.activateLowerThird('worship');
      } else {
        this.state.worshipVisible = false;
        this.syncVisibilityClasses();
        this.saveState();
      }
    }

    /* =========================================================
       COUNTDOWN TIMERS & WEB AUDIO CHIME
       ========================================================= */
    startTimer() {
      if (this.timerInterval) clearInterval(this.timerInterval);
      this.state.timer.isRunning = true;

      this.timerInterval = setInterval(() => {
        if (this.state.timer.remainingSeconds > 0) {
          this.state.timer.remainingSeconds--;
          this.updateTimerDisplay();

          if (this.state.timer.remainingSeconds === 0) {
            this.pauseTimer();
            this.playSacredChime();
          }
        }
      }, 1000);

      this.updateTimerDisplay();
      this.saveState();
    }

    pauseTimer() {
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
        this.timerInterval = null;
      }
      this.state.timer.isRunning = false;
      this.saveState();
    }

    resetTimer(seconds) {
      this.pauseTimer();
      const secs = (seconds !== undefined && seconds > 0) ? seconds : this.state.timer.totalSeconds;
      this.state.timer.totalSeconds = secs;
      this.state.timer.remainingSeconds = secs;
      this.updateTimerDisplay();
      this.saveState();
    }

    addTimerSeconds(sec) {
      this.state.timer.remainingSeconds = Math.max(0, this.state.timer.remainingSeconds + sec);
      this.state.timer.totalSeconds = Math.max(this.state.timer.totalSeconds, this.state.timer.remainingSeconds);
      this.updateTimerDisplay();
      this.saveState();
    }

    updateTimerDisplay() {
      const rem = Math.max(0, this.state.timer.remainingSeconds);
      const total = Math.max(1, this.state.timer.totalSeconds);

      const mins = Math.floor(rem / 60);
      const secs = rem % 60;
      const formatted = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

      // Update Landing Text
      const timerEl = document.getElementById('landingCountdownText');
      if (timerEl) {
        timerEl.textContent = formatted;
      }

      // Update SVG Circular Ring
      const circle = document.getElementById('countdownRingCircle');
      if (circle) {
        const circumference = 753.98; // 2 * PI * 120
        const progress = (total - rem) / total;
        const offset = circumference * (1 - progress);
        circle.style.strokeDashoffset = offset;
      }

      // Update Break Countdown
      const breakEl = document.getElementById('breakCountdownVal');
      if (breakEl) {
        breakEl.textContent = formatted;
      }
    }

    playSacredChime() {
      if (!this.chimeEnabled) return;
      try {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;

        if (!this.audioCtx) {
          this.audioCtx = new AudioContext();
        }

        const ctx = this.audioCtx;
        if (ctx.state === 'suspended') {
          ctx.resume();
        }

        const now = ctx.currentTime;
        // Warm angelic harmonic chime chord (C5, E5, G5, C6)
        const notes = [523.25, 659.25, 783.99, 1046.50];

        notes.forEach((freq, idx) => {
          const osc = ctx.createOscillator();
          const gain = ctx.createGain();

          osc.type = 'sine';
          osc.frequency.value = freq;

          const start = now + idx * 0.18;
          const dur = 2.4;

          gain.gain.setValueAtTime(0, start);
          gain.gain.linearRampToValueAtTime(0.12, start + 0.05);
          gain.gain.exponentialRampToValueAtTime(0.0001, start + dur);

          osc.connect(gain);
          gain.connect(ctx.destination);

          osc.start(start);
          osc.stop(start + dur);
        });
      } catch (err) {
        // Audio policy or silent failure
      }
    }

    /* =========================================================
       REAL-TIME MALAYSIA/PAPAR CLOCK & DATE
       ========================================================= */
    startClock() {
      const update = () => {
        const now = new Date();

        // 24-hr Time (Asia/Kuching GMT+8)
        const timeOptions = {
          hour12: false,
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          timeZone: 'Asia/Kuching'
        };
        const timeStr = new Intl.DateTimeFormat('en-GB', timeOptions).format(now);

        // Malay Day & Date (e.g. "AHAD, 15 NOV 2026")
        const days = ['AHAD', 'ISNIN', 'SELASA', 'RABU', 'KHAMIS', 'JUMAAT', 'SABTU'];
        const months = ['JAN', 'FEB', 'MAC', 'APR', 'MEI', 'JUN', 'JUL', 'OGOS', 'SEP', 'OKT', 'NOV', 'DIS'];
        const dayName = days[now.getDay()];
        const dateStr = `${dayName}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;

        document.querySelectorAll('.data-clock-time').forEach(el => {
          el.textContent = timeStr;
        });
        document.querySelectorAll('.data-clock-date').forEach(el => {
          el.textContent = dateStr;
        });
      };

      update();
      this.clockInterval = setInterval(update, 1000);
    }

    setupKeybindings() {
      window.addEventListener('keydown', (e) => {
        // Don't intercept if user is typing in an input
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;

        switch (e.key) {
          case '1':
            this.switchScene('landing');
            break;
          case '2':
            this.switchScene('title');
            break;
          case '3':
            this.switchScene('overlay');
            break;
          case '4':
            this.switchScene('break');
            break;
          case '5':
            this.switchScene('ending');
            break;
          case ' ':
            e.preventDefault();
            if (this.state.timer.isRunning) this.pauseTimer();
            else this.startTimer();
            break;
          case 'l':
          case 'L':
            this.setSpeakerLtVisibility(!this.state.speakerLtVisible);
            break;
          case 's':
          case 'S':
            this.setScriptureHudVisibility(!this.state.scriptureVisible);
            break;
          case 'w':
          case 'W':
            this.setWorshipHudVisibility(!this.state.worshipVisible);
            break;
          case 't':
          case 'T':
            this.setTickerVisibility(!this.state.tickerVisible);
            break;
          case 'o':
          case 'O':
            this.setGivingLtVisibility(!this.state.givingLtVisible);
            break;
        }
      });
    }
  }

  window.ChurchCore = ChurchCore;
})(window);
