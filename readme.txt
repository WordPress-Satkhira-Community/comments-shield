=== Comments Shield - Disable and Remove Comment Spam ===
Contributors: monarchwp23, wpdelower, zakir021063008, wpsatkhira
Tags: comments, spam, disable comments, delete comments, cleanup
Requires at least: 6.1
Tested up to: 6.8
Stable tag: 1.3.1
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Comments Shield lets you disable comment support on your site, hide existing comments, and permanently remove comment records while keeping WooCommerce review notes intact.

== Description ==

Comments Shield is a lightweight plugin for responsibly shutting down WordPress comments when they are no longer needed.

It includes:

* disable comment support across post types
* close comment forms on the front end
* hide existing comments from visitors
* remove the Comments admin menu and dashboard widget
* delete all standard comments in a single action while preserving WooCommerce review records

The plugin is intentionally lean: no remote assets, no telemetry, and no bundled tracking scripts.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Comments Shield in the admin menu and choose the settings you want.
4. Use the Delete Comments screen to remove existing comment records.

== Frequently Asked Questions ==

= Does the plugin remove existing comments from the database? =

Yes. The Delete Comments screen can permanently remove standard comments from the database while preserving WooCommerce reviews and notes.

= Can I leave the admin menu in place? =

Yes. The settings page can stay in the main menu or be moved under Settings depending on your preference.

= Does this plugin collect data? =

No. Comments Shield does not send usage or visitor data to any external service.

== Changelog ==

= 1.3.1 (2026-11-15) =
* Stabilise the release: fix the version mismatch, remove unsafe WordPress core requires, harden the activation redirect, repair the delete routine, switch to per-user transient notices, add uninstall cleanup, add the language catalog, and bring the packaging and CI checks in line with release requirements.
