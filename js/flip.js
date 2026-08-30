// js/flip.js — turn clickable info tiles into 3D flip cards.
// Front keeps the icon/title/summary + trigger; the accordion body becomes the
// back face (pre-rotated in CSS). Click front to flip; a "↻ back" control at the
// TOP of the back returns. Flipping is user-driven only — nothing flips on its own.
(function () {
  var BACK_ICON =
    '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" ' +
    'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    '<path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>';

  document.addEventListener('DOMContentLoaded', function () {
    var tiles = document.querySelectorAll('.card-accent, .industry-tile');

    Array.prototype.forEach.call(tiles, function (tile) {
      var body = tile.querySelector('.accordion-body');
      if (!body) return; // nothing to reveal -> leave tile untouched

      var toggle = tile.querySelector('.accordion-toggle');
      var titleEl = tile.querySelector('h3');
      var title = titleEl ? titleEl.textContent.trim() : '';

      var inner = document.createElement('div');
      inner.className = 'flip-inner';
      var front = document.createElement('div');
      front.className = 'flip-face flip-front';
      var back = document.createElement('div');
      back.className = 'flip-face flip-back';

      // Move everything except the accordion body onto the front face.
      var kids = Array.prototype.slice.call(tile.children);
      kids.forEach(function (ch) {
        if (ch !== body) front.appendChild(ch);
      });

      // Solutions cards: pin the explore link to the bottom with a flip hint
      // beside it, matching the design's front-face footer row.
      if (tile.classList.contains('card-accent') && toggle) {
        var foot = document.createElement('div');
        foot.className = 'flip-foot';
        toggle.parentNode.insertBefore(foot, toggle);
        foot.appendChild(toggle);
        var hint = document.createElement('span');
        hint.className = 'flip-hint';
        hint.setAttribute('aria-hidden', 'true');
        hint.innerHTML = 'FLIP <span>⟳</span>';
        foot.appendChild(hint);
      }

      // Back header: title (left) + return control (top-right).
      var head = document.createElement('div');
      head.className = 'flip-back-head';
      var h = document.createElement('strong');
      h.className = 'flip-back-title';
      h.textContent = title;
      var backBtn = document.createElement('button');
      backBtn.type = 'button';
      backBtn.className = 'flip-back-btn';
      backBtn.innerHTML = BACK_ICON + '<span>back</span>';
      backBtn.setAttribute('aria-label', 'Flip card back');
      head.appendChild(h);
      head.appendChild(backBtn);
      back.appendChild(head);

      // Then the revealed content.
      while (body.firstChild) back.appendChild(body.firstChild);
      body.parentNode && body.parentNode.removeChild(body);

      // Design's back face closes on a call to action pinned to the bottom.
      var cta = document.createElement('a');
      cta.className = 'flip-back-cta';
      cta.href = 'contact.html';
      cta.innerHTML = (tile.classList.contains('card-accent')
        ? 'Start a project'
        : 'Tell us what you need') + ' <span>&rarr;</span>';
      back.appendChild(cta);

      inner.appendChild(front);
      inner.appendChild(back);
      tile.appendChild(inner);
      tile.classList.add('flip');

      // Stop accordion.js from also acting on this toggle.
      if (toggle) toggle.removeAttribute('data-target');

      function flip() { tile.classList.add('flipped'); backBtn.focus(); }
      function unflip() { tile.classList.remove('flipped'); }

      front.addEventListener('click', function (e) {
        if (e.target.closest('a')) return; // let real links behave normally
        flip();
      });
      backBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        unflip();
      });
      tile.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && tile.classList.contains('flipped')) unflip();
      });
    });

  });
})();
