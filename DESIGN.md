# Design

<!-- impeccable:design-schema 1 -->

## Scope

This system covers the **public marketing pages only**: home, attività (list + detail), foto, iscriviti (registration), login, attesa-approvazione, tessera/verifica, and the shared `layouts/public.blade.php` + `layouts/guest.blade.php` shells. The private member area (dashboard, documenti, account), the Filament admin panel, and all shared Blade components used by both worlds (`x-text-input`, `x-input-label`, `x-primary-button`, `x-application-logo`) were deliberately left untouched — those pages bypass the shared components and use inline-styled markup instead, specifically so this system never leaks into the private/admin surfaces. Do not apply these tokens or classes outside the files listed above without deciding that on purpose.

## World

**Jet-age airline ticket / coupon-book.** Membership here is a real, validated credential (request → human approval → tessera with QR), so the site borrows the visual grammar an office worker already reads as "official paperwork that matters" — a boarding-pass/coupon book — rather than a soft SaaS gradient hero. Chosen via the impeccable direction-seed process (seed `2e692ca2`, own list index 3) fused with dealt challenger `vernacular-ephemera-jet-age-ticket-wallet`, which won on both audience identification and product clarity.

## Palette

Full-palette strategy, three named roles (per PRODUCT.md's binding brand commitment: bright red + white primary, navy for accents — no fourth hue):

- `cral-red` `#E4002B` — action color: primary buttons, date/stamp tags, active nav underline, the closing CTA section background. Never diluted into a gradient.
- `cral-navy` `#14213D` — structural ink: all body text, borders/rules, header/footer backgrounds, secondary buttons.
- `cral-navy-soft` `#4A5878` — muted navy for secondary/caption text only.
- `cral-cream` `#F5F2EA` — page ground (the "coupon stock").
- `cral-paper` `#FFFDF9` — reserved for card surfaces that need to read as a lighter stock than the page (currently unused directly; white `#FFFFFF` is used for card surfaces instead).

Defined in `tailwind.config.js` under `theme.extend.colors.cral`.

## Type

Two voices, both loaded via bunny.net alongside the pre-existing Figtree (Figtree is untouched — it's still the default `font-sans` used by the private area):

- **`font-display` → Anton** — display/headline voice only. Always uppercase, tight leading (`leading-[0.95]`), used for H1/H2 and the ticket header wordmark. Never used for body copy or UI chrome — it's illegible at small sizes.
- **`font-grotesk` → Archivo** — everything else: nav, labels, body copy, form fields, buttons. Bold + wide tracking (`tracking-[0.15em]`/`tracking-[0.2em]`) for caps labels (nav items, section kickers, form field labels); regular weight for paragraph text.

Both are new `fontFamily` keys in `tailwind.config.js`, added alongside (not replacing) the existing `sans` key.

## Recurring components/patterns

- **Section kicker**: `/// LABEL` — a literal triple-slash prefix in `text-cral-red text-xs font-bold tracking-[0.2em] uppercase` before section headings and panel headers. This is the single most repeated device in the system; use it, don't invent a different kicker style.
- **Sharp edges only**: `border-2 border-cral-navy`, zero `rounded-*` anywhere in this system (the previous version's `rounded-xl`/`rounded-2xl` was explicitly the thing being replaced). Buttons, cards, inputs, badges — all square corners.
- **Hard offset shadow** (`.stack-shadow` / `.stack-shadow-sm` / `.stack-shadow-red` in `resources/css/app.css`): a `6px 6px 0 0` (or `4px 4px 0 0`) solid-color offset shadow, no blur — reads as a stacked paper/ticket, not a soft SaaS glow. Primary CTAs pair it with a `hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-none` press-down interaction.
- **Ticket punch notches** (`.ticket-card` in `resources/css/app.css`): circular cutout notches on a card's left/right edge via `::before`/`::after`, colored via the `--notch-bg` custom property to match whatever sits behind the card. Add modifier class `ticket-card--no-notch` to opt out (used on the manifest list rows, which are ticket-styled without physical notches). Set `--notch-bg` inline (or via a wrapping context) whenever the card sits on a background other than `cral-cream`.
- **Tear line** (`.tear-line` horizontal / `.tear-line-v` vertical): a dashed repeating-gradient rule simulating a perforated coupon edge, used as a divider between a card's header info and its meta row.
- **Numbered leg tab**: a small `h-6 w-6`/`h-7 w-7` navy square with a bold white index number, used on manifest/activity list items to read as a flight-leg number.
- **Date/stamp tag**: `bg-cral-red text-white text-[10px] font-bold tracking-widest uppercase px-2 py-1`, used wherever a date needs to read as a boarding-pass date stamp.

## Layout notes

- Header (`layouts/public.blade.php`) is sticky, cream background, `border-b-2 border-cral-navy`, nav items get a red underline `border-b-2 border-cral-red` when active. Mobile collapses to a hamburger that becomes an X (both are `x-show` SVGs inside the same button); the panel below is a full-width stacked menu, not a slide-over — verified working after a clean page load (a stale Vite-HMR session state can make Alpine's `x-show` appear stuck; that's a dev-only testing artifact, not a code issue, and does not reproduce on a fresh load).
- Footer is a full navy panel, `border-t-4 border-cral-red`, columns use the `///` kicker pattern.
- The tessera card shown on the homepage is explicitly illustrative ("Anteprima — il design definitivo arriva a breve") — per PRODUCT.md, a real logo and real physical tessera design are coming from the user later, so nothing here should be mistaken for final brand artwork.

## What is intentionally NOT part of this system

- No gradients, no blurred/soft shadows, no glassmorphism, no rounded corners.
- No fourth color — resist the temptation to add a neutral gray; use `cral-navy-soft` or opacity on navy/cream instead.
- Not applied to `resources/views/dashboard.blade.php`, `documenti/`, `account/`, `profile/`, or anything under `layouts/app.blade.php` / `layouts/navigation.blade.php` — those remain the original Figtree/rounded/orange system on purpose.
