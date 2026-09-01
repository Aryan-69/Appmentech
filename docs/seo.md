# SEO — what shipped, and what still needs you

This documents the changes made against the September 2026 site audit, and the
handful of items that need real business data before they can be finished.

## Shipped

**Critical**

- `robots.txt` and `sitemap.xml` now point at `https://appmentech.in` instead of the
  dead `www.appmentechtech.com` host. `<priority>` dropped (Google ignores it),
  `<lastmod>` added, and the new landing pages included.
- Six real landing pages replace nav items that used to be homepage anchors:
  - `/services/web-development.html`
  - `/services/mobile-app-development.html`
  - `/services/ai-genai-solutions.html`
  - `/services/software-testing-qa.html`
  - `/services/cloud-devops.html`
  - `/industries/ecommerce.html`

  Each is ~900 words of real copy with its own title, H1, FAQ, breadcrumb and
  `Service` + `FAQPage` + `BreadcrumbList` schema. The nav and footer point at them.
- `about.html` and `case-studies.html` are no longer "Coming Soon" placeholders.
  `careers.html` carries `noindex,follow` and is out of the sitemap until roles exist.

**Warnings**

- Homepage `<title>` (50 chars) and description (138 chars) rewritten to lead with the
  service and market rather than the brand name.
- Full Open Graph and Twitter card tags on every page, with a generated
  `assets/og-image.png` (1200×630). The page it was rendered from is kept at
  `assets/og-image.source.html` — re-render it with headless Chromium if the wording
  changes.
- The stub `Organization` JSON-LD is now `ProfessionalService` with a logo, service
  catalogue and `knowsAbout`, plus a `WebSite` node and `@id` references so the other
  pages' schema links back to one entity.
- Homepage H1 changed to "Custom Web, Mobile & AI Development for Growing Businesses";
  the old tagline moved to the sub-headline.
- Every `href="index.html"` is now `href="/"`, with a 301 from `/index.html` to `/`
  and a www→apex redirect in `.htaccess`.
- An 8-question FAQ section with `FAQPage` schema on the homepage, and 6 on each
  landing page.

## Still needs you

1. **NAP (name, address, phone).** `+91 12345 67890` is still a placeholder across the
   site, and there is no street address anywhere. Publishing a fake one is worse than
   publishing none, so the schema currently carries only `addressCountry: IN`. Once you
   have the real number and address:
   - update the utility bar, footer and `submit.php` auto-reply,
   - add `telephone` and the full `PostalAddress` to the JSON-LD in `index.html`,
   - create a Google Business Profile with exactly the same details.
2. **`sameAs` links.** Add your LinkedIn, GitHub and Google Business Profile URLs to the
   `ProfessionalService` block in `index.html`. This is the field that proves the entity
   exists elsewhere.
3. **About page facts.** Founding year, team size, leadership names and any
   certifications — marked with a `TODO` comment in `about.html`. These are direct
   E-E-A-T signals and should be true.
4. **Cookrie metrics.** The case study describes the build accurately but has no numbers.
   Launch date, order volume or performance figures would make it far more persuasive
   (`TODO` comment in `case-studies.html`).
5. **Google Search Console.** Verify the domain and submit `https://appmentech.in/sitemap.xml`.
   Nothing above is measurable until this exists.
