# AGENTS.md — Comments Shield

Instructions for AI coding agents (and humans) working in this repository, plus the live tracker for the modernisation roadmap.

## Project

- WordPress plugin, slug `comments-shield`, text domain `comments-shield`, prefix `cmsh_` / `CMSH_`.
- Requires WordPress 6.1+, PHP 8.0+. Licence GPL-2.0-or-later.
- Released to WordPress.org by pushing a git tag (`.github/workflows/deploy.yml`).
- Full task descriptions and rationale: [`docs/ROADMAP.md`](docs/ROADMAP.md).

```
comments-shield.php            bootstrap, constants, activation
includes/helpers.php           settings defaults, sanitising, admin URLs
includes/class-cmsh-core.php   front-end and admin behaviour, delete routine
includes/class-cmsh-admin.php  menus, settings pages, form handlers
assets/css/admin.css           admin styles
.wordpress-org/                directory banner and icons (not shipped in zip)
```

## Hard rules

1. **Never copy code from competitor plugins.** Disable Comments is GPL-3.0-or-later and would force a licence change. Implement from WordPress core hooks and documentation.
2. **Never copy SG Security assets.** No `sg-*` class names, stylesheets, icons or logos. Use our tokens under `.cmsh-app`.
3. **No remote assets.** Fonts, scripts and images are bundled or system-provided. No CDN requests.
4. **No tracking or telemetry.**
5. **Security on every entry point:** capability check, nonce check, sanitise input, escape output, `$wpdb->prepare()` only with placeholders.
6. **Do not `require` WordPress core files** (`pluggable.php`, `functions.php`, etc.).
7. **Settings changes need a migration.** Existing 1.x sites must behave identically after update until the admin changes something.
8. **Everything user-facing is translatable** and works in RTL.
9. **Keep dev files out of the release:** anything new at repo root that is not runtime code goes in `.distignore`.
10. **Do not tag or bump `Stable tag`** unless the maintainer asks; a tag publishes to WordPress.org.

## Workflow for each task

1. Pick the lowest unfinished ID in the earliest open phase unless told otherwise.
2. Branch `feat/<ID>-short-name` or `fix/<ID>-short-name`. One task per pull request.
3. Before opening the PR: PHPCS (WordPress standard) clean, Plugin Check clean, tested on latest WordPress and on 6.1, single site and multisite where relevant.
4. In the same PR, update this file: set the task status, add a line to the change log below, and add a readme changelog entry if users will notice the change.
5. If a task turns out to be wrong or needs splitting, edit `docs/ROADMAP.md` and the tracker together and note why in the change log.

Status values: `todo`, `doing`, `review`, `done`, `dropped`.

## Tracker

### Phase 0 — Stabilise (v1.3.1)

| ID | Task | Status | PR |
|---|---|---|---|
| F-01 | Sync `CMSH_VERSION` with plugin header | todo | |
| F-02 | Remove manual `require_once` of core files | todo | |
| F-03 | Safe activation redirect (transient, skip bulk/CLI/network) | todo | |
| F-04 | Fix delete routine: `prepare()` misuse, cache flush, recount `comment_count` | todo | |
| F-05 | Delete result notice via per-user transient | todo | |
| F-06 | Remove dead `render_settings_page()` / `options_page()` | todo | |
| F-07 | Add `uninstall.php` | todo | |
| F-08 | Add `/languages` and `.pot` | todo | |
| F-09 | Make readme match real features | todo | |
| F-10 | Extend `.distignore` (AGENTS.md, docs/, tests/, tooling) | todo | |
| F-11 | PHPCS + Plugin Check in CI; pin action versions | todo | |

### Phase 1 — New interface (v1.4.0)

| ID | Task | Status | PR |
|---|---|---|---|
| U-01 | Design tokens (CSS custom properties under `.cmsh-app`) | todo | |
| U-02 | App shell with URL-backed tabs: Overview, Disable, Delete, Tools | todo | |
| U-03 | Component set: card, setting row, toggle, buttons, notice, stat tile, modal | todo | |
| U-04 | Save settings through REST route without reload | todo | |
| U-05 | Accessible confirm modal replaces inline `confirm()` | todo | |
| U-06 | Accessibility pass (switch role, focus, contrast, keyboard, reduced motion) | todo | |
| U-07 | Responsive layout and RTL stylesheet | todo | |
| U-08 | Menu placement option kept; delete page folded into Delete tab | todo | |
| U-09 | New WordPress.org screenshots and banner | todo | |

### Phase 2 — Disable parity (v1.5.0)

| ID | Task | Status | PR |
|---|---|---|---|
| D-01 | Settings schema v2 with migration from 1.x | todo | |
| D-02 | Scope: everywhere or selected post types | todo | |
| D-03 | Block new and hide existing as separate options (block themes + REST) | todo | |
| D-04 | Block XML-RPC and REST comment submission | todo | |
| D-05 | Full cleanup: feeds, X-Pingback, pingbacks, widgets, blocks, Discussion page, admin bar, columns | todo | |
| D-06 | Allowed comment types (block-editor Notes kept by default) | todo | |
| D-07 | WooCommerce reviews as a separate setting | todo | |
| D-08 | Empty comments template with opt-out | todo | |
| D-09 | Page-cache purge on save and delete | todo | |

### Phase 3 — Delete tool (v1.6.0)

| ID | Task | Status | PR |
|---|---|---|---|
| X-01 | Filters: post type, status, comment type, date range | todo | |
| X-02 | Preview with count and sample list | todo | |
| X-03 | CSV backup with formula-safe escaping | todo | |
| X-04 | Trash (default) or permanent delete | todo | |
| X-05 | Batched AJAX delete with progress and recount | todo | |
| X-06 | Scheduled cleanup of old spam and trash | todo | |

### Phase 4 — Site operations (v1.7.0)

| ID | Task | Status | PR |
|---|---|---|---|
| O-01 | Role-based exclusions | todo | |
| O-02 | Multisite network settings and enforcement | todo | |
| O-03 | WP-CLI commands | todo | |
| O-04 | Export / import settings as JSON with diff preview | todo | |
| O-05 | Site Health integration | todo | |
| O-06 | Blocked-attempt counter and Overview stats | todo | |
| O-07 | Dashboard widget, toolbar menu, Comments-screen shortcut | todo | |

### Phase 5 — Advanced (v2.0.0)

| ID | Task | Status | PR |
|---|---|---|---|
| A-01 | Conditional rules: taxonomy, template, post age, hide by date | todo | |
| A-02 | Abilities API read and write (WP 6.9+) | todo | |
| A-03 | Theme conflict scanner | todo | |
| A-04 | Setup wizard | todo | |
| A-05 | Must-use variant | todo | |

## Change log

Newest first. One line per merged change: date, ID, what changed, PR.

| Date | ID | Change | PR |
|---|---|---|---|
| 2026-11-15 | — | Roadmap and tracker created from competitor review (Disable Comments 2.9.1, Disable Comments RB 2.1.0, SG Security UI) | |
