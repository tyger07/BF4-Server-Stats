<?php
// server stats suspicious page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo '
<div class="middlecontent">
<table width="100%" border="0">
<tr><td  class="headline">
';
// if there is a ServerID, this is a server stats page
if(isset($ServerID) AND !is_null($ServerID))
{
	echo '<br/><center><b>Suspicious Players</b></center><br/>';
}
// or else this is a global stats page
else
{
	echo '<br/><center><b>Global Suspicious Players</b></center><br/>';
}
echo  '
</td></tr>
</table>
<table width="100%" border="0">
<tr><td>
<br/>
<center><b>Just because a player shows up on the list as being suspicious does not necessarily mean they are cheating.</b><br />
<font class="information">The search algorithm makes sure that there is an appropriate sample size used before marking the player as suspicious.</font></center>
<br/>
</td></tr>
</table>
</div>
<br/><br/>
<div  class="middlecontent">
<table width="100%" border="0">
<tr>
<th class="headline"><b>Suspicious Players</b></th>
</tr>
<tr>
<td>
';
// get current rank query details
if(isset($_GET['rank']) AND !empty($_GET['rank']))
{
	$rank = $_GET['rank'];
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
	// check for suspicious players
	$Suspicious_q = @mysqli_query($BF4stats,"
		SELECT tpd.SoldierName, tps.Kills, tps.Deaths, tps.Headshots, tps.Rounds, (tps.Kills/tps.Deaths) AS KDR, (tps.Headshots/tps.Kills) AS HSR, tpd.PlayerID
		FROM tbl_playerstats tps
		INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID
		INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID
		WHERE tsp.ServerID = {$ServerID}
		AND (((tps.Kills/tps.Deaths) > 5 AND (tps.Headshots/tps.Kills) > 0.70 AND tps.Kills > 30 AND tps.Rounds > 1) OR ((tps.Kills/tps.Deaths) > 10 AND tps.Kills > 50 AND tps.Rounds > 1))
		ORDER BY {$rank} {$order}, SoldierName {$nextorder}
	");
}
// or else this is a global stats page
else
{
	// check for suspicious players
	$Suspicious_q = @mysqli_query($BF4stats,"
		SELECT tpd.SoldierName, SUM(tps.Kills) AS Kills, SUM(tps.Deaths) AS Deaths, SUM(tps.Headshots) AS Headshots, SUM(tps.Rounds) AS Rounds, (SUM(tps.Kills)/SUM(tps.Deaths)) AS KDR, (SUM(tps.Headshots)/SUM(tps.Kills)) AS HSR, tpd.PlayerID
		FROM tbl_playerstats tps
		INNER JOIN tbl_server_player tsp ON tsp.StatsID = tps.StatsID
		INNER JOIN tbl_playerdata tpd ON tsp.PlayerID = tpd.PlayerID
		WHERE (((tps.Kills/tps.Deaths) > 5 AND (tps.Headshots/tps.Kills) > 0.70 AND tps.Kills > 30 AND tps.Rounds > 1) OR ((tps.Kills/tps.Deaths) > 10 AND tps.Kills > 50 AND tps.Rounds > 1))
		GROUP BY SoldierName
		ORDER BY {$rank} {$order}, SoldierName {$nextorder}
	");
}
// no suspicious players found
if(@mysqli_num_rows($Suspicious_q) == 0)
{
	echo '
	<div class="innercontent">
	<table width="98%" align="center" border="0">
	<tr><td>
	';
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<br/><center><font class="information">No suspicious players found in this server.</font></center>';
	}
	// or else this is a global stats page
	else
	{
		echo '<br/><center><font class="information">No suspicious players found in these servers.</font></center>';
	}
	echo '
	</td></tr>
	</table>
	<br/>
	</div>
	';
}
// found suspicious players
else
{
	echo '
	<div class="innercontent">
	<br/>
	<table width="98%" align="center" class="prettytable" border="0">
	<tr>
	<th width="5%" style="text-align:left">#</th>
	';
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;suspicious=1&amp;rank=SoldierName&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsuspicious=1&amp;rank=SoldierName&amp;order=';
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
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;suspicious=1&amp;rank=KDR&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsuspicious=1&amp;rank=KDR&amp;order=';
	}
	if($rank != 'KDR')
	{
		echo 'DESC"><span class="orderheader">Kill/Death Raio</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Kill/Death Raio</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;suspicious=1&amp;rank=HSR&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsuspicious=1&amp;rank=HSR&amp;order=';
	}
	if($rank != 'HSR')
	{
		echo 'DESC"><span class="orderheader">Headshot Ratio</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Headshot Ratio</span></a></th>';
	}
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;suspicious=1&amp;rank=Rounds&amp;order=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="18%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsuspicious=1&amp;rank=Rounds&amp;order=';
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
		$Kills = $Suspicious_r['Kills'];
		$Deaths = $Suspicious_r['Deaths'];
		$KDR = round($Suspicious_r['KDR'], 2);
		$Headshots = $Suspicious_r['Headshots'];
		$HSpercent = round(($Suspicious_r['HSR']*100), 2);
		$Rounds = $Suspicious_r['Rounds'];
		$count++;
		echo '
		<tr>
		<td width="5%" class="tablecontents" style="text-align: left;"><font class="information">' . $count . ':</font></td>
		';
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="25%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $PlayerID . '&amp;search=1">' . $SoldierName . '</a></td>';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="25%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		echo '
		<td width="20%" class="tablecontents" style="text-align: left;">' . $KDR . '</td>
		<td width="25%" class="tablecontents" style="text-align: left;">' . $HSpercent . ' <font class="information">%</font></td>
		<td width="25%" class="tablecontents" style="text-align: left;">' . $Rounds . '</td>
		</tr>
		';
	}
	echo '
	</table>
	<br/>
	</div>
	';
}
// free up suspicious query memory
@mysqli_free_result($Suspicious_q);
echo '
</td></tr>
</table>
</div>
';
?>