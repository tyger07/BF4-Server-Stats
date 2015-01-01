<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// function to find player's weapon stats
function Statsout($damagetype,$weapon_array,$PlayerID,$ServerID,$valid_ids,$GameID,$BF4stats,$ID)
{
	// if there is a ServerID, this is a server stats page
	// also filter out 'VehicleCustom' since there is no need to waste time querying for that custom array
	if(!empty($ServerID) && ($damagetype != 'VehicleCustom'))
	{
		// see if this player has used this category's weapons
		$Weapon_q = @mysqli_query($BF4stats,"
			SELECT tws.`Friendlyname`, wa.`Kills`, wa.`Deaths`, wa.`Headshots`, (wa.`Headshots`/wa.`Kills`) AS HSR
			FROM `tbl_weapons_stats` wa
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`PlayerID` = {$PlayerID}
			AND tpd.`GameID` = {$GameID}
			AND tws.`Damagetype` = '{$damagetype}'
			AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
			ORDER BY Kills DESC, Deaths DESC
		");
	}
	// or else this is a combined stats page
	// also filter out 'VehicleCustom' since there is no need to waste time querying for that custom array
	elseif($damagetype != 'VehicleCustom')
	{
		// see if this player has used this category's weapons
		$Weapon_q = @mysqli_query($BF4stats,"
			SELECT tws.`Friendlyname`, SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
			FROM `tbl_weapons_stats` wa
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` IN ({$valid_ids})
			AND tws.`Damagetype` = '{$damagetype}'
			AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
			GROUP BY tws.`Friendlyname`
			ORDER BY Kills DESC, Deaths DESC
		");
	}
	// see if we have any records for this player for this category
	if(@mysqli_num_rows($Weapon_q) != 0)
	{
		echo '
		<table class="prettytable">
			<tr>
				<th width="23%" style="text-align:left;padding-left: 10px;">Weapon Name</th>
				<th width="19%" style="text-align:left;padding-left: 5px;"><span class="orderedDESCheader">Kills</span></th>
				<th width="19%" style="text-align:left;padding-left: 10px;">Deaths</th>
				<th width="19%" style="text-align:left;padding-left: 10px;">Headshots</th>
				<th width="20%" style="text-align:left;padding-left: 10px;">Headshot Ratio</th>
			</tr>
		';
		// set default count value
		$count = 0;
		while($Weapon_r = @mysqli_fetch_assoc($Weapon_q))
		{
			// show expand/contract if very long
			if($count == 5)
			{
				echo '
				</table>
				<div>
				<span class="expanded' . $ID . '">
				<table class="prettytable" style="margin-top: -2px;">
				';
			}
			$count++;
			$weapon = $Weapon_r['Friendlyname'];
			// rename 'Death'
			if($weapon == 'Death')
			{
				$weapon = 'Machinery';
			}
			// convert weapon to friendly name
			if(in_array($weapon,$weapon_array))
			{
				$weapon_name = array_search($weapon,$weapon_array);
				$weapon_img = './common/images/weapons/' . $weapon . '.png';
			}
			// this weapon is missing!
			else
			{
				$weapon_name = preg_replace("/_/"," ",$weapon);
				$weapon_img = './common/images/weapons/missing.png';
			}
			$kills = $Weapon_r['Kills'];
			$deaths = $Weapon_r['Deaths'];
			$headshots = $Weapon_r['Headshots'];
			$ratio = round(($Weapon_r['HSR']*100),2);
			echo '
			<tr>
				<td width="23%" class="tablecontents"  style="text-align: left;"><table width="100%" border="0"><tr><td width="120px"><img src="'. $weapon_img . '" style="height: 57px; width: 95px;" alt="' . $weapon_name . '" /></td><td style="text-align: left;" valign="middle"><font class="information">' . $weapon_name . '</font></td></tr></table></td>
				<td width="19%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $kills . '</td>
				<td width="19%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $deaths . '</td>
				<td width="19%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $headshots . '</td>
				<td width="20%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $ratio . ' <font class="information">%</font></td>
			</tr>
			';
		}
		// finish expand/contract if very long
		if($count > 5)
		{
			$remaining = $count - 5;
			echo '
			</table>
			</span>
			<a href="javascript:void(0)" class="collapsed' . $ID . '"><table class="prettytable" style="margin-top: -2px;"><tr><td class="tablecontents" style="text-align: left;padding-left: 15px;"><span class="orderedDESCheader">Show ' . $remaining . ' More</span></td></tr></table></a>
			</div>
			<table>
			<tr>
			<td>
			</td>
			</tr>
			';
		}
		echo '
		</table>
		';
	}
	// vehicle stats for 'VehicleCustom' array
	elseif($damagetype == 'VehicleCustom')
	{
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			// see if this player has used this category's weapons
			$Vehicle_q = @mysqli_query($BF4stats,"
				SELECT tws.`Fullname`, tws.`Friendlyname`, wa.`Kills`, wa.`Deaths`, wa.`Headshots`, (wa.`Headshots`/wa.`Kills`) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND (tws.`Damagetype` LIKE '%vehicle%' OR tws.`Damagetype` = 'none')
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				ORDER BY Kills DESC, Deaths DESC
			");
		}
		// or else this is a combined stats page
		else
		{
			// see if this player has used this category's weapons
			$Vehicle_q = @mysqli_query($BF4stats,"
				SELECT tws.`Fullname`, tws.`Friendlyname`, SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				AND (tws.`Damagetype` LIKE '%vehicle%' OR tws.`Damagetype` = 'none')
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				GROUP BY tws.`Fullname`
				ORDER BY Kills DESC, Deaths DESC
			");
		}
		// see if we have any records for this player for this category
		if(@mysqli_num_rows($Vehicle_q) != 0)
		{
			echo '
			<table class="prettytable">
				<tr>
					<th width="23%" style="text-align:left;padding-left: 10px;">Weapon Name</th>
					<th width="19%" style="text-align:left;padding-left: 5px;"><span class="orderedDESCheader">Kills</span></th>
					<th width="19%" style="text-align:left;padding-left: 10px;">Deaths</th>
					<th width="19%" style="text-align:left;padding-left: 10px;">Headshots</th>
					<th width="20%" style="text-align:left;padding-left: 10px;">Headshot Ratio</th>
				</tr>
			';
			// set default count value
			$count = 0;
			while($Vehicle_r = @mysqli_fetch_assoc($Vehicle_q))
			{
				// show expand/contract if very long
				if($count == 5)
				{
					echo '
					</table>
					<div>
					<span class="expanded' . $ID . '">
					<table class="prettytable" style="margin-top: -2px;">
					';
				}
				$count++;
				$weapon = $Vehicle_r['Fullname'];
				// try the full name version
				if(in_array($weapon,$weapon_array))
				{
					$weapon_name = array_search($weapon,$weapon_array);
					$weapon_img = './common/images/weapons/' . $weapon_name . '.png';
				}
				// this weapon is missing!
				// try the friendly name version
				else
				{
					$weapon = $Vehicle_r['Friendlyname'];
					if(in_array($weapon,$weapon_array))
					{
						$weapon_name = array_search($weapon,$weapon_array);
						$weapon_img = './common/images/weapons/' . $weapon . '.png';
					}
					// this weapon is still missing!
					else
					{
						$weapon_name = preg_replace("/_/"," ",$weapon);
						$weapon_img = './common/images/weapons/missing.png';
					}
				}
				$kills = $Vehicle_r['Kills'];
				$deaths = $Vehicle_r['Deaths'];
				$headshots = $Vehicle_r['Headshots'];
				$ratio = round(($Vehicle_r['HSR']*100),2);
				echo '
				<tr>
					<td width="23%" class="tablecontents"  style="text-align: left;"><table width="100%" border="0"><tr><td width="120px"><img src="'. $weapon_img . '" style="height: 57px; width: 95px;" alt="' . $weapon_name . '" /></td><td style="text-align: left;" valign="middle"><font class="information">' . $weapon_name . '</font></td></tr></table></td>
					<td width="19%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $kills . '</td>
					<td width="19%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $deaths . '</td>
					<td width="19%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $headshots . '</td>
					<td width="20%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $ratio . ' <font class="information">%</font></td>
				</tr>
				';
			}
			// finish expand/contract if very long
			if($count > 5)
			{
				$remaining = $count - 5;
				echo '
				</table>
				</span>
				<a href="javascript:void(0)" class="collapsed' . $ID . '"><table class="prettytable" style="margin-top: -2px;"><tr><td class="tablecontents" style="text-align: left;padding-left: 15px;"><span class="orderedDESCheader">Show ' . $remaining . ' More</span></td></tr></table></a>
				</div>
				<table>
				<tr>
				<td>
				</td>
				</tr>
				';
			}
			echo '
			</table>
			';
		}
	}
}
// rank queries function for player stats page
function rank($ServerID,$valid_ids,$PlayerID,$BF4stats,$GameID)
{
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
	// this is a combined stats page
	// check if this player's rank is cached in the database
	// we do this early so that we can insert dummy data now into the database (if necessary) to reduce duplicates later when the slower parallel process is executed
	// (in other words, insert dummy data now quickly, so later the parallel slow execution updates the one dummy data row instead of inserting multiple new data rows in parallel)
	if(empty($ServerID))
	{
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
		if(@mysqli_num_rows($ScoreC_q) == 0)
		{
			// insert useless dummy data for now
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'Score', '0', '0')
			");
		}
	}
	// done with the dummy cache stuff...
	// rank players by score (for real!)
	// check if score rank is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$ScoreC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$PlayerID}
			AND `category` = 'Score'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$ServerID}'
			GROUP BY `PlayerID`
		");
	}
	// or else this is a combined stats page
	else
	{
		$ScoreC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$PlayerID}
			AND `category` = 'Score'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$valid_ids}'
			GROUP BY `PlayerID`
		");
	}
	if(@mysqli_num_rows($ScoreC_q) != 0)
	{
		$ScoreC_r = @mysqli_fetch_assoc($ScoreC_q);
		$srank = $ScoreC_r['rank'];
		$timestamp = $ScoreC_r['timestamp'];
		// data older than 12 hours? or incorrect data? recalculate
		if(($timestamp <= $old) OR ($srank == 0))
		{
			// check if this is a top 20 player
			// if so, we can get their score rank much faster
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$Top_q = @mysqli_query($BF4stats,"
					SELECT `PlayerID`
					FROM `tyger_stats_top_twenty_cache`
					WHERE `SID` = '{$ServerID}'
					AND `GID` = '{$GameID}'
					AND `timestamp` >= '{$old}'
					AND `PlayerID` = {$PlayerID}
				");
			}
			// or else this is a combined stats page
			else
			{
				$Top_q = @mysqli_query($BF4stats,"
					SELECT `PlayerID`
					FROM `tyger_stats_top_twenty_cache`
					WHERE `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
					AND `timestamp` >= '{$old}'
					AND `PlayerID` = {$PlayerID}
				");
			}
			if(@mysqli_num_rows($Top_q) != 0)
			{
				// rank players by score
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					$Score_q = @mysqli_query($BF4stats,"
						SELECT sub2.rank
						FROM
							(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
							 FROM
								(SELECT `PlayerID`
								FROM `tyger_stats_top_twenty_cache`
								INNER JOIN (SELECT @num := 0) x
								WHERE `SID` = '{$ServerID}'
								AND `GID` = '{$GameID}'
								AND `timestamp` >= '{$old}'
								GROUP BY `PlayerID`
								ORDER BY `Score` DESC, `SoldierName` ASC
								) sub
							) sub2
						WHERE sub2.`PlayerID` = {$PlayerID}
					");
				}
				// or else this is a global stats page
				else
				{
					$Score_q = @mysqli_query($BF4stats,"
						SELECT sub2.rank
						FROM
							(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
							 FROM
								(SELECT `PlayerID`
								FROM `tyger_stats_top_twenty_cache`
								INNER JOIN (SELECT @num := 0) x
								WHERE `SID` = '{$valid_ids}'
								AND `GID` = '{$GameID}'
								AND `timestamp` >= '{$old}'
								GROUP BY `PlayerID`
								ORDER BY `Score` DESC, `SoldierName` ASC
								) sub
							) sub2
						WHERE sub2.`PlayerID` = {$PlayerID}
					");
				}
				if(@mysqli_num_rows($Score_q) == 1)
				{
					$Score_r = @mysqli_fetch_assoc($Score_q);
					$srank = $Score_r['rank'];
				}
				else
				{
					$srank = 0;
				}
			}
			// not in top 20
			// have to do slow query
			else
			{
				// rank players by score
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					$Score_q = @mysqli_query($BF4stats,"
						SELECT sub2.rank
						FROM
							(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
							 FROM
								(SELECT tpd.`PlayerID`
								FROM `tbl_playerdata` tpd
								INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
								INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
								INNER JOIN (SELECT @num := 0) x
								WHERE tpd.`GameID` = {$GameID}
								AND tsp.`ServerID` = {$ServerID}
								GROUP BY tpd.`PlayerID`
								ORDER BY SUM(tps.`Score`) DESC, tpd.`SoldierName` ASC
								) sub
							) sub2
						WHERE sub2.`PlayerID` = {$PlayerID}
					");
				}
				// or else this is a combined stats page
				else
				{
					$Score_q = @mysqli_query($BF4stats,"
						SELECT sub2.rank
						FROM
							(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
							 FROM
								(SELECT tpd.`PlayerID`
								FROM `tbl_playerdata` tpd
								INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
								INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
								INNER JOIN (SELECT @num := 0) x
								WHERE tpd.`GameID` = {$GameID}
								AND tsp.`ServerID` IN ({$valid_ids})
								GROUP BY tpd.`PlayerID`
								ORDER BY SUM(tps.`Score`) DESC, tpd.`SoldierName` ASC
								) sub
							) sub2
						WHERE sub2.`PlayerID` = {$PlayerID}
					");
				}
				if(@mysqli_num_rows($Score_q) == 1)
				{
					$Score_r = @mysqli_fetch_assoc($Score_q);
					$srank = $Score_r['rank'];
				}
				else
				{
					$srank = 0;
				}
			}
			// update old data in database
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$srank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'Score'
					AND `SID` = '{$ServerID}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$PlayerID}
				");
			}
			// or else this is a combined stats page
			else
			{
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$srank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'Score'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$PlayerID}
				");
			}
		}
	}
	// no score cached for this player
	else
	{
		// check if this is a top 20 player
		// if so, we can get their score rank much faster
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$Top_q = @mysqli_query($BF4stats,"
				SELECT `PlayerID`
				FROM `tyger_stats_top_twenty_cache`
				WHERE `SID` = '{$ServerID}'
				AND `GID` = '{$GameID}'
				AND `timestamp` >= '{$old}'
				AND `PlayerID` = {$PlayerID}
			");
		}
		// or else this is a combined stats page
		else
		{
			$Top_q = @mysqli_query($BF4stats,"
				SELECT `PlayerID`
				FROM `tyger_stats_top_twenty_cache`
				WHERE `SID` = '{$valid_ids}'
				AND `GID` = '{$GameID}'
				AND `timestamp` >= '{$old}'
				AND `PlayerID` = {$PlayerID}
			");
		}
		if(@mysqli_num_rows($Top_q) != 0)
		{
			// rank players by score
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$Score_q = @mysqli_query($BF4stats,"
					SELECT sub2.rank
					FROM
						(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
						 FROM
							(SELECT `PlayerID`
							FROM `tyger_stats_top_twenty_cache`
							INNER JOIN (SELECT @num := 0) x
							WHERE `SID` = '{$ServerID}'
							AND `GID` = '{$GameID}'
							AND `timestamp` >= '{$old}'
							GROUP BY `PlayerID`
							ORDER BY `Score` DESC, `SoldierName` ASC
							) sub
						) sub2
					WHERE sub2.`PlayerID` = {$PlayerID}
				");
			}
			// or else this is a combined stats page
			else
			{
				$Score_q = @mysqli_query($BF4stats,"
					SELECT sub2.rank
					FROM
						(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
						 FROM
							(SELECT `PlayerID`
							FROM `tyger_stats_top_twenty_cache`
							INNER JOIN (SELECT @num := 0) x
							WHERE `SID` = '{$valid_ids}'
							AND `GID` = '{$GameID}'
							AND `timestamp` >= '{$old}'
							GROUP BY `PlayerID`
							ORDER BY `Score` DESC, `SoldierName` ASC
							) sub
						) sub2
					WHERE sub2.`PlayerID` = {$PlayerID}
				");
			}
			if(@mysqli_num_rows($Score_q) == 1)
			{
				$Score_r = @mysqli_fetch_assoc($Score_q);
				$srank = $Score_r['rank'];
			}
			else
			{
				$srank = 0;
			}
		}
		// not in top 20
		// have to do slow query
		else
		{
			// rank players by score
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$Score_q = @mysqli_query($BF4stats,"
					SELECT sub2.rank
					FROM
						(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
						 FROM
							(SELECT tpd.`PlayerID`
							FROM `tbl_playerdata` tpd
							INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
							INNER JOIN (SELECT @num := 0) x
							WHERE tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` = {$ServerID}
							GROUP BY tpd.`PlayerID`
							ORDER BY SUM(tps.`Score`) DESC, tpd.`SoldierName` ASC
							) sub
						) sub2
					WHERE sub2.`PlayerID` = {$PlayerID}
				");
			}
			// or else this is a combined stats page
			else
			{
				$Score_q = @mysqli_query($BF4stats,"
					SELECT sub2.rank
					FROM
						(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
						 FROM
							(SELECT tpd.`PlayerID`
							FROM `tbl_playerdata` tpd
							INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
							INNER JOIN (SELECT @num := 0) x
							WHERE tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` IN ({$valid_ids})
							GROUP BY tpd.`PlayerID`
							ORDER BY SUM(tps.`Score`) DESC, tpd.`SoldierName` ASC
							) sub
						) sub2
					WHERE sub2.`PlayerID` = {$PlayerID}
				");
			}
			if(@mysqli_num_rows($Score_q) == 1)
			{
				$Score_r = @mysqli_fetch_assoc($Score_q);
				$srank = $Score_r['rank'];
			}
			else
			{
				$srank = 0;
			}
		}
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$PlayerID}', '{$GameID}', '{$ServerID}', 'Score', '{$srank}', '{$now_timestamp}')
			");
		}
		// or else this is a combined stats page
		else
		{
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'Score', '{$srank}', '{$now_timestamp}')
			");
		}
	}
	// rank players by KDR
	// check if KDR rank is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$KDRC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$PlayerID}
			AND `category` = 'KDR'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$ServerID}'
			GROUP BY `PlayerID`
		");
	}
	// or else this is a combined stats page
	else
	{
		$KDRC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$PlayerID}
			AND `category` = 'KDR'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$valid_ids}'
			GROUP BY `PlayerID`
		");
	}
	if(@mysqli_num_rows($KDRC_q) != 0)
	{
		$KDRC_r = @mysqli_fetch_assoc($KDRC_q);
		$kdrrank = $KDRC_r['rank'];
		$timestamp = $KDRC_r['timestamp'];
		// data older than 12 hours? or incorrect data? recalculate
		if(($timestamp <= $old) OR ($kdrrank == 0))
		{
			// rank players by kdr
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$KDR_q = @mysqli_query($BF4stats,"
					SELECT sub2.rank
					FROM
						(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
						 FROM
							(SELECT tpd.`PlayerID`
							FROM `tbl_playerdata` tpd
							INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
							INNER JOIN (SELECT @num := 0) x
							WHERE tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` = {$ServerID}
							GROUP BY tpd.`PlayerID`
							ORDER BY (tps.`Kills`/tps.`Deaths`) DESC, tpd.`SoldierName` ASC
							) sub
						) sub2
					WHERE sub2.`PlayerID` = {$PlayerID}
				");
			}
			// or else this is a combined stats page
			else
			{
				$KDR_q = @mysqli_query($BF4stats,"
					SELECT sub2.rank
					FROM
						(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
						 FROM
							(SELECT tpd.`PlayerID`
							FROM `tbl_playerdata` tpd
							INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
							INNER JOIN (SELECT @num := 0) x
							WHERE tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` IN ({$valid_ids})
							GROUP BY tpd.`PlayerID`
							ORDER BY (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) DESC, tpd.`SoldierName` ASC
							) sub
						) sub2
					WHERE sub2.`PlayerID` = {$PlayerID}
				");
			}
			if(@mysqli_num_rows($KDR_q) == 1)
			{
				$KDR_r = @mysqli_fetch_assoc($KDR_q);
				$kdrrank = $KDR_r['rank'];
			}
			else
			{
				$kdrrank = 0;
			}
			// update old data in database
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$kdrrank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'KDR'
					AND `SID` = '{$ServerID}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$PlayerID}
				");
			}
			// or else this is a combined stats page
			else
			{
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$kdrrank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'KDR'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$PlayerID}
				");
			}
		}
	}
	// no kdr cached for this player
	else
	{
		// rank players by kdr
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$KDR_q = @mysqli_query($BF4stats,"
				SELECT sub2.rank
				FROM
					(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
					 FROM
						(SELECT tpd.`PlayerID`
						FROM `tbl_playerdata` tpd
						INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
						INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
						INNER JOIN (SELECT @num := 0) x
						WHERE tpd.`GameID` = {$GameID}
						AND tsp.`ServerID` = {$ServerID}
						GROUP BY tpd.`PlayerID`
						ORDER BY (tps.`Kills`/tps.`Deaths`) DESC, tpd.`SoldierName` ASC
						) sub
					) sub2
				WHERE sub2.`PlayerID` = {$PlayerID}
			");
		}
		// or else this is a combined stats page
		else
		{
			$KDR_q = @mysqli_query($BF4stats,"
				SELECT sub2.rank
				FROM
					(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
					 FROM
						(SELECT tpd.`PlayerID`
						FROM `tbl_playerdata` tpd
						INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
						INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
						INNER JOIN (SELECT @num := 0) x
						WHERE tpd.`GameID` = {$GameID}
						AND tsp.`ServerID` IN ({$valid_ids})
						GROUP BY tpd.`PlayerID`
						ORDER BY (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) DESC, tpd.`SoldierName` ASC
						) sub
					) sub2
				WHERE sub2.`PlayerID` = {$PlayerID}
			");
		}
		if(@mysqli_num_rows($KDR_q) == 1)
		{
			$KDR_r = @mysqli_fetch_assoc($KDR_q);
			$kdrrank = $KDR_r['rank'];
		}
		else
		{
			$kdrrank = 0;
		}
		// add this data to the cache
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$PlayerID}', '{$GameID}', '{$ServerID}', 'KDR', '{$kdrrank}', '{$now_timestamp}')
			");
		}
		// or else this is a combined stats page
		else
		{
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'KDR', '{$kdrrank}', '{$now_timestamp}')
			");
		}
	}
	echo '
	<div style="position: relative;">
	';
	// rank players by kills
	// check if kills rank is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$KillsC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$PlayerID}
			AND `category` = 'Kills'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$ServerID}'
			GROUP BY `PlayerID`
		");
	}
	// or else this is a combined stats page
	else
	{
		$KillsC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$PlayerID}
			AND `category` = 'Kills'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$valid_ids}'
			GROUP BY `PlayerID`
		");
	}
	if(@mysqli_num_rows($KillsC_q) != 0)
	{
		$KillsC_r = @mysqli_fetch_assoc($KillsC_q);
		$killsrank = $KillsC_r['rank'];
		$timestamp = $KillsC_r['timestamp'];
		// data older than 12 hours? or incorrect data? recalculate
		if(($timestamp <= $old) OR ($killsrank == 0))
		{
			// rank players by kills
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$Kills_q = @mysqli_query($BF4stats,"
					SELECT sub2.rank
					FROM
						(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
						 FROM
							(SELECT tpd.`PlayerID`
							FROM `tbl_playerdata` tpd
							INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
							INNER JOIN (SELECT @num := 0) x
							WHERE tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` = {$ServerID}
							GROUP BY tpd.`PlayerID`
							ORDER BY tps.`Kills` DESC, tpd.`SoldierName` ASC
							) sub
						) sub2
					WHERE sub2.`PlayerID` = {$PlayerID}
				");
			}
			// or else this is a combined stats page
			else
			{
				$Kills_q = @mysqli_query($BF4stats,"
					SELECT sub2.rank
					FROM
						(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
						 FROM
							(SELECT tpd.`PlayerID`
							FROM `tbl_playerdata` tpd
							INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
							INNER JOIN (SELECT @num := 0) x
							WHERE tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` IN ({$valid_ids})
							GROUP BY tpd.`PlayerID`
							ORDER BY SUM(tps.`Kills`) DESC, tpd.`SoldierName` ASC
							) sub
						) sub2
					WHERE sub2.`PlayerID` = {$PlayerID}
				");
			}
			if(@mysqli_num_rows($Kills_q) == 1)
			{
				$Kills_r = @mysqli_fetch_assoc($Kills_q);
				$killsrank = $Kills_r['rank'];
			}
			else
			{
				$killsrank = 0;
			}
			// update old data in database
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$killsrank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'Kills'
					AND `SID` = '{$ServerID}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$PlayerID}
				");
			}
			// or else this is a combined stats page
			else
			{
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$killsrank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'Kills'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$PlayerID}
				");
			}
			echo '
			<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Recreated:<br/>Ranks</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
		}
		else
		{
			echo '
			<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Used:<br/>Ranks</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
		}
	}
	// no kills cached for this player
	else
	{
		// rank players by kills
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$Kills_q = @mysqli_query($BF4stats,"
				SELECT sub2.rank
				FROM
					(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
					 FROM
						(SELECT tpd.`PlayerID`
						FROM `tbl_playerdata` tpd
						INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
						INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
						INNER JOIN (SELECT @num := 0) x
						WHERE tpd.`GameID` = {$GameID}
						AND tsp.`ServerID` = {$ServerID}
						GROUP BY tpd.`PlayerID`
						ORDER BY tps.`Kills` DESC, tpd.`SoldierName` ASC
						) sub
					) sub2
				WHERE sub2.`PlayerID` = {$PlayerID}
			");
		}
		// or else this is a combined stats page
		else
		{
			$Kills_q = @mysqli_query($BF4stats,"
				SELECT sub2.rank
				FROM
					(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
					 FROM
						(SELECT tpd.`PlayerID`
						FROM `tbl_playerdata` tpd
						INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
						INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
						INNER JOIN (SELECT @num := 0) x
						WHERE tpd.`GameID` = {$GameID}
						AND tsp.`ServerID` IN ({$valid_ids})
						GROUP BY tpd.`PlayerID`
						ORDER BY SUM(tps.`Kills`) DESC, tpd.`SoldierName` ASC
						) sub
					) sub2
				WHERE sub2.`PlayerID` = {$PlayerID}
			");
		}
		if(@mysqli_num_rows($Kills_q) == 1)
		{
			$Kills_r = @mysqli_fetch_assoc($Kills_q);
			$killsrank = $Kills_r['rank'];
		}
		else
		{
			$killsrank = 0;
		}
		// add this data to the cache
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$PlayerID}', '{$GameID}', '{$ServerID}', 'Kills', '{$killsrank}', '{$now_timestamp}')
			");
		}
		// or else this is a combined stats page
		else
		{
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'Kills', '{$killsrank}', '{$now_timestamp}')
			");
		}
		echo '
		<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
		<div class="subsection" style="width: 100px; font-size: 12px;">
		<center>Cache Created:<br/>Ranks</center>
		</div>
		</div>
		<script type="text/javascript">
		$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
		</script>
		';
	}
	echo '
	</div>
	';
	// count the total number of players
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// query for player count
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
	}
	// or else this is a combined stats page
	else
	{
		// check to see if this count cache table exists
		@mysqli_query($BF4stats,"
			CREATE TABLE IF NOT EXISTS `tyger_stats_count_cache`
			(`category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `value` INT(10) UNSIGNED NOT NULL DEFAULT '0', `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`category`))
			ENGINE=MyISAM
			DEFAULT CHARSET=utf8
			COLLATE=utf8_bin
		");
		// check to see if player count is already cached
		$TotalRowsC_q = @mysqli_query($BF4stats,"
			SELECT DISTINCT(`value`) AS value, `timestamp`
			FROM `tyger_stats_count_cache`
			WHERE `category` = 'total_players'
			AND `SID` = '{$valid_ids}'
			AND `GID` = '{$GameID}'
		");
		// if cached...
		if(@mysqli_num_rows($TotalRowsC_q) != 0)
		{
			$TotalRowsC_r = @mysqli_fetch_assoc($TotalRowsC_q);
			$Players = $TotalRowsC_r['value'];
			$timestamp = $TotalRowsC_r['timestamp'];
			// data older than 12 hours? or incorrect data? recalculate
			if(($timestamp <= $old) OR ($Players == 0))
			{
				// find out how many rows are in the table
				$TotalRows_q = @mysqli_query($BF4stats,"
					SELECT COUNT(DISTINCT tpd.`PlayerID`) AS count
					FROM  `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
				");
				if(@mysqli_num_rows($TotalRows_q) != 0)
				{
					$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
					$Players = $TotalRows_r['count'];
				}
				else
				{
					$Players = 0;
				}
				// update old data in database
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_count_cache`
					SET `value` = '{$Players}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'total_players'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
				");
			}
		}
		// not cached.  add it
		else
		{
			// find out how many rows are in the table
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT COUNT(DISTINCT tpd.`PlayerID`) AS count
				FROM  `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
			");
			if(@mysqli_num_rows($TotalRows_q) != 0)
			{
				$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
				$Players = $TotalRows_r['count'];
			}
			else
			{
				$Players = 0;
			}
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_count_cache`
				(`category`, `GID`, `SID`, `value`, `timestamp`)
				VALUES ('total_players', '{$GameID}', '{$valid_ids}', '{$Players}', '{$now_timestamp}')
			");
		}
	}
	echo '
	<th width="15%" style="padding-left: 10px;">Score</th>
	<td width="18%" class="tablecontents" style="padding-left: 10px;"><span class="information">#</span> ' . $srank . ' <span class="information">of</span> ' . $Players . '</td>
	<th width="15%" style="padding-left: 10px;">Kills</th>
	<td width="18%" class="tablecontents" style="padding-left: 10px;"><span class="information">#</span> ' . $killsrank . ' <span class="information">of</span> ' . $Players . '</td>
	<th width="15%" style="padding-left: 10px;">Kill / Death</th>
	<td width="18%" class="tablecontents" style="padding-left: 10px;"><span class="information">#</span> ' . $kdrrank . ' <span class="information">of</span> ' . $Players . '</td>
	';
}
// function to create pagination links
function pagination_links($ServerID,$root,$page,$currentpage,$totalpages,$rank,$order,$query)
{
	echo '
	<div class="pagination">
	';
	// reduce pagination width if few page results were found
	if($totalpages == 1)
	{
		echo '
		<table class="prettytable" style="width: 10%">
		';
	}
	elseif($totalpages <= 3 && $totalpages >= 2)
	{
		echo '
		<table class="prettytable" style="width: 30%">
		';
	}
	else
	{
		echo '
		<table class="prettytable" style="width: 60%">
		';
	}
	echo '
	<tr>
	';
	// range of number of links to show
	// the range changes at the lowest and highest numbers to make the number of link outputs the same
	// low end
	if($currentpage == 4)
	{
		$range = 4;
	}
	elseif($currentpage == 3)
	{
		$range = 5;
	}
	elseif($currentpage == 2)
	{
		$range = 6;
	}
	elseif($currentpage == 1)
	{
		$range = 7;
	}
	// high end
	elseif($currentpage == ($totalpages - 3))
	{
		$range = 4;
	}
	elseif($currentpage == ($totalpages - 2))
	{
		$range = 5;
	}
	elseif($currentpage == ($totalpages - 1))
	{
		$range = 6;
	}
	elseif($currentpage == $totalpages)
	{
		$range = 7;
	}
	// the default if not at the low or high end
	else
	{
		$range = 3;
	}
	// if on page 1, don't show earlier page links
	if ($currentpage > 1)
	{
		// show first page link to go back to first page
		echo '
		<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=1&amp;r=' . $rank . '&amp;o=' . $order;
		if(!empty($ServerID))
		{
			echo '&amp;sid=' . $ServerID;
		}
		if(!empty($query))
		{
			echo '&amp;q=' . $query;
		}
		echo '">1</a></td>
		';
		// get previous page number
		$prevpage = $currentpage - 1;
		// show ... as spacer if beyond the first pages
		if (($currentpage - $range) > 3)
		{
			echo '
			<td width="9%" class="pagspace">...</td>
			';
		}
		// show page 2 instead of ... if the ... would have represented page 2 anyways
		elseif (($currentpage - $range) == 3)
		{
			echo '
			<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=2&amp;r=' . $rank . '&amp;o=' . $order;
			if(!empty($ServerID))
			{
				echo '&amp;sid=' . $ServerID;
			}
			if(!empty($query))
			{
				echo '&amp;q=' . $query;
			}
			echo '">2</a></td>
			';
		}
	}
	// loop to show links to pages in a range of pages around current page
	for($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++)
	{
		// handle the first and last pages differently
		if ((($x == 1) || ($x == $totalpages)) && ($x == $currentpage))
		{
			// 'highlight' the current page but don't make it a link
			echo '
			<td width="9%" class="pagcountselected"><font class="information">' . $x . '</font></td>
			';
		}
		// if it's a valid page number... and isn't the first or last page
		if (($x > 1) && ($x < $totalpages))
		{
			// if we're on current page...
			if ($x == $currentpage)
			{
				// 'highlight' the current page but don't make it a link
				echo '
				<td width="9%" class="pagcountselected"><font class="information">' . $x . '</font></td>
				';
			}
			else
			{
				// make it a link
				echo '
				<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=' . $x . '&amp;r=' . $rank . '&amp;o=' . $order;
				if(!empty($ServerID))
				{
					echo '&amp;sid=' . $ServerID;
				}
				if(!empty($query))
				{
					echo '&amp;q=' . $query;
				}
				echo '">' . $x . '</a></td>
				';
			}
		}
	}
	// if not on last page, show forward links        
	if ($currentpage != $totalpages)
	{
		// get next page
		$nextpage = $currentpage + 1;
		// show ... as spacer if before the last pages
		if (($currentpage + $range) < ($totalpages - 2))
		{
			echo '<td width="9%" class="pagspace">...</td>';
		}
		// show 2nd-to-last page instead of ... if the ... would have represented 2nd-to-last page anyways
		elseif(($currentpage + $range) == ($totalpages - 2))
		{
			$onelesstotalpages = $totalpages - 1;
			echo '
			<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=' . $onelesstotalpages . '&amp;r=' . $rank . '&amp;o=' . $order;
			if(!empty($ServerID))
			{
				echo '&amp;sid=' . $ServerID;
			}
			if(!empty($query))
			{
				echo '&amp;q=' . $query;
			}
			echo '">' . $onelesstotalpages . '</a></td>
			';
		}
		// show last page link
		echo '
		<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=' . $totalpages . '&amp;r=' . $rank . '&amp;o=' . $order;
		if(!empty($ServerID))
		{
			echo '&amp;sid=' . $ServerID;
		}
		if(!empty($query))
		{
			echo '&amp;q=' . $query;
		}
		echo '">' . $totalpages . '</a></td>
		';
	}
	echo '
	</tr>
	</table>
	</div>
	';
}
// function to create pagination table headers
function pagination_headers($columnname,$ServerID,$targetpage,$width,$ranktext,$rank,$targetrank,$ordertext,$order,$targetorder,$nextorder,$currentpage,$colspan,$player,$query)
{
	if(empty($colspan))
	{
		$colspan = 1;
	}
	// build this column's link
	$link = './index.php?';
	if(!empty($ServerID))
	{
		$link .= 'sid=' . $ServerID . '&amp;';
	}
	if(!empty($player))
	{
		$link .= 'player=' . $player . '&amp;';
	}
	if(!empty($query))
	{
		$link .= 'q=' . $query . '&amp;';
	}
	$link .= 'p=' . $targetpage . '&amp;' . $ranktext . '=' . $targetrank . '&amp;' . $ordertext . '=';
	if($rank != $targetrank)
	{
		$link .= $targetorder;
	}
	else
	{
		$link .= $nextorder . '&amp;cp=' . $currentpage;
	}
	// then echo out html table header
	echo '
	<th width="' . $width . '%" colspan="' . $colspan . '" style="text-align:left; position: relative;">
		<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
			<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
		</div>
		<a href="' . $link . '"><span class="order';
		if($rank == $targetrank)
		{
			echo 'ed' . $order;
		}
		echo 'header">' . $columnname . '</span></a>
	</th>
	';
}
// function to count sessions
function session_count($userip, $ServerID, $GameID, $BF4stats)
{
	// check to see if the session table exists
	@mysqli_query($BF4stats,"
		CREATE TABLE IF NOT EXISTS `tyger_stats_sessions`
		(`IP` VARCHAR(45) NULL DEFAULT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` INT(11) NOT NULL DEFAULT '0', `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`IP`))
		ENGINE=MyISAM
		DEFAULT CHARSET=utf8
		COLLATE=utf8_bin
	");
	// initialize values
	$now_timestamp = time();
	$old = $now_timestamp - 1800;
	// check if this user already has a session stored
	$exist_query = @mysqli_query($BF4stats,"
		SELECT DISTINCT(`IP`) AS IP
		FROM `tyger_stats_sessions`
		WHERE `IP` = '{$userip}'
		AND `SID` = '{$ServerID}'
		AND `GID` = '{$GameID}'
	");
	// user IP found, update timestamp
	if(@mysqli_num_rows($exist_query) != 0)
	{
		@mysqli_query($BF4stats,"
			UPDATE `tyger_stats_sessions`
			SET `timestamp` = {$now_timestamp}
			WHERE `IP` = '{$userip}'
			AND `SID` = '{$ServerID}'
			AND `GID` = '{$GameID}'
		");
	}
	// user IP not found, add it to session table
	else
	{
		@mysqli_query($BF4stats,"
			INSERT INTO `tyger_stats_sessions`
			(`IP`, `GID`, `SID`, `timestamp`)
			VALUES ('{$userip}', '{$GameID}', '{$ServerID}', '{$now_timestamp}')
		");
	}
	// find if there are sessions older than 30 minutes
	// check this to avoid optimizing the table (slow) when it isn't necessary
	$old_query = @mysqli_query($BF4stats,"
		SELECT `IP`
		FROM `tyger_stats_sessions`
		WHERE `timestamp` <= '{$old}'
	");
	// remove sessions older than 30 minutes
	if(@mysqli_num_rows($old_query) != 0)
	{
		@mysqli_query($BF4stats,"
			DELETE FROM `tyger_stats_sessions`
			WHERE `timestamp` <= '{$old}'
		");
		// optimize this session table
		@mysqli_query($BF4stats,"
			OPTIMIZE TABLE `tyger_stats_sessions`
		");
	}
	// count all sessions
	$ses_count = @mysqli_query($BF4stats,"
		SELECT COUNT(DISTINCT(`IP`)) AS ses
		FROM `tyger_stats_sessions`
		WHERE `SID` = '{$ServerID}'
		AND `GID` = '{$GameID}'
	");
	if(@mysqli_num_rows($ses_count) != 0)
	{
		$ses_row = @mysqli_fetch_assoc($ses_count);
		$ses = $ses_row['ses'];
	}
	else
	{
		$ses = 0;
	}
	// return the value out of the function
	return $ses;
}
// function to cache total players
function cache_total_players($ServerID, $valid_ids, $GameID, $BF4stats)
{
	// check to see if this count cache table exists
	@mysqli_query($BF4stats,"
		CREATE TABLE IF NOT EXISTS `tyger_stats_count_cache`
		(`category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `value` INT(10) UNSIGNED NOT NULL DEFAULT '0', `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`category`))
		ENGINE=MyISAM
		DEFAULT CHARSET=utf8
		COLLATE=utf8_bin
	");
	// initialize timestamp values
	$now_timestamp = time();
	$old = $now_timestamp - 43200;
	// check to see if player count is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$TotalRowsC_q = @mysqli_query($BF4stats,"
			SELECT DISTINCT(`value`) AS value, `timestamp`
			FROM `tyger_stats_count_cache`
			WHERE `category` = 'total_players'
			AND `SID` = '{$ServerID}'
			AND `GID` = '{$GameID}'
		");
	}
	// or else this is a combined stats page
	else
	{
		$TotalRowsC_q = @mysqli_query($BF4stats,"
			SELECT DISTINCT(`value`) AS value, `timestamp`
			FROM `tyger_stats_count_cache`
			WHERE `category` = 'total_players'
			AND `SID` = '{$valid_ids}'
			AND `GID` = '{$GameID}'
		");
	}
	// if cached...
	if(@mysqli_num_rows($TotalRowsC_q) != 0)
	{
		$TotalRowsC_r = @mysqli_fetch_assoc($TotalRowsC_q);
		$total_players = $TotalRowsC_r['value'];
		$timestamp = $TotalRowsC_r['timestamp'];
		// data older than 12 hours? or incorrect data? recalculate
		if(($timestamp <= $old) OR ($total_players == 0))
		{
			// find out how many rows are in the table
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$TotalRows_q = @mysqli_query($BF4stats,"
					SELECT COUNT(DISTINCT tpd.`PlayerID`) AS count
					FROM  `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` = {$ServerID}
				");
			}
			// or else this is a combined stats page
			else
			{
				$TotalRows_q = @mysqli_query($BF4stats,"
					SELECT COUNT(DISTINCT tpd.`PlayerID`) AS count
					FROM  `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
				");
			}
			if(@mysqli_num_rows($TotalRows_q) != 0)
			{
				$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
				$total_players = $TotalRows_r['count'];
			}
			else
			{
				$total_players = 0;
			}
			if(!empty($ServerID))
			{
				// update old data in database
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_count_cache`
					SET `value` = '{$total_players}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'total_players'
					AND `SID` = '{$ServerID}'
					AND `GID` = '{$GameID}'
				");
			}
			else
			{
				// update old data in database
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_count_cache`
					SET `value` = '{$total_players}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'total_players'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
				");
			}
			echo '
			<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Recreated:<br/>Player Count</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
		}
		else
		{
			echo '
			<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Used:<br/>Player Count</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
		}
	}
	// not cached.  add it
	else
	{
		// find out how many rows are in the table
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT COUNT(DISTINCT tpd.`PlayerID`) AS count
				FROM  `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` = {$ServerID}
			");
		}
		// or else this is a combined stats page
		else
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT COUNT(DISTINCT tpd.`PlayerID`) AS count
				FROM  `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
			");
		}
		if(@mysqli_num_rows($TotalRows_q) != 0)
		{
			$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
			$total_players = $TotalRows_r['count'];
		}
		else
		{
			$total_players = 0;
		}
		if(!empty($ServerID))
		{
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_count_cache`
				(`category`, `GID`, `SID`, `value`, `timestamp`)
				VALUES ('total_players', '{$GameID}', '{$ServerID}', '{$total_players}', '{$now_timestamp}')
			");
		}
		else
		{
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_count_cache`
				(`category`, `GID`, `SID`, `value`, `timestamp`)
				VALUES ('total_players', '{$GameID}', '{$valid_ids}', '{$total_players}', '{$now_timestamp}')
			");
		}
		echo '
		<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
		<div class="subsection" style="width: 100px; font-size: 12px;">
		<center>Cache Created:<br/>Player Count</center>
		</div>
		</div>
		<script type="text/javascript">
		$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
		</script>
		';
	}
	// return the value out of the function
	return $total_players;
}

// function to cache total suspects
function cache_total_suspects($ServerID, $valid_ids, $GameID, $BF4stats)
{
	// check to see if this count cache table exists
	@mysqli_query($BF4stats,"
		CREATE TABLE IF NOT EXISTS `tyger_stats_count_cache`
		(`category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `value` INT(10) UNSIGNED NOT NULL DEFAULT '0', `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`category`))
		ENGINE=MyISAM
		DEFAULT CHARSET=utf8
		COLLATE=utf8_bin
	");
	// initialize timestamp values
	$now_timestamp = time();
	$old = $now_timestamp - 43200;
	// check to see if player count is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$TotalRowsC_q = @mysqli_query($BF4stats,"
			SELECT DISTINCT(`value`) AS value, `timestamp`
			FROM `tyger_stats_count_cache`
			WHERE `category` = 'total_suspects'
			AND `SID` = '{$ServerID}'
			AND `GID` = '{$GameID}'
		");
	}
	// or else this is a combined stats page
	else
	{
		$TotalRowsC_q = @mysqli_query($BF4stats,"
			SELECT DISTINCT(`value`) AS value, `timestamp`
			FROM `tyger_stats_count_cache`
			WHERE `category` = 'total_suspects'
			AND `SID` = '{$valid_ids}'
			AND `GID` = '{$GameID}'
		");
	}
	// if cached...
	if(@mysqli_num_rows($TotalRowsC_q) != 0)
	{
		$TotalRowsC_r = @mysqli_fetch_assoc($TotalRowsC_q);
		$numrows = $TotalRowsC_r['value'];
		$timestamp = $TotalRowsC_r['timestamp'];
		// data older than 12 hours? or incorrect data? recalculate
		if(($timestamp <= $old) OR ($numrows == 0))
		{
			// find out how many rows are in the table
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$TotalRows_q = @mysqli_query($BF4stats,"
					SELECT COUNT(DISTINCT(tpd.`PlayerID`)) AS count
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE tsp.`ServerID` = {$ServerID}
					AND (((tps.`Kills`/tps.`Deaths`) > 5 AND (tps.`Headshots`/tps.`Kills`) > 0.70 AND tps.`Kills` > 30 AND tps.`Rounds` > 1) OR ((tps.`Kills`/tps.`Deaths`) > 10 AND tps.`Kills` > 50 AND tps.`Rounds` > 1))
					AND tpd.`GameID` = {$GameID}
				");
			}
			// or else this is a combined stats page
			else
			{
				$TotalRows_q = @mysqli_query($BF4stats,"
					SELECT COUNT(DISTINCT(tpd.`PlayerID`)) AS count
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE (((tps.`Kills`/tps.`Deaths`) > 5 AND (tps.`Headshots`/tps.`Kills`) > 0.70 AND tps.`Kills` > 30 AND tps.`Rounds` > 1) OR ((tps.`Kills`/tps.`Deaths`) > 10 AND tps.`Kills` > 50 AND tps.`Rounds` > 1))
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
				");
			}
			if(@mysqli_num_rows($TotalRows_q) != 0)
			{
				$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
				$numrows = $TotalRows_r['count'];
			}
			else
			{
				$numrows = 0;
			}
			if(!empty($ServerID))
			{
				// update old data in database
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_count_cache`
					SET `value` = '{$numrows}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'total_suspects'
					AND `SID` = '{$ServerID}'
					AND `GID` = '{$GameID}'
				");
			}
			else
			{
				// update old data in database
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_count_cache`
					SET `value` = '{$numrows}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'total_suspects'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
				");
			}
			echo '
			<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Recreated:<br/>Suspect Count</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
		}
		else
		{
			echo '
			<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Used:<br/>Suspect Count</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
		}
	}
	// not cached.  add it
	else
	{
		// find out how many rows are in the table
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT COUNT(DISTINCT(tpd.`PlayerID`)) AS count
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND (((tps.`Kills`/tps.`Deaths`) > 5 AND (tps.`Headshots`/tps.`Kills`) > 0.70 AND tps.`Kills` > 30 AND tps.`Rounds` > 1) OR ((tps.`Kills`/tps.`Deaths`) > 10 AND tps.`Kills` > 50 AND tps.`Rounds` > 1))
				AND tpd.`GameID` = {$GameID}
			");
		}
		// or else this is a combined stats page
		else
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT COUNT(DISTINCT(tpd.`PlayerID`)) AS count
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE (((tps.`Kills`/tps.`Deaths`) > 5 AND (tps.`Headshots`/tps.`Kills`) > 0.70 AND tps.`Kills` > 30 AND tps.`Rounds` > 1) OR ((tps.`Kills`/tps.`Deaths`) > 10 AND tps.`Kills` > 50 AND tps.`Rounds` > 1))
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
			");
		}
		if(@mysqli_num_rows($TotalRows_q) != 0)
		{
			$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
			$numrows = $TotalRows_r['count'];
		}
		else
		{
			$numrows = 0;
		}
		if(!empty($ServerID))
		{
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_count_cache`
				(`category`, `GID`, `SID`, `value`, `timestamp`)
				VALUES ('total_suspects', '{$GameID}', '{$ServerID}', '{$numrows}', '{$now_timestamp}')
			");
		}
		else
		{
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_count_cache`
				(`category`, `GID`, `SID`, `value`, `timestamp`)
				VALUES ('total_suspects', '{$GameID}', '{$valid_ids}', '{$numrows}', '{$now_timestamp}')
			");
		}
		echo '
		<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
		<div class="subsection" style="width: 100px; font-size: 12px;">
		<center>Cache Created:<br/>Suspect Count</center>
		</div>
		</div>
		<script type="text/javascript">
		$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
		</script>
		';
	}
	// return the value out of the function
	return $numrows;
}

// function to cache total chat rows
function cache_total_chat($ServerID, $valid_ids, $GameID, $BF4stats)
{
	// check to see if this count cache table exists
	@mysqli_query($BF4stats,"
		CREATE TABLE IF NOT EXISTS `tyger_stats_count_cache`
		(`category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `value` INT(10) UNSIGNED NOT NULL DEFAULT '0', `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`category`))
		ENGINE=MyISAM
		DEFAULT CHARSET=utf8
		COLLATE=utf8_bin
	");
	// initialize timestamp values
	$now_timestamp = time();
	$old = $now_timestamp - 3600;
	// check to see if chat count is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$TotalRowsC_q = @mysqli_query($BF4stats,"
			SELECT DISTINCT(`value`) AS value, `timestamp`
			FROM `tyger_stats_count_cache`
			WHERE `category` = 'total_chat'
			AND `SID` = '{$ServerID}'
			AND `GID` = '{$GameID}'
		");
	}
	// otherwise this is a combined stats page
	else
	{
		$TotalRowsC_q = @mysqli_query($BF4stats,"
			SELECT DISTINCT(`value`) AS value, `timestamp`
			FROM `tyger_stats_count_cache`
			WHERE `category` = 'total_chat'
			AND `SID` = '{$valid_ids}'
			AND `GID` = '{$GameID}'
		");
	}
	// if cached...
	if(@mysqli_num_rows($TotalRowsC_q) != 0)
	{
		$TotalRowsC_r = @mysqli_fetch_assoc($TotalRowsC_q);
		$numrows = $TotalRowsC_r['value'];
		$timestamp = $TotalRowsC_r['timestamp'];
		// data older than 1 hour? or incorrect data? recalculate
		if(($timestamp <= $old) OR ($numrows == 0))
		{
			// find out how many rows are in the table
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$TotalRows_q = @mysqli_query($BF4stats,"
					SELECT count(`ID`) AS count
					FROM `tbl_chatlog`
					WHERE `ServerID` = {$ServerID}
				");
			}
			// otherwise this is a combined stats page
			else
			{
				$TotalRows_q = @mysqli_query($BF4stats,"
					SELECT count(`ID`) AS count
					FROM `tbl_chatlog`
					WHERE `ServerID` IN ({$valid_ids})
				");
			}
			if(@mysqli_num_rows($TotalRows_q) != 0)
			{
				$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
				$numrows = $TotalRows_r['count'];
			}
			else
			{
				$numrows = 0;
			}
			if(!empty($ServerID))
			{
				// update old data in database
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_count_cache`
					SET `value` = '{$numrows}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'total_chat'
					AND `SID` = '{$ServerID}'
					AND `GID` = '{$GameID}'
				");
			}
			else
			{
				// update old data in database
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_count_cache`
					SET `value` = '{$numrows}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'total_chat'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
				");
			}
			echo '
			<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Recreated:<br/>Chat Count</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
		}
		else
		{
			echo '
			<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Used:<br/>Chat Count</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
		}
	}
	// not cached.  add it
	else
	{
		// find out how many rows are in the table
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT count(`ID`) AS count
				FROM `tbl_chatlog`
				WHERE `ServerID` = {$ServerID}
			");
		}
		// or else this is a combined stats page
		else
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT count(`ID`) AS count
				FROM `tbl_chatlog`
				WHERE `ServerID` IN ({$valid_ids})
			");
		}
		if(@mysqli_num_rows($TotalRows_q) != 0)
		{
			$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
			$numrows = $TotalRows_r['count'];
		}
		else
		{
			$numrows = 0;
		}
		if(!empty($ServerID))
		{
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_count_cache`
				(`category`, `GID`, `SID`, `value`, `timestamp`)
				VALUES ('total_chat', '{$GameID}', '{$ServerID}', '{$numrows}', '{$now_timestamp}')
			");
		}
		else
		{
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_count_cache`
				(`category`, `GID`, `SID`, `value`, `timestamp`)
				VALUES ('total_chat', '{$GameID}', '{$valid_ids}', '{$numrows}', '{$now_timestamp}')
			");
		}
		echo '
		<div id="cache_fade" style="position: absolute; top: 3px; left: -150px; display: none;">
		<div class="subsection" style="width: 100px; font-size: 12px;">
		<center>Cache Created:<br/>Chat Count</center>
		</div>
		</div>
		<script type="text/javascript">
		$("#cache_fade").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
		</script>
		';
		
	}
	// return the value out of the function
	return $numrows;
}

// function to cache top 20 players
function cache_top_twenty($ServerID, $valid_ids, $GameID, $BF4stats)
{
	// check to see if this top twenty cache table exists
	@mysqli_query($BF4stats,"
		CREATE TABLE IF NOT EXISTS `tyger_stats_top_twenty_cache`
		(`PlayerID` INT(10) UNSIGNED NOT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `SoldierName` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `Score` INT(11) NOT NULL DEFAULT '0', `Kills` INT(11) NOT NULL DEFAULT '0', `KDR` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `HSR` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`PlayerID`, `SID`))
		ENGINE=MyISAM
		DEFAULT CHARSET=utf8
		COLLATE=utf8_bin
	");
	// initialize timestamp values
	$now_timestamp = time();
	$old = $now_timestamp - 43200;
	// check to see if top 20 is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$TopC_q = @mysqli_query($BF4stats,"
			SELECT `PlayerID`, `SoldierName`, `Score`, `Kills`, `KDR`, `HSR`, `timestamp`
			FROM `tyger_stats_top_twenty_cache`
			WHERE `SID` = '{$ServerID}'
			AND `GID` = '{$GameID}'
			AND `timestamp` >= '{$old}'
			GROUP BY `PlayerID`
			ORDER BY `Score` DESC, `SoldierName` ASC
		");
	}
	// or else this is a combined stats page
	else
	{
		$TopC_q = @mysqli_query($BF4stats,"
			SELECT `PlayerID`, `SoldierName`, `Score`, `Kills`, `KDR`, `HSR`, `timestamp`
			FROM `tyger_stats_top_twenty_cache`
			WHERE `SID` = '{$valid_ids}'
			AND `GID` = '{$GameID}'
			AND `timestamp` >= '{$old}'
			GROUP BY `PlayerID`
			ORDER BY `Score` DESC, `SoldierName` ASC
		");
	}
	// if cached and data is newer than 12 hours old...
	if(@mysqli_num_rows($TopC_q) != 0)
	{
		echo '
		<div id="cache_fade2" style="position: absolute; top: ';
		if(!empty($ServerID))
		{
			echo '3px;';
		}
		else
		{
			echo '50px;';
		}
		echo ' left: -150px; display: none;">
		<div class="subsection" style="width: 100px; font-size: 12px;">
		<center>Cache Used:<br/>Top Twenty</center>
		</div>
		</div>
		<script type="text/javascript">
		$("#cache_fade2").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
		</script>
		';
		// return the value out of the function
		return $TopC_q;
	}
	// otherwise, cache or re-cache
	else
	{
		// delete old rows
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			@mysqli_query($BF4stats,"
				DELETE
				FROM `tyger_stats_top_twenty_cache`
				WHERE `timestamp` <= '{$old}'
				AND `SID` = '{$ServerID}'
				AND `GID` = '{$GameID}'
			");
			@mysqli_query($BF4stats,"
				OPTIMIZE TABLE `tyger_stats_top_twenty_cache`
			");
		}
		else
		{
			@mysqli_query($BF4stats,"
				DELETE
				FROM `tyger_stats_top_twenty_cache`
				WHERE `timestamp` <= '{$old}'
				AND `SID` = '{$valid_ids}'
				AND `GID` = '{$GameID}'
			");
			@mysqli_query($BF4stats,"
				OPTIMIZE TABLE `tyger_stats_top_twenty_cache`
			");
		}
		// insert new rows
		// get the info from the db
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`GameID` = {$GameID}
				ORDER BY Score DESC, tpd.`SoldierName` ASC
				LIMIT 0, 20
			");
		}
		// or else this is a global stats page
		else
		{
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`PlayerID`
				ORDER BY Score DESC, tpd.`SoldierName` ASC
				LIMIT 0, 20
			");
		}
		while($Players_r = @mysqli_fetch_assoc($Players_q))
		{
			$Score = $Players_r['Score'];
			$SoldierName = $Players_r['SoldierName'];
			$PlayerID = $Players_r['PlayerID'];
			$Kills = $Players_r['Kills'];
			$KDR = round($Players_r['KDR'],2);
			$HSR = round($Players_r['HSR'],4);
			// insert into db
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				@mysqli_query($BF4stats,"
					INSERT INTO `tyger_stats_top_twenty_cache`
					(`PlayerID`, `GID`, `SID`, `SoldierName`, `Score`, `Kills`, `KDR`, `HSR`, `timestamp`)
					VALUES ('{$PlayerID}', '{$GameID}', '{$ServerID}', '{$SoldierName}', '{$Score}', '{$Kills}', '{$KDR}', '{$HSR}', '{$now_timestamp}')
				");
			}
			// or else this is a global stats page
			else
			{
				@mysqli_query($BF4stats,"
					INSERT INTO `tyger_stats_top_twenty_cache`
					(`PlayerID`, `GID`, `SID`, `SoldierName`, `Score`, `Kills`, `KDR`, `HSR`, `timestamp`)
					VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', '{$SoldierName}', '{$Score}', '{$Kills}', '{$KDR}', '{$HSR}', '{$now_timestamp}')
				");
			}
		}
		// query the cache again for the new info
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$TopC_q = @mysqli_query($BF4stats,"
				SELECT `PlayerID`, `SoldierName`, `Score`, `Kills`, `KDR`, `HSR`, `timestamp`
				FROM `tyger_stats_top_twenty_cache`
				WHERE `SID` = '{$ServerID}'
				AND `GID` = '{$GameID}'
				AND `timestamp` >= '{$old}'
				GROUP BY `PlayerID`
				ORDER BY `Score` DESC, `SoldierName` ASC
			");
		}
		else
		{
			$TopC_q = @mysqli_query($BF4stats,"
				SELECT `PlayerID`, `SoldierName`, `Score`, `Kills`, `KDR`, `HSR`, `timestamp`
				FROM `tyger_stats_top_twenty_cache`
				WHERE `SID` = '{$valid_ids}'
				AND `GID` = '{$GameID}'
				AND `timestamp` >= '{$old}'
				GROUP BY `PlayerID`
				ORDER BY `Score` DESC, `SoldierName` ASC
			");
		}
		// if cached and data is newer than 12 hours old...
		if(@mysqli_num_rows($TopC_q) != 0)
		{
			echo '
			<div id="cache_fade2" style="position: absolute; top: ';
			if(!empty($ServerID))
			{
				echo '3px;';
			}
			else
			{
				echo '50px;';
			}
			echo ' left: -150px; display: none;">
			<div class="subsection" style="width: 100px; font-size: 12px;">
			<center>Cache Used:<br/>Top Twenty</center>
			</div>
			</div>
			<script type="text/javascript">
			$("#cache_fade2").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
			</script>
			';
			// return the value out of the function
			return $TopC_q;
		}
	}
}
// function to replace dangerous characters in content
function textcleaner($content)
{
	$content = preg_replace("/&/","&amp;",$content);
	$content = preg_replace("/'/","&#39;",$content);
	$content = preg_replace("/</","&lt;",$content);
	$content = preg_replace("/>/","&gt;",$content);
	// return the value out of the function
	return $content;
}
?>