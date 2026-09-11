// js/hero.js — hero band motion: the orbital diagram canvas + the stat counters.
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

    // One family of blues so the diagram reads as a single system. 'col' is the
    // body, 'hi' the lit edge — both light, because nothing here glows any more.
    var planets = [
      { r: 0.085, size: 7,  speed: 1.55, col: '#7DD3FC', hi: '#E0F2FE' },
      { r: 0.135, size: 10, speed: 1.05, col: '#38BDF8', hi: '#BAE6FD' },
      { r: 0.195, size: 13, speed: 0.74, col: '#0EA5E9', hi: '#7DD3FC' },
      { r: 0.265, size: 10, speed: 0.52, col: '#22D3EE', hi: '#A5F3FC' },
      { r: 0.35,  size: 19, speed: 0.33, col: '#0284C7', hi: '#7DD3FC', ring: true },
      { r: 0.44,  size: 14, speed: 0.22, col: '#0369A1', hi: '#38BDF8' }
    ];

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
    window.addEventListener('resize', function () {
      dim = fit();
      // Assigning canvas.width inside fit() clears the canvas. When the visitor
      // prefers reduced motion nothing re-requests a frame, so without this one
      // repaint the hero artwork disappears for good on the first resize —
      // including the implicit resize a phone does when its address bar hides.
      if (still) draw(0);
    }, { passive: true });

    function draw(t) {
      var w = dim.w, h = dim.h;
      if (!w || !h) { raf = requestAnimationFrame(draw); return; }
      var cx = w * 0.12, cy = h * 0.5;
      var R = Math.max(w, h) * 1.9;
      var tilt = 0.34;

      // Everything below paints normally. The dark-theme version used additive
      // ('lighter') blending for the stars, orbit rings and corona, which is
      // invisible on an off-white ground — adding light to near-white is a
      // no-op. So the star field is gone and the glow is now a soft blue wash.
      ctx.clearRect(0, 0, w, h);
      ctx.globalCompositeOperation = 'source-over';

      // Orbit rings: faint blue hairlines, like a drawn diagram.
      for (var o = 0; o < planets.length; o++) {
        var orbit = R * planets[o].r;
        ctx.save();
        ctx.translate(cx, cy);
        ctx.scale(1, tilt);
        ctx.strokeStyle = 'rgba(12,74,110,0.16)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.arc(0, 0, orbit, 0, 6.2832);
        ctx.stroke();
        ctx.restore();
      }

      // The centre: a soft wash instead of a corona, fading to fully
      // transparent so it never banes against the band colour.
      var halo = ctx.createRadialGradient(cx, cy, 0, cx, cy, R * 0.26);
      halo.addColorStop(0, 'rgba(56,189,248,0.34)');
      halo.addColorStop(0.08, 'rgba(56,189,248,0.20)');
      halo.addColorStop(0.4, 'rgba(56,189,248,0.06)');
      halo.addColorStop(1, 'rgba(56,189,248,0)');
      ctx.fillStyle = halo;
      ctx.beginPath();
      ctx.arc(cx, cy, R * 0.26, 0, 6.2832);
      ctx.fill();

      var pulse = 1 + 0.03 * Math.sin(t * 0.0006);
      ctx.fillStyle = '#0EA5E9';
      ctx.beginPath();
      ctx.arc(cx, cy, 34 * pulse, 0, 6.2832);
      ctx.fill();

      for (var p = 0; p < planets.length; p++) {
        var pl = planets[p];
        var a = (still ? 0.6 : t * 0.000045) * pl.speed * 6.2832 + pl.r * 17;
        var rr = R * pl.r;
        var x = cx + Math.cos(a) * rr;
        var y = cy + Math.sin(a) * rr * tilt;
        var front = Math.sin(a) > 0;
        // Lit edge towards the centre, body colour elsewhere. There is no dark
        // terminator: a near-black rim reads as dirt on an off-white ground.
        var lit = ctx.createRadialGradient(
          x - Math.cos(a) * pl.size * 0.45, y - Math.sin(a) * pl.size * 0.45,
          pl.size * 0.08, x, y, pl.size
        );
        lit.addColorStop(0, pl.hi);
        lit.addColorStop(0.55, pl.col);
        lit.addColorStop(1, pl.col);
        // Bodies on the far side of the orbit sit back rather than dim to grey.
        ctx.globalAlpha = front ? 1 : 0.4;
        ctx.fillStyle = lit;
        ctx.beginPath();
        ctx.arc(x, y, pl.size, 0, 6.2832);
        ctx.fill();
        if (pl.ring) {
          ctx.save();
          ctx.translate(x, y);
          ctx.rotate(-0.4);
          ctx.scale(1, 0.32);
          ctx.strokeStyle = 'rgba(3,105,161,0.55)';
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
