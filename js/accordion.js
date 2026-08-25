// js/accordion.js — generic expand/collapse for .accordion-toggle / .accordion-body pairs
(function () {
  document.addEventListener('click', function (e) {
    var toggle = e.target.closest ? e.target.closest('.accordion-toggle') : null;
    if (!toggle) return;

    var targetId = toggle.getAttribute('data-target');
    if (!targetId) return;
    var body = document.getElementById(targetId);
    if (!body) return;

    var isOpen = body.classList.toggle('open');
    toggle.classList.toggle('open', isOpen);

    var moreLabel = toggle.getAttribute('data-label-more') || 'Show more';
    var lessLabel = toggle.getAttribute('data-label-less') || 'Show less';
    var textNode = toggle.querySelector('.accordion-toggle-text');
    if (textNode) {
      textNode.textContent = isOpen ? lessLabel : moreLabel;
    }
  });
})();
