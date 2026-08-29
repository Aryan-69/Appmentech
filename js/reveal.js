// js/reveal.js — staggered scroll-reveal for content tiles via IntersectionObserver.
// Uses only opacity + transform (hardware-accelerated) to avoid layout thrashing.
// A scroll/load sweep guarantees no tile can ever stay stuck hidden (e.g. after an
// anchor-jump that IntersectionObserver skips over).
(function () {
  var SELECTOR = '.card, .industry-tile, .stepper-step, .stat-tile';

  // Respect reduced-motion and lack of IO support: leave tiles fully visible.
  var prefersReduced = window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (prefersReduced || !('IntersectionObserver' in window)) {
    return;
  }

  // Marking the root enables the hidden start-state in CSS. Done in JS so that
  // no-JS visitors never see hidden tiles.
  document.documentElement.classList.add('reveal-ready');

  document.addEventListener('DOMContentLoaded', function () {
    var tiles = Array.prototype.slice.call(document.querySelectorAll(SELECTOR));
    if (!tiles.length) return;

    var hasWeakMap = typeof WeakMap === 'function';
    var groupCounters = hasWeakMap ? new WeakMap() : null;

    tiles.forEach(function (tile) {
      tile.classList.add('reveal');
      var i = 0;
      if (groupCounters) {
        var parent = tile.parentElement || document.body;
        i = groupCounters.get(parent) || 0;
        groupCounters.set(parent, i + 1);
      }
      tile.setAttribute('data-reveal-index', i);
    });

    function cleanup(tile) {
      // Once revealed, drop the reveal classes/inline delay so the tile's own
      // spring hover transition takes over cleanly (no specificity clash).
      tile.classList.remove('reveal', 'is-visible');
      tile.style.transitionDelay = '';
      tile.style.willChange = '';
    }

    function show(tile, stagger) {
      if (!tile.classList.contains('reveal') || tile.classList.contains('is-visible')) {
        return;
      }
      observer.unobserve(tile);

      var delay = stagger ? Math.min(
        (parseInt(tile.getAttribute('data-reveal-index'), 10) || 0) * 70, 420
      ) : 0;
      tile.style.transitionDelay = delay + 'ms';

      requestAnimationFrame(function () {
        tile.classList.add('is-visible');
      });

      var done = false;
      var finish = function () {
        if (done) return;
        done = true;
        tile.removeEventListener('transitionend', onEnd);
        cleanup(tile);
      };
      var onEnd = function (e) {
        if (e.propertyName === 'transform' || e.propertyName === 'opacity') finish();
      };
      tile.addEventListener('transitionend', onEnd);
      setTimeout(finish, delay + 800); // safety net if transitionend never fires
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) show(entry.target, true);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    tiles.forEach(function (tile) { observer.observe(tile); });

    // Safety sweep: reveal anything already in or above the viewport. Catches
    // tiles that IntersectionObserver skips during fast/anchor-jump scrolls.
    function sweep() {
      var vh = window.innerHeight || document.documentElement.clientHeight;
      for (var i = 0; i < tiles.length; i++) {
        var tile = tiles[i];
        if (!tile.classList.contains('reveal') || tile.classList.contains('is-visible')) continue;
        var r = tile.getBoundingClientRect();
        if (r.top < vh) {
          // above the viewport -> no animation; in-view -> staggered
          show(tile, r.bottom > 0);
        }
      }
    }

    var ticking = false;
    function onScroll() {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(function () { sweep(); ticking = false; });
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    window.addEventListener('load', sweep);
    window.addEventListener('hashchange', function () { setTimeout(sweep, 400); });
    sweep(); // initial pass for tiles in the first viewport
  });
})();
