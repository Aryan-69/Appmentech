# Appmentech Technologies Website — Design Spec

Date: 2026-08-25
Source content: `appmentech_technologies_website.md` (repo root)

## 1. Goal

Build a static marketing website for Appmentech Technologies from the provided content spec (`appmentech_technologies_website.md`). The site is a hybrid: a content-rich single-page homepage covering most of the spec's sections, plus a dedicated Contact page, with a small set of nav-only stub pages so the navigation described in the spec isn't broken.

## 2. Tech Stack

Plain HTML/CSS/JavaScript. No framework, no build step, no package manager. Static files servable from any host (e.g. Netlify, GitHub Pages, S3 static hosting).

## 3. File / Folder Structure

```
/
├── index.html                     Hybrid homepage
├── contact.html                   Full contact form page
├── about.html                     Stub — "coming soon"
├── careers.html                   Stub — "coming soon"
├── case-studies.html              Stub — "coming soon"
├── privacy.html                   Minimal legal stub
├── terms.html                     Minimal legal stub
├── cookies.html                   Minimal legal stub
├── sitemap.xml
├── robots.txt
├── css/
│   ├── base.css                   Reset, design tokens (colors/spacing/type), base typography
│   ├── components.css             Nav, buttons, cards, accordion, form, footer
│   └── layout.css                 Section/grid layout, responsive breakpoints
├── js/
│   ├── nav.js                     Mobile hamburger toggle, smooth-scroll anchor nav
│   └── accordion.js               "Show more" expand on solution/industry cards
├── assets/
│   └── icons/                     Inline SVG line-icon set (no photos)
└── docs/superpowers/specs/        This spec
```

## 4. Navigation

Persistent header nav, same on every page:

`Home · Solutions · Industries · AI & Automation · Case Studies · About · Careers · Contact`

- `Home`, `Solutions`, `Industries`, `AI & Automation` are in-page anchor links to sections on `index.html` (from any page, they link to `index.html#solutions` etc.).
- `Case Studies`, `About`, `Careers` link to their respective stub pages (title, one-line placeholder copy, CTA button back to Contact). This keeps every nav link resolving to a real page without inventing content the source spec doesn't provide.
- `Contact` links to `contact.html`.
- Mobile: hamburger icon toggles a collapsed menu (`js/nav.js`).

## 5. Homepage (`index.html`) Section Order

Maps to the numbered sections of `appmentech_technologies_website.md`:

1. **Hero** (spec §2) — Layout "Option A": centered content. Eyebrow line ("ONE PLATFORM. ENDLESS SOLUTIONS."), H1, subhead, primary CTA ("Let's Build Your Solution") + secondary CTA ("Get a Free Consultation"), 4-item icon strip below the fold (Web / Mobile / AI-GenAI / Cloud) drawn from "We Build" list.
2. **Why Businesses Choose Appmentech** (spec §2 sub-list) — 4 tiles: Customized Solutions, Scalable Architecture, Secure & Reliable, Future-Ready Technology.
3. **We Build** (spec §2) — icon/tag strip of all 15 items (Websites, Web Applications, … Cybersecurity & Identity Solutions).
4. **Why Choose Us** (spec §3) — the 10-row capability table rendered as a responsive table (stacks to cards on mobile).
5. **Solutions** (spec §4–§10: Web Dev, Mobile, AI & Intelligent Solutions, Cloud & Enterprise, Business Automation, Testing & QE, DevOps & CI/CD, API & System Integration) — id=`solutions`. Compact card grid, 15 cards total across the merged solution categories from §4–§10, each card: icon + title + one-line description (from the section's intro sentence). Click/tap "Show more" expands an accordion revealing that category's full sub-bullet list in place.
6. **AI example flow** (spec §5 example line) — small diagram strip: `Customer Query → AI Agent → Knowledge Base / Vector DB → Business APIs → Action → Response`.
7. **Industries** (spec §11) — id=`industries`. Compact card grid, 12 industry cards (E-Commerce & Retail … Government & Public Services) + a 13th "And Many More" card, same icon + title + one-line description + accordion "show more" pattern as Solutions.
8. **Product Development Lifecycle** (spec §12) — 7-step process (Discover → Design → Develop → Test → Deploy → Monitor → Improve), horizontal stepper on desktop, stacked list on mobile.
9. **Technology Capabilities** (spec §13) — grouped tag/chip lists by category (Frontend, Mobile, Backend, Databases, Cloud, AI, DevOps).
10. **Business Models** (spec §14) — 5 cards (Fixed Project, Dedicated Team, Time & Material, Product Partnership, Managed Technology Services).
11. **Our Approach** (spec §15) — 6 principle tiles (Business First … AI Ready).
12. **Final CTA** (spec §16) — dark contrast band (`#0F172A`), heading + both CTA buttons linking to `contact.html`.
13. **Footer** (spec §22) — brand line, service tag list, trust markers, contact snippet (email/phone), legal links (Privacy/Terms/Cookies/Contact), copyright.

Sections not separately reproduced on the homepage: §1 (Website Vision) and §19 (Homepage Short Version) are source narrative used to write the Hero/Why-Us copy, not standalone sections. §17 (Contact) lives on `contact.html`. §18 (nav) and §20 (SEO) and §21 (Brand Positioning) inform structure/meta/copy rather than being visible sections themselves — Brand Positioning short statements are used as rotating copy in the Final CTA / footer.

## 6. Visual Design System

- **Colors:** primary/background `#F8FAFC`, accent `#6366F1` (indigo — CTAs, links, icon fills, active states), headings `#0F172A`, body text `#475569`, muted text `#64748B`, borders/dividers `#E2E8F0`, card surface `#FFFFFF`, contrast band background `#0F172A` (used only for the Final CTA and Footer) with light text on it.
- **Typography:** system font stack (`-apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif`) — no external font requests, zero extra network dependency.
- **Iconography:** inline SVG line icons only (hand-picked simple stroke icons per category), no photography anywhere, no external icon font/CDN.
- **Components:** pill buttons (solid indigo primary, outline indigo secondary), card with 1px `#E2E8F0` border + subtle shadow on hover, accordion (chevron rotates, max-height transition), responsive table → stacked card list under 640px, chip/tag pills for tech stack.
- **Layout:** CSS Grid for card grids (auto-fit, minmax), Flexbox for nav/hero/footer, max content width ~1200px, standard spacing scale (4/8/16/24/32/48/64px).
- **Breakpoints:** mobile <640px, tablet 640–1024px, desktop >1024px.

## 7. Contact Page (`contact.html`)

Fields (spec §17): Name, Company, Email, Phone, Industry, Solution Required, Estimated Budget, Project Timeline, Project Description. Submit button label: "Send Project Requirement".

- Client-side validation: required fields, email format regex, phone loosely validated (digits/+/spaces).
- Submission: `<form>` posts via `fetch` to a Formspree endpoint (`https://formspree.io/f/PLACEHOLDER_FORM_ID` — a code comment marks where the real ID goes once a Formspree account/form is created). On success, form is replaced with a confirmation message; on failure, an inline error message is shown and the form stays filled in.
- Page also shows static contact details (email, phone, location) from spec §17.

## 8. Interactivity (vanilla JS, no dependencies)

- `nav.js`: hamburger toggle for mobile nav; smooth-scroll behavior for in-page anchor links; closes mobile menu on link click.
- `accordion.js`: generic expand/collapse used by Solutions and Industries cards ("Show more" / "Show less").
- Contact form fetch-submit handler (inline `<script>` on `contact.html` or a small `js/contact.js`).

## 9. SEO

- `index.html`: title = "Appmentech Technologies | All-in-One Digital & Software Solutions - Web, Mobile, AI & Cloud" (spec §20), meta description from spec §20, keywords list, Open Graph (`og:title`, `og:description`, `og:type=website`) and Twitter card meta.
- `contact.html`: its own title/description variant ("Contact Appmentech Technologies — Free Consultation").
- Stub pages: simple noindex-friendly titles, no fabricated content marked as canonical.
- Semantic heading hierarchy (single `h1` per page), `sitemap.xml` listing real pages, `robots.txt` allowing all except none (public marketing site).

## 10. Non-goals / Explicitly Out of Scope

- No CMS, no backend, no database.
- No real photography — icon/graphic only per user decision.
- No fabricated About/Careers/Case-Studies content — those are stub "coming soon" pages, not filled in with invented copy.
- No automated test suite (static markup/CSS/JS, no business logic to unit test) — QA is manual cross-browser/responsive checking.
- No dark-mode toggle, no CMS-driven content, no multi-language support.

## 11. Open Item

Formspree form ID is a placeholder until the user creates a Formspree account/form and supplies the real endpoint ID.
