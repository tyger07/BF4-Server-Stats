## BF4 Server Stats Webpage Overview

This page requires the use of a stats database which is created by XpKiller's Procon "BF4 Chat, GUID, Stats and Mapstats Logger" Plugin.
If you need help with XpKiller's stats logger plugin, you must seek assistance in XpKiller's plugin thread:
https://forum.myrcon.com/showthread.php?6698-Chat-GUID-Stats-and-Mapstats-Logger-1-0-0-2

For best compatibility with this webstats page, use the following settings in XpKiller's Procon logging plugin:
"Enable Statslogging?" : Yes
"Enable Weaponstats?" : Yes
"Enable Livescoreboard in DB?" : Yes
"tableSuffix" : None
"MapStats ON?" : Yes
"Session ON?" : Yes
"Save Sessiondata to DB?" : Yes
"Log playerdata only (no playerstats)?" : No

This webpage code requires that you have access to a web server running a modern version of php and requires that you have permission to modify files on the web server.


## Help and Support

Visit the following forum topic:
https://forum.myrcon.com/showthread.php?6854


## Installation Steps

1) Download the following file:
https://github.com/tyger07/BF4-Server-Stats/zipball/master

2) Extract the files. (maintain the original folder structure)

You may change the appearance of the page by modifying the stats.css file in the common folder.

3) Fill in the required parameters in config.php.

Note:  You may not include single quotation marks (') in the following fields without also using an appropriate php delimiter.
For instance, you may not call your clan 'Ty_ger07's Clan' as it would cause a php compilation error.

For example, this would not work:

$clan_name = 'Ty_ger07's Clan';

Using a delimiter would cause it to work:

$clan_name = 'Ty_ger07\'s Clan';

4) Upload to your php-enabled web server and enjoy!


## You must fill in the following information in config.php which is in the config folder.

**1)** Input your stats database host, stats database user name, stats database password, and stats database name.

// DATABASE INFORMATION

DEFINE('HOST', '');													// database host address

DEFINE('PORT', '3306');												// database port - default is 3306

DEFINE('NAME', '');													// database name

DEFINE('USER', '');													// database user name - sometimes the same as the database name

DEFINE('PASS', '');													// database password


For example:

// DATABASE INFORMATION

DEFINE('HOST', '100.200.300.400');									// database host address

DEFINE('PORT', '3306');												// database port - default is 3306

DEFINE('NAME', 'database');											// database name

DEFINE('USER', 'user');												// database user name - sometimes the same as the database name

DEFINE('PASS', 'pass');												// database password


**Note:** Some web server providers (such as GoDaddy) use the same value for database name and database user name.


**2)** Input your clan name as you would like it to appear in the stats pages.

$clan_name = ''; // your gaming clan or organization name


For example:

$clan_name = 'MyClan'; // your gaming clan or organization name


**3)** Input your desired banner image URL if you want one other than the default to be displayed.

$banner_image = './images/bf4-logo.png'; // your desired page banner


**4)** Enter the URL which you would like users to redirect to if they click your banner image.

$banner_url = 'http://tyger07.github.io/BF4-Server-Stats/'; // where clicking the banner will take you


Enjoy!


## Changelog:

01-28-2017
- Unbanned bot access and made bot access restricted instead.  This allows search engines to crawl the stats page and get more clan/community attention while also restricting bot database performance impact to some slower features.

01-07-2017:
- Removed server IP address from server banner image
- Added basic AdKats integration to highlight a player if they are banned or warned if running AdKats plugin
- Optimizations and improvements: SQL queries combined where possible to reduce the SQL server connection pool size (can drasticly improve performance in some cases with huge databases at remote locations), server HTML banner uses jquery to reload content instead of reloading the whole HTML document (less distracting reloads), file size reductions for CSS, JS, and some image files, background image resizes width to fit various resolutions better, etc.

08-29-2016:
- Removed some potential XSS exploits (thanks zer0sec)
- Removed "second-order" or "type 2" attack vectors for attackers who may try to craft specific server chat messages or user names to attempt an attack against the website or database
- Fixed an issue with the `tyger_stats_sessions` table where it was
saving `SID` as INT(11) instead of VARCHAR(100) which caused combined
stats page visitor sessions not to be counted correctly.  Drop your
`tyger_stats_sessions` table and let it rebuild that table using the new
code if you are affected.
- Made it so you can select and copy text in the chat page instead of
making all the content one giant link.

04-18-2016:
- Fixed PHP Notice caused by leftover no longer used variable in signature.php (thanks marshyonline)

02-12-2016:
- Added new maps and weapons (thanks pwk)
- Added git-ignore (thanks nikez)

01-21-2015:
- Added visual indicator of server location uncertainty to server banner image in the case that the server's location is not known and is instead guessed

01-16-2015:
- Fixed Gamemode undefined error in maps page

01-15-2015:
- Added optional query string parameters to resize server banner image output as requested

01-08-2015:
- Fixed duplicate loading of javascript resources causing reduced performance
- Added a few elements into stats.css

01-01-2015:
- Fixed a reported display bug in Firefox
- Did a little code conformity of the player page

12-31-2014:
- Cleaned up a lot of spaghetti code
- New directory structure to make editing/upkeep easier
- More asynchronous loading of page contents for a smoother/faster experience
- Bug fixes and performance improvements
- Added missing Final Stand weapons

12-07-2014:
- Fixed missing banner_cache folder causing banner graphs not to appear

12-03-2014:
- Increased font size in banner graph for better visibility for those with wonky php GD extension
- Fixed serverbanner.php directory location in server info tab
- Removed combined rank calculation from combined player stats pages due to large database load for those with many combined players

11-22-2014:
- Made alignment changes to banner image and fixed graph going off the chart in some cases in banner image
- Added optional "&cc=.." URL query string variable to change the country flag displayed in the banner image

11-21-2014:
- Added Final Stand maps and ranks
- Added GameTracker style server banner signature images to the Server Info page Banners tab
- Added Soldier Name search on Leaderboard

11-16-2014:
- Added pagination to Suspicious Players page
- Added expand/contract functionality to player page to reduce page height
- Player ranks are now calculated on player stats page after the player page is loaded
- Combined server ranks now available on player stats page

11-12-2014:
- Fixed error caused while caching combined server stats of databases with many servers
- Added a couple database indexes to speed up certain queries
- Fixed issue with server join button link
- Updated to new Battlelog background image

11-11-2014:
- Fixed error which sometimes caused duplicates in Top 20 players cache
- Performance enhancement of rank function
- Added missing country Montenegro

11-06-2014:
- Top 20 players are cached for performance reasons

11-04-2014:
- Optimizations in load time
- Missing weapon images corrected

11-01-2014:
- Optimizations
- Cacheing
- Bug fixes
- Full vehicle stats

10-29-2014:
- Block common robots
- Faster combined player counts on a few pages
- Added filter to not display servers if their `ConnectionState` is NOT NULL or NOT 'ON' in the `tbl_server` table

10-25-2014:
- DLC Weapons added
- Vehicle images and stats added (where possible)

10-24-2014:
- Most DLC now included (weapons and vehicles still missing)
- Bugs fixed
- More accurate stats
- May be a little slower (due to increased accuracy)

10-12-2014:
- Battlelog inspired theme
- Page load time optimizations using asynchronous queries and background data refresh
- Improved search, navigation, and tabs
- Chat log can be searched through based on player name, message text, or date/time

02-19-2014:
- Added missing Second Assault banner images
- Added updated pChart API

02-18-2014:
- Added Second Assault Maps, Mode, and Weapons

01-23-2014:
- Bug fixes
- Code size reduction
- Style changes
- Added map pie chart to maps page and moved daily player quantity data chart from maps page to server info page
- Reverted back to on-demand signature images instead of caching signature images on the server (for server space usage reasons and to ensure up-to-date data is displayed)

01-03-2014:
- Fixed server info graphs showing old data instead of new data.
- Use GameID associated specifically with 'BF4' instead of just the most common GameID in the database.
- Better error explanations if database doesn't connect or BF4 servers aren't found in the database.

12-30-2013:
- Fixed players of the week page grouping by player id instead of stats id to remove duplicates

12-26-2013:
- Fixed a stats conformity issue by filtering out false database data for players with incorrect secondary Player IDs.
- Fixed signature images by allowing most data to be available in the signature image even if weapon stats are disabled.

12-23-2013:
- Added stats  signature generator
- Added pagination to player of the week pages
- Added player stats signatures to player stats pages

12-22-2013:
- More server banner generator help in example file and an additional option to adjust online player count

12-20-2013:
- Minor bug fix with counting sessions on the index page and global stats pages
- Added server banner generator for generating server banners for website homepage

12-19-2013:
- Slightly different directory structure moving the config file from the common folder to the config folder and renaming it from common.php to config.php
- Change database connection variables to constants
- Made dog tag names in global pages links to global player stats page
- Better battlelog link
- Minor bug fixes
- Removal of map images since they didn't fit the appearance of the page
- Sleeker appearance

12-18-2013:
- Made code more uniform and removed unnecessary duplicate code and duplicate files
- Minor bug fixes
- Added visual indicator to show which column is currently ordered and which direction it is ordered
- Added more comments in code where needed

12-17-2013:
- Disabled global stats if only one server is in the database
- Added more graphs
- Bug fix for battlelog link
- Fixed a typo
- Added players of the week
- Added missing weapon groups

12-16-2013:
- Global stats pages finished off with menus and more pages

12-15-2013:
- Global stats added
- Ranks simplified to speed up pages
- Maps in maps page ordered by game mode

12-14-2013:
- SQL injection security fix by only allowing expected inputs
- New code structure splitting code into different files in the common folder to make finding code easier
- One single index file which leads to all available servers automatically without needing to know server IDs or create multiple separate pages
- More comments in the code to explain what is going on
- Formatting SQL queries to make them more readable
- SQL queries executed with a connection link to avoid issues which might happen if you executed this page inside another page
- Minor SQL optimizations
- Minor file size reductions
- Better error handling
- URL query strings changed to make them slightly shorter and to reference PlayerID instead of SoldierName
- More graphs
- Vehicle stats (as much as the crippled Rcon will allow)
- Added DLC

11-26-2013:
- Map image transparency fixed.
- CSS cleaned up a bit.
- Scoreboard header fixed up a bit.

11-25-2013:
- Popular requests added.
- New directory structure.
- Images added.
- common.php file in the common folder has shared stats page info so less info has to be changed on each individual stats page
- Added this project on GitHub ( https://github.com/tyger07/BF4-Server-Stats )

11-22-2013:
- Appearance renovated to look like BF4

11-21-2013:
- Many more stats
- Tables can be organized by various categories
- Scoreboard takes up less area
- Weapon ranks added
- More ranks in more places

11-20-2013:
- Initial release modified and transferred over from my BF3 stats page version
