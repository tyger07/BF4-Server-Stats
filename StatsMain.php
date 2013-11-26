<?php
// server stats index page by Ty_ger07 at http://open-web-community.com/
// THIS FOLLOWING INFORMATION NEEDS TO BE FILLED IN

// DATABASE SERVER ID
$server_ID		= array('',''); // If you have multiple servers in same database, enter each server ID.  Otherwise, enter each one as '1'.
//
// for example, if you use different server IDs:
// $server_ID	= array('1','2','3');

// SERVER NAME
$server_name	= array('',''); // Enter each server name to display.
//
// for example:
// $server_name	= array('Server 1','Server 2','Server 3');

// STATS LINK
$stats_link		= array('',''); // Enter each server stats link.
//
// for example:
// $stats_link	= array('player_stats1.php','player_stats2.php','player_stats3.php');

// BATTLE LOG LINK
$battlelog		= array('',''); // your server battlelog link

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING
//
//
// include common.php contents
require_once('./common/common.php');
// database connection details
$db_connect = $db_host . ':' . $db_port;
// start counting page load time
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;
// hide php notices
error_reporting(E_ALL ^ E_NOTICE);
// output the header
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
<meta name="keywords" content="BF4,Player,Stats,Server,Index,' . $clan_name . '" />
<meta name="description" content="This is the ' . $clan_name . ' BF4 player stats server index page." />
<title>' . $clan_name . ' BF4 Player Stats Index Page</title>
<link rel="stylesheet" href="./common/stats.css" type="text/css" />
</head>
<body>
<br/>
<div id="pagebody">
<br/>
<table width="100%" cellspacing="1">
<tr> 
<td>
<div>
<div class="topcontent">
<center><a href="' . $banner_url . '" target="_blank"><img alt="BF4 Stats Page Copyright 2013 Open-Web-Community" border="0" src="' . $banner_image . '" /></a></center>
</div>
<br/><br/>
<div class="topcontent">
<table width="100%" border="0">
<tr>
<td class="headline">
<center>
<b>' . $clan_name . ' BF4 Player Stats Index Page</b>
</center>
</td>
</tr>
</table>
</div>
<table border="0" width="100%" align="center">
<tr>
<td>
<center>
';
// connect to the database if not already done
@mysql_connect($db_connect, $db_uname, $db_pass);
@mysql_select_db($db_name) or die ("<b><br/><br/>Unable to access stats database. Please notify this website's administrator.</b><br/>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger'>here</a>.<br/><br/></center></td></tr></table></div></td></tr></table></div></body></html>");
echo '
<table width="100%">
<tr>
<td width="1%">
</td>
<td>
<table width="100%">
<tr>
<td>
<br/><div class="innercontent">
<table width="100%" border="0">
<tr>
<td width="100%" align="center" style="text-align: left;">
<br/>&nbsp; Please select the desired server stats page from our game servers listed below:<br/><br/><br/>
</td>
</tr>
';
// initialize values
$step = 0;
$now_timestamp = time();
$old = $now_timestamp - 1800;
foreach($server_ID as $sid)
{
	// initialize values
	$ses = 0;
	$players = 0;
	$total_players = 0;
	$score = 0;
	$kills = 0;
	$server = $server_name[$step];
	$link = $stats_link[$step];
	$battlelink = $battlelog[$step];
	// get server stats
	$query  = @mysql_query("SELECT `CountPlayers`, `SumScore`, `SumKills` FROM tbl_server_stats WHERE `ServerID` = '$sid'");
	if(@mysql_num_rows($query)!=0)
	{
		$row = @mysql_fetch_assoc($query);
		$total_players = $row['CountPlayers'];
		$score = $row['SumScore'];
		$kills = $row['SumKills'];
	}
	// get current players
	$current_players = @mysql_query("SELECT count(`TeamID`) AS count FROM tbl_currentplayers WHERE `ServerID` = '$sid'");
	if(@mysql_num_rows($current_players)!=0)
	{
		$current_row = @mysql_fetch_assoc($current_players);
		$players = $current_row['count'];
	}
	// get current map
	// make an array of map names
	$map_array = array('Zavod 311'=>'MP_Abandoned','Lancang Dam'=>'MP_Damage','Flood Zone'=>'MP_Flooded','Golmud Railway'=>'MP_Journey','Paracel Storm'=>'MP_Naval','Operation Locker'=>'MP_Prison','Hainan Resort'=>'MP_Resort','Siege of Shanghai'=>'MP_Siege','Rogue Transmission'=>'MP_TheDish','Dawnbreaker'=>'MP_Tremors');
	$map_query = @mysql_query("SELECT `mapName` FROM tbl_server WHERE `ServerID` = '$sid'");
	if(@mysql_num_rows($map_query)!=0)
	{
		$map_row = @mysql_fetch_assoc($map_query);
		// convert map to friendly name
		$map = $map_row['mapName'];
		$map_name = array_search($map,$map_array);
		$map_img = './images/maps/' . $map . '.jpg';
	}
	// remove ses older than 30 minutes
	@mysql_query("DELETE FROM ses_{$sid}_tbl WHERE `timestamp` <= '$old'");
	@mysql_query("OPTIMIZE TABLE ses_{$sid}_tbl");
	// count ses
	$session_count = @mysql_query("SELECT count(`IP`) AS count FROM ses_{$sid}_tbl WHERE 1");
	if(@mysql_num_rows($session_count)!=0)
	{
		$session_row = @mysql_fetch_assoc($session_count);
		$ses = $session_row['count'];
	}
	echo '
	<tr>
	<td width="100%" style="text-align: left;">
	<div style="background-image: url(' . $map_img . '); background-position: left center; background-repeat: repeat; background-size: 100% auto; border-radius: 10px; box-shadow: 2px 2px 20px 2px rgba(0,0,0,0.5);">
	<div style="background-image: url(./images/50.png), linear-gradient(170deg, rgba(020,000,000,0.8), rgba(040,040,040,0.4), rgba(000,000,020,0.8)); border-radius: 10px;">
	<table width="95%" align="center" border="0">
	<tr>
	<td width="35%">
	<br/><a href="' . $link . '"><font size="4">' . $server . '</font></a><br/><br/>
	</td>
	<td width="22%">
	<br/><font class="information">Current Players In Server:</font> ' . $players . '<br/><br/>
	</td>
	<td width="22%">
	<br/><font class="information">Current Map:</font> ' . $map_name . '<br/><br/>
	</td>
	<td width="21%">
	<br/><font class="information">Users Viewing Stats:</font> ' . $ses . '<br/><br/>
	</td>
	</tr>
	<tr>
	<td width="35%">
	<a href="' . $battlelink . '" target="_blank"><img src="./images/joinbtn.png" alt="join"/></a><br/><br/>
	</td>
	<td width="22%">
	<font class="information">Players Logged:</font> ' . $total_players . '<br/><br/>
	</td>
	<td width="22%">
	<font class="information">Total Score:</font> ' . $score . '<br/><br/>
	</td>
	<td width="21%">
	<font class="information">Total Kills:</font> ' . $kills . '<br/><br/>
	</td>
	</tr>
	</table>
	</div>
	</div>
	<br/><br/>
	</td>
	</tr>
	';
	// step through the arrays
	$step++;
}
echo '
</table>
</div>
</td></tr></table>
<table width="100%">
<tr>
<td>
';
// query for server totals
$server_totals = @mysql_query("SELECT SUM(CountPlayers) AS total_players, SUM(SumRounds) AS total_rounds, SUM(SumPlaytime) AS total_playtime, SUM(SumTKs) AS total_tks, SUM(SumKills) AS total_kills, SUM(SumDeaths) AS total_deaths, SUM(SumHeadshots) AS total_headshots, SUM(SumSuicide) AS total_suicides FROM tbl_server_stats WHERE 1");
if(@mysql_num_rows($server_totals)!=0)
{
	$totals_row = @mysql_fetch_assoc($server_totals);
	$total_players = $totals_row['total_players'];
	$total_rounds = $totals_row['total_rounds'];
	$total_playtime = $totals_row['total_playtime'];
	$total_days = round($total_playtime/60/60/24,0);
	$total_tks = $totals_row['total_tks'];
	$total_kills = $totals_row['total_kills'];
	$total_deaths = $totals_row['total_deaths'];
	$total_headshots = $totals_row['total_headshots'];
	$total_suicides = $totals_row['total_suicides'];
	echo '
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>Server Totals</b></th>
	</tr>
	<tr>
	<td>
	<div class="innercontent">
	<table width="98%" align="center" border="0" class="prettytable">
	<tr>
	<td width="10%" style="text-align:left"><br/>&nbsp;<br/><br/></td>
	<td width="22%" style="text-align:left"><br/><font class="information">Total Players:</font> ' . $total_players . '<br/><br/></td>
	<td width="22%" style="text-align:left"><br/><font class="information">Total Rounds Played:</font> ' . $total_rounds . '<br/><br/></td>
	<td width="22%" style="text-align:left"><br/><font class="information">Total Days Played:</font> ' . $total_days . '<br/><br/></td>
	<td width="22%" style="text-align:left"><br/><font class="information">Total Team Kills:</font> ' . $total_tks . '<br/><br/></td>
	</tr>
	<tr>
	<td width="10%" style="text-align:left">&nbsp;</td>
	<td width="22%" style="text-align:left"><font class="information">Total Kills:</font> ' . $total_kills . '</td>
	<td width="22%" style="text-align:left"><font class="information">Total Deaths:</font> ' . $total_deaths . '</td>
	<td width="22%" style="text-align:left"><font class="information">Total Headshots:</font> ' . $total_headshots . '</td>
	<td width="22%" style="text-align:left"><font class="information">Total Suicides:</font> ' . $total_suicides . '</td>
	</tr>
	</table><br/>
	</div>
	</td>
	</tr>
	</table>
	</div>
	<br/>
	';
}
echo '
</td>
</tr>
</table>
</td>
<td width="1%"></td>
</tr>
</table>
</center>
</td>
</tr> 
</table>
</div>
';
// finish counting page load time
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = round(($endtime - $starttime),3);
echo '
<center><font class="footertext">data computed in ' . $totaltime . ' seconds</font></center><br/>
</td>
</tr>
</table>
</div>
<br/>
</body>
</html>
';
// flush buffers in case it is necessary
flush();
ob_flush();
?>