<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
require_once('../constants.php');

// we will need a server ID from the URL query string!
if(!empty($sid) && in_array($sid,$ServerIDs))
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
	<meta name="copyright" content="2021 Ty_ger07 http://tyger07.github.io/BF4-Server-Stats/" />
	<meta name="keywords" content="banner" />
	<meta name="description" content="banner" />
	<script type="text/javascript" src="' . $host . $dir . '/javascript/jquery-1.10.2.js"></script>
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
	<div id="fadein" style="position: absolute; top: 84px; left: 10px; display: none; background-color: #' . $bgcolor . '">
	<center>Updating ...<span style="float:right;"><img class="update" src="../images/loading.gif" alt="loading" /></span></center>
	</div>
	<script type="text/javascript">
		$(function() {
			function callAjax(){
				$(\'#content\').load("' . $host . $dir . '/server-banner/html-banner-content.php?sid=' . $ServerID . '");
			}
			setInterval(callAjax, 30000 );
		});
	</script>
	<div id="content">
		<br/><br/>
		<center><img class="load" src="../images/loading.gif" alt="loading" /></center>
		<script type="text/javascript">
			$(\'#content\').load("' . $host . $dir . '/server-banner/html-banner-content.php?sid=' . $ServerID . '");
		</script>
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
	<meta name="copyright" content="2021 Ty_ger07 http://tyger07.github.io/BF4-Server-Stats/" />
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
	A valid ServerID was not provided!
	</div>
	<br/>
	You must provide a valid ServerID.
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
