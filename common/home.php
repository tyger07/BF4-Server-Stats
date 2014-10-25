<?php
// server stats home page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo '
<div class="sectionheader">
<div class="headline">
';
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	echo 'Statistics data presented is not from all BF4 servers.  These are the statistics of players only in this server.';
}
// or else this is a global stats page
else
{
	echo 'Statistics data presented is not from all BF4 servers.  These are the statistics of players in ' . $clan_name . '\'s servers.';
}
echo '
</div>
</div>
<br/>
<br/>
';

// if there is a ServerID, this is a server stats page
// show scoreboard on welcome page
if(!empty($ServerID))
{
	// this will auto refresh every 20 seconds to ./common/scoreboard.php
	echo'
	<div id="scoreboard">
	<div class="sectionheader" style="position: relative;">
	';
	// updating text...
	echo '
	<div id="fadein" style="position: absolute; top: 4px; left: -150px; display: none;">
	<div class="subsection" style="width: 100px;">
	<center>Updating ...</center>
	</div>
	</div>
	<div class="headline">Live Scoreboard</div>
	</div>
	';
	// fadein javascript
	echo '
	<script type="text/javascript">
	$("#fadein").delay(19000).fadeIn("slow");
	</script>
	';

	// query for player in server and order them by team
	$Scoreboard_q = @mysqli_query($BF4stats,"
		SELECT `TeamID`
		FROM `tbl_currentplayers`
		WHERE `ServerID` = {$ServerID}
		ORDER BY `TeamID` ASC
	");
	// no players were found in the server
	// display basic server information
	if(@mysqli_num_rows($Scoreboard_q) == 0)
	{
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
				$map_img = './images/maps/' . $map . '.png';
			}
			// this map is missing!
			else
			{
				$map_name = $map;
				$map_img = './images/maps/missing.png';
			}
			echo '
			<div style="margin-bottom: 4px; position: relative;">
			<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;"><a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $battlelog . '" target="_blank"></a></div>
			<table>
			<tr>
			<td class="subsection" style="width: 57px;">
			<img style="height: 32px;" src="' . $map_img . '" alt="map image" />
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
			echo '
			<div style="margin-bottom: 4px; position: relative;">
			<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;"><a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $battlelog . '" target="_blank"></a></div>
			<table>
			<tr>
			<td class="subsection" style="width: 57px;">
			<img style="height: 32px;" src="./images/maps/missing.png" alt="map image" />
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
		// free up basic query memory
		@mysqli_free_result($Basic_q);
	}
	// players were found in the server
	// display teams and players
	else
	{
		// initialize values
		$mode_name = 'Unknown';
		$map_name = 'Unknown';
		$mode = 'Unknown';
		$count2 = 0;
		// figure out current game mode and map name
		$Basic_q = @mysqli_query($BF4stats,"
			SELECT `mapName`, `Gamemode`, `maxSlots`, `usedSlots`, `ServerName`
			FROM `tbl_server`
			WHERE `ServerID` = {$ServerID}
			AND `GameID` = {$GameID}
		");
		if(@mysqli_num_rows($Basic_q) != 0)
		{
			$Basic_r = @mysqli_fetch_assoc($Basic_q);
			$used_slots = $Basic_r['usedSlots'];
			$available_slots = $Basic_r['maxSlots'];
			$name = $Basic_r['ServerName'];
			$mode = $Basic_r['Gamemode'];
			// convert mode to friendly name
			// first find if this mode is even in the mode array
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
				$map_img = './images/maps/' . $map . '.png';
			}
			// this map is missing!
			else
			{
				$map_name = $map;
				$map_img = './images/maps/missing.png';
			}
			echo '
			<div style="margin-bottom: 4px; position: relative;">
			<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;"><a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $battlelog . '" target="_blank"></a></div>
			<table>
			<tr>
			<td class="subsection" style="width: 57px;">
			<img style="height: 32px;" src="' . $map_img . '" alt="map image" />
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
			echo '
			<div style="margin-bottom: 4px; position: relative;">
			<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;"><a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $battlelog . '" target="_blank"></a></div>
			<table>
			<tr>
			<td class="subsection" style="width: 57px;">
			<img style="height: 32px;" src="./images/maps/missing.png" alt="map image" />
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
		// free up basic query memory
		@mysqli_free_result($Basic_q);
		
		// initialize values
		$last_team = -1;
		// get current rank query details
		if(!empty($_GET['rank']))
		{
			$srank = $_GET['rank'];
			// filter out SQL injection
			if($srank != 'Score' AND $srank != 'Kills' AND $srank != 'Deaths' AND $srank != 'SquadID')
			{
				// unexpected input detected
				// use default instead
				$srank = 'Score';
			}
		}
		// set default if no rank provided in URL
		else
		{
			$srank = 'Score';
		}
		// get current order query details
		if(!empty($_GET['order']))
		{
			$sorder = $_GET['order'];
			// filter out SQL injection
			if($sorder != 'DESC' AND $sorder != 'ASC')
			{
				// unexpected input detected
				// use default instead
				$sorder = 'DESC';
				$snextorder = 'ASC';
			}
			else
			{
				if($sorder == 'DESC')
				{
					$snextorder = 'ASC';
				}
				else
				{
					$snextorder = 'DESC';
				}
			}
		}
		// set default if no order provided in URL
		else
		{
			$sorder = 'DESC';
			$snextorder = 'ASC';
		}
		
		echo '
		<table style="border-spacing: 0px;">
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
				if($this_team == 3)
				{
					echo '</tr><tr>';
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
					// change team name displayed on scoreboard based on team number and game mode
					if(($mode == 'ConquestLarge0') OR ($mode == 'ConquestSmall0') OR ($mode == 'Domination0') OR ($mode == 'Elimination0') OR ($mode == 'Obliteration') OR ($mode == 'TeamDeathMatch0') OR ($mode == 'AirSuperiority0') OR ($mode == 'CaptureTheFlag0'))
					{
						if($this_team == 1)
						{
							if(($map == 'MP_Abandoned') OR ($map == 'MP_Damage') OR ($map == 'MP_Journey') OR ($map == 'MP_TheDish'))
							{
								$team_name = 'RU Army';
							}
							elseif(($map == 'MP_Flooded') OR ($map == 'MP_Naval') OR ($map == 'MP_Prison') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_Tremors') OR ($map == 'XP1_001') OR ($map == 'XP1_002') OR ($map == 'XP1_003') OR ($map == 'XP1_004') OR ($map == 'XP0_Caspian') OR ($map == 'XP0_Firestorm') OR ($map == 'XP0_Metro') OR ($map == 'XP0_Oman') OR ($map == 'XP2_001') OR ($map == 'XP2_002') OR ($map == 'XP2_003') OR ($map == 'XP2_004') OR ($map == 'XP3_MarketPl') OR ($map == 'XP3_Prpganda') OR ($map == 'XP3_UrbanGdn') OR ($map == 'XP3_WtrFront') OR ($map == 'XP4_Arctic') OR ($map == 'XP4_SubBase') OR ($map == 'XP4_Titan') OR ($map == 'XP4_Wlkrftry'))
							{
								$team_name = 'US Army';
							}
							else
							{
								$team_name = 'US Army';
							}
						}
						elseif($this_team == 2)
						{
							if($map == 'MP_Abandoned')
							{
								$team_name = 'US Army';
							}
							elseif(($map == 'MP_Damage') OR ($map == 'MP_Flooded') OR ($map == 'MP_Journey') OR ($map == 'MP_Naval') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_TheDish') OR ($map == 'MP_Tremors') OR ($map == 'XP1_001') OR ($map == 'XP1_002') OR ($map == 'XP1_003') OR ($map == 'XP1_004') OR ($map == 'XP3_MarketPl') OR ($map == 'XP3_Prpganda') OR ($map == 'XP3_UrbanGdn') OR ($map == 'XP3_WtrFront'))
							{
								$team_name = 'CN Army';
							}
							elseif(($map == 'MP_Prison') OR ($map == 'XP0_Caspian') OR ($map == 'XP0_Firestorm') OR ($map == 'XP0_Metro') OR ($map == 'XP0_Oman') OR ($map == 'XP2_001') OR ($map == 'XP2_002') OR ($map == 'XP2_003') OR ($map == 'XP2_004') OR ($map == 'XP4_Arctic') OR ($map == 'XP4_SubBase') OR ($map == 'XP4_Titan') OR ($map == 'XP4_Wlkrftry'))
							{
								$team_name = 'RU Army';
							}
							else
							{
								$team_name = 'CN Army';
							}
						}
						// something unexpected occurred and a correct team name was not found
						// just name the team based on team number instead
						else
						{
							$team_name = 'Team ' . $this_team;
						}
					}
					elseif($mode == 'RushLarge0')
					{
						if($this_team == 1)
						{
							if(($map == 'MP_Abandoned') OR ($map == 'MP_Damage') OR ($map == 'MP_Flooded') OR ($map == 'MP_Journey') OR ($map == 'MP_Naval') OR ($map == 'MP_Prison') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_TheDish') OR ($map == 'MP_Tremors') OR ($map == 'XP1_001') OR ($map == 'XP1_002') OR ($map == 'XP1_003') OR ($map == 'XP1_004') OR ($map == 'XP0_Caspian') OR ($map == 'XP0_Firestorm') OR ($map == 'XP0_Metro') OR ($map == 'XP0_Oman') OR ($map == 'XP2_001') OR ($map == 'XP2_002') OR ($map == 'XP2_003') OR ($map == 'XP2_004') OR ($map == 'XP3_MarketPl') OR ($map == 'XP3_Prpganda') OR ($map == 'XP3_UrbanGdn') OR ($map == 'XP3_WtrFront') OR ($map == 'XP4_Arctic') OR ($map == 'XP4_SubBase') OR ($map == 'XP4_Titan') OR ($map == 'XP4_Wlkrftry'))
							{
								$team_name = 'US Attackers';
							}
							else
							{
								$team_name = 'Attackers';
							}
						}
						elseif($this_team == 2)
						{
							if(($map == 'MP_Abandoned') OR ($map == 'MP_Damage') OR ($map == 'MP_Flooded') OR ($map == 'MP_Journey') OR ($map == 'MP_Naval') OR ($map == 'MP_Prison') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_TheDish') OR ($map == 'MP_Tremors') OR ($map == 'XP1_001') OR ($map == 'XP1_002') OR ($map == 'XP1_003') OR ($map == 'XP1_004') OR ($map == 'XP3_MarketPl') OR ($map == 'XP3_Prpganda') OR ($map == 'XP3_UrbanGdn') OR ($map == 'XP3_WtrFront'))
							{
								$team_name = 'CN Defenders';
							}
							elseif(($map == 'XP0_Caspian') OR ($map == 'XP0_Firestorm') OR ($map == 'XP0_Metro') OR ($map == 'XP0_Oman') OR ($map == 'XP2_001') OR ($map == 'XP2_002') OR ($map == 'XP2_003') OR ($map == 'XP2_004') OR ($map == 'XP4_Arctic') OR ($map == 'XP4_SubBase') OR ($map == 'XP4_Titan') OR ($map == 'XP4_Wlkrftry'))
							{
								$team_name = 'RU Defenders';
							}
							else
							{
								$team_name = 'Defenders';
							}
						}
						// something unexpected occurred and a correct team name was not found
						// just name the team based on team number instead
						else
						{
							$team_name = 'Team ' . $this_team;
						}
					}
					elseif(($mode == 'SquadDeathMatch0'))
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
					elseif(($mode == 'CarrierAssaultLarge0') OR ($mode == 'CarrierAssaultSmall0'))
					{
						if($this_team == 1)
						{
							$team_name = 'US Attackers';
						}
						elseif($this_team == 2)
						{
							$team_name = 'CN Defenders';
						}
						else
						{
							$team_name = 'Team ' . $this_team;
						}
					}
					elseif($mode == 'Chainlink0')
					{
						if($this_team == 1)
						{
							$team_name = 'US Attackers';
						}
						elseif($this_team == 2)
						{
							$team_name = 'CN Defenders';
						}
						else
						{
							$team_name = 'Team ' . $this_team;
						}
					}
					// something unexpected occurred and a correct team name was not found
					// just name the team based on team number instead
					else
					{
						$team_name = 'Team ' . $this_team;
					}
				}
				// the player is not on a team yet, the "loading in" collumn is formatted different than the team collumns (it extends over two team collumns)
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
								<table class="prettytable" style="margin-top: 4px;">
								<tr>
								<th style="padding-right: 3px;">
								<span class="teamname">' . $team_name . '</span> &nbsp; <span style="float: right;"><span class="information">Tickets Remaining:</span> ' . $Score . '</span>
								</th>
								</tr>
								</table>
								';
							}
							else
							{
								echo '
								<table class="prettytable" style="margin-top: 4px;">
								<tr>
								<th style="padding-right: 3px;">
								<span class="teamname">' . $team_name . '</span> &nbsp; <span style="float: right;"><span class="information">Tickets:</span> ' . $Score . '<span class="information">/</span>' . $WinningScore . '</span>
								</th>
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
						<table class="prettytable" style="margin-top: 4px;">
						<tr>
						<th style="padding-right: 3px;">
						<span class="teamname">' . $team_name . '</span>
						</th>
						</tr>
						</table>
						';
					}
					// free up score query memory
					@mysqli_free_result($Score_q);
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
					<th width="100%" colspan="3" style="text-align:left"><span class="teamname">' . $team_name . '</span></th>
					';
				}
				// this is a real team
				else
				{
					echo '
					<th width="4%" class="countheader">#</th>
					<th width="40%" colspan="2" style="text-align:left">Player</th>
					';
				}
				// if player is loading in, don't show the score, kills, deaths, or squad name headers
				if($this_team != 0)
				{
					echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=home&amp;sid=' . $ServerID . '&amp;rank=Score&amp;order=';
					if($srank != 'Score')
					{
						echo 'DESC"><span class="orderheader">Score</span></a></th>';
					}
					else
					{
						echo $snextorder . '"><span class="ordered' . $sorder . 'header">Score</span></a></th>';
					}
					echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=home&amp;sid=' . $ServerID . '&amp;rank=Kills&amp;order=';
					if($srank != 'Kills')
					{
						echo 'DESC"><span class="orderheader">Kills</span></a></th>';
					}
					else
					{
						echo $snextorder . '"><span class="ordered' . $sorder . 'header">Kills</span></a></th>';
					}
					echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=home&amp;sid=' . $ServerID . '&amp;rank=Deaths&amp;order=';
					if($srank != 'Deaths')
					{
						echo 'DESC"><span class="orderheader">Deaths</span></a></th>';
					}
					else
					{
						echo $snextorder . '"><span class="ordered' . $sorder . 'header">Deaths</span></a></th>';
					}
					echo '<th width="17%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=home&amp;sid=' . $ServerID . '&amp;rank=SquadID&amp;order=';
					if($srank != 'SquadID')
					{
						echo 'ASC"><span class="orderheader">Squad</span></a></th>';
					}
					else
					{
						echo $snextorder . '"><span class="ordered' . $sorder . 'header">Squad</span></a></th>';
					}
				}
				echo'</tr>';
				// query for all players on this team
				$Team_q = @mysqli_query($BF4stats,"
					SELECT `Soldiername`, `Score`, `Kills`, `Deaths`, `TeamID`, `SquadID`, `CountryCode`
					FROM `tbl_currentplayers`
					WHERE `ServerID` = {$ServerID}
					AND `TeamID` = {$this_team}
					ORDER BY {$srank} {$sorder}
				");
				// if team query worked and players were found on this team
				if(@mysqli_num_rows($Team_q) != 0)
				{
					$count = 1;
					while($Team_r = @mysqli_fetch_assoc($Team_q))
					{
						$player = $Team_r['Soldiername'];
						// see if this player has server stats in this server yet
						$PlayerID_q = @mysqli_query($BF4stats,"
							SELECT tpd.`PlayerID`
							FROM `tbl_playerstats` tps
							INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
							INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
							WHERE tsp.`ServerID` = {$ServerID}
							AND tpd.`SoldierName` = '{$player}'
							AND tpd.`GameID` = {$GameID}
						");
						// server stats found for this player in this server
						if(@mysqli_num_rows($PlayerID_q) == 1)
						{
							$PlayerID_r = @mysqli_fetch_assoc($PlayerID_q);
							$PlayerID = $PlayerID_r['PlayerID'];
						}
						// this player needs to finish this round to get server stats in this server
						else
						{
							$PlayerID = null;
						}
						$score = $Team_r['Score'];
						$kills = $Team_r['Kills'];
						$deaths = $Team_r['Deaths'];
						$team = $Team_r['TeamID'];
						$squad = $Team_r['SquadID'];
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
								$country_img = './images/flags/none.png';
							}
							else
							{
								$country_img = './images/flags/' . strtolower($country) . '.png';	
							}
						}
						// this country is missing!
						else
						{
							$country_name = $country;
							$country_img = './images/flags/none.png';
						}
						// if player is 'loading in', the style is different
						if($this_team == 0)
						{
							echo '
							<tr>
							<td width="4%" class="count"><span class="information">' . $count . '</span></td>
							<td class="tablecontents" width="3%" style="text-align:left"><img src="' . $country_img . '" alt="' . $country_name . '"/></td>
							';
							// if this player has stats in this server, provide a link to their stats page
							if($PlayerID != null)
							{
								echo '<td class="tablecontents" width="93%" style="text-align:left"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;pid=' . $PlayerID . '&amp;p=player">' . $player . '</a></td>';
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
							<td class="tablecontents" width="3%" style="text-align:left"><img src="' . $country_img . '" alt="' . $country_name . '"/></td>
							';
							// if this player has stats in this server, provide a link to their stats page
							if($PlayerID != null)
							{
								echo '<td class="tablecontents" width="37%" style="text-align:left"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;pid=' . $PlayerID . '&amp;p=player">' . $player . '</a></td>';
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
		// free up player ID query memory
		@mysqli_free_result($PlayerID_q);
		// free up team query memory
		@mysqli_free_result($Team_q);
		echo '
		</tr>
		</table>
		';
	}
	// free up score board query memory
	@mysqli_free_result($Scoreboard_q);
	echo '
	</div>
	<br/><br/>
	';
}
// end of scoreboard

echo '
<div class="sectionheader">
<div class="headline">Top Players</div>
</div>
';

// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// find out how many rows are in the table 
	$TotalRows_q = @mysqli_query($BF4stats,"
		SELECT COUNT(tpd.`SoldierName`)
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tsp.`ServerID` = {$ServerID}
		AND tpd.`GameID` = {$GameID}
	");
	$TotalRows_r = @mysqli_fetch_row($TotalRows_q);
	$numrows = $TotalRows_r[0];
}
// or else this is a global stats page
else
{
	// find out how many rows are in the table
	$TotalRows_q = @mysqli_query($BF4stats,"
		SELECT SUM(tps.`Score`) AS Score
		FROM `tbl_playerdata` tpd
		INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
		WHERE tpd.`GameID` = {$GameID}
		GROUP BY tpd.`PlayerID`
	");
	$numrows = @mysqli_num_rows($TotalRows_q);
}
// number of rows to show per page
$rowsperpage = 20;
// find out total pages
$totalpages = ceil($numrows / $rowsperpage);
// set current pagination page to default if none provided
if(empty($currentpage))
{
	// default page num
	$currentpage = 1;
}
// if current page is greater than total pages...
if($currentpage > $totalpages)
{
	// set current page to last page
	$currentpage = $totalpages;
}
// if current page is less than first page...
if($currentpage < 1)
{
	// set current page to first page
	$currentpage = 1;
}
// get current rank query details
if(!empty($rank))
{
	// filter out SQL injection
	if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Rounds' AND $rank != 'Kills' AND $rank != 'Deaths' AND $rank != 'KDR' AND $rank != 'Headshots' AND $rank != 'HSR')
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
if(!empty($order))
{
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
// the offset of the list, based on current page 
$offset = ($currentpage - 1) * $rowsperpage;
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// get the info from the db 
	$Players_q  = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, tps.`Deaths`, (tps.`Kills`/tps.`Deaths`) AS KDR, tps.`Rounds`, tps.`Headshots`, (tps.`Headshots`/tps.`Kills`) AS HSR
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tsp.`ServerID` = {$ServerID}
		AND tpd.`GameID` = {$GameID}
		ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
		LIMIT {$offset}, {$rowsperpage}
	");
}
// or else this is a global stats page
else
{
	// get the info from the db 
	$Players_q  = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, SUM(tps.`Rounds`) AS Rounds, SUM(tps.`Headshots`) AS Headshots, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR
		FROM `tbl_playerdata` tpd
		INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
		WHERE tpd.`GameID` = {$GameID}
		GROUP BY tpd.`PlayerID`
		ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
		LIMIT {$offset}, {$rowsperpage}
	");
}
// offset of player rank count to show on scoreboard
$count = ($currentpage * 20) - 20;
// check if there are rows returned
if(@mysqli_num_rows($Players_q) != 0)
{
	echo '
	<table class="prettytable">
	<tr>
	<th width="3%" class="countheader">#</th>
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=SoldierName&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=SoldierName&amp;o=';
	}
	if($rank != 'SoldierName')
	{
		echo 'ASC"><span class="orderheader">Player</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Player</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="12%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Score&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="12%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Score&amp;o=';
	}
	if($rank != 'Score')
	{
		echo 'DESC"><span class="orderheader">Score</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Score</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Rounds&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Rounds&amp;o=';
	}
	if($rank != 'Rounds')
	{
		echo 'DESC"><span class="orderheader">Rounds</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Rounds</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Kills&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Kills&amp;o=';
	}
	if($rank != 'Kills')
	{
		echo 'DESC"><span class="orderheader">Kills</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Kills</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Deaths&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Deaths&amp;o=';
	}
	if($rank != 'Deaths')
	{
		echo 'DESC"><span class="orderheader">Deaths</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Deaths</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=KDR&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=KDR&amp;o=';
	}
	if($rank != 'KDR')
	{
		echo 'DESC"><span class="orderheader">Kill/Death</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered'. $order . 'header">Kill/Death</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Headshots&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Headshots&amp;o=';
	}
	if($rank != 'Headshots')
	{
		echo 'DESC"><span class="orderheader">Headshots</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Headshots</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="12%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=HSR&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="12%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=HSR&amp;o=';
	}
	if($rank != 'HSR')
	{
		echo 'DESC"><span class="orderheader">Headshot/Kill</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Headshot/Kill</span></a></th>';
	}
	echo '</tr>';
	// while there are rows to be fetched...
	while($Players_r = @mysqli_fetch_assoc($Players_q))
	{
		$Score = $Players_r['Score'];
		$SoldierName = $Players_r['SoldierName'];
		$PlayerID = $Players_r['PlayerID'];
		$Kills = $Players_r['Kills'];
		$Deaths = $Players_r['Deaths'];
		$Headshots = $Players_r['Headshots'];
		$KDR = round($Players_r['KDR'], 2);
		$HSR = round(($Players_r['HSR']*100),2);
		$Rounds = $Players_r['Rounds'];
		$count++;
		echo '
		<tr>
		<td width="3%" class="count"><span class="information">' . $count . '</span></td>
		';
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '<td width="18%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;pid=' . $PlayerID . '&amp;p=player">' . $SoldierName . '</a></td>';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="18%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		echo '
		<td width="12%" class="tablecontents">' . $Score . '</td>
		<td width="11%" class="tablecontents">' . $Rounds . '</td>
		<td width="11%" class="tablecontents">' . $Kills . '</td>
		<td width="11%" class="tablecontents">' . $Deaths . '</td>
		<td width="11%" class="tablecontents">' . $KDR . '</td>
		<td width="11%" class="tablecontents">' . $Headshots . '</td>
		<td width="12%" class="tablecontents">' . $HSR . '<span class="information"> %</span></td>
		</tr>	
		';
	}
	echo '</table>';
	// build the pagination links
	pagination_links($ServerID,$_SERVER['PHP_SELF'],'leaders',$currentpage,$totalpages,$rank,$order,'');
}
else
{
	echo '
	<div class="subsection">
	<div class="headline">
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo 'No player stats found for this server.';
	}
	// or else this is a global stats page
	else
	{
		echo 'No player stats found for these servers.';
	}
	echo '
	</div>
	</div>
	';
}
// free up total rows query memory
@mysqli_free_result($TotalRows_q);
// free up players query memory
@mysqli_free_result($Players_q);
?>
