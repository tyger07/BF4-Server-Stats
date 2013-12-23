<?php

// first connect to the database
// include config.php contents
include_once('../config/config.php');
$BF4stats = mysqli_connect(HOST, USER, PASS, NAME, PORT);

// then include constants.php
include_once('../common/constants.php');

// we will need a server ID from the URL query string!
// if no data query string is provided, this is an image
if(isset($_GET['PlayerID']) AND !is_null($_GET['PlayerID']) AND is_numeric($_GET['PlayerID']))
{
	// initialize defaults
	$PlayerID = 0;
	$fav = 0;
	$found = 0;
	
	// assign variable to input
	$PlayerID = mysqli_real_escape_string($BF4stats, $_GET['PlayerID']);
	if(isset($_GET['fav']) AND !is_null($_GET['fav']) AND is_numeric($_GET['fav']))
	{
		$fav = 1;
	}
	
	// query for this player's info
	$q = @mysqli_query($BF4stats,"
		SELECT tw.Friendlyname, SUM(tws.Kills) AS weaponKills, tpd.SoldierName, tpd.GlobalRank, SUM(tps.Score) AS Score, SUM(tps.Kills) AS Kills, SUM(tps.Deaths) AS Deaths, (SUM(tps.Kills)/SUM(tps.Deaths)) AS KDR, SUM(tps.Rounds) AS Rounds, SUM(tps.Headshots) AS Headshots, (SUM(tps.Headshots)/SUM(tps.Kills)) AS HSR
		FROM tbl_playerstats tps
		INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID
		INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID
		INNER JOIN tbl_weapons_stats tws ON tws.StatsID = tps.StatsID
		INNER JOIN tbl_weapons tw ON tw.WeaponID = tws.WeaponID
		WHERE tpd.PlayerID = {$PlayerID}
		GROUP BY Friendlyname
		ORDER BY weaponKills DESC
		LIMIT 1
	");
	if(mysqli_num_rows($q) == 1)
	{
		$found = 1;
		$r = @mysqli_fetch_assoc($q);
		$rank = $r['GlobalRank'];
		$rank_img = '../images/ranks/r' . $r['GlobalRank'] . '.png';
		$weapon = preg_replace("/_/"," ",$r['Friendlyname']);
		// rename 'death'
		if($weapon == 'Death')
		{
			$weapon = 'Machinery';
		}
		$weapon_img = '../images/weapons/' . $r['Friendlyname'] . '.png';
		$weapon_kills = $r['weaponkills'];
		$soldier = $r['SoldierName'];
		$score = $r['Score'];
		$kills = $r['Kills'];
		$deaths = $r['Deaths'];
		$kdr = round($r['KDR'],2);
		$rounds = $r['Rounds'];
		$headshots = $r['Headshots'];
		$hsr = round(($r['HSR']*100),2);
	}
	else
	{
		$rank_img = '../images/ranks/r0.png';
		$weapon_img = '../images/ranks/r0.png';
	}
	
	// start outputting the image
	header("Content-type: image/png");
	
	// base image
	$base = imagecreatefrompng("./images/background.png");
	
	// text color
	$light = imagecolorallocate($base, 255, 255, 200);
	$dark = imagecolorallocate($base, 220, 220, 200);
	
	// add clan name text
	imagestring($base, 2, 210, 17, "$clan_name's Servers", $dark);
	
	// default is rank
	if($fav == 0)
	{
		// rank image
		$rank = imagecreatefrompng("$rank_img");
		
		// copy the rank image onto the background image
		imagecopy($base, $rank, 0, 0, 0, 0, 94, 94);
		$white = imagecolorallocate($rank, 255, 255, 255);
		imagecolortransparent($base, $white);
		imagealphablending($base, false);
		imagesavealpha($base, true);
	}
	// otherwise use weapon
	else
	{
		// weapon image
		$rank = imagecreatefrompng("$weapon_img");
		
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
		imagestring($base, 4, 110, 15, "$soldier", $light);
		imagestring($base, 1, 130, 40, "Score:", $dark);
		imagestring($base, 1, 170, 40, "$score", $dark);
		imagestring($base, 1, 130, 50, "Kills:", $dark);
		imagestring($base, 1, 170, 50, "$kills", $dark);
		imagestring($base, 1, 130, 60, "Deaths:", $dark);
		imagestring($base, 1, 170, 60, "$deaths", $dark);
		imagestring($base, 1, 130, 70, "KDR:", $dark);
		imagestring($base, 1, 170, 70, "$kdr", $dark);
		imagestring($base, 1, 230, 40, "Favorite:", $dark);
		imagestring($base, 1, 290, 40, "$weapon", $dark);
		imagestring($base, 1, 230, 50, "Rounds:", $dark);
		imagestring($base, 1, 290, 50, "$rounds", $dark);
		imagestring($base, 1, 230, 60, "Headshots:", $dark);
		imagestring($base, 1, 290, 60, "$headshots", $dark);
		imagestring($base, 1, 230, 70, "HSR:", $dark);
		imagestring($base, 1, 290, 70, "$hsr", $dark);
	}
	// this soldier was not found
	else
	{
		// add text to image
		imagestring($base, 4, 130, 40, "This player has no stats.", $light);
	}
	
	// compile image
	imagepng($base);
	imagedestroy($base);
}
// no PlayerID provided
else
{
	// set rank to 0 for missing player
	$rank = '../images/ranks/r0.png';
	
	// start outputting the image
	header("Content-type: image/png");
	
	// base image
	$base = imagecreatefrompng("./images/background.png");
	
	// text color
	$light = imagecolorallocate($base, 255, 255, 200);
	$dark = imagecolorallocate($base, 220, 220, 200);
	
	// add clan name text
	imagestring($base, 2, 210, 17, "$clan_name's Servers", $dark);
	
	// rank image
	$rank = imagecreatefrompng("$rank");
	
	// Set the margins for the stamp and get the height/width of the stamp image
	$marge_left = 0;
	$marge_bottom = 0;
	$sx = imagesx($rank);
	$sy = imagesy($rank);

	// copy the rank image onto the background image
	imagecopy($base, $rank, $marge_left, imagesy($base) - $sy - $marge_bottom, 0, 0, imagesx($rank), imagesy($rank));
	$white = imagecolorallocate($rank, 255, 255, 255);
	imagecolortransparent($base, $white);
	imagealphablending($base, false);
	imagesavealpha($base, true);
	
	// add text
	imagestring($base, 4, 130, 40, "No Player ID was provided!", $light);
	
	// compile image
	imagepng($base);
	imagedestroy($base);
}
?>