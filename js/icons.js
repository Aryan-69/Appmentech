// js/icons.js — inline SVG line-icon registry, no external requests.
(function () {
  var S = 'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"';

  window.ICONS = {
    web: '<svg ' + S + '><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 4 6 4 9s-1.5 6.3-4 9c-2.5-2.7-4-6-4-9s1.5-6.3 4-9z"/></svg>',
    mobile: '<svg ' + S + '><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg>',
    ai: '<svg ' + S + '><rect x="4" y="8" width="16" height="11" rx="2"/><path d="M9 8V5a3 3 0 0 1 6 0v3M9 13h.01M15 13h.01"/></svg>',
    cloud: '<svg ' + S + '><path d="M7 18a4 4 0 0 1-.5-7.97A5.5 5.5 0 0 1 17 9a4.5 4.5 0 0 1-1 9H7z"/></svg>',
    customized: '<svg ' + S + '><path d="M4 21v-6M4 11V3M12 21v-9M12 8V3M20 21v-4M20 13V3M2 15h4M10 8h4M18 17h4"/></svg>',
    scalable: '<svg ' + S + '><path d="M3 21h18M6 21V10l6-5 6 5v11M6 21h12"/></svg>',
    secure: '<svg ' + S + '><path d="M12 3l8 3.5V11c0 5-3.4 8.5-8 10-4.6-1.5-8-5-8-10V6.5L12 3z"/><path d="M9 12l2 2 4-4"/></svg>',
    'future-ready': '<svg ' + S + '><path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/></svg>',
    automation: '<svg ' + S + '><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/></svg>',
    testing: '<svg ' + S + '><path d="M9 3h6M10 3v5.5L5.5 17a2 2 0 0 0 1.8 3h9.4a2 2 0 0 0 1.8-3L14 8.5V3"/></svg>',
    devops: '<svg ' + S + '><path d="M17 2l4 4-4 4M21 6H9a4 4 0 0 0-4 4v1M7 22l-4-4 4-4M3 18h12a4 4 0 0 0 4-4v-1"/></svg>',
    integration: '<svg ' + S + '><circle cx="6" cy="6" r="3"/><circle cx="18" cy="18" r="3"/><path d="M8.5 8.5l7 7M9 6h6a3 3 0 0 1 3 3v0"/></svg>',
    ecommerce: '<svg ' + S + '><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/><path d="M3 4h2l2.4 11.5a2 2 0 0 0 2 1.5h7.2a2 2 0 0 0 2-1.6L20 8H6"/></svg>',
    healthcare: '<svg ' + S + '><path d="M12 21s-7-4.5-9.5-9A5.5 5.5 0 0 1 12 6a5.5 5.5 0 0 1 9.5 6c-2.5 4.5-9.5 9-9.5 9z"/><path d="M9 11h6M12 8v6"/></svg>',
    education: '<svg ' + S + '><path d="M2 9l10-5 10 5-10 5-10-5z"/><path d="M6 11v5c0 1.5 3 3 6 3s6-1.5 6-3v-5"/></svg>',
    media: '<svg ' + S + '><rect x="2" y="4" width="20" height="14" rx="2"/><path d="M10 9l6 3-6 3V9z"/></svg>',
    transport: '<svg ' + S + '><path d="M3 13l2-6h10l2 6"/><rect x="3" y="13" width="18" height="5" rx="1"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="16.5" cy="18.5" r="1.5"/></svg>',
    social: '<svg ' + S + '><circle cx="8" cy="8" r="3"/><circle cx="17" cy="6" r="2.5"/><path d="M2 20c0-3.3 2.7-6 6-6s6 2.7 6 6M14 20c0-2.5 1.8-5.5 5-5.5s5 2 5 5.5"/></svg>',
    enterprise: '<svg ' + S + '><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/></svg>',
    travel: '<svg ' + S + '><path d="M2 12l20-8-8 20-2-8-8-2z"/></svg>',
    realestate: '<svg ' + S + '><path d="M4 11l8-7 8 7"/><path d="M6 10v10h12V10"/><path d="M10 20v-6h4v6"/></svg>',
    food: '<svg ' + S + '><path d="M6 2v8a2 2 0 0 0 2 2v10M6 2v10M9 2v10M18 2c-2 0-3 2-3 5s1 4 3 4v11"/></svg>',
    government: '<svg ' + S + '><path d="M3 10l9-6 9 6M4 10v9M20 10v9M2 21h20M8 10v9M16 10v9"/></svg>',
    more: '<svg ' + S + '><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>',
    check: '<svg ' + S + '><path d="M20 6L9 17l-5-5"/></svg>',
    'arrow-right': '<svg ' + S + '><path d="M5 12h14M13 6l6 6-6 6"/></svg>'
  };

  window.renderIcons = function (root) {
    root = root || document;
    var nodes = root.querySelectorAll('[data-icon]');
    for (var i = 0; i < nodes.length; i++) {
      var name = nodes[i].getAttribute('data-icon');
      if (window.ICONS[name]) {
        nodes[i].innerHTML = window.ICONS[name];
      }
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    window.renderIcons(document);
  });
})();
