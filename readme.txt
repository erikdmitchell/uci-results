=== UCI Results ===
Contributors: erikdmitchell
Donate link: erikdmitchell@gmail.com
Tags: uci, cycling, bicycle, races
Requires at least: 4.0
Tested up to: 4.7.3
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Pulls in race results from the UCI website and adds it to your site.

== Description ==

Pulls in race results from the UCI website and adds it to your site.

== Installation ==

1. Upload `uci-results` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the UCI Results admin tab for more and to setup.

== Frequently Asked Questions ==

coming soon...

== Screenshots ==

coming soon...

== Hooks and Filters ==

coming soon...

== Changelog ==

= 1.0.0 =

* DB version 1.0.0
* Completely integrated into REST API
* Virtually all of our components are custom post types, taxonomies and/or post meta.
* Eliminates almost all of the custom db tables (4 remain)
* Massive speed increase

= 0.1.5 =

* Database upgraded to version 0.2.1
* The cron job log is now controlled through admin settings. You can enable, view and clear the log.

= 0.1.4 =

* Reworked our backend for automatically adding races. Synched WP CLI and cron job functionality.
* Added an automated class to handle our cron job/wp cli automatic functions.
* Integrated duplicate rider cleanup functions into WP CLI.

= 0.1.3 =

* Database upgrade to version 0.2.0. Mainly added indexes for faster queries.

= 0.1.2 =

* Added trim function to race name. Also added this as part of db upgrade.
* Added db update/upgrade functionality.
* Added admin override for custom templates.

= 0.1.1 =

* Added updater

= 0.1.0 =

* Now that the plugin is UCI Results, we rolled back the version.
* A major rework of the entire plugin.
* Now called UCI Results
* Removed all "fantasy" aspects of plugin - see Fantasy Cycling plugin
* Previous log removed. It is in git somewhere if need be.

== Upgrade Notice ==

* 1.0.0 is a massive restructuring of this plugin. It includes a migration page that will help.
* 0.1.0 is a major overhaul and renaming of plugin. Not backwards compatible.