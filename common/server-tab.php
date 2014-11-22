<?php
// server-tab banner page for server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// first connect to the database
// and include necessary files
require_once('../config/config.php');
require_once('../common/connect.php');
require_once('../common/case.php');

// default variable to null
$ServerID = null;

// find current URL info
$host = 'http://' . $_SERVER['HTTP_HOST'];
$dir = dirname($_SERVER['PHP_SELF']);
$file = $_SERVER['PHP_SELF'];

// get values
if(!empty($sid))
{
	$ServerID = $sid;
}

// find current URL info
$host = 'http://' . $_SERVER['HTTP_HOST'];
$dir = dirname($_SERVER['PHP_SELF']);
$dir = preg_replace('/common/', 'banner', $dir);
$home = preg_replace('/banner/', '', $dir);

echo '
<table class="prettytable">
<tr>
<td class="tablecontents" width="240px">
<br/>
<center><a href="' . $host . $home . 'index.php?sid=' . $ServerID . '" target="_blank"><img src="./banner/banner.png?sid=' . $ServerID . '" alt="banner"/></a></center>
<br/>
<div class="headline" style="width: 550px; text-align: left;">BBcode</div>
<table class="prettytable" style="width: 550px;">
<tr>
<td class="tablecontents">
<span style="font-size: 10px;">[URL=' . $host . $home . 'index.php?sid=' . $ServerID . '][IMG]' . $host . $dir . '/banner.png?sid=' . $ServerID . '[/IMG][/URL]</span>
</td>
</tr>
</table>
<br/>
</td>
</tr>
</table>
<table class="prettytable">
<tr>
<td class="tablecontents" width="240px">
<br/>
<center><iframe src="./common/serverbanner.php?sid=' . $ServerID . '" width="220px" height="712px" frameborder="0" scrolling="no"><p>iframes not supported</p></iframe></center>
<br/>
</td>
<td class="tablecontents" valign="top" style="padding-right: 4px;">
<br/>
<br/>
<div class="headline" style="text-align: left;">HTML Embed</div>
<table class="prettytable">
<tr>
<td class="tablecontents" style="padding-left: 10px;">
<span style="font-size: 12px;">&lt;iframe src="' . $host . $dir . '/serverbanner.php?sid=' . $ServerID . '" width="220px" height="712px" frameborder="0" scrolling="no"&gt;&lt;p&gt;iframes not supported&lt;/p&gt;&lt;/iframe&gt;</span>
</td>
</tr>
</table>
<br/>
<br/>
<br/>
<div class="headline" style="text-align: left;">Advanced URL Query String Help</div>
<table class="prettytable">
<tr>
<td class="tablecontents" style="padding-left: 10px;">
<span style="font-size: 12px;"><b>?sid=</b> : (required) server ID of this server (default: null)</span>
</td>
</tr>
<tr>
<td class="tablecontents" style="padding-left: 10px;">
<span style="font-size: 12px;"><b>&amp;bgcolor=</b> : (optional) 6-digit hexadecimal background color (default: 1D2023)</span>
</td>
</tr>
<tr>
<td class="tablecontents" style="padding-left: 10px;">
<span style="font-size: 12px;"><b>&amp;fontcolor=</b> : (optional) 6-digit hexadecimal font color (default: BBBBBB)</span>
</td>
</tr>
<tr>
<td class="tablecontents" style="padding-left: 10px;">
<span style="font-size: 12px;"><b>&amp;linkcolor=</b> : (optional) 6-digit hexadecimal link color (default: 439BC8)</span>
</td>
</tr>
<tr>
<td class="tablecontents" style="padding-left: 10px;">
<span style="font-size: 12px;"><b>&amp;sectionbgcolor=</b> : (optional) 6-digit hexadecimal background color of headline sections (default: 0A0C0F)</span>
</td>
</tr>
<tr>
<td class="tablecontents" style="padding-left: 10px;">
<span style="font-size: 12px;"><b>&amp;sectionfontcolor=</b> : (optional) 6-digit hexadecimal font color of headline sections (default: AAAAAA)</span>
</td>
</tr>
<tr>
<td class="tablecontents" style="padding-left: 10px;">
<span style="font-size: 12px;"><b>&amp;onlinecount=</b> : (optional) number of players to show in the online players list (default: 10)</span>
</td>
</tr>
<tr>
<td>
&nbsp;
</td>
</tr>
<tr>
<td class="tablecontents" style="padding-left: 10px;">
<span style="font-size: 12px;"><b>Hint</b>: At the bottom of the html output from serverbanner.php, it will give you the suggested iframe width and height values.</span>
</span>
</td>
</tr>
</table>
<br/>
</td>
</tr>
</table>
';
?>
