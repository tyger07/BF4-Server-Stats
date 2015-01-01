<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// fade in
echo '
<div id="loaded" style="display: none;">
<script type="text/javascript">
$(\'#loaded\').fadeIn("slow");
</script>
';
// no soldiername input from GET
if($SoldierName == null)
{
	// if there was a $playerid input but it was nulled out, let user know it was nulled out because index.php did not find that as a valid player id
	if(!empty($_GET['pid']) && is_numeric($_GET['pid']))
	{
		echo '
		<div class="subsection">
		<div class="headline">
		This player ID does not exist.
		</div>
		</div>
		';
	}
	else
	{
		echo '
		<div class="subsection">
		<div class="headline">
		Please enter a player name.
		</div>
		</div>
		';
	}
}
// SoldierName from GET has been determined
elseif($SoldierName != null)
{
	// initialize value
	$soldier_found = 0;
	// $playerid is found automatically for this input in index.php
	// if there is no $playerid, no need to do $playerid specific soldier info searches
	if(!empty($PlayerID))
	{
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
			// get player stats
			$PlayerData_q = @mysqli_query($BF4stats,"
				SELECT tpd.`CountryCode`, tpd.`PlayerID`, tpd.`GlobalRank`, SUM(tps.`Suicide`) AS Suicide, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, SUM(tps.`TKs`) AS TKs, SUM(tps.`Headshots`) AS Headshots, SUM(tps.`Rounds`) AS Rounds, MAX(tps.`Killstreak`) AS Killstreak, MAX(tps.`Deathstreak`) AS Deathstreak, SUM(tps.`Wins`) AS Wins, SUM(tps.`Losses`) AS Losses, (SUM(tps.`Wins`)/SUM(tps.`Losses`)) AS WLR, MAX(tps.`HighScore`) AS HighScore, MIN(tps.`FirstSeenOnServer`) AS FirstSeenOnServer, MAX(tps.`LastSeenOnServer`) AS LastSeenOnServer
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`PlayerID`
			");
			// was a soldier found?
			if(@mysqli_num_rows($PlayerData_q) == 1)
			{
				$soldier_found = 1;
				
			}
		}
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
		';
		// get current rank query details
		if(!empty($rank))
		{
			// filter out SQL injection
			if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Kills' AND $rank != 'KDR')
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
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR
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
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`SoldierName` LIKE '%{$SoldierName}%'
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`SoldierName`
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT 0, 20
			");
		}
		// if a similar name was found, display this
		if(@mysqli_num_rows($PlayerMatch_q) != 0)
		{
			echo '
			<div class="subsection" style="margin-top: 2px;">
			<div class="headline">
			Here are some players with names similar to "' . $SoldierName . '".
			</div>
			</div>
			<br/><br/>
			<table class="prettytable">
			<tr>
			<th width="5%" class="countheader">#</th>
			';
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				echo '<th width="24%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=SoldierName&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="24%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=SoldierName&amp;o=';
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
				echo '<th width="24%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=Score&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="24%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=Score&amp;o=';
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
				echo '<th width="24%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=Kills&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="24%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=Kills&amp;o=';
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
				echo '<th width="24%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '&amp;r=KDR&amp;o=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="24%"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;player=' . $SoldierName . '&amp;r=KDR&amp;o=';
			}
			if($rank != 'KDR')
			{
				echo 'DESC"><span class="orderheader">Kill / Death</span></a></th>';
			}
			else
			{
				echo $nextorder . '"><span class="ordered' . $order . 'header">Kill / Death</span></a></th>';
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
				$KDR = round($PlayerMatch_r['KDR'],2);
				echo '
				<tr>
				<td width="5%" class="count"><span class="information">' . $count . '</span></td>
				';
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					echo '<td width="24%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;pid=' . $Player_ID . '">' . $Soldier_Name . '</a></td>';
				}
				// or else this is a global stats page
				else
				{
					echo '<td width="24%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;pid=' . $Player_ID . '">' . $Soldier_Name . '</a></td>';
				}
				echo'
				<td width="24%" class="tablecontents">' . $Score . '</td>
				<td width="24%" class="tablecontents">' . $Kills . '</td>
				<td width="24%" class="tablecontents">' . $KDR . '</td>
				</tr>
				';
			}
			echo '
			</table>
			';
		}
	}
	// this unique player was found
	elseif($soldier_found == 1)
	{
		echo '
		<div class="subsection">
		<div class="headline">
		' . ucfirst($SoldierName) . '
		</div>
		</div>
		';
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '
			<br/>
			<br/>
			<div id="ranks">
			<br/>
			<center><img src="./common/images/loading.gif" alt="loading" style="width: 24px; height: 24px;" /></center>
			<br/><br/>
			</div>
			';
			// ajax load ranks
			echo '
			<script type="text/javascript">
			$(\'#ranks\').load("./common/player/player-ranks.php?gid=' . $GameID;
			if(!empty($ServerID))
			{
				echo '&sid=' . $ServerID;
			}
			if(!empty($PlayerID))
			{
				echo '&pid=' . $PlayerID;
			}
			if(!empty($ServerName))
			{
				echo '&server=' . urlencode($ServerName);
			}
			echo '");
			</script>
			';
		}
		// commented out combined stats due to heavy database load if there are a large number of combined players
		//// or else this is a global stats page
		//else
		//{
		//	echo '
		//	<br/>
		//	<br/>
		//	<table id="ranks" class="prettytable">
		//	<tr>
		//	<td class="tablecontents"><center>Ranks in ' . $clan_name . '\'s Servers</center></td>
		//	</tr>
		//	<tr>
		//	<td class="tablecontents">
		//	<center>Loading ... <img src="./common/images/loading.gif" alt="loading" width="16px" height="16px" /></center>
		//	</td>
		//	</tr>
		//	</table>
		//	';
		//	// ajax load ranks
		//	echo '
		//	<script type="text/javascript">
		//	$(\'#ranks\').load("./common/player/ranks.php?gid=' . $GameID;
		//	if(!empty($PlayerID))
		//	{
		//		echo '&pid=' . $PlayerID;
		//	}
		//	if(!empty($ServerName))
		//	{
		//		echo '&server=' . urlencode($ServerName);
		//	}
		//	echo '");
		//	</script>
		//	';
		//}
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
				$country_img = './common/images/flags/none.png';
			}
			else
			{
				$country_img = './common/images/flags/' . strtolower($CountryCode) . '.png';	
			}
		}
		// this country is missing!
		else
		{
			$country = $CountryCode;
			$country_img = './common/images/flags/none.png';
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
		$WLR = round($PlayerData_r['WLR'],2) * 100;
		$HighScore = $PlayerData_r['HighScore'];
		$FirstSeen = date("M d Y", strtotime($PlayerData_r['FirstSeenOnServer']));
		$LastSeen = date("M d Y", strtotime($PlayerData_r['LastSeenOnServer']));
		$PlayerID = $PlayerData_r['PlayerID'];
		$rank = $PlayerData_r['GlobalRank'];
		// filter out the available ranks
		if($rank >= $rank_min && $rank <= $rank_max)
		{
			$rank_img = './common/images/ranks/r' . $rank . '.png';
		}
		else
		{
			$rank_img = './common/images/ranks/missing.png';
		}
		echo '
		<br/>
		<br/>
		<table class="prettytable">
		<tr>
		<td width="15%" class="tablecontents" style="text-align: center;"><img src="' . $rank_img . '" style="height: 95px; width: 95px;" alt="rank ' . $rank . '"/></td>
		<td width="85%" class="tablecontents" colspan="5"><div class="headline">Overview</div></td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Country</th>
		<td width="18%" class="tablecontents"><img src="' . $country_img . '" style="height: 11px; width: 16px;" alt="' . $country . '"/> ' . $country . '<span class="information"> (</span>' . $CountryCode . '<span class="information">)</span></td>
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
		<th width="15%" style="padding-left: 10px;">Kill / Death</th>
		<td width="18%" class="tablecontents">' . $KDR . '</td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Suicides</th>
		<td width="18%" class="tablecontents">' . $Suicides . '</td>
		<th width="15%" style="padding-left: 10px;">Headshots</th>
		<td width="18%" class="tablecontents">' . $Headshots . '</td>
		<th width="15%" style="padding-left: 10px;">Headshot / Kill</th>
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
				SELECT tws.`Damagetype`, SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				AND (tws.`Damagetype` = 'assaultrifle' OR tws.`Damagetype` = 'lmg' OR tws.`Damagetype` = 'shotgun' OR tws.`Damagetype` = 'smg' OR tws.`Damagetype` = 'sniperrifle' OR tws.`Damagetype` = 'handgun' OR tws.`Damagetype` = 'projectileexplosive' OR tws.`Damagetype` = 'explosive' OR tws.`Damagetype` = 'melee' OR tws.`Damagetype` = 'carbine' OR tws.`Damagetype` = 'dmr' OR tws.`Damagetype` = 'impact')
				GROUP BY tws.`Damagetype`
				ORDER BY Kills DESC
			");
			// do a separate query for vehicles
			$Vehicle_q = @mysqli_query($BF4stats,"
				SELECT SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				AND (tws.`Damagetype` LIKE '%vehicle%' OR tws.`DamageType` = 'none')
				AND (tws.`Friendlyname` <> 'dlSHTR')
				GROUP BY tpd.`PlayerID`
			");
			// initialize values
			$VehicleFound = 0;
			$VehicleAdded = 0;
			if(@mysqli_num_rows($Vehicle_q) == 1)
			{
				$Vehicle_r = @mysqli_fetch_assoc($Vehicle_q);
				$VehicleKills = $Vehicle_r['Kills'];
				$VehicleDeaths = $Vehicle_r['Deaths'];
				$VehicleHS = $Vehicle_r['Headshots'];
				$VehicleHSR = $Vehicle_r['HSR'];
				$VehicleFound = 1;
			}
		}
		// or else this is a global stats page
		else
		{
			
			$Weapon_q = @mysqli_query($BF4stats,"
				SELECT tws.`Damagetype`, SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tsp.`ServerID` IN ({$valid_ids})
				AND tpd.`GameID` = {$GameID}
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				AND (tws.`Damagetype` = 'assaultrifle' OR tws.`Damagetype` = 'lmg' OR tws.`Damagetype` = 'shotgun' OR tws.`Damagetype` = 'smg' OR tws.`Damagetype` = 'sniperrifle' OR tws.`Damagetype` = 'handgun' OR tws.`Damagetype` = 'projectileexplosive' OR tws.`Damagetype` = 'explosive' OR tws.`Damagetype` = 'melee' OR tws.`Damagetype` = 'carbine' OR tws.`Damagetype` = 'dmr' OR tws.`Damagetype` = 'impact')
				GROUP BY tws.`Damagetype`
				ORDER BY Kills DESC
			");
			// do a separate query for vehicles
			$Vehicle_q = @mysqli_query($BF4stats,"
				SELECT SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tsp.`ServerID` IN ({$valid_ids})
				AND tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				AND (tws.`Damagetype` LIKE '%vehicle%' OR tws.`DamageType` = 'none')
				AND (tws.`Friendlyname` <> 'dlSHTR')
				GROUP BY tpd.`PlayerID`
			");
			// initialize values
			$VehicleFound = 0;
			$VehicleAdded = 0;
			if(@mysqli_num_rows($Vehicle_q) == 1)
			{
				$Vehicle_r = @mysqli_fetch_assoc($Vehicle_q);
				$VehicleKills = $Vehicle_r['Kills'];
				$VehicleDeaths = $Vehicle_r['Deaths'];
				$VehicleHS = $Vehicle_r['Headshots'];
				$VehicleHSR = $Vehicle_r['HSR'];
				$VehicleFound = 1;
			}
		}
		// do normal weapon query and insert vehicles in where necessary
		if(@mysqli_num_rows($Weapon_q) != 0 || $VehicleFound == 1)
		{
			// initialize empty array for storing weapons
			$WeaponCodes = array();
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
						// if there are more than just vehicle kills, do the normal weapon output inserting vehicles wherever appropriate
						if(@mysqli_num_rows($Weapon_q) != 0)
						{
							while($Weapon_r = @mysqli_fetch_assoc($Weapon_q))
							{
								$weapon = $Weapon_r['Damagetype'];
								// convert to nice version
								if(in_array($weapon,$cat_array))
								{
									$weapon = array_search($weapon,$cat_array);
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
								// are there vehicle stats?
								if($VehicleFound == 1)
								{
									// we want to inject the vehicle stats into the correct place
									if($kills > $VehicleKills)
									{
										// add to tabbed display array
										$WeaponCodes[] = $Weapon_r['Damagetype'];
									}
									elseif($kills < $VehicleKills && $VehicleAdded == 0)
									{
										// add vehicle to tabbed display array
										$WeaponCodes[] = 'VehicleCustom';
										echo ',
										[\'Vehicle\', ' . $VehicleKills . ', ' . $VehicleDeaths . ', ' . $VehicleHS . ',  ' . $VehicleHSR * 100 . ']
										';
										// add regular to tabbed display array
										$WeaponCodes[] = $Weapon_r['Damagetype'];
										$VehicleAdded = 1;
									}
									else
									{
										// proceed like normal
										$WeaponCodes[] = $Weapon_r['Damagetype'];
									}
								}
								else
								{
									// add to tabbed display array
									$WeaponCodes[] = $Weapon_r['Damagetype'];
								}
								echo ',
								[\'' . $weapon . '\', ' . $kills . ', ' . $deaths . ', ' . $headshots . ',  ' . $hsr * 100 . ']
								';
							}
						}
						// or else just process vehicle kills on its own
						elseif($VehicleFound == 1)
						{
							// set max values higher than max for cropping reasons
							$vmax = $VehicleDeaths + 50;
							$hmax = $VehicleKills + 50;
							// add vehicle to tabbed display array
							$WeaponCodes[] = 'VehicleCustom';
							echo ',
							[\'Vehicle\', ' . $VehicleKills . ', ' . $VehicleDeaths . ', ' . $VehicleHS . ',  ' . $VehicleHSR * 100 . ']
							';
						}
						echo '
					]);
					var options = {
						title: \'Headshots\',
						titleTextStyle: {color: \'#888\', bold: \'false\', fontSize: 12, auraColor: \'none\'},
						legend: {textStyle: {color: \'#888\', bold: \'false\', fontSize: 12, auraColor: \'none\'}},
						hAxis: {title: \'Kills\', titleTextStyle: {color: \'#888\', bold: \'false\', fontSize: 12, auraColor: \'none\'}, textStyle:  {color: \'#888\', auraColor: \'none\', fontSize: 8}, minValue: -1, maxValue: ' . $hmax . ', gridlines: {color: \'transparent\'}, baselineColor:  \'#666\'},
						vAxis: {title: \'Deaths\', titleTextStyle: {color: \'#888\', bold: \'false\', fontSize: 12, auraColor: \'none\'}, textStyle:  {color: \'#888\', auraColor: \'none\', fontSize: 8}, minValue: -1, maxValue: ' . $vmax . ', gridlines: {color: \'transparent\'}, baselineColor:  \'#666\'},
						bubble: {textStyle: {color: \'#888\', fontSize: 10, bold: \'false\', auraColor: \'none\'}, stroke: \'transparent\'},
						backgroundColor: \'transparent\',
						chartArea: {left: 20, top: 50, width: "90%", height: "70%"},
						colorAxis: {minValue: 0, colors: [\'#333333\', \'#640000\'], legend: {textStyle: {color: \'#888\'}, position: \'top\'}},
						sizeAxis: {minValue: 0, maxValue: 1, minSize: 15, maxSize: 20},
						tooltip: {textStyle: {color: \'#AA0000\', auraColor: \'none\'}},
						enableInteractivity: \'true\'
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
						$tab_code_Displayed = array_search($this_WeaponCode,$cat_array);
					}
					// not found in array.  use ugly version :(
					else
					{
						$tab_code_Displayed = $this_WeaponCode;
					}
					$count_tracker++;
					echo '<li><div class="subscript">' . $count_tracker . '</div><a href="./common/player/weapon-tab.php?sid=' . $ServerID . '&amp;gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;c=' . $this_WeaponCode . '">' . $tab_code_Displayed . '</a></li>';
				}
			}
			echo '
			</ul>
			<div id="tabs-1">
			';
			// get weapon stats for weapon stats list
			Statsout($WeaponCodes['0'], $weapon_array, $PlayerID, $ServerID, $valid_ids, $GameID, $BF4stats, '3');
			echo '</div>
			</div>
			<br/>
			';
		}
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
				AND tsp.`ServerID` IN ({$valid_ids})
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
				AND tsp.`ServerID` IN ({$valid_ids})
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
			<div id="dogtag_tab">
			<ul>
			<li><a href="#dogtag_tab-1">Dog Tags Collected</a></li>
			';
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				echo '<li><a href="./common/player/dogtag-tab.php?gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '">Dog Tags Surrendered</a></li>';
			}
			// or else this is a global stats page
			else
			{
				echo '<li><a href="./common/player/dogtag-tab.php?gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;player=' . $SoldierName . '">Dog Tags Surrendered</a></li>';
			}
			echo '
			</ul>
			<div id="dogtag_tab-1">
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
					AND tsp.`ServerID` IN ({$valid_ids})
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
					// show expand/contract if very long
					if($count == 10)
					{
						echo '
						</table>
						<div>
						<span class="expanded">
						<table class="prettytable" style="margin-top: -2px;">
						';
					}
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
				// finish expand/contract if very long
				if($count > 10)
				{
					$remaining = $count - 10;
					echo '
					</table>
					</span>
					<a href="javascript:void(0)" class="collapsed"><table class="prettytable" style="margin-top: -2px;"><tr><td class="tablecontents" style="text-align: left;padding-left: 15px;"><span class="orderedDESCheader">Show ' . $remaining . ' More</span></td></tr></table></a>
					</div>
					<table>
					<tr>
					<td>
					</td>
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
			echo '
			</table>
			</div>
			</div>
			<br/>
			';
		}
		// signature images...
		echo '
		<br/>
		<div class="subsection" style="position: relative;"><div class="headline"><span class="information">Signature images use combined stats from all of ' . $clan_name . '\'s Servers.</span></div>
		';
		// check if this player's rank is cached in the database
		// we do this early so that we can insert dummy data now into the database (if necessary) to reduce duplicates later when the slower parallel process is executed
		// (in other words, insert dummy data now quickly, so later the parallel slow execution updates the one dummy data row instead of inserting multiple new data rows in parallel)
		// rank players by score
		// check to see if this rank cache table exists
		@mysqli_query($BF4stats,"
			CREATE TABLE IF NOT EXISTS `tyger_stats_rank_cache`
			(`PlayerID` INT(10) UNSIGNED NOT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `rank` INT(10) UNSIGNED NOT NULL DEFAULT '0', `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`PlayerID`, `SID`))
			ENGINE=MyISAM
			DEFAULT CHARSET=utf8
			COLLATE=utf8_bin
		");
		// initialize timestamp values
		$now_timestamp = time();
		$old = $now_timestamp - 43200;
		// rank players by score
		// check if score rank is already cached
		$ScoreC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$PlayerID}
			AND `category` = 'Score'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$valid_ids}'
			GROUP BY `PlayerID`
		");
		if(@mysqli_num_rows($ScoreC_q) != 0)
		{
			$ScoreC_r = @mysqli_fetch_assoc($ScoreC_q);
			$srank = $ScoreC_r['rank'];
			$timestamp = $ScoreC_r['timestamp'];
			// data older than 12 hours? or incorrect data? show recalculate message
			if(($timestamp <= $old) OR ($srank == 0))
			{
				// we aren't actually doing this now
				// we are just showing the message that it will be done
				echo '
				<div id="cache_fade2" style="position: absolute; top: 3px; left: -150px; display: none;">
				<div class="subsection" style="width: 100px; font-size: 12px;">
				<center>Cache Recreated:<br/>Ranks</center>
				</div>
				</div>
				<script type="text/javascript">
				$("#cache_fade2").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
				</script>
				';
			}
			// show reuse message
			else
			{
				// we aren't actually doing this now
				// we are just showing the message that it will be done
				echo '
				<div id="cache_fade2" style="position: absolute; top: 3px; left: -150px; display: none;">
				<div class="subsection" style="width: 100px; font-size: 12px;">
				<center>Cache Used:<br/>Ranks</center>
				</div>
				</div>
				<script type="text/javascript">
				$("#cache_fade2").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
				</script>
				';
			}
		}
		else
		{
			// show insert message
			echo '
			<div id="cache_fade2" style="position: absolute; top: 3px; left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Created:<br/>Ranks</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade2").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
			// insert useless dummy data for now
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'Score', '0', '0')
			");
		}
		// done with the dummy cache stuff...
		// find current URL info
		$host = 'http://' . $_SERVER['HTTP_HOST'];
		$dir = dirname($_SERVER['PHP_SELF']);
		$file = $_SERVER['PHP_SELF'];
		// show signature images
		echo '
		</div>
		<table class="prettytable">
		<tr>
		<td class="tablecontents" style="text-align: left; padding: 20px;" valign="top" width="50%">
		Stats image with player\'s rank:<br/><br/>
		';
		// include signature.php image
		echo '
		<a href="' . $host . $file . '?p=player&amp;pid=' . $PlayerID . '" target="_blank"><img src="./common/signature/signature.png?pid=' . $PlayerID . '" style="height: 100px; width: 400px;" alt="signature" /></a>
		<br/>
		<span class="information">BBcode:</span>
		<br/><br/>
		<table class="prettytable">
		<tr>
		<td class="tablecontents">
		<span style="font-size: 10px;">[URL=' . $host . $file . '?p=player&amp;pid=' . $PlayerID . '][IMG]' . $host . $dir . '/common/signature/signature.png?pid=' . $PlayerID . '[/IMG][/URL]</span>
		</td>
		</tr>
		</table>
		</td>
		<td class="tablecontents" style="text-align: left; padding: 20px;" valign="top" width="50%">
		Stats image with player\'s favorite weapon:<br/><br/>
		';
		// include signature.php image
		echo '
		<a href="' . $host . $file . '?p=player&amp;pid=' . $PlayerID . '" target="_blank"><img src="./common/signature/signature.png?pid=' . $PlayerID . '&amp;fav=1" style="height: 100px; width: 400px;" alt="signature" /></a>
		<br/>
		<span class="information">BBcode:</span>
		<br/><br/>
		<table class="prettytable">
		<tr>
		<td class="tablecontents">
		<span style="font-size: 10px;">[URL=' . $host . $file . '?p=player&amp;pid=' . $PlayerID . '][IMG]' . $host . $dir . '/common/signature/signature.png?pid=' . $PlayerID . '&amp;fav=1[/IMG][/URL]</span>
		</td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		';
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
	<div class="subsection"><div class="headline"><span class="information">External Links for "' . $SoldierName . '"</span></div></div>
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
echo '
</div>
';
?>