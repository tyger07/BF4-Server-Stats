<?php
// functions for server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// function to find player's weapon stats
function Statsout($headingprint, $damagetype, $PlayerID, $ServerID, $BF4stats)
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
			$rank = 'Kills';
		}
	}
	// set default if no rank provided in URL
	else
	{
		$rank = 'Kills';
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
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		// see if this player has used this category's weapons
		$Weapon_q = @mysqli_query($BF4stats,"
			SELECT tws.`Friendlyname`, wa.`Kills`, wa.`Deaths`, wa.`Headshots`, wa.`WeaponID`, (wa.`Headshots`/wa.`Kills`) AS HSR
			FROM `tbl_weapons_stats` wa
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`PlayerID` = {$PlayerID}
			AND tws.`Damagetype` = '{$damagetype}'
			AND wa.`Kills` > 0
			ORDER BY {$rank} {$order}
		");
	}
	// or else this is a global stats page
	else
	{
		// see if this player has used this category's weapons
		$Weapon_q = @mysqli_query($BF4stats,"
			SELECT tws.`Friendlyname`, SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, wa.`WeaponID`, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
			FROM `tbl_weapons_stats` wa
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND tws.`Damagetype` = '{$damagetype}'
			AND wa.`Kills` > 0
			GROUP BY tws.`Friendlyname`
			ORDER BY {$rank} {$order}
		");
	}
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
		<td width="1%" style="text-align:left">&nbsp;</td>
		';
		// if server id is not null, this is a server query
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="22%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $PlayerID . '&amp;search=1&amp;rank=Friendlyname&amp;order=';
		}
		// otherwise it is a global player query
		else
		{
			echo '<td width="21%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $PlayerID . '&amp;rank=Friendlyname&amp;order=';
		}
		if($rank != 'Friendlyname')
		{
			echo 'ASC"><span class="orderheader">Weapon Name</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Weapon Name</span></a></td>';
		}
		// if server id is not null, this is a server query
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="19%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $PlayerID . '&amp;search=1&amp;rank=Kills&amp;order=';
		}
		// otherwise it is a global player query
		else
		{
			echo '<td width="19%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $PlayerID . '&amp;rank=Kills&amp;order=';
		}
		if($rank != 'Kills')
		{
			echo 'DESC"><span class="orderheader">Kills</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Kills</span></a></td>';
		}
		// if server id is not null, this is a server query
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="19%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $PlayerID . '&amp;search=1&amp;rank=Deaths&amp;order=';
		}
		// otherwise it is a global player query
		else
		{
			echo '<td width="19%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $PlayerID . '&amp;rank=Deaths&amp;order=';
		}
		if($rank != 'Deaths')
		{
			echo 'DESC"><span class="orderheader">Deaths</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Deaths</span></a></td>';
		}
		// if server id is not null, this is a server query
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="19%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $PlayerID . '&amp;search=1&amp;rank=Headshots&amp;order=';
		}
		// otherwise it is a global player query
		else
		{
			echo '<td width="19%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $PlayerID . '&amp;rank=Headshots&amp;order=';
		}
		if($rank != 'Headshots')
		{
			echo 'DESC"><span class="orderheader">Headshots</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Headshots</span></a></td>';
		}
		// if server id is not null, this is a server query
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="20%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $PlayerID . '&amp;search=1&amp;rank=HSR&amp;order=';
		}
		// otherwise it is a global player query
		else
		{
			echo '<td width="20%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $PlayerID . '&amp;rank=HSR&amp;order=';
		}
		if($rank != 'HSR')
		{
			echo 'DESC"><span class="orderheader">Headshot Ratio</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Headshot Ratio</span></a></td>';
		}
		echo '</tr>';
		while($Weapon_r = @mysqli_fetch_assoc($Weapon_q))
		{
			$weapon_name_displayed = preg_replace("/_/"," ",$Weapon_r['Friendlyname']);
			$weapon_img = './images/weapons/' . $Weapon_r['Friendlyname'] . '.png';
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
			echo '
			<tr>
			<td width="1%" style="text-align:left">&nbsp;</td>
			<td width="22%" class="tablecontents"  style="text-align: left;"><table width="100%" border="0"><tr><td width="120px"><img src="'. $weapon_img . '" alt="' . $weapon_name_displayed . '" /></td><td style="text-align: left;" valign="middle"><font class="information">' . $weapon_name_displayed . ':</font></td></tr></table></td>
			<td width="19%" class="tablecontents" style="text-align: left">' . $kills . '</td>
			<td width="19%" class="tablecontents" style="text-align: left">' . $deaths . '</td>
			<td width="19%" class="tablecontents" style="text-align: left">' . $headshots . '</td>
			<td width="20%" class="tablecontents" style="text-align: left">' . $ratio . ' <font class="information">%</font></td>
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
function rank($ServerID, $PlayerID, $BF4stats, $GameID)
{
	// initialize KDR rank values
	$KDRrank = 0;
	$KDRmatch = 0;
	// rank players by KDR
	$KDR_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`PlayerID`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
			ORDER BY (tps.`Kills`/tps.`Deaths`) DESC, tpd.`SoldierName` ASC
	");
	// loop through the list until this player's ID is found
	if(@mysqli_num_rows($KDR_q) != 0)
	{
		while($KDR_r = @mysqli_fetch_assoc($KDR_q))
		{
			$KDRrank++;
			$ThisID = strtolower($KDR_r['PlayerID']);
			// if player name in rank row matches player of interest
			if($PlayerID == $ThisID)
			{
					$KDRmatch = 1;
					break;
			}
		}
	}
	// free up KDR rank query memory
	@mysqli_free_result($KDR_q);
	
	// initialize HSR rank values
	$HSRrank = 0;
	$HSRmatch = 0;
	// rank players by HSR
	$HSR_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`PlayerID`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
			ORDER BY (tps.`Headshots`/tps.`Kills`) DESC, tpd.`SoldierName` ASC
	");
	// loop through the list until this player's ID is found
	if(@mysqli_num_rows($HSR_q) != 0)
	{
		while($HSR_r = @mysqli_fetch_assoc($HSR_q))
		{
			$HSRrank++;
			$ThisID = strtolower($HSR_r['PlayerID']);
			// if player name in rank row matches player of interest
			if($PlayerID == $ThisID)
			{
					$HSRmatch = 1;
					break;
			}
		}
	}
	// free up HSR rank query memory
	@mysqli_free_result($HSR_q);
	
	// look for error in queries above
	if($KDRmatch == 0)
	{
		$KDRrank = 'error';
	}
	if($HSRmatch == 0)
	{
		$HSRrank = 'error';
	}
	
	// get player database ranks
	$Rank_q  = @mysqli_query($BF4stats,"
		SELECT `rankScore`, `rankKills`
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tpd.`PlayerID` = {$PlayerID}
		AND tsp.`ServerID` = {$ServerID}
	");
	// query worked
	if(@mysqli_num_rows($Rank_q) != 0)
	{
		$Rank_r = @mysqli_fetch_assoc($Rank_q);
		$ScoreRank = round($Rank_r['rankScore'],0);
		$KillsRank = round($Rank_r['rankKills'],0);
	}
	// error occured
	else
	{
		$ScoreRank = 'Unknown';
		$KillsRank = 'Unknown';
	}
	// query server stats
	$Server_q = @mysqli_query($BF4stats,"
		SELECT `CountPlayers`
		FROM `tbl_server_stats`
		WHERE `ServerID` = {$ServerID}
	");
	// query worked
	if(@mysqli_num_rows($Server_q) != 0)
	{
		$Server_r = @mysqli_fetch_assoc($Server_q);
		$Players = $Server_r['CountPlayers'];
	}
	// error occured
	else
	{
		$Players = 'Unknown';
	}
	echo '
	<td width="25%" style="text-align:left"><br/><font class="information">Score:</font> ' . $ScoreRank . '<font class="information"> / </font>' . $Players . '<br/></td>
	<td width="25%" style="text-align:center"><br/><font class="information">Kills:</font> ' . $KillsRank . '<font class="information"> / </font>' . $Players . '<br/></td>
	<td width="25%" style="text-align:center"><br/><font class="information">Kill/Death Ratio:</font> ' . $KDRrank . '<font class="information"> / </font>' . $Players . '<br/></td>
	<td width="25%" style="text-align:right"><br/><font class="information">Headshot Ratio:</font> ' . $HSRrank . '<font class="information"> / </font>' . $Players . '<br/></td>
	';
	// free up player rank query memory
	@mysqli_free_result($Rank_q);
	// free up server query memory
	@mysqli_free_result($Server_q);
}
// function to create and display scoreboard
function scoreboard($ServerID, $ServerName, $mode_array, $map_array, $squad_array, $country_array, $BF4stats, $GameID)
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
		// initialize values
		$mode_name = 'Unknown';
		$map_name = 'Unknown';
		$mode = 'Unknown';
		// figure out current game mode and map name
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
			$name = substr($Basic_r['ServerName'],0,25) . ' ...';
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
			}
			// this map is missing!
			else
			{
				$map_name = $map;
			}
			echo '
			<div class="innercontent">
			<br/>
			<table width="98%" align="center" border="0" class="prettytable">
			<tr>
			<td class="shadowcontent">
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
			</td>
			</tr>
			</table>
			<br/>
			</div>
			';
		}
		// an error occured
		// display blank information
		else
		{
			echo '
			<div class="innercontent">
			<br/>
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
			<br/>
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
		echo '
		<div class="innercontent">
		<br/>
		<table width="98%" align="center" border="0">
		';
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
			$name = substr($Basic_r['ServerName'],0,25) . ' ...';
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
			}
			// this map is missing!
			else
			{
				$map_name = $map;
			}
			echo '
			<tr>
			<td colspan="2" class="shadowcontent">
			';
		}
		else
		{
			echo '<tr><td colspan="2"><div>';
		}
		// initialize values
		$mode_shown = 0;
		$last_team = -1;
		// get current rank query details
		if(isset($_GET['rank']) AND !empty($_GET['rank']))
		{
			$rank = $_GET['rank'];
			// filter out SQL injection
			if($rank != 'Score' AND $rank != 'Kills' AND $rank != 'Deaths' AND $rank != 'SquadID')
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
		if(isset($_GET['order']) AND !empty($_GET['order']))
		{
			$order = $_GET['order'];
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
		while($Scoreboard_r = @mysqli_fetch_assoc($Scoreboard_q))
		{
			$this_team = $Scoreboard_r['TeamID'];
			// change to a different collumn or row of the scoreboard when the team number changes
			if($this_team != $last_team)
			{
				// if the game mode has more than 2 teams, the third team should be moved down to the next row of the scoreboard
				if($this_team == 3)
				{
					echo '</tr><tr><td colspan="2">&nbsp;</td></tr><tr>';
				}
				// only show the server header information once
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
				// team 0 is 'loading in'
				if($this_team == 0)
				{
					$team_name = 'Loading In';
				}
				// player is actually assigned to a team
				else
				{
					// change team name based on team number and game mode
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
						// something unexpected occured and a correct team name was not found
						// just name the team based on team number instead
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
						// something unexpected occured and a correct team name was not found
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
						// something unexpected occured and a correct team name was not found
						// just name the team based on team number instead
						else
						{
							$team_name = 'Team ' . $this_team;
						}
					}
					// something unexpected occured and a correct team name was not found
					// just name the team based on team number instead
					else
					{
						$team_name = 'Team ' . $this_team;
					}
				}
				// the player is not on a team yet, the "loading in" collumn is formatted different than the team collumns (it extends over two team collumns)
				if($this_team == 0)
				{
					echo '<td valign="top" colspan="2"><br/>';
				}
				// this is a team collumn
				else
				{
					echo '<td valign="top" class="prettytable">';
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
								echo '<br/><b><font class="teamname">' . $team_name . '</font></b> &nbsp; <font class="information">Tickets Remaining:</font> ' . $Score;
							}
							else
							{
								echo '<br/><b><font class="teamname">' . $team_name . '</font></b> &nbsp; <font class="information">Tickets:</font> ' . $Score . '<font class="information">/</font>' . $WinningScore;
							}
						}
					}
					// an error occured
					// display blank information
					else
					{
						echo '<b><font class="teamname">' . $team_name . '</font></b>';
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
					<th width="15%" style="text-align:left">' . $team_name . '</th>
					<th width="40%" colspan="3" style="text-align:left">Player</th>
					';
				}
				// this is a real team
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
					echo '<th width="10%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;rank=Score&amp;order=';
					if($rank != 'Score')
					{
						echo 'DESC"><span class="orderheader">Score</span></a></th>';
					}
					else
					{
						echo $nextorder . '"><span class="ordered' . $order . 'header">Score</span></a></th>';
					}
					echo '<th width="10%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;rank=Kills&amp;order=';
					if($rank != 'Kills')
					{
						echo 'DESC"><span class="orderheader">Kills</span></a></th>';
					}
					else
					{
						echo $nextorder . '"><span class="ordered' . $order . 'header">Kills</span></a></th>';
					}
					echo '<th width="10%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;rank=Deaths&amp;order=';
					if($rank != 'Deaths')
					{
						echo 'DESC"><span class="orderheader">Deaths</span></a></th>';
					}
					else
					{
						echo $nextorder . '"><span class="ordered' . $order . 'header">Deaths</span></a></th>';
					}
					echo '<th width="14%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;rank=SquadID&amp;order=';
					if($rank != 'SquadID')
					{
						echo 'ASC"><span class="orderheader">Squad</span></a></th>';
					}
					else
					{
						echo $nextorder . '"><span class="ordered' . $order . 'header">Squad</span></a></th>';
					}
				}
				echo'</tr>';
				// query for all players on this team
				$Team_q = @mysqli_query($BF4stats,"
					SELECT `Soldiername`, `Score`, `Kills`, `Deaths`, `TeamID`, `SquadID`, `CountryCode`
					FROM `tbl_currentplayers`
					WHERE `ServerID` = {$ServerID}
					AND `TeamID` = {$this_team}
					ORDER BY {$rank} {$order}
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
						echo '
						<tr>
						<td class="tablecontents" width="5%" style="text-align:left"><font class="information">' . $count . ':</font></td>
						';
						// if this player has stats in this server, provide a link to their stats page
						if($PlayerID != null)
						{
							echo '<td class="tablecontents" width="26%" style="text-align:left"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $PlayerID . '&amp;search=1">' . $player . '</a></td>';
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
				// no players were found on this team!
				// some sort of database error must have occured
				// this is bad..
				// playing damage control
				else
				{
					echo '
					<tr>
					<td class="tablecontents" width="5%" style="text-align:left">&nbsp;</td>
					<td class="tablecontents" width="95%" style="text-align:left" colspan="5"><font class="information">An error occured!</font></td>
					</tr>
					';
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
// function to make player stats signature image
function signature($PlayerID, $FAV, $clan_name, $BF4stats, $GameID)
{
	// initialize defaults
	$found = 0;
	
	// query for this player's info
	$q = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName`, tpd.`GlobalRank`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, SUM(tps.`Rounds`) AS Rounds, SUM(tps.`Headshots`) AS Headshots, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tpd.`PlayerID` = {$PlayerID}
		AND tpd.`GameID` = {$GameID}
	");
	if(mysqli_num_rows($q) == 1)
	{
		$found = 1;
		$r = @mysqli_fetch_assoc($q);
		$rank = $r['GlobalRank'];
		$rank_img = './images/ranks/r' . $r['GlobalRank'] . '.png';
		$soldier = $r['SoldierName'];
		$score = $r['Score'];
		$kills = $r['Kills'];
		$deaths = $r['Deaths'];
		$kdr = round($r['KDR'],2);
		$rounds = $r['Rounds'];
		$headshots = $r['Headshots'];
		$hsr = round(($r['HSR']*100),2);
		// query for this player's weapon stats
		$wq = @mysqli_query($BF4stats,"
			SELECT tw.`Friendlyname`, SUM(tws.`Kills`) AS weaponKills
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_weapons_stats` tws ON tws.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_weapons` tw ON tw.`WeaponID` = tws.`WeaponID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND (tw.`Damagetype` = 'assaultrifle' OR tw.`Damagetype` = 'lmg' OR tw.`Damagetype` = 'shotgun' OR tw.`Damagetype` = 'smg' OR tw.`Damagetype` = 'sniperrifle' OR tw.`Damagetype` = 'handgun' OR tw.`Damagetype` = 'projectileexplosive' OR tw.`Damagetype` = 'explosive' OR tw.`Damagetype` = 'melee' OR tw.`Damagetype` = 'none' OR tw.`Damagetype` = 'carbine' OR tw.`Damagetype` = 'dmr' OR tw.`Damagetype` = 'impact')
			AND tpd.`GameID` = {$GameID}
			GROUP BY tw.`Friendlyname`
			ORDER BY weaponKills DESC
			LIMIT 1
		");
		// are there weapon stats?
		if(mysqli_num_rows($wq) != 0)
		{
			$wr = @mysqli_fetch_assoc($wq);
			$weapon = preg_replace("/_/"," ",$wr['Friendlyname']);
			// rename 'death'
			if($weapon == 'Death')
			{
				$weapon = 'Machinery';
			}
			$weapon_img = './images/weapons/' . $wr['Friendlyname'] . '.png';
			$weapon_kills = $wr['weaponkills'];
		}
		// or else default
		else
		{
			$rank_img = './images/ranks/missing.png';
			$weapon_img = './images/weapons/missing.png';
			$weapon = 'Unknown';
			$weapon_kills = 'Unknown';
		}
	}
	else
	{
		$rank_img = './images/ranks/missing.png';
		$weapon_img = './images/weapons/missing.png';
	}
	
	// free up player info query memory
	@mysqli_free_result($q);
	
	// free up weapon query memory
	@mysqli_free_result($wq);
	
	// base image
	$base = imagecreatefrompng('./signature/images/background.png');
	
	// text color
	$light = imagecolorallocate($base, 255, 255, 255);
	$yellow = imagecolorallocate($base, 252, 199, 66);
	$dark = imagecolorallocate($base, 200, 200, 190);
	
	// add clan name text
	imagestring($base, 2, 220, 17, $clan_name . '\'s Servers', $dark);
	
	// default is rank
	if($FAV == 0)
	{
		// rank image
		$rank = imagecreatefrompng($rank_img);
		
		// copy the rank image onto the background image
		imagecopy($base, $rank, 0, 2, 0, 0, 94, 94);
		$white = imagecolorallocate($rank, 255, 255, 255);
		imagecolortransparent($base, $white);
		imagealphablending($base, false);
		imagesavealpha($base, true);
	}
	// otherwise use weapon
	else
	{
		// weapon image
		$rank = imagecreatefrompng($weapon_img);
		
		// copy the rank image onto the background image
		imagecopy($base, $rank, 0, 20, 0, 0, 94, 56);
		$white = imagecolorallocate($rank, 255, 255, 255);
		imagecolortransparent($base, $white);
		imagealphablending($base, false);
		imagesavealpha($base, true);
	}
	
	// if this soldier was found...
	if($found == 1)
	{
		// add text to image
		imagestring($base, 2, 110, 17, $soldier, $light);
		imagestring($base, 1, 130, 40, 'Score:', $yellow);
		imagestring($base, 1, 170, 40, $score, $yellow);
		imagestring($base, 1, 130, 50, 'Kills:', $yellow);
		imagestring($base, 1, 170, 50, $kills, $yellow);
		imagestring($base, 1, 130, 60, 'Deaths:', $yellow);
		imagestring($base, 1, 170, 60, $deaths, $yellow);
		imagestring($base, 1, 130, 70, 'KDR:', $yellow);
		imagestring($base, 1, 170, 70, $kdr, $yellow);
		imagestring($base, 1, 230, 40, 'Favorite:', $yellow);
		imagestring($base, 1, 290, 40, $weapon, $yellow);
		imagestring($base, 1, 230, 50, 'Rounds:', $yellow);
		imagestring($base, 1, 290, 50, $rounds, $yellow);
		imagestring($base, 1, 230, 60, 'Headshots:', $yellow);
		imagestring($base, 1, 290, 60, $headshots, $yellow);
		imagestring($base, 1, 230, 70, 'HSR:', $yellow);
		imagestring($base, 1, 290, 70, $hsr . ' %', $yellow);
	}
	// this soldier was not found
	else
	{
		// add text to image
		imagestring($base, 4, 150, 40, 'This player has no stats.', $light);
	}
	
	// compile image
	imagepng($base, './signature/cache/PID' . $PlayerID . 'FAV' . $FAV . '.png');
	imagedestroy($base);
}
?>