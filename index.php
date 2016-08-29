<?php
// BF4 Stats Page by Ty_ger07
// https://forum.myrcon.com/showthread.php?6854

// start HTML header
echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-gb" xml:lang="en-gb">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="content-language" content="en-gb" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="imagetoolbar" content="no" />
<meta name="resource-type" content="document" />
<meta name="distribution" content="global" />
<meta name="copyright" content="2016 Ty_ger07 https://forum.myrcon.com/showthread.php?6854" />
<link rel="stylesheet" href="./common/stats.css" type="text/css" />
<link rel="stylesheet" href="./common/javascript/jquery-ui.css" />
<script type="text/javascript" src="./common/javascript/jquery-1.10.2.js"></script>
<script type="text/javascript" src="./common/javascript/jquery-ui.js"></script>
';
// hide php notices
error_reporting(E_ALL ^ E_NOTICE);
// set default client user agent value
$useragent = 'unknown';
// update client's user agent
if(isset($_SERVER["HTTP_USER_AGENT"]))
{
	$useragent = $_SERVER["HTTP_USER_AGENT"];
}
// detect (and block) common bots
if(stripos($useragent, 'search') === false && stripos($useragent, 'seek') === false && stripos($useragent, 'fetch') === false && stripos($useragent, 'archiv') === false && stripos($useragent, 'spide') === false && stripos($useragent, 'validat') === false && stripos($useragent, 'analyze') === false && stripos($useragent, 'crawl') === false && stripos($useragent, 'robot') === false && stripos($useragent, 'track') === false && stripos($useragent, 'generat') === false && stripos($useragent, 'google') === false && stripos($useragent, 'bing') === false && stripos($useragent, 'msnbot') === false && stripos($useragent, 'yahoo') === false && stripos($useragent, 'facebook') === false && stripos($useragent, 'yandex') === false && stripos($useragent, 'alexa') === false)
{
	// NOT a common bot
	// proceed
	// block Internet Explorer version 7 and lower due to compatibility issues with javascript and HTML5
	// these will mostly be bots in disguise anyway
	if(!(preg_match('/(?i)msie [1-7]/',$useragent)))
	{
		// NOT IE 7 and lower
		// proceed
		// include necessary files
		require_once('./config/config.php');
		require_once('./common/connect.php');
		require_once('./common/functions.php');
		require_once('./common/constants.php');
		require_once('./common/case.php');
		// initialize values as null
		$ServerID = null;
		$ServerName = null;
		$SoldierName = null;
		$PlayerID = null;
		// if there is only one server, no need for index page
		// assign the only server to the $ServerID variable
		// and get this server's basic information
		// otherwise, we need to find this $ServerID manually
		// $sid in URL is given by case.php
		// was a server ID given in the URL?  Is it a valid server ID?
		// if so, we must NOT be looking at combined stats
		if((count($ServerIDs) == 1) || (!empty($sid) && in_array($sid,$ServerIDs) && empty($ServerID)))
		{
			// assign the only server to the $ServerID variable (inherited)
			if(count($ServerIDs) == 1)
			{
				$ServerID = $ServerIDs[0];
			}
			// assign $ServerID variable from URL
			elseif(!empty($sid) && in_array($sid,$ServerIDs) && empty($ServerID))
			{
				$ServerID = $sid;
			}
			// find this server info
			$Server_q = @mysqli_query($BF4stats,"
				SELECT `ServerName`
				FROM `tbl_server`
				WHERE `ServerID` = {$ServerID}
				AND `GameID` = {$GameID}
			");
			// the server info was found
			if(@mysqli_num_rows($Server_q) == 1)
			{
				$Server_r = @mysqli_fetch_assoc($Server_q);
				$ServerName = $Server_r['ServerName'];
				// create battlelog link for this server
				$battlelog = 'http://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . urlencode($ServerName);
			}
			// error?  what?  This will probably never happen.
			// damage control...
			else
			{
				$ServerName = 'Error';
				$battlelog = 'http://battlelog.battlefield.com/bf4/servers/pc/';
			}
			// lets see if a SoldierName or PlayerID was provided to us in the URL
			// first look for a SoldierName in URL and try to convert it to PlayerID
			if(!empty($player))
			{
				$SoldierName = $player;
				$PlayerID_q = @mysqli_query($BF4stats,"
					SELECT tpd.`PlayerID`
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tpd.`PlayerID` = tsp.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					WHERE tpd.`SoldierName` = '{$SoldierName}'
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` = {$ServerID}
				");
				// was there a result?
				if(@mysqli_num_rows($PlayerID_q) == 1)
				{
					$PlayerID_r = @mysqli_fetch_assoc($PlayerID_q);
					$PlayerID = $PlayerID_r['PlayerID'];
				}
				// otherwise null variable
				else
				{
					$PlayerID = null;
				}
			}
			// then look for PlayerID in URL and make sure it wasn't already successfully matched above
			if(!empty($pid) && empty($PlayerID))
			{
				$PlayerID = $pid;
				$SoldierName_q = @mysqli_query($BF4stats,"
					SELECT tpd.`SoldierName`
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tpd.`PlayerID` = tsp.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` = {$ServerID}
				");
				// was there a result?
				if(@mysqli_num_rows($SoldierName_q) == 1)
				{
					$SoldierName_r = @mysqli_fetch_assoc($SoldierName_q);
					$SoldierName = $SoldierName_r['SoldierName'];
				}
				// otherwise null variables
				else
				{
					$SoldierName = null;
					$PlayerID = null;
				}
			}
		}
		// no server id in URL
		// and there is more than one valid server id available
		// this must be a combined stats page
		else
		{
			// lets see if a SoldierName or PlayerID was provided to us in the URL
			// first look for a SoldierName in URL and try to convert it to PlayerID
			if(!empty($player))
			{
				$SoldierName = $player;
				$PlayerID_q = @mysqli_query($BF4stats,"
					SELECT DISTINCT(tpd.`PlayerID`)
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					WHERE tpd.`SoldierName` = '{$SoldierName}'
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
				");
				// was there a result?
				if(@mysqli_num_rows($PlayerID_q) == 1)
				{
					$PlayerID_r = @mysqli_fetch_assoc($PlayerID_q);
					$PlayerID = $PlayerID_r['PlayerID'];
				}
				// otherwise null variable
				else
				{
					$PlayerID = null;
				}
			}
			// then look for PlayerID in URL and make sure it wasn't already successfully matched above
			if(!empty($pid) && empty($PlayerID))
			{
				$PlayerID = $pid;
				$SoldierName_q = @mysqli_query($BF4stats,"
					SELECT DISTINCT(tpd.`SoldierName`)
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
				");
				// was there a result?
				if(@mysqli_num_rows($SoldierName_q) == 1)
				{
					$SoldierName_r = @mysqli_fetch_assoc($SoldierName_q);
					$SoldierName = $SoldierName_r['SoldierName'];
				}
				// otherwise null variables
				else
				{
					$SoldierName = null;
					$PlayerID = null;
				}
			}
		}
		// change page title, meta description, and keywords depending on the URL
		require_once('./common/common/meta.php');
		echo '
		</head>
		<body>
		<div class="body-grid"></div>
		<div class="content-gradient"></div>
		<div id="topcontent">
		<div id="topbanner">
		<a href="' . $banner_url . '" target="_blank"><img class="banner" src="' . $banner_image . '" alt="BF4 Stats Page Copyright 2015 Open-Web-Community" border="0" /></a>
		</div>
		</div>
		<div id="topmenu">
		';
		// drop-down menu
		require_once('./common/menu/drop-down-menu.php');
		echo '
		</div>
		';
		// navigation menu
		require_once('./common/menu/navigation-menu.php');
		echo '
		<div id="pagebody">
		';
		// display bread crumbs to remind user where they are
		require_once('./common/menu/bread-crumbs.php');
		// load page content depending on user selection
		// the current page is determined by a valid ?p in the URL
		if(!empty($page))
		{
			if($page == 'player')
			{
				// include player.php contents
				require_once('./common/player/player.php');
			}
			elseif($page == 'suspicious')
			{
				// include suspicious.php contents through wrapper
				require_once('./common/suspicious/wrapper.php');
			}
			elseif($page == 'countries')
			{
				// include countries.php contents
				require_once('./common/countries/wrapper.php');
			}
			elseif($page == 'maps')
			{
				// include maps.php contents
				require_once('./common/maps/wrapper.php');
			}
			elseif($page == 'server')
			{
				// include serverstats.php contents
				require_once('./common/server/wrapper.php');
			}
			elseif($page == 'chat')
			{
				// include chat.php contents
				require_once('./common/chat/wrapper.php');
			}
			elseif($page == 'leaders')
			{
				// include leaders.php contents
				require_once('./common/leaders/wrapper.php');
			}
			elseif($page == 'home')
			{
				// include home.php contents
				require_once('./common/home/home-wrapper.php');
			}
		}
		// there was no valid ?p in the URL
		else
		{
			// inherited home page
			if(!empty($ServerID))
			{
				// include home.php contents
				require_once('./common/home/home-wrapper.php');
			}
			else
			{
				// display the index page
				require_once('./common/home/index-display-servers-wrapper.php');
			}
		}
		echo '
		<br/>
		<br/>
		<div class="subsection">
		<div class="headline">[ <span class="information">Statistics data provided by <a href="https://forum.myrcon.com/showthread.php?6698-_BF4-PRoCon-Chat-GUID-Stats-and-Mapstats-Logger-1-0-0-1" target="_blank">XpKiller\'s Procon logging plugin</a></span> ]  &nbsp; [ <span class="information">Stats page provided by <a href="http://tyger07.github.io/BF4-Server-Stats/" target="_blank">Ty_ger07</a></span> ]</div>
		</div>
		';
		// now lets check our stats page sessions
		// stats page sessions are used to monitor how many people are viewing these stats pages
		// set default client IP address value
		$userip = 'unknown';
		// update client's IP address
		if(isset($_SERVER["REMOTE_ADDR"]))
		{
			$userip = $_SERVER["REMOTE_ADDR"];
		}
		$ses = session_count($userip,$ServerID,$valid_ids,$GameID,$BF4stats);
		echo '
		<br/>
		<center>
		<span class="footertext">' . $ses . ' users viewing these BF4 stats pages</span>
		</center>
		';
		// display denied bot stats
		// check to see if denied table exists
		@mysqli_query($BF4stats,"
			CREATE TABLE IF NOT EXISTS `tyger_stats_denied`
			(`category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `count` INT(11) NOT NULL DEFAULT '0', INDEX (`category`))
			ENGINE=MyISAM
			DEFAULT CHARSET=utf8
			COLLATE=utf8_bin
		");
		// count number of bots recorded
		$TotalBots_q = @mysqli_query($BF4stats,"
			SELECT SUM(`count`) AS count
			FROM `tyger_stats_denied`
			WHERE `category` = 'bots'
			GROUP BY `category`
		");
		if(@mysqli_num_rows($TotalBots_q) != 0)
		{
			$TotalBots_r = @mysqli_fetch_assoc($TotalBots_q);
			$TotalBots = $TotalBots_r['count'];
			// display bot stats
			echo '
			<center>
			<span class="footertext">' . $TotalBots . ' bots have been denied access</span>
			</center>
			';
		}
		// display denied browser stats
		// check to see if denied table exists
		@mysqli_query($BF4stats,"
			CREATE TABLE IF NOT EXISTS `tyger_stats_denied`
			(`category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `count` INT(11) NOT NULL DEFAULT '0', INDEX (`category`))
			ENGINE=MyISAM
			DEFAULT CHARSET=utf8
			COLLATE=utf8_bin
		");
		// count number of browsers recorded
		$TotalDenied_q = @mysqli_query($BF4stats,"
			SELECT SUM(`count`) AS count
			FROM `tyger_stats_denied`
			WHERE `category` = 'browsers'
			GROUP BY `category`
		");
		if(@mysqli_num_rows($TotalDenied_q) != 0)
		{
			$TotalDenied_r = @mysqli_fetch_assoc($TotalDenied_q);
			$TotalDenied = $TotalDenied_r['count'];
			// display browser stats
			echo '
			<center>
			<span class="footertext">' . $TotalDenied . ' archaic browsers have been blocked</span>
			</center>
			';
		}
		echo '
		<br/>
		</div>
		</body>
		</html>
		';
	}
	// IS IE 7 or lower
	else
	{
		// blocked
		// include necessary files
		require_once('./config/config.php');
		require_once('./common/connect.php');
		echo '
		<meta name="keywords" content="Restricted" />
		<meta name="description" content="BF4 Stats Page - Restricted" />
		<title>BF4 Stats Page - Restricted</title>
		</head>
		';
		// check to see if denied table exists
		@mysqli_query($BF4stats,"
			CREATE TABLE IF NOT EXISTS `tyger_stats_denied`
			(`category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `count` INT(11) NOT NULL DEFAULT '0', INDEX (`category`))
			ENGINE=MyISAM
			DEFAULT CHARSET=utf8
			COLLATE=utf8_bin
		");
		// count number of browsers recorded
		$TotalDenied_q = @mysqli_query($BF4stats,"
			SELECT SUM(`count`) AS count
			FROM `tyger_stats_denied`
			WHERE `category` = 'browsers'
			GROUP BY `category`
		");
		if(@mysqli_num_rows($TotalDenied_q) != 0)
		{
			$TotalDenied_r = @mysqli_fetch_assoc($TotalDenied_q);
			$TotalDenied = $TotalDenied_r['count'];
			// increment
			$TotalDenied++;
			// store new value
			@mysqli_query($BF4stats,"
				UPDATE `tyger_stats_denied`
				SET `count` = '{$TotalDenied}'
				WHERE `category` = 'browsers'
			");
		}
		else
		{
			// add this browser
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_denied`
				(`category`, `count`)
				VALUES ('browsers', '1')
			");
		}
		echo '
		<body>
		<br/><br/>
		<center><b>Sorry, Internet Explorer version 7 and lower is not supported.</b><br/><br/>Update your browser version or disable compatibility mode in your browser.<br/>Please contact this website\'s administrator if you need further assistance.<br/><br/>Your user agent: ' . $useragent . '</center>
		</body>
		</html>
		';
	}
}
// IS a bot
else
{
	// blocked
	// include necessary files
	require_once('./config/config.php');
	require_once('./common/connect.php');
	echo '
	<meta name="keywords" content="Restricted,BF4,Stats,' . $clan_name . '" />
	<meta name="description" content="BF4 Stats Page - ' . $clan_name . ' - Restricted" />
	<title>BF4 Stats Page - ' . $clan_name . ' - Restricted</title>
	</head>
	';
	// check to see if denied table exists
	@mysqli_query($BF4stats,"
		CREATE TABLE IF NOT EXISTS `tyger_stats_denied`
		(`category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `count` INT(11) NOT NULL DEFAULT '0', INDEX (`category`))
		ENGINE=MyISAM
		DEFAULT CHARSET=utf8
		COLLATE=utf8_bin
	");
	// count number of bots already recorded
	$TotalBots_q = @mysqli_query($BF4stats,"
		SELECT SUM(`count`) AS count
		FROM `tyger_stats_denied`
		WHERE `category` = 'bots'
		GROUP BY `category`
	");
	if(@mysqli_num_rows($TotalBots_q) != 0)
	{
		$TotalBots_r = @mysqli_fetch_assoc($TotalBots_q);
		$TotalBots = $TotalBots_r['count'];
		// increment
		$TotalBots++;
		// store new value
		@mysqli_query($BF4stats,"
			UPDATE `tyger_stats_denied`
			SET `count` = '{$TotalBots}'
			WHERE `category` = 'bots'
		");
	}
	else
	{
		// add this bot
		@mysqli_query($BF4stats,"
			INSERT INTO `tyger_stats_denied`
			(`category`, `count`)
			VALUES ('bots', '1')
		");
	}
	echo '
	<body>
	<br/><br/>
	<center><b>Sorry, bot access has been disabled.</b><br/><br/>Please contact this website\'s administrator if you need further assistance.<br/><br/>Your user agent: ' . $useragent . '</center>
	</body>
	</html>
	';
}
?>