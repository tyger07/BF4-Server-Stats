<?php
// server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// hide php notices
error_reporting(E_ALL ^ E_NOTICE);

// include common.php contents
require_once('./common/common.php');

// include functions.php contents
require_once('./common/functions.php');

// include constants.php contents
require_once('./common/constants.php');

// start counting page load time
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

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
<link rel="stylesheet" href="./common/stats.css" type="text/css" />
';

// connect to this database
$BF4stats = @mysqli_connect($db_host, $db_uname, $db_pass, $db_name, $db_port) or die ("<title>BF4 Player Stats - Error</title></head><body><br/><br/><center><b>Unable to access stats database. Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/></body></html>");
@mysqli_select_db($BF4stats, "$db_name") or die ("<title>BF4 Player Stats - Error</title></head><body><br/><br/><center><b>Unable to access stats database. Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/></body></html>");

// find all servers in this database
$ServerID_q = @mysqli_query($BF4stats,"
	SELECT `ServerID`
	FROM `tbl_server`
	WHERE 1
");
// at least one server was found
if(@mysqli_num_rows($ServerID_q) != 0)
{
	// initialize empty array
	$ServerIDs = array();
	// add found servers ID to array
	while($ServerID_r = @mysqli_fetch_assoc($ServerID_q))
	{
		$ServerIDs[] = $ServerID_r['ServerID'];
	}
}
// no result found
// assume database connection error
// there must be at least one server, right?
else
{
	$ServerIDs = array('1');
}
// free up server id query memory
@mysqli_free_result($ServerID_q);

// initialize $ServerID as null
$ServerID = null;

// was a server ID given in the URL?  Is it a valid server ID?
// if so, we will initialize the $ServerID variable which will be used often in the rest of this page
// and we will find this server's, create a battlelog link, and finish this page's header
if(isset($_GET['ServerID']) AND !empty($_GET['ServerID']) AND is_numeric($_GET['ServerID']) AND in_array($_GET['ServerID'],$ServerIDs))
{
	// this is momentus!
	// this is important!
	// this means that you are  viewing a server page and not the index!
	// assign the ServerID variable with this server ID
	// this ServerID variable will be used over and over again
	// this is easily the most important variable in this code
	$ServerID = $_GET['ServerID'];
	// find this server name
	$ServerName_q = @mysqli_query($BF4stats,"
		SELECT `ServerName`
		FROM `tbl_server`
		WHERE `ServerID` = {$ServerID}
	");
	// the server name was found
	if(@mysqli_num_rows($ServerName_q) == 1)
	{
		$ServerName_r = @mysqli_fetch_assoc($ServerName_q);
		$ServerName = $ServerName_r['ServerName'];
		$battlelog = 'http://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . $ServerName;
	}
	// a database error occured?
	// oh well, we will have to do something
	else
	{
		$ServerName = 'Not Found';
		$battlelog = 'http://battlelog.battlefield.com/bf4/servers/pc/';
	}
	// free up server name query memory
	@mysqli_free_result($ServerName_q);

	// change page title, meta description, and keywords depending on the page content
	if((isset($_GET['search']) AND !empty($_GET['search'])) AND (isset($_GET['SoldierName']) AND !empty($_GET['SoldierName'])))
	{
		// remove spaces from name input
		$SoldierName = preg_replace('/\s/','',($_GET['SoldierName']));
		// remove dangerous / invalid characters from input
		if((strpos($SoldierName,'`') !== false) OR (strpos($SoldierName,'\'') !== false) OR (strpos($SoldierName,'=') !== false))
		{
			$SoldierName = 'Not Found';
		}
		echo '
		<meta name="keywords" content="' . $SoldierName . ',' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server player stats page for ' . $SoldierName . '." />
		<title>' . $clan_name . ' BF4 Player Stats - ' . $SoldierName . ' - ' . $ServerName . '</title>
		';
	}
	elseif((isset($_GET['search']) AND !empty($_GET['search'])) AND (isset($_GET['PlayerID']) AND !empty($_GET['PlayerID'])))
	{
		// make sure player id provided is a number
		if(is_numeric($_GET['PlayerID']))
		{
			// search for soldier name using provided player ID
			$SoldierName_q = @mysqli_query($BF4stats,"
				SELECT `SoldierName`
				FROM `tbl_playerdata`
				WHERE `PlayerID` = {$_GET['PlayerID']}
			");
			if(@mysqli_num_rows($SoldierName_q) == 1)
			{
				$SoldierName_r = @mysqli_fetch_assoc($SoldierName_q);
				$SoldierName = $SoldierName_r['SoldierName'];
			}
			else
			{
				$SoldierName = 'Not Found';
			}
			// free up soldier name query memory
			@mysqli_free_result($SoldierName_q);
		}
		// invalid
		else
		{
			$SoldierName = 'Not Found';
		}
		echo '
		<meta name="keywords" content="' . $SoldierName . ',' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server player stats page for ' . $SoldierName . '." />
		<title>' . $clan_name . ' BF4 Player Stats - ' . $SoldierName . ' - ' . $ServerName . '</title>
		';
	}
	elseif(isset($_GET['suspicious']) AND !empty($_GET['suspicious']))
	{
		echo '
		<meta name="keywords" content="Suspicious,Players,' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server Suspicious Players page." />
		<title>' . $clan_name . ' BF4 Player Stats - Suspicious Players - ' . $ServerName . '</title>
		';
	}
	elseif(isset($_GET['topplayers']) AND !empty($_GET['topplayers']))
	{
		echo '
		<meta name="keywords" content="Top,Players,' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server player stats page of Top Players." />
		<title>' . $clan_name . ' BF4 Player Stats - Top Players - ' . $ServerName . '</title>
		';
	}
	elseif(isset($_GET['countries']) AND !empty($_GET['countries']))
	{
		echo '
		<meta name="keywords" content="Country,' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server Country Stats page." />
		<title>' . $clan_name . ' BF4 Player Stats - Country Stats - ' . $ServerName . '</title>
		';
	}
	elseif(isset($_GET['maps']) AND !empty($_GET['maps']))
	{
		echo '
		<meta name="keywords" content="Map,' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server Map Stats page." />
		<title>' . $clan_name . ' BF4 Player Stats - Map Stats - ' . $ServerName . '</title>
		';
	}
	elseif(isset($_GET['serverstats']) AND !empty($_GET['serverstats']))
	{
		echo '
		<meta name="keywords" content="Server,Scoreboard,' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats,Info" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server Scoreboard and Info page." />
		<title>' . $clan_name . ' BF4 Player Stats - Server Info - ' . $ServerName . '</title>
		<meta http-equiv="refresh" content="60" />
		';
	}
	elseif(isset($_GET['chat']) AND !empty($_GET['chat']))
	{
		echo '
		<meta name="keywords" content="Chat,' . $ServerName . ',' . $clan_name . ',BF4,Player,Recent,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server Chat Content page." />
		<title>' . $clan_name . ' BF4 - Recent Chat - ' . $ServerName . '</title>
		<meta http-equiv="refresh" content="60" />
		';
	}
	else
	{
		echo '
		<meta name="keywords" content="Main,Top,Players,' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server main player stats page." />
		<title>' . $clan_name . ' BF4 Player Stats - Main Page - ' . $ServerName . '</title>
		<meta http-equiv="refresh" content="60" />
		';
	}
}
// no server ID was given in the URL or an invalid server ID was given, so use index page header
else
{
	echo '
	<meta name="keywords" content="BF4,Player,Stats,Server,Index,' . $clan_name . '" />
	<meta name="description" content="This is the ' . $clan_name . ' BF4 player stats server index page." />
	<title>' . $clan_name . ' BF4 Player Stats - Index Page</title>
	';
}
echo '
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
';
// if not at index page, display index link
if(isset($ServerID) AND !is_null($ServerID))
{
	echo '
	<br/>
	<div class="topcontent">
	<table width="98%" align="center" border="0">
	<tr>
	<td width="90%">
	<table width="100%" border="0">
	<tr>
	<td>
	<br/><a href="' . $_SERVER['PHP_SELF'] . '"><font size="3">Return to ' . $clan_name . ' Stats Index Page</font></a><br/>
	</td>
	</tr>
	<tr>
	<td>
	<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '"><font class="information">Currently viewing:</font> ' . $ServerName . '</a><br/>
	</td>
	</tr>
	</table>
	</td>
	<td width="10%" style="text-align: right;">
	<br/><a href="' . $battlelog . '" target="_blank"><img src="./images/joinbtn.png" alt="join" class="joinbutton"/></a><br/>
	</td>
	</tr>
	</table>
	<br/>
	</div>
	';
}
// at global stats page
elseif((isset($_GET['topglobal']) AND !empty($_GET['topglobal'])) OR (isset($_GET['globalsearch']) AND !empty($_GET['globalsearch'])))
{
	echo '
	<br/>
	<div class="topcontent">
	<table width="98%" align="center" border="0">
	<tr>
	<td width="90%">
	<table width="100%" border="0">
	<tr>
	<td>
	<br/><a href="' . $_SERVER['PHP_SELF'] . '"><font size="3">Return to ' . $clan_name . ' Stats Index Page</font></a><br/>
	</td>
	</tr>
	<tr>
	<td>
	<a href="' . $_SERVER['PHP_SELF'] . '?topglobal=1"><font class="information">Currently viewing:</font> Global Server Stats</a><br/>
	</td>
	</tr>
	</table>
	</td>
	<td width="10%" style="text-align: right;">
	&nbsp;
	</td>
	</tr>
	</table>
	</div>
	';
}
// already at index page
// display empty content
else
{
	echo '
	<div class="topcontent">
	<table width="98%" align="center" border="0">
	<tr>
	<td width="75%">
	</td>
	<td width="25%" style="float: right;">
	</td>
	</tr>
	</table>
	</div>
	';
}
echo '
<table border="0" width="100%" align="center">
<tr>
<td>
<center>
<table width="100%">
<tr>
<td width="1%">
</td>
<td>
<table width="100%">
<tr>
<td>';
// don't display menu if this is the index page
if(isset($ServerID) AND !is_null($ServerID))
{
	echo '
	<div class="menucontent">
	<table align="center" width="100%" border="0">
	<tr>
	<td width="40%" style="text-align: left">
	<form action="' . $_SERVER['PHP_SELF'] . '" method="get">
	<input type="hidden" name="ServerID" value="' . $ServerID . '" />
	&nbsp; &nbsp; <font class="information">Player:</font>&nbsp;
	';
	// try to fill in search box
	if(isset($SoldierName) AND !empty($SoldierName) AND $SoldierName != 'Not Found')
	{
		echo '<input type="text" class="inputbox" value="' . $SoldierName . '" name="SoldierName" />';
	}
	else
	{
		echo '<input type="text" class="inputbox" name="SoldierName" />';
	}
	echo '
	<input type="submit" name="search" value="Search" title="Search" class="button" />
	</form>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '">Home</a>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;suspicious=1">Suspicious</a>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1">Chat</a>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;countries=1">Countries</a>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;maps=1">Maps</a>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;serverstats=1">Server Info</a>
	</td>
	</tr>
	</table>
	</div>
	';
}
echo '<br/>';
// page content depending on searches
// begin search player logic
if((isset($ServerID) AND !is_null($ServerID)) AND (isset($_GET['search']) AND !empty($_GET['search'])))
{
	// include player.php contents
	require_once('./common/player.php');
}
// begin suspicious players logic
if((isset($ServerID) AND !is_null($ServerID)) AND (isset($_GET['suspicious']) AND !empty($_GET['suspicious'])))
{
	// include suspicious.php contents
	require_once('./common/suspicious.php');
}
// begin top countries logic
if((isset($ServerID) AND !is_null($ServerID)) AND (isset($_GET['countries']) AND !empty($_GET['countries'])))
{
	// include countries.php contents
	require_once('./common/countries.php');
}
// begin map stats logic
if((isset($ServerID) AND !is_null($ServerID)) AND (isset($_GET['maps']) AND !empty($_GET['maps'])))
{
	// include maps.php contents
	require_once('./common/maps.php');
}
// begin server stats logic
if((isset($ServerID) AND !is_null($ServerID)) AND (isset($_GET['serverstats']) AND !empty($_GET['serverstats'])))
{
	// include serverstats.php contents
	require_once('./common/serverstats.php');
}
// begin chat logic
if((isset($ServerID) AND !is_null($ServerID)) AND (isset($_GET['chat']) AND !empty($_GET['chat'])))
{
	// include chat.php contents
	require_once('./common/chat.php');
}
// begin home page logic
if((isset($ServerID) AND !is_null($ServerID)) AND (($_GET['topplayers']) OR !(($_GET['search']) OR ($_GET['suspicious']) OR ($_GET['countries']) OR ($_GET['maps']) OR ($_GET['serverstats']) OR ($_GET['chat']))))
{
	// include home.php contents
	require_once('./common/home.php');
}
// begin index page logic
if(!isset($ServerID) OR is_null($ServerID))
{
	// include index.php contents
	require_once('./common/index.php');
}
echo '
<br/>
<br/>
<div class="middlecontent">
<table width="100%" border="0">
<tr><td>
<br/>
<center>[ <font class="information">Stats provided by <a href="https://forum.myrcon.com/showthread.php?6698-_BF4-PRoCon-Chat-GUID-Stats-and-Mapstats-Logger-1-0-0-1" target="_blank">XpKiller\'s PRoCon logging plugin</a></font> ]  &nbsp; [ <font class="information">Stats page provided by <a href="http://open-web-community.com/" target="_blank">Ty_ger07 at Open-Web-Community</a></font> ]</center>
<br/>
</td></tr>
</table>
</div>
';
// check to see if ses table exists
@mysqli_query($BF4stats,"
	CREATE TABLE IF NOT EXISTS `ses_{$ServerID}_tbl` (`IP` VARCHAR(45) NULL DEFAULT NULL, `timestamp` int(11) NOT NULL default '00000000000', PRIMARY KEY (`IP`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin
");
// get user's IP address
$userip = $_SERVER["REMOTE_ADDR"];
// initialize values
$now_timestamp = time();
$old = $now_timestamp - 1800;
// check if this user already has ses stored
$exist_query = @mysqli_query($BF4stats,"
	SELECT `IP`
	FROM `ses_{$ServerID}_tbl`
	WHERE `IP` = '{$userip}'
");
if(@mysqli_num_rows($exist_query)!=0)
{
	// user IP found, update timestamp
	@mysqli_query($BF4stats,"
		UPDATE `ses_{$ServerID}_tbl`
		SET `timestamp` = {$now_timestamp}
		WHERE `IP` = '{$userip}'
	");
}
else
{
	// user IP not found, add it to session table
	@mysqli_query($BF4stats,"
		INSERT INTO `ses_{$ServerID}_tbl` (`IP`, `timestamp`)
		VALUES ('{$userip}', {$now_timestamp})
	");
}
// free up exist query memory
@mysqli_free_result($exist_query);
// find if there are sessions older than 30 minutes
// do this to avoid optimizing the table (slow) every page load
$old_query = @mysqli_query($BF4stats,"
	SELECT `timestamp`
	FROM `ses_{$ServerID}_tbl`
	WHERE `timestamp` <= {$old}
");
if(@mysqli_num_rows($old_query) != 0)
{
	// remove sessions older than 30 minutes
	@mysqli_query($BF4stats,"
		DELETE FROM `ses_{$ServerID}_tbl`
		WHERE `timestamp` <= {$old}
	");
	@mysqli_query($BF4stats,"
		OPTIMIZE TABLE `ses_{$ServerID}_tbl`
	");
}
// free up old query memory
@mysqli_free_result($old_query);
// count all sessions
$ses_count = @mysqli_query($BF4stats,"
	SELECT count(`IP`) as ses
	FROM `ses_{$ServerID}_tbl`
	WHERE 1
");
if(@mysqli_num_rows($ses_count) != 0)
{
	$ses_row = @mysqli_fetch_assoc($ses_count);
	$ses = $ses_row['ses'];
	echo '<br/><center><font class="footertext">' . $ses . ' users viewing these BF4 stats pages</font></center>';
}
else
{
	echo '<br/><center><font class="footertext">an error occured while counting sessions</font></center>';
}
// free up session count query memory
@mysqli_free_result($ses_count);
// figure out total page load time
$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = round(($endtime - $starttime),2);
// display total page load time
echo '<center><font class="footertext">server computed in ' . $totaltime . ' seconds</font></center>';
// display total server memory used
echo '<center><font class="footertext">' . round(memory_get_usage(false)/1024,0) . ' KB of server memory used</font></center>';
echo '
</td></tr>
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
</td>
</tr>
</table>
</div>
<br/>
</body>
</html>
';
// flush ouput buffers to the client in case it is necessary for this server
// servers should do this automatically
// but it doesn't hurt to do it manaully anyways
flush();
ob_flush();
?>