# Comments Shield roadmap

Baseline: v1.3.0 (commit `82a9fee`). Reviewed 2026-11-15 against Disable Comments 2.9.1 (WPDeveloper) and Disable Comments RB 2.1.0, with SG Security (Security Optimizer) as the visual reference.

Status of every task lives in the tracker in [`AGENTS.md`](../AGENTS.md). This file explains what each task is and why.

## Where we are

Comments Shield 1.3.0 is about 500 lines of PHP: six on/off toggles stored in one option (`cmsh_settings`), a delete-everything button, and a plain Settings API page with one small CSS file. No JavaScript, no multisite handling, no uninstall routine, no translations folder.

| Area | Comments Shield 1.3.0 | Disable Comments 2.9.1 | Disable Comments RB 2.1.0 |
|---|---|---|---|
| Disable everywhere | Yes (toggles) | Yes | Yes |
| Disable per post type | No | Yes | Yes |
| Block new vs. hide existing, separately | Partly | Yes | Yes |
| XML-RPC / REST comment blocking | No | Yes | Yes |
| Comment feeds, X-Pingback, widgets, Discussion page | No | Yes | Partly |
| Keep block-editor Notes (WP 6.9+) working | No | Yes | Yes |
| WooCommerce reviews as own setting | Preserved on delete only | Yes | PRO |
| Delete by post type / status / comment type | No (all or nothing) | Yes | Yes |
| Preview before delete, CSV backup | No | Yes | Count only |
| Trash instead of permanent delete | No | No | Yes |
| Role-based exclusions | No | Yes | PRO |
| Multisite network control | No | Yes | PRO |
| WP-CLI | No | Yes | PRO |
| Export / import settings | No | Yes | PRO |
| Site Health integration | No | Yes | Yes |
| Blocked-attempt statistics | No | Yes | Stats tab (PRO) |
| Dashboard widget, toolbar menu, Comments-page shortcut | No | No | Yes |
| Conditional rules (taxonomy, template, post age) | No | Yes | Hide by date (PRO) |
| Abilities API (AI / MCP clients, WP 6.9+) | No | Yes | No |
| Theme conflict scanner | No | Yes | No |
| Setup wizard | No | Yes | No |
| AI auto-replies | No | No | PRO |

Our opening: RB keeps roles, multisite, WP-CLI, export/import and full statistics behind PRO. Shipping those free, in a cleaner interface, is the positioning.

## Rules that shape the plan

1. **No copied code.** Disable Comments is GPL-3.0-or-later; Comments Shield is GPL-2.0-or-later. Pasting their code would force a licence change. Reimplement behaviour from the WordPress hooks, not from their source.
2. **Own look, SG-inspired.** Match the feel of SG Security (soft grey canvas, white rounded cards, large toggles, title plus description rows). Do not ship their stylesheet, class names, icons or logo.
3. **Fonts are bundled or system.** SG uses Poppins and Roboto. WordPress.org does not allow loading fonts from Google's CDN, so either bundle the WOFF2 files (both are open-licensed) or fall back to the system stack.
4. **Readme stays truthful.** The current readme promises export of comment data and "priority support"; neither exists. Fix the text in Phase 0 and only re-add claims when the feature ships.
5. **No tracking.** The readme says no personal data is collected. Do not add a usage tracker like the competitor's.

## Phase 0 — Stabilise (v1.3.1)

Bugs found in the current code. Small, safe, ship first.

- **F-01 Version constant.** `CMSH_VERSION` is `1.2.1` while the header says `1.3.0`, so the stylesheet cache-busts with the wrong version.
- **F-02 Remove core `require_once` calls.** `comments-shield.php` and `class-cmsh-admin.php` load `pluggable.php`, `functions.php` and admin includes by hand. Loading `pluggable.php` early stops other plugins from overriding pluggable functions. None of these lines are needed.
- **F-03 Activation redirect.** The `activated_plugin` hook redirects and exits unconditionally. That breaks bulk activation, WP-CLI activation and network activation. Use a transient set on activation and redirect on the next `admin_init`, skipping bulk, CLI, AJAX and multisite network cases.
- **F-04 Delete routine.** `$wpdb->prepare()` is called without placeholders (a `_doing_it_wrong` notice), `clean_comment_cache( $wpdb->insert_id )` clears nothing useful, and `comment_count` on posts is never recalculated, so themes keep showing the old numbers. Recount per post and flush properly.
- **F-05 Admin notice storage.** The delete result is stored in an option. Use a short-lived per-user transient.
- **F-06 Dead code.** `render_settings_page()` and `options_page()` are unused duplicates of the two real pages.
- **F-07 `uninstall.php`.** Remove `cmsh_settings` and any other data on uninstall.
- **F-08 Translations.** Header declares `Domain Path: /languages` but the folder does not exist. Add it with a `.pot` file.
- **F-09 Readme accuracy.** Remove unimplemented claims, duplicate feature bullets and the outdated upgrade notice.
- **F-10 Packaging.** Add `AGENTS.md`, `docs/`, `tests/`, `package.json`, `phpcs.xml` and build sources to `.distignore` so they never reach WordPress.org.
- **F-11 Tooling.** Add PHPCS with WordPress Coding Standards and run Plugin Check in CI on every pull request. The deploy workflow uses `actions/checkout@master`; pin it to a release tag.

## Phase 1 — New interface (v1.4.0)

Rebuild the admin screens before adding features, so each later feature lands in its final place.

Design tokens measured from the SG Security screen (use as a starting point, then adjust the accent to our own brand colour):

| Token | Value |
|---|---|
| Canvas | `#F9F9F9` |
| Card | `#FFFFFF`, radius 12px, padding 20px |
| Card shadow | `0 0 20px rgba(0,0,0,.05), 0 1px 5px rgba(0,0,0,.05)` |
| Text | `#363636` |
| Page title | 30px / 600, display font |
| Section title | 20px / 600, display font |
| Body | 16px / 400, text font |
| Toggle | 56 × 32px, pill, accent when on (`#4343F0` in SG; pick ours) |
| Row layout | Title and description left, toggle right, hairline divider between rows |

- **U-01 Design tokens.** One `:root`-scoped set of CSS custom properties under `.cmsh-app`; every component reads from them.
- **U-02 App shell.** Header with plugin name and version, intro card, and tabs: Overview, Disable, Delete, Tools. Tab is reflected in the URL so it survives refresh and can be linked.
- **U-03 Components.** Card, setting row, toggle, checkbox group, radio card, button set (primary, secondary, danger), badge, notice, stat tile, modal. Plain PHP templates plus a small vanilla JS file; no React build step is needed at this size.
- **U-04 Save without reload.** Settings save through a REST route (`comments-shield/v1/settings`) with nonce and `manage_options` check; inline success and error states.
- **U-05 Confirm dialog.** Replace the inline `confirm()` with an accessible modal that states what will be deleted.
- **U-06 Accessibility.** Toggles are real checkboxes with `role="switch"`, visible focus rings, labels tied to inputs, 4.5:1 text contrast, full keyboard use, respects `prefers-reduced-motion`.
- **U-07 Responsive and RTL.** Works at 782px and below; generate an RTL stylesheet.
- **U-08 Menu placement.** Keep the existing top-level versus Settings option; fold the separate Tools-menu delete page into the Delete tab.
- **U-09 Store assets.** New screenshots and banner for WordPress.org once the interface is final.

## Phase 2 — Disable parity (v1.5.0)

- **D-01 Settings schema v2.** Move from six booleans to a structured option (mode, post types, what to block, what to hide, exclusions) with a versioned migration from 1.x that preserves current behaviour exactly.
- **D-02 Scope.** Everywhere, or selected post types (including custom types registered later). "Everywhere" must cover types added after saving.
- **D-03 Block new and hide existing as separate choices.** Hiding must also work on block themes and in REST output, not only through `comments_array`.
- **D-04 Channels.** Block comments through XML-RPC and the REST API, with separate toggles.
- **D-05 Full cleanup when disabled.** Comment feeds redirect to the post, `X-Pingback` header removed, outgoing pingbacks off, Recent Comments widget and comment blocks unregistered, Discussion settings page and admin-bar item hidden, comment columns and quick-edit fields removed.
- **D-06 Allowed comment types.** Keep block-editor Notes (WP 6.9+) working by default; filterable list for other types.
- **D-07 WooCommerce reviews.** A separate setting: keep reviews open while comments are off, or close reviews too.
- **D-08 Empty comments template.** Replace the theme's comment template when disabled, with a constant or filter to opt out.
- **D-09 Cache purge.** Purge page caches (common cache plugins and hosts) after settings change or deletion, so cached pages stop showing the form.

## Phase 3 — Delete tool (v1.6.0)

- **X-01 Filters.** Delete by post type, status (approved, pending, spam, trash), comment type and date range.
- **X-02 Preview first.** Show the count and a sample list (author, date, post, excerpt) before anything is removed.
- **X-03 CSV backup.** Download the matched comments before deleting. Escape every free-text cell so nothing runs as a spreadsheet formula.
- **X-04 Trash or permanent.** Offer move-to-trash as the default, permanent delete as the explicit choice.
- **X-05 Batching.** Process in chunks over AJAX with a progress bar so large sites do not time out; recount `comment_count` at the end.
- **X-06 Scheduled cleanup.** Optional recurring removal of spam and trash older than N days.

## Phase 4 — Site operations (v1.7.0)

- **O-01 Role exclusions.** Chosen roles keep commenting and keep the Comments admin screens.
- **O-02 Multisite.** Network settings page, enforce on all or selected sites, per-site override when allowed.
- **O-03 WP-CLI.** `wp comments-shield status | set | delete | export | import`, with `--dry-run` on delete.
- **O-04 Export / import.** Settings as JSON with a diff preview before applying.
- **O-05 Site Health.** Report current mode, channels blocked and any conflict found.
- **O-06 Blocked-attempt counter.** Count rejected comment, trackback, XML-RPC and REST attempts; show on Overview and in a dashboard widget. Counts only, no visitor data.
- **O-07 Shortcuts.** Optional dashboard widget, admin toolbar menu and a button on the native Comments screen.

## Phase 5 — Advanced (v2.0.0)

- **A-01 Conditional rules.** Disable or re-enable by taxonomy term, page template or post age (auto-close after N days), and hide existing comments by date.
- **A-02 Abilities API.** Read and write abilities for WordPress 6.9+ so AI assistants and MCP clients can query and change settings, gated on the same capabilities as the screens.
- **A-03 Theme conflict scanner.** Detect a theme that still prints a comment form after disabling and explain the fix.
- **A-04 Setup wizard.** Three-step first-run flow: scope, existing comments, cleanup.
- **A-05 Must-use variant.** Optional mu-plugin build for agencies.

## Decided against, for now

- **AI auto-replies** (RB PRO). Different product: it answers comments, we remove them. Revisit only on user demand.
- **Usage tracking and cross-promotion panels.** Conflicts with the no-data promise and the clean look.

## Suggested order and size

| Phase | Version | Rough effort |
|---|---|---|
| 0 Stabilise | 1.3.1 | 2–3 days |
| 1 Interface | 1.4.0 | 1.5–2 weeks |
| 2 Disable parity | 1.5.0 | 2 weeks |
| 3 Delete tool | 1.6.0 | 1.5 weeks |
| 4 Site operations | 1.7.0 | 2–3 weeks |
| 5 Advanced | 2.0.0 | 3–4 weeks |

Effort figures are estimates for one developer and should be revised after Phase 1.
