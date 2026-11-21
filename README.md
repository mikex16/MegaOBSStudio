# 🎬 MegaOBS Studio — Unified One-Screen Church Broadcast, HUD & Lyric System

**Dibangunkan secara sukarela oleh [mikex16](https://github.com/mikex16/MegaOBSStudio)** untuk kegunaan gereja supaya pengurusan siaran langsung (**"LIVE"**) dapat dikawal sepenuhnya dalam **satu skrin oleh seorang operator sahaja**.

---

## ✨ Ciri-Ciri Utama (All-in-One Standalone System)

1. **🎛️ Kawalan Jauh OBS Studio (WebSocket v5 + PHP Bridge + HTML5 Canvas)**:
   - Tukar Scene Program/Preview, Studio Mode, Transisi (`CUT`, `AUTO`, `FADE`), dan T-Bar interaktif.
   - Monitor HTML5 Canvas dengan keupayaan *drag & resize* layer OBS secara terus serta pratonton gabungan (*Live Composite HUD + Lyric Overlay*).
   - Audio Mixer dengan meter stereo VU dB masa-nyata, kawalan Stream/Record/VirtualCam/Replay, dan butang **⚡ 1-Click Inject HUD to OBS**.
2. **⛪ Church Broadcast HUD & Smart Lower-Thirds (`hud.php`)**:
   - **5 Scene Siaran**: *Starting Soon (Countdown)*, *Sermon Title*, *Transparent Camera HUD*, *Break & Offering (DuitNow QR)*, dan *Stream Ending (Benediction)*.
   - **4 Lower-Third Pintar (Smart Collision Avoidance)**:
     - 🎤 **Speaker Lower-Third** (`L`)
     - 📜 **Scripture Verse Prompter** (`S`)
     - 💰 **Offering & DuitNow QR Info** (`O`) — lengkap dengan pelaras saiz QR (`70px`–`380px`) & kedudukan bebas di tengah atau mana-mana bahagian skrin (*Middle / Custom X% & Y%*).
     - 📢 **Marquee Info Ticker** (`T`) — lengkap dengan pelaras saiz tulisan (`14px`–`56px`, mesra skrin telefon) dan kelajuan gerakan (*Speed*).
   - **Smart Collision Avoidance**: Apabila lirik Easyslides dipaparkan di bahagian bawah skrin (`Bottom`), semua overlay lower-third akan bergerak naik secara automatik supaya tidak bertindih. Apabila lirik diletakkan di tengah (`Center`) atau atas (`Top`), lower-third kekal di tempat asal.
   - **📋 Preset Studio (`presets_editor.php`)**: Dialog pengurusan preset untuk menambah, menyunting, atau memadam preset *Verse*, *Sermon*, dan *Ticker*.
3. **🎵 28 Pangkalan Data Lirik SQLite & Easyslides 5.2 Multicast Projector (`obs.php`)**:
   - Mengandungi **28 fail pangkalan data SQLite** tempatan (`MasterLyricDB.db` dengan 3,254+ lagu, `JRChord.db`, `KJ.db`, `PKJ.db`, `NKB.db`, `NKI.db`, `BN.db`, `GB.db`, dll.).
   - Sambungan terus TCP Multicast ke **Easyslides 5.2** (Port `9701`) atau tayangan slaid terus dari pelayar web.
   - **🎵 DB Studio (`lyrics_editor.php`)**: Editor lagu penuh, pembuang kord automatik (*Strip Chords / Tanpa Chord*), dan *multi-site scraper*.

---

## 📚 Senarai Sumber & Penghargaan (Sources & Credits)

Projek ini menggabungkan dan menggunakan sumber-sumber serta teknologi berikut:
- **OBS Studio & OBS WebSocket v5 Protocol** ([obsproject.com](https://obsproject.com/)) — Protokol kawalan jauh WebSocket v5.
- **Easyslides 5.2** ([easyslides.com](https://www.easyslides.com/)) — Skema SQLite `items` & protokol Multicast TCP XML/RSA-OAEP (Port `9701`).
- **Sumber Lirik Lagu Rohani & Kidung Jemaat**:
  - [JRChord.com](https://www.jrchord.com/)
  - [LaguRohani.id](https://www.lagurohani.id/)
  - [Alkitab.app](https://alkitab.app/songs) (23 Buku Nyanyian: *KJ, PKJ, NKB, NKI, BN, GB, GBL, JB, KC, KK, KLIK, KPPK, KPRI, KRI, LS, MNR1, MNR2, NNBT, NP, NR, NRM, PPK, PPPR*).
- **Alkitab Terjemahan Baru (TB)** — Petikan ayat-ayat Firman Tuhan.
- **Google Fonts** (*Cinzel, Plus Jakarta Sans, Outfit, Inter, JetBrains Mono*) & **HTML5 Canvas / Web Audio API**.
- **XAMPP / Apache / PHP 8 (`pdo_sqlite`, `curl`, `openssl`, `simplexml`, `mbstring`)**.

---

## 📜 Lesen & Penafian (License & Disclaimer)

- **Tujuan Sukarela**: Aplikasi ini dibangunkan secara sukarela oleh **mikex16** untuk membantu pelayanan media gereja supaya urusan siaran langsung ("LIVE") lebih mudah dan teratur.
- **Syarat Penggunaan (MIT License with Attribution)**: Sesiapa sahaja bebas menggunakan, mengubah suai, dan berkongsi aplikasi ini secara percuma **selagi anda bersetuju untuk TIDAK membuang nama pencipta asal (`mikex16` / `MegaOBSStudio`)** di dalam setiap skrip dan paparan *About Us*.
- **Penafian (Disclaimer — Gunakan Atas Risiko Sendiri)**: Aplikasi ini dihasilkan **seadanya ("AS-IS")** tanpa sebarang jaminan bahawa ia akan berfungsi 100% tanpa ralat. Penggunaan adalah atas risiko anda sendiri.
