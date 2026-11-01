# Website improvement content and source map

Revision: 26 October 2026. Local implementation; no production publication.

Sources: `Website Data _Graphite Industries Limited _ApVd.docx` (Word), its PDF counterpart (PDF), and `Corporate_Project_Brief_Draft.pdf` (Brief). Existing Markdown extractions are reading aids. The revised complete brief governs presentation where earlier drafts differ.

| Page / section | Visitor objective | Source passage | Existing gap | Implemented direction |
| --- | --- | --- | --- | --- |
| Home | Retain the owner's preferred original homepage | Saved source and content from before the improvement update; owner's latest instruction | Owner preferred the previous design | Restored original homepage template, copy, footer, tiles and styling; inner-page improvements retained |
| About | Assess identity and purpose | Word: About Us, Our Vision, Our Mission, Strategic Objectives | Vision implicit; mission and objectives absent | Explicit mission and vision, objectives and evidence-led development approach |
| Project / process | Understand ore-to-product relationships | Word: Proposed Mine-to-Manufacturing Graphite Value Chain, steps 01–06 | Process absent | Six readable, connected processing stages with ordinary-language explanations |
| Project / roadmap | Understand decisions before implementation | PDF: Proposed Development Roadmap; Brief: Technical Workstreams | Four compressed workstreams | Six proposed stages, distinct from processing, without completion claims |
| Project / location | Understand the proposed location and practical dependencies | Brief: Infrastructure, Logistics and Market Access | Little explanation of dependencies | Separate feedstock, infrastructure and logistics evaluation; no ownership map or headline capacity |
| Products | Assess potential fit and qualification requirements | Word: Product Manufacturing and Value Addition; Product Development | Short product definitions only | Three product pathways with applications, customer requirements and status; four application cards |
| Investors & Partners | Understand rationale and potential contribution | Brief: Partnership Opportunities and Proposed Engagement Process | Contribution details absent; engagement hidden | Rationale, six contribution areas and three visible engagement steps |
| Sustainability | Understand intended environmental, social and governance priorities | Word/PDF: ESG Commitment; Brief: Sustainability | Short general statements | Specific proposed priorities for water, energy, waste, safety, communities, sourcing and accountability |
| Contact | Send a useful, relevant enquiry | Word/PDF: Contact Us; Brief: Contact | Seven inconsistent categories, little guidance | Five categories, contextual prompts, optional buyer details, accessible real submission feedback |
| Updates | Find factual announcements | Word/PDF: News and Updates | No approved posts in database | Retain honest empty state; no invented announcements or homepage news grid |

## Reconciliation and unresolved inputs

- Word supplies several alternative vision/mission formulations. Select the explicit About Us statements and preserve their intended wording rather than publishing all alternatives.
- Some PDF passages repeat drafts and prioritisation advice. Consolidate into a phased, conditional project description. No reserve, licence, customer, financing or commissioning claim is established.
- 2,000 tonnes/day, three shifts, 330 days/year and roughly 1,000 acres are preliminary assumptions, not validated plant capacity or secure land tenure. Omit numerical promotional claims until supported by feasibility and land evidence.
- Website, Word/PDF and local CMS settings agree on `ce@graphiteindustries.co.tz`, `+255 713 283 711` and the Morocco Squire office address. Retain that spelling and domain. No mailbox delivery or domain-ownership test is implied. Word/PDF also list an additional phone; retain the existing primary phone.
- On 31 October 2026 the user confirmed the supplied mining photograph depicts the actual project and was provided by the CEO. The matching original, `WhatsApp Image 2026-10-28 at 9.57.09 AM.jpeg`, is retained as `assets/images/project-site-original.jpg`. The Our Project header uses responsive WebP copies. Visible photo captions were subsequently removed across the public website at the user's request; descriptive alternative text remains. This photograph does not establish additional claims about project stage, licences, capacity or infrastructure.
- The approved Project page refinement adds four overview facts, a visible planning status, six expandable processing steps, six proposed roadmap steps, and a general Tanzania/Mpwapwa orientation map. `app/project-layout.html` is the shared PHP/browser structure; run `node bin/sync-project-template.cjs` after editing it. CMS body copy supplies the introduction, stage descriptions and infrastructure text. Both project CTAs open editable enquiry drafts, without submitting them. Map provenance and its general-area limitation are recorded in `assets/images/mpwapwa-map-source.md`.
- The About leadership spotlight now loops automatically every five seconds within the selected Board or Management group. Hover, keyboard focus, a portrait dialog, an offscreen panel or a hidden tab suspend playback. Explicit Pause remains paused; reduced-motion preferences disable automatic playback. Existing member order and manual controls are retained.
- Products & Applications now uses online-sourced imagery stored locally, three compact product cards, five illustrated application cards, native disclosures for CMS detail, and product-specific editable enquiry drafts. No image captions or overlays are displayed. `app/products-layout.html` is shared by PHP and JavaScript; run `node bin/sync-products-template.cjs` after changing it. Technical CMS text is retained inside disclosures. `public/image-credits.html` provides attribution through a Products footer link; source and licence records are in `assets/images/products-sources.md`. Images are illustrative and do not establish company product qualification or availability.
- Verified leadership profiles, registration details, evidenced milestones and specifically authorised partnership opportunities remain unpublished pending supporting information.
- The downloadable brief is explicitly a draft and asks for contact verification before external distribution. Replace public download promotion with an enquiry for suitable project information until public distribution and a dated revision are approved. Retain the existing asset; do not invent an issue date.
- Email notifications remain deferred as recorded in REVIEW.md. Enquiries go to the existing CMS inbox; success means the server stored the enquiry, not email delivery.

## Research notes

Checked 26 October 2026. Research describes graphite generally; it establishes no Graphite Industries capability.

- [USGS Graphite Statistics and Information](https://www.usgs.gov/centers/national-minerals-information-center/graphite-statistics-and-information): conductivity, heat resistance and lubricity; batteries, friction materials and lubricants.
- [USGS Natural Graphite, Mineral Commodity Summaries 2026](https://pubs.usgs.gov/periodicals/mcs2026/mcs2026-graphite.pdf): referenced in the supplied brief for general applications including refractories and steelmaking; direct re-fetch returned HTTP 403. No market statistics used.
- [Georg H. LUH, Expandable Graphite](https://www.luh.de/en/expandable-graphite/): use of expandable graphite in fire-protection materials and coatings. No company testing or certification inferred.
- [EcoGraf Midstream](https://www.ecograf.com.au/midstream/): comparator for additional processing between flake concentrate and battery materials. No specifications, infrastructure, imagery or commercial claims transferred.

## Safe content update

`bin/improve-content.php` checks each existing page against a retained baseline before changing it. It only changes untouched baseline fields, saves an entire previous page in CMS revision history and reports conflicts for manual review. It never resets the database, changes settings or replaces custom edits. Run without arguments to review; `--apply` applies eligible changes transactionally. Fresh installations use the revised seed. Full verification is recorded in WEBSITE-IMPROVEMENTS.md.
