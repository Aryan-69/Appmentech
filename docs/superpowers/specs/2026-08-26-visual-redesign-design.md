# Appmentech Website Visual Redesign — Design Spec

Date: 2026-08-26
Reference sites: https://www.digitide.com/ (stat strip, amber/slate palette, pill buttons, bold headline weight), https://www.weblinkindia.net/ (navy headings, green card-accent border, boxed tech-logo tiles, pill "Explore More" buttons, slim utility contact bar, icon-grid industries)
Applies to: the already-deployed site at https://appmentech.in (branch `appmentech-website`, worktree `C:\Users\lexpr\Appmentech\.worktrees\appmentech-website`)

## 1. Goal

Restyle and restructure the existing static site (built per `2026-08-25-appmentech-website-design.md`) to match a combined visual language drawn from two reference sites, without changing the tech stack, content, page set, or JS behavior contracts (accordion/nav/contact-form logic). This is a template/visual pass on top of the existing implementation, not a rebuild.

## 2. Non-goals

- No framework/build-step change — stays plain HTML/CSS/JS.
- No new pages.
- No portfolio, case-study, or testimonial sections — both reference sites have these, but building them here would require fabricated client data. Skipped, consistent with the original site's decision to avoid invented content.
- No font-family change — system font stack stays (no external font request).
- No change to `js/contact.js` validation/Formspree logic, `js/nav.js` toggle logic, or `js/accordion.js` expand/collapse logic — only the markup/classes they attach to change where noted below.

## 3. Design Tokens (replace values in `css/base.css` `:root`)

| Token | Old value | New value |
|---|---|---|
| `--color-bg` | `#F8FAFC` | `#FFFFFF` |
| `--color-surface` | `#FFFFFF` | `#FFFFFF` (unchanged) |
| `--color-accent` | `#6366F1` | `#F5A71C` |
| `--color-accent-hover` | `#4F46E5` | `#D98E12` |
| `--color-accent-light` | `#EEF2FF` | `#FDF1DC` |
| `--color-accent-light-text` | `#4338CA` | `#B36F0A` |
| `--color-heading` | `#0F172A` | `#12235B` |
| `--color-body` | `#475569` | `#475569` (unchanged) |
| `--color-muted` | `#64748B` | `#64748B` (unchanged) |
| `--color-border` | `#E2E8F0` | `#EEEEEE` |
| `--color-contrast-bg` | `#0F172A` | `#0F172A` (unchanged — dark band stays dark) |
| `--color-contrast-text` | `#F8FAFC` | `#F8FAFC` (unchanged) |
| `--color-contrast-muted` | `#94A3B8` | `#94A3B8` (unchanged) |

New tokens to add:

| Token | Value | Use |
|---|---|---|
| `--color-secondary` | `#33506E` | Stat numbers, tech-tile icon-box fill (rotates with accent/green), secondary emphasis |
| `--color-card-accent` | `#22C55E` | Top border accent line on solution/industry cards |
| `--color-utility-bg` | `#12235B` | Slim utility bar background (same as heading navy) |

`--radius` changes from `8px` to `999px` for buttons only (new `--radius-pill: 999px` token; `--radius`/`--radius-lg` stay for cards/boxes, card radius drops from `8px` to `6px` per weblinkindia reference — add `--radius-card: 6px`).

## 4. New Structural Elements

### 4.1 Utility bar (new, all 8 pages)

A new `<div class="utility-bar">` inserted immediately before the existing `<header class="nav">`, on every page (same markup on all 8 HTML files, like the nav/footer already are). Navy background (`--color-utility-bg`), white text, right-aligned, contact email + phone, 11-12px font. The nav's existing bottom border becomes `2px solid var(--color-accent)` (was a plain `1px solid var(--color-border)`).

### 4.2 Stat strip (new, `index.html` only, inserted right after the hero section)

4 tiles, real derived counts (not fabricated business-scale claims):
- **8** — Solution Categories
- **12+** — Industries Served
- **15+** — Technologies
- **7** — Step Delivery Process

Numbers in `--color-secondary` (slate-blue), bold, large (~28-32px); labels small muted text below. Laid out as a 4-up flex/grid row on a white card strip with subtle border, collapsing to 2-up then 1-up at existing breakpoints.

## 5. Restyled Existing Elements

### 5.1 Buttons (`.btn`, `.btn-primary`, `.btn-secondary`, `.btn-outline-light`)

Already pill-shaped (`border-radius: 999px` was already used in the original design) — no shape change needed, only color-token changes (amber primary, navy/white outline variants) since `--color-accent` etc. cascade automatically.

### 5.2 Tech stack section (`index.html`, spec §13)

Currently: plain `.chip-list`/`.chip` text pills grouped under category labels. **Change to:** boxed logo-style tiles — each tech item becomes a small square tile (icon-box placeholder using first 1-2 letters or a generic tech glyph, since no real logo assets exist — see §7 Open Item) with the tech name below, arranged in a wrapping flex/grid row per category, replacing the chip-pill look with a card-tile look (`border:1px solid var(--color-border); border-radius:8px; box-shadow: var(--shadow-card); padding`).

### 5.3 Solution cards (`index.html`, spec §4-10, 8 cards)

Card gets `border-top: 4px solid var(--color-card-accent)` and `border-radius: var(--radius-card)` (was `var(--radius)`). The existing `.accordion-toggle` text-link ("Show more" + arrow icon) is restyled as a small pill button: `background: var(--color-accent); color:#fff; border-radius:999px; padding:8px 16px;` displaying "Explore More »" instead of "Show more" — same `data-target`/`accordion.js` wiring, only the button's visual style and label text change. `data-label-more`/`data-label-less` attributes update to `"Explore More »"` / `"Show Less"`.

### 5.4 Industry cards (`index.html`, spec §11, 11 cards + CTA)

Drop the one-line paragraph description (`<p>Online stores, marketplaces...</p>`) from the always-visible card face — card face becomes icon + title only, more compact. The paragraph's content moves inside the accordion body, prepended above the existing bullet `<ul>`, so it's not lost — just no longer shown before clicking. Same "Explore More »" pill-button restyle as solution cards (§5.3). The 12th "And Many More" CTA card is unaffected structurally (no accordion there already) but gets the new card-accent border and token colors.

### 5.5 Business models, our-approach, lifecycle stepper, why-choose-us table

No structural change — token colors cascade (navy headings, amber accents in stepper step-numbers, green nowhere here since these aren't "card" elements with the green accent convention — green accent is reserved for solution/industry cards only, per §4 pattern, to avoid overusing the accent color).

### 5.6 Footer

No structural change, token colors cascade (navy/white already used via contrast tokens, unaffected).

## 6. Files Touched

- `css/base.css` — token value changes (§3), no new file.
- `css/layout.css` — `.section-alt`/`.eyebrow` etc. token references already point at variables, no direct edits expected unless a hardcoded hex is found during implementation (flag if so).
- `css/components.css` — card border/radius changes, new `.utility-bar` class, new `.stat-strip`/`.stat-tile` classes, new `.tech-tile` class, accordion-toggle pill-button restyle, card-accent border rule.
- `index.html`, `contact.html`, `about.html`, `careers.html`, `case-studies.html`, `privacy.html`, `terms.html`, `cookies.html` — each gets the new `.utility-bar` markup inserted before `<header class="nav">`.
- `index.html` only — stat strip insertion, tech-stack markup restructure, solution/industry card markup changes (§5.3, §5.4).

## 7. Open Items

- **Tech logo tiles have no real brand-logo assets.** Using a simple colored icon-box placeholder (first letters or a generic glyph) rather than actual React/Node/AWS/etc. logos, since sourcing real third-party trademarked logo SVGs is out of scope for this pass (licensing/attribution concerns) — placeholder tiles convey the "boxed tile" layout pattern from the reference site without using another company's IP.

## 8. QA

Same manual browser-based QA approach as the original build (local static server, live render check, contrast check, accordion/nav interaction check) — re-verify no regressions to the fixes from the original final review (contrast, form-message placement, etc.) since this pass touches the same CSS files.
