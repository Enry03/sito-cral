# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Primary users are the employees of the company behind "CRAL Selex ES Chieti" (an office/administrative workforce, not retail or warehouse staff) who may become members ("soci") of the company's after-work association (CRAL). Secondary users are the association's own staff/administrators ("gestori"), who are non-technical and run the back office themselves day to day.

## Product Purpose

A public + members-only website for a company after-work association. It lets any employee discover the association's activities and past events before deciding to join, submit a real membership request through a proper form, and — once approved by a human admin — access a members-only area with association documents and a digital membership card. Success means the association can run its member communications and admin work (approvals, activity postings, document sharing) from one place instead of ad hoc paper/email/spreadsheets.

## Positioning

Unlike a generic template or a Facebook-group/paper-based approach many small company associations fall back on, this site pairs a genuine membership workflow (request → human approval → digital card with QR verification) with a real non-technical back office (Filament admin panel), so the association's own staff can run it without ongoing developer involvement.

## Operating Context

Public visitors browse occasionally, mostly to check upcoming activities or decide whether to join — desktop and mobile both matter. Members check the private area infrequently (documents, their own card). Admin staff use the back office periodically in bursts: posting a new activity, uploading photos or documents, reviewing and approving pending membership requests. The current hosting (Aruba shared hosting, no SSH) has no bearing on visual design but does mean deploys are manual, so design changes should be batched rather than shipped as many tiny increments.

## Capabilities and Constraints

Live today: public Home, Attività (list + detail), Foto gallery, Iscriviti (registration form creating a pending membership request); members-only Dashboard, Documenti (download), Account (personal data + digital tessera with QR code, verifiable at a public `/tessera/{numero}` URL); admin panel (Soci, Attività, Documenti, Foto) built on Filament 3. No real activities/photos/documents are loaded yet — the live site currently shows genuinely empty states. Not yet built (discussed as future ideas, not committed): event RSVP/calendar, partner discounts/convenzioni, an announcements board, polls.

## Brand Commitments

Primary palette (user-specified, binding): **rosso acceso** (bright red) and **bianco** (white) as the two primary colors; **blu navy** as a third color for refinements/gradients/accents only. Org name in use across the site: "CRAL" (short form) / "CRAL Selex ES Chieti" (full — confirmed *not* affiliated with the Selex retail/supermarket group; the name similarity is coincidental, so the palette must not be treated as a Selex brand reference). A real logo and a real physical membership-card design already exist and will be supplied by the user later. Until then, the logo and the tessera artwork must sit in clearly-marked placeholder containers sized/shaped for a real asset drop-in — do not invent a permanent-feeling logo mark or fabricate tessera artwork.

## Evidence on Hand

No real activity photos, documents, or event copy exist yet — the current placeholder copy and empty states are honest, not to be replaced with invented testimonials or fabricated past events. A real logo and a real physical tessera design exist off-site and will be provided by the user.

## Product Principles

1. Anyone can understand the association and browse its activities/photos with zero login — the public site is the trust-building step before someone bothers to fill in the join form.
2. Membership is a real credential, not a decorative feature: the request → approval → digital tessera/QR flow must read as official and trustworthy, not playful or generic.
3. The audience is office workers, not a retail/warehouse crowd — tone and any imagery should skew calm and professional-but-warm rather than high-energy retail.
4. The back office is used by non-technical staff — the visual system has to extend cleanly into the Filament admin UI, not just the public marketing pages.
5. Real brand assets (logo, tessera artwork) are coming later — every design decision must leave an obvious, clean slot for them rather than building around a fake logo that will need to be re-designed around later.
