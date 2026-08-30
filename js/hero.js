// js/hero.js — hero band motion: the orbital system canvas + the stat counters.
// Ported from the Claude Design source (Appmentech Hero.dc.html). Reduced-motion
// visitors get a single static frame and the final stat values.
(function () {
  if (!document.body.classList.contains('index-hero')) return;

  var still = window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function runSolar() {
    var cv = document.querySelector('.hero-solar');
    if (!cv || !cv.getContext) return;
    var ctx = cv.getContext('2d');

    var planets = [
      { r: 0.085, size: 7,  speed: 1.55, col: '#b9a596', hi: '#efe4da' },
      { r: 0.135, size: 10, speed: 1.05, col: '#e8c07a', hi: '#fff3d4' },
      { r: 0.195, size: 13, speed: 0.74, col: '#3f7fd6', hi: '#a8d8ff' },
      { r: 0.265, size: 10, speed: 0.52, col: '#c1502e', hi: '#ff9d72' },
      { r: 0.35,  size: 19, speed: 0.33, col: '#d8a45f', hi: '#ffe3ae', ring: true },
      { r: 0.44,  size: 14, speed: 0.22, col: '#5fc9c2', hi: '#c2fbf6' }
    ];
    var stars = [];
    for (var i = 0; i < 130; i++) {
      stars.push({
        x: Math.random(), y: Math.random(),
        s: Math.random() * 1.3 + 0.25, p: Math.random() * 6.28
      });
    }

    function fit() {
      var dpr = Math.min(2, window.devicePixelRatio || 1);
      var w = cv.clientWidth, h = cv.clientHeight;
      cv.width = w * dpr;
      cv.height = h * dpr;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      return { w: w, h: h };
    }
    var dim = fit();
    var raf;
    window.addEventListener('resize', function () { dim = fit(); }, { passive: true });

    function draw(t) {
      var w = dim.w, h = dim.h;
      if (!w || !h) { raf = requestAnimationFrame(draw); return; }
      var cx = w * 0.12, cy = h * 0.5;
      var R = Math.max(w, h) * 1.9;
      var tilt = 0.34;

      ctx.clearRect(0, 0, w, h);
      ctx.globalCompositeOperation = 'lighter';

      for (var s = 0; s < stars.length; s++) {
        var st = stars[s];
        var tw = 0.35 + 0.65 * Math.abs(Math.sin(t * 0.0007 + st.p));
        ctx.fillStyle = 'rgba(255,246,232,' + (0.5 * tw).toFixed(3) + ')';
        ctx.beginPath();
        ctx.arc(st.x * w, st.y * h, st.s, 0, 6.2832);
        ctx.fill();
      }

      for (var o = 0; o < planets.length; o++) {
        var orbit = R * planets[o].r;
        ctx.save();
        ctx.translate(cx, cy);
        ctx.scale(1, tilt);
        ctx.strokeStyle = 'rgba(255,214,160,0.16)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.arc(0, 0, orbit, 0, 6.2832);
        ctx.stroke();
        ctx.restore();
      }

      var corona = ctx.createRadialGradient(cx, cy, 0, cx, cy, R * 0.26);
      corona.addColorStop(0, 'rgba(255,226,178,0.6)');
      corona.addColorStop(0.05, 'rgba(255,186,92,0.26)');
      corona.addColorStop(0.18, 'rgba(242,140,60,0.06)');
      corona.addColorStop(1, 'rgba(242,140,60,0)');
      ctx.fillStyle = corona;
      ctx.beginPath();
      ctx.arc(cx, cy, R * 0.26, 0, 6.2832);
      ctx.fill();

      var pulse = 1 + 0.03 * Math.sin(t * 0.0006);
      ctx.shadowColor = 'rgba(255,190,110,0.6)';
      ctx.shadowBlur = 18;
      ctx.fillStyle = '#fff3dc';
      ctx.beginPath();
      ctx.arc(cx, cy, 34 * pulse, 0, 6.2832);
      ctx.fill();
      ctx.shadowBlur = 0;

      ctx.globalCompositeOperation = 'source-over';
      for (var p = 0; p < planets.length; p++) {
        var pl = planets[p];
        var a = (still ? 0.6 : t * 0.000045) * pl.speed * 6.2832 + pl.r * 17;
        var rr = R * pl.r;
        var x = cx + Math.cos(a) * rr;
        var y = cy + Math.sin(a) * rr * tilt;
        var front = Math.sin(a) > 0;
        var lit = ctx.createRadialGradient(
          x - Math.cos(a) * pl.size * 0.45, y - Math.sin(a) * pl.size * 0.45,
          pl.size * 0.08, x, y, pl.size
        );
        lit.addColorStop(0, pl.hi);
        lit.addColorStop(0.55, pl.col);
        lit.addColorStop(0.9, pl.col);
        lit.addColorStop(1, 'rgba(14,12,18,0.92)');
        ctx.globalAlpha = front ? 1 : 0.55;
        ctx.shadowColor = pl.col;
        ctx.shadowBlur = front ? 12 : 5;
        ctx.fillStyle = lit;
        ctx.beginPath();
        ctx.arc(x, y, pl.size, 0, 6.2832);
        ctx.fill();
        ctx.shadowBlur = 0;
        if (pl.ring) {
          ctx.save();
          ctx.translate(x, y);
          ctx.rotate(-0.4);
          ctx.scale(1, 0.32);
          ctx.strokeStyle = 'rgba(255,224,178,0.5)';
          ctx.lineWidth = 1.6;
          ctx.beginPath();
          ctx.arc(0, 0, pl.size * 2.1, 0, 6.2832);
          ctx.stroke();
          ctx.restore();
        }
        ctx.globalAlpha = 1;
      }

      if (!still) raf = requestAnimationFrame(draw);
    }
    raf = requestAnimationFrame(draw);
  }

  function runCounters() {
    var root = document.querySelector('.hero-stats');
    if (!root) return;
    var nodes = Array.prototype.slice.call(root.querySelectorAll('[data-count]'));
    if (!nodes.length) return;

    function set(el, v) { el.textContent = v + (el.getAttribute('data-suffix') || ''); }

    if (still || !('IntersectionObserver' in window)) {
      nodes.forEach(function (el) { set(el, el.getAttribute('data-count')); });
      return;
    }

    var SPEED = 1700;
    nodes.forEach(function (el) { set(el, 0); });

    var start;
    function tick(now) {
      if (!start) start = now;
      var t = Math.min(1, (now - start) / SPEED);
      var e = 1 - Math.pow(1 - t, 3);
      nodes.forEach(function (el) {
        set(el, Math.round(Number(el.getAttribute('data-count')) * e));
      });
      if (t < 1) requestAnimationFrame(tick);
    }

    var io = new IntersectionObserver(function (entries, obs) {
      for (var i = 0; i < entries.length; i++) {
        if (entries[i].isIntersecting) {
          obs.disconnect();
          requestAnimationFrame(tick);
          return;
        }
      }
    }, { threshold: 0.4 });
    io.observe(root);
  }

  document.addEventListener('DOMContentLoaded', function () {
    runSolar();
    runCounters();
  });
})();
