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
// get value
if(!empty($sid))
{
	$ServerID = $sid;
}
// jquery tabs
echo '
<script type="text/javascript">
$(function()
{
	$( "#tabs, #dogtag_tab" ).tabs(
	{
		beforeLoad: function( event, ui )
		{
			ui.panel.html(
			"<br/><br/><center><img class=\"load\" src=\"./common/images/loading.gif\" alt=\"loading\" /></center><br/><br/>"
			);
			ui.jqXHR.error(function()
			{
				ui.panel.html(
				"<div class=\"subsection\" style=\"margin-top: 2px;\"><div class=\"headline\"><span class=\"information\" style=\"font-size: 14px;\">Error: could not load this tab!</span></div></div>" );
			});
		}
	});
});
</script>
';
// javascript transition wrapper between loading and loaded
echo '
<script type="text/javascript">
$(\'#loading\').hide(0);
$(\'#loaded\').fadeIn("slow");
</script>
';
// continue ...
// query for maps in this server
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	$Mode_q = @mysqli_query($BF4stats,"
		SELECT `Gamemode`, SUM(`NumberofRounds`) AS TotalRounds
		FROM `tbl_mapstats`
		WHERE `ServerID` = {$ServerID}
		AND `Gamemode` != ''
		GROUP BY `Gamemode`
		ORDER BY TotalRounds DESC
		LIMIT 8
	");
}
// or else this is a combined stats page
else
{	
	$Mode_q = @mysqli_query($BF4stats,"
		SELECT `Gamemode`, SUM(`NumberofRounds`) AS TotalRounds
		FROM `tbl_mapstats`
		WHERE `ServerID` IN ({$valid_ids})
		AND `Gamemode` != ''
		GROUP BY `Gamemode`
		ORDER BY TotalRounds DESC
		LIMIT 8
	");
}
if(!$Mode_q || @mysqli_num_rows($Mode_q) == 0)
{
	echo '
	<div class="subsection">
	<div class="headline">No map stats found for ';
	if(!empty($ServerID))
	{
		echo 'this server.';
	}
	else
	{
		echo 'these servers.';
	}
	echo '
	</div>
	</div>
	';
}
else
{
	echo '
	<div class="subsection">
	<br/><center><div class="embed"><img src="./common/maps/maps-played.png';
	if(!empty($ServerID))
	{
		echo '?sid=' . $ServerID;
	}
	echo '" style="height: 300px; width: 600px;" alt="maps played" title="maps played" /></div></center><br/>
	</div>
	<br/>
	';
	// create empty array for storing values in
	$game_modes = array();
	// break the data up into individual game modes
	while($Mode_r = @mysqli_fetch_assoc($Mode_q))
	{
		$Mode = $Mode_r['Gamemode'];
		$game_modes[] = $Mode;
	}
	// find out the friendly name of the first game mode
	if(in_array($game_modes[0],$mode_array))
	{
		$GameMode = array_search($game_modes[0],$mode_array);
		$first_game_mode = $game_modes[0];
	}
	// this mode is missing!
	else
	{
		$GameMode = $game_modes[0];
		$first_game_mode = $game_modes[0];
	}
	echo  '
	<div id="tabs">
	<ul>
	<li><div class="subscript">1</div><a href="#tabs-1">' . $GameMode . '</a></li>
	';
	$count_tracker = 1;
	// step through the game modes for creating tabs
	foreach($game_modes AS $this_game_mode)
	{
		// first tab was already created; skip it
		if($this_game_mode != $game_modes[0])
		{
			// find out the friendly name of this game mode
			if(in_array($this_game_mode,$mode_array))
			{
				$GameMode = array_search($this_game_mode,$mode_array);
			}
			// this mode is missing!
			else
			{
				$GameMode = $this_game_mode;
			}
			$count_tracker++;
			echo '
			<li><div class="subscript">' . $count_tracker . '</div><a href="./common/maps/maps-tab.php?';
			if(!empty($ServerID))
			{
				echo 'sid=' . $ServerID . '&amp;';
			}
			echo 'gid=' . $GameID . '&amp;c=' . $this_game_mode . '">' . $GameMode . '</a></li>
			';
		}
	}
	echo '
	</ul>
	<div id="tabs-1">
	';
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
			AND `Gamemode` = '{$first_game_mode}'
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
			AND `Gamemode` = '{$first_game_mode}'
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
}
?>