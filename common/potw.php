<?php
// server stats player of the week page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo '
<div class="middlecontent">
<table width="100%" border="0">
<tr>
<td>
<br/>
<center>
';
// if there is a ServerID, this is a server stats page
if(isset($ServerID) AND !is_null($ServerID))
{
	echo 'These are the top players in this server over the last week.';
}
// or else this is a global stats page
else
{
	echo 'These are the top players in these servers over the last week.';
}
echo '
</center>
<br/>
</td>
</tr>
</table>
</div>
<br/><br/>
<div class="middlecontent">
<table width="100%" border="0">
<tr>
';
// get current rank query details
if(isset($_GET['rank']) AND !empty($_GET['rank']))
{
	$rank = $_GET['rank'];
	// filter out SQL injection
	if($rank != 'Score' AND $rank != 'Kills' AND $rank != 'Deaths' AND $rank != 'KDR' AND $rank != 'Headshots' AND $rank != 'HSR')
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
// if there is a ServerID, this is a server stats page
if(isset($ServerID) AND !is_null($ServerID))
{
	// query players
	$Player_q = @mysqli_query($BF4stats,"
		SELECT tpd.PlayerID, tpd.SoldierName, SUM(tss.Score) AS Score, SUM(Kills) AS Kills, SUM(Deaths) AS Deaths, (SUM(Kills)/SUM(Deaths)) AS KDR, SUM(Headshots) AS Headshots, (SUM(Headshots)/SUM(Kills)) AS HSR
		FROM tbl_sessions tss
		INNER JOIN tbl_server_player tsp ON tss.StatsID = tsp.StatsID
		INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID
		WHERE ServerID = {$ServerID}
		AND tss.Starttime BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
		GROUP BY tsp.StatsID
		ORDER BY {$rank} {$order}, SoldierName {$nextorder}
		LIMIT 20
	");
}
// or else this is a global stats page
else
{
	// query players
	$Player_q = @mysqli_query($BF4stats,"
		SELECT tpd.PlayerID, tpd.SoldierName, SUM(tss.Score) AS Score, SUM(Kills) AS Kills, SUM(Deaths) AS Deaths, (SUM(Kills)/SUM(Deaths)) AS KDR, SUM(Headshots) AS Headshots, (SUM(Headshots)/SUM(Kills)) AS HSR
		FROM tbl_sessions tss
		INNER JOIN tbl_server_player tsp ON tss.StatsID = tsp.StatsID
		INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID
		WHERE tss.Starttime BETWEEN CURDATE() - INTERVAL 7 DAY AND CURDATE()
		GROUP BY tsp.StatsID
		ORDER BY {$rank} {$order}, SoldierName {$nextorder}
		LIMIT 20
	");
}
if(@mysqli_num_rows($Player_q) != 0)
{
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th class="headline"><b>Players of the Week</b></th>';
	}
	// or else this is a global stats page
	else
	{
		echo '<th class="headline"><b>Global Players of the Week</b></th>';
	}
	echo '
	</tr>
	<tr>
	<td>
	<div class="innercontent">
	<br/>
	<table width="98%" align="center" border="0">
	<tr>
	<th width="5%" style="text-align:left">#</th>
	<th width="17%" style="text-align:left;">Player</th>
	';
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;potw=1&amp;rank=Score&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalpotw=1&amp;rank=Score&amp;order=';
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
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;potw=1&amp;rank=Kills&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalpotw=1&amp;rank=Kills&amp;order=';
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
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;potw=1&amp;rank=Deaths&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalpotw=1&amp;rank=Deaths&amp;order=';
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
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;potw=1&amp;rank=KDR&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalpotw=1&amp;rank=KDR&amp;order=';
	}
	if($rank != 'KDR')
	{
		echo 'DESC"><span class="orderheader">Kill/Death Ratio</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Kill/Death Ratio</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;potw=1&amp;rank=Headshots&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalpotw=1&amp;rank=Headshots&amp;order=';
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
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;potw=1&amp;rank=HSR&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalpotw=1&amp;rank=HSR&amp;order=';
	}
	if($rank != 'HSR')
	{
		echo 'DESC"><span class="orderheader">Headshot Ratio</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Headshot Ratio</span></a></th>';
	}
	echo '</tr>';
	// initialize value
	$count = 0;
	while($Player_r = @mysqli_fetch_assoc($Player_q))
	{
		$count++;
		$Soldier_Name = $Player_r['SoldierName'];
		$Player_ID = $Player_r['PlayerID'];
		$Score = $Player_r['Score'];
		$Kills = $Player_r['Kills'];
		$Deaths = $Player_r['Deaths'];
		$KDR = round($Player_r['KDR'],2);
		$Headshots = $Player_r['Headshots'];
		$HSR = round(($Player_r['HSR']*100),2);
		echo '
		<tr>
		<td width="5%" class="tablecontents" style="text-align: left;"><font class="information">' . $count . ':</font></td>
		';
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="17%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $Player_ID . '&amp;search=1">' . $Soldier_Name . '</a></td>';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="17%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $Player_ID . '">' . $Soldier_Name . '</a></td>';
		}
		echo '
		<td width="13%" class="tablecontents" style="text-align: left;">' . $Score . '</td>
		<td width="13%" class="tablecontents" style="text-align: left;">' . $Kills . '</td>
		<td width="13%" class="tablecontents" style="text-align: left;">' . $Deaths . '</td>
		<td width="13%" class="tablecontents" style="text-align: left;">' . $KDR . '</td>
		<td width="13%" class="tablecontents" style="text-align: left;">' . $Headshots . '</td>
		<td width="13%" class="tablecontents" style="text-align: left;">' . $HSR . '<font class="information"> %</font></td>
		</tr>
		';
	}
	echo '</table><br/></div>';
}
else
{
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<td width="100%"><br/><center><font class="information">No session stats found for this server over the last week.</font></center><br/>';
	}
	// or else this is a global stats page
	else
	{
		echo '<td width="100%"><br/><center><font class="information">No session stats found for these servers over the last week.</font></center><br/>';
	}
}
// free up player stats query memory
@mysqli_free_result($Player_q);
echo '
</td>
</tr>
</table>
</div>
';
?>