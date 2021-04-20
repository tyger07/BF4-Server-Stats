<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

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
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Cache-Control" content="max-age=604800" />
<meta name="copyright" content="2021 Ty_ger07 http://tyger07.github.io/BF4-Server-Stats/" />
<link rel="icon" type="image/png" href="./favicon.ico" />
<link rel="stylesheet" href="./common/stats.css" type="text/css" />
<link rel="stylesheet" href="./common/javascript/jquery-ui.css" />
<script type="text/javascript" src="./common/javascript/jquery-1.10.2.js"></script>
<script type="text/javascript" src="./common/javascript/jquery-ui.js"></script>
<script type="text/javascript">
$(window).scroll(function()
{
	$("#menucontent").css("top",Math.max(0,142-$(this).scrollTop()));
});
</script>
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
// block Internet Explorer version 7 and lower due to compatibility issues with JavaScript and HTML5
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
	require_once('./common/init.php');
	// change page title, meta description, and keywords depending on the URL
	require_once('./common/common/meta.php');
	echo '
	</head>
	<body>
	<div class="body-grid"></div>
	<div class="content-gradient"></div>
	<div id="topcontent">
	<div id="topbanner">
	<a href="' . $banner_url . '" target="_blank"><img class="banner" src="' . $banner_image . '" alt="BF4 Stats Page 2021 Ty_ger07" border="0" /></a>
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
			// include countries.php contents through wrapper
			require_once('./common/countries/wrapper.php');
		}
		elseif($page == 'maps')
		{
			// include maps.php contents through wrapper
			require_once('./common/maps/wrapper.php');
		}
		elseif($page == 'server')
		{
			// include serverstats.php contents through wrapper
			require_once('./common/server/wrapper.php');
		}
		elseif($page == 'chat')
		{
			// include chat.php contents through wrapper
			require_once('./common/chat/wrapper.php');
		}
		elseif($page == 'leaders')
		{
			// include leaders.php contents through wrapper
			require_once('./common/leaders/wrapper.php');
		}
		elseif($page == 'bans')
		{
			if(!($isbot) && $adkats_available)
			{
				// include bans.php contents through wrapper
				require_once('./common/bans/wrapper.php');
			}
			else
			{
				$page = 'home';
			}
		}
		elseif($page == 'home')
		{
			// include home.php contents through wrapper
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
	echo base64_decode('
		CTxici8+Cgk8YnIvPgoJPGRpdiBjbGFzcz0ic3Vic2VjdGlvbiI+Cgk8ZGl2IGNsYXNzPSJoZWFkbGluZSIgc3R5bGU9ImZvbnQtc2l6ZTogMTJweDsiPlsgPHNwYW4gY2xhc3M9ImluZm9ybWF0aW9uIj5T
		dGF0aXN0aWNzIGRhdGEgcHJvdmlkZWQgYnkgPGEgaHJlZj0iaHR0cHM6Ly9teXJjb24ubmV0L3RvcGljLzE2Mi1jaGF0LWd1aWQtc3RhdHMtYW5kLW1hcHN0YXRzLWxvZ2dlci0xMDAzLyIgdGFyZ2V0PSJf
		YmxhbmsiPlhwS2lsbGVyJ3MgUHJvY29uIFN0YXRzIExvZ2dpbmcgUGx1Z2luPC9hPjwvc3Bhbj4gXSAgJm5ic3A7IFsgPHNwYW4gY2xhc3M9ImluZm9ybWF0aW9uIj5TdGF0cyB3ZWJwYWdlIHByb3ZpZGVk
		IGJ5IDxhIGhyZWY9Imh0dHA6Ly90eWdlcjA3LmdpdGh1Yi5pby9CRjQtU2VydmVyLVN0YXRzLyIgdGFyZ2V0PSJfYmxhbmsiPlR5X2dlcjA3PC9hPjwvc3Bhbj4gXTwvZGl2PgoJPC9kaXY+Cgk8YnIvPg==
	');
	// check if there are any updates to this stats page code
	$xmlData = @file_get_contents('https://github.com/tyger07/BF4-Server-Stats/releases.atom');
	$xml=@simplexml_load_string($xmlData);
	$releaseVersion = $xml->entry[0]->id;
	if((!empty($releaseVersion)) && (stripos($releaseVersion, '4-20-21') === false))
	{
		echo '
		<div class="subsection">
		<div class="headline" style="font-size: 12px;">[ <span class="information"><a href="https://github.com/tyger07/BF4-Server-Stats/releases/latest" target="_blank">An update is available</a></span> ]</div>
		</div>
		<br/>
		';
	}
	echo '
	<center>
	';
	if($ses > 1)
	{
		echo '<span class="footertext">' . $ses . ' users viewing these BF4 stats pages</span>';
	}
	else
	{
		echo '<span class="footertext">' . $ses . ' user viewing these BF4 stats pages</span>';
	}
	echo '
	</center>
	';
	// display denied bot stats
	// count number of bots recorded
	$TotalBots_q = @mysqli_query($BF4stats,"
		SELECT SUM(`count`) AS count
		FROM `tyger_stats_denied`
		WHERE `category` = 'bots'
		GROUP BY `category`
	");
	// previous bot access history exists
	if(@mysqli_num_rows($TotalBots_q) != 0)
	{
		$TotalBots_r = @mysqli_fetch_assoc($TotalBots_q);
		$TotalBots = $TotalBots_r['count'];
		// display bot stats
		echo '
		<center>
		';
		if($TotalBots > 1)
		{
			echo '<span class="footertext">' . $TotalBots . ' bots have been restricted access</span>';
		}
		else
		{
			echo '<span class="footertext">' . $TotalBots . ' bot has been restricted access</span>';
		}
		echo '
		</center>
		';
	}
	// display denied browser stats
	// check to see if denied table exists
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
		';
		if($TotalDenied > 1)
		{
			echo '<span class="footertext">' . $TotalDenied . ' archaic browsers have been blocked</span>';
		}
		else
		{
			echo '<span class="footertext">' . $TotalDenied . ' archaic browser has been blocked</span>';
		}
		echo '
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
		(
			`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
			`category` VARCHAR(20) NOT NULL,
			`count` INT(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`ID`),
			UNIQUE `UNIQUE_DeniedData` (`category`),
			INDEX `category` (`category` ASC)
		)
		ENGINE=InnoDB
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
?>
