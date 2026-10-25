# Graphite Industries Limited — corporate website & CMS

Custom HTML, CSS, JavaScript, PHP and MySQL application. No WordPress, framework, Composer or Node.js process is required on the production server.

## Included

- Responsive corporate website: Home, About, Project, Products, Sustainability, Investors, News, Contact, Privacy and Terms.
- Approved palette: charcoal `#25282A`, silver `#A7ADB0`, forest green `#174A3A`, gold `#C6A15B`, white `#FFFFFF`, soft grey `#F2F3F3`.
- Montserrat headings, Open Sans body, Roboto Mono selected labels and data. Google Fonts are requested remotely; system fonts remain available when blocked. Self-host fonts if required by your privacy policy.
- Company logo and illustrative images extracted from the supplied Word document. Confirm rights to use supplied images before launch. Project draft PDF included, clearly marked as draft.
- PHP CMS: administrator/editor access, page text/image/SEO editing, draft content previews, publish/unpublish, page revision history and restore.
- News drafts and publishing, image/PDF upload library, searchable/filterable enquiries and CSV export.
- Consent-based visitor sessions, page views, popular pages, referrer hostnames and brief-link clicks. No fake statistics, stored raw IP addresses or country inference. Counts are sessions, not unique people.
- Company settings, user creation, password changes, secure-session expiry, content export, scheduled backup script.

## Requirements

For live hosting, follow [the production deployment guide](deploy/PRODUCTION.md).
It includes strict environment-based configuration, PHP settings, and the
`php bin/check-production.php` readiness check.

PHP 8.2+ (8.3+ recommended) with PDO MySQL, fileinfo, session and ZipArchive. MySQL 8+ or compatible MariaDB. Apache with `.htaccess` enabled, or equivalent Nginx rules. PHP `upload_max_filesize` at least `8M`, `post_max_size` at least `10M`. HTTPS for production.

## Install locally on WAMP/XAMPP or on cPanel

1. Extract the project into a private directory, for example `/home/ACCOUNT/graphite`.
2. Create an empty MySQL database and a user authorised for that database. Do not use a server-wide root account.
3. Copy `app/config.example.php` to `app/config.php`. Enter the DSN, database username/password and real website base URL. Generate a rate-limit key with `php -r "echo bin2hex(random_bytes(32));"` and put the output into `rate_limit_key`. Use `secure_cookies => false` only for local HTTP testing; keep it `true` on HTTPS hosting. Never commit configuration or credentials.
4. Run `php bin/install.php` from the project folder. The installer refuses to overwrite an initialised database.
5. Run `php bin/user.php`. Enter the first administrator name, email and a password of at least 12 characters. There are no default production credentials. Running it for an existing email resets that account's password and revokes existing sessions.
6. Set the domain's **document root to the project's `public/` folder**. On cPanel use Domains → Manage → Document Root if available. WAMP can use a VirtualHost with this document root. Do not expose `app/`, `database/`, `storage/`, `bin/` or the repository root through the web server. If your provider fixes the document root to `public_html`, place the public files in `public_html`, keep the rest outside it and adjust the bootstrap paths accordingly; ask your host to verify these paths.
7. Allow the PHP account to write to `public/uploads/` and `storage/backups/`. Use least-privilege ownership/permissions; do not use world-writable `777`.
8. Open `/` for the website and `/admin.php` for the dashboard. Confirm `DirectoryIndex index.php` and the upload-directory protections are active. The application also works under a subdirectory when configured consistently.
9. Add the website's absolute `sitemap.php` URL to search-console tooling. The live site uses server-generated titles, descriptions, canonical links and a no-JavaScript body fallback for published core pages.

### Root redirect for local WAMP access

When opened at `http://localhost/graphite/`, the root `index.php` redirects to
`public/` and preserves query parameters (for example `?page=contact`). The
redirect is temporary (HTTP 302) and works when the project is in a subdirectory.
Set `base_url` to the actual public URL, such as
`http://localhost/graphite/public`, and use `secure_cookies => false` for local HTTP.

The root `.htaccess` disables directory listing and blocks direct HTTP access to
private folders and project metadata. This requires Apache `mod_rewrite` and
`AllowOverride` support. Keep these protections enabled when serving the project
root. For production, setting the document root to `public/` remains preferred.
Nginx and PHP's built-in server do not apply these `.htaccess` protections.

### Nginx note

Set the web root to `public/`, configure the PHP-FPM handler, deny PHP/script execution under `/uploads/`, disable directory listing and return 403 for hidden files. Apache `.htaccess` rules are not read by Nginx. Uploaded filenames are random and restricted to validated JPEG/PNG/WebP/PDF types.

## Typical workflow

- Pages → Edit → change title, summary, body, hero image or SEO title → Preview draft → Save page.
- The status selected when saving determines visibility. **Draft removes a page from public navigation.** Saving a published page updates it immediately; there is no separate staging copy of a published page. Use preview before saving, and revision history to restore earlier content.
- Content is plain text for predictable safe display. Public page layouts and exploration links are maintained in `public/assets/presentation.js`, rendering and form behaviour in `public/assets/site.js`, and the shared corporate presentation in `public/assets/premium.css`. Legal copy is shared between PHP and JavaScript through `app/legal.php`; this is not a visual drag-and-drop page builder.
- Media → Upload → select uploaded image in a page editor. Files are publicly accessible, so do not upload confidential documents. The fixed brief download uses `public/assets/corporate-project-brief-draft.pdf`; replace that file when an authorised new brief is issued.
- Enquiries are stored in the dashboard. **Email sending, SMTP notifications and autoresponders are not configured.** Staff can follow up through their own email client.
- Administrators create accounts and update settings. Editors can manage content, uploads and enquiries, but cannot create users or change company settings. Users → Manage access lets administrators change roles and deactivate/reactivate accounts. Saving access revokes existing sessions. The current administrator cannot disable or demote their own account.

## Analytics and retention

The website asks permission before optional analytics. Counts start at zero. Referrer query strings are discarded. Abuse throttling temporarily stores keyed hashes derived from client IPs, not raw addresses. Run `php bin/maintenance.php` daily to delete expired limits and analytics over 90 days old. Contact details are stored until the company deletes them; agree a retention schedule and final privacy notice before public launch. Link clicks are measured, not completed PDF downloads. No third-party analytics account is needed.

## Backups and recovery

- Run `php bin/backup.php` manually or in a daily cPanel Cron Job. Use the actual CLI PHP binary path provided by your host: `/PATH/TO/php /home/ACCOUNT/graphite/bin/backup.php`.
- The backup contains the content tables, users/password hashes, enquiries, revisions, analytics, rate limits and uploaded media (schema version 2). The latest 7 ZIP archives are kept outside the document root in `storage/backups/` with restricted permissions. Database credentials are not included.
- Copy backups to access-controlled offsite storage using your host's backup system. This project does not create a hosting account or enable cron by itself.
- Recovery: validate a trusted version 2 archive with `php bin/restore.php /absolute/path/backup.zip`. Apply it with `php bin/restore.php /absolute/path/backup.zip --apply`. Put the site into maintenance at the web-server level and take a fresh backup first. The tool validates paths, column names, upload types and the presence of an active administrator before replacing database rows. It restores media and revokes existing sessions. Unreferenced existing uploads are retained. Older version 1 archives require manual recovery. Keep a hosting-level database dump as an independent recovery option.
- Dashboard Settings → Export is a content JSON export, not a full server backup; it omits password hashes and media bytes.

## Static preview versus installed app

`dist/` is an intentionally separate interactive visual preview for hosting without PHP. Its dashboard requires no real login, edits stay in browser localStorage and form submission/uploads/accounts are disabled. The yellow notices identify this limitation. **Never use `dist/` as your PHP production application.** Deploy `public/` with the private sibling directories as described above. Demo mode is enabled only by an explicit attribute in generated static HTML; the PHP app does not silently fall back to demo mode on an error.

Rebuild the visual preview with `python3 bin/build-preview.py`. This copies the public UI and enables the isolated demo adapter. Production `api.php`, database and credentials are not copied to `dist/`.

## Validation

The native PHP suite needs PHP 8.2+ with PDO SQLite, cURL and ZipArchive:

```powershell
& C:/wamp64/bin/php/php8.3.28/php.exe tests/integration.php
& C:/wamp64/bin/php/php8.3.28/php.exe tests/integration.php --mysql
```

The MySQL suite creates and deletes its own randomly named test database. It uses
local `root` with an empty password by default; override with
`GRAPHITE_TEST_MYSQL_DSN`, `GRAPHITE_TEST_MYSQL_USER` and
`GRAPHITE_TEST_MYSQL_PASSWORD` environment variables where needed. It never uses
or modifies the installed application database. Both database suites pass 72
checks, including metadata literals, 404 responses, server rendering, pagination,
authentication, permissions, upload rejection and full backup/media restoration.

Optional browser smoke checks: `node tests/browser.cjs` with Node 22+ and Edge on
Windows. Set `GRAPHITE_BROWSER` to another browser executable and
`GRAPHITE_BROWSER_URL` to another local public URL if needed. The script reads
the initial local credentials from `storage/local-admin.txt`, checks the website
and dashboard, and saves `storage/website-mobile.png`. It does not submit public
enquiries or save content changes. Update its credential loading if the initial
password has already been changed. Browser checks cover website routes, mobile
navigation, login, dashboard sections, image search, preview and logout.

`npm test` and `npm run test:mysql` invoke the native PHP suites when PHP is on
PATH. The older WASM test remains available with `npm install` and
`npm run test:wasm`; those development dependencies are optional. Production
hosting needs no Node process. Staging verification is still required on the
final HTTPS domain and hosting stack.

## Local installation and upgrades

This checkout is installed at `http://localhost/graphite/public/`, with a
dedicated MySQL account scoped to the `graphite` database. The administrator is
`admin@graphite.com`. The generated initial password is stored privately in
`storage/local-admin.txt`; change it after signing in and delete that file.
`app/config.php`, credentials, sessions and backups are excluded from Git and
blocked from HTTP access. Local cookies are HTTP-compatible; enable secure
cookies when moving to HTTPS.

For a new empty WAMP installation only, `php bin/setup-local.php` creates the
local database, configuration and initial administrator. It refuses existing
configuration or an existing database. It assumes a local MySQL root account
with no password; environment variables `GRAPHITE_SETUP_DB_USER` and
`GRAPHITE_SETUP_DB_PASSWORD` override that setup connection. Use the manual
installation steps above for other environments.

Existing installations: back up first, deploy the updated files, then run
`php bin/migrate.php` once. The migration adds account activation and query
indexes without replacing content. Feature handlers live in `app/actions/`;
database, security, validation and pagination helpers have separate modules.

Dashboard news, media, enquiries and users load 20 records per page. Search and
enquiry status filters run on the server. The image selector can search and page
through the complete media library. Enquiry CSV exports include the current
filtered page; administrator content exports include all matching tables.

Server routes generate metadata and visible HTML before JavaScript loads,
including news and legal pages. Unknown, draft and missing-post routes return
404. Normal website navigation requests the server so statuses and metadata stay
consistent. The enhanced layout loads after the API responds.

`robots.php` generates rules using `base_url`; Apache serves it as `robots.txt`
in both the project root and public folder. Crawlers request `/robots.txt` at the
domain root: for a subdirectory installation on a shared domain, incorporate
these rules into that domain's root robots file. On Nginx, route `/robots.txt` to
`robots.php` explicitly. The sitemap includes published news, news listing pages,
legal pages and published core pages.

Email notifications remain deferred. Staff must monitor the dashboard inbox.
Daily backup and maintenance commands are ready; configure their schedule and
offsite backup destination on the final host. Sessions use private
`storage/sessions/`, which must be writable by PHP.

## Before public launch

Confirm company contacts/address, final privacy/terms and image permissions; approve draft project content; install on PHP/MySQL hosting; run the end-to-end enquiry and publishing checks; configure backups/cron and verify restoration. No operating mine, reserves, approvals, confirmed product grades, customers or funding commitments are asserted.
