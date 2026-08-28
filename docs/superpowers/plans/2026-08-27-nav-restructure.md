# Nav Restructure & Section Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Restructure the site's top nav into 7 items (2 simple-list dropdowns + 1 mega-panel dropdown), box-hover style, remove two homepage sections, retitle/restyle the industries section, and give the stat strip a hover state + horizontal-scroll mobile behavior — on the already-deployed Appmentech site.

**Architecture:** Pure CSS/HTML edits to the existing plain-HTML/CSS/JS site (no framework, no build step). Dropdown open/close reuses the existing `js/accordion.js` module (event-delegated click handler already generic on `document`) — no new JS files. New CSS lands in `css/components.css`.

**Tech Stack:** Unchanged — HTML5, CSS3 (custom properties), vanilla JS. No dependencies added.

## Global Constraints

- No new pages, no new JS files, no framework/build step.
- `js/contact.js`, `js/nav.js`, `js/icons.js` must not change. `js/accordion.js`'s existing click-delegation logic (`.accordion-toggle` → toggles `.open` on the element whose `id` matches `data-target`) is reused as-is for nav dropdown triggers — do not modify it.
- Keep the "What Services We Provide" → industries and "Our Industry Solutions" → solutions label pairing exactly as specified, including the duplicate "SaaS Products" line in the solutions dropdown — do not deduplicate or relabel.
- "Careers" nav link is removed; `careers.html` itself stays on disk, just unlinked.
- The `#ai-automation` homepage section stays; only its nav link is dropped (already true on the live site — no action needed, just don't add a nav link to it in the new nav).
- Footer nav links are NOT touched by this plan (stay "About" / "Careers" / "Case Studies" / "Contact").
- All 8 HTML pages' `<header class="nav">…</header>` block must stay byte-identical to each other, same as the existing site convention.
- No test framework — every task's verify step is a `grep`/manual browser check.
- Work from: `C:\Users\lexpr\Appmentech` (this plan is expected to execute in a fresh isolated worktree per `superpowers:using-git-worktrees` — set that up before Task 1). Commit after every task.

---

### Task 1: Nav dropdown, mega-panel, industry-tile, and stat-tile-hover CSS (`css/components.css`)

**Files:**
- Modify: `css/components.css`

**Interfaces:**
- Consumes: existing tokens (`--color-accent`, `--color-heading`, `--color-body`, `--color-border`, `--color-accent-light-text`, `--color-bg`, `--radius-card`, `--space-*`, `--shadow-card-hover`) from `css/base.css`.
- Produces: `.nav-dropdown`, `.nav-dropdown-trigger`, `.nav-dropdown-panel`, `.nav-dropdown-panel.mega`, `.industries-grid`, `.industry-tile`, `.industry-tile-icon`, `.industry-tile-label`, `.stat-tile:hover` rules — Tasks 2-5 use these class names verbatim.

- [ ] **Step 1: Replace the `.nav-links a:hover` rule with the box-hover pattern**

Find:

```css
.nav-links a {
  color: var(--color-body);
  font-weight: 600;
  font-size: 14px;
}
.nav-links a:hover { color: var(--color-accent); }
```

Replace with:

```css
.nav-links a {
  color: var(--color-body);
  font-weight: 600;
  font-size: 14px;
  padding: 8px 12px;
  border-radius: 6px;
  transition: background 0.15s ease, color 0.15s ease;
}
.nav-links a:hover,
.nav-links a:focus {
  background: var(--color-accent);
  color: var(--color-heading);
}
```

- [ ] **Step 2: Append the nav-dropdown, mega-panel, industry-tile, and stat-tile-hover rules**

Add this block at the end of `css/components.css` (after the existing `.card-accent` rule):

```css

/* Nav dropdowns (nav restructure) */
.nav-dropdown { position: relative; }
.nav-dropdown-trigger.accordion-toggle {
  background: none;
  color: var(--color-heading);
  font-weight: 600;
  font-size: 14px;
  padding: 8px 12px;
  border-radius: 6px;
}
.nav-dropdown-trigger.accordion-toggle:hover,
.nav-dropdown-trigger.accordion-toggle:focus {
  background: var(--color-accent);
}
.nav-dropdown-trigger svg { width: 12px; height: 12px; }

.nav-dropdown-panel {
  background: #fff;
  border: 1px solid var(--color-border);
  border-radius: 8px;
}
.nav-dropdown-panel a {
  display: block;
  padding: 6px 4px;
  font-size: 13px;
  color: var(--color-body);
  font-weight: 400;
}
.nav-dropdown-panel a:hover { color: var(--color-accent-light-text); }

.nav-dropdown-panel.mega h4 {
  font-size: 11px;
  color: var(--color-accent-light-text);
  text-transform: uppercase;
  margin: 0 0 6px;
  letter-spacing: 1px;
}
.nav-dropdown-panel.mega ul {
  list-style: none;
  margin: 0;
  padding: 0;
}
.nav-dropdown-panel.mega li { margin-bottom: 2px; }

@media (min-width: 1025px) {
  .nav-dropdown-panel {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 220px;
    box-shadow: var(--shadow-card-hover);
    padding: var(--space-3);
    z-index: 60;
  }
  .nav-dropdown:hover .nav-dropdown-panel,
  .nav-dropdown:focus-within .nav-dropdown-panel {
    display: block;
  }
  .nav-dropdown-panel.mega {
    display: none;
    grid-template-columns: repeat(7, 1fr);
    gap: var(--space-4);
    min-width: 760px;
    padding: var(--space-4);
  }
  .nav-dropdown:hover .nav-dropdown-panel.mega,
  .nav-dropdown:focus-within .nav-dropdown-panel.mega {
    display: grid;
  }
}

@media (max-width: 1024px) {
  .nav-dropdown-trigger {
    width: 100%;
    justify-content: space-between;
    padding: var(--space-3) var(--space-4);
    border-top: 1px solid var(--color-border);
    border-radius: 0;
  }
  .nav-dropdown-panel {
    display: block;
    border: none;
    border-radius: 0;
    padding: 0 var(--space-4);
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.2s ease;
  }
  .nav-dropdown-panel.open { max-height: 700px; padding-bottom: var(--space-2); }
  .nav-dropdown-panel.mega { display: block; padding-top: var(--space-2); }
  .nav-dropdown-panel.mega h4 { margin-top: var(--space-2); }
}

/* Industries section restyle (nav restructure) */
.industries-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: var(--space-4);
  background: #F6F8FA;
  padding: var(--space-5);
  border-radius: var(--radius-card);
}
@media (max-width: 1024px) {
  .industries-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 640px) {
  .industries-grid { grid-template-columns: repeat(2, 1fr); }
}
.industry-tile {
  text-align: center;
  padding: var(--space-3) var(--space-2);
}
.industry-tile-icon {
  width: 48px;
  height: 48px;
  margin: 0 auto var(--space-2);
  border-radius: 50%;
  background: var(--color-accent-light);
  color: var(--color-accent);
  display: flex;
  align-items: center;
  justify-content: center;
}
.industry-tile-icon svg { width: 26px; height: 26px; }
.industry-tile-label {
  font-size: 14px;
  margin-bottom: var(--space-2);
}
.industry-tile .accordion-toggle {
  font-size: 10px;
  padding: 6px 12px;
}

/* Stat tile hover (nav restructure) */
.stat-tile {
  border-radius: var(--radius);
  transition: background 0.15s ease;
  padding: var(--space-2);
}
.stat-tile:hover { background: var(--color-accent-light); }
.stat-tile:hover .stat-number { color: var(--color-heading); }
.stat-tile:hover .stat-label { color: var(--color-accent-light-text); font-weight: 700; }
```

- [ ] **Step 3: Change the stat-strip mobile behavior from wrap to horizontal scroll**

Find:

```css
.stat-strip {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-3);
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-card);
  padding: var(--space-4);
}
```

Replace with:

```css
.stat-strip {
  display: flex;
  flex-wrap: nowrap;
  gap: var(--space-3);
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-card);
  padding: var(--space-4);
  overflow-x: auto;
}
```

- [ ] **Step 4: Verify**

Run: `grep -c "\.nav-dropdown-panel\.mega {" css/components.css` — expect `1`.
Run: `grep -c "\.industries-grid {" css/components.css` — expect `1`.
Run: `grep -c "\.stat-tile:hover {" css/components.css` — expect `1`.
Run: `grep -c "flex-wrap: nowrap;" css/components.css` — expect `1`.
Run: `grep -o "{" css/components.css | wc -l` and `grep -o "}" css/components.css | wc -l` — the two counts must be equal (confirms every rule block is properly closed).

- [ ] **Step 5: Commit**

```bash
git add css/components.css
git commit -m "feat: add nav dropdown, industry-tile, and stat-hover styles"
```

---

### Task 2: Rewrite nav markup on `index.html` (new 7-item structure) + add `id="technology"`

**Files:**
- Modify: `index.html`

**Interfaces:**
- Consumes: `.nav-dropdown`, `.nav-dropdown-trigger`, `.nav-dropdown-panel`, `.nav-dropdown-panel.mega` from Task 1; `js/accordion.js`'s existing `.accordion-toggle`/`data-target`/`id` click-delegation (unchanged) — trigger buttons carry both `.nav-dropdown-trigger` and `.accordion-toggle` classes so the existing JS module picks them up with no JS changes.
- Produces: the canonical new nav markup that Task 3 copies verbatim onto the other 7 pages.

- [ ] **Step 1: Replace the `<header class="nav">…</header>` block**

Find:

```html
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
```

Replace with:

```html
  <header class="nav">
    <div class="container nav-inner">
      <a href="index.html" class="nav-logo">Appmentech<span>.</span></a>
      <button class="nav-toggle" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <ul class="nav-links" id="nav-links">
        <li><a href="index.html">Home</a></li>
        <li class="nav-dropdown">
          <button class="nav-dropdown-trigger accordion-toggle" data-target="nav-services">
            What Services We Provide <svg data-icon="arrow-right"></svg>
          </button>
          <div class="nav-dropdown-panel" id="nav-services">
            <a href="index.html#industries">E-Commerce &amp; Retails Websites</a>
            <a href="index.html#industries">Healthcare</a>
            <a href="index.html#industries">Education</a>
            <a href="index.html#industries">Media &amp; Entertainment</a>
            <a href="index.html#industries">Transportation &amp; Logistics</a>
            <a href="index.html#industries">Enterprise</a>
            <a href="index.html#industries">Travel &amp; Hospitality</a>
            <a href="index.html#industries">Real Estate</a>
            <a href="index.html#industries">Food &amp; Hospitality</a>
            <a href="index.html#industries">Government</a>
          </div>
        </li>
        <li class="nav-dropdown">
          <button class="nav-dropdown-trigger accordion-toggle" data-target="nav-solutions">
            Our Industry Solutions <svg data-icon="arrow-right"></svg>
          </button>
          <div class="nav-dropdown-panel" id="nav-solutions">
            <a href="index.html#solutions">Web Development</a>
            <a href="index.html#solutions">Mobile Applications</a>
            <a href="index.html#solutions">SaaS Products</a>
            <a href="index.html#solutions">SaaS Products</a>
            <a href="index.html#solutions">AI &amp; GenAI</a>
            <a href="index.html#solutions">Cloud Solutions</a>
            <a href="index.html#solutions">Automation</a>
            <a href="index.html#solutions">API &amp; Integration</a>
            <a href="index.html#solutions">Testing &amp; Quality Engineering</a>
          </div>
        </li>
        <li class="nav-dropdown">
          <button class="nav-dropdown-trigger accordion-toggle" data-target="nav-technology">
            Technology Capabilities <svg data-icon="arrow-right"></svg>
          </button>
          <div class="nav-dropdown-panel mega" id="nav-technology">
            <div>
              <h4>Frontend</h4>
              <ul><li><a href="index.html#technology">React</a></li><li><a href="index.html#technology">Angular</a></li><li><a href="index.html#technology">Vue</a></li><li><a href="index.html#technology">HTML/CSS/JavaScript</a></li><li><a href="index.html#technology">Progressive Web Applications</a></li></ul>
            </div>
            <div>
              <h4>Mobile</h4>
              <ul><li><a href="index.html#technology">Android</a></li><li><a href="index.html#technology">iOS</a></li><li><a href="index.html#technology">Cross-platform frameworks</a></li></ul>
            </div>
            <div>
              <h4>Backend</h4>
              <ul><li><a href="index.html#technology">.NET</a></li><li><a href="index.html#technology">Java</a></li><li><a href="index.html#technology">Node.js</a></li><li><a href="index.html#technology">Python</a></li><li><a href="index.html#technology">REST</a></li><li><a href="index.html#technology">gRPC</a></li></ul>
            </div>
            <div>
              <h4>Databases</h4>
              <ul><li><a href="index.html#technology">PostgreSQL</a></li><li><a href="index.html#technology">MySQL</a></li><li><a href="index.html#technology">SQL Server</a></li><li><a href="index.html#technology">MongoDB</a></li><li><a href="index.html#technology">DynamoDB</a></li><li><a href="index.html#technology">Redis</a></li><li><a href="index.html#technology">Elasticsearch / OpenSearch</a></li></ul>
            </div>
            <div>
              <h4>Cloud</h4>
              <ul><li><a href="index.html#technology">AWS</a></li><li><a href="index.html#technology">Cloud-native architecture</a></li><li><a href="index.html#technology">Serverless</a></li><li><a href="index.html#technology">Containers</a></li><li><a href="index.html#technology">Infrastructure automation</a></li></ul>
            </div>
            <div>
              <h4>AI</h4>
              <ul><li><a href="index.html#technology">LLM applications</a></li><li><a href="index.html#technology">RAG</a></li><li><a href="index.html#technology">Vector databases</a></li><li><a href="index.html#technology">AI agents</a></li><li><a href="index.html#technology">Agentic workflows</a></li><li><a href="index.html#technology">AI automation</a></li></ul>
            </div>
            <div>
              <h4>DevOps Solutions</h4>
              <ul><li><a href="index.html#technology">Git</a></li><li><a href="index.html#technology">Jenkins</a></li><li><a href="index.html#technology">CI/CD</a></li><li><a href="index.html#technology">Docker</a></li><li><a href="index.html#technology">Infrastructure automation</a></li><li><a href="index.html#technology">Monitoring and observability</a></li></ul>
            </div>
          </div>
        </li>
        <li><a href="case-studies.html">Case Studies</a></li>
        <li><a href="about.html">About Us</a></li>
        <li><a href="contact.html">Contact Us</a></li>
      </ul>
    </div>
  </header>
```

- [ ] **Step 2: Add `id="technology"` to the Technology Capabilities section**

Find:

```html
    <section class="section">
      <div class="container">
        <span class="eyebrow">Technology Capabilities</span>
        <h2>Modern Technology Stack</h2>
```

Replace with:

```html
    <section class="section" id="technology">
      <div class="container">
        <span class="eyebrow">Technology Capabilities</span>
        <h2>Modern Technology Stack</h2>
```

- [ ] **Step 3: Verify**

Run: `grep -c "nav-dropdown-trigger" index.html` — expect `3`.
Run: `grep -c "id=\"nav-technology\"" index.html` — expect `1`.
Run: `grep -c "<section class=\"section\" id=\"technology\">" index.html` — expect `1`.
Run: `grep -c "SaaS Products" index.html` — expect `2` (the intentional duplicate).
Run: `grep -c ">Careers<" index.html` — expect `0` (nav link removed; `careers.html` file itself is untouched by this task).
Run: `grep -c ">About Us<" index.html` and `grep -c ">Contact Us<" index.html` — expect `1` each.

- [ ] **Step 4: Commit**

```bash
git add index.html
git commit -m "feat: rewrite index.html nav with dropdown mega-menus"
```

---

### Task 3: Copy the new nav markup to the other 7 pages

**Files:**
- Modify: `contact.html`, `about.html`, `careers.html`, `case-studies.html`, `privacy.html`, `terms.html`, `cookies.html`

**Interfaces:**
- Consumes: the exact nav block from Task 2 (read it from the already-committed `index.html`, don't retype it from memory — copy verbatim to guarantee byte-identity).

- [ ] **Step 1: Read the new `<header class="nav">…</header>` block from `index.html` (Task 2's output)**

- [ ] **Step 2: Replace each of the 7 files' old nav block with that exact same new block**

Each of the 7 files currently has the same old nav structure `index.html` had before Task 2 (with `Solutions`/`Industries`/`AI & Automation`/`Careers`/`About`/`Contact` links instead of the new structure). Find and replace it with the identical new block from Task 2 in each file. Do NOT add `id="technology"` changes to these 7 files — that only applies to `index.html`'s own Technology Capabilities section (Task 2), which doesn't exist on these other pages.

- [ ] **Step 3: Verify byte-identity across all 8 pages**

Run this for each of the 8 files and confirm all 8 outputs are identical: extract each file's header block and diff. A simple proxy check: `for f in index.html contact.html about.html careers.html case-studies.html privacy.html terms.html cookies.html; do grep -c "nav-dropdown-trigger" "$f"; done` — expect `3` printed 8 times.
Run: `for f in index.html contact.html about.html careers.html case-studies.html privacy.html terms.html cookies.html; do grep -c ">About Us<" "$f"; done` — expect `1` printed 8 times.
Run: `for f in index.html contact.html about.html careers.html case-studies.html privacy.html terms.html cookies.html; do grep -c ">Contact Us<" "$f"; done` — expect `1` printed 8 times.

- [ ] **Step 4: Commit**

```bash
git add contact.html about.html careers.html case-studies.html privacy.html terms.html cookies.html
git commit -m "feat: apply new nav markup to remaining pages"
```

---

### Task 4: Remove the hero chip-list and the Why-Choose-Us table section (`index.html`)

**Files:**
- Modify: `index.html`

- [ ] **Step 1: Remove the hero's pill-chip list**

Find:

```html
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
```

Replace with:

```html
        <div class="stack" style="flex-direction:row;justify-content:center;flex-wrap:wrap;">
          <a href="contact.html" class="btn btn-primary">Let's Build Your Solution</a>
          <a href="contact.html" class="btn btn-secondary">Get a Free Consultation</a>
        </div>
      </div>
    </section>
```

- [ ] **Step 2: Remove the entire "Why Choose Us" capability-table section**

Find:

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
```

Replace with nothing (delete the entire block — the section immediately before it, "Why Businesses Choose Appmentech" 4-tile grid, and the section immediately after it, the Solutions section, become adjacent).

- [ ] **Step 3: Verify**

Run: `grep -c "class=\"chip-list\"" index.html` — expect `0` (the hero chip list is gone; note the AI-flow section and elsewhere don't use `.chip-list`, only `.chip` spans directly, so this must be 0).
Run: `grep -c "Why Choose Us" index.html` — expect `0`.
Run: `grep -c "table-responsive" index.html` — expect `0`.
Run: `grep -c "Custom Development" index.html` — expect `0`.

- [ ] **Step 4: Commit**

```bash
git add index.html
git commit -m "feat: remove hero chip strip and why-choose-us table section"
```

---

### Task 5: Restyle the Industries section (`index.html`)

**Files:**
- Modify: `index.html`

**Interfaces:**
- Consumes: `.industries-grid`, `.industry-tile`, `.industry-tile-icon`, `.industry-tile-label` from Task 1.
- Produces: none new — the 11 industry accordion `id`s (`industry-ecommerce` etc.) and their bodies are unchanged, only the surrounding wrapper markup/classes change.

- [ ] **Step 1: Replace the section's opening (heading + eyebrow removal) and grid wrapper**

Find:

```html
    <section class="section" id="industries">
      <div class="container">
        <span class="eyebrow">Industries</span>
        <h2>Solutions for Every Industry</h2>
      </div>
      <div class="container grid-3">
```

Replace with:

```html
    <section class="section" id="industries">
      <div class="container">
        <h2 class="text-center">We Serve All Industries</h2>
      </div>
      <div class="container industries-grid">
```

- [ ] **Step 2: Convert each of the 11 industry cards from `.card.card-accent` to `.industry-tile`**

For each of the 11 industries, change the wrapping `<div>` and the `<h3>` line. Apply this transformation identically for all 11 (only the `data-icon` value, industry name, and `data-target`/`id` differ — those are already correct in the file and don't change):

Find (repeated 11 times with different `data-icon`/text/`data-target`/`id` values — apply the same structural change to each):

```html
        <div class="card card-accent">
          <div class="card-icon" data-icon="ecommerce"></div>
          <h3>E-Commerce &amp; Retail</h3>
```

Becomes:

```html
        <div class="industry-tile">
          <div class="industry-tile-icon" data-icon="ecommerce"></div>
          <h3 class="industry-tile-label">E-Commerce &amp; Retail</h3>
```

Do this same `class="card card-accent"` → `class="industry-tile"` and `class="card-icon"` → `class="industry-tile-icon"` and `<h3>` → `<h3 class="industry-tile-label">` substitution for all 11 industries (E-Commerce & Retail, Healthcare & Telemedicine, Education, Media & Entertainment, Transportation & Logistics, Social Networking & Communication, Enterprise & Cloud Infrastructure, Travel & Hospitality, Real Estate & Property Management, Food & Hospitality, Government & Public Services). The `<button class="accordion-toggle" ...>` and `<div class="accordion-body" id="...">` lines that follow each one are unchanged — leave them exactly as they are.

Since the string `class="card card-accent">\n          <div class="card-icon" data-icon="` (with each industry's specific icon name right after) is unique per industry (the `data-icon` value differs each time), you can safely do 11 separate find/replace operations, one per industry, using each industry's exact `data-icon` value to disambiguate.

- [ ] **Step 3: Update the 12th "And Many More" CTA tile's background to match**

Find:

```html
        <div class="card" style="display:flex;flex-direction:column;justify-content:center;text-align:center;background:var(--color-accent-light);border-color:var(--color-accent);">
```

Replace with:

```html
        <div class="card" style="display:flex;flex-direction:column;justify-content:center;text-align:center;background:#fff;border-color:var(--color-accent);">
```

(It now sits on the new `.industries-grid`'s light `#F6F8FA` band instead of the page's white background, so switching its own card background to `#fff` keeps it visually distinct as a card within that band, rather than blending in with the old accent-tinted look designed for a white surface.)

- [ ] **Step 4: Verify**

Run: `grep -c "class=\"industry-tile\"" index.html` — expect `11`.
Run: `grep -c "class=\"card card-accent\"" index.html` — expect `8` (the 8 Solutions-section cards still legitimately use this class; only the 11 Industries cards were converted to `.industry-tile` by this task — a count of `8` confirms that and rules out having left any Industries card unconverted).
Run: `grep -c "We Serve All Industries" index.html` — expect `1`.
Run: `grep -c "Solutions for Every Industry" index.html` — expect `0`.
Run: `grep -c "industries-grid" index.html` — expect `1`.

- [ ] **Step 5: Commit**

```bash
git add index.html
git commit -m "feat: restyle industries section as icon-tile grid"
```

---

### Task 6: Final QA pass (visual regression, no code changes expected)

**Files:**
- No new files. Verification-only task; fix any issues found in files touched by Tasks 1-5.

- [ ] **Step 1: Local render check**

Start a local static file server from the worktree root (e.g. `python -m http.server 8795`), open `index.html` in a browser, and confirm:
- Nav shows exactly 7 top-level items: Home, What Services We Provide, Our Industry Solutions, Technology Capabilities, Case Studies, About Us, Contact Us.
- Hovering "What Services We Provide" and "Our Industry Solutions" reveals a simple-list dropdown; hovering "Technology Capabilities" reveals the 7-column mega-panel.
- Every nav item (including dropdown triggers) shows the amber box-highlight on hover.
- The hero no longer shows the 15-item pill-chip strip below its buttons.
- The "Why Choose Us" dark capability-table section is gone entirely (page flows directly from the "Why Businesses Choose Appmentech" 4-tile grid into the Solutions section).
- The industries section heading reads "We Serve All Industries" (no "Industries" eyebrow above it), and the 11 industries render as icon+label tiles with no card border/shadow/green-accent-line in their closed state, arranged in a 6-column grid on desktop.
- Clicking an industry tile's "Explore More »" still expands its bullet list (accordion still works).
- The stat strip is a single horizontal row; on a narrow viewport it scrolls horizontally rather than wrapping to 2x2; hovering a tile shows the amber-tinted background + text-color swap.
- No `console.error` entries.
- No horizontal scroll on the page body itself at 375px and 1440px widths (the stat-strip's own internal scroll is expected and fine — that's not page-level overflow).

- [ ] **Step 2: Mobile nav check**

Resize to a mobile width (<1024px), open the hamburger menu, tap "What Services We Provide" — confirm it expands in place (accordion behavior, using the existing `js/accordion.js`) showing its 10 links, without closing the whole mobile menu. Tap one of the links inside and confirm the mobile menu closes (existing `js/nav.js` behavior, unchanged).

- [ ] **Step 3: Cross-page nav check**

Open `contact.html`, `about.html`, and `case-studies.html` — confirm each shows the identical new nav (7 items, dropdowns working) and that "Careers" no longer appears in the nav on any page.

- [ ] **Step 4: Fix any issues found, then commit**

```bash
git add -A
git commit -m "fix: nav restructure QA pass"
```

(If nothing needs fixing, skip the commit and note in the task result that QA passed clean.)

---

## Summary

6 tasks: dropdown/industry-tile/stat-hover CSS → nav rewrite on index.html → nav copied to 7 other pages → hero-chip-strip and why-choose-us-table removal → industries section restyle → final QA. Every requirement from `docs/superpowers/specs/2026-08-27-nav-restructure-design.md` §2-§7 is covered; §8's open item (duplicate "SaaS Products" entry) is implemented exactly as specified, not silently fixed.
