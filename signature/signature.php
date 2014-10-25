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
			$weapon = $wr['Friendlyname'];
			// rename 'Death'
			if($weapon == 'Death')
			{
				$weapon = 'Machinery';
			}
			// convert weapon to friendly name
			if(in_array($weapon,$weapon_array))
			{
				$weapon = array_search($weapon,$weapon_array);
				$weapon_img = '../images/weapons/' . $wr['Friendlyname'] . '.png';
			}
			// this weapon is missing!
			else
			{
				$weapon = preg_replace("/_/"," ",$weapon);
				$weapon_img = '../images/weapons/missing.png';
			}
			$weapon_kills = $wr['weaponkills'];
		}
		// or else default
		else
		{
			$rank_img = '../images/ranks/missing.png';
			$weapon_img = '../images/weapons/missing.png';
			$weapon = 'Unknown';
			$weapon_kills = 'Unknown';
		}
		// rank players by score
		// initialize score rank values
		$srank = 0;
		$smatch = 0;
		$srank_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`PlayerID`, SUM(tps.`Score`) AS Score
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`GameID` = {$GameID}
				GROUP BY tpd.`PlayerID`
				ORDER BY Score DESC, tpd.`SoldierName` ASC
		");
		// loop through the list until this player's ID is found
		if(@mysqli_num_rows($srank_q) != 0)
		{
			while($srank_r = @mysqli_fetch_assoc($srank_q))
			{
				$srank++;
				$ThisID = strtolower($srank_r['PlayerID']);
				// if player name in rank row matches player of interest
				if($PlayerID == $ThisID)
				{
						$smatch = 1;
						break;
				}
			}
		}
		if($smatch == 0)
		{
			$srank = 'error';
		}
		
		// find out how many rows are in the table
		$TotalRows_q = @mysqli_query($BF4stats,"
			SELECT SUM( tpd.`PlayerID` ) AS IDs
			FROM `tbl_playerdata` tpd
			INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
			WHERE tpd.`GameID` = {$GameID}
			GROUP BY tpd.`PlayerID`
		");
		if(@mysqli_num_rows($TotalRows_q) != 0)
		{
			$total_players = @mysqli_num_rows($TotalRows_q);
		}
		else
		{
			$total_players = 'error';
		}
		
		// free up total rows query memory
		@mysqli_free_result($TotalRows_q);
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
	
	// free up score rank query memory
	@mysqli_free_result($srank_q);
	
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
		imagestring($base, 2, 295, 50, $weapon, $yellow);
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
?>
