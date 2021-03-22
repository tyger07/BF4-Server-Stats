<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
require_once('../constants.php');
// check if necessary environment exists on this server
if(extension_loaded('gd') && function_exists('gd_info'))
{
	// we will need a server ID from the URL query string!
	// if no data query string is provided, this is an image
	if(!empty($pid))
	{
		// initialize defaults
		$PlayerID = 0;
		$fav = 0;
		$found = 0;
		// assign variable to input
		$PlayerID = $pid;
		if(!empty($_GET['fav']) AND is_numeric($_GET['fav']))
		{
			$fav = mysqli_real_escape_string($BF4stats, strip_tags($_GET['fav']));
		}
		// is adkats information available?
		if($adkats_available)
		{
			// query for this player's info
			$q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`GlobalRank`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, SUM(tps.`Headshots`) AS Headshots, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, adk.`ban_status`
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`PlayerID`
			");
		}
		else
		{
			// query for this player's info
			$q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`GlobalRank`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, SUM(tps.`Headshots`) AS Headshots, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`PlayerID`
			");
		}
		if(mysqli_num_rows($q) == 1)
		{
			$found = 1;
			$r = @mysqli_fetch_assoc($q);
			$rank = $r['GlobalRank'];
			$soldier = $r['SoldierName'];
			$score = $r['Score'];
			$kills = $r['Kills'];
			$deaths = $r['Deaths'];
			$kdr = round($r['KDR'],2);
			$headshots = $r['Headshots'];
			$hsr = round(($r['HSR']*100),2);
			// filter out the available ranks
			if($rank >= $rank_min && $rank <= $rank_max)
			{
				$rank_img = '../images/ranks/r' . $r['GlobalRank'] . '.png';
			}
			else
			{
				$rank_img = '../images/ranks/missing.png';
			}
			// is this player banned?
			// or have previous ban which was lifted?
			$player_banned = 0;
			$previous_banned = 0;
			if($adkats_available)
			{
				$ban_status = $r['ban_status'];
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
			// query for this player's weapon stats
			// this doesn't include vehicle weapon stats
			$wq = @mysqli_query($BF4stats,"
				SELECT tws.`Fullname`, tws.`Friendlyname`, tws.`Damagetype`, SUM(wa.`Kills`) AS Kills
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				AND (tws.`Damagetype` = 'assaultrifle' OR tws.`Damagetype` = 'lmg' OR tws.`Damagetype` = 'shotgun' OR tws.`Damagetype` = 'smg' OR tws.`Damagetype` = 'sniperrifle' OR tws.`Damagetype` = 'handgun' OR tws.`Damagetype` = 'projectileexplosive' OR tws.`Damagetype` = 'explosive' OR tws.`Damagetype` = 'melee' OR tws.`Damagetype` = 'carbine' OR tws.`Damagetype` = 'dmr' OR tws.`Damagetype` = 'impact' OR tws.`Damagetype` LIKE '%vehicle%' OR tws.`Damagetype` = 'none')
				GROUP BY tws.`Fullname`
				ORDER BY Kills DESC, Deaths DESC
				LIMIT 1
			");
			// are there weapon stats?
			if(mysqli_num_rows($wq) != 0)
			{
				$wr = @mysqli_fetch_assoc($wq);
				$damage_type = $wr['Damagetype'];
				// change how weapon is handled depending on damage type
				if($damage_type == 'none' OR strpos($damage_type,'vehicle') !== FALSE)
				{
					$weapon = $wr['Fullname'];
					// find weapon in weapon array
					if(in_array($weapon,$weapon_array))
					{
						$weapon_name = array_search($weapon,$weapon_array);
						$weapon_img = '../images/weapons/' . $weapon_name . '.png';
					}
					// this weapon is missing!
					else
					{
						$weapon = $wr['Friendlyname'];
						if(in_array($weapon,$weapon_array))
						{
							$weapon_name = array_search($weapon,$weapon_array);
							$weapon_img = '../images/weapons/' . $weapon_name . '.png';
						}
						// this weapon is still missing!
						else
						{
							$weapon_name = preg_replace("/_/"," ",$weapon);
							$weapon_img = '../images/weapons/missing.png';
						}
					}
					if($weapon_name == 'Not Specified')
					{
						$weapon_name = 'Machinery';
					}
				}
				else
				{
					$weapon = $wr['Friendlyname'];
					if(in_array($weapon,$weapon_array))
					{
						$weapon_name = array_search($weapon,$weapon_array);
						$weapon_img = '../images/weapons/' . $weapon . '.png';
					}
					// this weapon is still missing!
					else
					{
						$weapon_name = preg_replace("/_/"," ",$weapon);
						$weapon_img = '../images/weapons/missing.png';
					}
				}
			}
			// or else default
			else
			{
				$weapon_img = '../images/weapons/missing.png';
				$weapon_name = 'Unknown';
			}
			// rank players by score
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
			// initialize timestamp values
			$now_timestamp = time();
			// if cache refresh triggered, refresh cache regardless of last cache time
			if($cr == 1)
			{
				$old = $now_timestamp;
			}
			else
			{
				$old = $now_timestamp - 10800;
			}
			// check if this player has a recent enough cached rank
			// if so, we can get their score rank much faster from the cache
			$ScoreRecentCache_q = @mysqli_query($BF4stats,"
				SELECT `rank`
				FROM `tyger_stats_rank_cache`
				WHERE `PlayerID` = {$PlayerID}
				AND `category` = 'Score'
				AND `GID` = '{$GameID}'
				AND `SID` = '{$valid_ids}'
				AND `timestamp` >= '{$old}'
				GROUP BY `PlayerID`
			");
			// if no recent results, do the slower query from live stats
			if(!$ScoreRecentCache_q || @mysqli_num_rows($ScoreRecentCache_q) == 0)
			{
				$ScoreRecache_q = @mysqli_query($BF4stats,"
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
			if(@mysqli_num_rows($ScoreRecentCache_q) == 1)
			{
				$Score_r = @mysqli_fetch_assoc($ScoreRecentCache_q);
				$srank = $Score_r['rank'];
			}
			elseif(@mysqli_num_rows($ScoreRecache_q) == 1)
			{
				$Score_r = @mysqli_fetch_assoc($ScoreRecache_q);
				$srank = $Score_r['rank'];
				// check if this player exists in the rank cache at all
				$ScoreExistCache_q = @mysqli_query($BF4stats,"
					SELECT `rank`
					FROM `tyger_stats_rank_cache`
					WHERE `PlayerID` = {$PlayerID}
					AND `category` = 'Score'
					AND `GID` = '{$GameID}'
					AND `SID` = '{$valid_ids}'
					GROUP BY `PlayerID`
				");
				if(!$ScoreExistCache_q || @mysqli_num_rows($ScoreExistCache_q) == 0)
				{
					// insert new data
					@mysqli_query($BF4stats,"
						INSERT INTO `tyger_stats_rank_cache`
						(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
						VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'Score', '{$srank}', '{$now_timestamp}')
					");
				}
				else
				{
					// update old data
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
			else
			{
				$srank = 'error';
			}
			// count total combined players
			// check to see if this count cache table exists
			@mysqli_query($BF4stats,"
				CREATE TABLE IF NOT EXISTS `tyger_stats_count_cache`
				(
					`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
					`category` VARCHAR(20) NOT NULL,
					`GID` TINYINT(4) UNSIGNED NOT NULL,
					`SID` VARCHAR(100) NOT NULL,
					`value` INT(10) UNSIGNED NOT NULL DEFAULT '0',
					`timestamp` INT(11) NOT NULL DEFAULT '0',
					PRIMARY KEY (`ID`),
					UNIQUE `UNIQUE_CountData` (`category`, `GID`, `SID`),
					INDEX `category` (`category` ASC),
					INDEX `GID` (`GID` ASC),
					INDEX `SID` (`SID` ASC),
					INDEX `timestamp` (`timestamp` ASC),
					CONSTRAINT `fk_tyger_stats_count_cache_GID` FOREIGN KEY (`GID`) REFERENCES `tbl_games`(`GameID`) ON DELETE CASCADE ON UPDATE CASCADE
				)
				ENGINE=InnoDB
			");
			// check to see if player count is already cached
			$TotalRowsC_q = @mysqli_query($BF4stats,"
				SELECT DISTINCT(`value`) AS count
				FROM `tyger_stats_count_cache`
				WHERE `category` = 'total_players'
				AND `SID` = '{$valid_ids}'
				AND `GID` = '{$GameID}'
				AND `timestamp` >= '{$old}'
			");
			// if no recent results, do the slower query from live stats
			if(!$TotalRowsC_q || @mysqli_num_rows($TotalRowsC_q) == 0)
			{
				$TotalRowsC_q = @mysqli_query($BF4stats,"
					SELECT COUNT(DISTINCT tpd.`PlayerID`) AS count
					FROM  `tbl_playerdata` tpd
					INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
					INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
					WHERE tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
				");
			}
			if(@mysqli_num_rows($TotalRowsC_q) == 1)
			{
				$TotalRows_r = @mysqli_fetch_assoc($TotalRowsC_q);
				$total_players = $TotalRows_r['count'];
			}
			else
			{
				$total_players = 'error';
			}
		}
		else
		{
			$rank_img = '../images/ranks/missing.png';
			$weapon_img = '../images/weapons/missing.png';
		}
		// start outputting the image
		header('Pragma: public');
		header('Cache-Control: max-age=10800');
		header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', $now_timestamp + 10800));
		header("Content-type: image/png");
		// base image
		$base = imagecreatefrompng('./images/background.png');
		// text color
		$light = imagecolorallocate($base, 255, 255, 255);
		$yellow = imagecolorallocate($base, 255, 250, 200);
		$red = imagecolorallocate($base, 255, 000, 000);
		$orange = imagecolorallocate($base, 255, 100, 000);
		$dark = imagecolorallocate($base, 200, 200, 190);
		// add clan name text
		imagestring($base, 2, 220, 17, $clan_name . '\'s Servers', $dark);
		// add rank or weapon image
		// default is rank
		if($fav == 0)
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
			$weapon = imagecreatefrompng($weapon_img);
			// copy the weapon image onto the background image
			imagecopy($base, $weapon, 0, 20, 0, 0, 94, 56);
			$white = imagecolorallocate($weapon, 255, 255, 255);
			imagecolortransparent($base, $white);
			imagealphablending($base, false);
			imagesavealpha($base, true);
		}
		// if this soldier was found...
		if($found == 1)
		{
			// add text to image
			imagestring($base, 2, 110, 17, $soldier, $light);
			imagestring($base, 2, 115, 35, 'Score:', $yellow);
			imagestring($base, 2, 160, 35, $score, $yellow);
			imagestring($base, 2, 115, 46, 'Kills:', $yellow);
			imagestring($base, 2, 160, 46, $kills, $yellow);
			imagestring($base, 2, 115, 57, 'Deaths:', $yellow);
			imagestring($base, 2, 160, 57, $deaths, $yellow);
			imagestring($base, 2, 115, 68, 'KDR:', $yellow);
			imagestring($base, 2, 160, 68, $kdr, $yellow);
			imagestring($base, 2, 225, 35, 'Rank #:', $yellow);
			imagestring($base, 2, 288, 35, $srank . ' of ' . $total_players, $yellow);
			imagestring($base, 2, 225, 46, 'Favorite:', $yellow);
			imagestring($base, 2, 288, 46, $weapon_name, $yellow);
			imagestring($base, 2, 225, 57, 'Headshots:', $yellow);
			imagestring($base, 2, 288, 57, $headshots, $yellow);
			imagestring($base, 2, 225, 68, 'HS %:', $yellow);
			imagestring($base, 2, 288, 68, $hsr . ' %', $yellow);
			if($player_banned == 1)
			{
				imagestring($base, 2, 110, 8, 'Banned', $red);
			}
			elseif($previous_banned == 1)
			{
				imagestring($base, 2, 110, 8, 'Warned', $orange);
			}
		}
		// this soldier was not found
		else
		{
			// add text to image
			imagestring($base, 4, 150, 40, 'This player has no stats.', $light);
		}
		// compile image
		imagepng($base);
		imagedestroy($base);
	}
	else
	{
		// start outputting the image
		header("Content-type: image/png");
		// base image
		$base = imagecreatefrompng('./images/background.png');
		imagealphablending($base, false);
		imagesavealpha($base, true);
		// text color
		$light = imagecolorallocate($base, 255, 255, 255);
		// add text to image
		imagestring($base, 4, 120, 40, 'Player ID required.', $light);
		// compile image
		imagepng($base);
		imagedestroy($base);
	}
// php GD extension doesn't exist. show error image
}
else
{
	// start outputting the image
	header("Content-type: image/png");
	echo file_get_contents('./images/error.png');
}
?>
