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

== LAST DATE IN GIT: 4/3/14 (12pm) ==

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
Note: in some instances "total" in FQis greater than 1. Need to double check.
Note: dynamic fq generation takes a while, we should hard code it into deb as object, when adding race to db in cURL class.