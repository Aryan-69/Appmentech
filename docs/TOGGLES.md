# Toggle registry

Everything on this list is **switched off, not deleted.** Each item is still in the
repository, wrapped in a marker you can find by searching for its `id`.

The site now sells two things — **web development** and **CRM development** — to a named
set of industries. Everything that contradicted that focus, or made a claim a small team
cannot back up, was toggled off rather than removed, so any of it can come back the
day it becomes true.

---

## How a toggle looks

**In HTML and XML**

```html
<!-- TOGGLE:OFF id="hero-vanity-counters" reason="..." See docs/TOGGLES.md to re-enable. -->
<!--
  ...the original markup, untouched...
-->
<!-- /TOGGLE:OFF id="hero-vanity-counters" -->
```

**In JavaScript**

```js
/* TOGGLE:OFF id="contact-form-old-option-lists"
   reason: ...

   ...the original code, untouched...

   /TOGGLE:OFF id="contact-form-old-option-lists" */
```

**A whole page**

The file is untouched and still loads at its URL. It is simply not linked from the
nav, the footer or `sitemap.xml`, and it carries `<meta name="robots" content="noindex,follow">`
directly under a `PAGE TOGGLED OFF` note in its `<head>`.

### To switch something back on

1. `grep -rn 'id="<the-id>"' .` to find every copy of it.
2. Delete the opening marker line, the bare `<!--` and `-->` lines, and the closing marker.
3. For a page: delete its `robots` meta and its `PAGE TOGGLED OFF` note, restore its
   `sitemap.xml` entry, and restore its nav and footer links (both are in the
   `nav-off-niche-services` and `footer-off-niche-services` toggles).
4. Bump the `?v=` number on any CSS or JS you touched so browsers reload it.

Several toggles are repeated on **every page**, because the nav and footer are copied
into each file rather than templated. Change one, change all of them.

---

## 1. Off-niche services

| id | Where | What it was |
|---|---|---|
| `nav-off-niche-services` | every page | Nav links to Mobile App Development, AI &amp; GenAI, Software Testing &amp; QA, Cloud &amp; DevOps, SaaS Products, Automation, API &amp; Integration |
| `footer-off-niche-services` | every page | The same four service pages in the footer |
| `solution-cards-off-niche` | `index.html` | Home page cards 02–08: Mobile, AI, Cloud &amp; Enterprise, Business Automation, Testing &amp; QA, DevOps &amp; CI/CD, API &amp; Integration |
| `section-ai-automation` | `index.html` | The "From Query to Action, Automatically" AI chain section |
| `approach-ai-ready-card` | `index.html` | The "AI Ready" card in Our Approach |
| `about-ai-ready-principle` | `about.html` | The "AI ready" principle card |
| `about-six-service-areas` | `about.html` | The six-service "What We Build" list |
| `about-quality-ai-tail` | `about.html` | The paragraph ending on AI systems |
| `about-who-we-are-everything` | `about.html` | "Websites and mobile applications, SaaS, e-commerce, internal tools, cloud infrastructure and automation" |
| `about-hero-sub-off-niche` | `about.html` | "custom web, mobile, AI and cloud software" |
| `nav-off-niche-tech-columns` | every page | Mega-menu columns for Mobile, Cloud, AI and DevOps |
| `auto-reply-eight-categories` | `submit.php` | The eight categories listed in the confirmation email |
| `sitemap-off-niche-service-pages` | `sitemap.xml` | Sitemap entries for the four toggled-off service pages |

**Pages toggled off** (file kept, `noindex`, unlinked, out of the sitemap):

- `services/mobile-app-development.html`
- `services/ai-genai-solutions.html`
- `services/software-testing-qa.html`
- `services/cloud-devops.html`
- `careers.html` — it advertises no roles

Testing, cloud, DevOps and integration work is still mentioned once on the home page
and once on the About page, as work taken on for existing clients and agency partners.
That sentence is deliberately not a service card: it does not compete for attention
with web and CRM, and it does not promise a page that has to rank.

## 2. Off-niche industries

| id | Where | What it was |
|---|---|---|
| `nav-off-niche-industries` | every page | Nav links to Transportation &amp; Logistics, Enterprise, Government |
| `industry-tiles-original-twelve` | `index.html` | All twelve original tiles, kept verbatim |
| `contact-form-old-option-lists` | `js/contact.js` | The old Industry and Solution dropdown options |
| `industry-tiles-media-sports` | `index.html` | The Media &amp; Entertainment and Sports tiles |
| `nav-industries-media-sports` | every page | Nav links to Media &amp; Entertainment and Sports |

The four claims that were the real credibility problem — **Government &amp; Public
Services, Social Networking, Transportation &amp; Logistics (fleet), Enterprise &amp; Cloud
Infrastructure** — are gone from the visible site.

**Media &amp; Entertainment and Sports came off later**, leaving nine live tiles. Neither
had work behind it, and "industries we already know how to build for" only holds if
every tile is one we do. Both tiles are intact under `industry-tiles-media-sports`,
their nav links under `nav-industries-media-sports`; switch both back together the day
either becomes true.

**E-Commerce &amp; Retail is kept as an eleventh tile**, alongside the ten target
industries, because Cookrie is the one real case study behind it and
`industries/ecommerce.html` is the only industry landing page. Its tile sits after
Retail Point of Sale &amp; Billing and before the Local &amp; Professional catch-all, and it
is the tile that links to that page. Its copy was rewritten for the niche — custom
stores, marketplaces, checkout, order management and migration — rather than restored
verbatim from `industry-tiles-original-twelve`.

## 3. Claims that could not be backed up

| id | Where | What it was |
|---|---|---|
| `hero-vanity-counters` | `index.html` | "8 Solution Categories · 12+ Industries Served · 15+ Technologies · 7 Step Delivery Process" |
| `hero-badge-endless-solutions` | `index.html` | The hero badge "One Platform. Endless Solutions." |
| `solutions-head-everything-you-need` | `index.html` | "Our Digital Solutions" / "Everything You Need, One Partner" |
| `industries-head-serve-all` | `index.html` | "Industry Coverage" / "We Serve All Industries" |
| `hero-all-in-one-headline` | `index.html` | "Custom Web, Mobile &amp; AI Development" / "Your all-in-one digital and software solutions partner" |
| `footer-taglines-all-in-one` | every page | "Your All-in-One Digital &amp; Software Solutions Partner", the eight-capability pipe list, and "On-Time Delivery Always" |
| `auto-reply-all-in-one-tagline` | `submit.php` | The same tagline in the confirmation email |
| `footer-social-dead-links` | `index.html` | Four social icons all pointing at `#` |

The counters counted menu items, not delivered work. Any visitor who reads
"12+ Industries Served" and then finds one case study has learned something you did
not want them to learn. They are replaced by a strip of capability chips, which claim
nothing about volume.

Put the social icons back the moment the accounts exist, with real URLs.

## 4. "Free consultation"

| id | Where | What it was |
|---|---|---|
| `hero-free-consultation-cta` | `index.html` | "Get a Free Consultation" |
| `contact-free-consultation-lead` | `contact.html` | "For a Free Consultation &amp; Get the Right Solution for Your Business." |

A consultation asks the buyer for their time before offering anything. "Get a Free
Website Review" offers them something first. Same effort for you, better conversion —
but only if you actually send a review. If you are not going to, change the wording
back rather than leaving a promise unmet.

## 5. Case studies

| id | Where | What it was |
|---|---|---|
| `home-case-studies-section` | `index.html` | The "Solutions We've Delivered" band with the Cookrie card |
| `nav-case-studies-link` | every page | The Case Studies nav tab |
| `footer-case-studies-link` | every page | The Case Studies link in the footer Company column |
| `cta-see-our-work` | `about.html`, all `services/*.html`, `industries/ecommerce.html` | The "See Our Work" secondary button in the hero CTA |
| `about-case-study-link` | `about.html` | The paragraph linking to the Cookrie case study |
| `ecommerce-cookrie-case-study-cta` | `industries/ecommerce.html` | The Cookrie band's "Read the full write-up" sentence and "Read the Cookrie case study" button |
| `sitemap-case-studies-page` | `sitemap.xml` | The `case-studies.html` sitemap entry |

One case study presented as a case *studies* section invites the visitor to count them.
The section comes back once there are three or four.

**The whole page is now toggled off too**, on the owner's call: one project is not enough
to fill it, and it goes back on once there are more. `case-studies.html` is untouched and
still loads at its URL, but it is unlinked from the nav, the footer and `sitemap.xml`, and
carries `noindex,follow` under a `PAGE TOGGLED OFF` note in its `<head>`.

Two places kept their content and changed only where they point, rather than going dark:

- **`about.html`** still makes the Cookrie point, linking to `cookrie.in` instead of the
  case study. The original paragraph is under `about-case-study-link`.
- **`industries/ecommerce.html`** keeps its Cookrie band; "Visit cookrie.in" is now the
  primary button, with "Start a project" beside it. The original is under
  `ecommerce-cookrie-case-study-cta`.

Switching the page back on means reversing all seven ids above plus the two live
rewrites, and restoring the sitemap entry and the `robots` meta — not just the nav tab.

---

## Kept deliberately

Worth recording, so nobody re-litigates it:

- **The FAQ.** It is the strongest thing on the site. The answers on IP ownership,
  fixed scope and taking over an abandoned project close deals. Two CRM questions were
  added; nothing was removed.
- **The seven-step delivery process.** Honest and useful. Only the hero counter that
  advertised it as a statistic was toggled off.
- **The Cookrie case study.** It is your own business, and saying so is fine. One real
  shipped platform beats three vague logos.
- **`industries/ecommerce.html`.** See above.

## Still to do — needs you, not code

- `index.html` and `about.html` carry `TODO (owner)` comments for the postal address,
  `sameAs` social profiles, founding year and team size. Those are direct trust signals
  and they have to be real, so they are left blank rather than invented.
- The site says "we" and "team" throughout. If Appmentech is one person right now,
  decide whether to keep that voice or switch to a first-person one. Both work; the
  wrong one is the one a client discovers on the kickoff call.
- A second case study in any of these industries would do more for conversion than
  any further change on this list.
