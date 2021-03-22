<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// first connect to the database
// and include necessary files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
// default variable to null
$ServerID = null;
// get values
if(!empty($sid))
{
	$ServerID = $sid;
}
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// is adkats information available?
	if($adkats_available)
	{
		// get the info from the db 
		$Players_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`PlayerID`, tpd.`SoldierName`, SUM(tss.`Score`) AS Score, SUM(tss.`Kills`) AS Kills, (SUM(tss.`Kills`)/SUM(tss.`Deaths`)) AS KDR, (SUM(tss.`Headshots`)/SUM(tss.`Kills`)) AS HSR, adk.`ban_status`
			FROM `tbl_sessions` tss
			INNER JOIN `tbl_server_player` tsp ON tss.`StatsID` = tsp.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tss.`Starttime` BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
			AND tpd.`GameID` = {$GameID}
			GROUP BY tpd.`PlayerID`
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
	else
	{
		// get the info from the db 
		$Players_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`PlayerID`, tpd.`SoldierName`, SUM(tss.`Score`) AS Score, SUM(tss.`Kills`) AS Kills, (SUM(tss.`Kills`)/SUM(tss.`Deaths`)) AS KDR, (SUM(tss.`Headshots`)/SUM(tss.`Kills`)) AS HSR
			FROM `tbl_sessions` tss
			INNER JOIN `tbl_server_player` tsp ON tss.`StatsID` = tsp.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tss.`Starttime` BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
			AND tpd.`GameID` = {$GameID}
			GROUP BY tpd.`PlayerID`
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
}
// or else this is a global stats page
else
{
	// is adkats information available?
	if($adkats_available)
	{
		// get the info from the db 
		$Players_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`PlayerID`, tpd.`SoldierName`, SUM(tss.`Score`) AS Score, SUM(tss.`Kills`) AS Kills, (SUM(tss.`Kills`)/SUM(tss.`Deaths`)) AS KDR, (SUM(tss.`Headshots`)/SUM(tss.`Kills`)) AS HSR, adk.`ban_status`
			FROM `tbl_sessions` tss
			INNER JOIN `tbl_server_player` tsp ON tss.`StatsID` = tsp.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
			WHERE tss.`Starttime` BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
			AND tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` IN ({$valid_ids})
			GROUP BY tpd.`PlayerID`
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
	else
	{
		// get the info from the db 
		$Players_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`PlayerID`, tpd.`SoldierName`, SUM(tss.`Score`) AS Score, SUM(tss.`Kills`) AS Kills, (SUM(tss.`Kills`)/SUM(tss.`Deaths`)) AS KDR, (SUM(tss.`Headshots`)/SUM(tss.`Kills`)) AS HSR
			FROM `tbl_sessions` tss
			INNER JOIN `tbl_server_player` tsp ON tss.`StatsID` = tsp.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tss.`Starttime` BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
			AND tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` IN ({$valid_ids})
			GROUP BY tpd.`PlayerID`
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
}
// check if there are rows returned
if(@mysqli_num_rows($Players_q) != 0)
{
	echo '
	<table class="prettytable">
	<tr>
	<th width="5%" class="countheader">#</th>
	<th width="19%">Player</th>
	<th width="19%"><span class="orderedDESCheader">Score</span></th>
	<th width="19%">Kills</th>
	<th width="19%">Kill / Death</th>
	<th width="19%">Headshot / Kill</th>
	</tr>
	</table>
	';
	$count = 0;
	// while there are rows to be fetched...
	while($Player_r = @mysqli_fetch_assoc($Players_q))
	{
		$count++;
		$Soldier_Name = htmlspecialchars(strip_tags($Player_r['SoldierName']));
		$Player_ID = $Player_r['PlayerID'];
		$Score = $Player_r['Score'];
		$Kills = $Player_r['Kills'];
		$KDR = round($Player_r['KDR'],2);
		$HSR = round(($Player_r['HSR']*100),2);
		$link = './index.php?';
		if(!empty($ServerID))
		{
			$link .= 'sid=' . $ServerID . '&amp;';
		}
		$link .= 'pid=' . $Player_ID . '&amp;p=player';
		// is this player banned?
		// or have previous ban which was lifted?
		$player_banned = 0;
		$previous_banned = 0;
		if($adkats_available)
		{
			$ban_status = $Player_r['ban_status'];
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
		echo '
		<table class="prettytable" style="margin-top: -2px; position: relative;">
			<tr>
				<td width="5%" class="count">
					<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
						<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
					</div>
					<span class="information">' . $count . '</span>
				</td>
				';
				if($player_banned == 1)
				{
					echo '<td width="19%" class="banoutline"><div class="bansubscript">Banned</div>';
				}
				elseif($previous_banned == 1)
				{
					echo '<td width="19%" class="warnoutline"><div class="bansubscript">Warned</div>';
				}
				else
				{
					echo '<td width="19%" class="tablecontents">';
				}
				echo '
				<a href="' . $link . '">' . $Soldier_Name . '</a></td>
				<td width="19%" class="tablecontents">' . $Score . '</td>
				<td width="19%" class="tablecontents">' . $Kills . '</td>
				<td width="19%" class="tablecontents">' . $KDR . '</td>
				<td width="19%" class="tablecontents">' . $HSR . '<span class="information"> %</span></td>
			</tr>
		</table>
		';
	}
}
else
{
	echo '
	<table class="prettytable">
	<tr>
	<td class="tablecontents">
	<div class="headline">
	No session stats found for
	';
	if(!empty($ServerID))
	{
		echo 'this server';
	}
	else
	{
		echo 'these servers';
	}
	echo '
	this week.
	</div>
	</td>
	</tr>
	</table>
	';
}
?>