<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../functions.php');
require_once('../constants.php');
require_once('../case.php');
// default variable to null
$ServerID = null;
// get values
if(!empty($sid))
{
	$ServerID = $sid;
}
// start creating scoreboard
echo '
<div class="subsection" style="position: relative;">
';
// updating text...
// hidden by default until time is reached
echo '
<div id="fadein" style="position: absolute; top: 4px; left: -150px; display: none;">
<div class="subsection" style="width: 100px;">
<center>Updating ...<span style="float:right;"><img class="update" src="./common/images/loading.gif" alt="loading" /></span></center>
</div>
</div>
';
// last updated text...
// shown by default until faded away
echo '
<div id="fadeaway" style="position: absolute; top: 4px; left: -150px;">
<div class="subsection" style="width: 100px;">
<center>Updated <span id="timestamp"></span></center>
</div>
</div>
<div class="headline">
Live Scoreboard
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
// display basic server information
$Basic_q = @mysqli_query($BF4stats,"
	SELECT `mapName`, `Gamemode`, `maxSlots`, `usedSlots`, `ServerName`
	FROM `tbl_server`
	WHERE `ServerID` = {$ServerID}
	AND `GameID` = {$GameID}
");
// information was found
if(@mysqli_num_rows($Basic_q) != 0)
{
	$Basic_r = @mysqli_fetch_assoc($Basic_q);
	$used_slots = $Basic_r['usedSlots'];
	$available_slots = $Basic_r['maxSlots'];
	$name = $Basic_r['ServerName'];
	$battlelog = 'https://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . urlencode($name);
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
	<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;"><a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $battlelog . '" target="_blank"></a></div>
	<table style="margin-bottom: -2px;">
	<tr>
	<td class="subsection" style="width: 57px;">
	<img src="' . $map_img . '" style="height: 32px; width: 57px;" alt="map image" />
	</td>
	<td class="subsection" style="width: 57px;">
	<div class="headline" style="text-align: center; font-size: 12px;">Players</div>
	<div style="text-align: center; font-size: 12px;">' . $used_slots . ' / ' . $available_slots . '</div>
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
	$battlelog = 'https://battlelog.battlefield.com/bf4/servers/pc/';
	echo '
	<div style="margin-bottom: 4px; position: relative;">
	<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;"><a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $battlelog . '" target="_blank"></a></div>
	<table style="margin-bottom: -2px;">
	<tr>
	<td class="subsection" style="width: 57px;">
	<img src="./common/images/maps/missing.png" style="height: 32px; width: 57px;" alt="map image" />
	</td>
	<td class="subsection" style="width: 57px;">
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
// query for player in server and order them by team
$Scoreboard_q = @mysqli_query($BF4stats,"
	SELECT `TeamID`
	FROM `tbl_currentplayers`
	WHERE `ServerID` = {$ServerID}
	ORDER BY `TeamID` ASC
");
// players were found in the server
// display teams and players
if(@mysqli_num_rows($Scoreboard_q) != 0)
{
	// initialize values
	$last_team = -1;
	// get current rank query details
	if(!empty($scoreboard_rank))
	{
		$rank = $scoreboard_rank;
		// filter out SQL injection
		if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Kills' AND $rank != 'Deaths' AND $rank != 'SquadID')
		{
			// unexpected input detected
			// use default instead
			$rank = 'Score';
		}
	}
	// set default if no rank provided in URL
	else
	{
		$rank = 'Score';
	}
	// get current order query details
	if(!empty($scoreboard_order))
	{
		$order = $scoreboard_order;
		// filter out SQL injection
		if($order != 'DESC' AND $order != 'ASC')
		{
			// unexpected input detected
			// use default instead
			$order = 'DESC';
			$nextorder = 'ASC';
		}
		else
		{
			if($order == 'DESC')
			{
				$nextorder = 'ASC';
			}
			else
			{
				$nextorder = 'DESC';
			}
		}
	}
	// set default if no order provided in URL
	else
	{
		$order = 'DESC';
		$nextorder = 'ASC';
	}
	echo '
	<table style="border-spacing: 0px; margin-top: 2px;">
	<tr>
	';
	// start looping through the scoreboard information
	while($Scoreboard_r = @mysqli_fetch_assoc($Scoreboard_q))
	{
		$this_team = $Scoreboard_r['TeamID'];
		// change to a different collumn or row of the scoreboard when the team number changes
		if($this_team != $last_team)
		{
			// if the game mode has more than 2 teams, the third team should be moved down to the next row of the scoreboard
			if($last_team == 2)
			{
				echo '
				</tr>
				<tr>
				';
			}
			// change team name shown depending on team number
			// team 0 is 'loading in'
			if($this_team == 0)
			{
				$team_name = 'Joining ...';
			}
			// player is actually assigned to a team
			else
			{
				if($mode == 'SquadDeathMatch0')
				{
					if($this_team == 1)
					{
						$team_name = 'Alpha';
					}
					elseif($this_team == 2)
					{
						$team_name = 'Bravo';
					}
					elseif($this_team == 3)
					{
						$team_name = 'Charlie';
					}
					elseif($this_team == 4)
					{
						$team_name = 'Delta';
					}
					// something unexpected occurred and a correct team name was not found
					// just name the team based on team number instead
					else
					{
						$team_name = 'Team ' . $this_team;
					}
				}
				else
				{
					$team_name = 'Team ' . $this_team;
				}
			}
			// the player is not on a team yet, the "loading in" column is formatted different than the team collumns (it extends over two team collumns)
			if($this_team == 0)
			{
				echo '<td valign="top" colspan="2">';
			}
			// this is a team collumn
			else
			{
				echo '<td valign="top">';
			}
			// the "loading in" team does not have scores
			if($this_team != 0)
			{
				// query for scores
				$Score_q = @mysqli_query($BF4stats,"
					SELECT `Score`, `WinningScore`
					FROM `tbl_teamscores`
					WHERE `ServerID` = {$ServerID}
					AND `TeamID` = {$this_team}
				");
				if(@mysqli_num_rows($Score_q) != 0)
				{
					while($Score_r = @mysqli_fetch_assoc($Score_q))
					{
						$Score = $Score_r['Score'];
						$WinningScore = $Score_r['WinningScore'];
						if($WinningScore == 0)
						{
							echo '
							<table class="prettytable" style="margin-top: 2px; margin-bottom: -2px;">
							<tr>
							<td class="tablecontents" style="padding-right: 3px;">
							<span class="teamname">' . $team_name . '</span> &nbsp; <span style="float: right;"><span class="information">Tickets Remaining:</span> ' . $Score . '</span>
							</td>
							</tr>
							</table>
							';
						}
						else
						{
							echo '
							<table class="prettytable" style="margin-top: 2px; margin-bottom: -2px;">
							<tr>
							<td class="tablecontents" style="padding-right: 3px;">
							<span class="teamname">' . $team_name . '</span> &nbsp; <span style="float: right;"><span class="information">Tickets:</span> ' . $Score . '<span class="information">/</span>' . $WinningScore . '</span>
							</td>
							</tr>
							</table>
							';
						}
					}
				}
				// an error occured
				// display blank information
				else
				{
					echo '
					<table class="prettytable" style="margin-top: 2px; margin-bottom: -2px;">
					<tr>
					<td class="tablecontents" style="padding-right: 3px;">
					<span class="teamname">' . $team_name . '</span>
					</td>
					</tr>
					</table>
					';
				}
			}
			echo '
			<table width="100%" align="center" border="0" class="prettytable">
			<tr>
			';
			// this formatting is changed depending on if this is a real team or is the "loading in" team
			// this is the "loading in" team
			if($this_team == 0)
			{
				echo '
				<td class="tablecontents" width="100%" colspan="3" style="text-align:left"><span class="teamname">' . $team_name . '</span></td>
				';
			}
			// this is a real team
			else
			{
				echo '
				<th width="4%" class="countheader">#</th>
				';
				// player column
				pagination_headers('Player',$ServerID,'home','40','rank',$rank,'SoldierName','order',$order,'ASC',$nextorder,$currentpage,'2','','');
				// score column
				pagination_headers('Score',$ServerID,'home','13','rank',$rank,'Score','order',$order,'DESC',$nextorder,$currentpage,'','','');
				// kills column
				pagination_headers('Kills',$ServerID,'home','13','rank',$rank,'Kills','order',$order,'DESC',$nextorder,$currentpage,'','','');
				// deaths column
				pagination_headers('Deaths',$ServerID,'home','13','rank',$rank,'Deaths','order',$order,'DESC',$nextorder,$currentpage,'','','');
				// squad column
				pagination_headers('Squad',$ServerID,'home','17','rank',$rank,'SquadID','order',$order,'ASC',$nextorder,$currentpage,'','','');
			}
			echo'</tr>';
			// query for all players on this team
			// is adkats information available?
			if($adkats_available)
			{
				$Team_q = @mysqli_query($BF4stats,"
					SELECT cp.`Soldiername`, cp.`Score`, cp.`Kills`, cp.`Deaths`, cp.`TeamID`, cp.`SquadID`, cp.`CountryCode`, sub.`PlayerID`, sub.`ban_status`
					FROM `tbl_currentplayers` cp
					LEFT JOIN
					(
						SELECT tpd.`PlayerID`, tpd.`SoldierName`, adk.`ban_status`
						FROM `tbl_playerdata` tpd
						INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
						INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
						LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
						WHERE tpd.`GameID` = {$GameID}
						AND tsp.`ServerID` = {$ServerID}
					) sub ON sub.`SoldierName` = cp.`SoldierName`
					WHERE cp.`ServerID` = {$ServerID}
					AND cp.`TeamID` = {$this_team}
					GROUP BY cp.`SoldierName`, sub.`PlayerID`
					ORDER BY {$rank} {$order};
				");
			}
			else
			{
				$Team_q = @mysqli_query($BF4stats,"
					SELECT cp.`Soldiername`, cp.`Score`, cp.`Kills`, cp.`Deaths`, cp.`TeamID`, cp.`SquadID`, cp.`CountryCode`, sub.`PlayerID`
					FROM `tbl_currentplayers` cp
					LEFT JOIN
					(
						SELECT tpd.`PlayerID`, tpd.`SoldierName`
						FROM `tbl_playerdata` tpd
						INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
						INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
						WHERE tpd.`GameID` = {$GameID}
						AND tsp.`ServerID` = {$ServerID}
					) sub ON sub.`SoldierName` = cp.`SoldierName`
					WHERE cp.`ServerID` = {$ServerID}
					AND cp.`TeamID` = {$this_team}
					GROUP BY cp.`SoldierName`, sub.`PlayerID`
					ORDER BY {$rank} {$order};
				");
			}
			// if team query worked and players were found on this team
			if(@mysqli_num_rows($Team_q) != 0)
			{
				$count = 1;
				while($Team_r = @mysqli_fetch_assoc($Team_q))
				{
					$PlayerID = $Team_r['PlayerID'];
					$player = textcleaner($Team_r['Soldiername']);
					$score = $Team_r['Score'];
					$kills = $Team_r['Kills'];
					$deaths = $Team_r['Deaths'];
					$team = $Team_r['TeamID'];
					$squad = $Team_r['SquadID'];
					// is this player banned?
					// or have previous ban which was lifted?
					$player_banned = 0;
					$previous_banned = 0;
					if($adkats_available)
					{
						$ban_status = $Team_r['ban_status'];
						if(!is_null($ban_status))
						{
							if($ban_status == 'Active')
							{
								$player_banned = 1;
							}
							elseif($ban_status == 'Expired')
							{
								$previous_banned = 1;
							}
						}
					}
					// convert squad name to friendly name
					// first find out if this squad name is the list of squad names
					if(in_array($squad,$squad_array))
					{
						$squad_name = array_search($squad,$squad_array);
					}
					// this squad is missing!
					else
					{
						$squad_name = $squad;
					}
					$country = strtoupper($Team_r['CountryCode']);
					// convert country name to friendly name
					// and compile flag image
					// first find out if this country name is the list of country names
					if(in_array($country,$country_array))
					{
						$country_name = array_search($country,$country_array);
						// compile country flag image
						// if country is null or unknown, use generic image
						if(($country == '') OR ($country == '--'))
						{
							$country_img = './common/images/flags/none.png';
						}
						else
						{
							$country_img = './common/images/flags/' . strtolower($country) . '.png';	
						}
					}
					// this country is missing!
					else
					{
						$country_name = $country;
						$country_img = './common/images/flags/none.png';
					}
					// create player link if player id is known
					if(!is_null($PlayerID))
					{
						$link = './index.php?sid=' . $ServerID . '&amp;pid=' . $PlayerID . '&amp;p=player';
					}
					// if player is 'loading in', the style is different
					if($this_team == 0)
					{
						echo '
						<tr>
						<td width="4%" class="count"><span class="information">' . $count . '</span></td>
						<td class="tablecontents" width="3%" style="text-align:left"><img src="' . $country_img . '" style="height: 11px; width: 16px;" alt="' . $country_name . '"/></td>
						';
						// if this player has stats in this server, provide a link to their stats page
						if(!is_null($PlayerID))
						{
							if($player_banned == 1)
							{
								echo '<td class="banoutline" width="93%" style="text-align:left; position: relative;"><div class="bansubscript">Banned</div>';
							}
							elseif($previous_banned == 1)
							{
								echo '<td class="warnoutline" width="93%" style="text-align:left; position: relative;"><div class="bansubscript">Warned</div>';
							}
							else
							{
								echo '<td class="tablecontents" width="93%" style="text-align:left; position: relative;">';
							}
							echo '
							<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
								<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
							</div>
							<a href="' . $link . '">' . $player . '</a>
							</td>';
						}
						// otherwise just display their name without a link
						else
						{
							echo '<td class="tablecontents" width="93%" style="text-align:left">' . $player . '</td>';
						}
					}
					else
					{
						echo '
						<tr>
						<td width="4%" class="count"><span class="information">' . $count . '</span></td>
						<td class="tablecontents" width="3%" style="text-align:left"><img src="' . $country_img . '" style="height: 11px; width: 16px;" alt="' . $country_name . '"/></td>
						';
						// if this player has stats in this server, provide a link to their stats page
						if(!is_null($PlayerID))
						{
							if($player_banned == 1)
							{
								echo '<td class="banoutline" width="37%" style="text-align:left; position: relative;"><div class="bansubscript">Banned</div>';
							}
							elseif($previous_banned == 1)
							{
								echo '<td class="warnoutline" width="37%" style="text-align:left; position: relative;"><div class="bansubscript">Warned</div>';
							}
							else
							{
								echo '<td class="tablecontents" width="37%" style="text-align:left; position: relative;">';
							}
							echo '
							<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
								<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
							</div>
							<a href="' . $link . '">' . $player . '</a>
							</td>';
						}
						// otherwise just display their name without a link
						else
						{
							echo '<td class="tablecontents" width="37%" style="text-align:left">' . $player . '</td>';
						}
					}
					// if player is loading in, don't show the score, kills, deaths, or squad name
					if($this_team != 0)
					{
						echo '
						<td class="tablecontents" width="13%" style="text-align:left">' . $score . '</td>
						<td class="tablecontents" width="13%" style="text-align:left">' . $kills . '</td>
						<td class="tablecontents" width="13%" style="text-align:left">' . $deaths . '</td>
						<td class="tablecontents" width="17%" style="text-align:left">' . $squad_name . '</td>
						';
					}
					$count++;
					echo '</tr>';
				}
			}
			// no players were found on this team!
			// some sort of database error must have occured
			// this is bad..
			// playing damage control
			else
			{
				// if player is 'loading in', the style is different
				if($this_team == 0)
				{
					echo '
					<tr>
					<td width="4%" class="count"><span class="information">&nbsp;</span></td>
					<td class="tablecontents" width="3%" style="text-align:left">&nbsp;</td>
					<td class="tablecontents" width="93%" style="text-align:left">An error occurred!</td>
					</tr>
					';
				}
				else
				{
					echo '
					<tr>
					<td width="4%" class="count"><span class="information">&nbsp;</span></td>
					<td class="tablecontents" width="3%" style="text-align:left">&nbsp;</td>
					<td class="tablecontents" width="37%" style="text-align:left">An error occured!</td>
					<td class="tablecontents" width="13%" style="text-align:left">&nbsp;</td>
					<td class="tablecontents" width="13%" style="text-align:left">&nbsp;</td>
					<td class="tablecontents" width="13%" style="text-align:left">&nbsp;</td>
					<td class="tablecontents" width="17%" style="text-align:left">&nbsp;</td>
					</tr>
					';
				}
			}
			echo '</table></td>';
			// the formatting between the "loading in" team and the other actual teams is different
			if($this_team == 0)
			{
				echo '</tr><tr>';
			}
		}
		// remember to track which team we just probed
		$last_team = $this_team;
	}
	echo '
	</tr>
	</table>
	';
}
?>
