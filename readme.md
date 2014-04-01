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

== LAST DATE IN GIT: 4/1/14 (init) ==