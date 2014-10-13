<?php
// server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// hide php notices
error_reporting(E_ALL ^ E_NOTICE);

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
<meta name="copyright" content="2013 Open-Web-Community http://open-web-community.com/" />
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
	// first look for a SoldierName in URL and try to convert it to PlayerID
	if(!empty($player))
	{
		$SoldierName = $player;
		
		// if there are dangerous characters, null out the input to prevent injection
		if((strpos($SoldierName,'`') !== false) || (strpos($SoldierName,'\'') !== false) || (strpos($SoldierName,'=') !== false))
		{
			$SoldierName = null;
		}
		// or else find this PlayerID
		else
		{
			$PlayerID_q = @mysqli_query($BF4stats,"
				SELECT tpd.`PlayerID`
				FROM  `tbl_playerdata` tpd
				INNER JOIN  `tbl_server_player` tsp ON tpd.`PlayerID` = tsp.`PlayerID`
				WHERE tpd.`SoldierName` = '{$SoldierName}'
				AND tsp.`ServerID` = {$ServerID}
				AND tpd.`GameID` = {$GameID}
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
	if(!empty($pid))
	{
		$PlayerID = $pid;
		
		// search for soldier name using provided player ID
		$SoldierName_q = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`
			FROM `tbl_playerdata` tpd
			INNER JOIN  `tbl_server_player` tsp ON tpd.`PlayerID` = tsp.`PlayerID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
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
// server id is inherited since this is the only server
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
		
		// if there are dangerous characters, null out the input to prevent injection
		if((strpos($SoldierName,'`') !== false) || (strpos($SoldierName,'\'') !== false) || (strpos($SoldierName,'=') !== false))
		{
			$SoldierName = null;
		}
		// or else find this PlayerID
		else
		{
			$PlayerID_q = @mysqli_query($BF4stats,"
				SELECT tpd.`PlayerID`
				FROM  `tbl_playerdata` tpd
				INNER JOIN  `tbl_server_player` tsp ON tpd.`PlayerID` = tsp.`PlayerID`
				WHERE tpd.`SoldierName` = '{$SoldierName}'
				AND tsp.`ServerID` = {$ServerID}
				AND tpd.`GameID` = {$GameID}
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
	if(!empty($pid))
	{
		$PlayerID = $pid;
		
		// search for soldier name using provided player ID
		$SoldierName_q = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`
			FROM `tbl_playerdata` tpd
			INNER JOIN  `tbl_server_player` tsp ON tpd.`PlayerID` = tsp.`PlayerID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
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
	if(!empty($player))
	{
		$SoldierName = $player;
		
		// if there are dangerous characters, null out the input to prevent injection
		if((strpos($SoldierName,'`') !== false) || (strpos($SoldierName,'\'') !== false) || (strpos($SoldierName,'=') !== false))
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
	if(!empty($pid))
	{
		$PlayerID = $pid;
		
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
	
	// search chat on demand
	if($page == 'chat')
	{
		echo '
		<script type="text/javascript">
		function searchOnDemand(str)
		{
			if(str=="")
			{
				document.getElementById("txtDefault").innerHTML=\'<br/><div class="subsection"><div class="headline">Please enter a search query.</div></div>\';
				return;
			}
			if(str.length <= 2 && xmlhttp.readyState == 4)
			{
				document.getElementById("txtDefault").innerHTML=\'<br/><div class="subsection"><div class="headline">Search query must be at least three characters in length.</div></div>\';
				return;
			}
			if(str.length >= 3)
			{
				if (window.XMLHttpRequest)
				{
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp=new XMLHttpRequest();
				}
				else
				{
					// code for IE6, IE5
					xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
				}

				xmlhttp.onreadystatechange=function()
				{
					if (xmlhttp.readyState==4 && xmlhttp.status==200)
					{
						document.getElementById("txtDefault").innerHTML=xmlhttp.responseText;
					}
				}
				xmlhttp.open("GET","./common/chat-search.php?p=chat';
				if(!empty($ServerID))
				{
					echo '&sid=' . $ServerID;
				}
				echo '&gid=' . $GameID . '&q="+str,true);
				xmlhttp.send();
			}
		}
		</script>
		';
	}
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
		function log( message )
		{
			$( "<div>" ).text( message ).prependTo( "#log" );
			$( "#log" ).scrollTop( 0 );
		}

		$( "#soldiers" ).autocomplete(
		{
			source: "./common/player-search.php?';
			if(!empty($ServerID))
			{
				echo 'sid=' . $ServerID . '&';
			}
			echo 'gid=' . $GameID . '",
			minLength: 2,
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
	// jquery auto refresh scoreboard every 20 seconds
	if($page == 'home' && !empty($ServerID))
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
			setInterval(callAjax, 20000 );
		});
		</script>
		';
	}
	// jquery auto refresh chat every 20 seconds
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
			setInterval(callAjax, 20000 );
		});
		</script>
		';
	}
}
// jquery auto refresh index page server list every 20 seconds
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
		setInterval(callAjax, 20000 );
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
<center>[ <span class="information">Stats provided by <a href="https://forum.myrcon.com/showthread.php?6698-_BF4-PRoCon-Chat-GUID-Stats-and-Mapstats-Logger-1-0-0-1" target="_blank">XpKiller\'s PRoCon logging plugin</a></span> ]  &nbsp; [ <span class="information">Stats page provided by <a href="http://tyger07.github.io/BF4-Server-Stats/" target="_blank">Ty_ger07</a></span> ]</center>
</div>
';
// now lets check our stats page sessions
// stats page sessions are used to monitor how many people are viewing these stats pages
// check to see if the session table exists
// build query
$query = 'CREATE TABLE IF NOT EXISTS `ses_';
// if there is a serverID, use it
if(!empty($ServerID))
{
	$query .= $ServerID;
}
else
{
	$query .= 'global';
}
$query .= '_tbl` (`IP` VARCHAR(45) NULL DEFAULT NULL, `timestamp` int(11) NOT NULL default \'00000000000\', PRIMARY KEY (`IP`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin';
// run query
@mysqli_query($BF4stats,$query);
// get user's IP address
$userip = $_SERVER["REMOTE_ADDR"];
// initialize values
$now_timestamp = time();
$old = $now_timestamp - 1800;
// check if this user already has a session stored
// build query
$query = 'SELECT `IP` FROM `ses_';
// if there is a serverID, use it
if(!empty($ServerID))
{
	$query .= $ServerID;
}
else
{
	$query .= 'global';
}
$query .= '_tbl` WHERE `IP` = \'' . $userip . '\'';
// run query
$exist_query = @mysqli_query($BF4stats,$query);
// user IP found, update timestamp
if(@mysqli_num_rows($exist_query) != 0)
{
	// build query
	$query = 'UPDATE `ses_';
	// if there is a serverID, use it
	if(!empty($ServerID))
	{
		$query .= $ServerID;
	}
	else
	{
		$query .= 'global';
	}
	$query .= '_tbl` SET `timestamp` = ' . $now_timestamp . ' WHERE `IP` = ' . $userip;
	// run query
	@mysqli_query($BF4stats,$query);
}
// user IP not found, add it to session table
else
{
	// build query
	$query = 'INSERT INTO `ses_';
	// if there is a serverID, use it
	if(!empty($ServerID))
	{
		$query .= $ServerID;
	}
	else
	{
		$query .= 'global';
	}
	$query .= '_tbl` (`IP`, `timestamp`) VALUES (\'' . $userip . '\', ' . $now_timestamp . ')';
	// run query
	@mysqli_query($BF4stats,$query);
}
// free up exist query memory
@mysqli_free_result($exist_query);
// find if there are sessions older than 30 minutes
// check this to avoid optimizing the table (slow) when it isn't necessary
// build query
$query = 'SELECT `timestamp` FROM `ses_';
// if there is a serverID, use it
if(!empty($ServerID))
{
	$query .= $ServerID;
}
else
{
	$query .= 'global';
}
$query .= '_tbl` WHERE `timestamp` <= ' . $old;
// run query
$old_query = @mysqli_query($BF4stats,$query);
// remove sessions older than 30 minutes
if(@mysqli_num_rows($old_query) != 0)
{
	// build query
	$query = 'DELETE FROM `ses_';
	// if there is a serverID, use it
	if(!empty($ServerID))
	{
		$query .= $ServerID;
	}
	else
	{
		$query .= 'global';
	}
	$query .= '_tbl` WHERE `timestamp` <= ' . $old;
	// run query
	@mysqli_query($BF4stats,$query);
	// optimize this session table
	// build query
	$query = 'OPTIMIZE TABLE `ses_';
	// if there is a serverID, use it
	if(!empty($ServerID))
	{
		$query .= $ServerID;
	}
	else
	{
		$query .= 'global';
	}
	$query .= '_tbl`';
	// run query
	@mysqli_query($BF4stats,$query);
}
// free up old query memory
@mysqli_free_result($old_query);
// count all sessions
// if there is a serverID, use it
if(!empty($ServerID))
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
	echo '<br/><center><span class="footertext">' . $ses . ' users viewing these BF4 stats pages</span></center>';
}
else
{
	echo '<br/><center><span class="footertext">an error occured while counting sessions</span></center>';
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
echo '<center><span class="footertext">server compiled page in ' . $totaltime . ' seconds</span></center>';
// display total server memory used
echo '<center><span class="footertext">' . round(memory_get_usage(false)/1024,0) . ' KB of server memory used</span></center><br/>
</div>
</body>
</html>
';
// flush output buffers to the client in case it is necessary for this server
// servers should do this automatically
// but it doesn't hurt to do it manually anyway
flush();
ob_flush();
?>
