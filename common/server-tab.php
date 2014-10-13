<?php
// server-tab banner page for server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// first connect to the database
// and include necessary files
include_once('../config/config.php');
include_once('../common/connect.php');
include_once('../common/case.php');

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

echo '
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
