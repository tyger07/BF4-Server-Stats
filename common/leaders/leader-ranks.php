<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// check to see if this rank cache table exists
@mysqli_query($BF4stats,"
	CREATE TABLE IF NOT EXISTS `tyger_stats_rank_cache`
	(
		`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		`PlayerID` INT(10) UNSIGNED NOT NULL,
		`GID` TINYINT(4) UNSIGNED NOT NULL,
		`SID` VARCHAR(100) NOT NULL,
		`category` VARCHAR(20) NOT NULL,
		`rank` INT(10) UNSIGNED NOT NULL DEFAULT '0',
		`timestamp` INT(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`ID`),
		UNIQUE `UNIQUE_RankData` (`PlayerID`, `GID`, `SID`, `category`),
		INDEX `PlayerID` (`PlayerID` ASC),
		INDEX `GID` (`GID` ASC),
		INDEX `SID` (`SID` ASC),
		INDEX `category` (`category` ASC),
		INDEX `timestamp` (`timestamp` ASC),
		CONSTRAINT `fk_tyger_stats_rank_cache_PlayerID` FOREIGN KEY (`PlayerID`) REFERENCES `tbl_playerdata`(`PlayerID`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `fk_tyger_stats_rank_cache_GID` FOREIGN KEY (`GID`) REFERENCES `tbl_games`(`GameID`) ON DELETE CASCADE ON UPDATE CASCADE
	)
	ENGINE=InnoDB
");
// check to see if this top twenty cache table exists
@mysqli_query($BF4stats,"
	CREATE TABLE IF NOT EXISTS `tyger_stats_top_twenty_cache`
	(
		`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		`PlayerID` INT(10) UNSIGNED NOT NULL,
		`GID` TINYINT(4) UNSIGNED NOT NULL,
		`SID` VARCHAR(100) NOT NULL,
		`SoldierName` VARCHAR(45) NOT NULL,
		`Score` INT(11) NOT NULL DEFAULT '0',
		`Playtime` INT(11) NOT NULL DEFAULT '0',
		`Kills` INT(11) NOT NULL DEFAULT '0',
		`KDR` VARCHAR(20) NOT NULL,
		`HSR` VARCHAR(20) NOT NULL,
		`timestamp` INT(11) NOT NULL DEFAULT '0',
		PRIMARY KEY (`ID`),
		UNIQUE `UNIQUE_TopTwentyData` (`PlayerID`, `GID`, `SID`),
		INDEX `PlayerID` (`PlayerID` ASC),
		INDEX `GID` (`GID` ASC),
		INDEX `SID` (`SID` ASC),
		INDEX `SoldierName` (`SoldierName` ASC),
		INDEX `Score` (`Score` ASC),
		INDEX `timestamp` (`timestamp` ASC),
		CONSTRAINT `fk_tyger_stats_top_twenty_cache_PlayerID` FOREIGN KEY (`PlayerID`) REFERENCES `tbl_playerdata`(`PlayerID`) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `fk_tyger_stats_top_twenty_cache_GID` FOREIGN KEY (`GID`) REFERENCES `tbl_games`(`GameID`) ON DELETE CASCADE ON UPDATE CASCADE
	)
	ENGINE=InnoDB
");
// initialize timestamp values
$now_timestamp = time();
$old = $now_timestamp - 10800;
if($rank == 'SoldierName')
{
	if(!empty($ServerID))
	{
		$rank_q = @mysqli_query($BF4stats,"
			SELECT sub2.rank
			FROM
				(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
				 FROM
					(SELECT tpd.`SoldierName`
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					INNER JOIN (SELECT @num := 0) x
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` = {$ServerID}
					GROUP BY tpd.`SoldierName`
					ORDER BY tpd.`SoldierName` ASC
					) sub
				) sub2
			WHERE sub2.`SoldierName` = '{$Soldier_Name}'
		");
	}
	// or else this is a global stats page
	else
	{
		$rank_q = @mysqli_query($BF4stats,"
			SELECT sub2.rank
			FROM
				(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
				 FROM
					(SELECT tpd.`SoldierName`
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					INNER JOIN (SELECT @num := 0) x
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
					GROUP BY tpd.`SoldierName`
					ORDER BY tpd.`SoldierName` ASC
					) sub
				) sub2
			WHERE sub2.`SoldierName` = '{$Soldier_Name}'
		");
	}
	if(@mysqli_num_rows($rank_q) == 1)
	{
		$rank_r = @mysqli_fetch_assoc($rank_q);
		$count = $rank_r['rank'];
	}
	else
	{
		$count = 0;
	}
}
elseif($rank == 'Score')
{
	// rank players by score
	// check if score rank is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$ScoreC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$Player_ID}
			AND `category` = 'Score'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$ServerID}'
			GROUP BY `PlayerID`
		");
	}
	// or else this is a global stats page
	else
	{
		$ScoreC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$Player_ID}
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
		// data old? or incorrect data? recalculate
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
					AND `PlayerID` = {$Player_ID}
				");
			}
			// or else this is a global stats page
			else
			{
				$Top_q = @mysqli_query($BF4stats,"
					SELECT `PlayerID`
					FROM `tyger_stats_top_twenty_cache`
					WHERE `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
					AND `timestamp` >= '{$old}'
					AND `PlayerID` = {$Player_ID}
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
						WHERE sub2.`PlayerID` = {$Player_ID}
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
						WHERE sub2.`PlayerID` = {$Player_ID}
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
						WHERE sub2.`PlayerID` = {$Player_ID}
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
						WHERE sub2.`PlayerID` = {$Player_ID}
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
					AND `PlayerID` = {$Player_ID}
				");
			}
			// or else this is a global stats page
			else
			{
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$srank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'Score'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$Player_ID}
				");
			}
		}
	}
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
				AND `PlayerID` = {$Player_ID}
			");
		}
		// or else this is a global stats page
		else
		{
			$Top_q = @mysqli_query($BF4stats,"
				SELECT `PlayerID`
				FROM `tyger_stats_top_twenty_cache`
				WHERE `SID` = '{$valid_ids}'
				AND `GID` = '{$GameID}'
				AND `timestamp` >= '{$old}'
				AND `PlayerID` = {$Player_ID}
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
					WHERE sub2.`PlayerID` = {$Player_ID}
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
					WHERE sub2.`PlayerID` = {$Player_ID}
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
					WHERE sub2.`PlayerID` = {$Player_ID}
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
					WHERE sub2.`PlayerID` = {$Player_ID}
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
				VALUES ('{$Player_ID}', '{$GameID}', '{$ServerID}', 'Score', '{$srank}', '{$now_timestamp}')
			");
		}
		// or else this is a global stats page
		else
		{
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$Player_ID}', '{$GameID}', '{$valid_ids}', 'Score', '{$srank}', '{$now_timestamp}')
			");
		}
	}
	$count = $srank;
}
elseif($rank == 'Kills')
{
	// rank players by kills
	// check if kills rank is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$KillsC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$Player_ID}
			AND `category` = 'Kills'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$ServerID}'
			GROUP BY `PlayerID`
		");
	}
	// or else this is a global stats page
	else
	{
		$KillsC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$Player_ID}
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
					WHERE sub2.`PlayerID` = {$Player_ID}
				");
			}
			// or else this is a global stats page
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
					WHERE sub2.`PlayerID` = {$Player_ID}
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
					AND `PlayerID` = {$Player_ID}
				");
			}
			// or else this is a global stats page
			else
			{
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$killsrank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'Kills'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$Player_ID}
				");
			}
		}
	}
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
				WHERE sub2.`PlayerID` = {$Player_ID}
			");
		}
		// or else this is a global stats page
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
				WHERE sub2.`PlayerID` = {$Player_ID}
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
				VALUES ('{$Player_ID}', '{$GameID}', '{$ServerID}', 'Kills', '{$killsrank}', '{$now_timestamp}')
			");
		}
		// or else this is a global stats page
		else
		{
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$Player_ID}', '{$GameID}', '{$valid_ids}', 'Kills', '{$killsrank}', '{$now_timestamp}')
			");
		}
	}
	$count = $killsrank;
}
elseif($rank == 'KDR')
{
	// rank players by KDR
	// check if KDR rank is already cached
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$KDRC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$Player_ID}
			AND `category` = 'KDR'
			AND `GID` = '{$GameID}'
			AND `SID` = '{$ServerID}'
			GROUP BY `PlayerID`
		");
	}
	// or else this is a global stats page
	else
	{
		$KDRC_q = @mysqli_query($BF4stats,"
			SELECT `rank`, `timestamp`
			FROM `tyger_stats_rank_cache`
			WHERE `PlayerID` = {$Player_ID}
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
					WHERE sub2.`PlayerID` = {$Player_ID}
				");
			}
			// or else this is a global stats page
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
					WHERE sub2.`PlayerID` = {$Player_ID}
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
					AND `PlayerID` = {$Player_ID}
				");
			}
			// or else this is a global stats page
			else
			{
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$kdrrank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'KDR'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$Player_ID}
				");
			}
		}
	}
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
				WHERE sub2.`PlayerID` = {$Player_ID}
			");
		}
		// or else this is a global stats page
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
				WHERE sub2.`PlayerID` = {$Player_ID}
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
				VALUES ('{$Player_ID}', '{$GameID}', '{$ServerID}', 'KDR', '{$kdrrank}', '{$now_timestamp}')
			");
		}
		// or else this is a global stats page
		else
		{
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$Player_ID}', '{$GameID}', '{$valid_ids}', 'KDR', '{$kdrrank}', '{$now_timestamp}')
			");
		}
	}
	$count = $kdrrank;
}
elseif($rank == 'HSR')
{
	if(!empty($ServerID))
	{
		$rank_q = @mysqli_query($BF4stats,"
			SELECT sub2.rank
			FROM
				(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
				 FROM
					(SELECT tpd.`SoldierName`
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					INNER JOIN (SELECT @num := 0) x
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` = {$ServerID}
					GROUP BY tpd.`SoldierName`
					ORDER BY (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) DESC, tpd.`SoldierName` ASC
					) sub
				) sub2
			WHERE sub2.`SoldierName` = '{$Soldier_Name}'
		");
	}
	// or else this is a global stats page
	else
	{
		$rank_q = @mysqli_query($BF4stats,"
			SELECT sub2.rank
			FROM
				(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
				 FROM
					(SELECT tpd.`SoldierName`
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					INNER JOIN (SELECT @num := 0) x
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
					GROUP BY tpd.`SoldierName`
					ORDER BY (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) DESC, tpd.`SoldierName` ASC
					) sub
				) sub2
			WHERE sub2.`SoldierName` = '{$Soldier_Name}'
		");
	}
	if(@mysqli_num_rows($rank_q) == 1)
	{
		$rank_r = @mysqli_fetch_assoc($rank_q);
		$count = $rank_r['rank'];
	}
	else
	{
		$count = 0;
	}
}
elseif($rank == 'Playtime')
{
	if(!empty($ServerID))
	{
		$rank_q = @mysqli_query($BF4stats,"
			SELECT sub2.rank
			FROM
				(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
				 FROM
					(SELECT tpd.`SoldierName`
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					INNER JOIN (SELECT @num := 0) x
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` = {$ServerID}
					GROUP BY tpd.`SoldierName`
					ORDER BY tps.`Playtime` DESC, tpd.`SoldierName` ASC
					) sub
				) sub2
			WHERE sub2.`SoldierName` = '{$Soldier_Name}'
		");
	}
	// or else this is a global stats page
	else
	{
		$rank_q = @mysqli_query($BF4stats,"
			SELECT sub2.rank
			FROM
				(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
				 FROM
					(SELECT tpd.`SoldierName`
					FROM `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					INNER JOIN (SELECT @num := 0) x
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
					GROUP BY tpd.`SoldierName`
					ORDER BY (SUM(tps.`Playtime`) DESC, tpd.`SoldierName` ASC
					) sub
				) sub2
			WHERE sub2.`SoldierName` = '{$Soldier_Name}'
		");
	}
	if(@mysqli_num_rows($rank_q) == 1)
	{
		$rank_r = @mysqli_fetch_assoc($rank_q);
		$count = $rank_r['rank'];
	}
	else
	{
		$count = 0;
	}
}
?>
