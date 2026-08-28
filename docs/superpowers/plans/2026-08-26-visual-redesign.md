# Appmentech Website Visual Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restyle and lightly restructure the existing static Appmentech site (already deployed at https://appmentech.in) to match a combined visual language from two reference sites — new color tokens, a utility bar, a stat strip, boxed tech tiles, and pill-button "Explore More" accordions — without changing the tech stack, page set, or JS logic.

**Architecture:** Pure CSS/HTML edits to the existing plain-HTML/CSS/JS site. No new files except none — all changes land in existing `css/base.css`, `css/components.css`, and the 8 existing HTML pages (utility bar only) plus `index.html` (stat strip, tech tiles, card restyle).

**Tech Stack:** Unchanged — HTML5, CSS3 (custom properties), vanilla JS. No dependencies added.

## Global Constraints

- No new pages, no new JS files, no framework/build step.
- `js/contact.js`, `js/nav.js`, `js/accordion.js` logic must not change — only CSS classes/HTML markup they attach to may change, and only as specified per task.
- Do not remove or reword any existing content bullet — the industries' one-line descriptions move into the accordion body (prepended above the bullet list), they are not deleted.
- Do not apply the new `.card-accent` green border to Business Models, Our Approach, or the "Why Businesses Choose Appmentech" tiles — it's scoped to the Solutions and Industries sections only (per design spec §5.5).
- Exact color values below are final — do not approximate.
- No test framework — every task's verify step is a `grep`/manual check, same convention as the original build.
- Line endings: repo is CRLF-normalized by git on Windows — expected, not an error.
- Work from: `C:\Users\lexpr\Appmentech\.worktrees\appmentech-website` (existing git worktree, branch `appmentech-website`). Commit after every task.

---

### Task 1: Update design tokens (`css/base.css`)

**Files:**
- Modify: `css/base.css:3-34`

**Interfaces:**
- Produces: 3 new custom properties — `--color-secondary` (`#33506E`), `--color-card-accent` (`#22C55E`), `--color-utility-bg` (`#12235B`), `--radius-card` (`6px`) — that Tasks 2, 4, 5, 6 rely on by these exact names.

- [ ] **Step 1: Replace the `:root` block**

Find the existing `:root { ... }` block (lines 3-34) and replace it entirely with:

```css
:root {
  --color-bg: #FFFFFF;
  --color-surface: #FFFFFF;
  --color-accent: #F5A71C;
  --color-accent-hover: #D98E12;
  --color-accent-light: #FDF1DC;
  --color-accent-light-text: #B36F0A;
  --color-heading: #12235B;
  --color-body: #475569;
  --color-muted: #64748B;
  --color-border: #EEEEEE;
  --color-contrast-bg: #0F172A;
  --color-contrast-text: #F8FAFC;
  --color-contrast-muted: #94A3B8;
  --color-secondary: #33506E;
  --color-card-accent: #22C55E;
  --color-utility-bg: #12235B;

  --font-sans: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;

  --space-1: 4px;
  --space-2: 8px;
  --space-3: 16px;
  --space-4: 24px;
  --space-5: 32px;
  --space-6: 48px;
  --space-7: 64px;

  --radius: 8px;
  --radius-card: 6px;
  --radius-lg: 16px;
  --max-width: 1200px;

  --shadow-card: 0 1px 3px rgba(15, 23, 42, 0.08);
  --shadow-card-hover: 0 8px 24px rgba(15, 23, 42, 0.12);
}
```

- [ ] **Step 2: Verify**

Run: `grep -c "color-accent: #F5A71C" css/base.css` — expect `1`.
Run: `grep -c "color-secondary: #33506E" css/base.css` — expect `1`.
Run: `grep -c "radius-card: 6px" css/base.css` — expect `1`.

- [ ] **Step 3: Commit**

```bash
git add css/base.css
git commit -m "feat: update design tokens for visual redesign"
```

---

### Task 2: New component styles + nav/accordion restyle (`css/components.css`)

**Files:**
- Modify: `css/components.css`

**Interfaces:**
- Consumes: `--color-secondary`, `--color-card-accent`, `--color-utility-bg`, `--radius-card` from Task 1.
- Produces: `.utility-bar`, `.utility-bar-inner`, `.stat-strip`, `.stat-tile`, `.stat-number`, `.stat-label`, `.tech-tile-list`, `.tech-tile`, `.tech-tile-icon`, `.tech-tile-label`, `.card-accent` — Tasks 3-7 use these class names verbatim.

- [ ] **Step 1: Change the nav border-bottom**

In the `.nav { ... }` rule (near the top of the file), find:

```css
.nav {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(248, 250, 252, 0.92);
  backdrop-filter: blur(6px);
  border-bottom: 1px solid var(--color-border);
}
```

Replace the `border-bottom` line with:

```css
.nav {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(6px);
  border-bottom: 2px solid var(--color-accent);
}
```

(Note: `background` also changes from the old light-slate rgba to white rgba, matching the new white page background.)

- [ ] **Step 2: Restyle `.accordion-toggle` as a pill button**

Find the existing `.accordion-toggle` rule:

```css
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
```

Replace it with:

```css
.accordion-toggle {
  background: var(--color-accent);
  border: none;
  color: #fff;
  font-weight: 700;
  font-size: 11px;
  cursor: pointer;
  padding: 8px 16px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: background 0.15s ease;
}
.accordion-toggle:hover { background: var(--color-accent-hover); }
```

Leave the `.accordion-toggle svg` and `.accordion-toggle.open > svg` rules directly below it unchanged.

- [ ] **Step 3: Append new component rules**

Add this block at the end of the file (after the existing `.form-field.invalid` rule):

```css

/* Utility bar (redesign) */
.utility-bar {
  background: var(--color-utility-bg);
  color: #fff;
}
.utility-bar-inner {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-4);
  padding: 6px 0;
  font-size: 11px;
}
@media (max-width: 640px) {
  .utility-bar-inner { justify-content: center; gap: var(--space-3); flex-wrap: wrap; }
}

/* Stat strip (redesign) */
.stat-strip {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-card);
  padding: var(--space-4);
}
.stat-tile {
  flex: 1;
  min-width: 130px;
  text-align: center;
}
.stat-tile .stat-number {
  font-size: 30px;
  font-weight: 700;
  color: var(--color-secondary);
  line-height: 1.1;
}
.stat-tile .stat-label {
  font-size: 12px;
  color: var(--color-muted);
  margin-top: 4px;
}

/* Tech tiles (redesign — replaces chip-list for the tech stack section) */
.tech-tile-list {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-2);
  list-style: none;
  margin: var(--space-2) 0 0 0;
  padding: 0;
}
.tech-tile {
  width: 84px;
  text-align: center;
  padding: 10px 4px;
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  box-shadow: var(--shadow-card);
  background: var(--color-surface);
}
.tech-tile-icon {
  display: block;
  width: 32px;
  height: 32px;
  margin: 0 auto 6px;
  border-radius: 6px;
  background: var(--color-accent);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  line-height: 32px;
}
.tech-tile-label {
  display: block;
  font-size: 10px;
  color: var(--color-heading);
  font-weight: 700;
}

/* Card accent border (Solutions + Industries cards only — see plan Global Constraints) */
.card-accent {
  border-top: 4px solid var(--color-card-accent);
  border-radius: var(--radius-card);
}
```

- [ ] **Step 4: Verify**

Run: `grep -c "\.utility-bar {" css/components.css` — expect `1`.
Run: `grep -c "\.stat-strip {" css/components.css` — expect `1`.
Run: `grep -c "\.tech-tile {" css/components.css` — expect `1`.
Run: `grep -c "\.card-accent {" css/components.css` — expect `1`.
Run: `grep -c "border-bottom: 2px solid var(--color-accent);" css/components.css` — expect `1`.

- [ ] **Step 5: Commit**

```bash
git add css/components.css
git commit -m "feat: add utility bar, stat strip, tech tile, and card-accent styles"
```

---

### Task 3: Insert utility bar markup on all 8 pages

**Files:**
- Modify: `index.html`, `contact.html`, `about.html`, `careers.html`, `case-studies.html`, `privacy.html`, `terms.html`, `cookies.html`

**Interfaces:**
- Consumes: `.utility-bar`, `.utility-bar-inner` from Task 2.

- [ ] **Step 1: Insert the utility bar before the nav on each of the 8 files**

In each file, find:

```html
<body>

  <header class="nav">
```

Replace with:

```html
<body>

  <div class="utility-bar">
    <div class="container utility-bar-inner">
      <span>info@appmentechtech.com</span>
      <span>+91 12345 67890</span>
    </div>
  </div>

  <header class="nav">
```

Do this identically in all 8 files listed above.

- [ ] **Step 2: Verify**

Run: `grep -l "utility-bar-inner" index.html contact.html about.html careers.html case-studies.html privacy.html terms.html cookies.html` — expect all 8 filenames printed.
Run: `for f in index.html contact.html about.html careers.html case-studies.html privacy.html terms.html cookies.html; do grep -c "class=\"utility-bar\"" "$f"; done` — expect `1` printed 8 times (one per file).

- [ ] **Step 3: Commit**

```bash
git add index.html contact.html about.html careers.html case-studies.html privacy.html terms.html cookies.html
git commit -m "feat: add utility contact bar to all pages"
```

---

### Task 4: Add stat strip to `index.html`

**Files:**
- Modify: `index.html`

**Interfaces:**
- Consumes: `.stat-strip`, `.stat-tile`, `.stat-number`, `.stat-label` from Task 2.

- [ ] **Step 1: Insert the stat strip between the hero section and the "Why Businesses Choose" section**

Find (the hero section's closing tag followed immediately by the next section's opening):

```html
        </ul>
      </div>
    </section>

    <section class="section" style="padding-top:0;">
      <div class="container">
        <h2 class="text-center">Why Businesses Choose Appmentech</h2>
```

Replace with:

```html
        </ul>
      </div>
    </section>

    <section class="section" style="padding-top:0;padding-bottom:0;">
      <div class="container">
        <div class="stat-strip">
          <div class="stat-tile"><div class="stat-number">8</div><div class="stat-label">Solution Categories</div></div>
          <div class="stat-tile"><div class="stat-number">12+</div><div class="stat-label">Industries Served</div></div>
          <div class="stat-tile"><div class="stat-number">15+</div><div class="stat-label">Technologies</div></div>
          <div class="stat-tile"><div class="stat-number">7</div><div class="stat-label">Step Delivery Process</div></div>
        </div>
      </div>
    </section>

    <section class="section" style="padding-top:0;">
      <div class="container">
        <h2 class="text-center">Why Businesses Choose Appmentech</h2>
```

- [ ] **Step 2: Verify**

Run: `grep -c "class=\"stat-strip\"" index.html` — expect `1`.
Run: `grep -c "Solution Categories" index.html` — expect `1`.
Run: `grep -c "Step Delivery Process" index.html` — expect `1`.

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: add stat strip section to homepage"
```

---

### Task 5: Restructure tech-stack section into boxed tiles (`index.html`)

**Files:**
- Modify: `index.html` (the "Technology Capabilities" section)

**Interfaces:**
- Consumes: `.tech-tile-list`, `.tech-tile`, `.tech-tile-icon`, `.tech-tile-label` from Task 2.
- Produces: no new ids/classes other than the above — Task 8 (QA) checks these render.

- [ ] **Step 1: Replace all 7 `<ul class="chip-list">...</ul>` blocks inside the Technology Capabilities section**

Find this entire block (the whole `<div class="stack">...</div>` inside the "Technology Capabilities" section — locate it via `<span class="eyebrow">Technology Capabilities</span>`):

```html
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
```

Replace with (each `<li class="tech-tile">` carries a 2-letter icon abbreviation and the full name as its label — abbreviations given explicitly below, do not invent different ones):

```html
        <div class="stack">
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Frontend</h3>
            <ul class="tech-tile-list">
              <li class="tech-tile"><span class="tech-tile-icon">RE</span><span class="tech-tile-label">React</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">AN</span><span class="tech-tile-label">Angular</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">VU</span><span class="tech-tile-label">Vue</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">H5</span><span class="tech-tile-label">HTML/CSS/JavaScript</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">PW</span><span class="tech-tile-label">Progressive Web Applications</span></li>
            </ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Mobile</h3>
            <ul class="tech-tile-list">
              <li class="tech-tile"><span class="tech-tile-icon">AN</span><span class="tech-tile-label">Android</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">IO</span><span class="tech-tile-label">iOS</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">CP</span><span class="tech-tile-label">Cross-platform frameworks</span></li>
            </ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Backend</h3>
            <ul class="tech-tile-list">
              <li class="tech-tile"><span class="tech-tile-icon">NE</span><span class="tech-tile-label">.NET</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">JA</span><span class="tech-tile-label">Java</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">NO</span><span class="tech-tile-label">Node.js</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">PY</span><span class="tech-tile-label">Python</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">RE</span><span class="tech-tile-label">REST</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">GR</span><span class="tech-tile-label">gRPC</span></li>
            </ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Databases</h3>
            <ul class="tech-tile-list">
              <li class="tech-tile"><span class="tech-tile-icon">PG</span><span class="tech-tile-label">PostgreSQL</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">MY</span><span class="tech-tile-label">MySQL</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">SQ</span><span class="tech-tile-label">SQL Server</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">MO</span><span class="tech-tile-label">MongoDB</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">DY</span><span class="tech-tile-label">DynamoDB</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">RD</span><span class="tech-tile-label">Redis</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">ES</span><span class="tech-tile-label">Elasticsearch / OpenSearch</span></li>
            </ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">Cloud</h3>
            <ul class="tech-tile-list">
              <li class="tech-tile"><span class="tech-tile-icon">AW</span><span class="tech-tile-label">AWS</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">CN</span><span class="tech-tile-label">Cloud-native architecture</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">SV</span><span class="tech-tile-label">Serverless</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">CT</span><span class="tech-tile-label">Containers</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">IA</span><span class="tech-tile-label">Infrastructure automation</span></li>
            </ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">AI</h3>
            <ul class="tech-tile-list">
              <li class="tech-tile"><span class="tech-tile-icon">LL</span><span class="tech-tile-label">LLM applications</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">RA</span><span class="tech-tile-label">RAG</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">VD</span><span class="tech-tile-label">Vector databases</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">AG</span><span class="tech-tile-label">AI agents</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">AW</span><span class="tech-tile-label">Agentic workflows</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">AA</span><span class="tech-tile-label">AI automation</span></li>
            </ul>
          </div>
          <div>
            <h3 style="font-size:14px;color:var(--color-muted);text-transform:uppercase;">DevOps</h3>
            <ul class="tech-tile-list">
              <li class="tech-tile"><span class="tech-tile-icon">GI</span><span class="tech-tile-label">Git</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">JK</span><span class="tech-tile-label">Jenkins</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">CI</span><span class="tech-tile-label">CI/CD</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">DK</span><span class="tech-tile-label">Docker</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">IA</span><span class="tech-tile-label">Infrastructure automation</span></li>
              <li class="tech-tile"><span class="tech-tile-icon">MO</span><span class="tech-tile-label">Monitoring and observability</span></li>
            </ul>
          </div>
        </div>
```

- [ ] **Step 2: Verify**

Run: `grep -c "tech-tile-list" index.html` — expect `7` (one per category).
Run: `grep -c "class=\"chip-list\"" index.html` — expect `1` (only the hero's "We Build" chip strip remains — that one is NOT part of this task and must stay untouched).
Run: `grep -c "Elasticsearch / OpenSearch" index.html` — expect `1`.

- [ ] **Step 3: Commit**

```bash
git add index.html
git commit -m "feat: restructure tech stack section into boxed tiles"
```

---

### Task 6: Restyle Solutions section cards (8 cards) — accent border + pill-button labels

**Files:**
- Modify: `index.html` (the `id="solutions"` section)

**Interfaces:**
- Consumes: `.card-accent` from Task 2 (visual style comes from CSS alone since `.accordion-toggle` is already globally restyled by Task 2 — this task only adds the `card-accent` class and swaps button label text).

- [ ] **Step 1: Add `card-accent` class to each of the 8 solution cards**

Within the Solutions section only (between `<section class="section" id="solutions">` and the section's closing `</section>` — do NOT touch any other section), change every occurrence of:

```html
        <div class="card">
```

to:

```html
        <div class="card card-accent">
```

There are exactly 8 such occurrences in this section (one per solution category: Web Development, Mobile Application Development, AI & Intelligent Solutions, Cloud & Enterprise Solutions, Business Automation, Software Testing & Quality Engineering, DevOps & CI/CD, API & System Integration).

- [ ] **Step 2: Swap the button label text (applies to all 19 accordion buttons site-wide — safe, this exact text pattern is used nowhere else)**

Replace every occurrence of:

```html
data-label-more="Show more" data-label-less="Show less">
            <span class="accordion-toggle-text">Show more</span> <svg data-icon="arrow-right"></svg>
```

with:

```html
data-label-more="Explore More &raquo;" data-label-less="Show Less">
            <span class="accordion-toggle-text">Explore More &raquo;</span> <svg data-icon="arrow-right"></svg>
```

This pattern occurs identically 19 times in `index.html` (8 in Solutions, 11 in Industries) — replace all occurrences in one pass.

- [ ] **Step 3: Verify**

Run: `grep -c "card card-accent" index.html` — expect `8` after this task (Task 7 adds 11 more for a total of 19 later — don't be alarmed it's not 19 yet).
Run: `grep -c "Explore More &amp;raquo;" index.html` — if your editor auto-escapes `&raquo;` differently, instead run `grep -c "Explore More" index.html` — expect `19` (Step 2's replace-all covers both Solutions and Industries buttons in one pass, even though Task 7 handles the Industries *paragraph* removal separately).
Run: `grep -c "data-label-less=\"Show Less\"" index.html` — expect `19`.

- [ ] **Step 4: Commit**

```bash
git add index.html
git commit -m "feat: restyle solutions cards with accent border and pill-button labels"
```

---

### Task 7: Restyle Industries section cards (11 cards) — accent border + move description into accordion

**Files:**
- Modify: `index.html` (the `id="industries"` section)

**Interfaces:**
- Consumes: `.card-accent` from Task 2. Depends on Task 6 Step 2 already having swapped all 19 buttons' labels (if executing out of order, verify Task 6 landed first).

- [ ] **Step 1: Add `card-accent` class to each of the 11 industry accordion cards (not the 12th CTA card)**

Within the Industries section (`<section class="section" id="industries">` ... closing `</section>`), change every occurrence of:

```html
        <div class="card">
```

to:

```html
        <div class="card card-accent">
```

There are exactly 11 such occurrences (E-Commerce & Retail, Healthcare & Telemedicine, Education, Media & Entertainment, Transportation & Logistics, Social Networking & Communication, Enterprise & Cloud Infrastructure, Travel & Hospitality, Real Estate & Property Management, Food & Hospitality, Government & Public Services). Do NOT touch the 12th "And Many More Custom Solutions" CTA card — it already has a distinct `<div class="card" style="...">` line with inline styles, leave it as-is (per design spec §5.4, it gets the new token colors automatically via cascade, no markup change needed).

- [ ] **Step 2: Move each card's one-line description into its accordion body, prepended as the first bullet**

For each of the 11 cards, remove its `<p>...</p>` line (immediately after the `<h3>`) and add its text as a new first `<li>` inside that card's `<ul>` in the accordion body. Apply exactly these 11 changes:

**E-Commerce & Retail** — find:
```html
          <p>Online stores, marketplaces, inventory management, secure payments &amp; more.</p>
```
Delete this line. Find:
```html
            <ul><li>Online stores</li>
```
Replace with:
```html
            <ul><li>Online stores, marketplaces, inventory management, secure payments &amp; more.</li><li>Online stores</li>
```

**Healthcare & Telemedicine** — find:
```html
          <p>Patient portals, teleconsultation, EHR systems, appointment booking &amp; healthcare apps.</p>
```
Delete this line. Find:
```html
            <ul><li>Patient portals</li>
```
Replace with:
```html
            <ul><li>Patient portals, teleconsultation, EHR systems, appointment booking &amp; healthcare apps.</li><li>Patient portals</li>
```

**Education** — find:
```html
          <p>E-learning platforms, virtual classrooms, interactive learning &amp; automated study suites.</p>
```
Delete this line. Find:
```html
            <ul><li>E-learning platforms</li>
```
Replace with:
```html
            <ul><li>E-learning platforms, virtual classrooms, interactive learning &amp; automated study suites.</li><li>E-learning platforms</li>
```

**Media & Entertainment** — find:
```html
          <p>Streaming platforms, content management, OTT apps, live events &amp; more.</p>
```
Delete this line. Find:
```html
            <ul><li>Streaming platforms</li>
```
Replace with:
```html
            <ul><li>Streaming platforms, content management, OTT apps, live events &amp; more.</li><li>Streaming platforms</li>
```

**Transportation & Logistics** — find:
```html
          <p>GPS navigation, real-time fleet tracking, route optimization, delivery &amp; logistic solutions.</p>
```
Delete this line. Find:
```html
            <ul><li>GPS navigation</li>
```
Replace with:
```html
            <ul><li>GPS navigation, real-time fleet tracking, route optimization, delivery &amp; logistic solutions.</li><li>GPS navigation</li>
```

**Social Networking & Communication** — find:
```html
          <p>Instant messaging, professional networking, content sharing, video conferencing &amp; more.</p>
```
Delete this line. Find:
```html
            <ul><li>Instant messaging</li>
```
Replace with:
```html
            <ul><li>Instant messaging, professional networking, content sharing, video conferencing &amp; more.</li><li>Instant messaging</li>
```

**Enterprise & Cloud Infrastructure** — find:
```html
          <p>Workflow automation, CRM, project monitoring, cloud resources &amp; Salesforce solutions.</p>
```
Delete this line. Find:
```html
            <ul><li>Workflow automation</li>
```
Replace with:
```html
            <ul><li>Workflow automation, CRM, project monitoring, cloud resources &amp; Salesforce solutions.</li><li>Workflow automation</li>
```

**Travel & Hospitality** — find:
```html
          <p>Hotel &amp; flight booking, digital ticketing, local guide apps, home-sharing services &amp; more.</p>
```
Delete this line. Find:
```html
            <ul><li>Hotel booking</li>
```
Replace with:
```html
            <ul><li>Hotel &amp; flight booking, digital ticketing, local guide apps, home-sharing services &amp; more.</li><li>Hotel booking</li>
```

**Real Estate & Property Management** — find:
```html
          <p>Property listings, lease management, maintenance tracking, tenant portals &amp; more.</p>
```
Delete this line. Find:
```html
            <ul><li>Property listing</li>
```
Replace with:
```html
            <ul><li>Property listings, lease management, maintenance tracking, tenant portals &amp; more.</li><li>Property listing</li>
```

**Food & Hospitality** — find:
```html
          <p>Restaurant reservation systems, digital QR menus, order management &amp; customer loyalty apps.</p>
```
Delete this line. Find:
```html
            <ul><li>Restaurant reservation</li>
```
Replace with:
```html
            <ul><li>Restaurant reservation systems, digital QR menus, order management &amp; customer loyalty apps.</li><li>Restaurant reservation</li>
```

**Government & Public Services** — find:
```html
          <p>Tax filing portals, civic engagement tools, identity verification, public transport updates &amp; more.</p>
```
Delete this line. Find:
```html
            <ul><li>Tax filing portals</li>
```
Replace with:
```html
            <ul><li>Tax filing portals, civic engagement tools, identity verification, public transport updates &amp; more.</li><li>Tax filing portals</li>
```

- [ ] **Step 3: Verify**

Run: `grep -c "card card-accent" index.html` — expect `19` now (8 from Task 6 + 11 from this task).
Run: `grep -c "<p>Online stores, marketplaces" index.html` — expect `0` (paragraph removed from card face).
Run: `grep -c "<li>Online stores, marketplaces, inventory management, secure payments &amp; more.</li>" index.html` — expect `1` (now inside the accordion body).
Run: a spot-check for one more: `grep -c "<li>Tax filing portals, civic engagement tools" index.html` — expect `1`.

- [ ] **Step 4: Commit**

```bash
git add index.html
git commit -m "feat: restyle industries cards with accent border and move descriptions into accordion"
```

---

### Task 8: Final QA pass (visual regression, no code changes expected)

**Files:**
- No new files. Verification-only task; fix any issues found in files touched by Tasks 1-7.

- [ ] **Step 1: Local render check**

Start a local static file server from the worktree root (e.g. `python -m http.server 8791`), open `index.html` and `contact.html` in a browser, and confirm:
- White background, amber CTAs, navy headings render (not the old indigo/light-slate palette).
- Utility bar visible above the nav on every page (spot-check `index.html`, `contact.html`, `about.html`).
- Nav has a visible amber bottom border.
- Stat strip renders 4 tiles with correct numbers/labels between the hero and "Why Businesses Choose Appmentech".
- Tech-stack section renders boxed tiles (icon-box + label), not plain pill chips — for all 7 categories.
- Solutions and Industries cards show a green top border and an amber "Explore More »" pill button (not a plain "Show more" text link).
- Clicking "Explore More »" on a Solutions card still expands its bullet list (accordion still works) and the button label flips to "Show Less".
- Clicking "Explore More »" on an Industries card expands a bullet list whose FIRST item is the original one-line description, followed by the original bullets.
- No `console.error` entries on either page.
- No horizontal scroll at 375px and 1440px viewport widths.

- [ ] **Step 2: Confirm untouched elements are actually untouched**

- Business Models cards, Our Approach cards, and "Why Businesses Choose Appmentech" tiles do NOT have a green top border (they should still look like plain `.card` — only Solutions/Industries cards get `.card-accent`).
- The hero's 15-item "We Build" chip strip is unchanged (still plain pill chips, not tiles — only the Technology Capabilities section changed to tiles).
- `js/contact.js` form validation still works on `contact.html` (submit empty form, confirm invalid-field styling and message still appear).

- [ ] **Step 3: Fix any issues found, then commit**

```bash
git add -A
git commit -m "fix: visual redesign QA pass"
```

(If nothing needs fixing, skip the commit and note in the task result that QA passed clean.)

---

## Summary

8 tasks: tokens → new component CSS (utility bar/stat strip/tech tiles/card accent/nav+accordion restyle) → utility bar on all 8 pages → stat strip → tech tiles → solutions card restyle → industries card restyle → final QA. Every requirement from `docs/superpowers/specs/2026-08-26-visual-redesign-design.md` §3-§6 is covered; §7's open item (no real tech logos, letter-tile placeholders used instead) is implemented as specified.

**After this plan's tasks are reviewed clean, redeploy to production:** the site is already live at https://appmentech.in via Hostinger (deployed in an earlier session using the `hostinger-api` MCP connector's `hosting_deployStaticWebsite` tool). Re-run that same deploy (zip the updated worktree's `index.html`, `contact.html`, `about.html`, `careers.html`, `case-studies.html`, `privacy.html`, `terms.html`, `cookies.html`, `sitemap.xml`, `robots.txt`, `css/`, `js/` and call `hosting_deployStaticWebsite` with `domain: "appmentech.in"`) from the controlling session once this plan is complete — this step is not part of the subagent task loop since it requires the controller's own Hostinger MCP access.
