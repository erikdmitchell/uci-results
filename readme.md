UCI cURL
===========

Pulls in results via cURL from the UCI website. 

Usage Instructions
===========

Coming Soon

Changelog
===========

### 1.0.6

	Preparation for public release.
	
	Reformatted and updated readme file.

### 1.0.5

	Added: a season field to uci_races db.
	Added: season column to uci curl page.
	Added: tabbed navigation to admin section
	
	Updated: functionality to support the new "season" db field in RiderStats class.
	Updated: functionality to support the new "season" db field in RaceStats class.	
	Updated: cURL admin interface to allow inputs of urls as well loading of stored urls.	
	
	Removed: ajax functions from ucu curl class. Also removed results table.
	
	Bug: you must update FQ twice?!
	
	Todo: tablesorter is installed, but not working in ViewDB.
	
	Note: this version involves a heavy amount of testing and tweaks that cannot be documented here.

### 1.0.4
	
	Added: RaceStats class
	Added RiderStats class
	
	Fixed: code for first weekend of racing - the race ends up w/ a 1/1 in terms of points, so for the whole weekend, we use last years points

### 1.0.3

	Added: config to primary curl class.
	Added: a method where we use the previous seasons UCI rankings for the first race of the season. Does not help the second race and needs more controls.

	Fixed: FQ can be updated, still no manual adjustment, but it should not be needed under new system.
	Fixed: various bugs in our FQ class. Wrote some extra functions, cleaned up math and modified it a bit.

	Updated: Pulled out view db functions
	Updated: Cleaned up css and added a seperate css file for the ViewDB class.
	
	Bug: FQ updater (View DB) needs to be run multiple times when super bulky.
	
	Todo: The new goal is to just straight upload results, then in a second step calculate all the other data (FQ class)
 
### 1.0.2 and Prior

	Added: sperate admin menu/section for plugin.
	Added: get_uci_multiplier() and get_world_cup_multiplier() to Field_Quality class. They are our auto detect of sorts.	
	Added: get_race_date() to UCI cURL class -- function slows down things a lot
	Added: UCICURLBASE constant for plugin url
	Added: bail out for corrupt data in get_race_results()
	Added: loading modal for "load all data" and "results" click(s)
	Added: a strip js to get_race_results() $html after cURL
	Added: code and check into get_race_data() to speed things up and be able to spit out duplicates and issues in debugging mode -- removed in production due to weird error
	Added: base64 to pre db process hoping to fix char issues
	Added: hard coded FQ when race is accessed via cURL.
	Added: built ajax functions to parse and display inividual race information

	Fixed: Notice: Array to string conversion in /Users/erik/Sites/wordpress/wp-content/plugins/uci-curl/classes/uci-curl.php on line 37
	Fixed: FQ, all set, need to hard code.
	Fixed: date issue, added reformat_date() to the cURL class
	Fixed: some minor file glitches after move things into classes folder.
	Fixed: Field_Quality and built out the class completly
	Fixed: allowing use of our auto load all function and ability to check out certain races - more for debugging, but could see future use

	Updated: get_race_results() now uses a switch case for data
	Updated: formatted db view page output

	Removed: admin page for Field_Quality class b/c it iss no longer needed.
	Removed: lots of junk code.

	Bug: fq WC and UCI pts are outputting zero stuff is producing results of zero.
	Bug: Warning: Division by zero in /Users/erik/Sites/wordpress/wp-content/plugins/uci-curl/classes/field-quality.php on line 250 
	Bug: there are still utf8 encoding issues

	Todo: need to use new function to fix char issues
	Todo: need a method to display links and add one at a time (like original function)

Credits
===========

This plugin is built and maintained by [@erikdmitchell](http://erikmitchell.net "@erikdmitchell")

License
===========

GPL 2 I think








### Todo
	
	Clean up readme file.
	Will develop an automater class (semi exists in curl class with get_race_data())
	clean up js 
	cURL ajax may no longer be needed
	write functions/code to diplay races and details (shortcode perhaps) and rank riders
	add db setup
	Spit out add to db stuff first, then load rest or something along those lines	

	Loaded Years:
		2013/2014
		2012/2013 - FQ
		2011/2012 - FQ
		2010/2011 - FQ

	To Be Loaded
		
		2009/2010 - Check date format
		2008/2009 - Check date format
		
	Load previous final standings

===== OLD README DATA =====

TODO: uci-curl.php (99%) - CONTAINS EVERYTHING
 - works well
 - needs clean up and a lot more functionality
 - need some sort of auto run function to run race results
 - build in error log and email
 - pull out all variables to class level
 - UTF-8 html before storing in db
 - make a click button in class