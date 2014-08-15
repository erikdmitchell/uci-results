uci-curl.php (99%) - CONTAINS EVERYTHING
 - works well
 - needs clean up and a lot more functionality
 - need some sort of auto run function to run race results
 - build in error log and email
 - pull out all variables to class level
 - UTF-8 html before storing in db
 - make a click button in class# This is my README


Changes (4/1/14)
added code and check into get_race_data() to speed things up and be able to spit out duplicates and issues in debugging mode -- removed in production due to weird error

added base64 to pre db process hoping to fix char issues
need to use new function to fix char issues
need a method to display links and add one at a time (like original function)

== LAST DATE IN GIT: 4/17/14 (3pm) ==

Changes 4/2-4/3
removing a lot of junck code
built ajax functions to parse and display inividual race information
allowing use of our auto load all function and ability to check out certain races - more for debugging, but could see future use
get_race_results() now uses a switch case for data
added bail out for corrupt data in get_race_results()
added a strip js to get_race_results() $html after cURL
there are still utf8 encoding issues
added loading modal for 'load all data' and 'results' click(s)
formatted db view page output

4/4
moved things into classes folder
fixed some minor file glitches after move
added get_race_date() to UCI cURL class -- function slows down things a lot
added UCICURLBASE constant for plugin url

4/8
fixed date issue, added reformat_date() to the cURL class
cleaned up Field_Quality and built out the class completly
TODO: set Field_Quality() -> $start_of_season dynamically or something.
Added get_uci_multiplier() and get_world_cup_multiplier() to Field_Quality class. They're our auto detect of sorts.
	- needs testing with multiple seasons in db
Disabled admin page for Field_Quality class b/c it's no longer needed.
Note: in some instances "total" in FQis greater than 1. Need to double check. OK EM

4/17
Fixed FQ, all set, need to hard code.
Error:? Warning: Division by zero in /Users/erik/Sites/wordpress/wp-content/plugins/uci-curl/classes/field-quality.php on line 250

4/18
Fixed: Notice: Array to string conversion in /Users/erik/Sites/wordpress/wp-content/plugins/uci-curl/classes/uci-curl.php on line 37

4/28
Added sperate admin menu/section for plugin.
Hard coded FQ when race is accessed via cURL.
ERROR: fq WC and UCI pts are outputting zero stuff is producing results of zero.
 - This is based on previous races, so we need to make sure the first few races are going in properly.
 - Will need to come up with some sort of mod for this testing.
 
### Version 1.0.3

 Pulled out view db functions
 The new goal is to just straight upload results, then in a second step calculate all the other data (FQ class)
 Cleaned up css and added a seperate css file for the ViewDB class.
 Added config to primary curl class.
 FQ can be updated, still no manual adjustment, but it shouldn't be needed under new system.
 Added a method where we use the previous seasons UCI rankings for the first race of the season. Doesn't help the second race and needs more controls.
 Fixed various bugs in our FQ class. Wrote some extra functions, cleaned up math and modified it a bit.
 
 Bug: FQ updater (View DB) needs to be run multiple times when super bulky.
 
### Version 1.0.4
	
	Added RaceStats class
	
	Fixed code for first weekend of racing - the race ends up w/ a 1/1 in terms of points, so for the whole weekend, we use last years points


TODO: clean upand standardize config for curl class
TODO: Will develop an automater class (semi exists in curl class with get_race_data())
TODO: clean up js 
TODO: cURL ajax may no longer be needed
4. TODO: try accessing 2012/2013 season
5. TODO: write functions/code to diplay races and details (shortcode perhaps) and rank riders
TODO:
TODO: add db setup
TODO: Spit out add to db stuff first, then load rest or something along those lines