<?php
// BF4 Stats Page by Ty_ger07
// https://forum.myrcon.com/showthread.php?6854

// include required files
require_once('../../config/config.php');
require_once('../functions.php');
require_once('../connect.php');
require_once('../case.php');
require_once('../constants.php');
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
Statistics data presented is not from all BF4 servers.
</div>
</div>
<div class="subsection" style="margin-top: 2px;">
<div class="headline">
These are the statistics of players';
if(!empty($ServerID))
{
	echo ' only in this server.';
}
else
{
	echo ' in ' . $clan_name . '\'s servers.';
}
echo '
</div>
</div>
<br/>
<br/>
';
// if there is a ServerID, this is a server stats page
// that means that we should show scoreboard on home page
if(!empty($ServerID))
{
	require_once('./scoreboard-wrapper.php');
	echo '<br/><br/>';
}
echo '
<div class="subsection">
<div class="headline">Top Players</div>
</div>
';
// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// find out how many rows are in the table 
	$TotalRows_q = @mysqli_query($BF4stats,"
		SELECT `CountPlayers`
		FROM `tbl_server_stats`
		WHERE `ServerID` = {$ServerID}
	");
	$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
	$numrows = $TotalRows_r['CountPlayers'];
}
// or else this is a global stats page
// use the function to efficiently find total players
else
{
	echo '<div style="position: relative;">';
	$numrows = cache_total_players($ServerID,$valid_ids,$GameID,$BF4stats);
	echo '</div>';
}
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
	if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Kills' AND $rank != 'KDR' AND $rank != 'HSR')
	{
		// unexpected input detected
		// use default instead
		$rank = 'Score';
	}
}
// set default if no rank provided in URL
else
{
	$rank = 'Score';
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
// the offset of the list, based on current page 
$offset = ($currentpage - 1) * $rowsperpage;
// if this is the default page, use cache
if($rank == 'Score' && $order == 'DESC' && $offset == '0')
{
	echo '<div style="position: relative;">';
	$Players_q = cache_top_twenty($ServerID,$valid_ids,$GameID,$BF4stats);
	echo '</div>';
}
else
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// get the info from the db 
		$Players_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
			ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
			LIMIT {$offset}, {$rowsperpage}
		");
	}
	// or else this is a global stats page
	else
	{
		// get the info from the db 
		$Players_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR
			FROM `tbl_playerdata` tpd
			INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
			WHERE tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` IN ({$valid_ids})
			GROUP BY tpd.`PlayerID`
			ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
			LIMIT {$offset}, {$rowsperpage}
		");
	}
}
// offset of player rank count to show on scoreboard
$count = ($currentpage * 20) - 20;
// check if there are rows returned
if(@mysqli_num_rows($Players_q) != 0)
{
	echo '
	<table class="prettytable">
	<tr>
	<th width="5%" class="countheader">#</th>
	';
	// player column
	pagination_headers('Player',$ServerID,'leaders','19','r',$rank,'SoldierName','o',$order,'ASC',$nextorder,$currentpage,'','','');
	// score column
	pagination_headers('Score',$ServerID,'leaders','19','r',$rank,'Score','o',$order,'DESC',$nextorder,$currentpage,'','','');
	// kills column
	pagination_headers('Kills',$ServerID,'leaders','19','r',$rank,'Kills','o',$order,'DESC',$nextorder,$currentpage,'','','');
	// kdr column
	pagination_headers('Kill / Death',$ServerID,'leaders','19','r',$rank,'KDR','o',$order,'DESC',$nextorder,$currentpage,'','','');
	// hsr column
	pagination_headers('Headshot / Kill',$ServerID,'leaders','19','r',$rank,'HSR','o',$order,'DESC',$nextorder,$currentpage,'','','');
	echo '
	</tr>
	</table>
	';
	// while there are rows to be fetched...
	while($Players_r = @mysqli_fetch_assoc($Players_q))
	{
		$Score = $Players_r['Score'];
		$SoldierName = textcleaner($Players_r['SoldierName']);
		$PlayerID = $Players_r['PlayerID'];
		$Kills = $Players_r['Kills'];
		$KDR = round($Players_r['KDR'], 2);
		$HSR = round(($Players_r['HSR']*100),2);
		$count++;
		$link = './index.php?';
		if(!empty($ServerID))
		{
			$link .= 'sid=' . $ServerID . '&amp;';
		}
		$link .= 'pid=' . $PlayerID . '&amp;p=player';
		echo '
		<table class="prettytable" style="margin-top: -2px; position: relative;">
			<tr>
				<td width="5%" class="count">
					<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
						<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
					</div>
					<span class="information">' . $count . '</span>
				</td>
				<td width="19%" class="tablecontents"><a href="' . $link . '">' . $SoldierName . '</a></td>
				<td width="19%" class="tablecontents">' . $Score . '</td>
				<td width="19%" class="tablecontents">' . $Kills . '</td>
				<td width="19%" class="tablecontents">' . $KDR . '</td>
				<td width="19%" class="tablecontents">' . $HSR . '<span class="information"> %</span></td>
			</tr>
		</table>
		';
	}
	// build the pagination links
	pagination_links($ServerID,'./index.php','leaders',$currentpage,$totalpages,$rank,$order,'');
}
else
{
	echo '
	<div class="subsection" style="margin-top: 2px;">
	<div class="headline">
	No player stats found for';
	if(!empty($ServerID))
	{
		echo ' this server.';
	}
	else
	{
		echo ' these servers.';
	}
	echo '
	</div>
	</div>
	';
}
?>