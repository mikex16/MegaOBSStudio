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
$initialTab = $_GET['tab'] ?? 'scripture';
if (!in_array($initialTab, ['scripture', 'sermon', 'ticker'], true)) {
    $initialTab = 'scripture';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Church HUD Presets Studio â€” Verse, Sermon & Ticker Editor</title>
  <style>
    :root {
      --bg-body: #0b111e;
      --bg-panel: #131d31;
      --bg-panel-alt: #1a2742;
      --bg-input: #070b14;
      --border: #263654;
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --gold: #f5c242;
      --primary: #3b82f6;
      --success: #10b981;
      --danger: #ef4444;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: var(--bg-body);
      color: var(--text-main);
      height: 100vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .topbar {
      height: 56px;
      background: var(--bg-panel);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 18px;
      gap: 12px;
      flex-shrink: 0;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 800;
      font-size: 15px;
      color: #fff;
    }

    .cat-tabs {
      display: flex;
      gap: 6px;
      background: var(--bg-input);
      padding: 4px;
      border-radius: 8px;
      border: 1px solid var(--border);
    }

    .cat-tab-btn {
      background: transparent;
      border: 1px solid transparent;
      color: var(--text-muted);
      padding: 6px 14px;
      border-radius: 6px;
      font-size: 12.5px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.15s;
    }
    .cat-tab-btn:hover { color: #fff; background: rgba(255,255,255,0.05); }
    .cat-tab-btn.active {
      background: rgba(245, 194, 66, 0.18);
      border-color: var(--gold);
      color: var(--gold);
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 13px;
      border-radius: 6px;
      font-size: 12.5px;
      font-weight: 600;
      border: 1px solid var(--border);
      cursor: pointer;
      transition: all 0.15s;
      color: var(--text-main);
      background: var(--bg-panel-alt);
      text-decoration: none;
      white-space: nowrap;
    }
    .btn:hover { background: #26385c; }
    .btn-primary { background: var(--primary); border-color: var(--primary); color: #fff; }
    .btn-primary:hover { background: #2563eb; }
    .btn-gold { background: linear-gradient(135deg, #f5c242, #d4af37); border-color: #fde047; color: #070b14; font-weight: 800; }
    .btn-gold:hover { filter: brightness(1.08); }
    .btn-success { background: var(--success); border-color: var(--success); color: #fff; }
    .btn-success:hover { background: #059669; }
    .btn-danger { background: rgba(239, 68, 68, 0.16); border-color: rgba(239, 68, 68, 0.45); color: #fca5a5; }
    .btn-danger:hover { background: var(--danger); color: #fff; }
    .btn-sm { padding: 5px 10px; font-size: 11.5px; }

    .workspace {
      display: flex;
      flex: 1;
      overflow: hidden;
    }

    /* Left Sidebar */
    .sidebar {
      width: 390px;
      background: var(--bg-panel);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
    }

    .sidebar-header {
      padding: 12px;
      border-bottom: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    input[type="text"], input[type="search"], textarea, select {
      background: var(--bg-input);
      color: var(--text-main);
      border: 1px solid var(--border);
      border-radius: 6px;
      padding: 8px 11px;
      font-size: 13px;
      font-family: inherit;
      outline: none;
      width: 100%;
    }
    input:focus, textarea:focus, select:focus {
      border-color: var(--gold);
    }

    .preset-list {
      flex: 1;
      overflow-y: auto;
    }

    .preset-item {
      padding: 11px 14px;
      border-bottom: 1px solid rgba(38, 54, 84, 0.6);
      cursor: pointer;
      transition: background 0.12s;
    }
    .preset-item:hover {
      background: rgba(255, 255, 255, 0.04);
    }
    .preset-item.active {
      background: rgba(245, 194, 66, 0.15);
      border-left: 3px solid var(--gold);
    }

    .preset-item-title {
      font-size: 13.5px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }

    .preset-badge-pill {
      font-size: 10px;
      font-weight: 800;
      padding: 2px 7px;
      border-radius: 4px;
      background: rgba(245, 194, 66, 0.2);
      color: var(--gold);
      border: 1px solid rgba(245, 194, 66, 0.35);
      text-transform: uppercase;
      flex-shrink: 0;
    }

    .preset-item-sub {
      font-size: 12px;
      color: var(--text-muted);
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      line-height: 1.4;
    }

    .sidebar-footer {
      padding: 10px 14px;
      border-top: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 12px;
      color: var(--text-muted);
    }

    /* Main Editor Pane */
    .editor-pane {
      flex: 1;
      display: flex;
      flex-direction: column;
      overflow-y: auto;
      padding: 24px 32px;
      gap: 18px;
    }

    .editor-card {
      background: var(--bg-panel);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 22px;
      max-width: 860px;
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 16px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
    }

    .editor-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid var(--border);
      padding-bottom: 14px;
      gap: 10px;
      flex-wrap: wrap;
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .field label {
      font-size: 11.5px;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    /* Live Preview Card inside Editor */
    .preview-banner {
      background: linear-gradient(135deg, rgba(10, 16, 30, 0.95), rgba(17, 26, 48, 0.92));
      border: 1px solid rgba(245, 194, 66, 0.45);
      border-left: 5px solid var(--gold);
      border-radius: 12px;
      padding: 18px 24px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    #toast {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: var(--success);
      color: #fff;
      padding: 10px 18px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      box-shadow: 0 10px 25px rgba(0,0,0,0.45);
      transform: translateY(80px);
      opacity: 0;
      transition: all 0.25s;
      z-index: 200;
    }
    #toast.show {
      transform: translateY(0);
      opacity: 1;
    }
  </style>
</head>
<body>

  <header class="topbar">
    <div class="brand">
      <span>ðŸ“‹ Church HUD Presets Studio</span>
    </div>

    <div class="cat-tabs">
      <button class="cat-tab-btn" id="tabBtn-scripture" onclick="switchCategory('scripture')">ðŸ“œ Verse (Scripture) Presets</button>
      <button class="cat-tab-btn" id="tabBtn-sermon" onclick="switchCategory('sermon')">ðŸŽ¤ Sermon & Speaker Presets</button>
      <button class="cat-tab-btn" id="tabBtn-ticker" onclick="switchCategory('ticker')">ðŸ“¢ Marquee Ticker Presets</button>
    </div>

    <div style="display:flex; align-items:center; gap:8px;">
      <button class="btn btn-gold" onclick="newPresetForm()">+ New Preset</button>
      <a href="presets_editor.php" target="_blank" class="btn btn-sm" id="openNewTabLink" title="Open Preset Studio in a separate browser tab">â†— Open in New Tab</a>
      <a href="index.php" class="btn btn-sm" id="backConsoleLink">â¬… Back to Console</a>
    </div>
  </header>

  <div class="workspace">
    <!-- Left Sidebar: Preset List -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <input type="search" id="searchInput" placeholder="ðŸ” Search presets in this category..." oninput="renderPresetList()">
      </div>

      <div class="preset-list" id="presetList"></div>

      <div class="sidebar-footer">
        <span id="presetCountLabel">0 presets</span>
        <button class="btn btn-sm" onclick="restoreDefaultPresets()" title="Restore default Natal & PKK presets">â†º Reset Defaults</button>
      </div>
    </aside>

    <!-- Main Editor Area -->
    <main class="editor-pane">
      <div class="editor-card">
        <div class="editor-card-header">
          <div>
            <h2 id="editorHeading" style="font-size:16px; font-weight:800; color:#fff;">Edit Preset</h2>
            <span id="editorSubHeading" style="font-size:12px; color:var(--text-muted);">Changes are saved immediately to local standalone storage.</span>
          </div>
          <div style="display:flex; gap:8px;">
            <button class="btn btn-gold" onclick="pushCurrentPresetLive()" title="Push this preset immediately to the Live OBS Church HUD!">âš¡ Push Live to HUD</button>
            <button class="btn btn-danger" id="btnDeletePreset" onclick="deleteCurrentPreset()">ðŸ—‘ Delete</button>
            <button class="btn btn-success" onclick="saveCurrentPreset()">ðŸ’¾ Save Preset (Ctrl+S)</button>
          </div>
        </div>

        <input type="hidden" id="presetId" value="">

        <!-- FORM 1: SCRIPTURE (VERSE) -->
        <div id="form-scripture" style="display:none; flex-direction:column; gap:14px;">
          <div class="grid-2">
            <div class="field">
              <label>Bible Reference (e.g. Mikha 5:1â€“3) *</label>
              <input type="text" id="scRef" placeholder="e.g. Mikha 5:1â€“3" oninput="updateLivePreview()">
            </div>
            <div class="field">
              <label>Bible Version (e.g. Alkitab TB)</label>
              <input type="text" id="scVersion" value="Alkitab TB" placeholder="e.g. Alkitab TB / AVB" oninput="updateLivePreview()">
            </div>
          </div>
          <div class="field">
            <label>Scripture Verse Text (Ayat Firman Tuhan) *</label>
            <textarea id="scText" rows="5" placeholder="Enter the full Bible verse text..." oninput="updateLivePreview()"></textarea>
          </div>
        </div>

        <!-- FORM 2: SERMON & SPEAKER -->
        <div id="form-sermon" style="display:none; flex-direction:column; gap:14px;">
          <div class="field">
            <label>Sermon Title (Tajuk Khutbah) *</label>
            <input type="text" id="srTitle" placeholder="e.g. Aku Kecil, Aku Berharga" oninput="updateLivePreview()">
          </div>
          <div class="grid-2">
            <div class="field">
              <label>Speaker Name (Pengkhotbah) *</label>
              <input type="text" id="srSpeaker" placeholder="e.g. Pta. Tinggih Sulog" oninput="updateLivePreview()">
            </div>
            <div class="field">
              <label>Scripture Reference (Petikan Firman)</label>
              <input type="text" id="srScripture" placeholder="e.g. Mikha 5:1â€“3" oninput="updateLivePreview()">
            </div>
          </div>
          <div class="grid-2">
            <div class="field">
              <label>Speaker Role / Title</label>
              <input type="text" id="srRole" placeholder="e.g. Pengkhotbah Kebaktian Natal & Graduasi PKK" oninput="updateLivePreview()">
            </div>
            <div class="field">
              <label>Sermon Series / Event Subtitle</label>
              <input type="text" id="srSeries" placeholder="e.g. Sambutan Natal & Graduasi PKK 2026" oninput="updateLivePreview()">
            </div>
          </div>
        </div>

        <!-- FORM 3: MARQUEE TICKER -->
        <div id="form-ticker" style="display:none; flex-direction:column; gap:14px;">
          <div class="grid-2">
            <div class="field">
              <label>Preset Name (For Quick Dropdown List) *</label>
              <input type="text" id="tkTitle" placeholder="e.g. Pengumuman Utama Natal 2026" oninput="updateLivePreview()">
            </div>
            <div class="field">
              <label>Ticker Left Badge Label *</label>
              <input type="text" id="tkBadge" placeholder="e.g. INFO NATAL & PKK" oninput="updateLivePreview()">
            </div>
          </div>
          <div class="field">
            <label>Running Marquee Announcement Text *</label>
            <textarea id="tkText" rows="5" placeholder="Use â€¢ to separate announcement items..." oninput="updateLivePreview()"></textarea>
          </div>
        </div>

        <!-- Live Visual Preview Card -->
        <div class="field" style="margin-top:6px;">
          <label>ðŸ–¥ Lower-Third HUD Preview</label>
          <div class="preview-banner" id="livePreviewBox"></div>
        </div>
      </div>
    </main>
  </div>

  <div id="toast"></div>

  <script>
    let currentCategory = <?= json_encode($initialTab) ?>;
    let allPresets = {
      scripturePresets: [],
      sermonPresets: [],
      tickerPresets: []
    };
    let activePresetId = '';
    const hudChannel = ('BroadcastChannel' in window) ? new BroadcastChannel('sib_church_hud_sync') : null;

    // Hide "Back to Console" or "Open in New Tab" depending on whether we are inside iframe drawer
    if (window.self !== window.top) {
      document.getElementById('backConsoleLink').style.display = 'none';
    } else {
      document.getElementById('openNewTabLink').style.display = 'none';
    }

    function showToast(msg, isError = false) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.style.background = isError ? '#ef4444' : '#10b981';
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 2800);
    }

    function escapeHtml(str) {
      return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function getCategoryKey(cat = currentCategory) {
      if (cat === 'scripture') return 'scripturePresets';
      if (cat === 'sermon') return 'sermonPresets';
      return 'tickerPresets';
    }

    async function loadAllPresets(selectFirst = true) {
      try {
        const res = await fetch('presets_api.php?action=get_presets&_=' + Date.now()).then(r => r.json());
        if (res.ok && res.presets) {
          allPresets = res.presets;
          renderPresetList(selectFirst);
        }
      } catch (e) {
        showToast('Failed to load presets', true);
      }
    }

    function switchCategory(cat) {
      currentCategory = cat;
      document.getElementById('openNewTabLink').href = `presets_editor.php?tab=${cat}`;
      ['scripture', 'sermon', 'ticker'].forEach(c => {
        document.getElementById('tabBtn-' + c).classList.toggle('active', c === cat);
        document.getElementById('form-' + c).style.display = (c === cat) ? 'flex' : 'none';
      });
      renderPresetList(true);
    }

    function renderPresetList(autoSelectFirst = false) {
      const key = getCategoryKey();
      const list = allPresets[key] || [];
      const q = document.getElementById('searchInput').value.trim().toLowerCase();

      const filtered = list.filter(it => {
        const blob = JSON.stringify(it).toLowerCase();
        return q === '' || blob.includes(q);
      });

      document.getElementById('presetCountLabel').textContent = `${filtered.length} preset${filtered.length === 1 ? '' : 's'}`;

      const container = document.getElementById('presetList');
      if (filtered.length === 0) {
        container.innerHTML = `<div style="padding:28px 16px; text-align:center; color:var(--text-muted); font-size:13px;">No presets found. Click "+ New Preset" to add one.</div>`;
        if (autoSelectFirst) newPresetForm();
        return;
      }

      if (autoSelectFirst && (!activePresetId || !filtered.some(x => x.id === activePresetId))) {
        activePresetId = filtered[0].id;
      }

      container.innerHTML = filtered.map(it => {
        const isAct = it.id === activePresetId;
        let title = '', badge = '', sub = '';
        if (currentCategory === 'scripture') {
          title = it.ref || 'Untitled Verse';
          badge = it.version || 'Alkitab TB';
          sub = it.text || '';
        } else if (currentCategory === 'sermon') {
          title = it.title || 'Untitled Sermon';
          badge = it.scripture || 'Firman';
          sub = `${it.speaker || ''} â€¢ ${it.role || ''}`;
        } else {
          title = it.title || it.badge || 'Ticker Preset';
          badge = it.badge || 'INFO';
          sub = it.text || '';
        }

        return `
          <div class="preset-item ${isAct ? 'active' : ''}" onclick="selectPreset('${escapeHtml(it.id)}')">
            <div class="preset-item-title">
              <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(title)}</span>
              <span class="preset-badge-pill">${escapeHtml(badge)}</span>
            </div>
            <div class="preset-item-sub">${escapeHtml(sub)}</div>
          </div>
        `;
      }).join('');

      if (autoSelectFirst && activePresetId) {
        const found = list.find(x => x.id === activePresetId);
        if (found) populateForm(found);
      }
    }

    function selectPreset(id) {
      activePresetId = id;
      const list = allPresets[getCategoryKey()] || [];
      const item = list.find(x => x.id === id);
      if (item) {
        populateForm(item);
        renderPresetList(false);
      }
    }

    function populateForm(it) {
      document.getElementById('presetId').value = it.id || '';
      document.getElementById('btnDeletePreset').style.display = it.id ? 'inline-flex' : 'none';

      if (currentCategory === 'scripture') {
        document.getElementById('editorHeading').textContent = it.id ? `Edit Verse Preset: ${it.ref}` : 'New Verse (Scripture) Preset';
        document.getElementById('scRef').value = it.ref || '';
        document.getElementById('scVersion').value = it.version || 'Alkitab TB';
        document.getElementById('scText').value = it.text || '';
      } else if (currentCategory === 'sermon') {
        document.getElementById('editorHeading').textContent = it.id ? `Edit Sermon Preset: ${it.title}` : 'New Sermon & Speaker Preset';
        document.getElementById('srTitle').value = it.title || '';
        document.getElementById('srSpeaker').value = it.speaker || '';
        document.getElementById('srRole').value = it.role || '';
        document.getElementById('srScripture').value = it.scripture || '';
        document.getElementById('srSeries').value = it.series || '';
      } else {
        document.getElementById('editorHeading').textContent = it.id ? `Edit Ticker Preset: ${it.title || it.badge}` : 'New Marquee Ticker Preset';
        document.getElementById('tkTitle').value = it.title || '';
        document.getElementById('tkBadge').value = it.badge || 'INFO NATAL & PKK';
        document.getElementById('tkText').value = it.text || '';
      }
      updateLivePreview();
    }

    function newPresetForm() {
      activePresetId = '';
      populateForm({ id: '' });
      renderPresetList(false);
      if (currentCategory === 'scripture') document.getElementById('scRef').focus();
      else if (currentCategory === 'sermon') document.getElementById('srTitle').focus();
      else document.getElementById('tkTitle').focus();
    }

    function getFormItemData() {
      const id = document.getElementById('presetId').value.trim();
      if (currentCategory === 'scripture') {
        return {
          id,
          ref: document.getElementById('scRef').value.trim(),
          version: document.getElementById('scVersion').value.trim() || 'Alkitab TB',
          text: document.getElementById('scText').value.trim()
        };
      } else if (currentCategory === 'sermon') {
        return {
          id,
          title: document.getElementById('srTitle').value.trim(),
          speaker: document.getElementById('srSpeaker').value.trim(),
          role: document.getElementById('srRole').value.trim(),
          scripture: document.getElementById('srScripture').value.trim(),
          series: document.getElementById('srSeries').value.trim()
        };
      } else {
        return {
          id,
          title: document.getElementById('tkTitle').value.trim() || document.getElementById('tkBadge').value.trim(),
          badge: document.getElementById('tkBadge').value.trim() || 'INFO GEREJA',
          text: document.getElementById('tkText').value.trim()
        };
      }
    }

    function updateLivePreview() {
      const it = getFormItemData();
      const box = document.getElementById('livePreviewBox');
      if (currentCategory === 'scripture') {
        box.innerHTML = `
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span style="background:var(--gold); color:#070b14; font-weight:800; font-size:11px; padding:3px 10px; border-radius:4px; text-transform:uppercase;">${escapeHtml(it.ref || 'MIKHA 5:1â€“3')}</span>
            <span style="color:var(--gold); font-size:11px; font-weight:700;">${escapeHtml(it.version || 'ALKITAB TB')}</span>
          </div>
          <div style="font-size:15px; font-style:italic; line-height:1.5; color:#fff;">"${escapeHtml(it.text || 'Verse text preview will appear here...')}"</div>
        `;
      } else if (currentCategory === 'sermon') {
        box.innerHTML = `
          <div style="font-size:11px; color:var(--gold); font-weight:800; letter-spacing:1px; text-transform:uppercase; margin-bottom:4px;">${escapeHtml(it.series || 'SERMON SERIES')} â€¢ ${escapeHtml(it.scripture || 'SCRIPTURE')}</div>
          <div style="font-size:19px; font-weight:800; color:#fff; margin-bottom:6px;">"${escapeHtml(it.title || 'Sermon Title')}"</div>
          <div style="font-size:13px; color:#cbd5e1;"><strong>${escapeHtml(it.speaker || 'Speaker Name')}</strong> â€” <span style="color:var(--gold);">${escapeHtml(it.role || 'Role')}</span></div>
        `;
      } else {
        box.innerHTML = `
          <div style="display:flex; align-items:center; gap:12px; background:#060a14; border:1px solid rgba(245,194,66,0.35); border-radius:6px; overflow:hidden; height:36px;">
            <span style="background:var(--gold); color:#070b14; font-weight:900; font-size:11px; padding:0 14px; height:100%; display:flex; align-items:center; white-space:nowrap;">${escapeHtml(it.badge || 'INFO')}</span>
            <span style="font-size:13px; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${escapeHtml(it.text || 'Running marquee announcement text...')}</span>
          </div>
        `;
      }
    }

    function notifyParentPresetsChanged() {
      if (hudChannel) hudChannel.postMessage({ action: 'PRESETS_UPDATED' });
      try {
        if (window.parent && window.parent !== window && typeof window.parent.initHudPresets === 'function') {
          window.parent.initHudPresets();
        }
      } catch (e) {}
    }

    async function saveCurrentPreset() {
      const item = getFormItemData();
      const res = await fetch('presets_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'save_preset',
          category: currentCategory,
          item
        })
      }).then(r => r.json());

      if (!res.ok) {
        showToast(res.error || 'Failed to save preset', true);
        return;
      }

      allPresets = res.presets;
      activePresetId = res.item.id;
      populateForm(res.item);
      renderPresetList(false);
      notifyParentPresetsChanged();
      showToast(res.message || 'Preset saved!');
    }

    async function deleteCurrentPreset() {
      const id = document.getElementById('presetId').value;
      if (!id) return;
      if (!confirm('Delete this preset?')) return;

      const res = await fetch('presets_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'delete_preset',
          category: currentCategory,
          id
        })
      }).then(r => r.json());

      if (res.ok) {
        allPresets = res.presets;
        activePresetId = '';
        renderPresetList(true);
        notifyParentPresetsChanged();
        showToast('Preset deleted.');
      } else {
        showToast(res.error || 'Delete failed', true);
      }
    }

    async function restoreDefaultPresets() {
      if (!confirm('Restore default Verse, Sermon, and Ticker presets?')) return;
      const res = await fetch('presets_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'reset_defaults' })
      }).then(r => r.json());

      if (res.ok) {
        allPresets = res.presets;
        activePresetId = '';
        renderPresetList(true);
        notifyParentPresetsChanged();
        showToast('Default presets restored.');
      }
    }

    async function pushCurrentPresetLive() {
      const it = getFormItemData();
      let msg = null;
      if (currentCategory === 'scripture') {
        msg = { action: 'PUSH_SCRIPTURE', ref: it.ref, version: it.version, text: it.text };
      } else if (currentCategory === 'sermon') {
        await fetch('sync.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'UPDATE_BULK',
            payload: {
              sermonTitle: it.title,
              speakerName: it.speaker,
              speakerRole: it.role,
              sermonScripture: it.scripture,
              sermonSeries: it.series
            }
          })
        });
        msg = { action: 'TOGGLE_SPEAKER_LT', visible: true };
      } else {
        await fetch('sync.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'UPDATE_BULK',
            payload: {
              tickerBadge: it.badge,
              tickerText: it.text
            }
          })
        });
        msg = { action: 'TOGGLE_TICKER', visible: true };
      }

      if (msg) {
        if (hudChannel) hudChannel.postMessage(msg);
        await fetch('sync.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(msg)
        });
        showToast('Pushed Live to OBS Church HUD!');
      }
    }

    window.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        saveCurrentPreset();
      }
    });

    switchCategory(currentCategory);
    loadAllPresets(true);
  </script>
</body>
</html>
