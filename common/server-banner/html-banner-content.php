<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
require_once('../constants.php');
require_once('../functions.php');
// get query string options
$ServerID = $sid;
// echo out HTML
echo '
<script type="text/javascript">
	$("#fadein").fadeOut("slow");
	$("#fadein").delay(28000).fadeIn("slow");
</script>
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
	$servername = textcleaner($CurrentMap_r['ServerName']);
	if(strlen($servername) > 24)
	{
		$servername = substr($servername,0,23);
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
echo '<center><a href="https://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . urlencode($server) . '" target="_blank"><b>' . $servername . '</b></a></center></div>';
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
elseif(!$Tickets_q || @mysqli_num_rows($Tickets_q) == 0)
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
// find current URL info
// is this an HTTPS server?
if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
{
	$host = 'https://' . $_SERVER['HTTP_HOST'];
}
else
{
	$host = 'http://' . $_SERVER['HTTP_HOST'];
}
$dir = dirname($_SERVER['PHP_SELF']);
// remove this directory name from the string
$dir = str_replace("/server-banner", "", $dir);
// adjust URL to reflect the location of index.php
$home = str_replace("common", "", $dir);
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
		' . $count . ' <a href="' . $host . $home . 'index.php?p=player&amp;sid=' . $ServerID . '&amp;player=' . $soldier . '" target="_blank">' . $soldier . '</a>
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
$old = $now_timestamp - 10800;
// check to see if this top twenty cache table exists
@mysqli_query($BF4stats,"
	CREATE TABLE IF NOT EXISTS `tyger_stats_top_twenty_cache`
	(
		`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		`PlayerID` INT(10) UNSIGNED NOT NULL,
		`GID` TINYINT(4) UNSIGNED NOT NULL,
		`SID` VARCHAR(100) NOT NULL,
		`SoldierName` VARCHAR(45) NOT NULL,
		`Score` INT(11) NOT NULL DEFAULT '0',
		`Playtime` INT(11) NOT NULL DEFAULT '0',
		`Kills` INT(11) NOT NULL DEFAULT '0',
		`KDR` VARCHAR(20) NOT NULL,
		`HSR` VARCHAR(20) NOT NULL,
		`timestamp` INT(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`ID`),
		UNIQUE `UNIQUE_TopTwentyData` (`PlayerID`, `GID`, `SID`),
		INDEX `PlayerID` (`PlayerID` ASC),
		INDEX `GID` (`GID` ASC),
		INDEX `SID` (`SID` ASC),
		INDEX `SoldierName` (`SoldierName` ASC),
		INDEX `Score` (`Score` ASC),
		INDEX `timestamp` (`timestamp` ASC),
		CONSTRAINT `fk_tyger_stats_top_twenty_cache_PlayerID` FOREIGN KEY (`PlayerID`) REFERENCES `tbl_playerdata`(`PlayerID`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `fk_tyger_stats_top_twenty_cache_GID` FOREIGN KEY (`GID`) REFERENCES `tbl_games`(`GameID`) ON DELETE CASCADE ON UPDATE CASCADE
	)
	ENGINE=InnoDB
");
// check if this is a top 20 player
// if so, we can get their score rank much faster from the cache
$Score_q = @mysqli_query($BF4stats,"
	SELECT `Score`, `SoldierName`
	FROM `tyger_stats_top_twenty_cache`
	WHERE `SID` = '{$ServerID}'
	AND `GID` = '{$GameID}'
	AND `timestamp` >= '{$old}'
	GROUP BY `PlayerID`
	ORDER BY tps.`Score` DESC, tpd.`SoldierName` ASC LIMIT 10
");
// if no recent results, do the slower query from live stats
if(!$Score_q || @mysqli_num_rows($Score_q) == 0)
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
		' . $count . ' <a href="' . $host . $home . 'index.php?p=player&amp;sid=' . $ServerID . '&amp;player=' . $soldier . '" target="_blank">' . $soldier . '</a>
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
?>
