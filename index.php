<?php
// server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// hide php notices
error_reporting(E_ALL ^ E_NOTICE);

// include config.php contents
require_once('./config/config.php');

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

// connect to the stats database
$BF4stats = @mysqli_connect(HOST, USER, PASS, NAME, PORT) or die ("<title>BF4 Player Stats - Error</title></head><body><br/><div id='pagebody'><div id='pagebodyback'><br/><center><b>Unable to access stats database. Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/></div></div></body></html>");
@mysqli_select_db($BF4stats, NAME) or die ("<title>BF4 Player Stats - Error</title></head><body><br/><div id='pagebody'><div id='pagebodyback'><br/><center><b>Unable to select stats database. Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/></div></div></body></html>");

// initialize value as null
$GameID = null;

// first we need to find the GameID
$Server_q = @mysqli_query($BF4stats,"
	SELECT `GameID`
	FROM `tbl_games`
	WHERE `Name` = 'BF4'
");

// the server info was found
if(@mysqli_num_rows($Server_q) == 1)
{
	$Server_r = @mysqli_fetch_assoc($Server_q);
	$GameID = $Server_r['GameID'];
}
// BF4 not found in this database
else
{
	// display error and die
	die ("<title>BF4 Player Stats - Error</title></head><body><br/><div id='pagebody'><div id='pagebodyback'><br/><center><b>The game 'BF4' was not found in this database! Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/></div></div></body></html>");
}

// free up server info query memory
@mysqli_free_result($Server_q);

// find all servers in this database
$ServerID_q = @mysqli_query($BF4stats,"
	SELECT `ServerID`
	FROM `tbl_server`
	WHERE `GameID` = {$GameID}
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
// no BF4 servers were found
else
{
	// display error and die
	die ("<title>BF4 Player Stats - Error</title></head><body><br/><div id='pagebody'><div id='pagebodyback'><br/><center><b>No 'BF4' servers were found in this database! Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/></div></div></body></html>");
}

// free up server id query memory
@mysqli_free_result($ServerID_q);

// initialize values as null
$ServerID = null;
$SoldierName = null;
$PlayerID = null;

// was a server ID given in the URL?  Is it a valid server ID?
// if so, we must not be looking at global stats
if(isset($_GET['ServerID']) AND !empty($_GET['ServerID']) AND is_numeric($_GET['ServerID']) AND in_array($_GET['ServerID'],$ServerIDs))
{
	// assign $ServerID variable
	$ServerID = mysqli_real_escape_string($BF4stats, $_GET['ServerID']);
	
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
	
	// free up server info query memory
	@mysqli_free_result($Server_q);
	
	// lets see if a SoldierName or PlayerID was provided to us in the URL
	// we will try to find this player in this server and convert everything to PlayerID
	// first look for a SoldierName in URL and try to convert it to PlayerID
	if(isset($_GET['SoldierName']) AND !empty($_GET['SoldierName']))
	{
		// remove spaces from name input
		$SoldierName = mysqli_real_escape_string($BF4stats, preg_replace('/\s/','',($_GET['SoldierName'])));
		
		// if there are dangerous characters, just stop
		if((strpos($SoldierName,'`') !== false) OR (strpos($SoldierName,'\'') !== false) OR (strpos($SoldierName,'=') !== false))
		{
			$SoldierName = null;
		}
		
		// or else find this PlayerID
		else
		{
			$PlayerID_q = @mysqli_query($BF4stats,"
				SELECT `PlayerID`
				FROM `tbl_playerdata`
				WHERE `SoldierName` = '{$SoldierName}'
				AND `GameID` = {$GameID}
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
	}
	
	// then look for PlayerID in URL and make sure it is valid
	if(isset($_GET['PlayerID']) AND !empty($_GET['PlayerID']) AND is_numeric($_GET['PlayerID']))
	{
		$PlayerID = mysqli_real_escape_string($BF4stats, $_GET['PlayerID']);
		
		// search for soldier name using provided player ID
		$SoldierName_q = @mysqli_query($BF4stats,"
			SELECT `SoldierName`
			FROM `tbl_playerdata`
			WHERE `PlayerID` = {$PlayerID}
			AND `GameID` = {$GameID}
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
// this must be a global stats page
else
{
	// lets see if a SoldierName or PlayerID was provided to us in the URL
	// first look for a SoldierName in URL and try to convert it to PlayerID
	if(isset($_GET['SoldierName']) AND !empty($_GET['SoldierName']))
	{
		// remove spaces from name input
		$SoldierName = mysqli_real_escape_string($BF4stats, preg_replace('/\s/','',($_GET['SoldierName'])));
		
		// if there are dangerous characters, just stop
		if((strpos($SoldierName,'`') !== false) OR (strpos($SoldierName,'\'') !== false) OR (strpos($SoldierName,'=') !== false))
		{
			$SoldierName = null;
		}
		
		// or else find this PlayerID
		else
		{
			$PlayerID_q = @mysqli_query($BF4stats,"
				SELECT `PlayerID`
				FROM `tbl_playerdata`
				WHERE `SoldierName` = '{$SoldierName}'
				AND `GameID` = {$GameID}
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
	}
	
	// then look for PlayerID in URL and make sure it is valid
	if(isset($_GET['PlayerID']) AND !empty($_GET['PlayerID']) AND is_numeric($_GET['PlayerID']))
	{
		$PlayerID = mysqli_real_escape_string($BF4stats, $_GET['PlayerID']);
		
		// search for soldier name using provided player ID
		$SoldierName_q = @mysqli_query($BF4stats,"
			SELECT `SoldierName`
			FROM `tbl_playerdata`
			WHERE `PlayerID` = {$PlayerID}
			AND `GameID` = {$GameID}
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

// this is not a global stats page
if(isset($_GET['ServerID']) AND !empty($_GET['ServerID']) AND is_numeric($_GET['ServerID']) AND in_array($_GET['ServerID'],$ServerIDs))
{
	// change page title, meta description, and keywords depending on the page content
	if(isset($_GET['search']) AND !empty($_GET['search']))
	{
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
		';
	}
	elseif(isset($_GET['chat']) AND !empty($_GET['chat']))
	{
		echo '
		<meta name="keywords" content="Chat,' . $ServerName . ',' . $clan_name . ',BF4,Player,Recent,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server Chat Content page." />
		<title>' . $clan_name . ' BF4 Player Stats - Recent Chat - ' . $ServerName . '</title>
		<meta http-equiv="refresh" content="60" />
		';
	}
	elseif(isset($_GET['potw']) AND !empty($_GET['potw']))
	{
		echo '
		<meta name="keywords" content="Players of the Week,' . $ServerName . ',' . $clan_name . ',BF4,Player,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server Players of the Week page." />
		<title>' . $clan_name . ' BF4 Player Stats - Players of the Week - ' . $ServerName . '</title>
		';
	}
	else
	{
		echo '
		<meta name="keywords" content="Home,Top,Players,' . $ServerName . ',' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 ' . $ServerName . ' server main player stats page." />
		<title>' . $clan_name . ' BF4 Player Stats - Home Page - ' . $ServerName . '</title>
		<meta http-equiv="refresh" content="60" />
		';
	}
}
// no server ID was given in the URL or an invalid server ID was given, so use index page header or global stats page header
else
{
	// use main global stats header if selected
	if(isset($_GET['globalhome']) AND !empty($_GET['globalhome']))
	{
		echo '
		<meta name="keywords" content="Home,Top,Players,Global,' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 global top players stats page." />
		<title>' . $clan_name . ' BF4 GLobal Player Stats - Home Page</title>
		';
	}
	// or else at global player stats page
	elseif(isset($_GET['globalsearch']) AND !empty($_GET['globalsearch']))
	{
		echo '
		<meta name="keywords" content="' . $SoldierName . ',' . $clan_name . ',BF4,Global,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 global server player stats page for ' . $SoldierName . '." />
		<title>' . $clan_name . ' BF4 Global Player Stats - ' . $SoldierName . '</title>
		';
	}
	// or else at global suspicious page
	elseif(isset($_GET['globalsuspicious']) AND !empty($_GET['globalsuspicious']))
	{
		echo '
		<meta name="keywords" content="Global,Suspicious,Players,' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 global server Suspicious Players page." />
		<title>' . $clan_name . ' BF4 Global Player Stats - Suspicious Players</title>
		';
	}
	// or else at global countries page
	elseif(isset($_GET['globalcountries']) AND !empty($_GET['globalcountries']))
	{
		echo '
		<meta name="keywords" content="Global,Country,' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 global server Country Stats page." />
		<title>' . $clan_name . ' BF4 Global Player Stats - Country Stats</title>
		';
	}
	// or else at global maps page
	elseif(isset($_GET['globalmaps']) AND !empty($_GET['globalmaps']))
	{
		echo '
		<meta name="keywords" content="Global,Map,' . $clan_name . ',BF4,Player,Stats,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 global server Map Stats page." />
		<title>' . $clan_name . ' BF4 Global Player Stats - Map Stats</title>
		';
	}
	// or else at global server stats page
	elseif(isset($_GET['globalserverstats']) AND !empty($_GET['globalserverstats']))
	{
		echo '
		<meta name="keywords" content="Server,Global,' . $clan_name . ',BF4,Player,Stats,Info" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 global server Info page." />
		<title>' . $clan_name . ' BF4 Global Player Stats - Server Info</title>
		';
	}
	elseif(isset($_GET['globalpotw']) AND !empty($_GET['globalpotw']))
	{
		echo '
		<meta name="keywords" content="Players of the Week,Global,' . $clan_name . ',BF4,Player,Server" />
		<meta name="description" content="This is our ' . $clan_name . ' BF4 global server Players of the Week page." />
		<title>' . $clan_name . ' BF4 Global Player Stats - Players of the Week</title>
		';
	}
	// or else use the server stats index page header
	else
	{
		echo '
		<meta name="keywords" content="BF4,Player,Stats,Server,Index,' . $clan_name . '" />
		<meta name="description" content="This is the ' . $clan_name . ' BF4 player stats server index page." />
		<title>' . $clan_name . ' BF4 Player Stats - Index Page</title>
		';
	}
}
echo '
</head>
<body>
<br/>
<div id="pagebody">
<div id="pagebodyback">
<br/>
<table width="100%" cellspacing="1">
<tr> 
<td>
<div>
<div class="topcontent">
<center><a href="' . $banner_url . '" target="_blank"><img alt="BF4 Stats Page Copyright 2013 Open-Web-Community" border="0" src="' . $banner_image . '" /></a></center>
</div>
';
// $ServerID is provided, so we are at a server page
// display server index link
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
	<br/><a href="' . $battlelog . '" target="_blank"><img src="./images/joinbtn.png" alt="join" class="imagebutton"/></a><br/>
	</td>
	</tr>
	</table>
	<br/>
	</div>
	';
}
// $ServerID was not provided, so we are at a global stats page
// display global index link
elseif((!isset($ServerID) OR is_null($ServerID)) AND ((isset($_GET['globalhome']) AND !empty($_GET['globalhome'])) OR (isset($_GET['globalsearch']) AND !empty($_GET['globalsearch'])) OR (isset($_GET['globalsuspicious']) AND !empty($_GET['globalsuspicious'])) OR (isset($_GET['globalcountries']) AND !empty($_GET['globalcountries'])) OR (isset($_GET['globalmaps']) AND !empty($_GET['globalmaps'])) OR (isset($_GET['globalserverstats']) AND !empty($_GET['globalserverstats'])) OR (isset($_GET['globalpotw']) AND !empty($_GET['globalpotw']))))
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
	<a href="' . $_SERVER['PHP_SELF'] . '?globalhome=1"><font class="information">Currently viewing:</font> ' . $clan_name . '\'s Global Server Stats</a><br/>
	</td>
	</tr>
	</table>
	</td>
	<td width="10%" style="text-align: right;">
	&nbsp;
	</td>
	</tr>
	</table>
	<br/>
	</div>
	';
}
// already at index page
// no need to display index link
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
// if this is a server stats page, display server stats page menu
if(isset($ServerID) AND !is_null($ServerID))
{
	echo '
	<div class="menucontent">
	<table align="center" width="100%" border="0">
	<tr>
	<td width="25%" style="text-align: left">
	<form action="' . $_SERVER['PHP_SELF'] . '" method="get">
	<input type="hidden" name="ServerID" value="' . $ServerID . '" />
	&nbsp; <font class="information">Player:</font>
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
	<td width="15%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;potw=1">Players of Week</a>
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
// if this is a global stats page, display global stats page menu
elseif((isset($_GET['globalhome']) AND !empty($_GET['globalhome'])) OR (isset($_GET['globalsearch']) AND !empty($_GET['globalsearch'])) OR (isset($_GET['globalsuspicious']) AND !empty($_GET['globalsuspicious'])) OR (isset($_GET['globalcountries']) AND !empty($_GET['globalcountries'])) OR (isset($_GET['globalmaps']) AND !empty($_GET['globalmaps'])) OR (isset($_GET['globalserverstats']) AND !empty($_GET['globalserverstats'])) OR (isset($_GET['globalpotw']) AND !empty($_GET['globalpotw'])))
{
	echo '
	<div class="menucontent">
	<table align="center" width="100%" border="0">
	<tr>
	<td width="35%" style="text-align: left">
	<form action="' . $_SERVER['PHP_SELF'] . '" method="get">
	<input type="hidden" name="globalsearch" value="1" />
	&nbsp; <font class="information">Player:</font>
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
	<a href="' . $_SERVER['PHP_SELF'] . '?globalhome=1">Home</a>
	</td>
	<td width="15%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?globalpotw=1">Players of Week</a>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?globalsuspicious=1">Suspicious</a>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?globalcountries=1">Countries</a>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?globalmaps=1">Maps</a>
	</td>
	<td width="10%" style="text-align: center">
	<a href="' . $_SERVER['PHP_SELF'] . '?globalserverstats=1">Server Info</a>
	</td>
	</tr>
	</table>
	</div>
	';
}
echo '<br/>';
// lets  do the server stats page logic first
if(isset($ServerID) AND !is_null($ServerID))
{
	// page content depending on searches
	// begin search player logic
	if(isset($_GET['search']) AND !empty($_GET['search']))
	{
		// include player.php contents
		require_once('./common/player.php');
	}
	// begin suspicious players logic
	if(isset($_GET['suspicious']) AND !empty($_GET['suspicious']))
	{
		// include suspicious.php contents
		require_once('./common/suspicious.php');
	}
	// begin top countries logic
	if(isset($_GET['countries']) AND !empty($_GET['countries']))
	{
		// include countries.php contents
		require_once('./common/countries.php');
	}
	// begin map stats logic
	if(isset($_GET['maps']) AND !empty($_GET['maps']))
	{
		// include maps.php contents
		require_once('./common/maps.php');
	}
	// begin server stats logic
	if(isset($_GET['serverstats']) AND !empty($_GET['serverstats']))
	{
		// include serverstats.php contents
		require_once('./common/serverstats.php');
	}
	// begin chat logic
	if(isset($_GET['chat']) AND !empty($_GET['chat']))
	{
		// include chat.php contents
		require_once('./common/chat.php');
	}
	// begin potw logic
	if(isset($_GET['potw']) AND !empty($_GET['potw']))
	{
		// include potw.php contents
		require_once('./common/potw.php');
	}
	// begin home page logic
	if(($_GET['topplayers']) OR !(($_GET['search']) OR ($_GET['suspicious']) OR ($_GET['countries']) OR ($_GET['maps']) OR ($_GET['serverstats']) OR ($_GET['chat']) OR ($_GET['potw'])))
	{
		// include home.php contents
		require_once('./common/home.php');
	}
}
// begin index page logic
if(!isset($ServerID) OR is_null($ServerID))
{
	// include index.php (the one in the common folder) contents
	require_once('./common/index.php');
}
echo '
<br/>
<br/>
<div class="middlecontent">
<table width="100%" border="0">
<tr><td>
<br/>
<center>[ <font class="information">Stats provided by <a href="https://forum.myrcon.com/showthread.php?6698-_BF4-PRoCon-Chat-GUID-Stats-and-Mapstats-Logger-1-0-0-1" target="_blank">XpKiller\'s PRoCon logging plugin</a></font> ]  &nbsp; [ <font class="information">Stats page provided by <a href="http://tyger07.github.io/BF4-Server-Stats/" target="_blank">Ty_ger07</a></font> ]</center>
<br/>
</td></tr>
</table>
</div>
';
// now lets check our stats page sessions
// stats page sessions are used to monitor how many people are viewing these stats pages
// check to see if the session table exists
// if there is a serverID, use it
if(isset($ServerID) AND !is_null($ServerID))
{
	@mysqli_query($BF4stats,"
		CREATE TABLE IF NOT EXISTS `ses_{$ServerID}_tbl` (`IP` VARCHAR(45) NULL DEFAULT NULL, `timestamp` int(11) NOT NULL default '00000000000', PRIMARY KEY (`IP`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin
	");
}
// otherwise we are global with no id
else
{
	@mysqli_query($BF4stats,"
		CREATE TABLE IF NOT EXISTS `ses_global_tbl` (`IP` VARCHAR(45) NULL DEFAULT NULL, `timestamp` int(11) NOT NULL default '00000000000', PRIMARY KEY (`IP`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin
	");
}
// get user's IP address
$userip = $_SERVER["REMOTE_ADDR"];
// initialize values
$now_timestamp = time();
$old = $now_timestamp - 1800;
// check if this user already has a session stored
// if there is a serverID, use it
if(isset($ServerID) AND !is_null($ServerID))
{
	$exist_query = @mysqli_query($BF4stats,"
		SELECT `IP`
		FROM `ses_{$ServerID}_tbl`
		WHERE `IP` = '{$userip}'
	");
}
// otherwise we are global with no id
else
{
	$exist_query = @mysqli_query($BF4stats,"
		SELECT `IP`
		FROM `ses_global_tbl`
		WHERE `IP` = '{$userip}'
	");
}
// user IP found, update timestamp
if(@mysqli_num_rows($exist_query)!=0)
{
	// if there is a serverID, use it
	if(isset($ServerID) AND !is_null($ServerID))
	{
		@mysqli_query($BF4stats,"
			UPDATE `ses_{$ServerID}_tbl`
			SET `timestamp` = {$now_timestamp}
			WHERE `IP` = '{$userip}'
		");
	}
	// otherwise we are global with no id
	else
	{
		@mysqli_query($BF4stats,"
			UPDATE `ses_global_tbl`
			SET `timestamp` = {$now_timestamp}
			WHERE `IP` = '{$userip}'
		");
	}
}
// user IP not found, add it to session table
else
{
	// if there is a serverID, use it
	if(isset($ServerID) AND !is_null($ServerID))
	{
		@mysqli_query($BF4stats,"
			INSERT INTO `ses_{$ServerID}_tbl` (`IP`, `timestamp`)
			VALUES ('{$userip}', {$now_timestamp})
		");
	}
	// otherwise we are global with no id
	else
	{
		@mysqli_query($BF4stats,"
			INSERT INTO `ses_global_tbl` (`IP`, `timestamp`)
			VALUES ('{$userip}', {$now_timestamp})
		");
	}
}
// free up exist query memory
@mysqli_free_result($exist_query);
// find if there are sessions older than 30 minutes
// check this to avoid optimizing the table (slow) when it isn't necessary
// if there is a serverID, use it
if(isset($ServerID) AND !is_null($ServerID))
{
	$old_query = @mysqli_query($BF4stats,"
		SELECT `timestamp`
		FROM `ses_{$ServerID}_tbl`
		WHERE `timestamp` <= {$old}
	");
}
// otherwise we are global with no ID
else
{
	$old_query = @mysqli_query($BF4stats,"
		SELECT `timestamp`
		FROM `ses_global_tbl`
		WHERE `timestamp` <= {$old}
	");
}
// remove sessions older than 30 minutes
if(@mysqli_num_rows($old_query) != 0)
{
	// if there is a serverID, use it
	if(isset($ServerID) AND !is_null($ServerID))
	{
		@mysqli_query($BF4stats,"
			DELETE FROM `ses_{$ServerID}_tbl`
			WHERE `timestamp` <= {$old}
		");
		// optimize this session table
		@mysqli_query($BF4stats,"
			OPTIMIZE TABLE `ses_{$ServerID}_tbl`
		");
	}
	// otherwise we are global with no id
	else
	{
		@mysqli_query($BF4stats,"
			DELETE FROM `ses_global_tbl`
			WHERE `timestamp` <= {$old}
		");
		// optimize this session table
		@mysqli_query($BF4stats,"
			OPTIMIZE TABLE `ses_global_tbl`
		");
	}
}
// free up old query memory
@mysqli_free_result($old_query);
// count all sessions
// if there is a serverID, use it
if(isset($ServerID) AND !is_null($ServerID))
{
	$ses_count = @mysqli_query($BF4stats,"
		SELECT count(`IP`) AS ses
		FROM `ses_{$ServerID}_tbl`
		WHERE 1
	");
}
// otherwise we are global with no ide
else
{
	$ses_count = @mysqli_query($BF4stats,"
		SELECT count(`IP`) AS ses
		FROM `ses_global_tbl`
		WHERE 1
	");
}
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
$totaltime = round(($endtime - $starttime),3);
// display total page load time
echo '<center><font class="footertext">server compiled page in ' . $totaltime . ' seconds</font></center>';
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