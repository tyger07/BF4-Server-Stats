<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
// default variables to null
$ServerID = null;
$SoldierName = null;
// get values
if(!empty($sid))
{
	$ServerID = $sid;
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
			AND tsp.`ServerID` IN ({$valid_ids})
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
}
echo json_encode($Soldiers);
?>