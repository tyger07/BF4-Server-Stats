<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// initialize values as null
$ServerID = null;
$ServerName = null;
$SoldierName = null;
$PlayerID = null;
// if there is only one server, no need for index page
// assign the only server to the $ServerID variable
// and get this server's basic information
// otherwise, we need to find this $ServerID manually
// $sid in URL is given by case.php
// was a server ID given in the URL?  Is it a valid server ID?
// if so, we must NOT be looking at combined stats
if((count($ServerIDs) == 1) || (!empty($sid) && in_array($sid,$ServerIDs) && empty($ServerID)))
{
	// assign the only server to the $ServerID variable (inherited)
	if(count($ServerIDs) == 1)
	{
		$ServerID = $ServerIDs[0];
	}
	// assign $ServerID variable from URL
	elseif(!empty($sid) && in_array($sid,$ServerIDs) && empty($ServerID))
	{
		$ServerID = $sid;
	}
	// lets check our stats page sessions
	// stats page sessions are used to monitor how many people are viewing these stats pages
	// set default client IP address value
	$userip = 'unknown';
	// update client's IP address
	if(isset($_SERVER["REMOTE_ADDR"]))
	{
		$userip = $_SERVER["REMOTE_ADDR"];
	}
	$ses = session_count($userip,$ServerID,$valid_ids,$GameID,$BF4stats,$page,$pid,$player,$isbot);
	// find this server info
	$Server_q = @mysqli_query($BF4stats,"
		SELECT `ServerName`
		FROM `tbl_server`
		WHERE `ServerID` = {$ServerID}
		AND `GameID` = {$GameID}
	");
	// the server info was found
	if(@mysqli_num_rows($Server_q) == 1)
	{
		$Server_r = @mysqli_fetch_assoc($Server_q);
		$ServerName = $Server_r['ServerName'];
		// create battlelog link for this server
		$battlelog = 'https://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . urlencode($ServerName);
		$ServerName = textcleaner($ServerName);
	}
	// error?  what?  This will probably never happen.
	// damage control...
	else
	{
		$ServerName = 'Error';
		$battlelog = 'https://battlelog.battlefield.com/bf4/servers/pc/';
	}
	// lets see if a SoldierName or PlayerID was provided to us in the URL
	// first look for a SoldierName in URL and try to convert it to PlayerID
	if(!empty($player))
	{
		$SoldierName = $player;
		$PlayerID_q = @mysqli_query($BF4stats,"
			SELECT tpd.`PlayerID`
			FROM `tbl_playerdata` tpd
			INNER JOIN `tbl_server_player` tsp ON tpd.`PlayerID` = tsp.`PlayerID`
			INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
			WHERE tpd.`SoldierName` = '{$SoldierName}'
			AND tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` = {$ServerID}
		");
		// was there a result?
		if(@mysqli_num_rows($PlayerID_q) == 1)
		{
			$PlayerID_r = @mysqli_fetch_assoc($PlayerID_q);
			$PlayerID = $PlayerID_r['PlayerID'];
		}
		// otherwise null variable
		else
		{
			$PlayerID = null;
		}
	}
	// then look for PlayerID in URL and make sure it wasn't already successfully matched above
	if(!empty($pid) && empty($PlayerID))
	{
		$PlayerID = $pid;
		$SoldierName_q = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`
			FROM `tbl_playerdata` tpd
			INNER JOIN `tbl_server_player` tsp ON tpd.`PlayerID` = tsp.`PlayerID`
			INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` = {$ServerID}
		");
		// was there a result?
		if(@mysqli_num_rows($SoldierName_q) == 1)
		{
			$SoldierName_r = @mysqli_fetch_assoc($SoldierName_q);
			$SoldierName = textcleaner($SoldierName_r['SoldierName']);
		}
		// otherwise null variables
		else
		{
			$SoldierName = null;
			$PlayerID = null;
		}
	}
}
// no server id in URL
// and there is more than one valid server id available
// this must be a combined stats page
else
{
	// lets check our stats page sessions
	// stats page sessions are used to monitor how many people are viewing these stats pages
	// set default client IP address value
	$userip = 'unknown';
	// update client's IP address
	if(isset($_SERVER["REMOTE_ADDR"]))
	{
		$userip = $_SERVER["REMOTE_ADDR"];
	}
	$ses = session_count($userip,$ServerID,$valid_ids,$GameID,$BF4stats,$page,$pid,$player,$isbot);
	// lets see if a SoldierName or PlayerID was provided to us in the URL
	// first look for a SoldierName in URL and try to convert it to PlayerID
	if(!empty($player))
	{
		$SoldierName = $player;
		$PlayerID_q = @mysqli_query($BF4stats,"
			SELECT DISTINCT(tpd.`PlayerID`)
			FROM `tbl_playerdata` tpd
			INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
			WHERE tpd.`SoldierName` = '{$SoldierName}'
			AND tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` IN ({$valid_ids})
		");
		// was there a result?
		if(@mysqli_num_rows($PlayerID_q) == 1)
		{
			$PlayerID_r = @mysqli_fetch_assoc($PlayerID_q);
			$PlayerID = $PlayerID_r['PlayerID'];
		}
		// otherwise null variable
		else
		{
			$PlayerID = null;
		}
	}
	// then look for PlayerID in URL and make sure it wasn't already successfully matched above
	if(!empty($pid) && empty($PlayerID))
	{
		$PlayerID = $pid;
		$SoldierName_q = @mysqli_query($BF4stats,"
			SELECT DISTINCT(tpd.`SoldierName`)
			FROM `tbl_playerdata` tpd
			INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` IN ({$valid_ids})
		");
		// was there a result?
		if(@mysqli_num_rows($SoldierName_q) == 1)
		{
			$SoldierName_r = @mysqli_fetch_assoc($SoldierName_q);
			$SoldierName = textcleaner($SoldierName_r['SoldierName']);
		}
		// otherwise null variables
		else
		{
			$SoldierName = null;
			$PlayerID = null;
		}
	}
}
?>
