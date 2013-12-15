<?php
// functions for server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// function to find player's weapon stats
function Statsout($headingprint, $damagetype, $ThisPlayerName, $ThisPlayerID, $server_ID, $db)
{
	// get current rank query details
	if(isset($_GET['rank']) AND !empty($_GET['rank']))
	{
		$rank = $_GET['rank'];
		// filter out SQL injection
		if($rank != 'Friendlyname' AND $rank != 'Kills' AND $rank != 'Deaths' AND $rank != 'Headshots' AND $rank != 'HSR')
		{
			// unexpected input detected
			// use default instead
			$rank = 'Friendlyname';
		}
	}
	// set default if no rank provided in URL
	else
	{
		$rank = 'Friendlyname';
	}
	// get current order query details
	if(isset($_GET['order']) AND !empty($_GET['order']))
	{
		$order = $_GET['order'];
		// filter out SQL injection
		if($order != 'DESC' AND $order != 'ASC')
		{
			// unexpected input detected
			// use default instead
			$order = 'ASC';
			$nextorder = 'DESC';
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
		$order = 'ASC';
		$nextorder = 'DESC';
	}
	// see if this player has used this category's weapons
	$Weapon_q = @mysqli_query($db,"
		SELECT tws.Friendlyname, wa.Kills, wa.Deaths, wa.Headshots, wa.WeaponID, (wa.Headshots/wa.Kills) AS HSR
		FROM tbl_weapons_stats wa
		INNER JOIN tbl_server_player tsp ON tsp.StatsID = wa.StatsID
		INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID
		INNER JOIN tbl_weapons tws ON tws.WeaponID = wa.WeaponID
		WHERE tsp.ServerID = {$server_ID}
		AND tpd.SoldierName = '{$ThisPlayerName}'
		AND tws.Damagetype = '{$damagetype}' AND wa.Kills > 0
		ORDER BY {$rank} {$order}
	");
	// see if we have any records for this player for this category
	if(@mysqli_num_rows($Weapon_q) != 0)
	{
		echo '
		<div class="innercontent">
		<table width="98%" border="0">
		<tr>
		<th style="text-align: left;">' . $headingprint . '</th>
		</tr>
		</table>
		<table align="center" width="98%" border="0">
		<tr>
		<td width="3%" style="text-align:left">&nbsp;</td>
		<th width="17%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $server_ID . '&amp;PlayerID=' . $ThisPlayerID . '&amp;search=1&amp;rank=Friendlyname&amp;order=';
		if($rank != 'Friendlyname')
		{
			echo 'ASC';
		}
		else
		{
			echo $nextorder;
		}
		echo '"><span class="orderheader">Weapon Name</span></a></th>
		<th width="16%" style="text-align:left;">Rank</th>
		<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $server_ID . '&amp;PlayerID=' . $ThisPlayerID . '&amp;search=1&amp;rank=Kills&amp;order=';
		if($rank != 'Kills')
		{
			echo 'DESC';
		}
		else
		{
			echo $nextorder;
		}
		echo '"><span class="orderheader">Kills</span></a></th>
		<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $server_ID . '&amp;PlayerID=' . $ThisPlayerID . '&amp;search=1&amp;rank=Deaths&amp;order=';
		if($rank != 'Deaths')
		{
			echo 'DESC';
		}
		else
		{
			echo $nextorder;
		}
		echo '"><span class="orderheader">Deaths</span></a></th>
		<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $server_ID . '&amp;PlayerID=' . $ThisPlayerID . '&amp;search=1&amp;rank=Headshots&amp;order=';
		if($rank != 'Headshots')
		{
			echo 'DESC';
		}
		else
		{
			echo $nextorder;
		}
		echo '"><span class="orderheader">Headshots</span></a></th>
		<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $server_ID . '&amp;PlayerID=' . $ThisPlayerID . '&amp;search=1&amp;rank=HSR&amp;order=';
		if($rank != 'HSR')
		{
			echo 'DESC';
		}
		else
		{
			echo $nextorder;
		}
		echo '"><span class="orderheader">Headshot Ratio</span></a></th>
		</tr>';
		while($Weapon_r = @mysqli_fetch_assoc($Weapon_q))
		{
			$weapon_name_displayed = preg_replace("/_/"," ",$Weapon_r['Friendlyname']);
			// rename 'death'
			if($weapon_name_displayed == 'Death')
			{
				$weapon_name_displayed = 'Machinery';
			}
			$kills = $Weapon_r['Kills'];
			$deaths = $Weapon_r['Deaths'];
			$headshots = $Weapon_r['Headshots'];
			$ratio = round(($Weapon_r['HSR']*100),2);
			$weaponID = $Weapon_r['WeaponID'];
			// find this player's weapon rank with this weapon
			// initialize values
			$weapon_count = 0;
			$num_rows = 0;
			// weapon rank query
			$WeaponRank_q = @mysqli_query($db,"
				SELECT wa.Kills
				FROM tbl_weapons_stats wa
				INNER JOIN tbl_server_player tsp ON tsp.StatsID = wa.StatsID
				WHERE tsp.ServerID = {$server_ID}
				AND wa.WeaponID = {$weaponID}
				AND wa.Kills > 0
				ORDER BY wa.Kills DESC
			");
			// count number of rows as total number of kills with this weapon in the database
			$num_rows = @mysqli_num_rows($WeaponRank_q);
			while($WeaponRank_r = @mysqli_fetch_assoc($WeaponRank_q))
			{
				$weapon_count++;
				$this_kills = $WeaponRank_r['Kills'];
				// if this player's number of kills matches the current kill row
				if($kills == $this_kills)
				{
					break;
				}
			}
			// free up weapon rank query memory
			@mysqli_free_result($WeaponRank_q);
			echo '
			<tr>
			<td width="3%" style="text-align:left">&nbsp;</td>
			<td width="17%" class="tablecontents"  style="text-align: left"><font class="information">' . $weapon_name_displayed . ':</font></td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $weapon_count . '<font class="information"> / </font>' . $num_rows . '</td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $kills . '</td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $deaths . '</td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $headshots . '</td>
			<td width="16%" class="tablecontents" style="text-align: left">' . $ratio . ' <font class="information">%</font></td>
			</tr>
			';
		}
		// free up weapon query memory
		@mysqli_free_result($Weapon_q);
		echo '
		</table></div>
		';
	}
}
// rank queries function for player stats page
function rank($server_ID, $player_name, $metric, $order, $width, $db)
{
	// initialize values
	$count = 0;
	$match = 0;
	// rank players
	$Rank_q  = @mysqli_query($db,"
		SELECT tpd.SoldierName
		FROM tbl_playerstats tps
		INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID
		INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID
		WHERE tsp.ServerID = {$server_ID}
		ORDER BY {$metric} {$order}, SoldierName ASC
	");
	// count number of rows as total number of players in this measurement
	$num_rows = @mysqli_num_rows($Rank_q);
	while($Rank_r = @mysqli_fetch_assoc($Rank_q))
	{
		// make sure case in player search doesn't matter
		$SoldierNameRank = strtolower($Rank_r['SoldierName']);
		$SoldierMatch = strtolower($player_name);
		$count++;
		// if player name in rank row matches player of interest
		if($SoldierNameRank == $SoldierMatch)
		{
			$match = 1;
			echo '<td width="' . $width . '%" class="tablecontents" style="text-align:left">&nbsp; &nbsp; ' . $count . '<font class="information"> / </font>' . $num_rows . '</td>';
			break;
		}
	}
	// free up player rank query memory
	@mysqli_free_result($Rank_q);
	// in case no rank match was found, display error (this shouldn't happen)
	if($match == 0)
	{
		echo '<td width="' . $width . '%" class="tablecontents" style="text-align:left">&nbsp; &nbsp; error</td>';
	}
}
// function to create and display scoreboard
function scoreboard($server_ID, $server_name, $mode_array, $map_array, $squad_array, $country_array, $db)
{
	echo'
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>Scoreboard</b></th>
	</tr>
	<tr>
	<td>
	';
	// query for player in server and order them by team
	$Scoreboard_q = @mysqli_query($db,"
		SELECT `TeamID`
		FROM tbl_currentplayers
		WHERE `ServerID` = {$server_ID}
		ORDER BY `TeamID` ASC
	");
	if(@mysqli_num_rows($Scoreboard_q) == 0)
	{
		// initialize values
		$mode_name = 'Unknown';
		$map_name = 'Unknown';
		$mode = 'Unknown';
		// figure out current game mode and map name
		$Basic_q = @mysqli_query($db,"
			SELECT `mapName`, `Gamemode`, `maxSlots`, `usedSlots`, `ServerName`
			FROM tbl_server
			WHERE `ServerID` = {$server_ID}
		");
		// information was found
		if(@mysqli_num_rows($Basic_q) != 0)
		{
			$Basic_r = @mysqli_fetch_assoc($Basic_q);
			$used_slots = $Basic_r['usedSlots'];
			$available_slots = $Basic_r['maxSlots'];
			$name = substr($Basic_r['ServerName'],0,25) . ' ...';
			$mode = $Basic_r['Gamemode'];
			// convert mode to friendly name
			$mode_name = array_search($Basic_r['Gamemode'],$mode_array);
			$map = $Basic_r['mapName'];
			// convert map to friendly name
			$map_name = array_search($map,$map_array);
			// compile map image
			$map_img = './images/maps/' . $map . '.png';
			echo '
			<div class="innercontent">
			<table width="98%" align="center" border="0" class="prettytable">
			<tr>
			<td class="mapimage" style="background-image: url(' . $map_img . ');">
			<div class="simplecontent">
			<table width="80%" align="center" border="0">
			<tr>
			<td width="10%" style="text-align:left"><br/><br/>&nbsp;<br/><br/></td>
			<td width="22%" style="text-align:left"><br/><br/><font class="information">Current Game Mode:</font><br/><br/></td>
			<td width="22%" style="text-align:left"><br/><br/>' . $mode_name . '<br/><br/></td>
			<td width="22%" style="text-align:left"><br/><br/><font class="information">Current Map:</font><br/><br/></td>
			<td width="22%" style="text-align:left"><br/><br/>' . $map_name . '<br/><br/></td>
			</tr>
			<tr>
			<td width="10%" style="text-align:left">&nbsp;<br/><br/></td>
			<td width="22%" style="text-align:left"><font class="information">Server Name:</font><br/><br/><br/></td>
			<td width="22%" style="text-align:left">' . $name . '<br/><br/><br/></td>
			<td width="22%" style="text-align:left"><font class="information">Server Slots:</font><br/><br/><br/></td>
			<td width="22%" style="text-align:left">' . $used_slots . ' <font class="information">/</font> ' . $available_slots . '<br/><br/><br/></td>
			</tr>
			</table>
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
			<div class="innercontent">
			<table width="98%" align="center" border="0">
			<tr>
			<td>
			<table width="80%" align="center" border="0">
			<tr>
			<td width="10%" style="text-align:left"><br/><br/>&nbsp;<br/><br/></td>
			<td width="22%" style="text-align:left"><br/><br/><font class="information">Current Game Mode:</font><br/><br/></td>
			<td width="22%" style="text-align:left"><br/><br/>Unknown<br/><br/></td>
			<td width="22%" style="text-align:left"><br/><br/><font class="information">Current Map:</font><br/><br/></td>
			<td width="22%" style="text-align:left"><br/><br/>Unknown<br/><br/></td>
			</tr>
			<tr>
			<td width="10%" style="text-align:left">&nbsp;<br/><br/></td>
			<td width="22%" style="text-align:left"><font class="information">Server Name:</font><br/><br/><br/></td>
			<td width="22%" style="text-align:left">Unknown<br/><br/><br/></td>
			<td width="22%" style="text-align:left"><font class="information">Server Slots:</font><br/><br/><br/></td>
			<td width="22%" style="text-align:left">Unknown<br/><br/><br/></td>
			</tr>
			</table>
			</td>
			</tr>
			</table>
			</div>
			';
		}
		// free up basic query memory
		@mysqli_free_result($Basic_q);
	}
	else
	{
		echo '
		<div class="innercontent">
		<table width="98%" align="center" border="0">
		';
		// initialize values
		$mode_name = 'Unknown';
		$map_name = 'Unknown';
		$mode = 'Unknown';
		$count2 = 0;
		// figure out current game mode and map name
		$Basic_q = @mysqli_query($db,"
			SELECT `mapName`, `Gamemode`, `maxSlots`, `usedSlots`, `ServerName`
			FROM tbl_server
			WHERE `ServerID` = {$server_ID}
		");
		if(@mysqli_num_rows($Basic_q) != 0)
		{
			$Basic_r = @mysqli_fetch_assoc($Basic_q);
			$used_slots = $Basic_r['usedSlots'];
			$available_slots = $Basic_r['maxSlots'];
			$name = substr($Basic_r['ServerName'],0,25) . ' ...';
			$mode = $Basic_r['Gamemode'];
			// convert mode to friendly name
			$mode_name = array_search($Basic_r['Gamemode'],$mode_array);
			$map = $Basic_r['mapName'];
			// convert map to friendly name
			$map_name = array_search($map,$map_array);
			// compile map image
			$map_img = './images/maps/' . $map . '.png';
			echo '
			<tr>
			<td colspan="2" class="mapimage" style="background-image: url(' . $map_img . ');">
			<div class="simplecontent">
			';
		}
		else
		{
			echo '<tr><td colspan="2"><div>';
		}
		// initialize values
		$mode_shown = 0;
		$last_team = -1;
		while($Scoreboard_r = @mysqli_fetch_assoc($Scoreboard_q))
		{
			$this_team = $Scoreboard_r['TeamID'];
			if($this_team != $last_team)
			{
				if($this_team == 3)
				{
					echo '</tr><tr><td colspan="2">&nbsp;</td></tr><tr>';
				}
				// only show the header information once
				if($mode_shown == 0)
				{
					if(@mysqli_num_rows($Basic_q) != 0)
					{
						echo '
						<table width="80%" align="center" border="0">
						<tr>
						<td width="10%" style="text-align:left"><br/><br/>&nbsp;<br/><br/></td>
						<td width="22%" style="text-align:left"><br/><br/><font class="information">Current Game Mode:</font><br/><br/></td>
						<td width="22%" style="text-align:left"><br/><br/>' . $mode_name . '<br/><br/></td>
						<td width="22%" style="text-align:left"><br/><br/><font class="information">Current Map:</font><br/><br/></td>
						<td width="22%" style="text-align:left"><br/><br/>' . $map_name . '<br/><br/></td>
						</tr>
						<tr>
						<td width="10%" style="text-align:left">&nbsp;<br/><br/></td>
						<td width="22%" style="text-align:left"><font class="information">Server Name:</font><br/><br/><br/></td>
						<td width="22%" style="text-align:left">' . $name . '<br/><br/><br/></td>
						<td width="22%" style="text-align:left"><font class="information">Server Slots:</font><br/><br/><br/></td>
						<td width="22%" style="text-align:left">' . $used_slots . ' <font class="information">/</font> ' . $available_slots . '<br/><br/><br/></td>
						</tr>
						</table>
						</div>
						</td>
						</tr>
						<tr>
						';
					}
					// an error occured
					// display blank information
					else
					{
						echo '
						<table width="80%" align="center" border="0">
						<tr>
						<td width="10%" style="text-align:left"><br/><br/>&nbsp;<br/><br/></td>
						<td width="22%" style="text-align:left"><br/><br/><font class="information">Current Game Mode:</font><br/><br/></td>
						<td width="22%" style="text-align:left"><br/><br/>Unknown<br/><br/></td>
						<td width="22%" style="text-align:left"><br/><br/><font class="information">Current Map:</font><br/><br/></td>
						<td width="22%" style="text-align:left"><br/><br/>Unknown<br/><br/></td>
						</tr>
						<tr>
						<td width="10%" style="text-align:left">&nbsp;<br/><br/></td>
						<td width="22%" style="text-align:left"><font class="information">Server Name:</font><br/><br/><br/></td>
						<td width="22%" style="text-align:left">Unknown<br/><br/><br/></td>
						<td width="22%" style="text-align:left"><font class="information">Server Slots:</font><br/><br/><br/></td>
						<td width="22%" style="text-align:left">Unknown<br/><br/><br/></td>
						</tr>
						</table>
						</div>
						</td>
						</tr>
						<tr>
						';
					}
					$mode_shown = 1;
				}
				// change team name shown depending on team number
				if($this_team == 0)
				{
					$team_name = 'Loading In';
				}
				else
				{
					if($mode == 'RushLarge0')
					{
						if($this_team == 1)
						{
							if(($map == 'MP_Abandoned') OR ($map == 'MP_Damage') OR ($map == 'MP_Journey') OR ($map == 'MP_TheDish'))
							{
								$team_name = 'RU Attackers';
							}
							elseif(($map == 'MP_Flooded') OR ($map == 'MP_Naval') OR ($map == 'MP_Prison') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_Tremors'))
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
							if($map == 'MP_Abandoned')
							{
								$team_name = 'US Defenders';
							}
							elseif(($map == 'MP_Damage') OR ($map == 'MP_Flooded') OR ($map == 'MP_Journey') OR ($map == 'MP_Naval') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_TheDish') OR ($map == 'MP_Tremors'))
							{
								$team_name = 'CN Defenders';
							}
							elseif($map == 'MP_Prison')
							{
								$team_name = 'RU Defenders';
							}
							else
							{
								$team_name = 'Defenders';
							}
						}
						else
						{
							$team_name = 'Team ' . $this_team;
						}
					}
					elseif(($mode == 'ConquestLarge0') OR ($mode == 'ConquestSmall0') OR ($mode == 'Domination0') OR ($mode == 'Elimination0') OR ($mode == 'Obliteration') OR ($mode == 'TeamDeathMatch0'))
					{
						if($this_team == 1)
						{
							if(($map == 'MP_Abandoned') OR ($map == 'MP_Damage') OR ($map == 'MP_Journey') OR ($map == 'MP_TheDish'))
							{
								$team_name = 'RU Army';
							}
							elseif(($map == 'MP_Flooded') OR ($map == 'MP_Naval') OR ($map == 'MP_Prison') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_Tremors'))
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
							elseif(($map == 'MP_Damage') OR ($map == 'MP_Flooded') OR ($map == 'MP_Journey') OR ($map == 'MP_Naval') OR ($map == 'MP_Resort') OR ($map == 'MP_Siege') OR ($map == 'MP_TheDish') OR ($map == 'MP_Tremors'))
							{
								$team_name = 'CN Army';
							}
							elseif($map == 'MP_Prison')
							{
								$team_name = 'RU Army';
							}
							else
							{
								$team_name = 'RU Army';
							}
						}
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
				if($this_team == 0)
				{
					echo '<td valign="top" colspan="2">';
				}
				else
				{
					echo '<td valign="top" class="prettytable">';
				}
				if($this_team != 0)
				{
					// query for scores
					$Score_q = @mysqli_query($db,"
						SELECT `Score`, `WinningScore`
						FROM `tbl_teamscores`
						WHERE `ServerID` = {$server_ID}
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
								echo '<b><font class="teamname">' . $team_name . '</font></b> &nbsp; <font class="information">Tickets Remaining:</font> ' . $Score;
							}
							else
							{
								echo '<b><font class="teamname">' . $team_name . '</font></b> &nbsp; <font class="information">Tickets:</font> ' . $Score . '<font class="information">/</font>' . $WinningScore;
							}
						}
					}
					// an error occured
					// display blank information
					else
					{
						echo '<b><font class="teamname">' . $team_name . '</font></b>';
					}
				}
				echo '
				<table width="100%" align="center" border="0" class="prettytable">
				<tr>
				';
				// change team color depending...
				if($this_team == 0)
				{
					echo '
					<th width="15%" style="text-align:left">' . $team_name . '</th>
					<th width="40%" colspan="3" style="text-align:left">Player</th>
					';
				}
				else
				{
					echo '
					<th width="5%" style="text-align:left">#</th>
					<th width="51%" colspan="2" style="text-align:left">Player</th>
					';
				}
				// if player is loading in, don't show the score, kills, deaths, or squad name headers
				if($this_team != 0)
				{
					echo'
					<th width="10%" style="text-align:left">Score</th>
					<th width="10%" style="text-align:left">Kills</th>
					<th width="10%" style="text-align:left">Deaths</th>
					<th width="14%" style="text-align:left">Squad</th>
					';
				}
				echo'</tr>';
				// query all players on this team
				$Team_q = @mysqli_query($db,"
					SELECT `Soldiername`, `Score`, `Kills`, `Deaths`, `TeamID`, `SquadID`, `CountryCode`
					FROM tbl_currentplayers
					WHERE ServerID = {$server_ID}
					AND `TeamID` = {$this_team}
					ORDER BY `Score` Desc
				");
				if(@mysqli_num_rows($Team_q) != 0)
				{
					$count = 1;
					while($Team_r = @mysqli_fetch_assoc($Team_q))
					{
						$player = $Team_r['Soldiername'];
						// see if this player has server stats in this server yet
						$PlayerID_q = @mysqli_query($db,"
							SELECT tpd.PlayerID
							FROM tbl_playerstats tps
							INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID
							INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID
							WHERE tsp.ServerID = {$server_ID}
							AND SoldierName = '{$player}'
						");
						// server stats found for this player in this server
						if(@mysqli_num_rows($PlayerID_q) != 0)
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
						// convert squad name and country name to friendly names
						$squad_name = array_search($squad,$squad_array);
						$country = strtoupper($Team_r['CountryCode']);
						$country_name = array_search($country,$country_array);
						// compile country flag image
						if(($country == '') OR ($country == '--'))
						{
							$country_img = './images/flags/none.png';
						}
						else
						{
							$country_img = './images/flags/' . strtolower($country) . '.png';	
						}
						echo '
						<tr>
						<td class="tablecontents" width="5%" style="text-align:left"><font class="information">' . $count . '</font></td>
						';
						// if this player has stats in this server, provide a link to their stats page
						if($PlayerID != null)
						{
							echo '<td class="tablecontents" width="26%" style="text-align:left"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $server_ID . '&amp;PlayerID=' . $PlayerID . '&amp;search=1">' . $player . '</a></td>';
						}
						// otherwise just display their name without a link
						else
						{
							echo '<td class="tablecontents" width="26%" style="text-align:left">' . $player . '</td>';
						}
						echo '
						<td class="tablecontents" width="26%" style="text-align:left"><img src="' . $country_img . '" alt="' . $country_name . '"/> ' . $country_name . '</td>
						';
						// if player is loading in, don't show the score, kills, deaths, or squad name
						if($this_team != 0)
						{
							echo '
							<td class="tablecontents" width="10%" style="text-align:left">' . $score . '</td>
							<td class="tablecontents" width="10%" style="text-align:left">' . $kills . '</td>
							<td class="tablecontents" width="10%" style="text-align:left">' . $deaths . '</td>
							<td class="tablecontents" width="14%" style="text-align:left">' . $squad_name . '</td>
							';
						}
						$count++;
						echo '</tr>';
					}
				}
				echo '</table></td>';
				if($this_team == 0)
				{
					echo '</tr><tr><td colspan="2">&nbsp;</td></tr><tr>';
				}
			}
			$last_team = $this_team;
		}
		// free up player ID query memory
		@mysqli_free_result($PlayerID_q);
		// free up team query memory
		@mysqli_free_result($Team_q);
		echo '
		</tr>
		</table>
		<br/>
		</div>
		';
	}
	// free up basic query memory
	@mysqli_free_result($Basic_q);
	// free up score board query memory
	@mysqli_free_result($Scoreboard_q);
	echo '
	</td></tr>
	</table>
	</div>
	';
}
// function to replace dangerous characters in content
function textcleaner($content)
{
	$content = preg_replace("/&/","&amp;",$content);
	$content = preg_replace("/'/","&#39;",$content);
	$content = preg_replace("/</","&lt;",$content);
	$content = preg_replace("/>/","&gt;",$content);
	return $content;
}
// function to reverse cleaning operation
function textuncleaner($content)
{
	$content = preg_replace("/&#39;/","'",$content);
	$content = preg_replace("/&lt;/","<",$content);
	$content = preg_replace("/&gt;/",">",$content);
	$content = preg_replace("/&amp;/","&",$content);
	return $content;
}
?>