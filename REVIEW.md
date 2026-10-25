# Corporate website review - resolved findings

Updated: 24 October 2026.

## Completed

| Finding | Resolution |
| --- | --- |
| Missing installation | Installed local MySQL database, a dedicated database account, private configuration and administrator `admin@graphite.com`. Public pages now return 200. |
| Repository exposure | Root redirect preserves query strings. Apache blocks private folders, hidden files and project metadata. Direct credential, schema and backup requests return 403. |
| Incomplete server rendering | Core pages, news listings, individual news articles, privacy and terms have server-generated HTML and metadata. Unknown, draft and missing-article routes return 404. Navigation uses server requests to preserve accurate metadata and status codes. |
| Metadata replacement | Callback replacements preserve literal dollar signs and backslashes; CMS output remains escaped. |
| Maintainability | Split database, validation, security, pagination and public-content helpers. API handlers are grouped by authentication, public actions, content, dashboard and administration. Reformatted PHP and JavaScript. |
| Account lifecycle | Administrators can change roles and deactivate/reactivate users. Access changes revoke sessions; self-deactivation and self-demotion are blocked. |
| Recovery | Version 2 backups contain all nine application tables and uploaded media. CLI restoration validates archive paths, schema and file types, supports validation without changes, restores data transactionally and revokes old sessions. |
| Unbounded dashboard lists | News, media, enquiries and users use 20-record server pages, with search and enquiry filters. Image pickers search the complete library. Dashboard totals are calculated separately. Full content export remains independent of pagination. |
| Deployment URLs | Local base URL matches `/graphite/public/`. Dynamic robots rules and sitemap URLs use configured paths. Sitemap covers legal pages, news listings and articles. |
| Local session permissions | Sessions now use private writable application storage instead of relying on the WAMP global temporary directory. |

## Explicitly deferred and deployment work

- Email delivery and notifications are deferred at the owner's request. Enquiries remain available in the CMS inbox; no email provider has been configured or contacted.
- Daily backup/maintenance commands are available. Their schedule, offsite storage and HTTPS production configuration belong to deployment on the final host.
- Final company/legal copy, image rights and draft project approval remain owner decisions. This technical work does not constitute approval of that material.
- A subdirectory installation on a shared domain needs its crawler rules incorporated into the domain-root `/robots.txt`. Production should use `public/` as the document root.

## Verification

- Native PHP integration: 72 checks passed on SQLite and 72 on MySQL 8.4.
- Browser checks passed for website routes, mobile navigation, CMS authentication, every dashboard section, image search, draft preview and logout.
- All PHP files parsed successfully; JavaScript parsed successfully.
- Local Apache verified root redirects, query preservation, successful public pages, true 404 responses, robots routing and private-path denials.
- Backup creation and maintenance commands ran successfully. Full database/media restoration and archive traversal rejection passed in isolated test databases.
- Browser screenshot: `storage/website-mobile.png`.

## Local access

Website: http://localhost/graphite/public/

CMS: http://localhost/graphite/public/admin.php

Administrator: `admin@graphite.com`. The generated password is in the private,
Git-ignored `storage/local-admin.txt`. Change it after first login and delete that
file. Do not publish the configuration, credential file, sessions or backups.

See README.md for installation, migration, pagination, crawler configuration and
backup/restore commands. Final hosting and HTTPS must still be checked during
deployment.
