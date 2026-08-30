// js/flip.js — turn clickable info tiles into 3D flip cards.
// Front keeps the icon/title/summary + trigger; the back face carries the
// design's animated service mock, the offering list and a call to action.
// Click the front to flip; a "↻ back" control at the TOP of the back returns.
// Flipping is user-driven only — nothing flips on its own.
(function () {
  // Per-service decorative mocks, keyed by the tile's data-icon. All shapes are
  // CSS; entry/loop animations are armed by the .flipped class (see flip.css).
  var MOCKS = {
    web:
      '<div class="svc-web-bar"><i></i><i></i><i></i><u></u></div>' +
      '<div class="svc-web-body">' +
        '<div class="svc-col">' +
          '<span class="svc-b1 svc-grow" style="--d:.15s"></span>' +
          '<span class="svc-b2 svc-grow" style="--d:.3s"></span>' +
          '<span class="svc-b2 svc-grow" style="--d:.4s"></span>' +
        '</div>' +
        '<div class="svc-col">' +
          '<span class="svc-b3 svc-grow" style="--d:.2s"></span>' +
          '<div class="svc-row">' +
            '<span class="svc-b4 svc-grow" style="--d:.35s"></span>' +
            '<span class="svc-b4 svc-grow" style="--d:.45s"></span>' +
          '</div>' +
        '</div>' +
      '</div>',

    mobile:
      '<div class="svc-stage svc-phones">' +
        '<div class="svc-phone svc-float" style="--dur:7s;--d:.4s">' +
          '<span class="svc-ph-cap"></span><span class="svc-ph-fill"></span>' +
        '</div>' +
        '<div class="svc-phone svc-phone-main svc-float" style="--dur:6s">' +
          '<span class="svc-ph-cap"></span><span class="svc-ph-hero"></span>' +
          '<div class="svc-ph-grid"><span></span><span></span><span></span><span></span></div>' +
        '</div>' +
        '<div class="svc-phone svc-float" style="--dur:7.5s;--d:.8s">' +
          '<span class="svc-ph-cap"></span><span class="svc-ph-fill"></span>' +
        '</div>' +
      '</div>',

    ai:
      '<div class="svc-stage svc-chat">' +
        '<span class="svc-bub svc-bub-user svc-pop" style="--d:.15s"><i></i></span>' +
        '<span class="svc-bub svc-bub-ai svc-pop" style="--d:.4s"><i></i><i></i></span>' +
        '<span class="svc-typing">' +
          '<i class="svc-blink"></i>' +
          '<i class="svc-blink" style="--d:.2s"></i>' +
          '<i class="svc-blink" style="--d:.4s"></i>' +
        '</span>' +
      '</div>',

    cloud:
      '<div class="svc-stage svc-cloud">' +
        '<span class="svc-pill">CLOUD</span>' +
        '<span class="svc-wire"></span>' +
        '<div class="svc-nodes">' +
          '<span class="svc-node svc-pop" style="--d:.2s"><i></i><i></i></span>' +
          '<span class="svc-node svc-pop" style="--d:.35s"><i></i><i></i></span>' +
          '<span class="svc-node svc-pop" style="--d:.5s"><i></i><i></i></span>' +
        '</div>' +
      '</div>',

    automation:
      '<div class="svc-stage svc-flow">' +
        '<span class="svc-fnode svc-blink" style="--dur:3s"></span>' +
        '<span class="svc-rail"><i class="svc-trace" style="--dur:3s"></i></span>' +
        '<span class="svc-fnode svc-fnode-2 svc-blink" style="--dur:3s;--d:.5s"></span>' +
        '<span class="svc-rail"><i class="svc-trace" style="--dur:3s;--d:.5s"></i></span>' +
        '<span class="svc-fnode svc-fnode-3 svc-blink" style="--dur:3s;--d:1s"></span>' +
        '<span class="svc-rail"><i class="svc-trace" style="--dur:3s;--d:1s"></i></span>' +
        '<span class="svc-fnode svc-fnode-4"></span>' +
      '</div>',

    testing:
      '<div class="svc-stage svc-check">' +
        '<span class="svc-crow svc-pop" style="--d:.15s"><i class="svc-box">&#10003;</i><u style="width:100%"></u></span>' +
        '<span class="svc-crow svc-pop" style="--d:.35s"><i class="svc-box">&#10003;</i><u style="width:82%"></u></span>' +
        '<span class="svc-crow svc-pop" style="--d:.55s"><i class="svc-box">&#10003;</i><u style="width:92%"></u></span>' +
        '<span class="svc-crow svc-crow-idle">' +
          '<i class="svc-box svc-box-empty svc-blink" style="--dur:1.6s"></i><u style="width:60%"></u>' +
        '</span>' +
      '</div>',

    devops:
      '<div class="svc-stage svc-pipe">' +
        '<div class="svc-chips">' +
          '<span class="svc-chip">BUILD</span>' +
          '<span class="svc-chip svc-chip-half">TEST</span>' +
          '<span class="svc-chip svc-chip-out">DEPLOY</span>' +
        '</div>' +
        '<span class="svc-progress"><i></i></span>' +
        '<div class="svc-lines"><span></span><span></span></div>' +
      '</div>',

    integration:
      '<div class="svc-stage svc-api">' +
        '<span class="svc-panel"><i></i><i></i><i></i></span>' +
        '<span class="svc-wires">' +
          '<span class="svc-rail"><i class="svc-trace"></i></span>' +
          '<span class="svc-rail"><i class="svc-trace svc-trace-cream" style="--d:1.2s"></i></span>' +
          '<span class="svc-rail"><i class="svc-trace" style="--d:.6s"></i></span>' +
        '</span>' +
        '<span class="svc-panel svc-panel-b"><i></i><i></i><i></i></span>' +
      '</div>'
  };

  var BACK_ICON =
    '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" ' +
    'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    '<path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>';

  function buildMock(key) {
    if (!MOCKS[key]) return '';
    return '<div class="svc-mock" data-mock="' + key + '" aria-hidden="true">' +
      MOCKS[key] + '<span class="svc-scan"></span></div>';
  }

  document.addEventListener('DOMContentLoaded', function () {
    var tiles = document.querySelectorAll('.card-accent, .industry-tile');

    Array.prototype.forEach.call(tiles, function (tile) {
      var body = tile.querySelector('.accordion-body');
      if (!body) return; // nothing to reveal -> leave tile untouched

      var isService = tile.classList.contains('card-accent');
      var toggle = tile.querySelector('.accordion-toggle');
      var titleEl = tile.querySelector('h3');
      var title = titleEl ? titleEl.textContent.trim() : '';
      var iconEl = tile.querySelector('.card-icon');
      var iconKey = iconEl ? iconEl.getAttribute('data-icon') : '';

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
      if (isService && toggle) {
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

      if (isService) {
        // The design's animated mock for this service, then its offering list.
        back.insertAdjacentHTML('beforeend', buildMock(iconKey));

        var items = Array.prototype.slice.call(body.querySelectorAll('li'));
        if (items.length) {
          var list = document.createElement('ul');
          list.className = 'flip-list' + (items.length > 13 ? ' flip-list-dense' : '');
          items.forEach(function (li, i) {
            var item = document.createElement('li');
            item.textContent = li.textContent.trim();
            // Staggered entry, played on flip-open.
            item.style.setProperty('--d', (0.12 + i * 0.04).toFixed(2) + 's');
            list.appendChild(item);
          });
          back.appendChild(list);
        }
      } else {
        // Industry tiles keep the compact capability-tag treatment.
        var tags = (tile.getAttribute('data-tags') || '').split('|');
        if (tags[0]) {
          var tagRow = document.createElement('div');
          tagRow.className = 'flip-tags';
          tags.forEach(function (label) {
            var tag = document.createElement('span');
            tag.className = 'flip-tag';
            tag.textContent = label.trim();
            tagRow.appendChild(tag);
          });
          back.appendChild(tagRow);
        }
      }

      // Call to action pinned to the bottom of the face.
      var cta = document.createElement('a');
      cta.className = 'flip-back-cta';
      cta.href = 'contact.html';
      cta.innerHTML = (isService ? 'Start a project' : (tile.getAttribute('data-cta') || 'Start a project')) +
        ' <span>&rarr;</span>';
      back.appendChild(cta);

      // The detail list stays in the markup as the no-JS accordion fallback;
      // the upgraded card shows the design's back face instead.
      body.parentNode && body.parentNode.removeChild(body);

      inner.appendChild(front);
      inner.appendChild(back);
      tile.appendChild(inner);
      tile.classList.add('flip');

      // Operable as a single control: pointer, keyboard and screen readers.
      tile.setAttribute('role', 'button');
      tile.setAttribute('tabindex', '0');
      tile.setAttribute('aria-expanded', 'false');
      tile.setAttribute('aria-label', title + ' — flip for details');

      // Stop accordion.js from also acting on this toggle.
      if (toggle) toggle.removeAttribute('data-target');

      function setFlipped(on) {
        tile.classList.toggle('flipped', on);
        tile.setAttribute('aria-expanded', on ? 'true' : 'false');
      }
      function flip() { setFlipped(true); backBtn.focus(); }
      function unflip() { setFlipped(false); }

      front.addEventListener('click', function (e) {
        if (e.target.closest('a')) return; // let real links behave normally
        flip();
      });
      back.addEventListener('click', function (e) {
        if (e.target.closest('a') || e.target.closest('.flip-back-btn')) return;
        unflip();
      });
      backBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        unflip();
      });
      tile.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && tile.classList.contains('flipped')) {
          unflip();
          tile.focus();
          return;
        }
        // Enter/Space toggle the tile, unless focus sits on a real control.
        if (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') return;
        if (e.target !== tile) return;
        e.preventDefault();
        if (tile.classList.contains('flipped')) unflip();
        else flip();
      });
    });

  });
})();
