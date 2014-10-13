<?php
// player-search asynchronous for server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// first connect to the database
// and include necessary files
include_once('../config/config.php');
include_once('../common/connect.php');
require_once('../common/case.php');

// default variables to null
$ServerID = null;
$GameID = null;
$SoldierName = null;

// get values
if(!empty($sid))
{
	$ServerID = $sid;
}
if(!empty($gid))
{
	$GameID = $gid;
}
if(!empty($term))
{
	$SoldierName = $term;
}

// initialize empty array for later storing names into
$Soldiers = array();

// if necessary info has been determined
if(!empty($SoldierName) && !empty($GameID))
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// check to see if there are any players who match a similar name
		$PlayerMatch_q = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`SoldierName` LIKE '%{$SoldierName}%'
			AND tpd.`GameID` = {$GameID}
			ORDER BY tps.`Score` DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
	// or else this is a global stats page
	else
	{
		// check to see if there are any players who match a similar name
		$PlayerMatch_q = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, SUM(tps.`Score`) AS Score
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tpd.`SoldierName` LIKE '%{$SoldierName}%'
			AND tpd.`GameID` = {$GameID}
			GROUP BY tpd.`SoldierName`
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
	// at least one soldier was found
	if(@mysqli_num_rows($PlayerMatch_q) != 0)
	{
		// add found soldiers to array
		while($Soldiers_r = @mysqli_fetch_assoc($PlayerMatch_q))
		{
			$Soldiers[] = $Soldiers_r['SoldierName'];
		}
	}
	// free up soldier query memory
	@mysqli_free_result($PlayerMatch_q);
}

echo json_encode($Soldiers);
?>
