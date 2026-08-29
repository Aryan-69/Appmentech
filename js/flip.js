// js/flip.js — turn clickable info tiles into 3D flip cards.
// Front keeps the icon/title/summary + trigger; the accordion body becomes the
// back face (pre-rotated in CSS). Click front to flip, "Back" to return.
(function () {
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

      // Build the back: title, the revealed content, a return button.
      if (title) {
        var h = document.createElement('strong');
        h.className = 'flip-back-title';
        h.textContent = title;
        back.appendChild(h);
      }
      while (body.firstChild) back.appendChild(body.firstChild);
      var backBtn = document.createElement('button');
      backBtn.type = 'button';
      backBtn.className = 'flip-back-btn';
      backBtn.textContent = '← Back';
      back.appendChild(backBtn);
      body.parentNode && body.parentNode.removeChild(body);

      inner.appendChild(front);
      inner.appendChild(back);
      tile.appendChild(inner);
      tile.classList.add('flip');

      // Stop accordion.js from also acting on this toggle.
      if (toggle) {
        toggle.removeAttribute('data-target');
        toggle.setAttribute('aria-hidden', 'false');
      }

      function flip() {
        tile.classList.add('flipped');
        backBtn.focus();
      }
      function unflip() {
        tile.classList.remove('flipped');
      }

      front.addEventListener('click', function (e) {
        // let real links (e.g. an <a> CTA) behave normally
        if (e.target.closest('a')) return;
        flip();
      });
      backBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        unflip();
      });
      // Esc flips back when focus is inside the tile
      tile.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && tile.classList.contains('flipped')) unflip();
      });
    });
  });
})();
