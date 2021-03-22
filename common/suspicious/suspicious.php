<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../functions.php');
require_once('../case.php');
// default variable to null
$ServerID = null;
// get value
if(!empty($sid))
{
	$ServerID = $sid;
}
// javascript transition wrapper between loading and loaded
echo '
<script type="text/javascript">
$(\'#loading\').hide(0);
$(\'#loaded\').fadeIn("slow");
</script>
';
// continue html output
echo '
<div class="subsection">
<div class="headline">
Just because a player shows up as being suspicious does not necessarily mean that they are cheating.
</div>
</div>
<br/><br/>
';
// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
// count the total number of results
echo '<div style="position: relative;">';
$numrows = cache_total_suspects($ServerID,$valid_ids,$GameID,$BF4stats);
echo '</div>';
// number of rows to show per page
$rowsperpage = 20;
// find out total pages
$totalpages = ceil($numrows / $rowsperpage);
// set current pagination page to default if none provided
if(empty($currentpage))
{
	// default page num
	$currentpage = 1;
}
// if current page is greater than total pages...
if($currentpage > $totalpages)
{
	// set current page to last page
	$currentpage = $totalpages;
}
// if current page is less than first page...
if($currentpage < 1)
{
	// set current page to first page
	$currentpage = 1;
}
// get current rank query details
if(!empty($rank))
{
	// filter out SQL injection
	if($rank != 'SoldierName' AND $rank != 'KDR' AND $rank != 'HSR' AND $rank != 'Rounds')
	{
		// unexpected input detected
		// use default instead
		$rank = 'KDR';
	}
}
// set default if no rank provided in URL
else
{
	$rank = 'KDR';
}
// get current order query details
if(!empty($order))
{
	// filter out SQL injection
	if($order != 'DESC' AND $order != 'ASC')
	{
		// unexpected input detected
		// use default instead
		$order = 'DESC';
		$nextorder = 'ASC';
	}
	else
	{
		if($order == 'DESC')
		{
			$nextorder = 'ASC';
		}
		else
		{
			$nextorder = 'DESC';
		}
	}
}
// set default if no order provided in URL
else
{
	$order = 'DESC';
	$nextorder = 'ASC';
}
// the pagination offset of the list, based on current page 
$offset = ($currentpage - 1) * $rowsperpage;
// no suspicious players found
if($numrows == 0)
{
	echo '
	<div class="subsection">
	<div class="headline">No suspicious players found in';
	if(!empty($ServerID))
	{
		echo ' this server.';
	}
	else
	{
		echo ' these servers.';
	}
	echo '</div>
	</div>
	';
}
// found suspicious players
else
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// is adkats information available?
		if($adkats_available)
		{
			// check for suspicious players
			$Suspicious_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tps.`Rounds`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR, tpd.`PlayerID`, adk.`ban_status`
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND (((tps.`Kills`/tps.`Deaths`) > 5 AND (tps.`Headshots`/tps.`Kills`) > 0.70 AND tps.`Kills` > 30 AND tps.`Rounds` > 1) OR ((tps.`Kills`/tps.`Deaths`) > 10 AND tps.`Kills` > 50 AND tps.`Rounds` > 1))
				AND tpd.`GameID` = {$GameID}
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT {$offset}, {$rowsperpage}
			");
		}
		else
		{
			// check for suspicious players
			$Suspicious_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tps.`Rounds`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR, tpd.`PlayerID`
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND (((tps.`Kills`/tps.`Deaths`) > 5 AND (tps.`Headshots`/tps.`Kills`) > 0.70 AND tps.`Kills` > 30 AND tps.`Rounds` > 1) OR ((tps.`Kills`/tps.`Deaths`) > 10 AND tps.`Kills` > 50 AND tps.`Rounds` > 1))
				AND tpd.`GameID` = {$GameID}
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT {$offset}, {$rowsperpage}
			");
		}
	}
	// or else this is a global stats page
	else
	{
		// is adkats information available?
		if($adkats_available)
		{
			// check for suspicious players
			$Suspicious_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, SUM(tps.`Rounds`) AS Rounds, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, tpd.`PlayerID`, adk.`ban_status`
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
				WHERE (((tps.`Kills`/tps.`Deaths`) > 5 AND (tps.`Headshots`/tps.`Kills`) > 0.70 AND tps.`Kills` > 30 AND tps.`Rounds` > 1) OR ((tps.`Kills`/tps.`Deaths`) > 10 AND tps.`Kills` > 50 AND tps.`Rounds` > 1))
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`SoldierName`
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT {$offset}, {$rowsperpage}
			");
		}
		else
		{
			// check for suspicious players
			$Suspicious_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, SUM(tps.`Rounds`) AS Rounds, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, tpd.`PlayerID`
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE (((tps.`Kills`/tps.`Deaths`) > 5 AND (tps.`Headshots`/tps.`Kills`) > 0.70 AND tps.`Kills` > 30 AND tps.`Rounds` > 1) OR ((tps.`Kills`/tps.`Deaths`) > 10 AND tps.`Kills` > 50 AND tps.`Rounds` > 1))
				AND tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`SoldierName`
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT {$offset}, {$rowsperpage}
			");
		}
	}
	// start the table
	echo '
	<table class="prettytable">
	<tr>
	<th width="5%" class="countheader">#</th>
	';
	// player column
	pagination_headers('Player',$ServerID,'suspicious','20','r',$rank,'SoldierName','o',$order,'ASC',$nextorder,$currentpage,'','','');
	// kdr column
	pagination_headers('Kill / Death',$ServerID,'suspicious','20','r',$rank,'KDR','o',$order,'DESC',$nextorder,$currentpage,'','','');
	// hsr column
	pagination_headers('Headshot / Kill',$ServerID,'suspicious','20','r',$rank,'HSR','o',$order,'DESC',$nextorder,$currentpage,'','','');
	// rounds played column
	pagination_headers('Rounds Played',$ServerID,'suspicious','20','r',$rank,'Rounds','o',$order,'DESC',$nextorder,$currentpage,'','','');
	echo '
	</tr>
	</table>
	';
	// offset pagination count when calculating the player's rank
	$count = ($currentpage * 20) - 20;
	// while there are suspicious players, display them
	while($Suspicious_r = @mysqli_fetch_assoc($Suspicious_q))
	{
		$SoldierName = textcleaner($Suspicious_r['SoldierName']);
		$PlayerID = $Suspicious_r['PlayerID'];
		$KDR = round($Suspicious_r['KDR'], 2);
		$HSpercent = round(($Suspicious_r['HSR']*100), 2);
		$Rounds = $Suspicious_r['Rounds'];
		$count++;
		$link = './index.php?';
		if(!empty($ServerID))
		{
			$link .= 'sid=' . $ServerID . '&amp;';
		}
		$link .= 'pid=' . $PlayerID . '&amp;p=player';
		// is this player banned?
		// or have previous ban which was lifted?
		$player_banned = 0;
		$previous_banned = 0;
		if($adkats_available)
		{
			$ban_status = $Suspicious_r['ban_status'];
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
					echo '<td width="20%" class="banoutline"><div class="bansubscript">Banned</div>';
				}
				elseif($previous_banned == 1)
				{
					echo '<td width="20%" class="warnoutline"><div class="bansubscript">Warned</div>';
				}
				else
				{
					echo '<td width="20%" class="tablecontents">';
				}
				echo '
				<a href="' . $link . '">' . $SoldierName . '</a></td>
				<td width="20%" class="tablecontents">' . $KDR . '</td>
				<td width="20%" class="tablecontents">' . $HSpercent . '<span class="information"> %</span></td>
				<td width="20%" class="tablecontents">' . $Rounds . '</td>
			</tr>
		</table>
		';
	}
	// build the pagination links
	pagination_links($ServerID,'./index.php',$page,$currentpage,$totalpages,$rank,$order,'');
}
?>