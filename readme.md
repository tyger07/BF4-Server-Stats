## BF4 Server Stats Webpage Overview
###### Version dated: 10/31/2017


### Features

* Easy setup.
* Individual server or combined server stats.
* Country stats.
* Map stats.
* Player stats.
* Weapon stats.
* Dog tag stats.
* Game server stats.
* Live scoreboard.
* Top players list / leaderboard.
* Player name search.
* Top players of the Week.
* Suspicious players search.
* Server chat log.
* Stats signature images.
* Gametracker-style server banners.
* Battlelog theme.


### Requirements

This web page code requires the use of a stats database which is created by XpKiller's Procon "Chat, GUID, Stats and Mapstats Logger" Plugin.
If you need help setting up and using XpKiller's Stats Logger plugin, you must seek assistance in XpKiller's plugin thread:
https://forum.myrcon.com/showthread.php?6698

For best compatibility with this web stats page, use the following settings in XpKiller's Procon Stats Logging plugin:
* "Enable Statslogging?" : Yes
* "Enable Weaponstats?" : Yes
* "Enable Livescoreboard in DB?" : Yes
* "tableSuffix" : None
* "MapStats ON?" : Yes
* "Session ON?" : Yes
* "Save Sessiondata to DB?" : Yes
* "Log playerdata only (no playerstats)?" : No

This webpage code requires that you have access to an Apache web server running a modern version of php and requires that you have read and write permission to add and modify files and directories on that web server.


### Help and Support

For help with this web stats page code, visit the following forum thread:
https://forum.myrcon.com/showthread.php?6854


### Installation Steps

1) Download the following file:
https://github.com/tyger07/BF4-Server-Stats/zipball/master

2) Extract the files. (maintain the original folder structure)

3) Fill in the required parameters in ./config/config.php.  For help with properly modifying the config.php file, see additional instructions further down in this readme file.

    Note:  You may not include single quotation marks (') in the config.php fields without also using an appropriate php delimiter. For instance, you may not call your clan `'Ty_ger07's Clan'` as it would cause a php compilation error due to the unequal and ambiguously placed single quotation marks.

    Using an appropriate php delimiter, when required, would cause it to work properly.  For example:

    `$clan_name = 'Ty_ger07\'s Clan';`

4) Upload the entire contents to your php-enabled web server and enjoy!


### You must fill in the following information in config.php which is in the config folder.

1) Input your stats database host, stats database user name, stats database password, and stats database name.

    For example:

    ```
    // DATABASE INFORMATION
    DEFINE('HOST', '100.200.300.400'); // database host address
    DEFINE('PORT', '3306');            // database port - default is 3306
    DEFINE('NAME', 'database');        // database name
    DEFINE('USER', 'user');		// database user name - sometimes the same as the database name
    DEFINE('PASS', 'pass');		// database password
    ```

    Note: Some web server providers use the same value for database name and database user name.


2) Input your clan name as you would like it to appear in the stats pages.

    For example:

    `$clan_name = 'MyClan';             // your gaming clan or organization name`


3) Input your desired banner image URL if you want one other than the default banner image to be displayed.

    `$banner_image = './images/bf4-logo.png'; // your desired page banner`


4) Enter the URL which you would like users to redirect to when they click your banner image.

    `$banner_url = 'http://tyger07.github.io/BF4-Server-Stats/'; // where clicking the banner will take you`


### Additional Information

A .sql file is included in the ./test-database/ folder for users to set up a dummy test database to test this web page when they otherwise have not yet set up a server or have not yet got XpKiller's Stats Logger plugin and database working.


### Changelog

Refer to commmit history on GitHub:
https://github.com/tyger07/BF4-Server-Stats/commits/master



Enjoy!
