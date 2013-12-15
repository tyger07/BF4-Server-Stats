<?php
// server stats home page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo'
<table width="100%" border="0">
<tr>
<td width="100%" align="center" style="text-align: left;">
';
// change this text if global scoreboard is being scrolled through
if(!isset($_GET['topglobal']) OR empty($_GET['topglobal']))
{
	echo '<br/>&nbsp; <font size="3">Please select the desired server stats page from our game servers listed below:</font><br/>';
}
else
{
	echo '<br/><a href="' . $_SERVER['PHP_SELF'] . '"><font size="3">Return to ' . $clan_name . ' Stats Index Page</font></a>';
}
echo '
</td>
</tr>
';
// don't show stats index if global scoreboard is being scrolled through
if(!isset($_GET['topglobal']) OR empty($_GET['topglobal']))
{
	// initialize values
	$now_timestamp = time();
	$old = $now_timestamp - 1800;
	// go through each detected server ID
	foreach($ServerIDs as $this_ServerID)
	{
		// get server stats
		$Stats_q = @mysqli_query($BF4stats,"
			SELECT `CountPlayers`, `SumScore`, `SumKills`
			FROM tbl_server_stats
			WHERE `ServerID` = {$this_ServerID}
		");
		if(@mysqli_num_rows($Stats_q) != 0)
		{
			$Stats_r = @mysqli_fetch_assoc($Stats_q);
			$total_players = $Stats_r['CountPlayers'];
			$score = $Stats_r['SumScore'];
			$kills = $Stats_r['SumKills'];
		}
		// some sort of error occured
		else
		{
			$total_players = 'Unknown';
			$score = 'Unknown';
			$kills = 'Unknown';
		}
		// free up stats query memory
		@mysqli_free_result($Stats_q);
		// get current players
		$CurrentPlayers_q = @mysqli_query($BF4stats,"
			SELECT count(`TeamID`) AS count
			FROM tbl_currentplayers
			WHERE `ServerID` = {$this_ServerID}
		");
		if(@mysqli_num_rows($CurrentPlayers_q) != 0)
		{
			$CurrentPlayers_r = @mysqli_fetch_assoc($CurrentPlayers_q);
			$players = $CurrentPlayers_r['count'];
		}
		// some sort of error occured
		else
		{
			$players = 'Unknown';
		}
		// free up stats query memory
		@mysqli_free_result($CurrentPlayers_q);
		// get current map
		$CurrentMap_q = @mysqli_query($BF4stats,"
			SELECT `mapName`, `ServerName`
			FROM tbl_server
			WHERE `ServerID` = {$this_ServerID}
		");
		if(@mysqli_num_rows($CurrentMap_q) != 0)
		{
			$CurrentMap_r = @mysqli_fetch_assoc($CurrentMap_q);
			// convert map to friendly name
			$map = $CurrentMap_r['mapName'];
			$ServerName = $CurrentMap_r['ServerName'];
			$map_name = array_search($map,$map_array);
			// compile map image
			$map_img = './images/maps/' . $map . '.png';
		}
		// some sort of error occured
		else
		{
			$map = 'Unknown';
			$ServerName = 'Unknown';
			$map_img = './images/40.png';
		}
		// free up stats query memory
		@mysqli_free_result($CurrentMap_q);
		// remove sessions older than 30 minutes from this server
		@mysqli_query($BF4stats,"
			DELETE FROM ses_{$this_ServerID}_tbl
			WHERE `timestamp` <= {$old}
		");
		// optimize this sessions table
		@mysqli_query($BF4stats,"
			OPTIMIZE TABLE ses_{$this_ServerID}_tbl");
		// count sessions
		$Session_q = @mysqli_query($BF4stats,"
			SELECT count(`IP`) AS count
			FROM ses_{$this_ServerID}_tbl
			WHERE 1
		");
		if(@mysqli_num_rows($Session_q) != 0)
		{
			$Session_r = @mysqli_fetch_assoc($Session_q);
			$sessions = $Session_r['count'];
		}
		// some sort of error occured
		else
		{
			$sessions = 'Unknown';
		}
		// free up stats query memory
		@mysqli_free_result($Session_q);
		echo '
		<tr>
		<td width="100%" style="text-align: left;">
		<br/>
		<div class="mapimage" style="background-image: url(' . $map_img . ');">
		<div class="simplecontent">
		<table width="95%" align="center" border="0">
		<tr>
		<td width="35%">
		<br/><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $this_ServerID . '"><font size="3">' . $ServerName . '</font></a><br/><br/>
		</td>
		<td width="22%">
		<br/><br/><font class="information">Current Players In Server:</font> ' . $players . '<br/><br/>
		</td>
		<td width="22%">
		<br/><br/><font class="information">Current Map:</font> ' . $map_name . '<br/><br/>
		</td>
		<td width="21%">
		<br/><br/><font class="information">Users Viewing Stats:</font> ' . $sessions . '<br/><br/>
		</td>
		</tr>
		<tr>
		<td width="35%">
		<a href="http://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . $ServerName . '" target="_blank"><img src="./images/joinbtn.png" alt="join"/></a><br/><br/><br/>
		</td>
		<td width="22%">
		<font class="information">Players Logged:</font> ' . $total_players . '<br/><br/><br/>
		</td>
		<td width="22%">
		<font class="information">Total Score:</font> ' . $score . '<br/><br/><br/>
		</td>
		<td width="21%">
		<font class="information">Total Kills:</font> ' . $kills . '<br/><br/><br/>
		</td>
		</tr>
		</table>
		</div>
		</div>
		</td>
		</tr>
		';
	}
}
echo '
</table>
<br/><br/>
<table width="100%" border="0">
<tr>
<td valign="top" align="center">
<div class="middlecontent">
<table width="100%" border="0">
<tr>
<th class="headline"><b>Global Top Players in These Servers</b></th>
</tr>
<tr>
<td>
<div class="innercontent">
';
// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
// find out how many rows are in the table
$TotalRows_q = @mysqli_query($BF4stats,"
	SELECT SUM(tps.Score) AS Score
	FROM tbl_playerdata tpd
	INNER JOIN tbl_server_player tsp ON tsp.PlayerID = tpd.PlayerID
	INNER JOIN tbl_playerstats tps ON tps.StatsID = tsp.StatsID
	WHERE 1
	GROUP BY tpd.PlayerID
");
$numrows = @mysqli_num_rows($TotalRows_q);
// number of rows to show per page
$rowsperpage = 25;
// find out total pages
$totalpages = ceil($numrows / $rowsperpage);
// get the current page or set a default
if(isset($_GET['currentpage']) && is_numeric($_GET['currentpage']))
{
	// cast var as int
	$currentpage = (int) $_GET['currentpage'];
}
else
{
	// default page num
	$currentpage = 1;
}
// if current page is greater than total pages...
if ($currentpage > $totalpages)
{
	// set current page to last page
	$currentpage = $totalpages;
}
// if current page is less than first page...
if ($currentpage < 1)
{
	// set current page to first page
	$currentpage = 1;
}
// get current rank query details
if(isset($_GET['rank']) AND !empty($_GET['rank']))
{
	$rank = $_GET['rank'];
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
if(isset($_GET['order']) AND !empty($_GET['order']))
{
	$order = $_GET['order'];
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
// get the info from the db 
$Players_q  = @mysqli_query($BF4stats,"
	SELECT tpd.SoldierName, tpd.PlayerID, SUM(tps.Score) AS Score, SUM(tps.Kills) AS Kills, SUM(tps.Deaths) AS Deaths, (SUM(tps.Kills)/SUM(tps.Deaths)) AS KDR, SUM(tps.Rounds) AS Rounds, SUM(tps.Headshots) AS Headshots, (SUM(tps.Headshots)/SUM(tps.Kills)) AS HSR
	FROM tbl_playerdata tpd
	INNER JOIN tbl_server_player tsp ON tsp.PlayerID = tpd.PlayerID
	INNER JOIN tbl_playerstats tps ON tps.StatsID = tsp.StatsID
	WHERE 1
	GROUP BY tpd.PlayerID
	ORDER BY {$rank} {$order}, SoldierName {$nextorder} LIMIT {$offset}, {$rowsperpage}
");
// offset of player rank count to show on scoreboard
$count = ($currentpage * 25) - 25;
// check if there are rows returned
if(@mysqli_num_rows($Players_q) != 0)
{
	echo '
	<table width="98%" align="center" class="prettytable" border="0">
	<tr>
	<th width="5%" style="text-align:left">#</th>
	<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $currentpage . '&amp;topglobal=1&amp;rank=SoldierName&amp;order=';
	if($rank != 'SoldierName')
	{
		echo 'ASC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Player</span></a></th>
	<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $currentpage . '&amp;topglobal=1&amp;rank=Score&amp;order=';
	if($rank != 'Score')
	{
		echo 'DESC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Score</span></a></th>
	<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $currentpage . '&amp;topglobal=1&amp;rank=Rounds&amp;order=';
	if($rank != 'Rounds')
	{
		echo 'DESC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Rounds Played</span></a></th>
	<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $currentpage . '&amp;topglobal=1&amp;rank=Kills&amp;order=';
	if($rank != 'Kills')
	{
		echo 'DESC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Kills</span></a></th>
	<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $currentpage . '&amp;topglobal=1&amp;rank=Deaths&amp;order=';
	if($rank != 'Deaths')
	{
		echo 'DESC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Deaths</span></a></th>
	<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $currentpage . '&amp;topglobal=1&amp;rank=KDR&amp;order=';
	if($rank != 'KDR')
	{
		echo 'DESC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Kill/Death Ratio</span></a></th>
	<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $currentpage . '&amp;topglobal=1&amp;rank=Headshots&amp;order=';
	if($rank != 'Headshots')
	{
		echo 'DESC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Headshots</span></a></th>
	<th width="11%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $currentpage . '&amp;topglobal=1&amp;rank=HSR&amp;order=';
	if($rank != 'HSR')
	{
		echo 'DESC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Headshot Ratio</span></a></th>
	</tr>';
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
		<td class="tablecontents" width="5%"><font class="information">' . $count . ':</font></td>
		<td width="18%" class="tablecontents">' . $SoldierName . '</td>
		<td width="11%" class="tablecontents">' . $Score . '</td>
		<td width="11%" class="tablecontents">' . $Rounds . '</td>
		<td width="11%" class="tablecontents">' . $Kills . '</td>
		<td width="11%" class="tablecontents">' . $Deaths . '</td>
		<td width="11%" class="tablecontents">' . $KDR . '</td>
		<td width="11%" class="tablecontents">' . $Headshots . '</td>
		<td width="11%" class="tablecontents">' . $HSR . '<font class="information"> %</font></td>
		</tr>	
		';
	}
	echo '</table>';
}
else
{
	echo '
	<table width="95%" align="center" border="0">
	<tr>
	<td style="text-align: left;" width="100%"><br/><center><font class="information">No player stats found for this server.</font></center><br/></td>
	</tr>
	</table>
	';
}
// free up players query memory
@mysqli_free_result($Players_q);
// build the pagination links
// don't display pagination links if no players found
if(@mysqli_num_rows($TotalRows_q) != 0)
{
	echo '
	<div class="pagination">
	<center>
	';
	// range of num links to show
	$range = 3;
	// if on page 1, don't show back links
	if ($currentpage > 1)
	{
		// show << link to go back to first page
		echo '<a href="' . $_SERVER['PHP_SELF'] . '?currentpage=1&amp;topglobal=1&amp;rank=' . $rank . '&amp;order=' . $order . '">&lt;&lt;</a>';
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back one page
		echo ' <a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $prevpage . '&amp;topglobal=1&amp;rank=' . $rank . '&amp;order=' . $order . '">&lt;</a> ';
	}
	// loop to show links to range of pages around current page
	for($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++)
	{
		// if it's a valid page number...
		if (($x > 0) && ($x <= $totalpages))
		{
			// if we're on current page...
			if ($x == $currentpage)
			{
				// 'highlight' it but don't make a link
				echo ' [<font class="information">' . $x . '</font>] ';
			}
			else
			{
				// make it a link
				echo ' <a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $x . '&amp;topglobal=1&amp;rank=' . $rank . '&amp;order=' . $order . '">' . $x . '</a> ';
			}
		}
	}
	// if not on last page, show forward links        
	if ($currentpage != $totalpages)
	{
		// get next page
		$nextpage = $currentpage + 1;
		// show > link to go forward one page
		echo ' <a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $nextpage . '&amp;topglobal=1&amp;rank=' . $rank . '&amp;order=' . $order . '">&gt;</a> ';
		// show >> link to last page
		echo '<a href="' . $_SERVER['PHP_SELF'] . '?currentpage=' . $totalpages . '&amp;topglobal=1&amp;rank=' . $rank . '&amp;order=' . $order . '">&gt;&gt;</a>';
	}
	echo '
	</center>
	</div>
	';
}
// end build pagination links and end block
// free up total rows query memory
@mysqli_free_result($TotalRows_q);
echo '
</div>
</td>
</tr>
</table>
</div>
</td></tr>
</table>
<table width="100%">
<tr>
<td>
';
// don't show server totals if global scoreboard is being scrolled through
if(!isset($_GET['topglobal']) OR empty($_GET['topglobal']))
{
	// query for server totals
	$ServerTotals_q = @mysqli_query($BF4stats,"
		SELECT SUM(CountPlayers) AS total_players, SUM(SumRounds) AS total_rounds, SUM(SumPlaytime) AS total_playtime, SUM(SumTKs) AS total_tks, SUM(SumKills) AS total_kills, SUM(SumDeaths) AS total_deaths, SUM(SumHeadshots) AS total_headshots, SUM(SumSuicide) AS total_suicides
		FROM tbl_server_stats
		WHERE 1
	");
	if(@mysqli_num_rows($ServerTotals_q) != 0)
	{
		$ServerTotals_r = @mysqli_fetch_assoc($ServerTotals_q);
		$total_players = $ServerTotals_r['total_players'];
		$total_rounds = $ServerTotals_r['total_rounds'];
		$total_playtime = $ServerTotals_r['total_playtime'];
		$total_days = round($total_playtime/60/60/24,0);
		$total_tks = $ServerTotals_r['total_tks'];
		$total_kills = $ServerTotals_r['total_kills'];
		$total_deaths = $ServerTotals_r['total_deaths'];
		$total_headshots = $ServerTotals_r['total_headshots'];
		$total_suicides = $ServerTotals_r['total_suicides'];
		echo '
		<br/><br/>
		<div class="middlecontent">
		<table width="100%" border="0">
		<tr>
		<th class="headline"><b>Server Totals</b></th>
		</tr>
		<tr>
		<td>
		<div class="innercontent">
		<table width="98%" align="center" border="0" class="prettytable">
		<tr>
		<td width="10%" style="text-align:left"><br/>&nbsp;<br/><br/></td>
		<td width="22%" style="text-align:left"><br/><font class="information">Total Players:</font> ' . $total_players . '<br/><br/></td>
		<td width="22%" style="text-align:left"><br/><font class="information">Total Rounds Played:</font> ' . $total_rounds . '<br/><br/></td>
		<td width="22%" style="text-align:left"><br/><font class="information">Total Days Played:</font> ' . $total_days . '<br/><br/></td>
		<td width="22%" style="text-align:left"><br/><font class="information">Total Team Kills:</font> ' . $total_tks . '<br/><br/></td>
		</tr>
		<tr>
		<td width="10%" style="text-align:left">&nbsp;</td>
		<td width="22%" style="text-align:left"><font class="information">Total Kills:</font> ' . $total_kills . '</td>
		<td width="22%" style="text-align:left"><font class="information">Total Deaths:</font> ' . $total_deaths . '</td>
		<td width="22%" style="text-align:left"><font class="information">Total Headshots:</font> ' . $total_headshots . '</td>
		<td width="22%" style="text-align:left"><font class="information">Total Suicides:</font> ' . $total_suicides . '</td>
		</tr>
		</table><br/>
		</div>
		</td>
		</tr>
		</table>
		</div>
		';
	}
	// free up server totals query memory
	@mysqli_free_result($ServerTotals_q);
}
echo '
</td>
</tr>
</table>
';
?>