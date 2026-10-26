// Maintainer helper: builds the reviewed content seed, never writes to a database.
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const seed = JSON.parse(fs.readFileSync(path.join(root, 'app/content-baseline-20261025.json'), 'utf8'));
const revisions = {
  // Homepage retained as requested by the owner.
  about: {
    title: 'Tanzanian roots. Industrial ambition.',
    summary: 'We are a development-stage enterprise exploring how graphite processing can create more industrial value in Tanzania.',
    image: 'assets/graphite.jpg',
    body: `Graphite Industries Limited is developing a graphite-focused mineral-processing and value-addition business in Tanzania. Our proposed Mpwapwa project brings together feedstock evaluation, processing technology assessment and market engagement.

## Our vision
To become a recognised Tanzanian graphite processing facility and value-addition enterprise connecting mineral resources with regional and global industrial markets.

## Our mission
To develop competitive graphite processing capabilities through responsible resource development, appropriate technologies, strategic partnerships, quality-focused operations and continuous improvement.

## Our strategic objectives
- Advance the technical and commercial evaluation of the proposed graphite project.
- Assess feedstock characteristics and suitable beneficiation technologies.
- Identify potential products through testing and consultation with industrial customers.
- Build relationships with experienced technology providers, research institutions and prospective buyers.
- Evaluate transport, electricity, water and other infrastructure needs.
- Integrate environmental protection, worker safety and community engagement into planning.
- Develop the organisational capabilities and governance needed for responsible implementation.

## How we intend to develop
We aim to evaluate the full path from graphite-bearing ore to useful industrial materials. Each development decision should be supported by representative samples, test results, appropriate engineering and a clear understanding of customer requirements.

Domestic processing could create opportunities for skills development, local participation and stronger links with regional and international markets. The scope and timing depend on feasibility, approvals, financing and implementation decisions.

## The values behind our approach
Integrity, technical excellence, responsible development, partnership, quality and accountability guide our intended approach. We seek reliable information, clear responsibilities and constructive relationships with customers, communities, suppliers and investors.`,
  },
  project: {
    title: 'The Mpwapwa project.',
    summary: 'A proposed graphite processing and value-addition facility in Mpwapwa District, Dodoma Region, Tanzania.',
    body: `Our ambition is to evaluate an integrated route from suitable graphite-bearing feedstock to products that meet industrial requirements. The project is in development and planning; a mine or processing facility has not been commissioned.

## From feedstock to finished material
The proposed processing chain connects six activities. The appropriate route will depend on feedstock characterisation, mineralogical and metallurgical testwork, customer requirements and feasibility. Product pathways may use different stages.

1. Feedstock sourcing and verification — Assess representative ore samples, graphite content, impurities, mineral rights and dependable supply arrangements.
2. Crushing and grinding — Reduce rock size to release graphite from surrounding minerals, balancing separation with preservation of valuable flakes.
3. Beneficiation and flotation — Separate graphite from unwanted minerals. Flotation uses differences in surface properties to produce a concentrate; performance requires testwork.
4. Further purification where required — Evaluate additional physical, thermal or chemical treatment against the intended product’s impurity limits, costs and environmental requirements.
5. Product-specific processing and value addition — Assess sizing, micronisation or expandable-graphite processing independently against technical and market needs.
6. Quality assurance, packaging and delivery — Plan sampling, analysis, product documentation, safe packaging and delivery against agreed customer requirements.

## A development roadmap
These are proposed stages and decision points, not completed milestones or a committed timetable. Evidence from each stage should determine the next investment in work.

1. Feedstock verification — Establish source suitability, lawful access, representative samples and available geological information.
2. Mineralogical and metallurgical testing — Understand mineral composition, separation behaviour, variability and potential product quality.
3. Project definition and pre-feasibility — Compare process routes, product markets, infrastructure needs and preliminary costs.
4. Feasibility, approvals and financing — Develop the selected scope, evaluate risks and establish the permissions and funding needed for an implementation decision.
5. Implementation and commissioning — Subject to preceding decisions, procure, construct, test the facility and validate performance and product quality.
6. Potential expansion — Assess additional capacity or downstream products only where operating evidence and market demand justify them.

## Planning for the place
Mpwapwa District in Dodoma Region is the proposed location. Site boundaries, land tenure and feedstock arrangements require verification. Plant capacity and final configuration will be determined through technical and commercial studies.

Water sourcing and recycling, electricity, safe waste and tailings management, access roads, worker facilities and communications are considerations for evaluation. No utility connection or capacity is represented as secured.

Road and rail options, including relevant central transport corridors, may be assessed for suitability, access, handling, costs and transit times. No dedicated railway access, export route or logistics agreement is claimed.`,
  },
  products: {
    title: 'Graphite, shaped by its purpose.',
    summary: 'Three potential product pathways, each guided by the feedstock, technical testing and the needs of industrial customers.',
    body: `We are evaluating graphite concentrate, micronised graphite and expandable graphite pathways. Product selection requires testing and market assessment; commercial grades, production volumes, sample availability and supply dates are not yet established.

## Graphite concentrate
Concentrate is graphite-rich material produced by separating graphite from surrounding minerals. Potential applications include further processing and suitable foundry, refractory or other industrial uses; refractory materials withstand high temperatures.

Customers may need defined carbon content, impurity limits, flake-size distribution, moisture and consistent quality. This proposed pathway depends on representative feedstock tests and a validated separation process.

## Micronised graphite
Micronised graphite is milled into fine particles. Potential applications include industrial lubricants, friction materials and other formulations where fine particle size is useful.

Customer requirements may cover particle-size distribution, purity, particle shape and performance in a specific formulation. Milling and classification would need to be assessed and qualified before an offering is defined.

## Expandable graphite
Expandable graphite is treated so that its layers expand when heated. In industry, it can form part of fire-protection materials, including coatings that swell to form a protective barrier.

Relevant requirements may include flake size, expansion behaviour, activation temperature, residual chemicals and testing in the final product. This is a potential development pathway, with no company fire-safety certification or product performance established.

## Where graphite can make a difference
Graphite’s conductivity, heat resistance and ability to reduce friction make it useful across several industries. The applications below describe industry uses; suitability of our proposed products would need separate testing and customer qualification.

### Energy Storage
Graphite is used in battery anodes, the electrode that stores lithium during charging. Concentrate is not automatically qualified anode material; further processing, purification and customer qualification are needed. Battery materials remain a future opportunity for evaluation.

### Steel & Foundries
Graphite has uses in steelmaking and heat-resistant materials for handling molten metal. Carbon content, impurities and particle or flake size must suit the particular process.

### Industrial Lubricants
Graphite can help reduce friction between moving surfaces. Particle size, purity and compatibility with the lubricant formulation affect suitability.

### Friction Materials
Graphite is used in materials such as brake linings to help manage friction. Consistent composition and performance in the finished formulation require qualification.

## Start with your requirements
Tell us your intended application, carbon and impurity requirements, particle or flake size, indicative demand, packaging, destination and qualification needs. Share what you know; detailed specifications are optional at this stage.`,
  },
  investors: {
    title: 'A shared ambition. A place to contribute.',
    summary: 'Connect with us about the expertise, technology, market insight and investment needed to evaluate the proposed Mpwapwa project.',
    body: `Domestic graphite processing offers a potential route to retain more industrial value in Tanzania. Our proposal combines phased development, technical evaluation and market engagement so that project scope can respond to evidence and customer needs.

## Contributions we seek
We welcome preliminary discussions with organisations able to contribute to the following areas. These are engagement interests, not announcements of appointed partners or secured commitments.

### Investment partners
Contribute project-development experience, financing insight and an understanding of phased investment and technical risk.

### Engineering & technology
Help assess process routes, equipment options, engineering requirements and potential product-specific technologies.

### Laboratories & technical services
Support representative sampling, mineralogical characterisation, metallurgical testing and product-quality evaluation.

### Feedstock suppliers
Discuss suitable graphite-bearing sources, lawful access, representative samples, technical data and potential supply arrangements.

### Potential buyers
Help define intended applications, product specifications, qualification procedures and indicative market requirements.

### Logistics providers
Help assess handling, packaging, storage, road and rail options, export documentation and delivery requirements.

## How a conversation develops
1. Introduce your organisation — Share a non-confidential outline of your experience, proposed contribution and information needs.
2. Explore the fit — Discuss the technical or commercial scope, relevant evidence and appropriate due diligence.
3. Exchange suitable information — Request available supporting material and agree next steps. Sensitive information should be shared only under appropriate arrangements.

Any cooperation depends on due diligence, agreed terms, internal authorisation and applicable approvals. No investment returns, financing, government support, signed customers or offtake agreements are represented as secured.

## Project information
An introductory project brief is in draft. Contact the team to request suitable supporting information and its current revision. The draft is not a feasibility study, resource or reserve statement, or offer to invest.`,
  },
  sustainability: {
    title: 'Responsibility begins with planning.',
    summary: 'Environmental stewardship, people and accountable governance are intended to shape how the proposed project develops.',
    image: 'assets/graphite.jpg',
    body: `Responsible development requires decisions grounded in the conditions of the place and the people affected. The priorities below are planned areas for assessment and management, rather than measured achievements or certifications.

## Environment
- Water — Assess supply, process demand, recycling opportunities and protection of water resources.
- Energy — Evaluate electricity needs, process efficiency and opportunities to reduce avoidable emissions.
- Waste and tailings — Characterise waste streams and plan suitable containment, treatment and monitoring.
- Pollution and land — Assess dust, noise, biodiversity, rehabilitation and eventual closure needs.

Project-specific investigations and environmental assessment should inform management measures, approvals and monitoring. No zero-impact or carbon-neutral outcome is claimed.

## People & communities
- Worker safety — Plan risk assessment, appropriate training, safe procedures and protective measures.
- Community engagement — Seek constructive dialogue to understand concerns and inform project planning.
- Local participation — Assess opportunities for employment, skills development, local procurement and inclusive participation.

The scope of community and employment initiatives will depend on project definition, consultation and implementation decisions.

## Governance
- Accountability — Develop clear responsibilities, financial controls and oversight of material risks.
- Responsible sourcing — Verify feedstock rights and source suitability, and assess supplier conduct.
- Ethical conduct — Plan procurement integrity, appropriate due diligence and transparent decision-making.
- Compliance and reporting — Identify applicable approvals and establish monitoring before reporting performance.

Targets, baselines and progress reporting should be published when supported by verified information and appropriate management systems.`,
  },
  contact: {
    title: 'Let’s start a conversation.',
    summary: 'Bring your questions, your expertise or your requirements. We’ll help you find the right starting point.',
    body: 'Share a non-confidential introduction and tell us what you would like to explore. Our team will review your enquiry in the website inbox and use your contact details to follow up.',
  },
};
for (const page of seed.pages) Object.assign(page, revisions[page.id] || {});
for (const target of ['app/seed.json', 'public/assets/seed.json']) fs.writeFileSync(path.join(root, target), JSON.stringify(seed, null, 4) + '\n');
console.log('Reviewed content seeds prepared. Database unchanged.');
