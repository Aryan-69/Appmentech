# Appmentech Technologies — appmentech.in

Marketing site and contact pipeline for Appmentech Technologies.

- **Live:** https://appmentech.in
- **Full runbook:** this file. Also available as
  [a Word document](docs/Appmentech-Site-Runbook.docx).

## Repository layout

| Path | What it holds |
|---|---|
| `*.html` | Every page. Static, no build step. |
| `css/` | `base.css` (tokens, keyframes), `layout.css`, `components.css`, `hero.css`, `flip.css` |
| `js/` | `hero.js`, `nav.js`, `flip.js`, `reveal.js`, `accordion.js`, `icons.js`, `contact.js` |
| `assets/` | Brand mark, lockups, favicons |
| `submit.php` | The contact-form endpoint |
| `lib/` | `requirements.php` (storage), `googledrive.php` (attachments) — web access denied |
| `docs/TOGGLES.md` | **Read before editing a page.** Everything switched off rather than deleted, and how to switch it back on |
| `db/schema.sql` | `UserRequirements` + audit table — web access denied |
| `config.sample.php` | Template for `config.php`, which is gitignored and lives only on the server |
| `db-check.php`, `drive-check.php`, `google-auth.php` | One-shot diagnostics. Upload, run, delete. |

## Working locally

Any static server will do; PHP endpoints need PHP.

```bash
python -m http.server 4321     # pages only
php -S 127.0.0.1:8000          # pages + submit.php (needs config.php)
```

There is no package manager, bundler or framework. Edit a file, reload the page.

## Theme

Off-white page, sky-blue accent. Tokens live in `css/base.css`; the other four
files should reference them rather than adding literals.

**The one rule that matters: there are two blues, and they are not
interchangeable.**

| Token | Value | Use |
|---|---|---|
| `--color-accent` | `#38BDF8` | **Fills only** — buttons, chips, icons, rules. 2.0:1 on off-white, so it can never carry text. |
| `--color-accent-ink` | `#0369A1` | **Words** — links, labels, eyebrows. 6.4:1, passes AA. |
| `--color-on-accent` | `#04293D` | Ink placed *on* a `--color-accent` fill. 7.1:1. |

Ask which one a thing is: a shape, or words? Using the fill blue for text is the
easy mistake, and it silently drops that text to about 2.8:1.

The three greys (`--color-body`, `--color-muted`, `--color-dim`) sit unusually
close together. On a dark ground a faint grey still reads; on off-white it does
not, so each was darkened until it clears 4.5:1 on every band. Hierarchy comes
from size and weight, not lightness — lightening any of them breaks AA.

Two names are historical and now mean the opposite of what they say. The
`.section-dark` / `.section-deep` classes and the `.hero-dark` / `.btn-outline-light`
selectors are from the previous near-black design; they are simply the two
tinted bands and the quiet button now. Renaming them would touch all 16 pages,
so they were left alone and commented instead.

Assets that carry the palette and have to be regenerated together:
`assets/favicon.svg` → the four `favicon-*.png` and `apple-touch-icon.png`;
`assets/og-image.source.html` → `og-image.png` at exactly 1200x630; and the
confirmation email in `submit.php`, which repeats the colours as literals
because no mail client supports CSS variables.

Every page must request the same `?v=` per asset. A page left on an older
number serves last week's `base.css` with this week's `components.css`, which
looks far worse than no cache-busting at all.

## Positioning

The site sells **web development** and **CRM development** to a named set of industries.
That focus is deliberate and recent: the previous version listed eight service
categories and twelve industries, which read to a buyer as a company with no
speciality.

Nothing from the old version was deleted. It is commented out behind
`TOGGLE:OFF` markers and catalogued in **[`docs/TOGGLES.md`](docs/TOGGLES.md)**,
with the reason for each and the steps to bring it back.

Two things follow from this, and they are easy to undo by accident:

- **The nav and footer are copied into every page**, so a link added to one has to be
  added to all of them. `grep -rn 'nav-off-niche-services' .` finds every copy.
- **Before adding a service or an industry**, check `docs/TOGGLES.md` — it may already
  be there, switched off on purpose.

---

## Runbook

How appmentech.in works, and what to check when part of it stops.

- **Front end** — static HTML / CSS / vanilla JS. No framework, no build step.
- **Server** — PHP 8.3 on Hostinger shared hosting, no Composer.
- **Storage** — MySQL (`UserRequirements`), Google Drive (attachments), SMTP (email).
- **Source** — `github.com/Aryan-69/Appmentech`, branch `main`.

## 1. Shape of the system

Every page is served straight off disk. The only server-side behaviour is
`submit.php`, which the contact form posts to. It talks to three services:

| Service | Purpose | Can it fail the request? |
|---|---|---|
| Hostinger SMTP | notification + customer confirmation | **Yes** — the only fatal dependency |
| MySQL | one live row per contact, plus an audit trail | No — reported as `Storage: Failed` |
| Google Drive | attachment filing | No — the file is emailed instead |

## 2. Request lifecycle

1. **Client validation** (`js/contact.js`) — name, email with a real TLD, phone of
   6–15 digits, description, attachment extension and size. Also converts the chosen
   contact window from the visitor's timezone to UTC and submits both.
2. **Server validation** (`submit.php`) — repeats every check, then inspects the
   upload: PHP error code, 10 MB ceiling, extension whitelist, and the real MIME type
   from `finfo` cross-checked against the extension.
3. **Identity** — a v4 GUID becomes the requirement id; `ContactKey` decides whether
   this contact already exists.
4. **Storage** — on a match, the current row is copied to `UserRequirementsAudit` and
   the live row updated in place; otherwise inserted. One transaction, `FOR UPDATE`.
5. **Drive** — refresh token exchanged for an access token, folders found or created,
   file uploaded. `AttachmentStatus` becomes `Uploaded`, `Failed`, or `Pending` when
   Drive is unconfigured.
6. **Email** — team notification (with the file attached and a `Storage:` line) and
   the customer confirmation. The confirmation is best-effort.
7. **Response** — `{ok, requirement_id, storage}` plus `storage_warning` /
   `attachment_warning` when something degraded.

## 3. Failure semantics

| Condition | Visitor sees | Recorded as |
|---|---|---|
| SMTP refuses | failure | `HTTP 502`, log `contact submit SMTP error` |
| DB unreachable / query fails | success + warning | `Storage: Failed` + driver message |
| DB not configured | success | `Storage: Not configured` |
| Drive upload fails | success + "please resend" | `AttachmentStatus = Failed` |
| Drive not configured | success + "emailed instead" | `AttachmentStatus = Pending` |
| Endpoint missing | "returned HTTP 404" | nothing — the request never ran |

## 4. Data model

`db/schema.sql` renders the specified SQL Server model in MySQL terms:
`UNIQUEIDENTIFIER` → `CHAR(36)` with a PHP-generated GUID, `NVARCHAR(MAX)` →
`LONGTEXT`, `ISJSON(...) = 1` → `CHECK (JSON_VALID(...))`.

- `UserRequirements` — one live row per contact, unique index on `ContactKey`.
- `UserRequirementsAudit` — every superseded version.

`ContactKey = SHA2(name | email | phone, 256)` where the name is lowercased with
whitespace runs collapsed, the email lowercased, and the phone reduced to its **last
ten digits**. The last-ten rule matters: stripping only leading zeros made
`+91 08179308281` and `08179308281` different keys for the same person. Rows written
before that fix carry the old key and never match a new submission.

`RequirementDetails` holds the whole submission as JSON, including attachment metadata
and the contact window in local and UTC form.

## 5. Google Drive

Files land in `UserRequirements/{UserRequirementId}/{filename}`. Drive has no paths, so
each level is looked up by name and created if missing. Up to 5 MB: one multipart
request. Larger: a resumable session in 4 MB chunks.

Two decisions worth keeping:

- **OAuth refresh token, not a service account.** A service account owns no storage
  quota, so uploading into a personal Drive folder fails with `storageQuotaExceeded`.
  The service-account path stays implemented for a future Shared Drive or Workspace.
- **Scope `drive.file`, not `drive`.** The broad scope is *restricted*: the client
  cannot be published without Google review, and an unpublished client's refresh tokens
  expire after seven days. `drive.file` covers everything this app does, needs no
  review, and keeps the token alive. It also means the app cannot see folders it did
  not create — which is why `folder_id` is left blank.

## 6. Email

`submit.php` contains a small SMTP-over-SSL client (EHLO / AUTH LOGIN / MAIL FROM /
DATA) so the project needs no Composer or vendor directory. It strips CR/LF from header
values, dot-stuffs the payload, and normalises line endings to CRLF.

| Message | To | Structure |
|---|---|---|
| Team notification | contact@appmentech.in | `multipart/mixed`: text + base64 attachment |
| Customer confirmation | the sender | `multipart/alternative`: text + branded HTML |

The HTML confirmation is table-based with inline styles on the site palette, with the
mark loaded from `assets/` and the wordmark as live text. Its copy avoids apostrophes
because it lives inside a single-quoted PHP string.

## 7. Configuration

`config.php` sits in `public_html` beside `submit.php`, is gitignored, and is the only
file holding secrets. `config.sample.php` is the template. Both are blocked from the web
by `.htaccess`, as are `lib/` and `db/`.

| Key | Blank behaviour |
|---|---|
| `host` `port` `username` `password` `from` `to` `helo` | request fails |
| `db.host` `db.name` | storage skipped silently |
| `db.user` `db.password` | `Storage: Failed` |
| `googledrive.client_id` `client_secret` `refresh_token` | attachment emailed instead |
| `googledrive.folder_id` | app creates its own folder at Drive root |
| `clamscan` | scan skipped |

Placeholders count as blank: `YOUR_*`, an all-zero GUID, a secret containing spaces, or
a client id not ending `.apps.googleusercontent.com`.

## 8. Credentials

**No secret values belong in this repository.** It is on GitHub; anything committed
there is effectively published. Values live only in `config.php` on the server.

| Credential | Issued in | Stored in | Rotate by |
|---|---|---|---|
| Mailbox password | hPanel → Emails | `config.php` → `password` | change in hPanel, update the file |
| MySQL password | hPanel → Databases | `config.php` → `db.password` | change in hPanel, update the file |
| Google client id + secret | Google Cloud → Credentials | `config.php` → `googledrive` | add a new secret, swap, delete the old |
| Google refresh token | `google-auth.php`, one-shot | `config.php` → `refresh_token` | revoke at myaccount.google.com/permissions, re-run |
| Hostinger API token | hPanel → Account → API | local MCP config | revoke and reissue |
| GitHub push access | GitHub account | local credential store | reissue the token |

Any credential pasted into a chat, screenshot or shared document should be treated as
compromised and rotated.

## 9. Deployment

**Hostinger Git deployment. The `deploy` branch is what is live.**

| | |
|---|---|
| Host | Hostinger shared, LiteSpeed, PHP 8.3 |
| Account | `u138660006` |
| Document root | `/home/u138660006/domains/appmentech.in/public_html` |
| Repository | `https://github.com/Aryan-69/Appmentech.git` (public, so no deploy key) |
| Branch | `deploy` |

`main` stays the source of truth for development. `deploy` is the release
pointer: nothing reaches the site until something is merged into it and pushed.

```bash
git checkout deploy
git merge --ff-only main      # or the feature branch you are shipping
git push origin deploy
```

Then either press **Deploy** in hPanel, or let the auto-deployment webhook do it.

### Two files on the server that are not in Git

Both must survive every deploy:

- **`config.php`** — the real credentials. Gitignored, and the server's copy is
  the only one. Never upload a local one.
- **`error_log`** — written by PHP, not by us.

Because Hostinger clones into the document root and needs it empty the first
time, `config.php` has to be taken out and put back. That is the whole of the
first-deploy dance; see below.

`google782610134da2e330.html` (Search Console verification) **is** in the
repository now, so a deploy no longer removes it.

### First deploy, once

1. **hPanel → Files → File Manager**, open `public_html`. Download `config.php`
   and keep it somewhere safe. Confirm you can open it and see real values.
2. Delete everything else in `public_html`, including `config.php`,
   `appmentechseodeploy.zip` and the `newcode/` directory (see §11). Hostinger
   refuses to clone into a directory that is not empty.
3. **hPanel → Websites → appmentech.in → Advanced → GIT.**
   - Repository: `https://github.com/Aryan-69/Appmentech.git`
   - Branch: `deploy`
   - Directory: leave blank — blank means `public_html` itself. Anything else
     puts the site at `appmentech.in/<that directory>`.
   - **Create**, then **Deploy**.
4. Upload `config.php` back into `public_html`.
5. Copy the **auto-deployment webhook URL** hPanel shows, then GitHub →
   repository → Settings → Webhooks → Add webhook. Paste it as the Payload URL,
   content type `application/json`, "Just the push event". Pushes to `deploy`
   now deploy themselves.

### After every deploy, check these four

The first two are the ones that have actually broken here before.

1. `https://appmentech.in/` loads and the nav dropdowns open.
2. Submit the contact form with an attachment — see §12. A 404 instead of JSON
   means `submit.php` did not arrive; `Storage: Not configured` means
   `config.php` did not come back.
3. `https://appmentech.in/.git/config` returns **403 or 404**, never a file.
4. `https://appmentech.in/drive-check.php` returns **403**.

Checks 3 and 4 verify the `.htaccess` hardening is actually in force. If either
one serves content, LiteSpeed is not reading `.htaccess` and the diagnostics and
full Git history are public — fix that before anything else.

### Why the old warning no longer applies

This section used to warn that partial uploads were the project's most expensive
failure mode: twice, long hunts traced back to files that never reached the
server — a missing `submit.php` returning a 404 page instead of JSON, and a
stale `lib/googledrive.php` reporting valid credentials as unconfigured. A Git
checkout is atomic in a way that extracting an archive over a live directory is
not, which is the main reason to prefer it. Check 2 above still exists because
the failure was expensive enough to be worth one minute of confirming.

## 10. Diagnostics

Three one-shot helpers. Each refuses to run without `?i-will-delete-this=yes`, and each
reports credentials only by shape and length. **Upload, read, delete.**

| Script | Answers |
|---|---|
| `db-check.php` | which database is reached, whether the tables exist, a rolled-back test insert with the real exception |
| `drive-check.php` | path/size/md5 of the loaded library, which config field fails, then a live token request and folder lookup |
| `google-auth.php` | walks the consent flow once and prints a refresh token |

Failures that never reach the visitor go to `public_html/error_log`, prefixed
`contact submit:`:

```
contact submit: database connection failed: SQLSTATE[HY000] [1045]   wrong password
contact submit: could not save requirement: ...                      connected, query failed
contact submit: Google Drive upload failed: token endpoint: ...      credentials or grant
contact submit SMTP error (notify): ...                              the only fatal one
```

No `contact submit:` lines at all, with rows still missing, means `db.host` or
`db.name` is blank.

## 11. Known limits

- **Malware scanning is inert** unless `clamscan` points at a real binary; shared
  hosting has none, so uploads pass on extension and MIME checks alone.
- **Phone number is `+91 73030 21135`** across the pages and the auto-reply.
- **Country flags degrade to ISO letters on Windows**; the ISO badge is the workaround.
- **Rows predating the phone-normalisation fix** carry keys that never match.
- **`newcode/` and `appmentechseodeploy.zip` are still on the live server** at the
  time of writing: a complete duplicate of the site reachable at
  `appmentech.in/newcode/`, and a downloadable archive of it. Both are leftovers
  from manual deploys, both are indexable, and the duplicate serves an older
  version of every page. Delete them — step 2 of the first deploy does.

## 12. Verifying the whole pipeline

Submit the form with an attachment. A healthy response:

```json
{"ok":true,"requirement_id":"…","storage":"Inserted"}
```

`ok` means SMTP accepted the message, `Inserted` means the row was written, and the
absence of any warning key means the Drive upload succeeded — the entire pipeline
confirmed in one request.
