# Nav Restructure & Section Cleanup — Design Spec

Date: 2026-08-27
Reference: Kyndryl.com (mega-menu dropdown pattern), user-supplied outline, live site https://appmentech.in
Applies to: `index.html`, `css/components.css`, and (nav markup only) `contact.html`/`about.html`/`careers.html`/`case-studies.html`/`privacy.html`/`terms.html`/`cookies.html`

## 1. Goal

Restructure the site's top navigation into a 7-item bar with two mega-menu-style dropdowns and one simple-list dropdown, restyle nav-item hover to a box highlight, remove two sections from the homepage, retitle/restyle the industries section, and give the stat strip a horizontal layout with per-tile hover.

## 2. Nav — New Structure

Top-level items, in order, on all 8 pages:

1. **Home** — plain link to `index.html`
2. **What Services We Provide** — dropdown (simple list, no sub-columns). Per explicit user confirmation, this dropdown lists the **industries**, not services (kept exactly as the user specified despite the label mismatch):
   - E-Commerce & Retails Websites → `index.html#industries` (anchor; individual sub-anchors don't exist per-industry, all items link to the `#industries` section)
   - Healthcare
   - Education
   - Media & Entertainment
   - Transportation & Logistics
   - Enterprise
   - Travel & Hospitality
   - Real Estate
   - Food & Hospitality
   - Government
3. **Our Industry Solutions** — dropdown (simple list). Per explicit user confirmation, this lists the **solution categories** (kept exactly as specified, including the duplicate line):
   - Web Development
   - Mobile Applications
   - SaaS Products
   - SaaS Products *(duplicate, as given by the user — kept verbatim)*
   - AI & GenAI
   - Cloud Solutions
   - Automation
   - API & Integration
   - Testing & Quality Engineering
   - All items link to `index.html#solutions`
4. **Technology Capabilities** — mega-panel dropdown, 7 columns (one per tech category), matching the Kyndryl multi-column reference since this needs two content levels (category → items):
   - Frontend: React, Angular, Vue, HTML/CSS/JavaScript, Progressive Web Applications
   - Mobile: Android, iOS, Cross-platform frameworks
   - Backend: .NET, Java, Node.js, Python, REST, gRPC
   - Databases: PostgreSQL, MySQL, SQL Server, MongoDB, DynamoDB, Redis, Elasticsearch / OpenSearch
   - Cloud: AWS, Cloud-native architecture, Serverless, Containers, Infrastructure automation
   - AI: LLM applications, RAG, Vector databases, AI agents, Agentic workflows, AI automation
   - DevOps Solutions: Git, Jenkins, CI/CD, Docker, Infrastructure automation, Monitoring and observability
   - The panel links to `index.html#technology` (a new anchor id added to the existing "Technology Capabilities" homepage section, which currently has no id).
5. **Case Studies** — plain link to `case-studies.html` (unchanged target)
6. **About Us** — plain link to `about.html` (label changes from "About")
7. **Contact Us** — plain link to `contact.html` (label changes from "Contact")

**Dropped from the nav:** "Careers" (link removed; `careers.html` the file stays on disk, just unlinked from nav — not deleted, since deleting a page wasn't requested) and "AI & Automation" (the `#ai-automation` section stays on the homepage per user confirmation, just loses its nav link).

**Hover style:** every top-level item gets a square-box highlight — `background: var(--color-accent); color: #fff;` (or navy text if that reads better against amber — implementer picks whichever the existing `.btn-primary` text-color decision used, for consistency: navy `var(--color-heading)` on amber, per the current button contrast fix), `border-radius: 6px`, `padding: 8px 12px`, replacing the current plain-text-color-change hover.

**Dropdown behavior:** desktop — CSS `:hover`/`:focus-within` reveals the panel (no JS needed for open/close, consistent with the site's zero-dependency approach); the panel is `position: absolute`, appears directly below its trigger item, `box-shadow` + white background + border, closes when the mouse leaves. Mobile (`<1024px`, inside the existing hamburger menu) — each dropdown becomes a tap-to-expand accordion using the *same* `js/accordion.js` module already on the site (new `data-target`/`id` pairs, not new JS).

## 3. Sections Removed From Homepage

1. **Hero pill-chip strip** — the `<ul class="chip-list">` with 15 items (Websites, Web Applications, ... Cybersecurity & Identity Solutions), directly below the hero's two CTA buttons. Deleted entirely, including its wrapping content — the hero section keeps its eyebrow/h1/paragraph/buttons, just loses the chip list below.
2. **"Why Choose Us" capability table** — the entire `<section class="section section-alt">` containing the eyebrow "Why Choose Us", heading "One Partner. Your Complete Technology Journey.", intro paragraph, and the 10-row capability table. Deleted entirely — content is not moved elsewhere, per user confirmation.

## 4. Industries Section Restyle

- Heading changes: `<h2>Solutions for Every Industry</h2>` → `<h2>We Serve All Industries</h2>`. The `<span class="eyebrow">Industries</span>` above it is removed (the reference design has no eyebrow on this section, just the heading).
- Visual restyle to match the reference (plain icon-grid, no card chrome): the 11 industry cards' **closed state** loses its card border, shadow, and green `.card-accent` top border — becomes icon (centered, above) + title (centered, below) only, laid out in a `grid-template-columns: repeat(6, 1fr)` grid (2 rows of 6, wrapping to fewer columns on smaller screens per the site's existing responsive breakpoints) on a light `var(--color-bg)`-toned band.
- Clicking an industry still expands its accordion body (full bullet list, same `accordion.js` mechanic, same ids) — the accordion toggle button and body render below the plain icon+title tile when expanded, not inside a card anymore.
- The 12th "And Many More Custom Solutions" CTA tile keeps its distinct treatment (it's already not a plain industry tile) but adopts the same light-band background as the rest of the restyled section.
- A new `.industry-tile`/`.industry-tile-icon`/`.industry-tile-label` class set is added to `css/components.css` for this closed-state look; `.card-accent` is removed from the 11 industry cards' `class` attribute (Solutions section cards keep `.card-accent` — this change is industries-only).

## 5. Stat Strip

- Already horizontal (flex row) — stays that way, no structural change needed.
- Adds a per-tile hover state: `background: var(--color-accent-light); color` swap on `.stat-number`/`.stat-label` to darker/heading tones (matching the mockup: `.stat-tile:hover { background: var(--color-accent-light); } .stat-tile:hover .stat-number { color: var(--color-heading); } .stat-tile:hover .stat-label { color: var(--color-accent-light-text); font-weight: 700; }`).
- Mobile (`<640px`): the strip switches from `flex-wrap: wrap` (current behavior — 4 tiles wrap to 2x2) to `overflow-x: auto; flex-wrap: nowrap;` — a single horizontally-scrollable row, per the user's confirmed choice.

## 6. Files Touched

- `css/components.css` — nav box-hover rule, new dropdown/mega-panel CSS, new `.industry-tile*` classes, `.stat-tile:hover` rules, stat-strip mobile media-query change.
- `index.html` — nav markup rewrite (new 7-item structure + dropdown panels), hero chip-list removal, Why-Choose-Us table section removal, industries section heading/markup restyle, new `id="technology"` on the tech-capabilities section.
- `contact.html`, `about.html`, `careers.html`, `case-studies.html`, `privacy.html`, `terms.html`, `cookies.html` — nav markup rewrite only (same new 7-item structure, dropdowns link back to `index.html#anchor`).

## 7. Non-Goals

- Footer nav links are unchanged (still "About" / "Careers" / "Case Studies" / "Contact", not renamed to match the new top-nav labels) — not requested, out of scope for this pass.
- `careers.html` itself is not deleted, only unlinked from the top nav.
- No new pages, no new JS files — dropdown open/close is pure CSS (`:hover`/`:focus-within`) on desktop and the existing `accordion.js` on mobile.
- No changes to the AI-flow section's content, only its removal from the nav (already covered in §2).
- No SEO meta/title changes beyond what naturally follows from the `id="technology"` anchor addition.

## 8. Open Items

- **"Our Industry Solutions" duplicate "SaaS Products" entry** is kept verbatim per the user's outline — flagged here as a likely typo the user may want to fix later, but implemented as given, not silently deduplicated.
