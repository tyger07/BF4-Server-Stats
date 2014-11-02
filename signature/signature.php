<?php

// first connect to the database
// and include necessary files
require_once('../config/config.php');
require_once('../common/connect.php');
require_once('../common/case.php');
require_once('../common/constants.php');

// we will need a server ID from the URL query string!
// if no data query string is provided, this is an image
if(!empty($pid) && !empty($gid))
{
	// initialize defaults
	$PlayerID = 0;
	$fav = 0;
	$found = 0;
	
	// assign variable to input
	$PlayerID = $pid;
	$GameID = $gid;
	if(!empty($_GET['fav']) AND is_numeric($_GET['fav']))
	{
		$fav = mysqli_real_escape_string($BF4stats, $_GET['fav']);
	}
	
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
		// query for this player's weapon stats
		$wq = @mysqli_query($BF4stats,"
			SELECT tw.`Friendlyname`, SUM(tws.`Kills`) AS weaponKills
			FROM `tbl_playerdata` tpd
			INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
			INNER JOIN `tbl_weapons_stats` tws ON tws.`StatsID` = tsp.`StatsID`
			INNER JOIN `tbl_weapons` tw ON tw.`WeaponID` = tws.`WeaponID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND (tw.`Damagetype` = 'assaultrifle' OR tw.`Damagetype` = 'lmg' OR tw.`Damagetype` = 'shotgun' OR tw.`Damagetype` = 'smg' OR tw.`Damagetype` = 'sniperrifle' OR tw.`Damagetype` = 'handgun' OR tw.`Damagetype` = 'projectileexplosive' OR tw.`Damagetype` = 'explosive' OR tw.`Damagetype` = 'melee' OR tw.`Damagetype` = 'none' OR tw.`Damagetype` = 'carbine' OR tw.`Damagetype` = 'dmr' OR tw.`Damagetype` = 'impact')
			AND tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` IN ({$valid_ids})
			GROUP BY tw.`Friendlyname`
			ORDER BY weaponKills DESC
			LIMIT 1
		");
		// are there weapon stats?
		if(mysqli_num_rows($wq) != 0)
		{
			$wr = @mysqli_fetch_assoc($wq);
			$weapon = $wr['Friendlyname'];
			// rename 'Death'
			if($weapon == 'Death')
			{
				$weapon = 'Machinery';
			}
			// convert weapon to friendly name
			if(in_array($weapon,$weapon_array))
			{
				$weapon_name = array_search($weapon,$weapon_array);
				$weapon_img = '../images/weapons/' . $weapon . '.png';
			}
			// this weapon is missing!
			else
			{
				$weapon_name = preg_replace("/_/"," ",$weapon);
				$weapon_img = '../images/weapons/missing.png';
			}
			$weapon_kills = $wr['weaponkills'];
		}
		// or else default
		else
		{
			$rank_img = '../images/ranks/missing.png';
			$weapon_img = '../images/weapons/missing.png';
			$weapon_name = 'Unknown';
			$weapon_kills = 'Unknown';
		}
		
		// rank players by score
		// check to see if this rank cache table exists
		@mysqli_query($BF4stats,"
			CREATE TABLE IF NOT EXISTS `tyger_stats_rank_cache`
			(`PlayerID` INT(10) UNSIGNED NOT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `rank` INT(10) UNSIGNED NOT NULL DEFAULT '0', `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`PlayerID`))
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
			
			// data older than 12 hours? or incorrect data? recalculate
			if(($timestamp <= $old) OR ($srank == 0))
			{
				// rank players by score
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
				if(@mysqli_num_rows($Score_q) == 1)
				{
					$Score_r = @mysqli_fetch_assoc($Score_q);
					$srank = $Score_r['rank'];
				}
				else
				{
					$srank = 0;
				}
				
				// update old data in database
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_rank_cache`
					SET `rank` = '{$srank}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'Score'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
					AND `PlayerID` = {$PlayerID}
				");
				// free up rank query memory
				@mysqli_free_result($Score_q);
			}
		}
		else
		{
			// rank players by score
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
			if(@mysqli_num_rows($Score_q) == 1)
			{
				$Score_r = @mysqli_fetch_assoc($Score_q);
				$srank = $Score_r['rank'];
			}
			else
			{
				$srank = 0;
			}
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_rank_cache`
				(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
				VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'Score', '{$srank}', '{$now_timestamp}')
			");
			// free up rank query memory
			@mysqli_free_result($Score_q);
		}
		// free up score rank cache query memory
		@mysqli_free_result($ScoreC_q);
		
		// count total combined players
		// check to see if this count cache table exists
		@mysqli_query($BF4stats,"
			CREATE TABLE IF NOT EXISTS `tyger_stats_count_cache`
			(`category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `value` INT(10) UNSIGNED NOT NULL DEFAULT '0', `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`category`))
			ENGINE=MyISAM
			DEFAULT CHARSET=utf8
			COLLATE=utf8_bin
		");

		// initialize timestamp values
		$now_timestamp = time();
		$old = $now_timestamp - 43200;

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
			$total_players = $TotalRowsC_r['value'];
			$timestamp = $TotalRowsC_r['timestamp'];
			
			// data older than 12 hours? or incorrect data? recalculate
			if(($timestamp <= $old) OR ($total_players == 0))
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
				if(@mysqli_num_rows($TotalRows_q) == 1)
				{
					$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
					$total_players = $TotalRows_r['count'];
				}
				else
				{
					$total_players = 0;
				}
				// update old data in database
				@mysqli_query($BF4stats,"
					UPDATE `tyger_stats_count_cache`
					SET `value` = '{$total_players}', `timestamp` = '{$now_timestamp}'
					WHERE `category` = 'total_players'
					AND `SID` = '{$valid_ids}'
					AND `GID` = '{$GameID}'
				");
				
				// free up count query memory
				@mysqli_free_result($TotalRows_q);
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
			if(@mysqli_num_rows($TotalRows_q) == 1)
			{
				$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
				$total_players = $TotalRows_r['count'];
			}
			else
			{
				$total_players = 0;
			}
			// add this data to the cache
			@mysqli_query($BF4stats,"
				INSERT INTO `tyger_stats_count_cache`
				(`category`, `GID`, `SID`, `value`, `timestamp`)
				VALUES ('total_players', '{$GameID}', '{$valid_ids}', '{$total_players}', '{$now_timestamp}')
			");
			
			// free up count query memory
			@mysqli_free_result($TotalRows_q);
		}
		// free up count cache query memory
		@mysqli_free_result($TotalRowsC_q);
	}
	else
	{
		$rank_img = '../images/ranks/missing.png';
		$weapon_img = '../images/weapons/missing.png';
	}
	
	// free up player info query memory
	@mysqli_free_result($q);
	
	// free up weapon query memory
	@mysqli_free_result($wq);
	
	// free up total player query memory
	@mysqli_free_result($PlayerCount_q);
	
	// start outputting the image
	header("Content-type: image/png");
	
	// base image
	$base = imagecreatefrompng('./images/background.png');
	
	// text color
	$light = imagecolorallocate($base, 255, 255, 255);
	$yellow = imagecolorallocate($base, 252, 199, 66);
	$dark = imagecolorallocate($base, 200, 200, 190);
	
	// add clan name text
	imagestring($base, 2, 220, 17, $clan_name . '\'s Servers', $dark);
	
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
		imagestring($base, 2, 120, 40, 'Score:', $yellow);
		imagestring($base, 2, 165, 40, $score, $yellow);
		imagestring($base, 2, 120, 50, 'Kills:', $yellow);
		imagestring($base, 2, 165, 50, $kills, $yellow);
		imagestring($base, 2, 120, 60, 'Deaths:', $yellow);
		imagestring($base, 2, 165, 60, $deaths, $yellow);
		imagestring($base, 2, 120, 70, 'KDR:', $yellow);
		imagestring($base, 2, 165, 70, $kdr, $yellow);
		imagestring($base, 2, 230, 40, 'Rank #:', $yellow);
		imagestring($base, 2, 295, 40, $srank . ' of ' . $total_players, $yellow);
		imagestring($base, 2, 230, 50, 'Favorite:', $yellow);
		imagestring($base, 2, 295, 50, $weapon_name, $yellow);
		imagestring($base, 2, 230, 60, 'Headshots:', $yellow);
		imagestring($base, 2, 295, 60, $headshots, $yellow);
		imagestring($base, 2, 230, 70, 'HS %:', $yellow);
		imagestring($base, 2, 295, 70, $hsr . ' %', $yellow);
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
	imagestring($base, 4, 120, 40, 'Player ID and Game ID required.', $light);
	
	// compile image
	imagepng($base);
	imagedestroy($base);
}
?>
