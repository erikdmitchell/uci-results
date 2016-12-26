=== UCI Results ===
Contributors: erikdmitchell
Donate link: erikdmitchell@gmail.com
Tags: uci, cycling, bicycle, races
Requires at least: 4.0
Tested up to: 4.7
Stable tag: 0.1.1
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

= 0.1.2 =

* Added trim function to race name.

= 0.1.1 =

* Added updater

= 0.1.0 =

* Now that the plugin is UCI Results, we rolled back the version.

= 2.0.0 =

* A major rework of the entire plugin.
* Now called UCI Results
* Removed all "fantasy" aspects of plugin - see Fantasy Cycling plugin

= 1.1.1 =

* Added rider stats/rankings

* Reworked our FQ

* Removed all custom login integration. Using EM Custom Login to do this.

= 1.1.0 =

* Added fantasy class/extension.
* Added country flags.
* Added graphs and stats for rider rankings.

* Tweaked url link functions.
* Fantasy class brings in custom login/register functionality.

= 1.0.9 =

* First "public release"

* SQL fixes issue with calculating all uci AND wcp NOT max which is only 1st places
* SOS now includes worlds (CN) as max value
* Cleaned up admin
* Built out templates and template system including default page generation.
* Added uci_curl_pagination()
* Lots of bug fixes and build out of templates.

= 1.0.8 =

* Added pagination for rider rankings
* Added shortcodes to display data on user end

* Major Update to get_riders
* removed riders_sort

= 1.0.7 =

* Added a setup config function to our admin allowing for more control.
* Added some admin errors to explain potential issues.
* Added a sort riders function.

* Updated "Select All" button for adding races to db.

* Major rework of RiderStats->get_riders() to increase speed and performance.

= 1.0.6.2 =

* Adjusted rider scoring percentage. Winning percentage is now weighted.

* Minor format and flow tweaks.

* Lots of testing and minor tweaks.

= 1.0.6.1 =

* Developed Admin Testing (debug) setup.

* 2008/2009 Testing

* Added uci_races,uci_rider_data and uci_season_rankings tables to be. Adjusted prefix issues in code.
* Added add_race_results_to_db() to process results as part of curl.
* Added get_race_results_from_db() to get results from db.

* Removed legacy folder and files

* Cleaned up race-stats and rider-stats.
* Cleaned up junk js and css files and code.

* DB Version: 0.0.9

= 1.0.6 =

* Preparation for public release.

* Added bootstrap css for admin structure
* Added ajax based get races table
* Added ajax races to db on curl page
* Added UCIcURLDB class to handle our custom databases

* Fixed select all bug on get races page
* Reformatted and updated readme file.
* Reworked config setup for urls in curl class
* Tweaked limit on races per year.
* Top25_cURL table is top25_races, not supporting a broader uci setup yet
* get_uci_season_rankings() disabled for now

= 1.0.5 =

* Added: a season field to uci_races db.
* Added: season column to uci curl page.
* Added: tabbed navigation to admin section

* Updated: functionality to support the new "season" db field in RiderStats class.
* Updated: functionality to support the new "season" db field in RaceStats class.
* Updated: cURL admin interface to allow inputs of urls as well loading of stored urls.

* Removed: ajax functions from ucu curl class. Also removed results table.

* Bug: you must update FQ twice?!

* Todo: tablesorter is installed, but not working in ViewDB.

* Note: this version involves a heavy amount of testing and tweaks that cannot be documented here.

= 1.0.4 =

* Added: RaceStats class
* Added RiderStats class

* Fixed: code for first weekend of racing - the race ends up w/ a 1/1 in terms of points, so for the whole weekend, we use last years points

= 1.0.3 =

* Added: config to primary curl class.
* Added: a method where we use the previous seasons UCI rankings for the first race of the season. Does not help the second race and needs more controls.

* Fixed: FQ can be updated, still no manual adjustment, but it should not be needed under new system.
* Fixed: various bugs in our FQ class. Wrote some extra functions, cleaned up math and modified it a bit.

* Updated: Pulled out view db functions
* Updated: Cleaned up css and added a seperate css file for the ViewDB class.

* Bug: FQ updater (View DB) needs to be run multiple times when super bulky.

* Todo: The new goal is to just straight upload results, then in a second step calculate all the other data (FQ class)

= 1.0.2 =

* Added: sperate admin menu/section for plugin.
* Added: get_uci_multiplier() and get_world_cup_multiplier() to Field_Quality class. They are our auto detect of sorts.
* Added: get_race_date() to UCI cURL class -- function slows down things a lot
* Added: UCICURLBASE constant for plugin url
* Added: bail out for corrupt data in get_race_results()
* Added: loading modal for "load all data" and "results" click(s)
* Added: a strip js to get_race_results() $html after cURL
* Added: code and check into get_race_data() to speed things up and be able to spit out duplicates and issues in debugging mode -- removed in production due to weird error
* Added: base64 to pre db process hoping to fix char issues
* Added: hard coded FQ when race is accessed via cURL.
* Added: built ajax functions to parse and display inividual race information

* Fixed: Notice: Array to string conversion in /Users/erik/Sites/wordpress/wp-content/plugins/uci-curl/classes/uci-curl.php on line 37
* Fixed: FQ, all set, need to hard code.
* Fixed: date issue, added reformat_date() to the cURL class
* Fixed: some minor file glitches after move things into classes folder.
* Fixed: Field_Quality and built out the class completly
* Fixed: allowing use of our auto load all function and ability to check out certain races - more for debugging, but could see future use

* Updated: get_race_results() now uses a switch case for data
* Updated: formatted db view page output

* Removed: admin page for Field_Quality class b/c it iss no longer needed.
* Removed: lots of junk code.

* Bug: fq WC and UCI pts are outputting zero stuff is producing results of zero.
* Bug: Warning: Division by zero in /Users/erik/Sites/wordpress/wp-content/plugins/uci-curl/classes/field-quality.php on line 250
* Bug: there are still utf8 encoding issues

* Todo: need to use new function to fix char issues
* Todo: need a method to display links and add one at a time (like original function)

== Upgrade Notice ==

2.0.0 is a major overhaul and renaming of plugin. Not backwards compatible.