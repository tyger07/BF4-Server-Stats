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
// continue
// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// find out how many rows are in the table
	$TotalRows_q  = @mysqli_query($BF4stats,"
		SELECT COUNT(tpd.`PlayerID`) AS Count
		FROM `tbl_playerdata` tpd
		INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
		INNER JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
		WHERE tsp.`ServerID` = {$ServerID}
		AND tpd.`GameID` = {$GameID}
		AND adk.`ban_status` = 'Active'
	");
	$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
	$numrows = $TotalRows_r['Count'];
}
// or else this is a global stats page
else
{
	// find out how many rows are in the table
	$TotalRows_q  = @mysqli_query($BF4stats,"
		SELECT COUNT(DISTINCT(tpd.`PlayerID`)) AS Count
		FROM `tbl_playerdata` tpd
		INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
		INNER JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
		WHERE tsp.`ServerID` IN ({$valid_ids})
		AND tpd.`GameID` = {$GameID}
		AND adk.`ban_status` = 'Active'
	");
	$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
	$numrows = $TotalRows_r['Count'];
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
	if($rank != 'SoldierName' AND $rank != 'KDR' AND $rank != 'HSR')
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
// the offset of the list, based on current page 
$offset = ($currentpage - 1) * $rowsperpage;
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// get the info from the db 
	$Players_q  = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName`, tpd.`PlayerID`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR, abr.`record_message`
		FROM `tbl_playerdata` tpd
		INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
		INNER JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
		LEFT JOIN `adkats_records_main` abr ON abr.`record_id` = adk.`latest_record_id`
		WHERE tsp.`ServerID` = {$ServerID}
		AND tpd.`GameID` = {$GameID}
		AND adk.`ban_status` = 'Active'
		ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
		LIMIT {$offset}, {$rowsperpage}
	");
}
// or else this is a global stats page
else
{
	// get the info from the db 
	$Players_q  = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName`, tpd.`PlayerID`, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, abr.`record_message`
		FROM `tbl_playerdata` tpd
		INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
		INNER JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
		LEFT JOIN `adkats_records_main` abr ON abr.`record_id` = adk.`latest_record_id`
		WHERE tpd.`GameID` = {$GameID}
		AND tsp.`ServerID` IN ({$valid_ids})
		AND adk.`ban_status` = 'Active'
		GROUP BY tpd.`PlayerID`
		ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
		LIMIT {$offset}, {$rowsperpage}
	");
}
// offset of player rank count to show on scoreboard
$count = ($currentpage * 20) - 20;
// check if there are rows returned
if(@mysqli_num_rows($Players_q) != 0)
{
	echo '
	<table class="prettytable" style="margin-top: -2px;">
	<tr>
	<th width="5%" class="countheader">#</th>
	';
	// player column
	pagination_headers('Player',$ServerID,'bans','19','r',$rank,'SoldierName','o',$order,'ASC',$nextorder,$currentpage,'',$player,'');
	// kdr column
	pagination_headers('Kill / Death',$ServerID,'bans','15','r',$rank,'KDR','o',$order,'DESC',$nextorder,$currentpage,'',$player,'');
	// hsr column
	pagination_headers('Headshot / Kill',$ServerID,'bans','15','r',$rank,'HSR','o',$order,'DESC',$nextorder,$currentpage,'',$player,'');
	echo '
	<th width="46%">Ban Reason</th>
	</tr>
	</table>
	';
	// while there are rows to be fetched...
	while($Players_r = @mysqli_fetch_assoc($Players_q))
	{
		$SoldierName = textcleaner($Players_r['SoldierName']);
		$PlayerID = $Players_r['PlayerID'];
		$KDR = round($Players_r['KDR'], 2);
		$HSR = round(($Players_r['HSR']*100),2);
		$ban_message = textcleaner($Players_r['record_message']);
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
				<td width="19%" class="banoutline"><div class="bansubscript">Banned</div><a href="' . $link . '">' . $SoldierName . '</a></td>
				<td width="15%" class="tablecontents">' . $KDR . '</td>
				<td width="15%" class="tablecontents">' . $HSR . '</td>
				<td width="46%" class="tablecontents">' . $ban_message . '</td>
			</tr>
		</table>
		';
	}
	if(empty($player))
	{
		// build the pagination links
		pagination_links($ServerID,'./index.php',$page,$currentpage,$totalpages,$rank,$order,'');
	}
}
else
{
	echo '
	<table class="prettytable" style="margin-top: -2px;">
	<tr>
	<td class="tablecontents">
	<div class="headline">
	No bans found for ';
	if(!empty($ServerID))
	{
		echo 'this server.';
	}
	else
	{
		echo 'these servers.';
	}
	echo '
	</div>
	</td>
	</tr>
	</table>
	';
}
?>