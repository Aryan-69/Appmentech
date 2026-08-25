# Appmentech Technologies Website Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the static Appmentech Technologies marketing site (hybrid homepage + Contact page + nav stub pages) described in `docs/superpowers/specs/2026-08-25-appmentech-website-design.md`, sourced from `appmentech_technologies_website.md`.

**Architecture:** Plain HTML/CSS/JS, no framework, no build step. Shared `css/base.css` (tokens/reset/typography), `css/layout.css` (grid/section layout/breakpoints), `css/components.css` (nav, buttons, cards, accordion, table, form, footer). Shared `js/icons.js` (inline SVG icon registry + injector), `js/nav.js` (mobile nav + smooth scroll), `js/accordion.js` (generic expand/collapse). Header/nav and footer markup is duplicated verbatim across every page (no server-side includes available on static hosting).

**Tech Stack:** HTML5, CSS3 (custom properties, Grid, Flexbox), vanilla ES5-compatible JavaScript. No dependencies, no CDN, no build tooling.

## Global Constraints

- No framework, no build step, no package.json — files must run by opening directly or from any static file server.
- Colors (exact hex, from approved design): background/primary `#F8FAFC`, accent `#6366F1`, accent hover `#4F46E5`, accent-light bg `#EEF2FF`, accent-light text `#4338CA`, heading `#0F172A`, body text `#475569`, muted text `#64748B`, border `#E2E8F0`, contrast band bg `#0F172A`, contrast band text `#F8FAFC`, contrast muted `#94A3B8`, card surface `#FFFFFF`.
- Font stack: `-apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif` — no external font requests.
- No photography anywhere — inline SVG line icons only (via `js/icons.js`), or plain text/chips where no icon was specified.
- Breakpoints: mobile `<640px`, tablet `640–1024px`, desktop `>1024px`. Implement as `max-width: 1024px` and `max-width: 640px` media queries (mobile-first overrides via cascading max-width blocks).
- Content wording (headings, CTAs, list items, descriptions) must match `appmentech_technologies_website.md` — do not invent new marketing copy beyond what's specified in each task.
- No automated test framework. Every task's "Verify" step is a manual check — either a `grep` on the file for required markup, or a browser check via the Browser tools (`mcp__Claude_Browser__*`) at the given viewport width. Run the verify step and confirm the expected result before moving to the next step.
- Commit after every task with a `feat:`/`docs:` prefixed message, per the repo's existing commit style (see `git log`).
- Line endings: the repo is CRLF-normalized by git on Windows (see earlier commit warnings) — this is expected, not an error, don't try to "fix" it.

---

### Task 1: Design tokens, reset, and base typography

**Files:**
- Create: `css/base.css`

**Interfaces:**
- Produces: CSS custom properties on `:root` — `--color-bg`, `--color-surface`, `--color-accent`, `--color-accent-hover`, `--color-accent-light`, `--color-accent-light-text`, `--color-heading`, `--color-body`, `--color-muted`, `--color-border`, `--color-contrast-bg`, `--color-contrast-text`, `--color-contrast-muted`, `--font-sans`, `--space-1` through `--space-7` (4/8/16/24/32/48/64px), `--radius` (8px), `--radius-lg` (16px), `--max-width` (1200px), `--shadow-card`, `--shadow-card-hover`. Every later task's CSS relies on these exact variable names.

- [ ] **Step 1: Write `css/base.css`**

```css
/* css/base.css — design tokens, reset, base typography */

:root {
  --color-bg: #F8FAFC;
  --color-surface: #FFFFFF;
  --color-accent: #6366F1;
  --color-accent-hover: #4F46E5;
  --color-accent-light: #EEF2FF;
  --color-accent-light-text: #4338CA;
  --color-heading: #0F172A;
  --color-body: #475569;
  --color-muted: #64748B;
  --color-border: #E2E8F0;
  --color-contrast-bg: #0F172A;
  --color-contrast-text: #F8FAFC;
  --color-contrast-muted: #94A3B8;

  --font-sans: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;

  --space-1: 4px;
  --space-2: 8px;
  --space-3: 16px;
  --space-4: 24px;
  --space-5: 32px;
  --space-6: 48px;
  --space-7: 64px;

  --radius: 8px;
  --radius-lg: 16px;
  --max-width: 1200px;

  --shadow-card: 0 1px 3px rgba(15, 23, 42, 0.08);
  --shadow-card-hover: 0 8px 24px rgba(15, 23, 42, 0.12);
}

*, *::before, *::after {
  box-sizing: border-box;
}

html {
  scroll-behavior: smooth;
}

body {
  margin: 0;
  font-family: var(--font-sans);
  background: var(--color-bg);
  color: var(--color-body);
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

h1, h2, h3, h4, h5, h6 {
  color: var(--color-heading);
  margin: 0 0 var(--space-3) 0;
  line-height: 1.25;
  font-weight: 800;
}

h1 { font-size: clamp(28px, 4vw, 44px); }
h2 { font-size: clamp(22px, 3vw, 32px); }
h3 { font-size: 18px; font-weight: 700; }

p { margin: 0 0 var(--space-3) 0; }

a {
  color: var(--color-accent);
  text-decoration: none;
}
a:hover { color: var(--color-accent-hover); }

img, svg { max-width: 100%; display: block; }

ul, ol { margin: 0; padding-left: var(--space-4); }

button, input, select, textarea {
  font-family: inherit;
  font-size: inherit;
}
```

- [ ] **Step 2: Verify**

Run: `grep -c "color-accent: #6366F1" css/base.css` — expect `1`.
Run: `grep -c "\-\-space-7: 64px" css/base.css` — expect `1`.

- [ ] **Step 3: Commit**

```bash
git add css/base.css
git commit -m "feat: add design tokens, reset, and base typography"
```

---

### Task 2: Icon registry (`js/icons.js`)

**Files:**
- Create: `js/icons.js`

**Interfaces:**
- Produces: global `window.ICONS` object mapping icon-name strings to inline SVG markup strings, and `window.renderIcons(root)` — walks `root.querySelectorAll('[data-icon]')` and sets `el.innerHTML = ICONS[name]` for each. Later tasks call `renderIcons(document)` on `DOMContentLoaded` and reference icons via `<span class="icon" data-icon="web"></span>`.
- Icon names this task defines (used by later tasks — do not rename): `web`, `mobile`, `ai`, `cloud`, `customized`, `scalable`, `secure`, `future-ready`, `automation`, `testing`, `devops`, `integration`, `ecommerce`, `healthcare`, `education`, `media`, `transport`, `social`, `enterprise`, `travel`, `realestate`, `food`, `government`, `more`, `check`, `arrow-right`.

- [ ] **Step 1: Write `js/icons.js`**

```javascript
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
```

- [ ] **Step 2: Verify**

Run: `grep -c "window.ICONS" js/icons.js` — expect `1`.
Run: `grep -o "^\s*[a-z-]*:" js/icons.js | grep -c ":"` — sanity count of icon keys (expect 26, allow ±2 from the regex being loose).
Run: `node -e "new Function(require('fs').readFileSync('js/icons.js','utf8'))()"` — expect no syntax error output (silent success).

- [ ] **Step 3: Commit**

```bash
git add js/icons.js
git commit -m "feat: add inline SVG icon registry"
```

---

### Task 3: Layout primitives and responsive grid utilities

**Files:**
- Create: `css/layout.css`

**Interfaces:**
- Consumes: `--space-*`, `--max-width` from Task 1 (`css/base.css`).
- Produces: `.container`, `.section` (+ `.section-alt` contrast variant), `.grid-2`/`.grid-3`/`.grid-4` (CSS Grid, `auto-fit`/`minmax`), `.stack` (vertical flex gap), `.eyebrow`, `.text-center`. Later tasks build content inside these.

- [ ] **Step 1: Write `css/layout.css`**

```css
/* css/layout.css — page container, section spacing, responsive grids */

.container {
  max-width: var(--max-width);
  margin: 0 auto;
  padding: 0 var(--space-4);
}

.section {
  padding: var(--space-7) 0;
}

.section-alt {
  background: var(--color-contrast-bg);
  color: var(--color-contrast-text);
}
.section-alt h1, .section-alt h2, .section-alt h3 {
  color: var(--color-contrast-text);
}
.section-alt p {
  color: var(--color-contrast-muted);
}

.text-center { text-align: center; }

.eyebrow {
  display: inline-block;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--color-accent);
  margin-bottom: var(--space-3);
}
.section-alt .eyebrow { color: #A5B4FC; }

.stack {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
}

.grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--space-4); }
.grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-4); }
.grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-4); }

@media (max-width: 1024px) {
  .grid-4 { grid-template-columns: repeat(2, 1fr); }
  .grid-3 { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
  .container { padding: 0 var(--space-3); }
  .section { padding: var(--space-6) 0; }
  .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
}
```

- [ ] **Step 2: Verify**

Run: `grep -c "grid-template-columns: 1fr" css/layout.css` — expect `1` (the mobile collapse rule).

- [ ] **Step 3: Commit**

```bash
git add css/layout.css
git commit -m "feat: add layout container, section, and grid utilities"
```

---

### Task 4: Shared components CSS (nav, buttons, cards, chips, table, accordion, footer)

**Files:**
- Create: `css/components.css`

**Interfaces:**
- Consumes: tokens from `css/base.css`, `.container`/`.section` from `css/layout.css`.
- Produces: `.nav`, `.nav-inner`, `.nav-links`, `.nav-toggle`, `.nav-links.open`; `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-outline-light`; `.card`, `.card-icon`, `.card-grid`; `.chip`, `.chip-list`; `.table-responsive table`; `.accordion-toggle`, `.accordion-body`, `.accordion-body.open`; `.stepper`, `.stepper-step`; `.footer`, `.footer-grid`, `.footer-links`. Tasks 5–17 use these class names verbatim — do not rename them later.

- [ ] **Step 1: Write `css/components.css`**

```css
/* css/components.css — nav, buttons, cards, chips, table, accordion, footer */

/* Nav */
.nav {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(248, 250, 252, 0.92);
  backdrop-filter: blur(6px);
  border-bottom: 1px solid var(--color-border);
}
.nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-3) 0;
}
.nav-logo {
  font-weight: 800;
  color: var(--color-heading);
  font-size: 18px;
}
.nav-logo span { color: var(--color-accent); }
.nav-links {
  display: flex;
  align-items: center;
  gap: var(--space-4);
  list-style: none;
  margin: 0;
  padding: 0;
}
.nav-links a {
  color: var(--color-body);
  font-weight: 600;
  font-size: 14px;
}
.nav-links a:hover { color: var(--color-accent); }
.nav-toggle {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
  padding: var(--space-2);
}
.nav-toggle span {
  display: block;
  width: 22px;
  height: 2px;
  background: var(--color-heading);
  margin: 4px 0;
}

@media (max-width: 1024px) {
  .nav-toggle { display: block; }
  .nav-links {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--color-surface);
    flex-direction: column;
    align-items: flex-start;
    gap: 0;
    border-bottom: 1px solid var(--color-border);
  }
  .nav-links.open { display: flex; }
  .nav-links li { width: 100%; }
  .nav-links a {
    display: block;
    padding: var(--space-3) var(--space-4);
    width: 100%;
    border-top: 1px solid var(--color-border);
  }
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: 12px 24px;
  border-radius: 999px;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.btn-primary {
  background: var(--color-accent);
  color: #fff;
}
.btn-primary:hover { background: var(--color-accent-hover); color: #fff; }
.btn-secondary {
  background: transparent;
  border-color: var(--color-accent);
  color: var(--color-accent);
}
.btn-secondary:hover { background: var(--color-accent-light); }
.btn-outline-light {
  background: transparent;
  border-color: rgba(248, 250, 252, 0.4);
  color: var(--color-contrast-text);
}
.btn-outline-light:hover { background: rgba(248, 250, 252, 0.1); }

/* Cards */
.card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  padding: var(--space-4);
  box-shadow: var(--shadow-card);
  transition: box-shadow 0.15s ease, transform 0.15s ease;
}
.card:hover {
  box-shadow: var(--shadow-card-hover);
  transform: translateY(-2px);
}
.card-icon {
  width: 40px;
  height: 40px;
  border-radius: var(--radius);
  background: var(--color-accent-light);
  color: var(--color-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: var(--space-3);
}
.card-icon svg { width: 22px; height: 22px; }
.card h3 { margin-bottom: var(--space-2); }
.card p { color: var(--color-muted); font-size: 14px; margin-bottom: var(--space-2); }

/* Chips */
.chip-list {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  margin: 0;
  padding: 0;
}
.chip {
  background: var(--color-accent-light);
  color: var(--color-accent-light-text);
  font-size: 13px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 999px;
}
.section-alt .chip {
  background: rgba(99, 102, 241, 0.25);
  color: #E0E7FF;
}

/* Table */
.table-responsive table {
  width: 100%;
  border-collapse: collapse;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  overflow: hidden;
}
.table-responsive th, .table-responsive td {
  text-align: left;
  padding: var(--space-3);
  border-bottom: 1px solid var(--color-border);
  font-size: 14px;
}
.table-responsive th {
  background: var(--color-accent-light);
  color: var(--color-accent-light-text);
}
.table-responsive tr:last-child td { border-bottom: none; }

@media (max-width: 640px) {
  .table-responsive thead { display: none; }
  .table-responsive table, .table-responsive tbody, .table-responsive tr, .table-responsive td {
    display: block;
    width: 100%;
  }
  .table-responsive tr {
    border-bottom: 1px solid var(--color-border);
    padding: var(--space-2) 0;
  }
  .table-responsive td {
    border: none;
    padding: 4px var(--space-3);
  }
  .table-responsive td::before {
    content: attr(data-label);
    display: block;
    font-weight: 700;
    color: var(--color-heading);
    font-size: 12px;
    text-transform: uppercase;
    margin-bottom: 2px;
  }
}

/* Accordion */
.accordion-toggle {
  background: none;
  border: none;
  color: var(--color-accent);
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  padding: 0;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.accordion-toggle svg {
  width: 14px;
  height: 14px;
  transition: transform 0.15s ease;
}
.accordion-toggle.open svg { transform: rotate(90deg); }
.accordion-body {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.2s ease;
}
.accordion-body.open { max-height: 600px; }
.accordion-body ul {
  margin: var(--space-2) 0 0 0;
  padding-left: var(--space-4);
  font-size: 13px;
  color: var(--color-muted);
}
.accordion-body li { margin-bottom: 4px; }

/* Stepper (Product Lifecycle) */
.stepper {
  display: flex;
  gap: var(--space-3);
  overflow-x: auto;
  padding-bottom: var(--space-2);
}
.stepper-step {
  flex: 1 0 160px;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  padding: var(--space-3);
}
.stepper-step .step-number {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--color-accent);
  color: #fff;
  font-weight: 700;
  font-size: 13px;
  margin-bottom: var(--space-2);
}

@media (max-width: 640px) {
  .stepper { flex-direction: column; overflow-x: visible; }
  .stepper-step { flex: none; }
}

/* Footer */
.footer {
  background: var(--color-contrast-bg);
  color: var(--color-contrast-muted);
  padding: var(--space-6) 0 var(--space-4);
}
.footer h3 { color: var(--color-contrast-text); }
.footer-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: var(--space-5);
  margin-bottom: var(--space-5);
}
.footer-links {
  list-style: none;
  margin: 0;
  padding: 0;
}
.footer-links li { margin-bottom: var(--space-2); }
.footer-links a { color: var(--color-contrast-muted); font-size: 14px; }
.footer-links a:hover { color: var(--color-contrast-text); }
.footer-bottom {
  border-top: 1px solid rgba(248, 250, 252, 0.12);
  padding-top: var(--space-4);
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: var(--space-2);
  font-size: 13px;
}

@media (max-width: 640px) {
  .footer-grid { grid-template-columns: 1fr; }
  .footer-bottom { flex-direction: column; }
}
```

- [ ] **Step 2: Verify**

Run: `grep -c "\.nav-links\.open" css/components.css` — expect `1`.
Run: `grep -c "\.accordion-body\.open" css/components.css` — expect `1`.

- [ ] **Step 3: Commit**

```bash
git add css/components.css
git commit -m "feat: add nav, button, card, chip, table, accordion, footer component styles"
```

---

### Task 5: `js/nav.js` — mobile nav toggle + smooth-scroll close

**Files:**
- Create: `js/nav.js`

**Interfaces:**
- Consumes: `.nav-toggle`, `.nav-links` classes from Task 4.
- Produces: click-to-toggle behavior; no exported functions (self-contained `DOMContentLoaded` listener). Later HTML tasks must include `<button class="nav-toggle" aria-label="Toggle menu" aria-expanded="false">...</button>` and `<ul class="nav-links" id="nav-links">...</ul>` for this to attach to.

- [ ] **Step 1: Write `js/nav.js`**

```javascript
// js/nav.js — mobile nav toggle, closes menu on link click
(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.nav-toggle');
    var links = document.querySelector('.nav-links');
    if (!toggle || !links) return;

    toggle.addEventListener('click', function () {
      var isOpen = links.classList.toggle('open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    var anchors = links.querySelectorAll('a');
    for (var i = 0; i < anchors.length; i++) {
      anchors[i].addEventListener('click', function () {
        links.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    }
  });
})();
```

- [ ] **Step 2: Verify**

Run: `node -e "new Function(require('fs').readFileSync('js/nav.js','utf8'))()"` — expect no syntax error output.

- [ ] **Step 3: Commit**

```bash
git add js/nav.js
git commit -m "feat: add mobile nav toggle script"
```

---

### Task 6: `js/accordion.js` — generic expand/collapse

**Files:**
- Create: `js/accordion.js`

**Interfaces:**
- Consumes: nothing (generic, data-attribute driven).
- Produces: event-delegated click handler on `document` for any `.accordion-toggle[data-target]` — toggles the `.open` class on the element with matching `id`, and flips the toggle button's text between "Show more" and "Show less" via its `data-label-more`/`data-label-less` attributes (falls back to a generic label if attributes are absent). Later HTML tasks build cards with `<button class="accordion-toggle" data-target="solutions-web" data-label-more="Show more" data-label-less="Show less">Show more <svg data-icon="arrow-right"></svg></button>` and `<div class="accordion-body" id="solutions-web">...</div>` pairs.

- [ ] **Step 1: Write `js/accordion.js`**

```javascript
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
```

- [ ] **Step 2: Verify**

Run: `node -e "new Function(require('fs').readFileSync('js/accordion.js','utf8'))()"` — expect no syntax error output.

- [ ] **Step 3: Commit**

```bash
git add js/accordion.js
git commit -m "feat: add generic accordion expand/collapse script"
```

---

### Task 7: `index.html` skeleton — head/SEO, header nav, and footer

**Files:**
- Create: `index.html`

**Interfaces:**
- Consumes: `css/base.css`, `css/layout.css`, `css/components.css`, `js/icons.js`, `js/nav.js`, `js/accordion.js`.
- Produces: the page skeleton every later index.html task inserts `<section>` blocks into, between `<!-- SECTIONS START -->` and `<!-- SECTIONS END -->` comments inside `<main>`. Also produces the canonical header/footer markup that Task 16 (contact.html) and Task 17 (stub pages) copy verbatim.

- [ ] **Step 1: Write `index.html`**

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appmentech Technologies | All-in-One Digital &amp; Software Solutions - Web, Mobile, AI &amp; Cloud</title>
  <meta name="description" content="Build websites, mobile applications, SaaS platforms, AI solutions, cloud systems, enterprise software and business automation with one trusted digital technology partner.">
  <meta name="keywords" content="software development company, web development company, mobile app development, AI development company, SaaS development, cloud solutions, enterprise software development, business automation, AI agents, GenAI solutions, software testing services, DevOps services, API integration, digital transformation, custom software solutions">
  <meta property="og:title" content="Appmentech Technologies | All-in-One Digital & Software Solutions">
  <meta property="og:description" content="Build websites, mobile applications, SaaS platforms, AI solutions, cloud systems, enterprise software and business automation with one trusted digital technology partner.">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/components.css">
</head>
<body>

  <header class="nav">
    <div class="container nav-inner">
      <a href="index.html" class="nav-logo">Appmentech<span>.</span></a>
      <button class="nav-toggle" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <ul class="nav-links" id="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="index.html#solutions">Solutions</a></li>
        <li><a href="index.html#industries">Industries</a></li>
        <li><a href="index.html#ai-automation">AI &amp; Automation</a></li>
        <li><a href="case-studies.html">Case Studies</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="careers.html">Careers</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
    </div>
  </header>

  <main>
    <!-- SECTIONS START -->
    <!-- SECTIONS END -->
  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <h3>Appmentech Technologies</h3>
          <p>Your All-in-One Digital &amp; Software Solutions Partner.</p>
          <p style="font-size:13px;">Web | Mobile | AI | SaaS | Cloud | Automation | Enterprise | Quality Engineering</p>
        </div>
        <div>
          <h3 style="font-size:14px;">Company</h3>
          <ul class="footer-links">
            <li><a href="about.html">About</a></li>
            <li><a href="careers.html">Careers</a></li>
            <li><a href="case-studies.html">Case Studies</a></li>
            <li><a href="contact.html">Contact</a></li>
          </ul>
        </div>
        <div>
          <h3 style="font-size:14px;">Contact</h3>
          <ul class="footer-links">
            <li>info@appmentechtech.com</li>
            <li>+91 12345 67890</li>
            <li>India / Global</li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>&copy; 2026 Appmentech Technologies. All Rights Reserved.</span>
        <span>
          <a href="privacy.html">Privacy Policy</a> ·
          <a href="terms.html">Terms of Service</a> ·
          <a href="cookies.html">Cookie Policy</a> ·
          <a href="contact.html">Contact</a>
        </span>
      </div>
    </div>
  </footer>

  <script src="js/icons.js"></script>
  <script src="js/nav.js"></script>
  <script src="js/accordion.js"></script>
</body>
</html>
```

- [ ] **Step 2: Verify (browser)**

Use `mcp__Claude_Browser__preview_start` with `url` pointing at the local `index.html` (e.g. `file:///C:/Users/lexpr/Appmentech/index.html`), then `mcp__Claude_Browser__resize_window` to `preset: "mobile"`, then `mcp__Claude_Browser__computer` with `action: "left_click"` on the hamburger button, then `read_page` to confirm `.nav-links` gained class `open`. Resize to `preset: "desktop"` and confirm footer text "Appmentech Technologies" and "info@appmentechtech.com" are present via `get_page_text`.

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add index.html skeleton with head/SEO, nav, and footer"
```

---

### Task 8: Hero + "Why Businesses Choose Appmentech" + "We Build" chip strip

**Files:**
- Modify: `index.html` — insert between `<!-- SECTIONS START -->` and `<!-- SECTIONS END -->`

**Interfaces:**
- Consumes: `.section`, `.container`, `.eyebrow`, `.grid-4`, `.card`, `.card-icon`, `.chip-list`, `.chip`, `.btn`, `.btn-primary`, `.btn-secondary` from Tasks 3–4; icons `web`, `mobile`, `ai`, `cloud`, `customized`, `scalable`, `secure`, `future-ready` from Task 2.

- [ ] **Step 1: Insert hero + why-us tiles + we-build chips**

Replace `<!-- SECTIONS START -->\n    <!-- SECTIONS END -->` with:

```html
    <!-- SECTIONS START -->
    <section class="section text-center" id="home">
      <div class="container">
        <span class="eyebrow">One Platform. Endless Solutions.</span>
        <h1>Your All-in-One Digital &amp;<br>Software Solutions Partner</h1>
        <p style="max-width:640px;margin:0 auto var(--space-4);font-size:17px;">
          From innovative websites and mobile applications to complex enterprise systems —
          we build solutions that drive your business forward.
        </p>
        <div class="stack" style="flex-direction:row;justify-content:center;flex-wrap:wrap;">
          <a href="contact.html" class="btn btn-primary">Let's Build Your Solution</a>
          <a href="contact.html" class="btn btn-secondary">Get a Free Consultation</a>
        </div>
        <ul class="chip-list" style="justify-content:center;margin-top:var(--space-5);">
          <li class="chip">Websites</li>
          <li class="chip">Web Applications</li>
          <li class="chip">Mobile Applications</li>
          <li class="chip">SaaS Platforms</li>
          <li class="chip">Enterprise Applications</li>
          <li class="chip">AI &amp; Generative AI Solutions</li>
          <li class="chip">AI Agents &amp; Agentic AI</li>
          <li class="chip">API &amp; System Integrations</li>
          <li class="chip">Cloud Solutions</li>
          <li class="chip">Business Automation</li>
          <li class="chip">CRM &amp; Workflow Platforms</li>
          <li class="chip">Data &amp; Analytics Platforms</li>
          <li class="chip">Testing &amp; Quality Engineering</li>
          <li class="chip">DevOps &amp; CI/CD Solutions</li>
          <li class="chip">Cybersecurity &amp; Identity Solutions</li>
        </ul>
      </div>
    </section>

    <section class="section" style="padding-top:0;">
      <div class="container">
        <h2 class="text-center">Why Businesses Choose Appmentech</h2>
        <div class="grid-4">
          <div class="card">
            <div class="card-icon" data-icon="customized"></div>
            <h3>Customized Solutions</h3>
            <p>Built for Your Unique Needs</p>
          </div>
          <div class="card">
            <div class="card-icon" data-icon="scalable"></div>
            <h3>Scalable Architecture</h3>
            <p>Grow Without Limits</p>
          </div>
          <div class="card">
            <div class="card-icon" data-icon="secure"></div>
            <h3>Secure &amp; Reliable</h3>
            <p>Enterprise-Grade Security</p>
          </div>
          <div class="card">
            <div class="card-icon" data-icon="future-ready"></div>
            <h3>Future-Ready Technology</h3>
            <p>Innovation for Tomorrow</p>
          </div>
        </div>
      </div>
    </section>
    <!-- SECTIONS END -->
```

- [ ] **Step 2: Verify (browser)**

Reload `index.html` in the Browser pane, run `get_page_text`, confirm it contains "Your All-in-One Digital" and "Customized Solutions" and "Cybersecurity & Identity Solutions". Zoom/screenshot the hero to confirm the 4 icon tiles render actual SVG glyphs (not blank boxes) — `renderIcons` runs on `DOMContentLoaded`.

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add hero, why-choose-us tiles, and we-build chip strip"
```

---

### Task 9: "Why Choose Us" capability table (spec §3)

**Files:**
- Modify: `index.html` — insert before `<!-- SECTIONS END -->`

**Interfaces:**
- Consumes: `.table-responsive` from Task 4.

- [ ] **Step 1: Insert table section**

Insert before `<!-- SECTIONS END -->`:

```html
    <section class="section section-alt">
      <div class="container">
        <span class="eyebrow">Why Choose Us</span>
        <h2>One Partner. Your Complete Technology Journey.</h2>
        <p>Instead of working with multiple vendors for design, development, cloud, testing, automation, and support, customers can work with one technology partner.</p>
        <div class="table-responsive">
          <table>
            <thead>
              <tr><th>Capability</th><th>What We Provide</th></tr>
            </thead>
            <tbody>
              <tr><td data-label="Capability">Custom Development</td><td data-label="What We Provide">Solutions designed around your business</td></tr>
              <tr><td data-label="Capability">Multi-Platform</td><td data-label="What We Provide">Web, Android, iOS, desktop and cloud</td></tr>
              <tr><td data-label="Capability">AI Ready</td><td data-label="What We Provide">AI, GenAI, RAG, AI agents and automation</td></tr>
              <tr><td data-label="Capability">Cloud Ready</td><td data-label="What We Provide">AWS and modern cloud architecture</td></tr>
              <tr><td data-label="Capability">Secure</td><td data-label="What We Provide">Security-focused development and deployment</td></tr>
              <tr><td data-label="Capability">Scalable</td><td data-label="What We Provide">Architecture designed for future growth</td></tr>
              <tr><td data-label="Capability">Quality Engineering</td><td data-label="What We Provide">Functional, API, mobile and performance testing</td></tr>
              <tr><td data-label="Capability">DevOps</td><td data-label="What We Provide">CI/CD, deployment and infrastructure automation</td></tr>
              <tr><td data-label="Capability">Integration</td><td data-label="What We Provide">APIs, databases, third-party and enterprise systems</td></tr>
              <tr><td data-label="Capability">Support</td><td data-label="What We Provide">Continuous maintenance and optimization</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
    <!-- SECTIONS END -->
```

(Note: replace the literal `<!-- SECTIONS END -->` that was already in the file — don't duplicate it.)

- [ ] **Step 2: Verify (browser)**

Reload, `get_page_text` contains "Multi-Platform" and "Continuous maintenance and optimization". Resize to `preset: "mobile"`, screenshot the table area, confirm it renders as stacked label/value rows (not a horizontally-scrolling 2-column table) — `.table-responsive thead { display:none }` rule from Task 4 applies under 640px.

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add why-choose-us capability table"
```

---

### Task 10: Solutions section — 8 category cards with accordions (spec §4–§10)

**Files:**
- Modify: `index.html` — insert before `<!-- SECTIONS END -->`

**Interfaces:**
- Consumes: `.card`, `.card-icon`, `.grid-3` (Task 3/4 — note: use `.grid-3` for a 3-per-row desktop layout, collapsing to 2 then 1 per the Task 3 media queries), `.accordion-toggle`/`.accordion-body` pattern (Task 6), icons `web`, `mobile`, `ai`, `cloud`, `automation`, `testing`, `devops`, `integration` (Task 2).
- Produces: 8 `id`-bearing accordion bodies (`solutions-web`, `solutions-mobile`, `solutions-ai`, `solutions-cloud`, `solutions-automation`, `solutions-testing`, `solutions-devops`, `solutions-integration`) — unique across the whole site, later tasks must not reuse these ids.

- [ ] **Step 1: Insert Solutions section**

Insert before `<!-- SECTIONS END -->` (each card follows the same pattern — icon, title, intro line, "Show more" accordion revealing the full bullet list):

```html
    <section class="section" id="solutions">
      <span class="container" style="display:block;">
        <span class="eyebrow">Our Digital Solutions</span>
        <h2>Everything You Need, One Partner</h2>
      </span>
      <div class="container grid-3">

        <div class="card">
          <div class="card-icon" data-icon="web"></div>
          <h3>Web Development</h3>
          <p>We create modern, responsive and scalable web solutions.</p>
          <button class="accordion-toggle" data-target="solutions-web" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="solutions-web">
            <ul>
              <li>Corporate websites</li><li>Business portals</li><li>Customer portals</li>
              <li>E-commerce websites</li><li>Marketplace platforms</li><li>Booking platforms</li>
              <li>SaaS web applications</li><li>Admin dashboards</li><li>B2B/B2C platforms</li>
              <li>Progressive Web Applications</li>
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="mobile"></div>
          <h3>Mobile Application Development</h3>
          <p>Build mobile experiences for customers, employees, partners, and communities.</p>
          <button class="accordion-toggle" data-target="solutions-mobile" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="solutions-mobile">
            <ul>
              <li>Android, iOS, and cross-platform applications</li>
              <li>E-commerce apps</li><li>Healthcare apps</li><li>Education apps</li>
              <li>Delivery apps</li><li>Travel apps</li><li>Finance and business apps</li>
              <li>Social applications</li><li>IoT companion applications</li><li>Field-service applications</li>
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="ai"></div>
          <h3>AI &amp; Intelligent Solutions</h3>
          <p>We help organizations integrate AI into existing products and build new AI-powered applications.</p>
          <button class="accordion-toggle" data-target="solutions-ai" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="solutions-ai">
            <ul>
              <li>Generative AI applications</li><li>AI assistants</li><li>AI agents</li>
              <li>Agentic AI workflows</li><li>Retrieval-Augmented Generation (RAG)</li>
              <li>Vector databases</li><li>Document intelligence</li><li>AI-powered search</li>
              <li>Recommendation engines</li><li>Automated customer support</li>
              <li>AI-powered testing</li><li>AI workflow automation</li>
              <li>LLM application development</li><li>Model/API integration</li>
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="cloud"></div>
          <h3>Cloud &amp; Enterprise Solutions</h3>
          <p>We design and implement cloud-ready applications and infrastructure.</p>
          <button class="accordion-toggle" data-target="solutions-cloud" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="solutions-cloud">
            <ul>
              <li>Cloud architecture</li><li>AWS solutions</li><li>Compute and storage</li>
              <li>Load balancing</li><li>CDN</li><li>DNS</li><li>Database architecture</li>
              <li>Caching</li><li>Messaging</li><li>Serverless applications</li>
              <li>Monitoring and observability</li><li>Backup and disaster recovery</li>
              <li>Security and encryption</li><li>Infrastructure automation</li>
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="automation"></div>
          <h3>Business Automation</h3>
          <p>We identify manual processes and convert them into automated digital workflows.</p>
          <button class="accordion-toggle" data-target="solutions-automation" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="solutions-automation">
            <ul>
              <li>Approval workflows</li><li>Report generation</li><li>Data synchronization</li>
              <li>API automation</li><li>Employee workflows</li><li>Customer onboarding</li>
              <li>Document processing</li><li>Notification systems</li><li>Test automation</li>
              <li>Deployment automation</li><li>Infrastructure automation</li><li>AI-powered workflow automation</li>
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="testing"></div>
          <h3>Software Testing &amp; Quality Engineering</h3>
          <p>We provide end-to-end quality engineering for web, mobile, API, cloud, and enterprise applications.</p>
          <button class="accordion-toggle" data-target="solutions-testing" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="solutions-testing">
            <ul>
              <li>Functional testing</li><li>API testing</li><li>Mobile application testing</li>
              <li>Web application testing</li><li>Integration testing</li><li>Regression testing</li>
              <li>Performance testing</li><li>Load testing</li><li>Stress testing</li>
              <li>Security testing coordination</li><li>Database testing</li><li>Compatibility testing</li>
              <li>Automation testing</li><li>CI/CD quality gates</li><li>Observability testing</li><li>Contract testing</li>
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="devops"></div>
          <h3>DevOps &amp; CI/CD</h3>
          <p>We help engineering teams automate software delivery and improve release reliability.</p>
          <button class="accordion-toggle" data-target="solutions-devops" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="solutions-devops">
            <ul>
              <li>CI/CD pipelines</li><li>Jenkins automation</li><li>Git-based workflows</li>
              <li>Build automation</li><li>Deployment automation</li><li>Environment management</li>
              <li>Infrastructure automation</li><li>Containerization</li><li>Monitoring</li>
              <li>Logging</li><li>Release management</li><li>Rollback strategies</li>
            </ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="integration"></div>
          <h3>API &amp; System Integration</h3>
          <p>We integrate applications, databases, cloud services, third-party platforms, and enterprise systems.</p>
          <button class="accordion-toggle" data-target="solutions-integration" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="solutions-integration">
            <ul>
              <li>REST APIs</li><li>gRPC</li><li>Webhooks</li><li>Message queues</li><li>RabbitMQ</li>
              <li>Database integration</li><li>Payment integration</li><li>Authentication integration</li>
              <li>OAuth / OAuth2</li><li>SAML / SSO</li><li>SCIM</li><li>CRM integration</li>
              <li>ERP integration</li><li>Third-party SaaS integration</li>
            </ul>
          </div>
        </div>

      </div>
    </section>
    <!-- SECTIONS END -->
```

- [ ] **Step 2: Verify (browser)**

Reload, `get_page_text` contains "API & System Integration" and "Software Testing & Quality Engineering". Click the "Show more" button on the Web Development card (`find` query "Show more" first match), confirm via `read_page` that `#solutions-web` now has class `open` and the bullet "Progressive Web Applications" is visible in `get_page_text`.

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add solutions section with 8 category cards and accordions"
```

---

### Task 11: AI example flow strip (spec §5 example)

**Files:**
- Modify: `index.html` — insert before `<!-- SECTIONS END -->`

- [ ] **Step 1: Insert AI flow section**

```html
    <section class="section section-alt" id="ai-automation">
      <div class="container text-center">
        <span class="eyebrow">Transform Your Business with AI</span>
        <h2>From Query to Action, Automatically</h2>
        <div class="stack" style="flex-direction:row;flex-wrap:wrap;justify-content:center;gap:var(--space-2);font-size:14px;font-weight:700;margin-top:var(--space-4);">
          <span class="chip">Customer Query</span>
          <span>→</span>
          <span class="chip">AI Agent</span>
          <span>→</span>
          <span class="chip">Knowledge Base / Vector DB</span>
          <span>→</span>
          <span class="chip">Business APIs</span>
          <span>→</span>
          <span class="chip">Action</span>
          <span>→</span>
          <span class="chip">Response</span>
        </div>
      </div>
    </section>
    <!-- SECTIONS END -->
```

- [ ] **Step 2: Verify (browser)**

Reload, `get_page_text` contains "Knowledge Base / Vector DB" and "From Query to Action, Automatically".

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add AI example flow strip"
```

---

### Task 12: Industries section — 12 cards + "and many more" (spec §11)

**Files:**
- Modify: `index.html` — insert before `<!-- SECTIONS END -->`

**Interfaces:**
- Consumes: same card/accordion pattern as Task 10; icons `ecommerce`, `healthcare`, `education`, `media`, `transport`, `social`, `enterprise`, `travel`, `realestate`, `food`, `government`, `more` (Task 2).
- Produces: 12 unique ids `industry-ecommerce`, `industry-healthcare`, `industry-education`, `industry-media`, `industry-transport`, `industry-social`, `industry-enterprise`, `industry-travel`, `industry-realestate`, `industry-food`, `industry-government` (11 accordion bodies — the 12th "And Many More" card has no accordion, it's a CTA card).

- [ ] **Step 1: Insert Industries section**

```html
    <section class="section" id="industries">
      <span class="container" style="display:block;">
        <span class="eyebrow">Industries</span>
        <h2>Solutions for Every Industry</h2>
      </span>
      <div class="container grid-3">

        <div class="card">
          <div class="card-icon" data-icon="ecommerce"></div>
          <h3>E-Commerce &amp; Retail</h3>
          <p>Online stores, marketplaces, inventory management, secure payments &amp; more.</p>
          <button class="accordion-toggle" data-target="industry-ecommerce" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-ecommerce">
            <ul><li>Online stores</li><li>Marketplaces</li><li>Inventory management</li><li>Order management</li><li>Payment integration</li><li>Customer loyalty</li><li>Retail analytics</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="healthcare"></div>
          <h3>Healthcare &amp; Telemedicine</h3>
          <p>Patient portals, teleconsultation, EHR systems, appointment booking &amp; healthcare apps.</p>
          <button class="accordion-toggle" data-target="industry-healthcare" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-healthcare">
            <ul><li>Patient portals</li><li>Doctor applications</li><li>Telemedicine</li><li>Appointment systems</li><li>Healthcare management</li><li>Electronic records integration</li><li>Secure communication</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="education"></div>
          <h3>Education</h3>
          <p>E-learning platforms, virtual classrooms, interactive learning &amp; automated study suites.</p>
          <button class="accordion-toggle" data-target="industry-education" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-education">
            <ul><li>E-learning platforms</li><li>Virtual classrooms</li><li>Learning management systems</li><li>Interactive language learning</li><li>Online examinations</li><li>Automated study suites</li><li>Student portals</li><li>Teacher portals</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="media"></div>
          <h3>Media &amp; Entertainment</h3>
          <p>Streaming platforms, content management, OTT apps, live events &amp; more.</p>
          <button class="accordion-toggle" data-target="industry-media" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-media">
            <ul><li>Streaming platforms</li><li>OTT applications</li><li>Content management</li><li>Video platforms</li><li>Digital publishing</li><li>Live-event platforms</li><li>Subscription platforms</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="transport"></div>
          <h3>Transportation &amp; Logistics</h3>
          <p>GPS navigation, real-time fleet tracking, route optimization, delivery &amp; logistic solutions.</p>
          <button class="accordion-toggle" data-target="industry-transport" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-transport">
            <ul><li>GPS navigation</li><li>Fleet management</li><li>Real-time tracking</li><li>Route optimization</li><li>Delivery management</li><li>Driver applications</li><li>Logistics dashboards</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="social"></div>
          <h3>Social Networking &amp; Communication</h3>
          <p>Instant messaging, professional networking, content sharing, video conferencing &amp; more.</p>
          <button class="accordion-toggle" data-target="industry-social" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-social">
            <ul><li>Instant messaging</li><li>Professional networking</li><li>Content sharing</li><li>Community platforms</li><li>Video conferencing</li><li>Social applications</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="enterprise"></div>
          <h3>Enterprise &amp; Cloud Infrastructure</h3>
          <p>Workflow automation, CRM, project monitoring, cloud resources &amp; Salesforce solutions.</p>
          <button class="accordion-toggle" data-target="industry-enterprise" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-enterprise">
            <ul><li>Workflow automation</li><li>CRM</li><li>Project monitoring</li><li>Enterprise portals</li><li>Cloud resource management</li><li>Salesforce integration</li><li>Business intelligence</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="travel"></div>
          <h3>Travel &amp; Hospitality</h3>
          <p>Hotel &amp; flight booking, digital ticketing, local guide apps, home-sharing services &amp; more.</p>
          <button class="accordion-toggle" data-target="industry-travel" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-travel">
            <ul><li>Hotel booking</li><li>Flight booking</li><li>Digital ticketing</li><li>Travel management</li><li>Local guide applications</li><li>Home-sharing platforms</li><li>Tourism applications</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="realestate"></div>
          <h3>Real Estate &amp; Property Management</h3>
          <p>Property listings, lease management, maintenance tracking, tenant portals &amp; more.</p>
          <button class="accordion-toggle" data-target="industry-realestate" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-realestate">
            <ul><li>Property listing</li><li>Property search</li><li>Rental management</li><li>Tenant portals</li><li>Property management</li><li>Maintenance management</li><li>Real estate CRM</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="food"></div>
          <h3>Food &amp; Hospitality</h3>
          <p>Restaurant reservation systems, digital QR menus, order management &amp; customer loyalty apps.</p>
          <button class="accordion-toggle" data-target="industry-food" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-food">
            <ul><li>Restaurant reservation</li><li>Online ordering</li><li>Digital QR menus</li><li>Restaurant management</li><li>Kitchen management</li><li>Delivery integration</li><li>Customer loyalty</li></ul>
          </div>
        </div>

        <div class="card">
          <div class="card-icon" data-icon="government"></div>
          <h3>Government &amp; Public Services</h3>
          <p>Tax filing portals, civic engagement tools, identity verification, public transport updates &amp; more.</p>
          <button class="accordion-toggle" data-target="industry-government" data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
          </button>
          <div class="accordion-body" id="industry-government">
            <ul><li>Tax filing portals</li><li>Citizen portals</li><li>Civic engagement</li><li>Identity verification</li><li>Public transport updates</li><li>Digital public services</li><li>Government workflow automation</li></ul>
          </div>
        </div>

        <div class="card" style="display:flex;flex-direction:column;justify-content:center;text-align:center;background:var(--color-accent-light);border-color:var(--color-accent);">
          <div class="card-icon" data-icon="more" style="margin:0 auto var(--space-3);"></div>
          <h3>And Many More Custom Solutions</h3>
          <p>Whatever your idea, we build the right solution for you. Tell us what you need — we can design a solution around your business process.</p>
          <a href="contact.html" class="btn btn-primary" style="margin:0 auto;">Tell Us What You Need</a>
        </div>

      </div>
    </section>
    <!-- SECTIONS END -->
```

- [ ] **Step 2: Verify (browser)**

Reload, `get_page_text` contains "Real Estate & Property Management" and "And Many More Custom Solutions". Confirm 12 `.card` elements exist inside `#industries` via `read_page` (11 with accordions + 1 CTA card).

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add industries section with 12 cards"
```

---

### Task 13: Product Development Lifecycle stepper (spec §12)

**Files:**
- Modify: `index.html` — insert before `<!-- SECTIONS END -->`

- [ ] **Step 1: Insert stepper section**

```html
    <section class="section section-alt">
      <div class="container">
        <span class="eyebrow">Product Development Lifecycle</span>
        <h2>From Idea to Production</h2>
        <div class="stepper">
          <div class="stepper-step"><span class="step-number">1</span><h3>Discover</h3><p style="font-size:13px;">Understand your business, users, challenges, goals, and technology requirements.</p></div>
          <div class="stepper-step"><span class="step-number">2</span><h3>Design</h3><p style="font-size:13px;">Create the product architecture, user experience, technical design, and roadmap.</p></div>
          <div class="stepper-step"><span class="step-number">3</span><h3>Develop</h3><p style="font-size:13px;">Build web, mobile, backend, APIs, databases, cloud services, and integrations.</p></div>
          <div class="stepper-step"><span class="step-number">4</span><h3>Test</h3><p style="font-size:13px;">Validate functionality, performance, security requirements, compatibility, and reliability.</p></div>
          <div class="stepper-step"><span class="step-number">5</span><h3>Deploy</h3><p style="font-size:13px;">Release the solution using automated CI/CD and cloud infrastructure.</p></div>
          <div class="stepper-step"><span class="step-number">6</span><h3>Monitor</h3><p style="font-size:13px;">Monitor applications, infrastructure, logs, metrics, performance, and user experience.</p></div>
          <div class="stepper-step"><span class="step-number">7</span><h3>Improve</h3><p style="font-size:13px;">Continuously enhance the product based on business needs, analytics, and customer feedback.</p></div>
        </div>
      </div>
    </section>
    <!-- SECTIONS END -->
```

- [ ] **Step 2: Verify (browser)**

Reload, `get_page_text` contains "Discover", "Improve", and "Continuously enhance the product". Resize to `preset: "mobile"`, screenshot, confirm the 7 steps stack vertically (Task 4's `.stepper { flex-direction:column }` mobile rule applies).

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add product development lifecycle stepper"
```

---

### Task 14: Tech stack chips, business models, and our-approach tiles (spec §13–§15)

**Files:**
- Modify: `index.html` — insert before `<!-- SECTIONS END -->`

- [ ] **Step 1: Insert tech stack + business models + approach**

```html
    <section class="section">
      <div class="container">
        <span class="eyebrow">Technology Capabilities</span>
        <h2>Modern Technology Stack</h2>
        <div class="stack">
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Frontend</h3>
            <ul class="chip-list"><li class="chip">React</li><li class="chip">Angular</li><li class="chip">Vue</li><li class="chip">HTML/CSS/JavaScript</li><li class="chip">Progressive Web Applications</li></ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Mobile</h3>
            <ul class="chip-list"><li class="chip">Android</li><li class="chip">iOS</li><li class="chip">Cross-platform frameworks</li></ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Backend</h3>
            <ul class="chip-list"><li class="chip">.NET</li><li class="chip">Java</li><li class="chip">Node.js</li><li class="chip">Python</li><li class="chip">REST</li><li class="chip">gRPC</li></ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Databases</h3>
            <ul class="chip-list"><li class="chip">PostgreSQL</li><li class="chip">MySQL</li><li class="chip">SQL Server</li><li class="chip">MongoDB</li><li class="chip">DynamoDB</li><li class="chip">Redis</li><li class="chip">Elasticsearch / OpenSearch</li></ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Cloud</h3>
            <ul class="chip-list"><li class="chip">AWS</li><li class="chip">Cloud-native architecture</li><li class="chip">Serverless</li><li class="chip">Containers</li><li class="chip">Infrastructure automation</li></ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">AI</h3>
            <ul class="chip-list"><li class="chip">LLM applications</li><li class="chip">RAG</li><li class="chip">Vector databases</li><li class="chip">AI agents</li><li class="chip">Agentic workflows</li><li class="chip">AI automation</li></ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">DevOps</h3>
            <ul class="chip-list"><li class="chip">Git</li><li class="chip">Jenkins</li><li class="chip">CI/CD</li><li class="chip">Docker</li><li class="chip">Infrastructure automation</li><li class="chip">Monitoring and observability</li></ul>
          </div>
        </div>
      </div>
    </section>

    <section class="section" style="background:var(--color-surface);">
      <div class="container">
        <span class="eyebrow">Business Models</span>
        <h2>Flexible Engagement Models</h2>
        <div class="grid-3">
          <div class="card"><h3>Fixed Project</h3><p>Best for clearly defined projects with fixed scope and timeline.</p></div>
          <div class="card"><h3>Dedicated Team</h3><p>A dedicated team works exclusively on your product.</p></div>
          <div class="card"><h3>Time &amp; Material</h3><p>Flexible development for evolving requirements.</p></div>
          <div class="card"><h3>Product Partnership</h3><p>Long-term collaboration for building and scaling digital products.</p></div>
          <div class="card"><h3>Managed Technology Services</h3><p>Continuous maintenance, monitoring, testing, cloud, and support.</p></div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <span class="eyebrow">Our Approach</span>
        <h2>Understand. Build. Automate. Scale.</h2>
        <div class="grid-3">
          <div class="card"><h3>Business First</h3><p>We start with the business problem instead of starting with technology.</p></div>
          <div class="card"><h3>User First</h3><p>Every product is designed around customer and user experience.</p></div>
          <div class="card"><h3>Quality First</h3><p>Testing and quality engineering are integrated throughout the development lifecycle.</p></div>
          <div class="card"><h3>Security First</h3><p>Security, privacy, identity, encryption, and access control are considered from the beginning.</p></div>
          <div class="card"><h3>Automation First</h3><p>We automate repetitive development, testing, deployment, and operational processes wherever practical.</p></div>
          <div class="card"><h3>AI Ready</h3><p>We design modern platforms so AI can be integrated as business requirements evolve.</p></div>
        </div>
      </div>
    </section>
    <!-- SECTIONS END -->
```

- [ ] **Step 2: Verify (browser)**

Reload, `get_page_text` contains "Elasticsearch / OpenSearch", "Managed Technology Services", and "AI Ready".

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add tech stack, business models, and our-approach sections"
```

---

### Task 15: Final CTA band (spec §16)

**Files:**
- Modify: `index.html` — insert before `<!-- SECTIONS END -->`

- [ ] **Step 1: Insert final CTA**

```html
    <section class="section section-alt text-center">
      <div class="container">
        <h2>Let's Build Something Amazing Together</h2>
        <p style="max-width:600px;margin:0 auto var(--space-4);">
          Whether you need a website, mobile application, SaaS platform, AI solution, enterprise system,
          cloud infrastructure, automation, or complete product development — we can help.
          Tell us what you want to build.
        </p>
        <div class="stack" style="flex-direction:row;justify-content:center;flex-wrap:wrap;">
          <a href="contact.html" class="btn btn-primary">Start Your Project</a>
          <a href="contact.html" class="btn btn-outline-light">Request Free Consultation</a>
        </div>
      </div>
    </section>
    <!-- SECTIONS END -->
```

- [ ] **Step 2: Verify (browser)**

Reload, `get_page_text` contains "Let's Build Something Amazing Together" and "Request Free Consultation". Confirm via `read_page` this section has class `section-alt` (dark band, per design system contrast band usage).

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add final CTA band"
```

---

### Task 16: `contact.html` — full contact page with validated Formspree submission

**Files:**
- Create: `contact.html`
- Create: `js/contact.js`

**Interfaces:**
- Consumes: header/footer markup pattern from Task 7 (copy verbatim, `nav-links` items unchanged except no `#anchor` needed — link to `index.html#solutions` etc. same as before since this page isn't `index.html`); `css/*`, `js/icons.js`, `js/nav.js`.
- Produces: `js/contact.js` exposes no globals other than its `DOMContentLoaded` listener — self-contained, consumed only by `contact.html`.

- [ ] **Step 1: Write `contact.html`**

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Appmentech Technologies — Free Consultation</title>
  <meta name="description" content="Tell us what you want to build. Get a free consultation and the right web, mobile, AI, cloud, or enterprise software solution for your business.">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/components.css">
</head>
<body>

  <header class="nav">
    <div class="container nav-inner">
      <a href="index.html" class="nav-logo">Appmentech<span>.</span></a>
      <button class="nav-toggle" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <ul class="nav-links" id="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="index.html#solutions">Solutions</a></li>
        <li><a href="index.html#industries">Industries</a></li>
        <li><a href="index.html#ai-automation">AI &amp; Automation</a></li>
        <li><a href="case-studies.html">Case Studies</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="careers.html">Careers</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
    </div>
  </header>

  <main>
    <section class="section">
      <div class="container">
        <span class="eyebrow">Contact Us Today</span>
        <h1>Get in Touch</h1>
        <p style="font-style:italic;">For a Free Consultation &amp; Get the Right Solution for Your Business.</p>

        <div class="grid-2" style="align-items:start;">
          <div class="card">
            <h3>Project Details</h3>
            <form id="contact-form" novalidate>
              <div class="stack">
                <label class="form-field">
                  <span>Name *</span>
                  <input type="text" name="name" id="field-name" required>
                </label>
                <label class="form-field">
                  <span>Company</span>
                  <input type="text" name="company" id="field-company">
                </label>
                <label class="form-field">
                  <span>Email *</span>
                  <input type="email" name="email" id="field-email" required>
                </label>
                <label class="form-field">
                  <span>Phone</span>
                  <input type="tel" name="phone" id="field-phone">
                </label>
                <label class="form-field">
                  <span>Industry</span>
                  <input type="text" name="industry" id="field-industry">
                </label>
                <label class="form-field">
                  <span>Solution Required</span>
                  <input type="text" name="solution" id="field-solution">
                </label>
                <label class="form-field">
                  <span>Estimated Budget</span>
                  <input type="text" name="budget" id="field-budget">
                </label>
                <label class="form-field">
                  <span>Project Timeline</span>
                  <input type="text" name="timeline" id="field-timeline">
                </label>
                <label class="form-field">
                  <span>Project Description *</span>
                  <textarea name="description" id="field-description" rows="4" required></textarea>
                </label>
                <div id="form-message" role="status" style="display:none;font-size:14px;font-weight:600;"></div>
                <button type="submit" class="btn btn-primary" id="form-submit">Send Project Requirement</button>
              </div>
            </form>
          </div>

          <div class="card">
            <h3>Company</h3>
            <p><strong>Appmentech Technologies</strong></p>
            <p>Email: <a href="mailto:info@appmentechtech.com">info@appmentechtech.com</a></p>
            <p>Phone: <a href="tel:+911234567890">+91 12345 67890</a></p>
            <p>Website: www.appmentechtech.com</p>
            <p>Location: India / Global</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <h3>Appmentech Technologies</h3>
          <p>Your All-in-One Digital &amp; Software Solutions Partner.</p>
          <p style="font-size:13px;">Web | Mobile | AI | SaaS | Cloud | Automation | Enterprise | Quality Engineering</p>
        </div>
        <div>
          <h3 style="font-size:14px;">Company</h3>
          <ul class="footer-links">
            <li><a href="about.html">About</a></li>
            <li><a href="careers.html">Careers</a></li>
            <li><a href="case-studies.html">Case Studies</a></li>
            <li><a href="contact.html">Contact</a></li>
          </ul>
        </div>
        <div>
          <h3 style="font-size:14px;">Contact</h3>
          <ul class="footer-links">
            <li>info@appmentechtech.com</li>
            <li>+91 12345 67890</li>
            <li>India / Global</li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>&copy; 2026 Appmentech Technologies. All Rights Reserved.</span>
        <span>
          <a href="privacy.html">Privacy Policy</a> ·
          <a href="terms.html">Terms of Service</a> ·
          <a href="cookies.html">Cookie Policy</a> ·
          <a href="contact.html">Contact</a>
        </span>
      </div>
    </div>
  </footer>

  <script src="js/icons.js"></script>
  <script src="js/nav.js"></script>
  <script src="js/contact.js"></script>
</body>
</html>
```

Add minimal form-field styling to `css/components.css` (append, don't replace the file):

```css

/* Form (contact.html) */
.form-field {
  display: block;
  font-size: 13px;
  font-weight: 700;
  color: var(--color-heading);
}
.form-field span { display: block; margin-bottom: 4px; }
.form-field input, .form-field textarea {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  font-size: 14px;
  color: var(--color-body);
  background: var(--color-bg);
  font-weight: 400;
}
.form-field input:focus, .form-field textarea:focus {
  outline: none;
  border-color: var(--color-accent);
}
.form-field.invalid input, .form-field.invalid textarea {
  border-color: #DC2626;
}
```

- [ ] **Step 2: Write `js/contact.js`**

```javascript
// js/contact.js — client-side validation + Formspree fetch submit for contact.html
(function () {
  // Replace PLACEHOLDER_FORM_ID with the real Formspree form ID once created
  // at https://formspree.io — e.g. "https://formspree.io/f/abcdwxyz".
  var FORMSPREE_ENDPOINT = 'https://formspree.io/f/PLACEHOLDER_FORM_ID';

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('contact-form');
    if (!form) return;

    var messageEl = document.getElementById('form-message');
    var submitBtn = document.getElementById('form-submit');

    function showMessage(text, isError) {
      messageEl.textContent = text;
      messageEl.style.display = 'block';
      messageEl.style.color = isError ? '#DC2626' : '#16A34A';
    }

    function isValidEmail(value) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    function clearFieldErrors() {
      var invalidFields = form.querySelectorAll('.form-field.invalid');
      for (var i = 0; i < invalidFields.length; i++) {
        invalidFields[i].classList.remove('invalid');
      }
    }

    function markInvalid(input) {
      var field = input.closest('.form-field');
      if (field) field.classList.add('invalid');
    }

    function validate() {
      clearFieldErrors();
      var valid = true;

      var name = document.getElementById('field-name');
      if (!name.value.trim()) { markInvalid(name); valid = false; }

      var email = document.getElementById('field-email');
      if (!email.value.trim() || !isValidEmail(email.value.trim())) { markInvalid(email); valid = false; }

      var description = document.getElementById('field-description');
      if (!description.value.trim()) { markInvalid(description); valid = false; }

      return valid;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      messageEl.style.display = 'none';

      if (!validate()) {
        showMessage('Please fill in Name, a valid Email, and Project Description.', true);
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Sending...';

      fetch(FORMSPREE_ENDPOINT, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: new FormData(form)
      }).then(function (response) {
        if (response.ok) {
          form.reset();
          form.style.display = 'none';
          showMessage('Thank you — your project requirement has been sent. We will get back to you shortly.', false);
        } else {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Send Project Requirement';
          showMessage('Something went wrong sending your request. Please email info@appmentechtech.com directly.', true);
        }
      }).catch(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Project Requirement';
        showMessage('Something went wrong sending your request. Please email info@appmentechtech.com directly.', true);
      });
    });
  });
})();
```

- [ ] **Step 3: Verify (browser)**

Open `contact.html` in the Browser pane. Click "Send Project Requirement" with all fields empty — confirm (via `read_page`) that `#field-name`'s `.form-field` parent gained class `invalid` and `#form-message` shows the "Please fill in Name..." text. Fill Name, Email (`test@example.com`), Description via `form_input`, submit again — since `FORMSPREE_ENDPOINT` is a placeholder, expect the fetch to fail/404 and the error branch message to show ("Something went wrong... email info@appmentechtech.com directly") — confirm this is the *expected* placeholder behavior, not a bug, and note in the task's completion notes that the real Formspree ID still needs to be swapped in per spec's Open Item.

- [ ] **Step 4: Commit**

```bash
git add contact.html js/contact.js css/components.css
git commit -m "feat: add contact page with validated Formspree submission"
```

---

### Task 17: Stub pages — About, Careers, Case Studies

**Files:**
- Create: `about.html`
- Create: `careers.html`
- Create: `case-studies.html`

**Interfaces:**
- Consumes: header/footer markup from Task 7/16, `css/*`, `js/icons.js`, `js/nav.js`.

- [ ] **Step 1: Write `about.html`**

Use the same `<head>`/header/footer/script structure as `contact.html` (Task 16), with title `About Us — Appmentech Technologies`, meta description `Learn more about Appmentech Technologies, your all-in-one digital and software solutions partner. Full story coming soon.`, and this `<main>`:

```html
  <main>
    <section class="section text-center">
      <div class="container">
        <span class="eyebrow">About Us</span>
        <h1>Our Story Is Coming Soon</h1>
        <p style="max-width:520px;margin:0 auto var(--space-4);">
          We're putting together the full story of Appmentech Technologies. In the meantime,
          tell us about your project and we'll get right back to you.
        </p>
        <a href="contact.html" class="btn btn-primary">Contact Us</a>
      </div>
    </section>
  </main>
```

- [ ] **Step 2: Write `careers.html`**

Same structure, title `Careers — Appmentech Technologies`, meta description `Careers at Appmentech Technologies. Open roles coming soon.`, `<main>`:

```html
  <main>
    <section class="section text-center">
      <div class="container">
        <span class="eyebrow">Careers</span>
        <h1>Open Roles Coming Soon</h1>
        <p style="max-width:520px;margin:0 auto var(--space-4);">
          We're not listing open positions yet, but we're always glad to hear from people
          who want to build with us. Get in touch and tell us what you're looking for.
        </p>
        <a href="contact.html" class="btn btn-primary">Get in Touch</a>
      </div>
    </section>
  </main>
```

- [ ] **Step 3: Write `case-studies.html`**

Same structure, title `Case Studies — Appmentech Technologies`, meta description `Client case studies from Appmentech Technologies. Coming soon.`, `<main>`:

```html
  <main>
    <section class="section text-center">
      <div class="container">
        <span class="eyebrow">Case Studies</span>
        <h1>Case Studies Coming Soon</h1>
        <p style="max-width:520px;margin:0 auto var(--space-4);">
          We're preparing detailed case studies from our client work. In the meantime,
          tell us about your project and we'll show you how we can help.
        </p>
        <a href="contact.html" class="btn btn-primary">Start a Conversation</a>
      </div>
    </section>
  </main>
```

Each file's full `<head>` + header nav + footer + scripts block must be copied verbatim from `contact.html` (Task 16 Step 1), swapping only `<title>`, the meta description, and the `<main>` content shown above.

- [ ] **Step 4: Verify (browser)**

For each of the 3 pages: open in Browser pane, `get_page_text` contains the page's H1 text ("Our Story Is Coming Soon" / "Open Roles Coming Soon" / "Case Studies Coming Soon"), and confirm nav + footer render identically to `contact.html` (same links present).

- [ ] **Step 5: Commit**

```bash
git add about.html careers.html case-studies.html
git commit -m "feat: add about, careers, and case-studies stub pages"
```

---

### Task 18: Legal stub pages — Privacy, Terms, Cookies

**Files:**
- Create: `privacy.html`
- Create: `terms.html`
- Create: `cookies.html`

- [ ] **Step 1: Write `privacy.html`**

Same head/header/footer/script structure as Task 17, title `Privacy Policy — Appmentech Technologies`, meta description `Privacy Policy for Appmentech Technologies.`, `<main>`:

```html
  <main>
    <section class="section">
      <div class="container" style="max-width:800px;">
        <span class="eyebrow">Legal</span>
        <h1>Privacy Policy</h1>
        <p>This page will host Appmentech Technologies' full Privacy Policy. Content is being finalized.
           For any privacy-related questions in the meantime, contact us at
           <a href="mailto:info@appmentechtech.com">info@appmentechtech.com</a>.</p>
      </div>
    </section>
  </main>
```

- [ ] **Step 2: Write `terms.html`**

Same structure, title `Terms of Service — Appmentech Technologies`, meta description `Terms of Service for Appmentech Technologies.`, `<main>`:

```html
  <main>
    <section class="section">
      <div class="container" style="max-width:800px;">
        <span class="eyebrow">Legal</span>
        <h1>Terms of Service</h1>
        <p>This page will host Appmentech Technologies' full Terms of Service. Content is being finalized.
           For any questions in the meantime, contact us at
           <a href="mailto:info@appmentechtech.com">info@appmentechtech.com</a>.</p>
      </div>
    </section>
  </main>
```

- [ ] **Step 3: Write `cookies.html`**

Same structure, title `Cookie Policy — Appmentech Technologies`, meta description `Cookie Policy for Appmentech Technologies.`, `<main>`:

```html
  <main>
    <section class="section">
      <div class="container" style="max-width:800px;">
        <span class="eyebrow">Legal</span>
        <h1>Cookie Policy</h1>
        <p>This page will host Appmentech Technologies' full Cookie Policy. Content is being finalized.
           For any questions in the meantime, contact us at
           <a href="mailto:info@appmentechtech.com">info@appmentechtech.com</a>.</p>
      </div>
    </section>
  </main>
```

- [ ] **Step 4: Verify**

Run: `grep -l "Privacy Policy" privacy.html`, `grep -l "Terms of Service" terms.html`, `grep -l "Cookie Policy" cookies.html` — each should print its own filename.

- [ ] **Step 5: Commit**

```bash
git add privacy.html terms.html cookies.html
git commit -m "feat: add privacy, terms, and cookies legal stub pages"
```

---

### Task 19: SEO files — sitemap.xml and robots.txt

**Files:**
- Create: `sitemap.xml`
- Create: `robots.txt`

- [ ] **Step 1: Write `sitemap.xml`**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>https://www.appmentechtech.com/</loc><priority>1.0</priority></url>
  <url><loc>https://www.appmentechtech.com/contact.html</loc><priority>0.9</priority></url>
  <url><loc>https://www.appmentechtech.com/about.html</loc><priority>0.5</priority></url>
  <url><loc>https://www.appmentechtech.com/careers.html</loc><priority>0.5</priority></url>
  <url><loc>https://www.appmentechtech.com/case-studies.html</loc><priority>0.5</priority></url>
  <url><loc>https://www.appmentechtech.com/privacy.html</loc><priority>0.2</priority></url>
  <url><loc>https://www.appmentechtech.com/terms.html</loc><priority>0.2</priority></url>
  <url><loc>https://www.appmentechtech.com/cookies.html</loc><priority>0.2</priority></url>
</urlset>
```

- [ ] **Step 2: Write `robots.txt`**

```
User-agent: *
Allow: /

Sitemap: https://www.appmentechtech.com/sitemap.xml
```

- [ ] **Step 3: Verify**

Run: `grep -c "appmentechtech.com" sitemap.xml` — expect `8`.
Run: `grep -c "Sitemap:" robots.txt` — expect `1`.

- [ ] **Step 4: Commit**

```bash
git add sitemap.xml robots.txt
git commit -m "feat: add sitemap.xml and robots.txt"
```

---

### Task 20: Final cross-page QA pass

**Files:**
- No new files. Verification-only task; fix any issues found in the files touched by Tasks 7–19.

- [ ] **Step 1: Responsive check across breakpoints**

For `index.html` and `contact.html`: use `mcp__Claude_Browser__resize_window` with `preset: "mobile"`, `preset: "tablet"`, and `preset: "desktop"` in turn, screenshotting the hero, solutions grid, industries grid, and footer at each width. Confirm no horizontal scroll (check `document.body.scrollWidth` via `javascript_tool` equals `window.innerWidth` at each width) and no overlapping text.

- [ ] **Step 2: Internal link check**

For every page (`index.html`, `contact.html`, `about.html`, `careers.html`, `case-studies.html`, `privacy.html`, `terms.html`, `cookies.html`), use `read_page` to list all `<a href>` targets, and confirm every internal `.html` target corresponds to a file that exists in the project (`ls` to cross-check). Fix any broken links found.

- [ ] **Step 3: Icon render check**

On `index.html`, use `javascript_tool` to run `document.querySelectorAll('[data-icon]').length` and `document.querySelectorAll('[data-icon] svg').length` — the two counts must match (every icon slot actually got an SVG injected, meaning every `data-icon` name used in HTML exists in `js/icons.js`'s `ICONS` map from Task 2). If they don't match, find the unmatched `data-icon` name(s) and add the missing icon to `js/icons.js`.

- [ ] **Step 4: Console error check**

Use `read_console_messages` with `onlyErrors: true` on both `index.html` and `contact.html` after full page load — expect zero entries.

- [ ] **Step 5: Fix any issues found, then commit**

```bash
git add -A
git commit -m "fix: cross-page QA pass — responsive, links, icons, console"
```

(If Step 5 finds nothing to fix, skip the commit — note in the task result that QA passed clean.)

---

## Summary

20 tasks, in order: design tokens → icons → layout → components → nav script → accordion script → index skeleton → hero/why-us/we-build → why-choose-us table → solutions (8 cards) → AI flow → industries (12 cards) → lifecycle stepper → tech stack/business models/approach → final CTA → contact page → stub pages → legal pages → SEO files → final QA. Every homepage section in the design spec (§2–§16, §22) is covered; the Contact page covers §17; nav stubs cover the remaining §18 links; §20 SEO is applied to `index.html`/`contact.html` head tags plus `sitemap.xml`/`robots.txt`.
