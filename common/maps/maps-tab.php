<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../functions.php');
require_once('../case.php');
require_once('../constants.php');
// default variable to null
$ServerID = null;
$Code = null;
// get query strings
if(!empty($sid))
{
	$ServerID = $sid;
}
if(!empty($_GET['c']) && !(is_numeric($_GET['c'])))
{
	$Code = mysqli_real_escape_string($BF4stats, strip_tags($_GET['c']));
}
echo '
<table class="prettytable">
<tr>
<th width="5%" class="countheader">#</th>
<th width="23%" style="text-align:left" colspan="2">Map Name</th>
<th width="18%" style="text-align:left;">Map Code</th>
<th width="18%" style="text-align:left;"><span class="orderedDESCheader">Rounds Played</span></th>
<th width="18%" style="text-align:left;">Average Players</th>
<th width="18%" style="text-align:left;">Joins / Leaves</th>
</tr>
';
// initialize value
$count = 0;
// query for map details for this game mode
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	$Map_q = @mysqli_query($BF4stats,"
		SELECT `MapName`, SUM(`NumberofRounds`) AS NumberofRounds, AVG(`AvgPlayers`) AS AveragePlayers, (AVG(`AvgPlayers`)/AVG(`PlayersLeftServer`)) AS AVGPop
		FROM `tbl_mapstats`
		WHERE `ServerID` = {$ServerID}
		AND `Gamemode` = '{$Code}'
		AND `MapName` != ''
		GROUP BY `MapName`
		ORDER BY NumberofRounds DESC
	");
}
// or else this is a combined stats page
else
{
	$Map_q = @mysqli_query($BF4stats,"
		SELECT `MapName`, SUM(`NumberofRounds`) AS NumberofRounds, AVG(`AvgPlayers`) AS AveragePlayers, (AVG(`AvgPlayers`)/AVG(`PlayersLeftServer`)) AS AVGPop
		FROM `tbl_mapstats`
		WHERE `ServerID` IN ({$valid_ids})
		AND `Gamemode` = '{$Code}'
		AND `MapName` != ''
		GROUP BY `MapName`
		ORDER BY NumberofRounds DESC
	");
}
if(@mysqli_num_rows($Map_q) != 0)
{
	while($Map_r = @mysqli_fetch_assoc($Map_q))
	{
		$NumberofRounds = $Map_r['NumberofRounds'];
		$MapCode = $Map_r['MapName'];
		// convert map to friendly name
		// first find if this map name is even in the map array
		if(in_array($MapCode,$map_array))
		{
			$MapName = array_search($MapCode,$map_array);
			$map_img = './common/images/maps/' . $MapCode . '.png';
		}
		// this map is missing!
		else
		{
			$MapName = $MapCode;
			$map_img = './common/images/maps/missing.png';
		}
		$AveragePlayers = round($Map_r['AveragePlayers'],2);
		$AveragePopularity = round($Map_r['AVGPop'],2) * 100;
		$count++;
		echo '
		<tr>
		<td width="5%" class="count"><span class="information">' . $count . '</span></td>
		<td class="subsection" style="width: 57px;padding: 3px;"><img src="' . $map_img . '" style="height: 32px; width: 57px;" alt="map image" /></td>
		<td width="18%" class="tablecontents">' . $MapName . '</td>
		<td width="18%" class="tablecontents">' . $MapCode . '</td>
		<td width="18%" class="tablecontents">' . $NumberofRounds . '</td>
		<td width="18%" class="tablecontents">' . $AveragePlayers . '</td>
		<td width="18%" class="tablecontents">' . $AveragePopularity . '<span class="information"> %</span></td>
		</tr>
		';
	}
}
// this shouldn't happen!  ... but just in case somehow it is possible
else
{
	echo '
	<tr>
	<td width="5%" class="count">&nbsp;</td>
	<td width="95%" class="tablecontents" colspan="7" style="text-align: left;">No information found!</td>
	</tr>
	';
}
echo '
</table>
';
?>