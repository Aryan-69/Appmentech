// js/hero.js — collapse the transparent hero nav into a compact solid bar on scroll.
(function () {
  if (!document.body.classList.contains('index-hero')) return;
  var nav = document.querySelector('.nav');
  if (!nav) return;

  var THRESHOLD = 40;
  var ticking = false;

  function update() {
    if (window.scrollY > THRESHOLD) {
      nav.classList.add('nav-scrolled');
    } else {
      nav.classList.remove('nav-scrolled');
    }
    ticking = false;
  }
  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(update);
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  update(); // set initial state (e.g. if reloaded mid-page)
})();
