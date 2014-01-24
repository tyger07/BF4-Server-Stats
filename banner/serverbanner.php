<?php

// first connect to the database
// include config.php contents
include_once('../config/config.php');
$BF4stats = mysqli_connect(HOST, USER, PASS, NAME, PORT);

// then include constants.php
include_once('../common/constants.php');

// we will need a server ID from the URL query string!
// if no data query string is provided, this is an image
if(!empty($_GET['ServerID']) AND is_numeric($_GET['ServerID']) AND empty($_GET['data']))
{
	$ServerID = mysqli_real_escape_string($BF4stats, $_GET['ServerID']);
	// then lets do some queries to get the data we will need
	// get current number of players
	$CurrentPlayers_q = @mysqli_query($BF4stats,"
		SELECT count(`TeamID`) AS `count`
		FROM `tbl_currentplayers`
		WHERE `ServerID` = {$ServerID}
	");
	if(@mysqli_num_rows($CurrentPlayers_q) != 0)
	{
		$CurrentPlayers_r = @mysqli_fetch_assoc($CurrentPlayers_q);
		$players = $CurrentPlayers_r['count'];
	}
	// some sort of error occured
	else
	{
		$players = 'Unknown';
	}
	// free up players query memory
	@mysqli_free_result($CurrentPlayers_q);
	// initialize values
	$team1 = 0;
	$team2 = 0;
	$team3 = 0;
	$team4 = 0;
	$score1 = 0;
	$score2 = 0;
	$score3 = 0;
	$score4 = 0;
	// get tickets information
	$Tickets_q = @mysqli_query($BF4stats,"
			SELECT `Score`, `WinningScore`, `TeamID`
			FROM `tbl_teamscores`
			WHERE `ServerID` = {$ServerID}
	");
	if(@mysqli_num_rows($Tickets_q) != 0)
	{
		while($Tickets_r = @mysqli_fetch_assoc($Tickets_q))
		{
			$score = $Tickets_r['Score'];
			$winning = $Tickets_r['WinningScore'];
			$team = $Tickets_r['TeamID'];
			// initialize that this team exists
			// this will be used to determine if there are 2 or 4 teams
			if($team == 1)
			{
				$team1 = 1;
				$score1 = $score;
			}
			if($team == 2)
			{
				$team2 = 1;
				$score2 = $score;
			}
			if($team == 3)
			{
				$team3 = 1;
				$score3 = $score;
			}
			if($team == 4)
			{
				$team4 = 1;
				$score4 = $score;
			}
		}
	}
	// some sort of error occured
	else
	{
		$score = 'Unknown';
		$winning = 'Unknown';
		$team = 'Unknown';
	}
	// free up ticket query memory
	@mysqli_free_result($CurrentPlayers_q);
	// get current map
	$CurrentMap_q = @mysqli_query($BF4stats,"
		SELECT `mapName`, `ServerName`, `maxSlots`, `Gamemode`
		FROM `tbl_server`
		WHERE `ServerID` = {$ServerID}
	");
	if(@mysqli_num_rows($CurrentMap_q) != 0)
	{
		$CurrentMap_r = @mysqli_fetch_assoc($CurrentMap_q);
		$map = $CurrentMap_r['mapName'];
		$servername = substr($CurrentMap_r['ServerName'],0,26);
		if(strlen($CurrentMap_r['ServerName']) > 26)
		{
			$servername .= '..';
		}
		$slots = $CurrentMap_r['maxSlots'];
		$mode = $CurrentMap_r['Gamemode'];
		// convert map to friendly name
		// first find if this map name is even in the map array
		if(in_array($map,$map_array))
		{
			$map_name = substr(array_search($map,$map_array),0,17);
			if(strlen(array_search($map,$map_array)) > 17)
			{
				$map_name .= '..';
			}
		}
		// this map is missing!
		else
		{
			$map_name = $map;
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
			$mode_name = $mode;
		}
	}
	// some sort of error occured
	else
	{
		$map_name = 'Unknown';
		$mode_name = 'Unknown';
		$slots = 'Unknown';
		$servername = 'Unknown';
	}
	// free up map query memory
	@mysqli_free_result($CurrentMap_q);
	// initialize value
	$online = 0;
	$row = 0;
	$player1 = 0;
	$player2 = 0;
	$player3 = 0;
	$player4 = 0;
	$player5 = 0;
	$player6 = 0;
	$player7 = 0;
	$pscore1 = 0;
	$pscore2 = 0;
	$pscore3 = 0;
	$pscore4 = 0;
	$pscore5 = 0;
	$pscore6 = 0;
	$pscore7 = 0;
	// query for scores
	$Score_q = @mysqli_query($BF4stats,"
		SELECT `Soldiername`, `Score`
		FROM `tbl_currentplayers`
		WHERE `ServerID` = {$ServerID}
		ORDER BY `Score` DESC
		LIMIT 7
	");
	if(@mysqli_num_rows($Score_q) != 0)
	{
		$online = 1;
		while($Score_r = @mysqli_fetch_assoc($Score_q))
		{
			$row++;
			if($row == 1)
			{
				$player1 = $Score_r['Soldiername'];
				$pscore1 = $Score_r['Score'];
			}
			if($row == 2)
			{
				$player2 = $Score_r['Soldiername'];
				$pscore2 = $Score_r['Score'];
			}
			if($row == 3)
			{
				$player3 = $Score_r['Soldiername'];
				$pscore3 = $Score_r['Score'];
			}
			if($row == 4)
			{
				$player4 = $Score_r['Soldiername'];
				$pscore4 = $Score_r['Score'];
			}
			if($row == 5)
			{
				$player5 = $Score_r['Soldiername'];
				$pscore5 = $Score_r['Score'];
			}
			if($row == 6)
			{
				$player6 = $Score_r['Soldiername'];
				$pscore6 = $Score_r['Score'];
			}
			if($row == 7)
			{
				$player7 = $Score_r['Soldiername'];
				$pscore7 = $Score_r['Score'];
			}
		}
	}
	// free up players query memory
	@mysqli_free_result($Score_q);
	
	// start outputting the image
	header("Content-type: image/png");
	
	// base image
	$base = imagecreatefrompng("./images/background.png");
	$white = imagecolorallocate($base, 255, 255, 255);
	imagecolortransparent($base, $white);
	imagealphablending($base, false);
	imagesavealpha($base, true);
	
	// text color
	$light = imagecolorallocate($base, 250, 250, 250);
	$dark = imagecolorallocate($base, 200, 200, 200);
	
	// add text to image
	// imagestring ( $base , int font , int x , int y , $string , int color )
	// server name
	imagestring($base, 2, 30, 25, $servername, $light);
	// players text
	imagestring($base, 2, 30, 40, "Players:", $dark);
	// players slots
	imagestring($base, 2, ((imagesx($base) - (strlen($players . '/' . $slots) * 6)) - 30), 40, $players . '/' . $slots, $dark);
	// map text
	imagestring($base, 2, 30, 55, "Map:", $dark);
	// map name
	imagestring($base, 2, ((imagesx($base) - (strlen($map_name) * 6)) - 30), 55, $map_name, $dark);
	// mapcode text
	imagestring($base, 2, 30, 70, "Mode:", $dark);
	// mapcode name
	imagestring($base, 2, ((imagesx($base) - (strlen($mode_name) * 6)) - 30), 70, $mode_name, $dark);
	// server tickets text
	imagestring($base, 2, 30, 90, "Tickets:", $light);
	// 4 teams
	if(($team3 == 1) OR ($team4 == 1))
	{
		// team 1 tickets text
		imagestring($base, 2, 30, 105, "Team1:", $dark);
		// team 1 score
		imagestring($base, 2, ((imagesx($base) - (strlen($score1 . '/' . $winning) * 6)) - 119), 105, $score1 . '/' . $winning, $dark);
		// team 2 tickets text
		imagestring($base, 2, 30, 120, "Team2:", $dark);
		// team 2 score
		imagestring($base, 2, ((imagesx($base) - (strlen($score2 . '/' . $winning) * 6)) - 119), 120, $score2 . '/' . $winning, $dark);
		// team 3 tickets text
		imagestring($base, 2, 119, 105, "Team3:", $dark);
		// team 3 score
		imagestring($base, 2, ((imagesx($base) - (strlen($score3 . '/' . $winning) * 6)) - 30), 105, $score3 . '/' . $winning, $dark);
		// team 4 tickets text
		imagestring($base, 2, 119, 120, "Team4:", $dark);
		// team 4 score
		imagestring($base, 2, ((imagesx($base) - (strlen($score4 . '/' . $winning) * 6)) - 30), 120, $score4 . '/' . $winning, $dark);
	}
	// 2 teams
	else
	{
		// team 1 tickets text
		imagestring($base, 2, 30, 105, "Team 1:", $dark);
		// team 1 score
		imagestring($base, 2, ((imagesx($base) - (strlen($score1 . '/' . $winning) * 6)) - 30), 105, $score1 . '/' . $winning, $dark);
		// team 2 tickets text
		imagestring($base, 2, 30, 120, "Team 2:", $dark);
		// team 2 score
		imagestring($base, 2, ((imagesx($base) - (strlen($score2 . '/' . $winning) * 6)) - 30), 120, $score2 . '/' . $winning, $dark);
	}
	// team 2 tickets text
	imagestring($base, 2, 30, 140, "Top Online Players:", $light);
	// are there players online?
	if($online == 1)
	{
		if($player1 !== 0)
		{
			// player 1 name text
			imagestring($base, 2, 30, 155, $player1, $dark);
			// player 1 score
			imagestring($base, 2, ((imagesx($base) - (strlen($pscore1) * 6)) - 30), 155, $pscore1, $dark);
		}
		if($player2 !== 0)
		{
			// player 1 name text
			imagestring($base, 2, 30, 170, $player2, $dark);
			// player 1 score
			imagestring($base, 2, ((imagesx($base) - (strlen($pscore2) * 6)) - 30), 170, $pscore2, $dark);
		}
		if($player3 !== 0)
		{
			// player 1 name text
			imagestring($base, 2, 30, 185, $player3, $dark);
			// player 1 score
			imagestring($base, 2, ((imagesx($base) - (strlen($pscore3) * 6)) - 30), 185, $pscore3, $dark);
		}
		if($player4 !== 0)
		{
			// player 1 name text
			imagestring($base, 2, 30, 200, $player4, $dark);
			// player 1 score
			imagestring($base, 2, ((imagesx($base) - (strlen($pscore4) * 6)) - 30), 200, $pscore4, $dark);
		}
		if($player5 !== 0)
		{
			// player 1 name text
			imagestring($base, 2, 30, 215, $player5, $dark);
			// player 1 score
			imagestring($base, 2, ((imagesx($base) - (strlen($pscore5) * 6)) - 30), 215, $pscore5, $dark);
		}
		if($player6 !== 0)
		{
			// player 1 name text
			imagestring($base, 2, 30, 230, $player6, $dark);
			// player 1 score
			imagestring($base, 2, ((imagesx($base) - (strlen($pscore6) * 6)) - 30), 230, $pscore6, $dark);
		}
		if($player7 !== 0)
		{
			// player 1 name text
			imagestring($base, 2, 30, 245, $player7, $dark);
			// player 1 score
			imagestring($base, 2, ((imagesx($base) - (strlen($pscore7) * 6)) - 30), 245, $pscore7, $dark);
		}
	}
	// there are no players online
	else
	{
		// no players online text
		imagestring($base, 2, 30, 160, "No players currently online.", $dark);
	}
	
	// compile image
	imagepng($base);
	imagedestroy($base);
}
// this is an image but no server ID was provided!
elseif(empty($_GET['data']))
{
	// start outputting the image
	header("Content-type: image/png");
	$string = $_GET['text'];
	
	// base image
	$base = imagecreatefrompng("./images/background.png");
	$back = imagecolorallocate($base, 0, 0, 0);
	imagecolortransparent($base, $back);
	imagealphablending($base, false);
	imagesavealpha($base, true);
	
	// text color
	$light = imagecolorallocate($base, 250, 250, 250);
	$dark = imagecolorallocate($base, 200, 200, 200);
	
	// add text to image
	// imagestring ( $base , int font , int x , int y , $string , int color )
	imagestring($base, 2, 38, 50, "No server ID was provided!", $light);
	imagestring($base, 1, 38, 85, "A server ID must be provided.", $dark);
	imagestring($base, 1, 38, 95, "For example: ?ServerID=1", $dark);
	
	// compile image
	imagepng($base);
	imagedestroy($base);
}
// this is an iframe showind data
elseif(!empty($_GET['ServerID']) AND is_numeric($_GET['ServerID']) AND !empty($_GET['data']))
{
	// get query string options
	$ServerID = mysqli_real_escape_string($BF4stats, $_GET['ServerID']);
	// background color?
	if(!empty($_GET['bgcolor']))
	{
		$bgcolor = mysqli_real_escape_string($BF4stats, $_GET['bgcolor']);
	}
	// use default
	else
	{
		$bgcolor = '1D2023';
	}
	// font color?
	if(!empty($_GET['fontcolor']))
	{
		$fontcolor = mysqli_real_escape_string($BF4stats, $_GET['fontcolor']);
	}
	// use default
	else
	{
		$fontcolor = 'BBBBBB';
	}
	// link color?
	if(!empty($_GET['linkcolor']))
	{
		$linkcolor = mysqli_real_escape_string($BF4stats, $_GET['linkcolor']);
	}
	// use default
	else
	{
		$linkcolor = '439BC8';
	}
	// section font color?
	if(!empty($_GET['sectionfontcolor']))
	{
		$sectionfontcolor = mysqli_real_escape_string($BF4stats, $_GET['sectionfontcolor']);
	}
	// use default
	else
	{
		$sectionfontcolor = 'AAAAAA';
	}
	// section background color?
	if(!empty($_GET['sectionbgcolor']))
	{
		$sectionbgcolor = mysqli_real_escape_string($BF4stats, $_GET['sectionbgcolor']);
	}
	// use default
	else
	{
		$sectionbgcolor = '0A0C0F';
	}
	// online player count?
	if(!empty($_GET['onlinecount']) AND is_numeric($_GET['onlinecount']))
	{
		$onlinecount = mysqli_real_escape_string($BF4stats, $_GET['onlinecount']);
	}
	// use default
	else
	{
		$onlinecount = 10;
	}
	// figure out this DIV's height based on number of players variable
	// online count section height in pixels
	$onlineheight = ($onlinecount * 19) + 6;
	// total page content height based on onlineheight
	$contentheight = 510 + $onlineheight;
	
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
	<meta name="copyright" content="2013 Open-Web-Community http://open-web-community.com/" />
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
	';
	// get current map
	$CurrentMap_q = @mysqli_query($BF4stats,"
		SELECT `mapName`, `ServerName`, `maxSlots`, `Gamemode`
		FROM `tbl_server`
		WHERE `ServerID` = {$ServerID}
	");
	if(@mysqli_num_rows($CurrentMap_q) != 0)
	{
		$CurrentMap_r = @mysqli_fetch_assoc($CurrentMap_q);
		$map = $CurrentMap_r['mapName'];
		$server = $CurrentMap_r['ServerName'];
		$servername = substr($CurrentMap_r['ServerName'],0,30);
		if(strlen($CurrentMap_r['ServerName']) > 30)
		{
			$servername .= '..';
		}
		$slots = $CurrentMap_r['maxSlots'];
		$mode = $CurrentMap_r['Gamemode'];
		// convert map to friendly name
		// first find if this map name is even in the map array
		if(in_array($map,$map_array))
		{
			$map_name = substr(array_search($map,$map_array),0,17);
			if(strlen(array_search($map,$map_array)) > 17)
			{
				$map_name .= '..';
			}
			$map_img = './images/maps/' . $map . '.png';
		}
		// this map is missing!
		else
		{
			$map_name = $map;
			$map_img = './images/maps/missing.png';
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
			$mode_name = $mode;
		}
	}
	// some sort of error occured
	else
	{
		$map_name = 'Unknown';
		$mode_name = 'Unknown';
		$slots = 'Unknown';
		$servername = 'Unknown';
		$map_img = './images/maps/missing.png';
	}
	// free up map query memory
	@mysqli_free_result($CurrentMap_q);
	// get current number of players
	$CurrentPlayers_q = @mysqli_query($BF4stats,"
		SELECT count(`TeamID`) AS count
		FROM `tbl_currentplayers`
		WHERE `ServerID` = {$ServerID}
	");
	if(@mysqli_num_rows($CurrentPlayers_q) != 0)
	{
		$CurrentPlayers_r = @mysqli_fetch_assoc($CurrentPlayers_q);
		$players = $CurrentPlayers_r['count'];
	}
	// some sort of error occured
	else
	{
		$players = 'Unknown';
	}
	// free up players query memory
	@mysqli_free_result($CurrentPlayers_q);
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
	// free up tickets query memory
	@mysqli_free_result($Tickets_q);
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
			$soldier = $Score_r['Soldiername'];
			$score = $Score_r['Score'];
			echo '
			<tr>
			<td width="75%" style="text-align: left;">
			' . $count . ' <a href="../index.php?ServerID=' . $ServerID . '&amp;SoldierName=' . $soldier . '&amp;search=1" target="_blank">' . $soldier . '</a>
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
	// free up player scores query memory
	@mysqli_free_result($Score_q);
	// display top all-time players
	echo '
	<div class="section"><b>Top 10 Players:</b></div>
	';
	// initialize value
	$count = 0;
	// query for player scores
	$Score_q = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName`, tps.`Score`
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tsp.`ServerID` = {$ServerID}
		ORDER BY tps.`Score` DESC, tpd.`SoldierName` ASC LIMIT 10
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
			$soldier = $Score_r['SoldierName'];
			$score = $Score_r['Score'];
			echo '
			<tr>
			<td width="75%" style="text-align: left;">
			' . $count . ' <a href="../index.php?ServerID=' . $ServerID . '&amp;SoldierName=' . $soldier . '&amp;search=1" target="_blank">' . $soldier . '</a>
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
	// free up player scores query memory
	@mysqli_free_result($Score_q);
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
// this is an iframe but no server ID was provided!
elseif(!empty($_GET['data']))
{
	// get query string options
	$ServerID = mysqli_real_escape_string($BF4stats, $_GET['ServerID']);
	// background color?
	if(!empty($_GET['bgcolor']))
	{
		$bgcolor = mysqli_real_escape_string($BF4stats, $_GET['bgcolor']);
	}
	// use default
	else
	{
		$bgcolor = '1D2023';
	}
	// font color?
	if(!empty($_GET['fontcolor']))
	{
		$fontcolor = mysqli_real_escape_string($BF4stats, $_GET['fontcolor']);
	}
	// use default
	else
	{
		$fontcolor = 'BBBBBB';
	}
	// link color?
	if(!empty($_GET['linkcolor']))
	{
		$linkcolor = mysqli_real_escape_string($BF4stats, $_GET['linkcolor']);
	}
	// use default
	else
	{
		$linkcolor = '439BC8';
	}
	// section font color?
	if(!empty($_GET['sectionfontcolor']))
	{
		$sectionfontcolor = mysqli_real_escape_string($BF4stats, $_GET['sectionfontcolor']);
	}
	// use default
	else
	{
		$sectionfontcolor = 'AAAAAA';
	}
	// section background color?
	if(!empty($_GET['sectionbgcolor']))
	{
		$sectionbgcolor = mysqli_real_escape_string($BF4stats, $_GET['sectionbgcolor']);
	}
	// use default
	else
	{
		$sectionbgcolor = '0A0C0F';
	}
	// online player count?
	if(!empty($_GET['onlinecount']) AND is_numeric($_GET['onlinecount']))
	{
		$onlinecount = mysqli_real_escape_string($BF4stats, $_GET['onlinecount']);
	}
	// use default
	else
	{
		$onlinecount = 10;
	}
	// figure out this DIV's height based on number of players variable
	// online count section height in pixels
	$onlineheight = ($onlinecount * 19) + 6;
	// total page content height based on onlineheight
	$contentheight = 510 + $onlineheight;
	
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
	<meta name="copyright" content="2013 Open-Web-Community http://open-web-community.com/" />
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