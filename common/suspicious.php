<?php
// server stats suspicious page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo '
<div class="subsection">
<div class="headline">
Just because a player shows up as being suspicious does not necessarily mean that they are cheating.
</div>
</div>
<br/>
<div class="subsection">
<div class="headline">
The search algorithm uses an appropriate sample size before marking the player as suspicious.
</div>
</div>
<br/><br/>
';
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
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
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
	");
}
// or else this is a global stats page
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
	");
}
// no suspicious players found
if(@mysqli_num_rows($Suspicious_q) == 0)
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '
		<div class="subsection">
		<div class="headline">No suspicious players found in this server.</div>
		</div>
		';
	}
	// or else this is a global stats page
	else
	{
		echo '
		<div class="subsection">
		<div class="headline">No suspicious players found in these servers.</div>
		</div>
		';
	}
}
// found suspicious players
else
{
	echo '
	<table class="prettytable">
	<tr>
	<th width="5%" class="countheader">#</th>
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=suspicious&amp;r=SoldierName&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?p=suspicious&amp;r=SoldierName&amp;o=';
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
		echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=suspicious&amp;r=KDR&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?p=suspicious&amp;r=KDR&amp;o=';
	}
	if($rank != 'KDR')
	{
		echo 'DESC"><span class="orderheader">Kill / Death</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Kill / Death</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=suspicious&amp;r=HSR&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?p=suspicious&amp;r=HSR&amp;o=';
	}
	if($rank != 'HSR')
	{
		echo 'DESC"><span class="orderheader">Headshot / Kill</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Headshot / Kill</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=suspicious&amp;r=Rounds&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="20%"><a href="' . $_SERVER['PHP_SELF'] . '?p=suspicious&amp;r=Rounds&amp;o=';
	}
	if($rank != 'Rounds')
	{
		echo 'DESC"><span class="orderheader">Rounds Played</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Rounds Played</span></a></th>';
	}
	echo '</tr>';
	//initialize value
	$count = 0;
	while($Suspicious_r = @mysqli_fetch_assoc($Suspicious_q))
	{
		$SoldierName = $Suspicious_r['SoldierName'];
		$PlayerID = $Suspicious_r['PlayerID'];
		$KDR = round($Suspicious_r['KDR'], 2);
		$HSpercent = round(($Suspicious_r['HSR']*100), 2);
		$Rounds = $Suspicious_r['Rounds'];
		$count++;
		echo '
		<tr>
		<td width="5%" class="count"><span class="information">' . $count . '</span></td>
		';
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '<td width="20%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="20%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		echo '
		<td width="20%" class="tablecontents">' . $KDR . '</td>
		<td width="20%" class="tablecontents">' . $HSpercent . ' <span class="information">%</span></td>
		<td width="20%" class="tablecontents">' . $Rounds . '</td>
		</tr>
		';
	}
	echo '
	</table>
	';
}
// free up suspicious query memory
@mysqli_free_result($Suspicious_q);

?>
