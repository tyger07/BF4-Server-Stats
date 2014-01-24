<?php

// first connect to the database
// include config.php contents
include_once('../config/config.php');
$BF4stats = mysqli_connect(HOST, USER, PASS, NAME, PORT);

// then include constants.php
include_once('../common/constants.php');

// we will need a server ID from the URL query string!
// if no data query string is provided, this is an image
if(!empty($_GET['PlayerID']) AND is_numeric($_GET['PlayerID']) AND !empty($_GET['GameID']) AND is_numeric($_GET['GameID']))
{
	// initialize defaults
	$PlayerID = 0;
	$fav = 0;
	$found = 0;
	
	// assign variable to input
	$PlayerID = mysqli_real_escape_string($BF4stats, $_GET['PlayerID']);
	$GameID = mysqli_real_escape_string($BF4stats, $_GET['GameID']);
	if(!empty($_GET['FAV']) AND is_numeric($_GET['FAV']))
	{
		$FAV = mysqli_real_escape_string($BF4stats, $_GET['FAV']);
	}
	
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
		$rank_img = '../images/ranks/r' . $r['GlobalRank'] . '.png';
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
			$weapon_img = '../images/weapons/' . $wr['Friendlyname'] . '.png';
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
	imagepng($base);
	imagedestroy($base);
}
?>