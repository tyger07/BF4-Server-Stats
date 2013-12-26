<?php
// server stats player page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING


if($SoldierName == null)
{
	echo '
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<td>
	<br/>
	<center>
	<font class="alert">Please enter a player name.</font>
	</center>
	<br/>
	</td>
	</tr>
	</table>
	</div>
	';
}
// SoldierName from GET has been determined
elseif($SoldierName != null)
{
	echo '
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<td class="headline">
	<br/>
	<center>
	';
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<b>Statistics Data for ' .$SoldierName . '</b>';
	}
	// or else this is a global stats page
	else
	{
		echo '<b>Global Statistics Data for ' .$SoldierName . ' in ' . $clan_name . '\'s Servers</b>';
	}
	echo '
	</center>
	<br/>
	</td>
	</tr>
	</table>
	</div>
	<br/>
	<br/>
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	';
	// initialize value
	$soldier_found = 0;
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		// get player stats
		$PlayerData_q = @mysqli_query($BF4stats,"
			SELECT tpd.`CountryCode`, tpd.`PlayerID`, tps.`Suicide`, tps.`Score`, tps.`Kills`, tps.`Deaths`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR, tps.`TKs`, tps.`Headshots`, tps.`Rounds`, tps.`Killstreak`, tps.`Deathstreak`, tps.`Wins`, tps.`Losses`, (tps.`Wins`/tps.`Losses`) AS WLR, tps.`HighScore`, tps.`FirstSeenOnServer`, tps.`LastSeenOnServer`
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
		if(@mysqli_num_rows($Find_q) != 0)
		{
			$soldier_found = 1;
		}
		// free up soldier find query memory
		@mysqli_free_result($Find_q);
		// get player stats
		$PlayerData_q = @mysqli_query($BF4stats,"
			SELECT tpd.`CountryCode`, tpd.`PlayerID`, SUM(tps.`Suicide`) AS Suicide, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, SUM(tps.`TKs`) AS TKs, SUM(tps.`Headshots`) AS Headshots, SUM(tps.`Rounds`) AS Rounds, MAX(tps.`Killstreak`) AS Killstreak, MAX(tps.`Deathstreak`) AS Deathstreak, SUM(tps.`Wins`) AS Wins, SUM(tps.`Losses`) AS Losses, (SUM(tps.`Wins`)/SUM(tps.`Losses`)) AS WLR, MAX(tps.`HighScore`) AS HighScore, MIN(tps.`FirstSeenOnServer`) AS FirstSeenOnServer, MAX(tps.`LastSeenOnServer`) AS LastSeenOnServer
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND tpd.`GameID` = {$GameID}
		");
	}
	// if no stats were found for player name, display this
	if($soldier_found == 0)
	{
		echo '
		<td>
		<br/>
		<center>
		';
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<font class="alert">No unique player data found for "' . $SoldierName . '" in this server.</font>';
		}
		// or else this is a global stats page
		else
		{
			echo '<font class="alert">No unique player data found for "' . $SoldierName . '" in these servers.</font>';
		}
		echo '
		</center><br/>
		</td></tr></table></div><br/>
		';
		// get current rank query details
		if(isset($_GET['rank']) AND !empty($_GET['rank']))
		{
			$rank = $_GET['rank'];
			// filter out SQL injection
			if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Rounds' AND $rank != 'Kills' AND $rank != 'Deaths' AND $rank != 'KDR')
			{
				// unexpected input detected
				// use default instead
				$rank = 'SoldierName'; 
			}
		}
		// set default if no rank provided in URL
		else
		{
			$rank = 'SoldierName';
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
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
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
			");
		}
		// if a similar name was found, display this
		if(@mysqli_num_rows($PlayerMatch_q) != 0)
		{
			echo '
			<br/>
			<div class="middlecontent">
			<table width="100%" border="0">
			<tr>
			<th class="headline"><b>Here are some players with names similar to "' . $SoldierName . '":</b></th>
			</tr>
			<tr>
			<td>
			<div class="innercontent">
			<br/>
			<table width="98%" align="center" border="0">
			<tr>
			<th width="5%" style="text-align:left">#</th>
			';
			// if there is a ServerID, this is a server stats page
			if(isset($ServerID) AND !is_null($ServerID))
			{
				echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;SoldierName=' . $SoldierName . '&amp;search=1&amp;rank=SoldierName&amp;order=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;SoldierName=' . $SoldierName . '&amp;rank=SoldierName&amp;order=';
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
			if(isset($ServerID) AND !is_null($ServerID))
			{
				echo '<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;SoldierName=' . $SoldierName . '&amp;search=1&amp;rank=Score&amp;order=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;SoldierName=' . $SoldierName . '&amp;rank=Score&amp;order=';
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
			if(isset($ServerID) AND !is_null($ServerID))
			{
				echo '<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;SoldierName=' . $SoldierName . '&amp;search=1&amp;rank=Rounds&amp;order=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;SoldierName=' . $SoldierName . '&amp;rank=Rounds&amp;order=';
			}
			if($rank != 'Rounds')
			{
				echo 'DESC"><span class="orderheader">Rounds Played</span></a></th>';
			}
			else
			{
				echo $nextorder . '"><span class="ordered' . $order . 'header">Rounds Played</span></a></th>';
			}
			// if there is a ServerID, this is a server stats page
			if(isset($ServerID) AND !is_null($ServerID))
			{
				echo '<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;SoldierName=' . $SoldierName . '&amp;search=1&amp;rank=Kills&amp;order=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;SoldierName=' . $SoldierName . '&amp;rank=Kills&amp;order=';
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
			if(isset($ServerID) AND !is_null($ServerID))
			{
				echo '<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;SoldierName=' . $SoldierName . '&amp;search=1&amp;rank=Deaths&amp;order=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="15%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;SoldierName=' . $SoldierName . '&amp;rank=Deaths&amp;order=';
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
			if(isset($ServerID) AND !is_null($ServerID))
			{
				echo '<th width="17%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;SoldierName=' . $SoldierName . '&amp;search=1&amp;rank=KDR&amp;order=';
			}
			// or else this is a global stats page
			else
			{
				echo '<th width="17%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;SoldierName=' . $SoldierName . '&amp;rank=KDR&amp;order=';
			}
			if($rank != 'KDR')
			{
				echo 'DESC"><span class="orderheader">Kill/Death Ratio</span></a></th>';
			}
			else
			{
				echo $nextorder . '"><span class="ordered' . $order . 'header">Kill/Death Ratio</span></a></th>';
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
				<td width="5%" class="tablecontents" style="text-align: left;"><font class="information">' . $count . ':</font></td>
				';
				// if there is a ServerID, this is a server stats page
				if(isset($ServerID) AND !is_null($ServerID))
				{
					echo '<td width="18%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $Player_ID . '&amp;search=1">' . $Soldier_Name . '</a></td>';
				}
				// or else this is a global stats page
				else
				{
					echo '<td width="18%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $Player_ID . '">' . $Soldier_Name . '</a></td>';
				}
				echo'
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Score . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Rounds . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Kills . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Deaths . '</td>
				<td width="17%" class="tablecontents" style="text-align: left;">' . $KDR . '</td>
				</tr>
				';
			}
			echo '</table><br/></div>';
		}
		// free up player match query memory
		@mysqli_free_result($PlayerMatch_q);
	}
	// this unique player was found
	elseif($soldier_found == 1)
	{
		// only show ranks if this is not a global stats page
		// there is no such thing as global rank in database
		// and computing it is too slow with lots of players in global
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '
			<th class="headline"><b>Ranks</b></th>
			</tr>
			<tr>
			<td>
			<div class="innercontent">
			<table width="70%" align="center" border="0">
			<tr>
			';
			// get this player's ranks
			// input as: server id, soldier, db
			rank($ServerID, $PlayerID, $BF4stats);
			echo '
			</tr>
			</table>
			<br/>
			</div>
			';
			echo '
			</td>
			</tr>
			</table>
			</div>
			';
			echo '
			<br/><br/>
			<div class="middlecontent">
			<table width="100%" border="0">
			<tr>
			';
		}
		echo '
		<th class="headline"><b>Overview</b></th>
		</tr>
		<tr>
		<td>
		';
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
		$WLR = round($PlayerData_r['WLR'],2);
		$HighScore = $PlayerData_r['HighScore'];
		$FirstSeen = date("M d Y", strtotime($PlayerData_r['FirstSeenOnServer']));
		$LastSeen = date("M d Y", strtotime($PlayerData_r['LastSeenOnServer']));
		$PlayerID = $PlayerData_r['PlayerID'];
		echo '
		<div class="innercontent">
		<br/>
		<table width="90%" align="center" border="0">
		<tr>
		<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Country: </font><img src="' . $country_img . '" alt="' . $country_name . '"/> ' . $country . '<font class="information"> (</font>' . $CountryCode . '<font class="information">)</font><br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">First Visit: </font>' . $FirstSeen . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Last Visit: </font>' . $LastSeen . '<br/><br/></td>
		</tr><tr>
		<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Kills: </font>' . $Kills . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Deaths: </font>' . $Deaths . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Kill/Death Ratio: </font>' . $KDR . '<br/><br/></td>
		</tr><tr>
		<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Killstreak: </font>' . $Killstreak . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Deathstreak: </font>' . $Deathstreak . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Team Kills: </font>' . $TKs . '<br/><br/></td>
		</tr><tr>
		<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Headshots: </font>' . $Headshots . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Headshot Ratio: </font>' . $HSpercent . '<font class="information"> %</font><br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Suicides: </font>' . $Suicides . '<br/><br/></td>
		</tr><tr>
		<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Wins: </font>' . $Wins . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Losses: </font>' . $Losses . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Win/Loss Ratio: </font>' . $WLR . '<br/><br/></td>
		</tr><tr>
		<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Total Score: </font>' . $Score . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">High Score: </font>' . $HighScore . '<br/><br/></td>
		<td width="30%" style="text-align: left;"> <font class="information">Rounds Played: </font>' . $Rounds . '<br/><br/></td>
		</tr>
		</table>
		</div>
		';
	}
	echo '
	</td>
	</tr>
	</table>
	</div>
	';
	// double check that a matching player was found
	if($soldier_found == 1)
	{
		echo '<br/>';
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			// get weapon stats for weapon graph
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
			// get weapon stats for weapon graph
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
			<div class="middlecontent">
			<table width="100%" border="0">
			<tr>
			<th class="headline"><b>Weapons</b></th>
			</tr>
			<tr>
			<td>
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
							$weapon = preg_replace("/_/"," ",$Weapon_r['Friendlyname']);
							// rename 'death'
							if($weapon == 'Death')
							{
								$weapon = 'Machinery';
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
							[\'' . $weapon . '\', ' . $kills . ', ' . $deaths . ', ' . $headshots . ',  ' . $hsr . ']
							';
						}
						echo '
					]);
					var options = {
						title: \'Headshots\',
						titleTextStyle: {color: \'#444\', bold: \'false\'},
						legend: {textStyle: {color: \'#444\'}},
						hAxis: {title: \'Kills\', titleTextStyle: {color: \'#444\'}, textStyle:  {color: \'transparent\'}, minValue: -1, maxValue: ' . $hmax . ', gridlines: {color: \'transparent\'}, baselineColor:  \'#222\'},
						vAxis: {title: \'Deaths\', titleTextStyle: {color: \'#444\'}, textStyle:  {color: \'transparent\'}, minValue: -1, maxValue: ' . $vmax . ', gridlines: {color: \'transparent\'}, baselineColor:  \'#222\'},
						bubble: {textStyle: {color: \'#888\', fontSize: 12}, stroke: \'transparent\'},
						backgroundColor: \'transparent\',
						chartArea: {left: 20, top: 50, width: "90%", height: "70%"},
						colorAxis: {minValue: 0, colors: [\'#000064\', \'#640000\'], legend: {textStyle: {color: \'#444\'}, position: \'top\'}},
						sizeAxis: {minValue: 0, maxValue: 1, minSize: 40, maxSize: 60},
						tooltip: {textStyle: {color: \'#AA0000\'}}
					};
					// Create and draw the visualization.
					var chart = new google.visualization.BubbleChart(
					document.getElementById(\'visualization\'));
					chart.draw(data, options);
				}
				google.setOnLoadCallback(drawVisualization);
			</script>
			<center>
			<div id="visualization" style="width: 98%; height: 250px;"></div>
			</center>
			';
			// if there is a ServerID, this is a server stats page
			if(isset($ServerID) AND !is_null($ServerID))
			{
				// get weapon stats for weapon stats list
				// input as: title, damage, soldier, player id, server, db
				Statsout("Assault Rifle Stats","assaultrifle", $PlayerID, $ServerID, $BF4stats);
				Statsout("Carbine Stats","carbine", $PlayerID, $ServerID, $BF4stats);
				Statsout("Designated Marksman Rifle Stats","dmr", $PlayerID, $ServerID, $BF4stats);
				Statsout("Light Machine Gun Stats","lmg", $PlayerID, $ServerID, $BF4stats);
				Statsout("Shot Gun Stats","shotgun", $PlayerID, $ServerID, $BF4stats);
				Statsout("Submachine Gun Stats","smg", $PlayerID, $ServerID, $BF4stats);
				Statsout("Sniper Rifle Stats","sniperrifle", $PlayerID, $ServerID, $BF4stats);
				Statsout("Hand Gun Stats","handgun", $PlayerID, $ServerID, $BF4stats);
				Statsout("Projectile Explosive Stats","projectileexplosive", $PlayerID, $ServerID, $BF4stats);
				Statsout("Explosive Stats","explosive", $PlayerID, $ServerID, $BF4stats);
				Statsout("Impact Stats","impact", $PlayerID, $ServerID, $BF4stats);
				Statsout("Other Weapon Stats","melee", $PlayerID, $ServerID, $BF4stats);
				Statsout("Vehicle Stats","none", $PlayerID, $ServerID, $BF4stats);
			}
			// or else this is a global stats page
			// we could probably just use the above since $ServerID has already been determined to be null
			// but I am paranoid
			else
			{
				// get weapon stats for weapon stats list
				// input as: title, damage, soldier, player id, server, db
				// we set ServerID to null to indicate to the function to do a global query
				Statsout("Assault Rifle Stats","assaultrifle", $PlayerID, null, $BF4stats);
				Statsout("Carbine Stats","carbine", $PlayerID, null, $BF4stats);
				Statsout("Designated Marksman Rifle Stats","dmr", $PlayerID, null, $BF4stats);
				Statsout("Light Machine Gun Stats","lmg", $PlayerID, null, $BF4stats);
				Statsout("Shot Gun Stats","shotgun", $PlayerID, null, $BF4stats);
				Statsout("Submachine Gun Stats","smg", $PlayerID, null, $BF4stats);
				Statsout("Sniper Rifle Stats","sniperrifle", $PlayerID, null, $BF4stats);
				Statsout("Hand Gun Stats","handgun", $PlayerID, null, $BF4stats);
				Statsout("Projectile Explosive Stats","projectileexplosive", $PlayerID, null, $BF4stats);
				Statsout("Explosive Stats","explosive", $PlayerID, null, $BF4stats);
				Statsout("Impact Stats","impact", $PlayerID, null, $BF4stats);
				Statsout("Other Weapon Stats","melee", $PlayerID, null, $BF4stats);
				Statsout("Vehicle Stats","none", $PlayerID, null, $BF4stats);
			}
			echo '
			<br/>
			</td>
			</tr>
			</table>
			</div>
			<br/>
			';
		}
		// free up weapon chart query memory
		@mysqli_free_result($Weapon_q);
		echo '<br/>';
	}
	// begin dog tag stats
	// double check that a matching player was found
	if($soldier_found == 1)
	{
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
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
				ORDER BY Count DESC
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
				ORDER BY Count DESC
			");
		}
		// initialize value
		$count = 0;
		echo '
		<div class="middlecontent">
		<table width="100%" border="0">
		<tr>
		<th class="headline"><b>Dogtags</b></th>
		</tr>
		</table>
		<div class="innercontent">
		<br/>
		<table width="98%" align="center" border="0">
		<tr>
		<th width="100%" colspan="4" style="text-align:left">Dog tags collected by ' . $SoldierName . ':</th>
		</tr>
		';
		// check to see if the player has gotten anyone's tags
		if(@mysqli_num_rows($DogTag_q) != 0)
		{
			echo '
			<tr>
			<td width="1%" style="text-align: left">&nbsp;</td>
			<td width="5%" class="tablecontents" style="text-align: left">#</td>
			<td width="47%" class="tablecontents" style="text-align: left">Victim</td>
			<td width="47%" class="tablecontents" style="text-align: left">Count</td>
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
				<td width="1%" style="text-align: left">&nbsp;</td>
				<td width="5%" class="tablecontents" style="text-align: left"><font class="information">' . $count . ':</font></td>
				';
				// if there is a ServerID, this is a server stats page
				if(isset($ServerID) AND !is_null($ServerID))
				{
					echo '<td width="47%" class="tablecontents" style="text-align: left"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $VictimID . '&amp;search=1">' . $Victim . '</a></td>';
				}
				// or else this is a global stats page
				else
				{
					echo '<td width="47%" class="tablecontents" style="text-align: left"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $VictimID . '">' . $Victim . '</a></td>';
				}
				echo '
				<td width="47%" class="tablecontents" style="text-align: left">' . $KillCount . '</td>
				</tr>
				';
			}
		}
		else
		{
			echo '
			<tr>
			<td width="1%" style="text-align: left">&nbsp;</td>
			<td width="99%" class="tablecontents" colspan="3" style="text-align: left"><font class="information">' . $SoldierName . ' has not collected any dog tags.</font></td>
			</tr>
			';
		}
		// free up dog tag query memory
		@mysqli_free_result($DogTag_q);
		echo '
		</table>
		</div>
		';
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			// find who has killed this player
			$DogTag_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName` AS Killer, tpd.`PlayerID` AS KillerID, dt.`Count`
				FROM `tbl_dogtags` dt
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
				INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
				WHERE tpd2.`PlayerID` = {$PlayerID}
				AND tpd2.`GameID` = {$GameID}
				AND tsp.`ServerID` = {$ServerID}
				ORDER BY Count DESC
			");
		}
		// or else this is a global stats page
		else
		{
			// find who has killed this player
			$DogTag_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName` AS Killer, tpd.`PlayerID` AS KillerID, SUM(dt.`Count`) AS Count
				FROM `tbl_dogtags` dt
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
				INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
				WHERE tpd2.`PlayerID` = {$PlayerID}
				AND tpd2.`GameID` = {$GameID}
				GROUP BY Killer
				ORDER BY Count DESC
			");
		}
		// initialize value
		$count = 0;
		echo '
		<div class="innercontent"><br/>
		<table width="98%" align="center" border="0">
		<tr>
		<th width="100%" colspan="4" style="text-align:left">Players who have collected ' . $SoldierName . '\'s dog tags:</th>
		</tr>
		';
		// check to see if anyone has got the player's tags
		if(@mysqli_num_rows($DogTag_q) != 0)
		{
			echo '
			<tr>
			<td width="1%" style="text-align: left">&nbsp;</td>
			<td width="5%" class="tablecontents" style="text-align: left">#</td>
			<td width="47%" class="tablecontents" style="text-align: left">Killer</td>
			<td width="47%" class="tablecontents" style="text-align: left">Count</td>
			</tr>
			';
			while($DogTag_r = @mysqli_fetch_assoc($DogTag_q))
			{
				$Killer = $DogTag_r['Killer'];
				$KillerID = $DogTag_r['KillerID'];
				$KillCount = $DogTag_r['Count'];
				$count++;
				echo '
				<tr>
				<td width="1%" style="text-align: left">&nbsp;</td>
				<td width="5%" class="tablecontents" style="text-align: left"><font class="information">' . $count . ':</font></td>
				';
				// if there is a ServerID, this is a server stats page
				if(isset($ServerID) AND !is_null($ServerID))
				{
					echo '<td width="47%" class="tablecontents" style="text-align: left"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $KillerID . '&amp;search=1">' . $Killer . '</a></td>';
				}
				// or else this is a global stats page
				else
				{
					echo '<td width="47%" class="tablecontents" style="text-align: left"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $KillerID . '">' . $Killer . '</a></td>';
				}
				echo '
				<td width="47%" class="tablecontents" style="text-align: left">' . $KillCount . '</td>
				</tr>
				';
			}
		}
		else
		{
			echo '
			<tr>
			<td width="1%" style="text-align: left">&nbsp;</td>
			<td width="99%" class="tablecontents" colspan="3" style="text-align: left"><font class="information">No one has gotten ' . $SoldierName . '\'s tags.</font></td>
			</tr>
			';
		}
		// free up dog tag query memory
		@mysqli_free_result($DogTag_q);
		echo '
		</table>
		</div>
		<br/>
		</div>
		';
	}
	// free up player data query memory
	@mysqli_free_result($PlayerData_q);
	if($soldier_found == 1)
	{
		// make sure the GD extension is available
		if(extension_loaded('gd') AND function_exists('gd_info'))
		{
			// make new signature images for this PlayerID
			signature($PlayerID, 0, $clan_name, $BF4stats, $GameID);
			signature($PlayerID, 1, $clan_name, $BF4stats, $GameID);
			// find current URL info
			$host = 'http://' . $_SERVER['HTTP_HOST'];
			$dir = dirname($_SERVER['PHP_SELF']);
			$file = $_SERVER['PHP_SELF'];
			// show signature images
			echo '
			<br/><br/>
			<div class="middlecontent">
			<table width="100%" border="0">
			<tr>
			<th class="headline"><b>Signature Images</b></th>
			</tr>
			<tr><td>
			<div class="innercontent">
			<br/>
			<table align="center" width="98%" border="0">
			<tr>
			<td style="text-align: left;" colspan="2"><center><font class="information">Signature images use global stats from all of ' . $clan_name . '\'s Servers.</font></center><br/></td>
			</tr>
			<tr>
			<td style="text-align: left; padding: 30px;" valign="top" width="50%">
			Stats image with player\'s rank:<br/><br/>
			<img src="' . $host . $dir . '/signature/cache/PID' . $PlayerID . 'FAV0.png" alt="signature" />
			<br/>
			<font class="information">Forum BBcode:</font>
			<br/><br/>
			[URL="' . $host . $file . '?globalsearch=1&amp;PlayerID=' . $PlayerID . '"][IMG]' . $host . $dir . '/signature/cache/PID' . $PlayerID . 'FAV0.png[/IMG][/URL]<br/>
			</td>
			<td style="text-align: left; padding: 30px;" valign="top" width="50%">
			Stats image with player\'s favorite weapon:<br/><br/>
			<img src="' . $host . $dir . '/signature/cache/PID' . $PlayerID . 'FAV1.png" alt="signature" />
			<br/>
			<font class="information">Forum BBcode:</font>
			<br/><br/>
			[URL="' . $host . $file . '?globalsearch=1&amp;PlayerID=' . $PlayerID . '"][IMG]' . $host . $dir . '/signature/cache/PID' . $PlayerID . 'FAV1.png[/IMG][/URL]<br/>
			</td>
			</tr>
			</table>
			<br/>
			</div>
			</td></tr>
			</table>
			</div>
			';
		}
	}
}
elseif($SoldierName == 'Not Found')
{
	echo '
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<td>
	<br/>
	<center>
	';
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<font class="alert">The selected player ID was not found in this server.</font>';
	}
	// or else this is a global stats page
	else
	{
		echo '<font class="alert">The selected player ID was not found in these servers.</font>';
	}
	echo '
	</center>
	<br/>
	</td>
	</tr>
	</table>
	</div>
	';
}
// begin external stats block
if($SoldierName != null AND $SoldierName != 'Not Found')
{
	echo '
	<br/><br/>
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<th class="headline"><b>External Links for ' . $SoldierName . '</b></th>
	</tr>
	<tr><td>
	<div class="innercontent">
	<br/>
	<table align="center" width="95%" border="0">
	<tr>
	<td width="33%" style="text-align: center"><font class="information">Battlelog Stats: </font><a href="http://battlelog.battlefield.com/bf4/user/' . $SoldierName . '" target="_blank">www.Battlelog.Battlefield.com</a></td>
	<td width="33%" style="text-align: center"><font class="information">BF4 Stats: </font><a href="http://bf4stats.com/pc/' . $SoldierName . '" target="_blank">www.BF4stats.com</a></td>
	<td width="33%" style="text-align: center"><font class="information">Metabans: </font><a href="http://metabans.com/search/' . $SoldierName . '" target="_blank">www.Metabans.com</a></td>
	</tr>
	</table>
	<br/>
	</div>
	</td></tr>
	</table>
	</div>
	';
}
?>