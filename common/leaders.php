<?php
// leaderboard stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo '
<div id="tabs">
<ul>
<li><div class="subscript">1</div><a href="#tabs-1">Top Players</a></li>
<li><div class="subscript">2</div><a href="./common/sessions-tab.php?sid=' . $ServerID . '&amp;gid=' . $GameID . '">Top 20 Players This Week</a></li>
</ul>
<div id="tabs-1">
';

// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// find out how many rows are in the table 
	$TotalRows_q = @mysqli_query($BF4stats,"
		SELECT COUNT(tpd.`SoldierName`)
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tsp.`ServerID` = {$ServerID}
		AND tpd.`GameID` = {$GameID}
	");
	$TotalRows_r = @mysqli_fetch_row($TotalRows_q);
	$numrows = $TotalRows_r[0];
}
// or else this is a global stats page
else
{
	// find out how many rows are in the table
	$TotalRows_q = @mysqli_query($BF4stats,"
		SELECT SUM(tps.`Score`) AS Score
		FROM `tbl_playerdata` tpd
		INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
		WHERE tpd.`GameID` = {$GameID}
		GROUP BY tpd.`PlayerID`
	");
	$numrows = @mysqli_num_rows($TotalRows_q);
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
	if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Rounds' AND $rank != 'Kills' AND $rank != 'Deaths' AND $rank != 'KDR' AND $rank != 'Headshots' AND $rank != 'HSR')
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
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// get the info from the db 
	$Players_q  = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, tps.`Deaths`, (tps.`Kills`/tps.`Deaths`) AS KDR, tps.`Rounds`, tps.`Headshots`, (tps.`Headshots`/tps.`Kills`) AS HSR
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
		SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, SUM(tps.`Rounds`) AS Rounds, SUM(tps.`Headshots`) AS Headshots, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR
		FROM `tbl_playerdata` tpd
		INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
		WHERE tpd.`GameID` = {$GameID}
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
	<table class="prettytable">
	<tr>
	<th width="3%" class="countheader">#</th>
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=SoldierName&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=SoldierName&amp;o=';
	}
	if($rank != 'SoldierName')
	{
		echo 'ASC"><span class="orderheader">Player</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Player</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="12%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Score&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="12%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Score&amp;o=';
	}
	if($rank != 'Score')
	{
		echo 'DESC"><span class="orderheader">Score</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Score</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Rounds&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Rounds&amp;o=';
	}
	if($rank != 'Rounds')
	{
		echo 'DESC"><span class="orderheader">Rounds</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Rounds</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Kills&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Kills&amp;o=';
	}
	if($rank != 'Kills')
	{
		echo 'DESC"><span class="orderheader">Kills</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Kills</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Deaths&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Deaths&amp;o=';
	}
	if($rank != 'Deaths')
	{
		echo 'DESC"><span class="orderheader">Deaths</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Deaths</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=KDR&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=KDR&amp;o=';
	}
	if($rank != 'KDR')
	{
		echo 'DESC"><span class="orderheader">Kill/Death</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered'. $order . 'header">Kill/Death</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Headshots&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Headshots&amp;o=';
	}
	if($rank != 'Headshots')
	{
		echo 'DESC"><span class="orderheader">Headshots</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Headshots</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="12%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=HSR&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="12%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=HSR&amp;o=';
	}
	if($rank != 'HSR')
	{
		echo 'DESC"><span class="orderheader">Headshot/Kill</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Headshot/Kill</span></a></th>';
	}
	echo '</tr>';
	// while there are rows to be fetched...
	while($Players_r = @mysqli_fetch_assoc($Players_q))
	{
		$Score = $Players_r['Score'];
		$SoldierName = $Players_r['SoldierName'];
		$PlayerID = $Players_r['PlayerID'];
		$Kills = $Players_r['Kills'];
		$Deaths = $Players_r['Deaths'];
		$Headshots = $Players_r['Headshots'];
		$KDR = round($Players_r['KDR'], 2);
		$HSR = round(($Players_r['HSR']*100),2);
		$Rounds = $Players_r['Rounds'];
		$count++;
		echo '
		<tr>
		<td width="3%" class="count"><span class="information">' . $count . '</span></td>
		';
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '<td width="18%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="18%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		echo '
		<td width="12%" class="tablecontents">' . $Score . '</td>
		<td width="11%" class="tablecontents">' . $Rounds . '</td>
		<td width="11%" class="tablecontents">' . $Kills . '</td>
		<td width="11%" class="tablecontents">' . $Deaths . '</td>
		<td width="11%" class="tablecontents">' . $KDR . '</td>
		<td width="11%" class="tablecontents">' . $Headshots . '</td>
		<td width="12%" class="tablecontents">' . $HSR . '<span class="information"> %</span></td>
		</tr>	
		';
	}
	echo '
	</table>
	';
	// build the pagination links
	pagination_links($ServerID,$_SERVER['PHP_SELF'],$page,$currentpage,$totalpages,$rank,$order,$query);
}
else
{
	echo '
	<table class="prettytable">
	<tr>
	<td class="tablecontents">
	<div class="headline">
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo 'No player stats found for this server.';
	}
	// or else this is a global stats page
	else
	{
		echo 'No player stats found for these servers.';
	}
	echo '
	</div>
	</td>
	</tr>
	</table>
	';
}
// free up total rows query memory
@mysqli_free_result($TotalRows_q);
// free up players query memory
@mysqli_free_result($Players_q);

echo '
</div>
</div>
';

?>
