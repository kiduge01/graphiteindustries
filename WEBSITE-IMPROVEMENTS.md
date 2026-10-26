# Website improvements — 26 October 2026

Local website: http://localhost/graphite/public/

## What changed

Latest owner feedback: the labelled “Our Approach” overview appears only on About Us. Other inner pages begin with their relevant sections, with page-specific introductory facts integrated into the first section rather than repeated as a separate overview block. Homepage restoration is retained.

- Homepage restored to its pre-update layout, wording, primary action, project/product/sustainability tiles and footer at the owner's request. The proposed redesign and carbon graphic are no longer used. Original homepage styling is retained; `improvements.css` applies to inner pages.
- Explicit About Us mission, vision, strategic objectives, development approach and values, based on the supplied documents.
- Six proposed processing activities shown separately from six development stages. Infrastructure and logistics remain evaluation considerations; unvalidated numerical planning assumptions are not used in the main copy.
- Three product pathways with potential uses, customer requirements and status; four application areas; a clear distinction between graphite concentrate and qualified battery materials.
- Partnership rationale, six contribution areas and visible engagement steps. Draft-document promotion replaced with a request for current supporting information.
- Environmental, social and governance intentions expressed as proposed assessment and management priorities.
- Five enquiry categories, category-specific guidance and seven optional buyer requirements. Details are stored with the message using the existing CMS inbox and backend validation. Delivery is acknowledged only after the API succeeds; email notifications remain deferred.
- More readable type, consistent spacing, charcoal/white/forest-green surfaces, restrained gold and illustrative-image labels. Mobile navigation supports an explicit close button, Escape, focus containment and inactive background content.
- PHP supplies the homepage and essential inner-page content without JavaScript. Without JavaScript, Contact provides the company contact details instead of an unusable form.

## Files and maintenance

- `public/assets/presentation.js`: public layouts and safely escaped structured content.
- `public/assets/improvements.css`: public-only visual refinements.
- `public/assets/navigation.css`: shared navigation behaviour; the original homepage does not load the inner-page visual refinements.
- `public/assets/site.js`: enquiry guidance, optional buyer details, feedback, footer and mobile navigation.
- `public/index.html`, `public/index.php`, `app/public-presentation.php`, `app/public-content.php`: initial rendering, navigation and structured content support.
- `app/seed.json`, `public/assets/seed.json`: reviewed content for new installations and static previews.
- `app/content-baseline-20261025.json`, `bin/improve-content.php`: non-destructive content update for existing installations.
- `bin/prepare-improvement-content.cjs`: maintainer helper that regenerates reviewed seeds; it does not modify a database.
- `public/assets/admin.js`, `README.md`: authoring guidance for plain-text headings and lists.
- `tests/integration.php`, `tests/browser.cjs`: coverage for migration preservation, server rendering, three-section homepage, application content, form guidance and navigation.
- `CONTENT-MAP.md`: source passages, current gaps, editorial decisions and unresolved information.

## Data protection

Before the local update, a full nine-table application backup was created outside the public document root. The update matched all seven local pages to the baseline and saved each previous page in CMS revision history. It preserved settings and publication status. Running the updater again reports no eligible changes.

Custom fields on another installation are skipped and reported as conflicts. Review `php bin/improve-content.php` before running with `--apply`. No database reset is required. Original source copies are retained privately in `storage/improvement-originals/`.

## Verification

Owner-review corrections: first removed the added carbon/introduction block; subsequently restored the entire homepage from its pre-update source and content. Both JavaScript and PHP render the original layout. Homepage content was restored with a revision of the replaced version. Inner-page changes were retained. The full-suite results and screenshots below describe the earlier improvement pass; focused checks cover the homepage restoration, and homepage assertions now follow the original design.

Restoration verified: JavaScript homepage output matches the saved original exactly; original CMS homepage fields match the saved copy; the homepage excludes inner-page visual refinements; project content and styling remain in place. PHP/JavaScript syntax checks passed. The SQLite integration suite passed all 84 checks after restoration. Restored desktop/mobile homepage screenshots were reviewed.

- PHP integration: 84 checks passed on SQLite and 84 on MySQL. Includes custom-content preservation, draft-status preservation, revision recording, idempotence, server-rendered content, enquiries, authentication, permissions, pagination and backup/restore.
- Browser: 122 checks passed at desktop (1440px), mobile (390px) and a tablet project view (768px). Checks cover all public routes, overflow, homepage structure and word count, processing/roadmap lengths, mission/vision, applications, enquiry guidance, optional inputs, navigation, no-JavaScript mission content, CMS login, dashboard sections, image search, preview and logout.
- PHP syntax and JavaScript parse checks passed for changed application code.
- Measured colour contrast: body grey/white 6.77:1; green/white 10.11:1; gold/charcoal 6.11:1; navigation text/white 8.35:1; footer text/charcoal 10.39:1. These are token-pair checks, not a full accessibility certification.
- Desktop, mobile and tablet screenshots reviewed. Full-page reviews cover desktop Home, Project, Products and Investors, plus mobile Home and Contact. A mobile consent-label wrapping issue found in visual review was corrected and verified with all seven optional product fields expanded. Captures are retained privately as `storage/review-*.png`.
- Original DOCX XML and both original PDFs were read to cross-check mission, vision, contact details and preliminary planning assumptions. The website-data PDF contains 59 pages and the project draft contains 4 pages.
- Local enquiry integration tests used isolated test databases. No unsolicited production enquiry was sent.
- Browser startup and navigation had intermittent timing failures before a successful complete run. The smoke test tolerates slower startup and temporary debugging-port file locks.
- No production deployment, live-site audit, mailbox-delivery verification or approval of image rights is implied. The separate `dist/` preview has not been rebuilt; the working PHP website is in `public/`.

## Remaining company inputs

Approved actual-project photographs and image-use rights; verified leadership and registration information; evidence for project milestones, technical results, land and capacity; specific approved partnership opportunities; and approval of a dated project brief for public distribution. These do not block the qualified local content now implemented.
