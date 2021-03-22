<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../constants.php');
require_once('../case.php');
require_once('../functions.php');
// default variable to null
$ServerID = null;
// get values
if(!empty($sid))
{
	$ServerID = $sid;
}
// javascript transition wrapper between loading and loaded
echo '
<script type="text/javascript">
$(\'#loading\').hide(0);
$(\'#loaded\').fadeIn("slow");
</script>
';
// updating text...
// hidden by default until time is reached
echo '
<div id="fadein" style="position: absolute; top: 10px; left: -150px; display: none;">
<div class="subsection" style="width: 100px;">
<center>Updating ...<span style="float:right;"><img class="update" src="./common/images/loading.gif" alt="loading" /></span></center>
</div>
</div>
';
// last updated text...
// shown by default until faded away
echo '
<div id="fadeaway" style="position: absolute; top: 10px; left: -150px;">
<div class="subsection" style="width: 100px;">
<center>Updated <span id="timestamp"></span></center>
</div>
</div>
';
// find out client's current time with javascript
// and fadeaway javascript
// and fadein javascript
echo '
<script type="text/javascript">
var date = new Date();
var hours = date.getHours();
var minutes = date.getMinutes();
if (hours.toString().length == 1)
{
	hours = "0" + hours;
}
if (minutes.toString().length == 1)
{
	minutes = "0" + minutes;
}
document.getElementById("timestamp").innerHTML = hours + \':\' + minutes;
$("#fadeaway").finish().show().delay(2000).fadeOut("slow");
$("#fadein").delay(29000).fadeIn("slow");
</script>
';
// put in table to fix error in firefox
echo '
<table>
<tr>
<td>
';
// go through each detected server ID
foreach($ServerIDs as $this_ServerID)
{
	$Basic_q = @mysqli_query($BF4stats,"
		SELECT ts.`mapName`, ts.`Gamemode`, ts.`maxSlots`, ts.`usedSlots`, ts.`ServerName`, tss.`CountPlayers`
		FROM `tbl_server` ts
		LEFT JOIN `tbl_server_stats` tss ON tss.`ServerID` = ts.`ServerID`
		WHERE ts.`ServerID` = {$this_ServerID}
		AND ts.`GameID` = {$GameID}
	");
	// information was found
	if(@mysqli_num_rows($Basic_q) != 0)
	{
		$Basic_r = @mysqli_fetch_assoc($Basic_q);
		$used_slots = $Basic_r['usedSlots'];
		$available_slots = $Basic_r['maxSlots'];
		$players = $Basic_r['CountPlayers'];
		// player count found for this server
		if(is_null($players))
		{
			$players = 0;
		}
		$name = $Basic_r['ServerName'];
		$mode = $Basic_r['Gamemode'];
		// convert mode to friendly name
		if(in_array($mode,$mode_array))
		{
			$mode_name = array_search($mode,$mode_array);
		}
		// this mode is missing!
		else
		{
			$mode_name = $mode;
		}
		$map = $Basic_r['mapName'];
		// convert map to friendly name
		// first find if this map name is even in the map array
		if(in_array($map,$map_array))
		{
			$map_name = array_search($map,$map_array);
			$map_img = './common/images/maps/' . $map . '.png';
		}
		// this map is missing!
		else
		{
			$map_name = $map;
			$map_img = './common/images/maps/missing.png';
		}
		echo '
		<div style="margin-bottom: 4px; position: relative;">
		<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;"><a class="fill-div" style="padding: 0px; margin: 0px;" href="./index.php?p=home&amp;sid=' . $this_ServerID . '"></a></div>
		<table style="margin-top: -4px;">
		<tr>
		<td class="subsection" style="width: 57px;">
		<img src="' . $map_img . '" style="height: 32px; width: 57px;" alt="map image" />
		</td>
		<td class="subsection" style="width: 57px;">
		<div class="headline" style="text-align: center; font-size: 12px;">Online</div>
		<div style="text-align: center; font-size: 12px;">' . $used_slots . ' / ' . $available_slots . '</div>
		</td>
		<td class="subsection" style="width: 70px;">
		<div class="headline" style="text-align: center; font-size: 12px;">Players</div>
		<div style="text-align: center; font-size: 12px;">' . $players . '</div>
		</td>
		<td class="subsection">
		<div class="headline" style="text-align: left; padding: 0px; padding-left: 3px;">
		' . $name . '
		</div>
		<div style="font-size: 12px; padding-left: 4px;">
		' . $mode_name . ' &bull; ' . $map_name . '
		</div>
		</td>
		</tr>
		</table>
		</div>
		';
	}
	// an error occured
	// display blank information
	else
	{
		echo '
		<div style="margin-bottom: 4px; position: relative;">
		<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;"><a class="fill-div" style="padding: 0px; margin: 0px;" href="./index.php?p=home&amp;sid=' . $this_ServerID . '"></a></div>
		<table style="margin-top: -4px;">
		<tr>
		<td class="subsection" style="width: 57px;">
		<img src="./common/images/maps/missing.png" style="height: 32px; width: 57px;" alt="map image" />
		</td>
		<td class="subsection" style="width: 57px;">
		<div class="headline" style="text-align: center; font-size: 12px;">Players</div>
		<div style="text-align: center; font-size: 12px;">error</div>
		</td>
		<td class="subsection" style="width: 70px;">
		<div class="headline" style="text-align: center; font-size: 12px;">Players</div>
		<div style="text-align: center; font-size: 12px;">error</div>
		</td>
		<td class="subsection">
		<div class="headline" style="text-align: left; padding: 0px; padding-left: 3px;">
		Unknown Name
		</div>
		<div style="font-size: 12px; padding-left: 4px;">
		Unknown Mode &bull; Unknown Map
		</div>
		</td>
		</tr>
		</table>
		</div>
		';
	}
}
// show global server stats link
echo '
<div style="margin-bottom: 4px; position: relative;">
';
// cache total players
$total_players = cache_total_players($ServerID,$valid_ids,$GameID,$BF4stats,$cr);
echo '
<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;"><a class="fill-div" style="padding: 0px; margin: 0px;" href="./index.php?p=home&amp;sid=null"></a></div>
<table>
<tr>
<td class="subsection" style="width: 208px;">
<div class="headline" style="text-align: center; font-size: 12px;">Players Logged</div>
<div style="text-align: center; font-size: 12px;">' . $total_players . '</div>
</td>
<td class="subsection">
<div class="headline" style="text-align: left; padding: 0px; padding-left: 3px;">
Combined Stats From Servers Above
</div>
<div style="font-size: 12px; padding-left: 4px;">
' . $clan_name . '
</div>
</td>
</tr>
</table>
</div>
</div>
';
// end it
echo '
</td>
</tr>
</table>
';
?>
