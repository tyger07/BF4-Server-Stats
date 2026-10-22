<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
// default variables to null
$ServerID = null;
$Chat_Search = null;
// get values
if(!empty($sid))
{
	$ServerID = $sid;
}
if(!empty($term))
{
	$Chat_Search = $term;
}
// initialize empty array for later storing names into
$Results = array();
// if necessary info has been determined
if(!empty($Chat_Search) && !empty($GameID))
{
	// lets see if the query input could possibly be a date/timestamp
	if(!empty($Chat_Search) && !(($timestamp = strtotime($Chat_Search)) === false))
	{
		// convert to compatible timestamp
		$date_search = date("Y-m-d H:i",strtotime($Chat_Search));
		// find timestamp for now and find the timestamp of the query
		$now = time();
		$compare = strtotime($date_search);
		// find the difference between the two
		$difference = ($now - $compare);
		// difference is less than an hour ago. show the last hour
		if($difference < 3600)
		{
			$low = date("Y-m-d H:i",($now - 3600));
			$high = date("Y-m-d H:i",$now);
		}
		// query contains 'week' so show a week worth of data
		elseif(stristr($Chat_Search, 'week') !== FALSE)
		{
			$low = date("Y-m-d",strtotime($Chat_Search)) . ' 00:01';
			$high = date("Y-m-d",(strtotime($Chat_Search) + 518400)) . ' 23:59';
		}
		// query contains 'month' so show a month worth of data
		elseif(stristr($Chat_Search, 'month') !== FALSE)
		{
			$low = date("Y-m-d",strtotime($Chat_Search)) . ' 00:01';
			$high = date("Y-m-d",(strtotime($Chat_Search) + 2592000)) . ' 23:59';
		}
		// query contains 'year' so show a year worth of data
		elseif(stristr($Chat_Search, 'year') !== FALSE)
		{
			$low = date("Y-m-d",strtotime($Chat_Search)) . ' 00:01';
			$high = date("Y-m-d",(strtotime($Chat_Search) + 31536000)) . ' 23:59';
		}
		// query contains a specific time of day so filter within 10 minutes of entered time
		elseif(stristr($Chat_Search, ':') !== FALSE)
		{
			$low = date("Y-m-d H:i",(strtotime($Chat_Search)) - 300);
			$high = date("Y-m-d H:i",(strtotime($Chat_Search) + 300));
		}
		// filter within a 1 day by default
		else
		{
			$low = date("Y-m-d",strtotime($Chat_Search)) . ' 00:01';
			$high = date("Y-m-d",strtotime($Chat_Search)) . ' 23:59';
		}
		// double check that $high is not in the future
		$compare = strtotime($high);
		$difference = max(($now - $compare),0);
		if($difference == 0)
		{
			$high = date("Y-m-d H:i",$now);
		}
		$Results[] = "Date: " . date("H:i F j, Y",strtotime($high)) . " through " . date("H:i F j, Y",strtotime($low));
	}
	// no matches yet?
	// continue
	if(empty($Results))
	{
		// lets see if the query input could possibly be a chat message
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			// check to see if there are any similar chat messages
			$ChatMatch_q = @mysqli_query($BF4stats,"
				SELECT `logMessage`
				FROM `tbl_chatlog`
				WHERE `ServerID` = {$ServerID}
				AND `logMessage` LIKE '%{$Chat_Search}%'
				LIMIT 0, 1
			");
		}
		// or else this is a global stats page
		else
		{
			// check to see if there are any similar chat messages
			$ChatMatch_q = @mysqli_query($BF4stats,"
				SELECT `logMessage`
				FROM `tbl_chatlog`
				WHERE `logMessage` LIKE '%{$Chat_Search}%'
				LIMIT 0, 1
			");
		}
		// at least one similar chat message was found
		if(@mysqli_num_rows($ChatMatch_q) != 0)
		{
			// add as an option to the array of results
			$Results[] = "Message: " . $Chat_Search;
		}
		// no matches yet?
		// continue
		if(empty($Results))
		{
			// lets see if the query input could possibly be a soldier name
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
					AND tpd.`SoldierName` LIKE '%{$Chat_Search}%'
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
					WHERE tpd.`SoldierName` LIKE '%{$Chat_Search}%'
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
					$Results[] = $Soldiers_r['SoldierName'];
				}
			}
		}
	}
}
echo json_encode($Results);
?>