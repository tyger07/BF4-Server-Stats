<?php
// server stats player page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// no soldiername input from GET
if($SoldierName == null)
{
	echo '
	<div class="subsection">
	<div class="headline">
	Please enter a player name.
	</div>
	</div>
	';
}
// SoldierName from GET has been determined
elseif($SoldierName != null)
{
	// initialize value
	$soldier_found = 0;
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// get player stats
		$PlayerData_q = @mysqli_query($BF4stats,"
			SELECT tpd.`CountryCode`, tpd.`PlayerID`, tpd.`GlobalRank`, tps.`Suicide`, tps.`Score`, tps.`Kills`, tps.`Deaths`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR, tps.`TKs`, tps.`Headshots`, tps.`Rounds`, tps.`Killstreak`, tps.`Deathstreak`, tps.`Wins`, tps.`Losses`, (tps.`Wins`/tps.`Losses`) AS WLR, tps.`HighScore`, tps.`FirstSeenOnServer`, tps.`LastSeenOnServer`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`PlayerID` = {$PlayerID}
			AND tpd.`GameID` = {$GameID}
		");
		// was a soldier found?
		if(@mysqli_num_rows($PlayerData_q) == 1)
		{
			$soldier_found = 1;
		}
	}
	// or else this is a global stats page
	else
	{
		// find if this soldiername is in server stats
		$Find_q = @mysqli_query($BF4stats,"
			SELECT `SoldierName`
			FROM `tbl_playerdata`
			WHERE `PlayerID` = {$PlayerID}
			AND `GameID` = {$GameID}
		");
		// was a soldier found?
		if(@mysqli_num_rows($Find_q) != 0)
		{
			$soldier_found = 1;
			// get player stats
			$PlayerData_q = @mysqli_query($BF4stats,"
				SELECT tpd.`CountryCode`, tpd.`PlayerID`, tpd.`GlobalRank`, SUM(tps.`Suicide`) AS Suicide, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, SUM(tps.`TKs`) AS TKs, SUM(tps.`Headshots`) AS Headshots, SUM(tps.`Rounds`) AS Rounds, MAX(tps.`Killstreak`) AS Killstreak, MAX(tps.`Deathstreak`) AS Deathstreak, SUM(tps.`Wins`) AS Wins, SUM(tps.`Losses`) AS Losses, (SUM(tps.`Wins`)/SUM(tps.`Losses`)) AS WLR, MAX(tps.`HighScore`) AS HighScore, MIN(tps.`FirstSeenOnServer`) AS FirstSeenOnServer, MAX(tps.`LastSeenOnServer`) AS LastSeenOnServer
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
			");
		}
		// free up soldier find query memory
		@mysqli_free_result($Find_q);
	}
	// if no stats were found for player name, display this
	if($soldier_found == 0)
	{
		echo '
		<div class="subsection">
		<div class="headline">
		';
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '<span class="information">No unique player data found for "' . $SoldierName . '" in this server.</span>';
		}
		// or else this is a global stats page
		else
		{
			echo '<span class="information">No unique player data found for "' . $SoldierName . '" in these servers.</span>';
		}
		echo '
		</div>
		</div>
		<br/>
		';
		// get current rank query details
		if(!empty($rank))
		{
			// filter out SQL injection
			if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Rounds' AND $rank != 'Kills' AND $rank != 'Deaths' AND $rank != 'KDR')
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
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			// check to see if there are any players who match a similar name
			$PlayerMatch_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, tps.`Deaths`, tps.`Rounds`, (tps.`Kills`/tps.`Deaths`) AS KDR
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`SoldierName` LIKE '%{$SoldierName}%'
				AND tpd.`GameID` = {$GameID}
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT 0, 20
			");
		}
		// or else this is a global stats page
		else
		{
			// check to see if there are any players who match a similar name
			$PlayerMatch_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, SUM(tps.`Rounds`) AS Rounds, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`SoldierName` LIKE '%{$SoldierName}%'
				AND tpd.`GameID` = {$GameID}
				GROUP BY tpd.`SoldierName`
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT 0, 20
			");
		}
		// if a similar name was found, display this
		if(@mysqli_num_rows($PlayerMatch_q) != 0)
		{
			echo '
			<div class="subsection">
			<div class="headline">
			Here are some players with names similar to "' . $SoldierName . '".
			</div>
			</div>
			<br/>
			<table class="prettytable">
			<tr>
			<th width="3%" class="countheader">#</th>
			';
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=SoldierName&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=SoldierName&amp;o=';
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
				echo '<th width="15%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=Score&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=Score&amp;o=';
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
				echo '<th width="15%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=Rounds&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="15%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=Rounds&amp;o=';
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
				echo '<th width="15%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=Kills&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="15%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=Kills&amp;o=';
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
				echo '<th width="15%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=Deaths&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="15%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=Deaths&amp;o=';
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
				echo '<th width="17%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=KDR&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="17%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=KDR&amp;o=';
			}
			if($rank != 'KDR')
			{
				echo 'DESC"><span class="orderheader">KDR</span></a></th>';
			}
			else
			{
				echo $nextorder . '"><span class="ordered' . $order . 'header">KDR</span></a></th>';
			}
			echo '</tr>';
			// initialize value
			$count = 0;
			while($PlayerMatch_r = @mysqli_fetch_assoc($PlayerMatch_q))
			{
				$count++;
				$Soldier_Name = $PlayerMatch_r['SoldierName'];
				$Player_ID = $PlayerMatch_r['PlayerID'];
				$Score = $PlayerMatch_r['Score'];
				$Kills = $PlayerMatch_r['Kills'];
				$Deaths = $PlayerMatch_r['Deaths'];
				$KDR = round($PlayerMatch_r['KDR'],2);
				$Rounds = $PlayerMatch_r['Rounds'];
				echo '
				<tr>
				<td width="3%" class="count"><span class="information">' . $count . '</span></td>
				';
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					echo '<td width="20%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;pid=' . $Player_ID . '">' . $Soldier_Name . '</a></td>';
				}
				// or else this is a global stats page
				else
				{
					echo '<td width="20%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;pid=' . $Player_ID . '">' . $Soldier_Name . '</a></td>';
				}
				echo'
				<td width="15%" class="tablecontents">' . $Score . '</td>
				<td width="15%" class="tablecontents">' . $Rounds . '</td>
				<td width="15%" class="tablecontents">' . $Kills . '</td>
				<td width="15%" class="tablecontents">' . $Deaths . '</td>
				<td width="17%" class="tablecontents">' . $KDR . '</td>
				</tr>
				';
			}
			echo '
			</table>
			';
		}
		// free up player match query memory
		@mysqli_free_result($PlayerMatch_q);
	}
	// this unique player was found
	elseif($soldier_found == 1)
	{
		echo '
		<div class="subsection">
		<div class="headline">
		' . $SoldierName . '
		</div>
		</div>
		';
		// only show ranks if this is not a global stats page
		// there is no such thing as global rank in database
		// and computing it is too slow with lots of players in global
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<br/>
			<br/>
			<table class="prettytable">
			<tr>
			<th colspan="4"><center>Ranks in ' . $ServerName . '</center></th>
			</tr>
			<tr>
			';
			// get this player's ranks
			// input as: server id, soldier, db, game id
			rank($ServerID, $PlayerID, $BF4stats, $GameID);
			echo '
			</tr>
			</table>
			';
		}
		// get information from the query
		$PlayerData_r = @mysqli_fetch_assoc($PlayerData_q);
		$CountryCode = strtoupper($PlayerData_r['CountryCode']);
		// convert country name to friendly name
		// and compile flag image
		// first find out if this country name is the list of country names
		if(in_array($CountryCode,$country_array))
		{
			$country = array_search($CountryCode,$country_array);
			// compile country flag image
			// if country is null or unknown, use generic image
			if(($CountryCode == '') OR ($CountryCode == '--'))
			{
				$country_img = './images/flags/none.png';
			}
			else
			{
				$country_img = './images/flags/' . strtolower($CountryCode) . '.png';	
			}
		}
		// this country is missing!
		else
		{
			$country = $CountryCode;
			$country_img = './images/flags/none.png';
		}
		// continue getting information from the query
		$Suicides = $PlayerData_r['Suicide'];
		$Score = $PlayerData_r['Score'];
		$Kills = $PlayerData_r['Kills'];
		$Deaths = $PlayerData_r['Deaths'];
		$Headshots = $PlayerData_r['Headshots'];
		$HSpercent = round(($PlayerData_r['HSR']*100),2);
		$Rounds = $PlayerData_r['Rounds'];
		$Killstreak = $PlayerData_r['Killstreak'];
		$Deathstreak = $PlayerData_r['Deathstreak'];
		$KDR = round($PlayerData_r['KDR'],2);
		$TKs = $PlayerData_r['TKs'];
		$Wins = $PlayerData_r['Wins'];
		$Losses = $PlayerData_r['Losses'];
		$WLR = round(($PlayerData_r['WLR']*100),2);
		$HighScore = $PlayerData_r['HighScore'];
		$FirstSeen = date("M d Y", strtotime($PlayerData_r['FirstSeenOnServer']));
		$LastSeen = date("M d Y", strtotime($PlayerData_r['LastSeenOnServer']));
		$PlayerID = $PlayerData_r['PlayerID'];
		$rank = $PlayerData_r['GlobalRank'];
		// filter out the available ranks
		if($rank >= $rank_min && $rank <= $rank_max)
		{
			$rank_img = './images/ranks/r' . $rank . '.png';
		}
		else
		{
			$rank_img = './images/ranks/missing.png';
		}
		echo '
		<br/>
		<br/>
		<table class="prettytable">
		<tr>
		<th width="15%" style="text-align: center;"><img src="' . $rank_img . '" alt="' . $rank . '"/></th>
		<th width="85%" colspan="5"><div class="headline">Overview</div></th>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Country</th>
		<td width="18%" class="tablecontents"><img src="' . $country_img . '" alt="' . $country_name . '"/> ' . $country . '<span class="information"> (</span>' . $CountryCode . '<span class="information">)</span></td>
		<th width="15%" style="padding-left: 10px;">First Visit</th>
		<td width="18%" class="tablecontents">' . $FirstSeen . '</td>
		<th width="15%" style="padding-left: 10px;">Last Visit</th>
		<td width="18%" class="tablecontents">' . $LastSeen . '</td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Kills</th>
		<td width="18%" class="tablecontents">' . $Kills . '</td>
		<th width="15%" style="padding-left: 10px;">Deaths</th>
		<td width="18%" class="tablecontents">' . $Deaths . '</td>
		<th width="15%" style="padding-left: 10px;">KDR</th>
		<td width="18%" class="tablecontents">' . $KDR . '</td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Suicides</th>
		<td width="18%" class="tablecontents">' . $Suicides . '</td>
		<th width="15%" style="padding-left: 10px;">Headshots</th>
		<td width="18%" class="tablecontents">' . $Headshots . '</td>
		<th width="15%" style="padding-left: 10px;">Headshot Percent</th>
		<td width="18%" class="tablecontents">' . $HSpercent . '<span class="information"> %</span></td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Wins</th>
		<td width="18%" class="tablecontents">' . $Wins . '</td>
		<th width="15%" style="padding-left: 10px;">Losses</th>
		<td width="18%" class="tablecontents">' . $Losses . '</td>
		<th width="15%" style="padding-left: 10px;">Wins / Losses</th>
		<td width="18%" class="tablecontents">' . $WLR . '<span class="information"> %</span></td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Kill-streak</th>
		<td width="18%" class="tablecontents">' . $Killstreak . '</td>
		<th width="15%" style="padding-left: 10px;">Death-streak</th>
		<td width="18%" class="tablecontents">' . $Deathstreak . '</td>
		<th width="15%" style="padding-left: 10px;">Team Kills</th>
		<td width="18%" class="tablecontents">' . $TKs . '</td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Total Score</th>
		<td width="18%" class="tablecontents">' . $Score . '</td>
		<th width="15%" style="padding-left: 10px;">High Score</th>
		<td width="18%" class="tablecontents">' . $HighScore . '</td>
		<th width="15%" style="padding-left: 10px;">Rounds Played</th>
		<td width="18%" class="tablecontents">' . $Rounds . '</td>
		</tr>
		</table>
		<br/>
		';
		// get weapon stats for weapon graph
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$Weapon_q = @mysqli_query($BF4stats,"
				SELECT tws.`Friendlyname`, wa.`Kills`, wa.`Deaths`, wa.`Headshots`, (wa.`Headshots`/wa.`Kills`) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND wa.`Kills` > 0
				AND (tws.`Damagetype` = 'assaultrifle' OR tws.`Damagetype` = 'lmg' OR tws.`Damagetype` = 'shotgun' OR tws.`Damagetype` = 'smg' OR tws.`Damagetype` = 'sniperrifle' OR tws.`Damagetype` = 'handgun' OR tws.`Damagetype` = 'projectileexplosive' OR tws.`Damagetype` = 'explosive' OR tws.`Damagetype` = 'melee' OR tws.`Damagetype` = 'none' OR tws.`Damagetype` = 'carbine' OR tws.`Damagetype` = 'dmr' OR tws.`Damagetype` = 'impact')
			");
		}
		// or else this is a global stats page
		else
		{
			$Weapon_q = @mysqli_query($BF4stats,"
				SELECT tws.`Friendlyname`, SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, wa.`WeaponID`, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND wa.`Kills` > 0
				AND (tws.`Damagetype` = 'assaultrifle' OR tws.`Damagetype` = 'lmg' OR tws.`Damagetype` = 'shotgun' OR tws.`Damagetype` = 'smg' OR tws.`Damagetype` = 'sniperrifle' OR tws.`Damagetype` = 'handgun' OR tws.`Damagetype` = 'projectileexplosive' OR tws.`Damagetype` = 'explosive' OR tws.`Damagetype` = 'melee' OR tws.`Damagetype` = 'none' OR tws.`Damagetype` = 'carbine' OR tws.`Damagetype` = 'dmr' OR tws.`Damagetype` = 'impact')
				GROUP BY tws.`Friendlyname`
			");
		}
		if(@mysqli_num_rows($Weapon_q) != 0)
		{
			echo '
			<br/>
			<table class="prettytable">
			<tr>
			<td class="tablecontents">
			<script type="text/javascript" src="//www.google.com/jsapi"></script>
			<script type="text/javascript">
				google.load(\'visualization\', \'1\', {packages: [\'corechart\']});
			</script>
			<script type="text/javascript">
				function drawVisualization() {
					// Create and populate the data table.
					var data = google.visualization.arrayToDataTable([
						[\'Weapon\', \'Kills\', \'Deaths\', \'Headshots\',  \'Headshot Ratio\']
						';
						$vmax = 0;
						$hmax = 0;
						while($Weapon_r = @mysqli_fetch_assoc($Weapon_q))
						{
							$weapon = $Weapon_r['Friendlyname'];
							// rename 'Death'
							if($weapon == 'Death')
							{
								$weapon = 'Machinery';
							}
							// convert weapon to friendly name
							if(in_array($weapon,$weapon_array))
							{
								$weapon = array_search($weapon,$weapon_array);
							}
							// this weapon is missing!
							else
							{
								$weapon = preg_replace("/_/"," ",$weapon);
							}
							$deaths = $Weapon_r['Deaths'];
							$kills = $Weapon_r['Kills'];
							$headshots = $Weapon_r['Headshots'];
							$hsr = $Weapon_r['HSR'];
							// set max values higher than max for cropping reasons
							// find the vmax value
							if($deaths > $vmax)
							{
								$vmax = $deaths + 50;
							}
							// find the hmax value
							if($kills > $hmax)
							{
								$hmax = $kills + 50;
							}
							echo ',
							[\'' . $weapon . '\', ' . $kills . ', ' . $deaths . ', ' . $headshots . ',  ' . $hsr * 100 . ']
							';
						}
						echo '
					]);
					var options = {
						title: \'Headshots\',
						titleTextStyle: {color: \'#666\', bold: \'false\'},
						legend: {textStyle: {color: \'#666\'}},
						hAxis: {title: \'Kills\', titleTextStyle: {color: \'#666\'}, textStyle:  {color: \'transparent\'}, minValue: -1, maxValue: ' . $hmax . ', gridlines: {color: \'transparent\'}, baselineColor:  \'#333\'},
						vAxis: {title: \'Deaths\', titleTextStyle: {color: \'#666\'}, textStyle:  {color: \'transparent\'}, minValue: -1, maxValue: ' . $vmax . ', gridlines: {color: \'transparent\'}, baselineColor:  \'#333\'},
						bubble: {textStyle: {color: \'#888\', fontSize: 12}, stroke: \'transparent\'},
						backgroundColor: \'transparent\',
						chartArea: {left: 20, top: 50, width: "90%", height: "70%"},
						colorAxis: {minValue: 0, colors: [\'#333333\', \'#640000\'], legend: {textStyle: {color: \'#666\'}, position: \'top\'}},
						sizeAxis: {minValue: 0, maxValue: 1, minSize: 30, maxSize: 50},
						tooltip: {textStyle: {color: \'#AA0000\'}}
					};
					// Create and draw the visualization.
					var chart = new google.visualization.BubbleChart(
					document.getElementById(\'visualization\'));
					chart.draw(data, options);
				}
				google.setOnLoadCallback(drawVisualization);
			</script>
			<br/>
			<center>
			<div id="visualization" class="embed"></div>
			</center>
			<br/>
			</td>
			</tr>
			</table>
			<br/>
			';
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				// find out which weapons this player has stats for
				$Weapon_q2 = @mysqli_query($BF4stats,"
					SELECT tws.`Damagetype`, SUM(wa.`Kills`) AS Kills
					FROM `tbl_weapons_stats` wa
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
					WHERE tsp.`ServerID` = {$ServerID}
					AND tpd.`PlayerID` = {$PlayerID}
					AND wa.`Kills` > 0
					AND (tws.`Damagetype` = 'assaultrifle' OR tws.`Damagetype` = 'lmg' OR tws.`Damagetype` = 'shotgun' OR tws.`Damagetype` = 'smg' OR tws.`Damagetype` = 'sniperrifle' OR tws.`Damagetype` = 'handgun' OR tws.`Damagetype` = 'projectileexplosive' OR tws.`Damagetype` = 'explosive' OR tws.`Damagetype` = 'melee' OR tws.`Damagetype` = 'none' OR tws.`Damagetype` = 'carbine' OR tws.`Damagetype` = 'dmr' OR tws.`Damagetype` = 'impact')
					GROUP BY tws.`Damagetype`
					ORDER BY Kills DESC
				");
				// initialize empty array
				$WeaponCodes = array();
				// add the damage type to an array which we will step through
				while($Weapon_r2 = @mysqli_fetch_array($Weapon_q2))
				{
					$WeaponCodes[] = $Weapon_r2['Damagetype'];
				}
				// display weapon category tabs
				// clean up first tab's name
				// find clean name in array
				if(in_array($WeaponCodes['0'],$cat_array))
				{
					$code_Displayed = array_search($WeaponCodes['0'],$cat_array);
				}
				// not found in array.  use ugly version :(
				else
				{
					$code_Displayed = $WeaponCodes['0'];
				}
				echo '
				<div id="tabs">
				<ul>
				<li><div class="subscript">1</div><a href="#tabs-1">' . $code_Displayed . '</a></li>
				';
				$count_tracker = 1;
				// step through the weapon categories for creating tabs
				foreach($WeaponCodes AS $this_WeaponCode)
				{
					// first one was already created; skip it
					if($this_WeaponCode != $WeaponCodes['0'])
					{
						// clean up name
						// find clean name in array
						if(in_array($this_WeaponCode,$cat_array))
						{
							$code_Displayed2 = array_search($this_WeaponCode,$cat_array);
						}
						// not found in array.  use ugly version :(
						else
						{
							$code_Displayed2 = $this_WeaponCode;
						}
						$count_tracker++;
						echo '<li><div class="subscript">' . $count_tracker . '</div><a href="./common/weapon-tab.php?sid=' . $ServerID . '&amp;gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;c=' . $this_WeaponCode . '">' . $code_Displayed2 . '</a></li>';
					}
				}
				echo '
				</ul>
				<div id="tabs-1">
				';
				// get weapon stats for weapon stats list
				// input as: title, damage, soldier, player id, server, db
				Statsout($code_Displayed . " Stats",$WeaponCodes['0'], $weapon_array, $PlayerID, $ServerID, $BF4stats);
				echo '</div>';
			}
			// or else this is a global stats page
			// we could probably just use the above since $ServerID has already been determined to be null
			// but I am paranoid
			else
			{
				// find out which weapons this player has stats for
				$Weapon_q2 = @mysqli_query($BF4stats,"
					SELECT tws.`Damagetype`, SUM(wa.`Kills`) AS Kills
					FROM `tbl_weapons_stats` wa
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND wa.`Kills` > 0
					AND (tws.`Damagetype` = 'assaultrifle' OR tws.`Damagetype` = 'lmg' OR tws.`Damagetype` = 'shotgun' OR tws.`Damagetype` = 'smg' OR tws.`Damagetype` = 'sniperrifle' OR tws.`Damagetype` = 'handgun' OR tws.`Damagetype` = 'projectileexplosive' OR tws.`Damagetype` = 'explosive' OR tws.`Damagetype` = 'melee' OR tws.`Damagetype` = 'none' OR tws.`Damagetype` = 'carbine' OR tws.`Damagetype` = 'dmr' OR tws.`Damagetype` = 'impact')
					GROUP BY tws.`Damagetype`
					ORDER BY Kills DESC
				");
				// initialize empty array
				$WeaponCodes = array();
				// add the damage type to an array which we will step through
				while($Weapon_r2 = @mysqli_fetch_array($Weapon_q2))
				{
					$WeaponCodes[] = $Weapon_r2['Damagetype'];
				}
				// display weapon category tabs
				// clean up first tab's name
				// find clean name in array
				if(in_array($WeaponCodes['0'],$cat_array))
				{
					$code_Displayed = array_search($WeaponCodes['0'],$cat_array);
				}
				// not found in array.  use ugly version :(
				else
				{
					$code_Displayed = $WeaponCodes['0'];
				}
				echo '
				<div id="tabs">
				<ul>
				<li><div class="subscript">1</div><a href="#tabs-1">' . $code_Displayed . '</a></li>
				';
				$count_tracker = 1;
				// step through the weapon categories for creating tabs
				foreach($WeaponCodes AS $this_WeaponCode)
				{
					// first one was already created; skip it
					if($this_WeaponCode != $WeaponCodes['0'])
					{
						// clean up name
						// find clean name in array
						if(in_array($this_WeaponCode,$cat_array))
						{
							$code_Displayed2 = array_search($this_WeaponCode,$cat_array);
						}
						// not found in array.  use ugly version :(
						else
						{
							$code_Displayed2 = $this_WeaponCode;
						}
						$count_tracker++;
						echo '<li><div class="subscript">' . $count_tracker . '</div><a href="./common/weapon-tab.php?gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;c=' . $this_WeaponCode . '">' . $code_Displayed2 . '</a></li>';
					}
				}
				echo '
				</ul>
				<div id="tabs-1">
				';
				// get weapon stats for weapon stats list
				// input as: title, damage, soldier, player id, server, db
				Statsout($code_Displayed . " Stats",$WeaponCodes['0'], $weapon_array, $PlayerID, null, $BF4stats);
				echo '</div>';
			}
			echo '
			</div>
			<br/>
			';
			// free up weapon category query memory
			@mysqli_free_result($Weapon_q2);
		}
		// free up weapon chart query memory
		@mysqli_free_result($Weapon_q);
		// begin dog tag stats
		// check to see if the player has gotten anyone's tags
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			// query for dog tags this user has collected
			$DogTag_q1 = @mysqli_query($BF4stats,"
				SELECT tpd.`PlayerID`
				FROM `tbl_dogtags` dt
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` = {$ServerID}
				LIMIT 1
			");
		}
		// or else this is a global stats page
		else
		{
			// query for dog tags this user has collected
			$DogTag_q1 = @mysqli_query($BF4stats,"
				SELECT tpd.`PlayerID`
				FROM `tbl_dogtags` dt
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				LIMIT 1
			");
		}
		// or if anyone has gotten his
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			// find who has killed this player
			$DogTag_q2 = @mysqli_query($BF4stats,"
				SELECT tpd.`PlayerID`
				FROM `tbl_dogtags` dt
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`VictimID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` = {$ServerID}
				LIMIT 1
			");
		}
		// or else this is a global stats page
		else
		{
			// find who has killed this player
			$DogTag_q2 = @mysqli_query($BF4stats,"
				SELECT tpd.`PlayerID`
				FROM `tbl_dogtags` dt
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`VictimID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				LIMIT 1
			");
		}
		// only display dogtag block if this player has dogtags or someone has this player's dogtags
		if(@mysqli_num_rows($DogTag_q1) != 0 || @mysqli_num_rows($DogTag_q2) != 0)
		{
			echo '<br/>';
			// initialize value
			$count = 0;
			echo '
			<div id="tabs2">
			<ul>
			<li><div class="subscript">1</div><a href="#tabs2-1">Dog Tags Collected</a></li>
			';
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				echo '<li><div class="subscript">2</div><a href="./common/dogtag-tab.php?gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '">Dog Tags Surrendered</a></li>';
			}
			// or else this is a global stats page
			else
			{
				echo '<li><div class="subscript">2</div><a href="./common/dogtag-tab.php?gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;player=' . $SoldierName . '">Dog Tags Surrendered</a></li>';
			}
			echo '
			</ul>
			<div id="tabs2-1">
			<table class="prettytable">
			';
			// check to see if the player has gotten anyone's tags
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				// query for dog tags this user has collected
				$DogTag_q = @mysqli_query($BF4stats,"
					SELECT dt.`Count`, tpd2.`SoldierName` AS Victim, tpd2.`PlayerID` AS VictimID
					FROM `tbl_dogtags` dt
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
					INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` = {$ServerID}
					ORDER BY Count DESC, Victim ASC
				");
			}
			// or else this is a global stats page
			else
			{
				// query for dog tags this user has collected
				$DogTag_q = @mysqli_query($BF4stats,"
					SELECT SUM(dt.`Count`) AS Count, tpd2.`SoldierName` AS Victim, tpd2.`PlayerID` AS VictimID
					FROM `tbl_dogtags` dt
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
					INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					GROUP BY Victim
					ORDER BY Count DESC, Victim ASC
				");
			}
			if(@mysqli_num_rows($DogTag_q) != 0)
			{
				echo '
				<tr>
				<th width="5%" class="countheader">#</th>
				<th width="47%" style="text-align: left;padding-left: 10px;">Victim</th>
				<th width="48%" style="text-align: left;padding-left: 5px;"><span class="orderedDESCheader">Count</span></th>
				</tr>
				';
				while($DogTag_r = @mysqli_fetch_assoc($DogTag_q))
				{
					$Victim = $DogTag_r['Victim'];
					$VictimID = $DogTag_r['VictimID'];
					$KillCount = $DogTag_r['Count'];
					$count++;
					echo '
					<tr>
					<td width="5%" class="count"><span class="information">' . $count . '</span></td>
					';
					// if there is a ServerID, this is a server stats page
					if(!empty($ServerID))
					{
						echo '<td width="47%" class="tablecontents" style="text-align: left;padding-left: 10px;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;pid=' . $VictimID . '&amp;p=player">' . $Victim . '</a></td>';
					}
					// or else this is a global stats page
					else
					{
						echo '<td width="47%" class="tablecontents" style="text-align: left;padding-left: 10px;"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;pid=' . $VictimID . '">' . $Victim . '</a></td>';
					}
					echo '
					<td width="48%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $KillCount . '</td>
					</tr>
					';
				}
			}
			else
			{
				echo '
				<tr>
				<td width="100%" class="tablecontents" colspan="3" style="text-align: left;padding-left: 10px;"><div class="headline">' . $SoldierName . ' has not collected any dog tags.</div></td>
				</tr>
				';
			}
			// free up dog tag query memory
			@mysqli_free_result($DogTag_q);
			echo '
			</table>
			</div>
			</div>
			<br/>
			';
		}
		// free up dog tag query1 memory
		@mysqli_free_result($DogTag_q1);
		// free up dog tag query2 memory
		@mysqli_free_result($DogTag_q2);
		// free up player data query memory
		@mysqli_free_result($PlayerData_q);
		// signature images...
		
		// make sure the GD extension is available
		if(extension_loaded('gd') AND function_exists('gd_info'))
		{
			// find current URL info
			$host = 'http://' . $_SERVER['HTTP_HOST'];
			$dir = dirname($_SERVER['PHP_SELF']);
			$file = $_SERVER['PHP_SELF'];
			// show signature images
			echo '
			<br/>
			<div class="subsection"><center><span class="information">Signature images use combined stats from all of ' . $clan_name . '\'s Servers.</span></center></div>
			<table class="prettytable">
			<tr>
			<td class="tablecontents" style="text-align: left; padding: 20px;" valign="top" width="50%">
			Stats image with player\'s rank:<br/><br/>
			';
			// include signature.php image
			echo '
			<img src="./signature/signature.php?pid=' . $PlayerID . '&amp;gid=' . $GameID . '&amp;fav=0" alt="signature" />
			<br/>
			<span class="information">BBcode:</span>
			<br/><br/>
			<span style="font-size: 10px;">[URL=' . $host . $file . '?p=player&amp;pid=' . $PlayerID . '][IMG]' . $host . $dir . '/signature/signature.php?pid=' . $PlayerID . '&amp;gid=' . $GameID . '&amp;fav=0[/IMG][/URL]</span><br/>
			</td>
			<td class="tablecontents" style="text-align: left; padding: 20px;" valign="top" width="50%">
			Stats image with player\'s favorite weapon:<br/><br/>
			';
			// include signature.php image
			echo '
			<img src="./signature/signature.php?pid=' . $PlayerID . '&amp;gid=' . $GameID . '&amp;fav=1" alt="signature" />
			<br/>
			<span class="information">BBcode:</span>
			<br/><br/>
			<span style="font-size: 10px;">[URL=' . $host . $file . '?p=player&amp;pid=' . $PlayerID . '][IMG]' . $host . $dir . '/signature/signature.php?pid=' . $PlayerID . '&amp;gid=' . $GameID . '&amp;fav=1[/IMG][/URL]</span><br/>
			</td>
			</tr>
			</table>
			<br/>
			';
		}
	}
}
// this shouldn't happen, but we will make sure
elseif($SoldierName == 'Not Found')
{
	echo '
	<div class="subsection">
	<div class="headline">
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo 'The selected player ID was not found in this server.';
	}
	// or else this is a global stats page
	else
	{
		echo 'The selected player ID was not found in these servers.';
	}
	echo '
	</div>
	</div>
	';
}
// begin external stats block
if($SoldierName != null AND $SoldierName != 'Not Found')
{
	echo '
	<br/><br/>
	<div class="subsection"><center><span class="information">External Links for "' . $SoldierName . '"</span></center></div>
	<table class="prettytable">
	<tr>
	<td class="tablecontents" width="33%" style="text-align: center"><span class="information">Battlelog Stats: </span><a href="http://battlelog.battlefield.com/bf4/user/' . $SoldierName . '" target="_blank">www.Battlelog.Battlefield.com</a></td>
	<td class="tablecontents" width="33%" style="text-align: center"><span class="information">BF4DB: </span><a href="http://bf4db.com/players?name=' . $SoldierName . '" target="_blank">www.BF4db.com</a></td>
	<td class="tablecontents" width="33%" style="text-align: center"><span class="information">Metabans: </span><a href="http://metabans.com/search/' . $SoldierName . '" target="_blank">www.Metabans.com</a></td>
	</tr>
	</table>
	<br/>
	';
}
?>
