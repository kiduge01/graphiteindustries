# Graphite Industries Limited — Complete Website Improvement Brief

**Revision:** 26 October 2026

This is the complete revised instruction set. Use it in place of the earlier improvement brief; no separate homepage addendum is needed. Starting afresh means correcting the design and content direction while retaining suitable existing code and working functionality.

## Task

Improve the existing Graphite Industries Limited website using the supplied company documents, the content audit, and the research below. Implement the improvements in the source code; do not stop at recommendations. Design the content, visual composition and visitor journeys together. The experience must capture attention, feel inviting and encourage exploration through arrangement, colour, typography, imagery and spacing. Keep the homepage concise and give inner pages the depth needed to fulfil the business objectives. Keep the existing PHP/MySQL architecture compatible with cPanel shared hosting.

This brief authorises local implementation and validation. It does not itself request production deployment. Preserve unrelated work and existing working features. Do not rebuild in another framework or introduce a dashboard, investor portal, payment system, or CMS replacement.

## 1. Business objective and project positioning

The website must establish corporate credibility, explain the proposed Mpwapwa graphite-processing and value-addition project in Dodoma, Tanzania, and generate qualified enquiries from investors, industrial buyers, technology partners and service providers.

Present the company as a development-stage enterprise. Distinguish the proposed facility, future opportunities and confirmed achievements. The site should introduce the company clearly and offer deeper commercial and technical information on relevant inner pages. Greater content depth must not turn the homepage into a long corporate report.

The central experience requirement is: **without saying “welcome”, make visitors feel welcome.** Aim for a professional, warm, spacious and quietly confident experience. Visitors should immediately understand enough to become curious and feel comfortable choosing where to explore. Avoid welcome banners, introductory splash screens and forced interactions. Apply this principle across the website, including navigation, forms and inner-page layouts.

The user reports that `/public/` opens normally and the website functions correctly. Treat this as user-reported status, not a new test result. Do not diagnose `/public/` as broken merely because a previous environment could not reach it. Verify the actual local and live routing before proposing routing changes.

## 2. Read and inspect before editing

Locate these documents by filename; their paths may differ in your environment:

1. `Website Data _Graphite Industries Limited _ApVd.docx`
2. `Website Data _Graphite Industries Limited _ApVd.pdf`
3. `Corporate_Project_Brief_Draft.pdf`
4. `Graphite-Website-Content-Analysis.md`, if supplied.
5. The current website source, supplied previously as `graphiteindustries-main.zip`.

In the originating workspace, documents were under `project_sources/`, extracted Markdown under `markdown/`, the content audit under `audit-deliverables/`, and source under `audit-source/graphiteindustries-main/`. These are discovery hints, not required paths.

Read repository instructions. Identify routes, shared layouts, content templates, database-backed content, seeders, CSS, assets and form handlers. Inspect current rendered content as well as source: live database content may differ from seed content. Do not overwrite edited production content or run destructive database resets.

The website-data documents contain repeated drafts and alternative wording. Consolidate them into one coherent content set. Do not assume the PDF and DOCX are identical or that every numerical statement is approved for publication. If they conflict materially, record the conflict and use qualified wording until resolved.

Mission, vision, project concept and contact information are already supplied. Extract and reuse them; do not request them again. Check current source documents before selecting among alternative formulations.

Create a concise content map showing: page/section, objective served, source passage, current gap and proposed change. Then implement. Do not wait for approval of routine editorial or layout decisions.

## 3. Content requirements by page

### Home — capture attention and invite exploration

Use four focused sections. Navigation and footer sit outside this structure. A section must earn its place through a clear visitor purpose; do not add more sections simply to represent every available document.

1. **Strong opening:** one compelling, relevant visual; one short, meaningful headline; one supporting sentence identifying the company’s proposed graphite activity in Tanzania; one prominent action such as “Discover Our Story”, leading to About Us. Integrate development-stage wording naturally. A secondary text link to the project is optional, not a competing large button.
2. **Brief introduction:** up to two short sentences about the company, paired with an image or purposeful composition. Give a specific reason to learn more and link to About Us. Avoid repeating the hero wording.
3. **A glimpse of the opportunity:** three visually led pathways—Our Project, Graphite Applications and Partnerships—with a concise title, at most one short supporting line, and a clear destination. Each should appeal to a different visitor interest.
4. **Simple invitation:** one short, human statement inviting a relevant conversation and one contact action. Keep the full enquiry form on Contact.

Use an approximate editorial budget of 150–220 words for the main homepage, excluding navigation/footer. This is a design guideline, not a reason to pad text or remove essential factual qualifications. Keep the hero headline around 6–12 words when natural. Use concrete language; do not trade clarity for vague slogans.

Keep full mission/vision statements, detailed processing diagrams, the development roadmap, specifications, extensive research, sustainability explanations and investor details on their respective inner pages. The homepage links to these rather than reproducing them.

Use real navigation links with descriptive labels. Avoid repeated “Learn More” buttons with unclear destinations. Make the next section discoverable through composition and normal scrolling; do not depend on an unexplained scroll icon. Avoid mandatory full-screen panels, scroll hijacking and autoplay carousels.

### Inner-page experience

Give each inner page a clear title, a short introductory explanation, readable content sections and a relevant next action. Keep the same brand, spacing, typography and interaction quality throughout. Offer depth through structured sections, illustrations and optional detail where useful. Do not hide essential facts in accordions or turn every paragraph into a card. The visitor should always know where they are and how to continue.

### About Us

- Present company identity, mission, vision, strategic objectives and development approach explicitly.
- Explain the intended contribution to Tanzanian industrial value addition using the supplied documents.
- Add leadership, governance and registration details only where verified and suitable for publication.
- Do not invent biographies or display empty leadership cards.

### Our Project

- Explain the proposed Mpwapwa facility and the relationship between feedstock, processing and products.
- Present this proposed processing chain in a readable diagram with a text equivalent:
  1. Feedstock sourcing and verification.
  2. Crushing and grinding, considering liberation and flake preservation.
  3. Beneficiation and flotation.
  4. Further purification where required by the intended product.
  5. Product-specific processing and value addition.
  6. Quality assurance, packaging and delivery.
- Explain that the route depends on feedstock characterisation, testwork, customer requirements and feasibility. Do not imply every product follows an identical route.
- Separately present the development roadmap: feedstock verification; mineralogical/metallurgical testing; project definition and pre-feasibility; feasibility, approvals and financing; implementation and commissioning; potential expansion.
- Distinguish proposed stages from completed milestones. Use no invented dates or progress percentages.
- Discuss infrastructure and logistics as considerations under evaluation unless confirmed. A location map must not imply verified licence boundaries or ownership.

### Products & Applications

- Explain proposed graphite concentrate, micronised graphite and expandable graphite pathways where supported by the documents.
- For each product area, show what it is, potential applications, relevant customer requirements and development status.
- Add four concise application cards: Energy Storage; Steel & Foundries; Industrial Lubricants; Friction Materials. Explain technical terms in ordinary language.
- Explain expandable graphite's potential in fire-protection materials as industry context.
- Separate future battery-material opportunities from confirmed company capability. Concentrate is not automatically a qualified battery anode material.
- Invite buyer enquiries containing intended application, carbon/impurity requirements, particle or flake size, indicative demand, packaging, destination and qualification needs. Keep detailed fields optional where appropriate.
- Do not promise sample availability, prices, delivery, purity or commercial supply without evidence.

### Investors & Partners

- Explain the proposal's rationale: domestic value addition, phased development, technical evaluation, market alignment and appropriate partnerships.
- Show the contributions sought from investment partners, engineering/technology providers, laboratories, feedstock suppliers, potential buyers and logistics providers.
- Describe a simple engagement journey: introductory enquiry; discussion of fit; provision of suitable supporting information when available.
- Offer the project brief only if its public distribution is appropriate; retain draft/development-stage labels and document revision information.
- Do not promise returns, financing, government support, signed customers or offtake agreements.

### Sustainability

- Organise existing intentions around environmental, social and governance priorities.
- Cover assessment of water use, energy, waste/tailings, worker safety, community engagement and responsible sourcing where consistent with the project scope.
- Use “planned”, “proposed” or “to be assessed” for future actions. Do not turn intentions into measured achievements, certifications or compliance claims.
- Avoid unsupported “zero impact”, “carbon neutral” or “100% sustainable” statements.

### Contact and Updates

- Reuse verified contact details from the supplied documents. Resolve any `.com` versus `.co.tz` discrepancy from actual configuration and approved materials; do not silently substitute domains.
- Provide enquiry categories: Investment; Products; Technology & Engineering; Supply & Logistics; General.
- Give each visitor a short explanation of useful information to include.
- Preserve validation and existing form handling; provide accessible success/error feedback. Never simulate a successful delivery.
- Publish only factual, approved updates. Omit empty news grids and invented announcements.

## 4. Design direction

The desired impression is an attractive, credible corporate industrial website that feels inviting before the visitor reads much text. Design quality must come from deliberate composition, not the amount of content or effects. Use the existing brand and owner's sidebar preference where supported by the source documents/current design. Treat BHP-style corporate clarity as inspiration, not permission to copy branding or layouts.

Verify these previously discussed brand values against the supplied materials before applying them globally:

| Token | Colour | Intended use |
|---|---|---|
| Charcoal | `#25282A` | Main text, dark sections, navigation |
| Silver | `#A7ADB0` | Secondary details and borders |
| Forest green | `#174A3A` | Primary actions and brand emphasis |
| Gold | `#C6A15B` | Restrained highlights |
| White | `#FFFFFF` | Main surfaces |
| Light grey | `#F2F3F3` | Alternating section backgrounds |

Previously discussed typography: Montserrat for headings, Open Sans for body copy and Roboto Mono sparingly for technical data. Reuse existing licensed/local assets where possible and keep loading lightweight.

- Establish one dominant focal point per section. Balance text and imagery rather than filling every available area.
- Create an intentional reading order: main message, short explanation, relevant next action. On inner pages, introduce supporting evidence where the visitor needs it.
- Use a restrained visual rhythm: a striking opening, a quieter introduction, a clear set of exploration choices and a calm closing invitation. Avoid stacking many identical card grids.
- Use charcoal and white as the main foundation, green for key actions and gold as a limited accent. Colours must have consistent roles; do not use all brand colours equally in every section.
- Define shared typography and spacing tokens. Suggested starting points: body text 16–18 px, line height 1.5–1.7, reading width around 55–70 characters, fluid heading sizes adjusted to the actual headline. Avoid oversized headings that fragment into many lines on mobile.
- Let whitespace group related elements and separate ideas. It must feel composed, not empty. Maintain useful content and a clear action within the first mobile screen where practical.
- Make interactions reassuring: visible hover/focus states, predictable links, clear feedback and short, optional transitions. Never conceal core text until an animation runs.
- Avoid intrusive pop-ups, automatic audio, flashing effects, fake urgency and decorative loaders. Do not delay access to create a dramatic introduction.
- Use comfortable line lengths, generous but purposeful spacing and consistent cards/buttons.
- Give the value chain and development roadmap distinct visual treatments.
- Keep text readable over imagery; avoid excessive animation, oversized empty areas and decorative sliders.
- Use real, approved company photographs where available. Label representative imagery as illustrative when it could be mistaken for the actual project. Never present stock or generated facilities as company assets.
- Use a responsive sidebar/mobile menu with keyboard access, visible focus and an obvious close control. Prevent navigation from covering content.
- Check contrast for actual colour combinations: at least 4.5:1 for normal text and 3:1 for large text. Do not assume gold or silver is readable on white.
- Provide semantic headings, form labels, meaningful alt text, reduced-motion support and usable touch targets.
- Put detailed visual explanations on the appropriate inner pages: process diagram and roadmap on Our Project, application cards on Products & Applications, and partner contribution details on Investors & Partners. The homepage offers concise visual invitations to these destinations.
- Optimise imagery for its rendered size, reserve image dimensions to prevent layout shifts and load below-the-fold imagery lazily. The main hero should appear promptly; do not lazy-load the primary hero image. Keep essential content visible if animation or JavaScript fails.

## 5. Research to incorporate responsibly

These sources were reviewed during the discussion. Keep a source note in the repository for research-derived statements; use unobtrusive source links on educational sections where useful. Recheck changing facts before publication. Do not add market statistics merely to fill space.

| Source | Supported use | Boundary |
|---|---|---|
| [USGS — Natural Graphite, Mineral Commodity Summaries 2026](https://pubs.usgs.gov/periodicals/mcs2026/mcs2026-graphite.pdf) | Graphite applications include batteries, brake linings, lubricants, refractory uses and steelmaking | Does not establish this company's product suitability or capacity |
| [USGS — Graphite Statistics and Information](https://www.usgs.gov/centers/national-minerals-information-center/graphite-statistics-and-information) | General graphite uses and authoritative industry background | Do not treat global facts as company achievements |
| [EcoGraf — Midstream](https://www.ecograf.com.au/midstream/) | Comparator explaining micronisation, shaping, purity requirements and proposed Tanzanian value addition | EcoGraf is a separate business; never copy its specifications, project figures, infrastructure claims or partnerships into Graphite Industries copy |
| [Georg H. LUH — Expandable Graphite](https://www.luh.de/en/expandable-graphite/) | Manufacturer explanation of expandable graphite applications, including fire-protection coatings | Application context, not proof of company testing, certification or product availability |

Research should make the website more informative, not change the approved business scope. Prefer concise paraphrases over copied passages. Do not use another company's logos or photographs without permission.

## 6. Claims and unresolved information

- Treat references to 2,000 tonnes/day, three shifts, 330 days/year and approximately 1,000 acres as preliminary planning assumptions, not operating achievements or proven capacity. Reconcile these with statements that final capacity depends on feasibility. Omit headline figures if unresolved.
- Never invent licences, reserves, assays, land ownership, funding, signed contracts, certifications or commissioning dates.
- Keep internal drafting instructions and client questions out of public copy.
- Track only genuinely missing client inputs: approved project photographs; verified leadership profiles; evidenced project status/milestones; specific partnership opportunities approved for publication.
- Continue all work that does not depend on missing facts. Use appropriately qualified content or omit the unsupported detail. Put unresolved items in a separate internal list with the affected section and evidence needed.

## 7. Implementation sequence and deliverables

1. Inspect the documents, repository and rendered website; map facts and visitor needs to appropriate pages.
2. Define the shared visual system and four-section homepage composition before expanding content. Choose imagery and hierarchy that support the company’s actual identity.
3. Implement the concise homepage and connected navigation. Put richer project, company, product, sustainability and partnership information on inner pages.
4. Integrate research and specific enquiry paths where relevant; verify all company claims against supplied evidence.
5. Review rendered desktop and mobile views, refine composition and spacing, then validate accuracy, accessibility, links, forms and relevant PHP changes.
6. Deliver changed source files, a concise change summary, a content/source map, unresolved client inputs and verification results. Include deployment notes only where changes need them.

Do not stop after adding copy or changing colours. Inspect the rendered result and correct weak hierarchy, clutter, awkward image crops, repetitive layouts and distracting interactions. If browser access is unavailable, state that limitation and do not claim visual verification.

If content is database-backed, provide a targeted, non-destructive update path compatible with the current application. Updating only a seeder is insufficient for existing installations. Do not reset the database or overwrite unrelated records. Keep secrets out of code and reports.

## 8. Completion checks

- [ ] The homepage has four focused sections and concise copy; detailed explanations live on inner pages.
- [ ] Visitors can identify the company and proposed Tanzanian graphite activity quickly and find the project location/status without effort.
- [ ] The opening has one clear focal point and one prominent next action.
- [ ] The site feels inviting through composition, readable typography, comfortable spacing and easy navigation; it does not rely on saying “welcome”.
- [ ] Each exploration pathway communicates what the visitor will discover and leads to the correct page.
- [ ] Colour roles, typography, buttons and spacing are consistent across all pages.
- [ ] Mobile crops preserve the visual subject and text remains readable without obscuring essential imagery.
- [ ] The page remains usable without animation, and the hero loads without an unnecessary waiting experience.
- [ ] Mission and vision reflect the source documents and are easy to find.
- [ ] Processing steps and project-development stages are explained separately.
- [ ] Potential products connect to applications without claiming unverified capability.
- [ ] Investors, buyers and technical partners each have a clear next action.
- [ ] Public claims distinguish confirmed facts, proposals and research context.
- [ ] No invented metrics, milestones, testimonials, partners or project photographs appear.
- [ ] Desktop, tablet and mobile layouts are checked; no horizontal overflow or inaccessible menu.
- [ ] Keyboard navigation, focus, labels, contrast and feedback are checked.
- [ ] Internal links, document downloads and relevant contact actions work in the tested environment.
- [ ] Changed PHP files pass syntax checks; existing relevant tests pass where available.
- [ ] Form validation is tested using a safe local/staging method; no unsolicited live messages are sent.
- [ ] Existing database content has a safe update path where required.
- [ ] Report distinguishes locally tested behaviour, live-tested behaviour and checks not performed. Do not claim a live audit when access was unavailable.

Finish by explaining what improved, how it supports the objectives, what was verified and which client facts remain unresolved. Do not describe the task as complete if only this plan or draft copy has been produced.
