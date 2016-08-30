<?php
// BF4 Stats Page by Ty_ger07
// https://forum.myrcon.com/showthread.php?6854

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
require_once('../constants.php');

// we will need a server ID from the URL query string!
if(!empty($sid))
{
	// get query string options
	$ServerID = $sid;
	// background color?
	if(!empty($_GET['bgcolor']))
	{
		$bgcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['bgcolor'])));
	}
	// use default
	else
	{
		$bgcolor = '1D2023';
	}
	// font color?
	if(!empty($_GET['fontcolor']))
	{
		$fontcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['fontcolor'])));
	}
	// use default
	else
	{
		$fontcolor = 'BBBBBB';
	}
	// link color?
	if(!empty($_GET['linkcolor']))
	{
		$linkcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['linkcolor'])));
	}
	// use default
	else
	{
		$linkcolor = '439BC8';
	}
	// section font color?
	if(!empty($_GET['sectionfontcolor']))
	{
		$sectionfontcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['sectionfontcolor'])));
	}
	// use default
	else
	{
		$sectionfontcolor = 'AAAAAA';
	}
	// section background color?
	if(!empty($_GET['sectionbgcolor']))
	{
		$sectionbgcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['sectionbgcolor'])));
	}
	// use default
	else
	{
		$sectionbgcolor = '0A0C0F';
	}
	// online player count?
	if(!empty($_GET['onlinecount']) AND is_numeric($_GET['onlinecount']))
	{
		$onlinecount = mysqli_real_escape_string($BF4stats, strip_tags($_GET['onlinecount']));
	}
	// use default
	else
	{
		$onlinecount = 10;
	}
	// figure out this DIV's height based on number of players variable
	// online count section height in pixels
	$onlineheight = ($onlinecount * 18) + 6;
	// total page content height based on onlineheight
	$contentheight = 500 + $onlineheight;
	// echo out the header
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
	<meta name="keywords" content="banner" />
	<meta name="description" content="banner" />
	<meta http-equiv="refresh" content="30" />
	<title>banner</title>
	<style type="text/css">
		body{
			margin: 0 auto;
			padding: 0;
			text-align: left;
			background-color: #' . $bgcolor . ';
			background: #' . $bgcolor . ';
			font-family: Arial, Arial, Arial, sans-serif;
			font-size: 12px;
			color: #' . $fontcolor . ';
		}
		table{
			font-family: Arial, Arial, Arial, sans-serif;
			font-size: 12px;
		}
		th{
			font-family: Arial, Arial, Arial, sans-serif;
			font-size: 12px;
		}
		td{
			font-family: Arial, Arial, Arial, sans-serif;
			font-size: 12px;
		}
		a, a:visited, a:hover, a:active{
			color: #' . $linkcolor . ';
			font-family: Arial, Arial, Arial, sans-serif;
			font-size: 12px;
			text-decoration: none;
		}
		#content{
			border-style: solid;
			border-width: 1px;
			border-color: #000000;
			width: 214px;
			height: ' . $contentheight . 'px;
			padding: 2px;
			font-family: Arial, Arial, Arial, sans-serif;
			font-size: 12px;
		}
		.section{
			background-color: #' . $sectionbgcolor . ';
			color: #' . $sectionfontcolor . ';
			padding: 4px;
			font-family: Arial, Arial, Arial, sans-serif;
			font-size: 12px;
		}
		.online{
			height: ' . $onlineheight . 'px;
			overflow-y: auto;
			overflow-x: hidden;
			font-family: Arial, Arial, Arial, sans-serif;
			font-size: 12px;
		}
	</style>
	</head>	
	<body>
	<div id="content">
	<div class="section">
	';
	// get current map
	$CurrentMap_q = @mysqli_query($BF4stats,"
		SELECT `mapName`, `ServerName`, `maxSlots`, `usedSlots`, `Gamemode`
		FROM `tbl_server`
		WHERE `ServerID` = {$ServerID}
		AND `GameID` = {$GameID}
	");
	if(@mysqli_num_rows($CurrentMap_q) != 0)
	{
		$CurrentMap_r = @mysqli_fetch_assoc($CurrentMap_q);
		$map = $CurrentMap_r['mapName'];
		$server = $CurrentMap_r['ServerName'];
		$servername = substr($CurrentMap_r['ServerName'],0,28);
		if(strlen($CurrentMap_r['ServerName']) > 28)
		{
			$servername .= '..';
		}
		$slots = $CurrentMap_r['maxSlots'];
		$players = $CurrentMap_r['usedSlots'];
		$mode = $CurrentMap_r['Gamemode'];
		// convert map to friendly name
		// first find if this map name is even in the map array
		if(in_array($map,$map_array))
		{
			$map_name = substr(array_search($map,$map_array),0,17);
			if(strlen(array_search($map,$map_array)) > 16)
			{
				$map_name .= '..';
			}
			$map_img = '../images/maps/' . $map . '.png';
		}
		// this map is missing!
		else
		{
			$map_name = substr($map,0,17);
			if(strlen($map_name) > 16)
			{
				$map_name .= '..';
			}
			$map_img = '../images/maps/missing.png';
		}
		// convert mode to friendly name
		// first find if this mode name is even in the mode array
		if(in_array($mode,$mode_array))
		{
			$mode_name = substr(array_search($mode,$mode_array),0,17);
			if(strlen(array_search($mode,$mode_array)) > 17)
			{
				$mode_name .= '..';
			}
		}
		// this map is missing!
		else
		{
			$mode_name = substr($mode,0,17);
			if(strlen($mode_name) > 16)
			{
				$mode_name .= '..';
			}
		}
	}
	// some sort of error occured
	else
	{
		$map_name = 'Unknown';
		$mode_name = 'Unknown';
		$slots = 'Unknown';
		$players = 'Unknown';
		$servername = 'Unknown';
		$server = 'Unknown';
		$map_img = '../images/maps/missing.png';
	}
	// display server information
	echo '<center><a href="http://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . urlencode($server) . '" target="_blank"><b>' . $servername . '</b></a></center></div>';
	echo '
	<center>
	<table border="0" align="center" width="198px" style="padding: 1px;">
	<tr>
	<td width="30%" style="text-align: left;">
	Players:
	</td>
	<td width="70%" style="text-align: right;">
	' . $players . '/' . $slots . '
	</td>
	</tr>
	<tr>
	<td width="30%" style="text-align: left;">
	Map:
	</td>
	<td width="70%" style="text-align: right;">
	' . $map_name . '
	</td>
	</tr>
	<tr>
	<td width="30%" style="text-align: left;">
	Mode:
	</td>
	<td width="70%" style="text-align: right;">
	' . $mode_name . '
	</td>
	</tr>
	</table>
	</center>';
	// display server map
	echo '<div style="padding-bottom: 4px;"><center><img src="' . $map_img . '" alt="map" style="border-style: solid; border-width: 1px; border-color: #000000;"/></center></div>';
	// display team tickets information
	echo '<div class="section"><b>Team Tickets:</b></div>';
	// get tickets information
	$Tickets_q = @mysqli_query($BF4stats,"
		SELECT `Score`, `WinningScore`, `TeamID`
		FROM `tbl_teamscores`
		WHERE `ServerID` = {$ServerID}
	");
	// two teams
	if(@mysqli_num_rows($Tickets_q) == 2)
	{
		echo '
		<center>
		<table border="0" align="center" width="198px" style="padding: 1px;">
		';
		while($Tickets_r = @mysqli_fetch_assoc($Tickets_q))
		{
			$score = $Tickets_r['Score'];
			$winning = $Tickets_r['WinningScore'];
			$team = $Tickets_r['TeamID'];
			echo '
			<tr>
			<td width="50%" style="text-align: left;">
			Team ' . $team . ':
			</td>
			<td width="50%" style="text-align: right;">
			' . $score . '/' . $winning . '
			</td>
			</tr>
			';
		}
		echo '
		</table>
		</center>';
	}
	// more than 2 teams
	if(@mysqli_num_rows($Tickets_q) > 2)
	{
		echo '
		<center>
		<table border="0" align="center" width="198px" style="padding: 1px;">
		<tr>
		';
		// initialize value
		$count = 0;
		while($Tickets_r = @mysqli_fetch_assoc($Tickets_q))
		{
			$count++;
			$score = $Tickets_r['Score'];
			$winning = $Tickets_r['WinningScore'];
			$team = $Tickets_r['TeamID'];
			echo '
			<td width="22%" style="text-align: left;">
			Team' . $team . ':
			</td>
			<td width="28%" style="text-align: right; padding-right: 8px;">
			' . $score . '/' . $winning . '
			</td>
			';
			if($count == 2)
			{
				echo '</tr><tr>';
			}
		}
		echo '
		</tr>
		</table>
		</center>';
	}
	// no teams found
	// some sort of error occured
	elseif(@mysqli_num_rows($Tickets_q) == 0)
	{
		echo '
		<center>
		<table border="0" align="center" width="198px" style="padding: 2px;">
		<tr>
		<td width="50%" style="text-align: left;">
		Team1:
		</td>
		<td width="50%" style="text-align: right;">
		Not Found
		</td>
		</tr>
		<tr>
		<td width="50%" style="text-align: left;">
		Team2:
		</td>
		<td width="50%" style="text-align: right;">
		Not Found
		</td>
		</tr>
		</table>
		</center>';
	}
	// display online players
	echo '
	<div class="section"><b>Online Players:</b></div>
	<div class="online">
	';
	// initialize value
	$count = 0;
	// query for player scores
	$Score_q = @mysqli_query($BF4stats,"
		SELECT `Soldiername`, `Score`
		FROM `tbl_currentplayers`
		WHERE `ServerID` = {$ServerID}
		ORDER BY `Score` DESC
	");
	if(@mysqli_num_rows($Score_q) != 0)
	{
		echo '
		<center>
		<table border="0" align="center" width="100%" style="padding: 2px;">
		';
		while($Score_r = @mysqli_fetch_assoc($Score_q))
		{
			$count++;
			$soldier = htmlspecialchars(strip_tags($Score_r['Soldiername']));
			$score = $Score_r['Score'];
			echo '
			<tr>
			<td width="75%" style="text-align: left;">
			' . $count . ' <a href="../../index.php?p=player&amp;sid=' . $ServerID . '&amp;player=' . $soldier . '" target="_blank">' . $soldier . '</a>
			</td>
			<td width="25%" style="text-align: right;">
			' . $score . '
			</td>
			</tr>
			';
		}
		echo '
		</table>
		</center>';
	}
	else
	{
		echo '
		<center>
		<table border="0" align="center" width="100%" style="padding: 2px;">
		<tr>
		<td width="100%" style="text-align: left;">
		No players online.
		</td>
		</tr>
		</table>
		</center>
		';
	}
	echo '</div>';
	// display top all-time players
	echo '
	<div class="section"><b>Top 10 Players:</b></div>
	';
	// initialize value
	$count = 0;
	// query for player scores
	// use faster query if top 20 cache is available
	// initialize timestamp values
	$now_timestamp = time();
	$old = $now_timestamp - 43200;
	// check if this is a top 20 player
	// if so, we can get their score rank much faster
	$Score_q = @mysqli_query($BF4stats,"
		SELECT `Score`, `SoldierName`
		FROM `tyger_stats_top_twenty_cache`
		WHERE `SID` = '{$ServerID}'
		AND `GID` = '{$GameID}'
		AND `timestamp` >= '{$old}'
		GROUP BY `PlayerID`
		ORDER BY tps.`Score` DESC, tpd.`SoldierName` ASC LIMIT 10
	");
	if(@mysqli_num_rows($Score_q) == 0)
	{
		$Score_q = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tps.`Score`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
			ORDER BY tps.`Score` DESC, tpd.`SoldierName` ASC LIMIT 10
		");
	}
	if(@mysqli_num_rows($Score_q) != 0)
	{
		echo '
		<center>
		<table border="0" align="center" width="100%" style="padding: 2px;">
		';
		while($Score_r = @mysqli_fetch_assoc($Score_q))
		{
			$count++;
			$soldier = htmlspecialchars(strip_tags($Score_r['SoldierName']));
			$score = $Score_r['Score'];
			echo '
			<tr>
			<td width="75%" style="text-align: left;">
			' . $count . ' <a href="../../index.php?p=player&amp;sid=' . $ServerID . '&amp;player=' . $soldier . '" target="_blank">' . $soldier . '</a>
			</td>
			<td width="25%" style="text-align: right;">
			' . $score . '
			</td>
			</tr>
			';
		}
		echo '
		</table>
		</center>';
	}
	else
	{
		echo '
		<center>
		<table border="0" align="center" width="100%" style="padding: 2px;">
		<tr>
		<td width="100%" style="text-align: left;">
		No players found.
		</td>
		</tr>
		</table>
		</center>
		';
	}
	echo '
	</div>
	<br/>
	suggested iframe size:<br/>
	';
	$suggestheight = $contentheight + 6;
	echo '
	width: 220px height: ' . $suggestheight . 'px
	</body>
	</html>
	';
}
// no server ID was provided!
else
{
	// background color?
	if(!empty($_GET['bgcolor']))
	{
		$bgcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['bgcolor'])));
	}
	// use default
	else
	{
		$bgcolor = '1D2023';
	}
	// font color?
	if(!empty($_GET['fontcolor']))
	{
		$fontcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['fontcolor'])));
	}
	// use default
	else
	{
		$fontcolor = 'BBBBBB';
	}
	// link color?
	if(!empty($_GET['linkcolor']))
	{
		$linkcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['linkcolor'])));
	}
	// use default
	else
	{
		$linkcolor = '439BC8';
	}
	// section font color?
	if(!empty($_GET['sectionfontcolor']))
	{
		$sectionfontcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['sectionfontcolor'])));
	}
	// use default
	else
	{
		$sectionfontcolor = 'AAAAAA';
	}
	// section background color?
	if(!empty($_GET['sectionbgcolor']))
	{
		$sectionbgcolor = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['sectionbgcolor'])));
	}
	// use default
	else
	{
		$sectionbgcolor = '0A0C0F';
	}
	// online player count?
	if(!empty($_GET['onlinecount']) AND is_numeric($_GET['onlinecount']))
	{
		$onlinecount = mysqli_real_escape_string($BF4stats, strip_tags(preg_replace('/\s/','',$_GET['onlinecount'])));
	}
	// use default
	else
	{
		$onlinecount = 10;
	}
	// figure out this DIV's height based on number of players variable
	// online count section height in pixels
	$onlineheight = ($onlinecount * 18) + 6;
	// total page content height based on onlineheight
	$contentheight = 500 + $onlineheight;
	// echo out the header
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
	<meta name="keywords" content="banner" />
	<meta name="description" content="banner" />
	<title>banner</title>
	<style type="text/css">
		body{
			margin: 0 auto;
			padding: 0;
			text-align: left;
			background-color: #' . $bgcolor . ';
			background: #' . $bgcolor . ';
			font-family: Arial, Arial, Arial, sans-serif;
			font-size: 12px;
			color: #' . $fontcolor . ';
		}
		a, a:visited, a:hover, a:active{
			color: #' . $linkcolor . ';
			font-size: 12px;
			text-decoration: none;
		}
		#content{
			border-style: solid;
			border-width: 1px;
			border-color: #000000;
			width: 214px;
			height: ' . $contentheight . 'px;
			padding: 2px;
			font-size: 12px;
		}
		.section{
			background-color: #' . $sectionbgcolor . ';
			color: #' . $sectionfontcolor . ';
			padding: 4px;
			font-size: 12px;
		}
		.online{
			height: ' . $onlineheight . 'px;
			overflow-y: auto;
			overflow-x: hidden;
			font-size: 12px;
		}
	</style>
	</head>	
	<body>
	<div id="content">
	<div class="section">
	No ServerID was provided!
	</div>
	<br/>
	You must provide a ServerID.
	</div>
	<br/>
	suggested iframe size:<br/>
	';
	$suggestheight = $contentheight + 6;
	echo '
	width: 220px height: ' . $suggestheight . 'px
	</body>
	</html>
	';
}
?>