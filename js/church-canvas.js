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
 * SIB Pekan Papar - 60FPS Sacred Broadcast Canvas Engine
 * Renders:
 * 1. Volumetric God Rays (Divine light streaming from above with harmonic sway)
 * 2. Floating Holy Bokeh & Golden Embers (Soft drifting particles with depth of field)
 * 3. Radiant Sacred Halo / Concentric Rings of Light
 * 4. Ambient Energy Waves at the bottom
 */

(function (window) {
  'use strict';

  class ChurchCanvasEngine {
    constructor() {
      this.bgCanvas = document.getElementById('church-bg-canvas');
      this.fxCanvas = document.getElementById('church-fx-canvas');

      this.bgCtx = this.bgCanvas ? this.bgCanvas.getContext('2d') : null;
      this.fxCtx = this.fxCanvas ? this.fxCanvas.getContext('2d') : null;

      this.width = 1920;
      this.height = 1080;

      this.theme = {
        primaryGold: '#f5c242',
        primaryGoldRgb: [245, 194, 66],
        goldLight: '#ffea9f',
        accentCyan: '#38bdf8'
      };

      // Particle simulation
      this.particles = [];
      this.numParticles = 90;

      // God rays simulation
      this.rays = [];
      this.numRays = 8;
      this.rayTime = 0;

      // Concentric halo rings
      this.haloAngle = 0;
      this.waveTime = 0;

      this.isRunning = false;
      this.lastTimestamp = 0;

      this.init();
    }

    init() {
      this.updateThemeColors();
      this.resize();
      this.initParticles();
      this.initRays();
      this.start();

      window.addEventListener('resize', () => this.resize());
    }

    updateThemeColors() {
      const computed = getComputedStyle(document.body);
      const pri = computed.getPropertyValue('--primary-gold').trim() || '#f5c242';
      const priRgbStr = computed.getPropertyValue('--primary-gold-rgb').trim() || '245, 194, 66';
      const goldLt = computed.getPropertyValue('--gold-light').trim() || '#ffea9f';
      const accent = computed.getPropertyValue('--accent-cyan').trim() || '#38bdf8';

      this.theme.primaryGold = pri;
      this.theme.goldLight = goldLt;
      this.theme.accentCyan = accent;
      this.theme.primaryGoldRgb = priRgbStr.split(',').map(n => parseInt(n.trim(), 10) || 0);
    }

    resize() {
      this.width = 1920;
      this.height = 1080;

      if (this.bgCanvas) {
        this.bgCanvas.width = this.width;
        this.bgCanvas.height = this.height;
      }
      if (this.fxCanvas) {
        this.fxCanvas.width = this.width;
        this.fxCanvas.height = this.height;
      }
    }

    initParticles() {
      this.particles = [];
      for (let i = 0; i < this.numParticles; i++) {
        this.particles.push({
          x: Math.random() * this.width,
          y: Math.random() * this.height,
          vx: (Math.random() - 0.5) * 0.45,
          vy: -(Math.random() * 0.55 + 0.25), // Drifts gently upwards
          radius: Math.random() * 2.8 + 0.8,
          baseAlpha: Math.random() * 0.5 + 0.2,
          pulseSpeed: Math.random() * 0.02 + 0.008,
          phase: Math.random() * Math.PI * 2,
          layer: Math.floor(Math.random() * 3) // 0: background blur, 1: mid, 2: crisp sharp
        });
      }
    }

    initRays() {
      this.rays = [];
      for (let i = 0; i < this.numRays; i++) {
        this.rays.push({
          angle: -0.25 + (i / this.numRays) * 0.5, // Sweep range
          width: Math.random() * 120 + 80,
          speed: Math.random() * 0.0006 + 0.0003,
          phase: Math.random() * Math.PI * 2,
          baseAlpha: Math.random() * 0.08 + 0.04
        });
      }
    }

    start() {
      if (this.isRunning) return;
      this.isRunning = true;
      this.lastTimestamp = performance.now();
      requestAnimationFrame((t) => this.loop(t));
    }

    stop() {
      this.isRunning = false;
    }

    loop(timestamp) {
      if (!this.isRunning) return;
      const dt = Math.min((timestamp - this.lastTimestamp) / 1000, 0.1);
      this.lastTimestamp = timestamp;

      this.update(dt);
      this.render();

      requestAnimationFrame((t) => this.loop(t));
    }

    update(dt) {
      this.rayTime += dt;
      this.haloAngle += dt * 0.12;
      this.waveTime += dt * 1.5;

      // Update Holy Bokeh & Embers
      for (let p of this.particles) {
        p.x += p.vx + Math.sin(p.phase) * 0.25;
        p.y += p.vy;
        p.phase += p.pulseSpeed;

        // Wrap around smoothly
        if (p.y < -10) {
          p.y = this.height + 10;
          p.x = Math.random() * this.width;
        }
        if (p.x < 0) p.x = this.width;
        if (p.x > this.width) p.x = 0;
      }
    }

    render() {
      if (this.bgCtx) {
        this.renderBackground(this.bgCtx);
      }
      if (this.fxCtx) {
        this.renderForeground(this.fxCtx);
      }
    }

    renderBackground(ctx) {
      ctx.clearRect(0, 0, this.width, this.height);

      // Do not render heavy background if transparent-bg mode is active
      if (document.body.classList.contains('transparent-bg') ||
          document.documentElement.classList.contains('transparent-bg') ||
          document.body.classList.contains('scene-is-overlay') ||
          document.documentElement.classList.contains('scene-is-overlay')) {
        return;
      }

      const [r, g, b] = this.theme.primaryGoldRgb;

      // 1. Draw Volumetric God Rays from Above
      this.drawGodRays(ctx, r, g, b);

      // 2. Draw Star of Bethlehem (Bintang Betlehem)
      this.drawBethlehemStar(ctx, r, g, b);

      // 3. Draw Sacred Concentric Halo Rings behind content
      this.drawSacredHalo(ctx, r, g, b);

      // 4. Draw Floating Holy Bokeh Particles
      this.drawBokehParticles(ctx, r, g, b);

      // 5. Draw Subtle Harmonic Grace Waves at bottom
      this.drawGraceWaves(ctx, r, g, b);
    }

    drawGodRays(ctx, r, g, b) {
      ctx.save();
      const originX = this.width * 0.5;
      const originY = -60;

      for (let i = 0; i < this.rays.length; i++) {
        const ray = this.rays[i];
        const currentAngle = ray.angle + Math.sin(this.rayTime * ray.speed * 1000 + ray.phase) * 0.08;
        const alpha = ray.baseAlpha + Math.sin(this.rayTime * 1.2 + ray.phase) * 0.03;

        const rayLength = 1300;
        const endX = originX + Math.sin(currentAngle) * rayLength;
        const endY = originY + Math.cos(currentAngle) * rayLength;

        const grad = ctx.createRadialGradient(originX, originY, 40, endX, endY, rayLength);
        grad.addColorStop(0, `rgba(${r}, ${g}, ${b}, ${alpha * 1.8})`);
        grad.addColorStop(0.35, `rgba(${r}, ${g}, ${b}, ${alpha})`);
        grad.addColorStop(1, 'rgba(0, 0, 0, 0)');

        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.moveTo(originX - 10, originY);
        ctx.lineTo(endX - ray.width * 0.5, endY);
        ctx.lineTo(endX + ray.width * 0.5, endY);
        ctx.lineTo(originX + 10, originY);
        ctx.closePath();
        ctx.fill();
      }
      ctx.restore();
    }

    drawBethlehemStar(ctx, r, g, b) {
      ctx.save();
      const sx = this.width * 0.88;
      const sy = 120;
      const pulse = Math.sin(this.rayTime * 2.2) * 0.15 + 0.85;

      // Outer glow
      const glowGrad = ctx.createRadialGradient(sx, sy, 5, sx, sy, 140 * pulse);
      glowGrad.addColorStop(0, 'rgba(255, 255, 255, 0.85)');
      glowGrad.addColorStop(0.25, `rgba(${r}, ${g}, ${b}, ${0.45 * pulse})`);
      glowGrad.addColorStop(0.65, `rgba(${r}, ${g}, ${b}, ${0.12 * pulse})`);
      glowGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
      ctx.fillStyle = glowGrad;
      ctx.beginPath();
      ctx.arc(sx, sy, 140 * pulse, 0, Math.PI * 2);
      ctx.fill();

      // Long vertical flare
      const vertLen = 150 * pulse;
      const flareGradV = ctx.createLinearGradient(sx, sy - vertLen, sx, sy + vertLen);
      flareGradV.addColorStop(0, 'rgba(255, 255, 255, 0)');
      flareGradV.addColorStop(0.5, 'rgba(255, 255, 255, 0.95)');
      flareGradV.addColorStop(1, 'rgba(255, 255, 255, 0)');
      ctx.strokeStyle = flareGradV;
      ctx.lineWidth = 3;
      ctx.beginPath();
      ctx.moveTo(sx, sy - vertLen);
      ctx.lineTo(sx, sy + vertLen);
      ctx.stroke();

      // Long horizontal flare
      const horizLen = 130 * pulse;
      const flareGradH = ctx.createLinearGradient(sx - horizLen, sy, sx + horizLen, sy);
      flareGradH.addColorStop(0, 'rgba(255, 255, 255, 0)');
      flareGradH.addColorStop(0.5, 'rgba(255, 255, 255, 0.95)');
      flareGradH.addColorStop(1, 'rgba(255, 255, 255, 0)');
      ctx.strokeStyle = flareGradH;
      ctx.lineWidth = 3;
      ctx.beginPath();
      ctx.moveTo(sx - horizLen, sy);
      ctx.lineTo(sx + horizLen, sy);
      ctx.stroke();

      // Diagonal flares
      const diagLen = 70 * pulse;
      ctx.strokeStyle = `rgba(255, 255, 255, ${0.7 * pulse})`;
      ctx.lineWidth = 1.5;
      ctx.beginPath();
      ctx.moveTo(sx - diagLen * 0.7, sy - diagLen * 0.7);
      ctx.lineTo(sx + diagLen * 0.7, sy + diagLen * 0.7);
      ctx.moveTo(sx - diagLen * 0.7, sy + diagLen * 0.7);
      ctx.lineTo(sx + diagLen * 0.7, sy - diagLen * 0.7);
      ctx.stroke();

      // Center bright core
      ctx.fillStyle = '#ffffff';
      ctx.beginPath();
      ctx.arc(sx, sy, 5 * pulse, 0, Math.PI * 2);
      ctx.fill();

      ctx.restore();
    }

    drawSacredHalo(ctx, r, g, b) {
      ctx.save();
      const cx = this.width * 0.5;
      const cy = this.height * 0.44;

      // Outer gentle pulsing halo
      const pulse = Math.sin(this.rayTime * 1.5) * 15;
      const radGrad = ctx.createRadialGradient(cx, cy, 50, cx, cy, 380 + pulse);
      radGrad.addColorStop(0, `rgba(${r}, ${g}, ${b}, 0.12)`);
      radGrad.addColorStop(0.5, `rgba(${r}, ${g}, ${b}, 0.04)`);
      radGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');

      ctx.fillStyle = radGrad;
      ctx.beginPath();
      ctx.arc(cx, cy, 400 + pulse, 0, Math.PI * 2);
      ctx.fill();

      // Rotating dashed golden ring 1
      ctx.translate(cx, cy);
      ctx.rotate(this.haloAngle);
      ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, 0.14)`;
      ctx.lineWidth = 1.5;
      ctx.setLineDash([24, 16, 8, 16]);
      ctx.beginPath();
      ctx.arc(0, 0, 310, 0, Math.PI * 2);
      ctx.stroke();

      // Rotating dashed golden ring 2 (counter-clockwise)
      ctx.rotate(-this.haloAngle * 1.8);
      ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, 0.08)`;
      ctx.lineWidth = 1;
      ctx.setLineDash([40, 24]);
      ctx.beginPath();
      ctx.arc(0, 0, 230, 0, Math.PI * 2);
      ctx.stroke();

      ctx.restore();
    }

    drawBokehParticles(ctx, r, g, b) {
      ctx.save();
      for (let p of this.particles) {
        const pAlpha = Math.max(0.05, p.baseAlpha + Math.sin(p.phase) * 0.2);

        ctx.fillStyle = `rgba(${r}, ${g}, ${b}, ${pAlpha})`;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
        ctx.fill();

        // Extra holy radiance on foreground particles
        if (p.radius > 2.2) {
          ctx.shadowColor = this.theme.primaryGold;
          ctx.shadowBlur = 10;
          ctx.fill();
          ctx.shadowBlur = 0;
        }
      }
      ctx.restore();
    }

    drawGraceWaves(ctx, r, g, b) {
      ctx.save();
      const baseY = this.height - 40;
      ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, 0.15)`;
      ctx.lineWidth = 1.5;

      ctx.beginPath();
      for (let x = 0; x <= this.width; x += 20) {
        const y = baseY + Math.sin(x * 0.003 + this.waveTime) * 12 + Math.cos(x * 0.007 - this.waveTime * 0.6) * 6;
        if (x === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
      }
      ctx.stroke();

      // Second harmonic wave
      ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, 0.08)`;
      ctx.beginPath();
      for (let x = 0; x <= this.width; x += 20) {
        const y = baseY - 8 + Math.cos(x * 0.004 - this.waveTime * 0.8) * 14;
        if (x === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
      }
      ctx.stroke();
      ctx.restore();
    }

    renderForeground(ctx) {
      ctx.clearRect(0, 0, this.width, this.height);
      // Optional high-frequency sparkling accents or lens flairs during scene transitions
    }
  }

  window.ChurchCanvasEngine = ChurchCanvasEngine;
})(window);
