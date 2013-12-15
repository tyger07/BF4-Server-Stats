[SIZE=5]ServerStats.php Overview[/SIZE]

This is a very simple and fast loading game server player stats webpage as requested by Friegide in these forums.  The page is designed to provide minimal performance impact on your web server while still providing much very useful statistical data.  This page requires the use of a stats database which is created by [URL="https://forum.myrcon.com/showthread.php?6698-_BF4-PRoCon-Chat-GUID-Stats-and-Mapstats-Logger-1-0-0-1"]XpKiller's BF4 Chat, GUID, Stats and Mapstats Logger Plugin[/URL] .  If you need help with his plugin, you must seek assistance in XpKiller's thread.


Each copy of this stats page code is designed to be used with one game server.  Multiple copies of the file using different names or placed in different directories can be used to create multiple stats pages.


[SIZE=5]Demo[/SIZE]

[ATTACH=CONFIG]2932[/ATTACH]

Demo > [URL]http://open-web-community.com/bf4stats/player-stats/index.php[/URL]


[SIZE=5]Additional Info[/SIZE]

Valid!
[URL="http://validator.w3.org/check?uri=http%3A%2F%2Fopen-web-community.com%2Fbf4stats%2Fplayer-stats%2FStatsMain.php&charset=%28detect+automatically%29&doctype=Inline&group=0&user-agent=W3C_Validator%2F1.3+http%3A%2F%2Fvalidator.w3.org%2Fservices"][IMG]http://validator.w3.org/images/valid_icons/valid-xhtml10[/IMG][/URL]

GitHub:
[url]https://github.com/tyger07/BF4-Server-Stats[/url]


[SIZE=5]Installation Steps:[/SIZE]

[B]Download the following file:[/B]
[URL]http://open-web-community.com/bf4stats/BF4Stats.zip[/URL]

Extract the files.  You may rename index.php to any name of your choice.

You may change the appearance of the page by modifying the stats.css file in the common folder.


Fill in the required parameters before using this code.  You must place the necessary data between the single quotation marks (''). 

[B]Note:[/B]  You may not include single quotation marks (') in the following fields.  For instance, you may not call your clan 'Ty_ger07's Clan' as it will create a PHP compilation error.
For example, this would not work:
[quote]
// CLAN NAME
$clan_name		= 'Ty_ger07's Clan'; // your gaming clan or organization name
[/quote]

You must use a PHP delimiter if you wish to use single quotes within the following fields.
For example, this would work:
[quote]
// CLAN NAME
$clan_name		= 'Ty_ger07\'s Clan'; // your gaming clan or organization name
[/quote]


[B]You must fill in the following information in the common.php file found in the common folder[/B]


[SIZE=3]1)[/SIZE] Input your stats database host, stats database user name, stats database password, and stats database name.

[code]
// DATABASE INFORMATION
$db_host        = ''; // database host
$db_port        = '3306'; // database port. default is 3306
$db_name        = ''; // database name
$db_uname        = ''; // database user name
$db_pass        = ''; // database password
[/code]
 
For example:
[quote]
//DATABASE INFORMATION
$db_host        = '100.200.300.400'; // database host
$db_port        = '3306'; // database port. default is 3306
$db_name        = 'database'; // database name
$db_uname        = 'user'; // database user name
$db_pass        = 'pass'; // database password
[/quote]

[B]Note:[/B] Some web server providers (such as GoDaddy) use the same value for database name and database user name.


[SIZE=3]2)[/SIZE] Input your clan name as you would like it to appear in the stats pages.

[code]
// CLAN NAME
$clan_name		= ''; // your gaming clan or organization name
[/code]

For example:
[quote]
// CLAN NAME
$clan_name		= 'Junglewraiths'; // your gaming clan or organization name
[/quote]


[SIZE=3]3)[/SIZE] Input your desired banner image URL if you want one other than the default to be displayed.

[code]
// PAGE BANNER
$banner_image	= './images/bf4-logo.png'; // your desired page banner
[/code]
 

[SIZE=3]4)[/SIZE] Enter the URL which you would like users to redirect to if they click your banner image.

[code]
// BANNER LINK
$banner_url		= 'http://open-web-community.com/'; // where clicking the banner will take you
[/code]
 

Enjoy!
 
[SIZE=5]Changelog:[/SIZE]

[B]12-14-2013:[/B]
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

[B]11-26-2013:[/B]
- Map image transparency fixed.
- CSS cleaned up a bit.
- Scoreboard header fixed up a bit.

[B]11-25-2013:[/B]
- Popular requests added.
- New directory structure.
- Images added.
- common.php file in the common folder has shared stats page info so less info has to be changed on each individual stats page
- Added this project on GitHub ( [url]https://github.com/tyger07/BF4-Server-Stats[/url] )

[B]11-22-2013:[/B]
- Appearance renovated to look like BF4

[B]11-21-2013:[/B]
- Many more stats
- Tables can be organized by various categories
- Scoreboard takes up less area
- Weapon ranks added
- More ranks in more places

[B]11-20-2013:[/B]
- Initial release modified and transferred over from BF3 stats page version