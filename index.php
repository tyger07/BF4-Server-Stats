<?php
// server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// hide php notices
error_reporting(E_ALL ^ E_NOTICE);


// detect common bots
// find user agent
if(isset($_SERVER["HTTP_USER_AGENT"]))
{
	$useragent = $_SERVER["HTTP_USER_AGENT"];
}
else
{
	$useragent = 'unknown';
}
// detect robot
if(stripos($useragent, 'search') === false && stripos($useragent, 'seek') === false && stripos($useragent, 'fetch') === false && stripos($useragent, 'archiv') === false && stripos($useragent, 'spide') === false && stripos($useragent, 'validat') === false && stripos($useragent, 'analyze') === false && stripos($useragent, 'crawl') === false && stripos($useragent, 'robot') === false && stripos($useragent, 'track') === false && stripos($useragent, 'generat') === false && stripos($useragent, 'google') === false && stripos($useragent, 'bing') === false && stripos($useragent, 'msnbot') === false && stripos($useragent, 'yahoo') === false && stripos($useragent, 'facebook') === false && stripos($useragent, 'yandex') === false && stripos($useragent, 'alexa') === false)
{
	// not a bot
	
	// block IE 7 and lower due to compatibility issues
	if(!(preg_match('/(?i)msie [1-7]/',$useragent)))
	{
		// not IE 7 and lower
		
		// include necessary files
		require_once('./config/config.php');
		require_once('./common/functions.php');
		require_once('./common/constants.php');

		// start counting page load time
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$starttime = $mtime;

		// start buffering HTML output
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
		<meta name="copyright" content="2014 Open-Web-Community http://open-web-community.com/" />
		<link rel="stylesheet" href="./common/stats.css" type="text/css" />
		';

		// connect to the stats database
		require_once('./common/connect.php');

		// initialize values as null
		$ServerID = null;
		$ServerName = null;
		$SoldierName = null;
		$PlayerID = null;

		// if there is only one server, no need for index
		// assign the $ServerID variable
		if(count($ServerIDs) == 1)
		{
			$ServerID = $ServerIDs[0];
		}

		// include case file for $_GET variables
		require_once('./common/case.php');

		// was a server ID given in the URL?  Is it a valid server ID?
		// if so, we must not be looking at global stats
		if(!empty($sid) && in_array($sid,$ServerIDs) && empty($ServerID))
		{
			// assign $ServerID variable
			$ServerID = $sid;
			
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
				$battlelog = 'http://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . urlencode($ServerName);
			}
			// error?  what?  This will probably never happen.
			else
			{
				$ServerName = 'Error';
				$battlelog = 'http://battlelog.battlefield.com/bf4/servers/pc/';
			}
			
			// free up server info query memory
			@mysqli_free_result($Server_q);
			
			// lets see if a SoldierName or PlayerID was provided to us in the URL
			// we will try to find this player in this server and convert everything to PlayerID
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
				// otherwise null variables
				else
				{
					$PlayerID = null;
				}
				
				// free up player id query memory
				@mysqli_free_result($PlayerID_q);
			}
			
			// then look for PlayerID in URL and make sure it is valid
			if(!empty($pid) && empty($PlayerID))
			{
				$PlayerID = $pid;
				
				// search for soldier name using provided player ID
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
				
				// free up soldier name query memory
				@mysqli_free_result($SoldierName_q);
			}
		}
		// server id was inherited since this is the only server
		elseif(!empty($ServerID))
		{	
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
				$battlelog = 'http://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . urlencode($ServerName);
			}
			// error?  what?  This will probably never happen.
			else
			{
				$ServerName = 'Error';
				$battlelog = 'http://battlelog.battlefield.com/bf4/servers/pc/';
			}
			
			// free up server info query memory
			@mysqli_free_result($Server_q);
			
			// lets see if a SoldierName or PlayerID was provided to us in the URL
			// we will try to find this player in this server and convert everything to PlayerID
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
				// otherwise null variables
				else
				{
					$PlayerID = null;
				}
				
				// free up player id query memory
				@mysqli_free_result($PlayerID_q);
			}
			
			// then look for PlayerID in URL and make sure it is valid
			if(!empty($pid) && empty($PlayerID))
			{
				$PlayerID = $pid;
				
				// search for soldier name using provided player ID
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
				
				// free up soldier name query memory
				@mysqli_free_result($SoldierName_q);
			}
		}
		// no server id in URL
		// and there is more than one valid server id available
		// this must be a global stats page
		else
		{
			// lets see if a SoldierName or PlayerID was provided to us in the URL
			// first look for a SoldierName in URL and try to convert it to PlayerID
			if(!empty($player))
			{
				$SoldierName = $player;
				
				// search for soldier name
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
				// otherwise null variables
				else
				{
					$PlayerID = null;
				}
				
				// free up player ID query memory
				@mysqli_free_result($PlayerID_q);
			}
			
			// then look for PlayerID in URL and make sure it is valid
			if(!empty($pid) && empty($PlayerID))
			{
				$PlayerID = $pid;
				
				// search for soldier name using provided player ID
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
				
				// free up soldier name query memory
				@mysqli_free_result($SoldierName_q);
			}
		}

		// change page title, meta description, and keywords depending on the page content
		if(!empty($page))
		{
			if($page == 'player')
			{
				// this is not a global stats page
				if(!empty($ServerID))
				{
					echo '
					<meta name="keywords" content="' . $SoldierName . ',' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' player stats page for ' . $SoldierName . '." />
					<title>' . $clan_name . ' BF4 Stats - ' . $SoldierName . ' - ' . $ServerName . '</title>
					';
				}
				// this is a global stats page
				else
				{
					echo '
					<meta name="keywords" content="' . $SoldierName . ',' . $clan_name . ',BF4,Player,Stats" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 global player stats page for ' . $SoldierName . '." />
					<title>' . $clan_name . ' BF4 Stats - ' . $SoldierName . ' - Global Stats</title>
					';
				}
			}
			elseif($page == 'suspicious')
			{
				// this is not a global stats page
				if(!empty($ServerID))
				{
					echo '
					<meta name="keywords" content="Suspicious,Players,' . $ServerName . ',' . $clan_name . ',BF4,Stats" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Suspicious Players page." />
					<title>' . $clan_name . ' BF4 Stats - Suspicious Players - ' . $ServerName . '</title>
					';
				}
				// this is a global stats page
				else
				{
					echo '
					<meta name="keywords" content="Suspicious,Players,' . $clan_name . ',BF4,Stats" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 global Suspicious Players page." />
					<title>' . $clan_name . ' BF4 Stats - Suspicious Players - Global Stats</title>
					';
				}
			}
			elseif($page == 'leaders')
			{
				// this is not a global stats page
				if(!empty($ServerID))
				{
					echo '
					<meta name="keywords" content="Top,Players,' . $ServerName . ',' . $clan_name . ',BF4,Stats" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Top Players page." />
					<title>' . $clan_name . ' BF4 Stats - Top Players - ' . $ServerName . '</title>
					';
				}
				// this is a global stats page
				else
				{
					echo '
					<meta name="keywords" content="Top,Players,' . $clan_name . ',BF4,Stats" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 global Top Players page." />
					<title>' . $clan_name . ' BF4 Stats - Top Players - Global Stats</title>
					';
				}
			}
			elseif($page == 'countries')
			{
				// this is not a global stats page
				if(!empty($ServerID))
				{
					echo '
					<meta name="keywords" content="Country,Stats,' . $ServerName . ',' . $clan_name . ',BF4" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Country Stats page." />
					<title>' . $clan_name . ' BF4 Stats - Country Stats - ' . $ServerName . '</title>
					';
				}
				// this is a global stats page
				else
				{
					echo '
					<meta name="keywords" content="Country,Stats,' . $clan_name . ',BF4" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 global Country Stats page." />
					<title>' . $clan_name . ' BF4 Stats - Country Stats - Global Stats</title>
					';
				}
			}
			elseif($page == 'maps')
			{
				// this is not a global stats page
				if(!empty($ServerID))
				{
					echo '
					<meta name="keywords" content="Map,Stats,' . $ServerName . ',' . $clan_name . ',BF4" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Map Stats page." />
					<title>' . $clan_name . ' BF4 Stats - Map Stats - ' . $ServerName . '</title>
					';
				}
				// this is a global stats page
				else
				{
					echo '
					<meta name="keywords" content="Map,Stats,' . $clan_name . ',BF4" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 global Map Stats page." />
					<title>' . $clan_name . ' BF4 Stats - Map Stats - Global Stats</title>
					';
				}
			}
			elseif($page == 'server')
			{
				// this is not a global stats page
				if(!empty($ServerID))
				{
					echo '
					<meta name="keywords" content="Server,Stats,' . $ServerName . ',' . $clan_name . ',BF4,Info" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Server Info page." />
					<title>' . $clan_name . ' BF4 Stats - Server Info - ' . $ServerName . '</title>
					';
				}
				// this is a global stats page
				else
				{
					echo '
					<meta name="keywords" content="Server,Stats,' . $clan_name . ',BF4,Info" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 global Server Info page." />
					<title>' . $clan_name . ' BF4 Stats - Server Info - Global Stats</title>
					';
				}
			}
			elseif($page == 'chat')
			{
				// this is not a global stats page
				if(!empty($ServerID))
				{
					echo '
					<meta name="keywords" content="Chat,Recent,' . $ServerName . ',' . $clan_name . ',BF4" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' Chat Content page." />
					<title>' . $clan_name . ' BF4 Stats - Chat Log - ' . $ServerName . '</title>
					';
				}
				// this is a global stats page
				else
				{
					echo '
					<meta name="keywords" content="Chat,Recent,' . $clan_name . ',BF4" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 global Chat Content page." />
					<title>' . $clan_name . ' BF4 Stats - Chat Log - Global Stats</title>
					';
				}
			}
			elseif($page == 'home')
			{
				// this is not a global stats page
				if(!empty($ServerID))
				{
					echo '
					<meta name="keywords" content="Home,' . $ServerName . ',' . $clan_name . ',BF4,Server,Stats" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' stats Home Page." />
					<title>' . $clan_name . ' BF4 Stats - Home Page - ' . $ServerName . '</title>
					';
				}
				// this is a global stats page
				else
				{
					echo '
					<meta name="keywords" content="Home,' . $clan_name . ',BF4,Server,Stats" />
					<meta name="description" content="This is our ' . $clan_name . ' BF4 global stats Home Page." />
					<title>' . $clan_name . ' BF4 Stats - Home Page - Global Stats</title>
					';
				}
			}
		}
		else
		{
			// this is not a global stats page
			if(!empty($ServerID))
			{
				echo '
				<meta name="keywords" content="Home,' . $ServerName . ',' . $clan_name . ',BF4,Server,Stats" />
				<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' stats Home Page." />
				<title>' . $clan_name . ' BF4 Stats - Home Page - ' . $ServerName . '</title>
				';
			}
			// this is a global stats page
			else
			{
				echo '
				<meta name="keywords" content="Index,' . $clan_name . ',BF4,Server,Stats" />
				<meta name="description" content="This is our ' . $clan_name . ' BF4 stats Index Page." />
				<title>' . $clan_name . ' BF4 Stats - Index Page</title>
				';
			}
		}

		// start javascript scripts

		// skip code execution if not needed
		// index page doesn't need most of this
		if(!empty($page) || !empty($ServerID))
		{
			// load necessary files
			echo '
			<link rel="stylesheet" href="./common/jquery-ui.css" />
			<script type="text/javascript" src="./common/jquery-1.10.2.js"></script>
			<script type="text/javascript" src="./common/jquery-ui.js"></script>
			';

			// jquery tabs
			echo '
			<script type="text/javascript">
			$(function()
			{
				$( "#tabs, #tabs2" ).tabs(
				{
					beforeLoad: function( event, ui )
					{
						ui.jqXHR.error(function()
						{
							ui.panel.html(
							"<div class=\"subsection\"><div class=\"headline\">Couldn\'t load this tab!</div></div>" );
						});
					}
				});
			});
			</script>
			';
			// jquery auto-find players in input box
			echo '
			<script type="text/javascript">
			$(function()
			{
				$( "#soldiers" ).autocomplete(
				{
					source: "./common/player-search.php?';
					if(!empty($ServerID))
					{
						echo 'sid=' . $ServerID . '&';
					}
					echo 'gid=' . $GameID . '",
					minLength: 3,
					select: function( event, ui )
					{
						if(ui.item)
						{
							$(\'#soldiers\').val(ui.item.value);
						}
						$(\'#ajaxsearch\').submit();
					}
				});
			});
			</script>
			';
			// jquery auto refresh scoreboard every 30 seconds
			if(($page == 'home' && !empty($ServerID)) OR (empty($page) && !empty($ServerID)))
			{
				echo '
				<script type="text/javascript">
				$(function() {
					function callAjax(){
						$(\'#scoreboard\').load("./common/scoreboard-live.php?p=home&sid=' . $ServerID . '&gid=' . $GameID;
						if(!empty($_GET['rank']))
						{
							echo '&rank=' . $_GET['rank'];
						}
						if(!empty($_GET['order']))
						{
							echo '&order=' . $_GET['order'];
						}
						echo '");
					}
					setInterval(callAjax, 30000 );
				});
				</script>
				';
			}
			// jquery auto refresh chat every 60 seconds
			if($page == 'chat')
			{
				echo '
				<script type="text/javascript">
				$(function() {
					function callAjax(){
						$(\'#chat\').load("./common/chat-live.php?p=chat&gid=' . $GameID;
						if(!empty($ServerID))
						{
							echo '&sid=' . $ServerID;
						}
						if(!empty($currentpage))
						{
							echo '&cp=' . $currentpage;
						}
						if(!empty($rank))
						{
							echo '&r=' . $rank;
						}
						if(!empty($order))
						{
							echo '&o=' . $order;
						}
						if(!empty($query))
						{
							echo '&q=' . urlencode($query);
						}
						echo '");
					}
					setInterval(callAjax, 60000 );
				});
				</script>
				';
			}
			// javascript expand/collapse
			if($page == 'player')
			{
				// expand / contract javascript
				echo '
				<script type="text/javascript">
				$(document).ready(function()
				{
					$(".expanded").hide();
					$(".collapsed, .expanded").click(function()
					{
						$(this).parent().children(".expanded, .collapsed").toggle();
					});
				});
				</script>
				<script type="text/javascript">
				$(document).ready(function()
				{
					$(".expanded3").hide();
					$(".collapsed3, .expanded3").click(function()
					{
						$(this).parent().children(".expanded3, .collapsed3").toggle();
					});
				});
				</script>
				';
			}
		}
		// jquery auto refresh index page server list every 30 seconds
		else
		{
			// load necessary file and run script
			echo '
			<script type="text/javascript" src="./common/jquery-1.10.2.js"></script>
			<script type="text/javascript">
			$(function() {
				function callAjax(){
					$(\'#servers\').load("./common/index-display-servers-live.php?gid=' . $GameID . '");
				}
				setInterval(callAjax, 30000 );
			});
			</script>
			';
		}
		// end of scripts
		echo '
		</head>
		<body>
		<div class="body-grid"></div>
		<div id="topcontent">
		<div id="topbanner">
		<a href="' . $banner_url . '" target="_blank"><img alt="BF4 Stats Page Copyright 2013 Open-Web-Community" border="0" src="' . $banner_image . '" style="height: 96px;"/></a>
		</div>
		</div>
		<div id="topmenu">
		';

		// include displayservers.php contents
		require_once('./common/display-servers.php');

		echo '</div>';

		// if this is a server stats page, display server stats page menu
		if(!empty($ServerID))
		{
			echo '<div id="menucontent">';
			if($page == 'player')
			{
				echo '<div class="menuitemselected" style="width: 19%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 19%">';
			}
			echo '
			<form id="ajaxsearch" action="' . $_SERVER['PHP_SELF'] . '" method="get">
			&nbsp; <span class="information">Player:</span>
			<input type="hidden" name="p" value="player" />
			<input type="hidden" name="sid" value="' . $ServerID . '" />
			<input id="soldiers" type="text" class="inputbox" ';
			// try to fill in search box
			if(!empty($SoldierName))
			{
				echo 'value="' . $SoldierName . '" ';
			}
			echo 'name="player" />
			</form>
			</div>
			';
			if($page == 'home')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=home&amp;sid=' . $ServerID . '">Home</a>
			</div>
			';
			if($page == 'leaders')
			{
				echo '<div class="menuitemselected" style="width: 13%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 13%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=leaders&amp;sid=' . $ServerID . '">Leaderboard</a>
			</div>
			';
			if($page == 'suspicious')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=suspicious&amp;sid=' . $ServerID . '">Suspicious</a>
			</div>
			';
			if($page == 'chat')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=chat&amp;sid=' . $ServerID . '">Chat</a>
			</div>
			';
			if($page == 'countries')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=countries&amp;sid=' . $ServerID . '">Countries</a>
			</div>
			';
			if($page == 'maps')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=maps&amp;sid=' . $ServerID . '">Maps</a>
			</div>
			';
			if($page == 'server')
			{
				echo '<div class="menuitemselected" style="width: 13%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 13%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=server&amp;sid=' . $ServerID . '">Server Info</a>
			</div>
			</div>
			';
		}
		// if this is a global stats page, display global stats page menu
		elseif(empty($ServerID) && (!empty($page)))
		{
			echo '<div id="menucontent">';
			if($page == 'player')
			{
				echo '<div class="menuitemselected" style="width: 19%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 19%">';
			}
			echo '
			<form id="ajaxsearch" action="' . $_SERVER['PHP_SELF'] . '" method="get">
			&nbsp; <span class="information">Player:</span>
			<input type="hidden" name="p" value="player" />
			<input id="soldiers" type="text" class="inputbox" ';
			// try to fill in search box
			if(!empty($SoldierName))
			{
				echo 'value="' . $SoldierName . '" ';
			}
			echo 'name="player" />
			</form>
			</div>
			';
			if($page == 'home')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=home">Home</a>
			</div>
			';
			if($page == 'leaders')
			{
				echo '<div class="menuitemselected" style="width: 13%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 13%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=leaders">Leaderboard</a>
			</div>
			';
			if($page == 'suspicious')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=suspicious">Suspicious</a>
			</div>
			';
			if($page == 'chat')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=chat">Chat</a>
			</div>
			';
			if($page == 'countries')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=countries">Countries</a>
			</div>
			';
			if($page == 'maps')
			{
				echo '<div class="menuitemselected" style="width: 11%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 11%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=maps">Maps</a>
			</div>
			';
			if($page == 'server')
			{
				echo '<div class="menuitemselected" style="width: 13%">';
			}
			else
			{
				echo '<div class="menuitems" style="width: 13%">';
			}
			echo '
			<a class="fill-div" href="' . $_SERVER['PHP_SELF'] . '?p=server">Server Info</a>
			</div>
			</div>
			';
		}

		echo '<div id="pagebody">';
		// display page header to remind user where they are
		// not at index page
		if(!empty($page))
		{
			if(!empty($ServerID))
			{
				echo '
				<div style="font-size: 10px; text-align: left; padding: 2px;">
				<a href="' . $_SERVER['PHP_SELF'] . '">Index</a>
				&bull;
				' . $ServerName . '
				</div>
				';
			}
			else
			{
				echo '
				<div style="font-size: 10px; text-align: left; padding: 2px;">
				<a href="' . $_SERVER['PHP_SELF'] . '">Index</a>
				&bull;
				Combined Stats
				</div>
				';
			}
			
			echo '
			<div class="title">
			';
			if($page == 'player')
			{
				echo 'PLAYER STATS';
			}
			elseif($page == 'suspicious')
			{
				echo 'SUSPICIOUS PLAYERS';
			}
			elseif($page == 'countries')
			{
				echo 'COUNTRY STATS';
			}
			elseif($page == 'maps')
			{
				echo 'MAP STATS';
			}
			elseif($page == 'server')
			{
				echo 'SERVER INFORMATION';
			}
			elseif($page == 'chat')
			{
				echo 'CHAT LOG';
			}
			elseif($page == 'leaders')
			{
				echo 'LEADERBOARD';
			}
			elseif($page == 'home')
			{
				echo 'HOME PAGE';
			}
			echo '
			</div>
			<div class="clear"></div>
			';
		}
		// at index page
		elseif(empty($page) && empty($ServerID))
		{
			echo '
			<div style="font-size: 10px; text-align: left; padding: 2px;">Select a Server Below</div>
			<div class="title">
			STATS INDEX
			</div>
			<div class="clear"></div>
			';
		}
		// at inherited page since this is the only server in db
		elseif(empty($page) && !empty($ServerID))
		{
			echo '
			<div style="font-size: 10px; text-align: left; padding: 2px;">
			<a href="' . $_SERVER['PHP_SELF'] . '">Index</a>
			&bull;
			' . $ServerName . '
			</div>
			<div class="title">
			HOME PAGE
			</div>
			<div class="clear"></div>
			';
		}

		// lets  do the server stats page logic first
		if(!empty($ServerID))
		{
			// page content depending on selection
			if(!empty($page))
			{	
				if($page == 'player')
				{
					// include player.php contents
					require_once('./common/player.php');
				}
				elseif($page == 'suspicious')
				{
					// include suspicious.php contents
					require_once('./common/suspicious.php');
				}
				elseif($page == 'countries')
				{
					// include countries.php contents
					require_once('./common/countries.php');
				}
				elseif($page == 'maps')
				{
					// include maps.php contents
					require_once('./common/maps.php');
				}
				elseif($page == 'server')
				{
					// include serverstats.php contents
					require_once('./common/serverstats.php');
				}
				elseif($page == 'chat')
				{
					// include chat.php contents
					require_once('./common/chat.php');
				}
				elseif($page == 'leaders')
				{
					// include leaders.php contents
					require_once('./common/leaders.php');
				}
				elseif($page == 'home')
				{
					// include home.php contents
					require_once('./common/home.php');
				}
			}
			else
			{
				// include home.php contents
				require_once('./common/home.php');
			}
		}

		// or else begin global stats logic
		elseif(empty($ServerID))
		{
			// don't show this text unless only at the index page
			if(empty($page))
			{
				// include player.php contents
				require_once('./common/index-display-servers.php');
			}

			// or else a global stats page has been selected
			// page content depending on selection
			else
			{
				if($page == 'player')
				{
					// include player.php contents
					require_once('./common/player.php');
				}
				elseif($page == 'suspicious')
				{
					// include suspicious.php contents
					require_once('./common/suspicious.php');
				}
				elseif($page == 'countries')
				{
					// include countries.php contents
					require_once('./common/countries.php');
				}
				elseif($page == 'maps')
				{
					// include maps.php contents
					require_once('./common/maps.php');
				}
				elseif($page == 'server')
				{
					// include serverstats.php contents
					require_once('./common/serverstats.php');
				}
				elseif($page == 'chat')
				{
					// include chat.php contents
					require_once('./common/chat.php');
				}
				elseif($page == 'leaders')
				{
					// include leaders.php contents
					require_once('./common/leaders.php');
				}
				elseif($page == 'home')
				{
					// include home.php contents
					require_once('./common/home.php');
				}
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
		
		// get user's IP address
		$userip = $_SERVER["REMOTE_ADDR"];
		
		$ses = session_count($userip, $ServerID, $GameID, $BF4stats);
		echo '<br/><center><span class="footertext">' . $ses . ' users viewing these BF4 stats pages</span></center>';
		
		// figure out total page load time
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round(($endtime - $starttime),3);
		
		// display total page load time
		echo '<center><span class="footertext">server compiled page in ' . $totaltime . ' seconds</span></center>';
		
		// display bot stats
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
			echo '<center><span class="footertext">' . $TotalBots . ' bots have been denied access</span></center>';
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
			echo '<center><span class="footertext">' . $TotalDenied . ' archaic browsers have been blocked</span></center>';
		}
		
		// display total server memory used
		echo '<center><span class="footertext">' . round(memory_get_usage(false)/1024,0) . ' KB of server memory used</span></center>';
		
		echo '
		<br/>
		</div>
		</body>
		</html>
		';
	}
	else
	{
		// is IE 7 or lower
		
		// include necessary files
		require_once('./config/config.php');
		
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
		<meta name="copyright" content="2014 Open-Web-Community http://open-web-community.com/" />
		<meta name="keywords" content="Restricted" />
		<meta name="description" content="BF4 Stats Page - Restricted" />
		<link rel="stylesheet" href="./common/stats.css" type="text/css" />
		';
	
		// connect to the stats database
		require_once('./common/connect.php');
		
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
			// add this bot
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_denied`
				(`category`, `count`)
				VALUES ('browsers', '1')
			");
		}
		
		echo '
		<title>BF4 Stats Page - Restricted</title>
		</head>
		<body>
		<br/><br/>
		<center><b>Sorry, Internet Explorer version 7 and lower is not supported.</b><br/><br/>Update your browser version or disable compatibility mode in your browser.<br/>Please contact this website\'s administrator if you need further assistance.<br/><br/>Your user agent: ' . $useragent . '</center>
		</body>
		</html>
		';
	}
}
else
{
	// is a bot
	
	// include necessary files
	require_once('./config/config.php');
	
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
	<meta name="copyright" content="2014 Open-Web-Community http://open-web-community.com/" />
	<meta name="keywords" content="Restricted,BF4,Stats,' . $clan_name . '" />
	<meta name="description" content="BF4 Stats Page - ' . $clan_name . ' - Restricted" />
	<link rel="stylesheet" href="./common/stats.css" type="text/css" />
	';
	
	// connect to the stats database
	require_once('./common/connect.php');
	
	// check to see if bot stats table exists
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
	<title>BF4 Stats Page - ' . $clan_name . ' - Restricted</title>
	</head>
	<body>
	<br/><br/>
	<center><b>Sorry, bot access has been disabled.</b><br/><br/>Please contact this website\'s administrator if you need further assistance.<br/><br/>Your user agent: ' . $useragent . '</center>
	</body>
	</html>
	';
}
?>
