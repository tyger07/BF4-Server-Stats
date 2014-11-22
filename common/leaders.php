<?php
// leaderboard stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo '
<div id="tabs">
<ul>
<li><a href="#tabs-1">Top Players</a></li>
<li><a href="./common/sessions-tab.php?sid=' . $ServerID . '&amp;gid=' . $GameID . '">Top 20 Players This Week</a></li>
</ul>
<div id="tabs-1">
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
	
	// free up total rows query memory
	@mysqli_free_result($TotalRows_q);
}
// or else this is a global stats page
else
{
	echo '<div style="position: relative;">';
	$numrows = cache_total_players($ServerID, $valid_ids, $GameID, $BF4stats);
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
if($rank == 'Score' && $order == 'DESC' && $offset == '0' && empty($player))
{
	echo '<div style="position: relative;">';
	$Players_q = cache_top_twenty($ServerID, $valid_ids, $GameID, $BF4stats);
	echo '</div>';
}
// if a player name was entered, search for that players' position
elseif(!empty($player))
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// get the info from the db 
		$Players_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR
			FROM `tbl_playerdata` tpd
			INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
			AND tpd.`SoldierName` LIKE '%{$player}%'
			ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
			LIMIT 10
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
			AND tpd.`SoldierName` LIKE '%{$player}%'
			GROUP BY tpd.`PlayerID`
			ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
			LIMIT 10
		");
	}
}
else
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// get the info from the db 
		$Players_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR
			FROM `tbl_playerdata` tpd
			INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
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
	<td class="tablecontents">
	<form id="ajaxsearch2" action="' . $_SERVER['PHP_SELF'] . '" method="get">
	&nbsp;<span class="information">Find Player\'s Leaderboard Position:</span>
	<input type="hidden" name="p" value="leaders" />
	<input type="hidden" name="sid" value="' . $ServerID . '" />
	<input id="soldiers2" type="text" class="inputbox" ';
	// try to fill in search box
	if(!empty($SoldierName))
	{
		echo 'value="' . $SoldierName . '" ';
	}
	echo 'name="player" />
	</form>
	</td>
	</tr>
	</table>
	<table class="prettytable">
	<tr>
	<th width="5%" class="countheader">#</th>
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=SoldierName&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=SoldierName&amp;o=';
		}
	}
	// or else this is a global stats page
	else
	{
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=SoldierName&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=SoldierName&amp;o=';
		}
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
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=Score&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Score&amp;o=';
		}
	}
	// or else this is a global stats page
	else
	{
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=Score&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Score&amp;o=';
		}
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
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=Kills&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=Kills&amp;o=';
		}
	}
	// or else this is a global stats page
	else
	{
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=Kills&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=Kills&amp;o=';
		}
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
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=KDR&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=KDR&amp;o=';
		}
	}
	// or else this is a global stats page
	else
	{
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=KDR&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=KDR&amp;o=';
		}
	}
	if($rank != 'KDR')
	{
		echo 'DESC"><span class="orderheader">Kill / Death</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered'. $order . 'header">Kill / Death</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=HSR&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=leaders&amp;cp=' . $currentpage . '&amp;r=HSR&amp;o=';
		}
	}
	// or else this is a global stats page
	else
	{
		if(!empty($player))
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=leaders&amp;player=' . $player . '&amp;cp=' . $currentpage . '&amp;r=HSR&amp;o=';
		}
		else
		{
			echo '<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?cp=' . $currentpage . '&amp;p=leaders&amp;r=HSR&amp;o=';
		}
	}
	if($rank != 'HSR')
	{
		echo 'DESC"><span class="orderheader">Headshot / Kill</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Headshot / Kill</span></a></th>';
	}
	echo '</tr>';
	// while there are rows to be fetched...
	while($Players_r = @mysqli_fetch_assoc($Players_q))
	{
		$Score = $Players_r['Score'];
		$SoldierName = $Players_r['SoldierName'];
		$PlayerID = $Players_r['PlayerID'];
		$Kills = $Players_r['Kills'];
		$KDR = round($Players_r['KDR'], 2);
		$HSR = round(($Players_r['HSR']*100),2);
		if(empty($player))
		{
			$count++;
		}
		else
		{
			// check to see if this rank cache table exists
			@mysqli_query($BF4stats,"
				CREATE TABLE IF NOT EXISTS `tyger_stats_rank_cache`
				(`PlayerID` INT(10) UNSIGNED NOT NULL, `GID` INT(11) NOT NULL DEFAULT '0', `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `category` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, `rank` INT(10) UNSIGNED NOT NULL DEFAULT '0', `timestamp` INT(11) NOT NULL DEFAULT '0', INDEX (`PlayerID`, `SID`))
				ENGINE=MyISAM
				DEFAULT CHARSET=utf8
				COLLATE=utf8_bin
			");
			
			// initialize timestamp values
			$now_timestamp = time();
			$old = $now_timestamp - 43200;
			
			if($rank == 'SoldierName')
			{
				if(!empty($ServerID))
				{
					$rank_q = @mysqli_query($BF4stats,"
						SELECT sub2.rank
						FROM
							(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
							 FROM
								(SELECT tpd.`SoldierName`
								FROM `tbl_playerdata` tpd
								INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
								INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
								INNER JOIN (SELECT @num := 0) x
								WHERE tpd.`GameID` = {$GameID}
								AND tsp.`ServerID` = {$ServerID}
								GROUP BY tpd.`SoldierName`
								ORDER BY tpd.`SoldierName` ASC
								) sub
							) sub2
						WHERE sub2.`SoldierName` = '{$SoldierName}'
					");
				}
				// or else this is a global stats page
				else
				{
					$rank_q = @mysqli_query($BF4stats,"
						SELECT sub2.rank
						FROM
							(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
							 FROM
								(SELECT tpd.`SoldierName`
								FROM `tbl_playerdata` tpd
								INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
								INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
								INNER JOIN (SELECT @num := 0) x
								WHERE tpd.`GameID` = {$GameID}
								AND tsp.`ServerID` IN ({$valid_ids})
								GROUP BY tpd.`SoldierName`
								ORDER BY tpd.`SoldierName` ASC
								) sub
							) sub2
						WHERE sub2.`SoldierName` = '{$SoldierName}'
					");
				}
				if(@mysqli_num_rows($rank_q) == 1)
				{
					$rank_r = @mysqli_fetch_assoc($rank_q);
					$count = $rank_r['rank'];
				}
				else
				{
					$count = 0;
				}
			}
			elseif($rank == 'Score')
			{
				// rank players by score
				// check if score rank is already cached
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					$ScoreC_q = @mysqli_query($BF4stats,"
						SELECT `rank`, `timestamp`
						FROM `tyger_stats_rank_cache`
						WHERE `PlayerID` = {$PlayerID}
						AND `category` = 'Score'
						AND `GID` = '{$GameID}'
						AND `SID` = '{$ServerID}'
						GROUP BY `PlayerID`
					");
				}
				// or else this is a global stats page
				else
				{
					$ScoreC_q = @mysqli_query($BF4stats,"
						SELECT `rank`, `timestamp`
						FROM `tyger_stats_rank_cache`
						WHERE `PlayerID` = {$PlayerID}
						AND `category` = 'Score'
						AND `GID` = '{$GameID}'
						AND `SID` = '{$valid_ids}'
						GROUP BY `PlayerID`
					");
				}
				if(@mysqli_num_rows($ScoreC_q) != 0)
				{
					$ScoreC_r = @mysqli_fetch_assoc($ScoreC_q);
					$srank = $ScoreC_r['rank'];
					$timestamp = $ScoreC_r['timestamp'];
					
					// data older than 12 hours? or incorrect data? recalculate
					if(($timestamp <= $old) OR ($srank == 0))
					{
						// check if this is a top 20 player
						// if so, we can get their score rank much faster
						// if there is a ServerID, this is a server stats page
						if(!empty($ServerID))
						{
							$Top_q = @mysqli_query($BF4stats,"
								SELECT `PlayerID`
								FROM `tyger_stats_top_twenty_cache`
								WHERE `SID` = '{$ServerID}'
								AND `GID` = '{$GameID}'
								AND `timestamp` >= '{$old}'
								AND `PlayerID` = {$PlayerID}
							");
						}
						// or else this is a global stats page
						else
						{
							$Top_q = @mysqli_query($BF4stats,"
								SELECT `PlayerID`
								FROM `tyger_stats_top_twenty_cache`
								WHERE `SID` = '{$valid_ids}'
								AND `GID` = '{$GameID}'
								AND `timestamp` >= '{$old}'
								AND `PlayerID` = {$PlayerID}
							");
						}
						if(@mysqli_num_rows($Top_q) != 0)
						{
							// rank players by score
							// if there is a ServerID, this is a server stats page
							if(!empty($ServerID))
							{
								$Score_q = @mysqli_query($BF4stats,"
									SELECT sub2.rank
									FROM
										(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
										 FROM
											(SELECT `PlayerID`
											FROM `tyger_stats_top_twenty_cache`
											INNER JOIN (SELECT @num := 0) x
											WHERE `SID` = '{$ServerID}'
											AND `GID` = '{$GameID}'
											AND `timestamp` >= '{$old}'
											GROUP BY `PlayerID`
											ORDER BY `Score` DESC, `SoldierName` ASC
											) sub
										) sub2
									WHERE sub2.`PlayerID` = {$PlayerID}
								");
							}
							// or else this is a global stats page
							else
							{
								$Score_q = @mysqli_query($BF4stats,"
									SELECT sub2.rank
									FROM
										(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
										 FROM
											(SELECT `PlayerID`
											FROM `tyger_stats_top_twenty_cache`
											INNER JOIN (SELECT @num := 0) x
											WHERE `SID` = '{$valid_ids}'
											AND `GID` = '{$GameID}'
											AND `timestamp` >= '{$old}'
											GROUP BY `PlayerID`
											ORDER BY `Score` DESC, `SoldierName` ASC
											) sub
										) sub2
									WHERE sub2.`PlayerID` = {$PlayerID}
								");
							}
							if(@mysqli_num_rows($Score_q) == 1)
							{
								$Score_r = @mysqli_fetch_assoc($Score_q);
								$srank = $Score_r['rank'];
							}
							else
							{
								$srank = 0;
							}
						}
						// not in top 20
						// have to do slow query
						else
						{
							// rank players by score
							// if there is a ServerID, this is a server stats page
							if(!empty($ServerID))
							{
								$Score_q = @mysqli_query($BF4stats,"
									SELECT sub2.rank
									FROM
										(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
										 FROM
											(SELECT tpd.`PlayerID`
											FROM `tbl_playerdata` tpd
											INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
											INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
											INNER JOIN (SELECT @num := 0) x
											WHERE tpd.`GameID` = {$GameID}
											AND tsp.`ServerID` = {$ServerID}
											GROUP BY tpd.`PlayerID`
											ORDER BY SUM(tps.`Score`) DESC, tpd.`SoldierName` ASC
											) sub
										) sub2
									WHERE sub2.`PlayerID` = {$PlayerID}
								");
							}
							// or else this is a global stats page
							else
							{
								$Score_q = @mysqli_query($BF4stats,"
									SELECT sub2.rank
									FROM
										(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
										 FROM
											(SELECT tpd.`PlayerID`
											FROM `tbl_playerdata` tpd
											INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
											INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
											INNER JOIN (SELECT @num := 0) x
											WHERE tpd.`GameID` = {$GameID}
											AND tsp.`ServerID` IN ({$valid_ids})
											GROUP BY tpd.`PlayerID`
											ORDER BY SUM(tps.`Score`) DESC, tpd.`SoldierName` ASC
											) sub
										) sub2
									WHERE sub2.`PlayerID` = {$PlayerID}
								");
							}
							if(@mysqli_num_rows($Score_q) == 1)
							{
								$Score_r = @mysqli_fetch_assoc($Score_q);
								$srank = $Score_r['rank'];
							}
							else
							{
								$srank = 0;
							}
						}
						
						// update old data in database
						// if there is a ServerID, this is a server stats page
						if(!empty($ServerID))
						{
							@mysqli_query($BF4stats,"
								UPDATE `tyger_stats_rank_cache`
								SET `rank` = '{$srank}', `timestamp` = '{$now_timestamp}'
								WHERE `category` = 'Score'
								AND `SID` = '{$ServerID}'
								AND `GID` = '{$GameID}'
								AND `PlayerID` = {$PlayerID}
							");
							// free up rank query memory
							@mysqli_free_result($Score_q);
						}
						// or else this is a global stats page
						else
						{
							@mysqli_query($BF4stats,"
								UPDATE `tyger_stats_rank_cache`
								SET `rank` = '{$srank}', `timestamp` = '{$now_timestamp}'
								WHERE `category` = 'Score'
								AND `SID` = '{$valid_ids}'
								AND `GID` = '{$GameID}'
								AND `PlayerID` = {$PlayerID}
							");
							// free up rank query memory
							@mysqli_free_result($Score_q);
						}
					}
				}
				else
				{
					// check if this is a top 20 player
					// if so, we can get their score rank much faster
					// if there is a ServerID, this is a server stats page
					if(!empty($ServerID))
					{
						$Top_q = @mysqli_query($BF4stats,"
							SELECT `PlayerID`
							FROM `tyger_stats_top_twenty_cache`
							WHERE `SID` = '{$ServerID}'
							AND `GID` = '{$GameID}'
							AND `timestamp` >= '{$old}'
							AND `PlayerID` = {$PlayerID}
						");
					}
					// or else this is a global stats page
					else
					{
						$Top_q = @mysqli_query($BF4stats,"
							SELECT `PlayerID`
							FROM `tyger_stats_top_twenty_cache`
							WHERE `SID` = '{$valid_ids}'
							AND `GID` = '{$GameID}'
							AND `timestamp` >= '{$old}'
							AND `PlayerID` = {$PlayerID}
						");
					}
					if(@mysqli_num_rows($Top_q) != 0)
					{
						// rank players by score
						// if there is a ServerID, this is a server stats page
						if(!empty($ServerID))
						{
							$Score_q = @mysqli_query($BF4stats,"
								SELECT sub2.rank
								FROM
									(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
									 FROM
										(SELECT `PlayerID`
										FROM `tyger_stats_top_twenty_cache`
										INNER JOIN (SELECT @num := 0) x
										WHERE `SID` = '{$ServerID}'
										AND `GID` = '{$GameID}'
										AND `timestamp` >= '{$old}'
										GROUP BY `PlayerID`
										ORDER BY `Score` DESC, `SoldierName` ASC
										) sub
									) sub2
								WHERE sub2.`PlayerID` = {$PlayerID}
							");
						}
						// or else this is a global stats page
						else
						{
							$Score_q = @mysqli_query($BF4stats,"
								SELECT sub2.rank
								FROM
									(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
									 FROM
										(SELECT `PlayerID`
										FROM `tyger_stats_top_twenty_cache`
										INNER JOIN (SELECT @num := 0) x
										WHERE `SID` = '{$valid_ids}'
										AND `GID` = '{$GameID}'
										AND `timestamp` >= '{$old}'
										GROUP BY `PlayerID`
										ORDER BY `Score` DESC, `SoldierName` ASC
										) sub
									) sub2
								WHERE sub2.`PlayerID` = {$PlayerID}
							");
						}
						if(@mysqli_num_rows($Score_q) == 1)
						{
							$Score_r = @mysqli_fetch_assoc($Score_q);
							$srank = $Score_r['rank'];
						}
						else
						{
							$srank = 0;
						}
					}
					// not in top 20
					// have to do slow query
					else
					{
						// rank players by score
						// if there is a ServerID, this is a server stats page
						if(!empty($ServerID))
						{
							$Score_q = @mysqli_query($BF4stats,"
								SELECT sub2.rank
								FROM
									(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
									 FROM
										(SELECT tpd.`PlayerID`
										FROM `tbl_playerdata` tpd
										INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
										INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
										INNER JOIN (SELECT @num := 0) x
										WHERE tpd.`GameID` = {$GameID}
										AND tsp.`ServerID` = {$ServerID}
										GROUP BY tpd.`PlayerID`
										ORDER BY SUM(tps.`Score`) DESC, tpd.`SoldierName` ASC
										) sub
									) sub2
								WHERE sub2.`PlayerID` = {$PlayerID}
							");
						}
						// or else this is a global stats page
						else
						{
							$Score_q = @mysqli_query($BF4stats,"
								SELECT sub2.rank
								FROM
									(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
									 FROM
										(SELECT tpd.`PlayerID`
										FROM `tbl_playerdata` tpd
										INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
										INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
										INNER JOIN (SELECT @num := 0) x
										WHERE tpd.`GameID` = {$GameID}
										AND tsp.`ServerID` IN ({$valid_ids})
										GROUP BY tpd.`PlayerID`
										ORDER BY SUM(tps.`Score`) DESC, tpd.`SoldierName` ASC
										) sub
									) sub2
								WHERE sub2.`PlayerID` = {$PlayerID}
							");
						}
						if(@mysqli_num_rows($Score_q) == 1)
						{
							$Score_r = @mysqli_fetch_assoc($Score_q);
							$srank = $Score_r['rank'];
						}
						else
						{
							$srank = 0;
						}
					}
					
					// if there is a ServerID, this is a server stats page
					if(!empty($ServerID))
					{
						// add this data to the cache
						@mysqli_query($BF4stats,"
							INSERT INTO `tyger_stats_rank_cache`
							(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
							VALUES ('{$PlayerID}', '{$GameID}', '{$ServerID}', 'Score', '{$srank}', '{$now_timestamp}')
						");
					}
					// or else this is a global stats page
					else
					{
						// add this data to the cache
						@mysqli_query($BF4stats,"
							INSERT INTO `tyger_stats_rank_cache`
							(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
							VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'Score', '{$srank}', '{$now_timestamp}')
						");
					}
					// free up rank query memory
					@mysqli_free_result($Score_q);
				}
				// free up score rank cache query memory
				@mysqli_free_result($ScoreC_q);
				$count = $srank;
			}
			elseif($rank == 'Kills')
			{
				// rank players by kills
				// check if kills rank is already cached
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					$KillsC_q = @mysqli_query($BF4stats,"
						SELECT `rank`, `timestamp`
						FROM `tyger_stats_rank_cache`
						WHERE `PlayerID` = {$PlayerID}
						AND `category` = 'Kills'
						AND `GID` = '{$GameID}'
						AND `SID` = '{$ServerID}'
						GROUP BY `PlayerID`
					");
				}
				// or else this is a global stats page
				else
				{
					$KillsC_q = @mysqli_query($BF4stats,"
						SELECT `rank`, `timestamp`
						FROM `tyger_stats_rank_cache`
						WHERE `PlayerID` = {$PlayerID}
						AND `category` = 'Kills'
						AND `GID` = '{$GameID}'
						AND `SID` = '{$valid_ids}'
						GROUP BY `PlayerID`
					");
				}
				if(@mysqli_num_rows($KillsC_q) != 0)
				{
					$KillsC_r = @mysqli_fetch_assoc($KillsC_q);
					$killsrank = $KillsC_r['rank'];
					$timestamp = $KillsC_r['timestamp'];
					
					// data older than 12 hours? or incorrect data? recalculate
					if(($timestamp <= $old) OR ($killsrank == 0))
					{
						// rank players by kills
						// if there is a ServerID, this is a server stats page
						if(!empty($ServerID))
						{
							$Kills_q = @mysqli_query($BF4stats,"
								SELECT sub2.rank
								FROM
									(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
									 FROM
										(SELECT tpd.`PlayerID`
										FROM `tbl_playerdata` tpd
										INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
										INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
										INNER JOIN (SELECT @num := 0) x
										WHERE tpd.`GameID` = {$GameID}
										AND tsp.`ServerID` = {$ServerID}
										GROUP BY tpd.`PlayerID`
										ORDER BY tps.`Kills` DESC, tpd.`SoldierName` ASC
										) sub
									) sub2
								WHERE sub2.`PlayerID` = {$PlayerID}
							");
						}
						// or else this is a global stats page
						else
						{
							$Kills_q = @mysqli_query($BF4stats,"
								SELECT sub2.rank
								FROM
									(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
									 FROM
										(SELECT tpd.`PlayerID`
										FROM `tbl_playerdata` tpd
										INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
										INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
										INNER JOIN (SELECT @num := 0) x
										WHERE tpd.`GameID` = {$GameID}
										AND tsp.`ServerID` IN ({$valid_ids})
										GROUP BY tpd.`PlayerID`
										ORDER BY SUM(tps.`Kills`) DESC, tpd.`SoldierName` ASC
										) sub
									) sub2
								WHERE sub2.`PlayerID` = {$PlayerID}
							");
						}
						if(@mysqli_num_rows($Kills_q) == 1)
						{
							$Kills_r = @mysqli_fetch_assoc($Kills_q);
							$killsrank = $Kills_r['rank'];
						}
						else
						{
							$killsrank = 0;
						}
						
						// update old data in database
						// if there is a ServerID, this is a server stats page
						if(!empty($ServerID))
						{
							@mysqli_query($BF4stats,"
								UPDATE `tyger_stats_rank_cache`
								SET `rank` = '{$killsrank}', `timestamp` = '{$now_timestamp}'
								WHERE `category` = 'Kills'
								AND `SID` = '{$ServerID}'
								AND `GID` = '{$GameID}'
								AND `PlayerID` = {$PlayerID}
							");
						}
						// or else this is a global stats page
						else
						{
							@mysqli_query($BF4stats,"
								UPDATE `tyger_stats_rank_cache`
								SET `rank` = '{$killsrank}', `timestamp` = '{$now_timestamp}'
								WHERE `category` = 'Kills'
								AND `SID` = '{$valid_ids}'
								AND `GID` = '{$GameID}'
								AND `PlayerID` = {$PlayerID}
							");
						}
						// free up rank query memory
						@mysqli_free_result($Kills_q);
					}
				}
				else
				{
					// rank players by kills
					// if there is a ServerID, this is a server stats page
					if(!empty($ServerID))
					{
						$Kills_q = @mysqli_query($BF4stats,"
							SELECT sub2.rank
							FROM
								(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
								 FROM
									(SELECT tpd.`PlayerID`
									FROM `tbl_playerdata` tpd
									INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
									INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
									INNER JOIN (SELECT @num := 0) x
									WHERE tpd.`GameID` = {$GameID}
									AND tsp.`ServerID` = {$ServerID}
									GROUP BY tpd.`PlayerID`
									ORDER BY tps.`Kills` DESC, tpd.`SoldierName` ASC
									) sub
								) sub2
							WHERE sub2.`PlayerID` = {$PlayerID}
						");
					}
					// or else this is a global stats page
					else
					{
						$Kills_q = @mysqli_query($BF4stats,"
							SELECT sub2.rank
							FROM
								(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
								 FROM
									(SELECT tpd.`PlayerID`
									FROM `tbl_playerdata` tpd
									INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
									INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
									INNER JOIN (SELECT @num := 0) x
									WHERE tpd.`GameID` = {$GameID}
									AND tsp.`ServerID` IN ({$valid_ids})
									GROUP BY tpd.`PlayerID`
									ORDER BY SUM(tps.`Kills`) DESC, tpd.`SoldierName` ASC
									) sub
								) sub2
							WHERE sub2.`PlayerID` = {$PlayerID}
						");
					}
					if(@mysqli_num_rows($Kills_q) == 1)
					{
						$Kills_r = @mysqli_fetch_assoc($Kills_q);
						$killsrank = $Kills_r['rank'];
					}
					else
					{
						$killsrank = 0;
					}
					// add this data to the cache
					// if there is a ServerID, this is a server stats page
					if(!empty($ServerID))
					{
						@mysqli_query($BF4stats,"
							INSERT INTO `tyger_stats_rank_cache`
							(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
							VALUES ('{$PlayerID}', '{$GameID}', '{$ServerID}', 'Kills', '{$killsrank}', '{$now_timestamp}')
						");
					}
					// or else this is a global stats page
					else
					{
						@mysqli_query($BF4stats,"
							INSERT INTO `tyger_stats_rank_cache`
							(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
							VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'Kills', '{$killsrank}', '{$now_timestamp}')
						");
					}
					// free up rank query memory
					@mysqli_free_result($Kills_q);
				}
				// free up score rank cache query memory
				@mysqli_free_result($KillsC_q);
				$count = $killsrank;
			}
			elseif($rank == 'KDR')
			{
				// rank players by KDR
				// check if KDR rank is already cached
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					$KDRC_q = @mysqli_query($BF4stats,"
						SELECT `rank`, `timestamp`
						FROM `tyger_stats_rank_cache`
						WHERE `PlayerID` = {$PlayerID}
						AND `category` = 'KDR'
						AND `GID` = '{$GameID}'
						AND `SID` = '{$ServerID}'
						GROUP BY `PlayerID`
					");
				}
				// or else this is a global stats page
				else
				{
					$KDRC_q = @mysqli_query($BF4stats,"
						SELECT `rank`, `timestamp`
						FROM `tyger_stats_rank_cache`
						WHERE `PlayerID` = {$PlayerID}
						AND `category` = 'KDR'
						AND `GID` = '{$GameID}'
						AND `SID` = '{$valid_ids}'
						GROUP BY `PlayerID`
					");
				}
				if(@mysqli_num_rows($KDRC_q) != 0)
				{
					$KDRC_r = @mysqli_fetch_assoc($KDRC_q);
					$kdrrank = $KDRC_r['rank'];
					$timestamp = $KDRC_r['timestamp'];
					
					// data older than 12 hours? or incorrect data? recalculate
					if(($timestamp <= $old) OR ($kdrrank == 0))
					{
						// rank players by kdr
						// if there is a ServerID, this is a server stats page
						if(!empty($ServerID))
						{
							$KDR_q = @mysqli_query($BF4stats,"
								SELECT sub2.rank
								FROM
									(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
									 FROM
										(SELECT tpd.`PlayerID`
										FROM `tbl_playerdata` tpd
										INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
										INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
										INNER JOIN (SELECT @num := 0) x
										WHERE tpd.`GameID` = {$GameID}
										AND tsp.`ServerID` = {$ServerID}
										GROUP BY tpd.`PlayerID`
										ORDER BY (tps.`Kills`/tps.`Deaths`) DESC, tpd.`SoldierName` ASC
										) sub
									) sub2
								WHERE sub2.`PlayerID` = {$PlayerID}
							");
						}
						// or else this is a global stats page
						else
						{
							$KDR_q = @mysqli_query($BF4stats,"
								SELECT sub2.rank
								FROM
									(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
									 FROM
										(SELECT tpd.`PlayerID`
										FROM `tbl_playerdata` tpd
										INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
										INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
										INNER JOIN (SELECT @num := 0) x
										WHERE tpd.`GameID` = {$GameID}
										AND tsp.`ServerID` IN ({$valid_ids})
										GROUP BY tpd.`PlayerID`
										ORDER BY (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) DESC, tpd.`SoldierName` ASC
										) sub
									) sub2
								WHERE sub2.`PlayerID` = {$PlayerID}
							");
						}
						if(@mysqli_num_rows($KDR_q) == 1)
						{
							$KDR_r = @mysqli_fetch_assoc($KDR_q);
							$kdrrank = $KDR_r['rank'];
						}
						else
						{
							$kdrrank = 0;
						}
						
						// update old data in database
						// if there is a ServerID, this is a server stats page
						if(!empty($ServerID))
						{
							@mysqli_query($BF4stats,"
								UPDATE `tyger_stats_rank_cache`
								SET `rank` = '{$kdrrank}', `timestamp` = '{$now_timestamp}'
								WHERE `category` = 'KDR'
								AND `SID` = '{$ServerID}'
								AND `GID` = '{$GameID}'
								AND `PlayerID` = {$PlayerID}
							");
						}
						// or else this is a global stats page
						else
						{
							@mysqli_query($BF4stats,"
								UPDATE `tyger_stats_rank_cache`
								SET `rank` = '{$kdrrank}', `timestamp` = '{$now_timestamp}'
								WHERE `category` = 'KDR'
								AND `SID` = '{$valid_ids}'
								AND `GID` = '{$GameID}'
								AND `PlayerID` = {$PlayerID}
							");
						}
						// free up rank query memory
						@mysqli_free_result($KDR_q);
					}
				}
				else
				{
					// rank players by kdr
					// if there is a ServerID, this is a server stats page
					if(!empty($ServerID))
					{
						$KDR_q = @mysqli_query($BF4stats,"
							SELECT sub2.rank
							FROM
								(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
								 FROM
									(SELECT tpd.`PlayerID`
									FROM `tbl_playerdata` tpd
									INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
									INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
									INNER JOIN (SELECT @num := 0) x
									WHERE tpd.`GameID` = {$GameID}
									AND tsp.`ServerID` = {$ServerID}
									GROUP BY tpd.`PlayerID`
									ORDER BY (tps.`Kills`/tps.`Deaths`) DESC, tpd.`SoldierName` ASC
									) sub
								) sub2
							WHERE sub2.`PlayerID` = {$PlayerID}
						");
					}
					// or else this is a global stats page
					else
					{
						$KDR_q = @mysqli_query($BF4stats,"
							SELECT sub2.rank
							FROM
								(SELECT (@num := @num + 1) AS rank, sub.`PlayerID`
								 FROM
									(SELECT tpd.`PlayerID`
									FROM `tbl_playerdata` tpd
									INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
									INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
									INNER JOIN (SELECT @num := 0) x
									WHERE tpd.`GameID` = {$GameID}
									AND tsp.`ServerID` IN ({$valid_ids})
									GROUP BY tpd.`PlayerID`
									ORDER BY (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) DESC, tpd.`SoldierName` ASC
									) sub
								) sub2
							WHERE sub2.`PlayerID` = {$PlayerID}
						");
					}
					if(@mysqli_num_rows($KDR_q) == 1)
					{
						$KDR_r = @mysqli_fetch_assoc($KDR_q);
						$kdrrank = $KDR_r['rank'];
					}
					else
					{
						$kdrrank = 0;
					}
					// add this data to the cache
					// if there is a ServerID, this is a server stats page
					if(!empty($ServerID))
					{
						@mysqli_query($BF4stats,"
							INSERT INTO `tyger_stats_rank_cache`
							(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
							VALUES ('{$PlayerID}', '{$GameID}', '{$ServerID}', 'KDR', '{$kdrrank}', '{$now_timestamp}')
						");
					}
					// or else this is a global stats page
					else
					{
						@mysqli_query($BF4stats,"
							INSERT INTO `tyger_stats_rank_cache`
							(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
							VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'KDR', '{$kdrrank}', '{$now_timestamp}')
						");
					}
					// free up rank query memory
					@mysqli_free_result($KDR_q);
				}
				// free up kdr rank cache query memory
				@mysqli_free_result($KDRC_q);
				$count = $kdrrank;
			}
			elseif($rank == 'HSR')
			{
				if(!empty($ServerID))
				{
					$rank_q = @mysqli_query($BF4stats,"
						SELECT sub2.rank
						FROM
							(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
							 FROM
								(SELECT tpd.`SoldierName`
								FROM `tbl_playerdata` tpd
								INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
								INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
								INNER JOIN (SELECT @num := 0) x
								WHERE tpd.`GameID` = {$GameID}
								AND tsp.`ServerID` = {$ServerID}
								GROUP BY tpd.`SoldierName`
								ORDER BY (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) DESC, tpd.`SoldierName` ASC
								) sub
							) sub2
						WHERE sub2.`SoldierName` = '{$SoldierName}'
					");
				}
				// or else this is a global stats page
				else
				{
					$rank_q = @mysqli_query($BF4stats,"
						SELECT sub2.rank
						FROM
							(SELECT (@num := @num + 1) AS rank, sub.`SoldierName`
							 FROM
								(SELECT tpd.`SoldierName`
								FROM `tbl_playerdata` tpd
								INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
								INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
								INNER JOIN (SELECT @num := 0) x
								WHERE tpd.`GameID` = {$GameID}
								AND tsp.`ServerID` IN ({$valid_ids})
								GROUP BY tpd.`SoldierName`
								ORDER BY (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) DESC, tpd.`SoldierName` ASC
								) sub
							) sub2
						WHERE sub2.`SoldierName` = '{$SoldierName}'
					");
				}
				if(@mysqli_num_rows($rank_q) == 1)
				{
					$rank_r = @mysqli_fetch_assoc($rank_q);
					$count = $rank_r['rank'];
				}
				else
				{
					$count = 0;
				}
			}
		}
		echo '
		<tr>
		<td width="5%" class="count"><span class="information">' . $count . '</span></td>
		';
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '<td width="19%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="19%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		echo '
		<td width="19%" class="tablecontents">' . $Score . '</td>
		<td width="19%" class="tablecontents">' . $Kills . '</td>
		<td width="19%" class="tablecontents">' . $KDR . '</td>
		<td width="19%" class="tablecontents">' . $HSR . '<span class="information"> %</span></td>
		</tr>	
		';
	}
	echo '
	</table>
	';
	if(empty($player))
	{
		// build the pagination links
		pagination_links($ServerID,$_SERVER['PHP_SELF'],$page,$currentpage,$totalpages,$rank,$order,'');
	}
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
// free up players query memory
@mysqli_free_result($Players_q);

echo '
</div>
</div>
';

?>
