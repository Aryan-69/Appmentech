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

`main` is the source of truth. There is no automated deploy — files go to
`public_html` by hand via hPanel File Manager. (The Hostinger API could automate this;
the configured MCP token is still a placeholder.)

**Partial uploads are this project's most expensive failure mode.** Twice, long hunts
traced back to files that never reached the server: a missing `submit.php` returning a
404 page instead of JSON, and a stale `lib/googledrive.php` reporting valid credentials
as unconfigured. After extracting an archive in place, confirm the file's modified date
— some File Manager builds skip existing files instead of overwriting.

Never upload a local `config.php`; the server's copy is the real one.

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
- **Deployment is manual** until the Hostinger API token is real.

## 12. Verifying the whole pipeline

Submit the form with an attachment. A healthy response:

```json
{"ok":true,"requirement_id":"…","storage":"Inserted"}
```

`ok` means SMTP accepted the message, `Inserted` means the row was written, and the
absence of any warning key means the Drive upload succeeded — the entire pipeline
confirmed in one request.
