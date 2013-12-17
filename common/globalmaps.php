<?php
// server stats maps page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo '
<div class="middlecontent">
<table width="100%" border="0">
<tr><td  class="headline">
<br/><center><b>Global Map Stats</b></center><br/>
</td></tr>
</table>
<table width="100%" border="0">
<tr><td>
<br/>
<center>Average Polularity is calculated as average players in the server for each map divided by average players leaving for each map.  Higher is better.</center>
<br/>
</td></tr>
</table>
</div>
<br/><br/>
<table width="100%" border="0">
<tr>
<td valign="top" align="center">
<div class="middlecontent">
<table width="100%" border="0">
<tr>
<th class="headline"><b>Map Stats</b></th>
</tr>
<tr>
<td>
';
// get current rank query details
if(isset($_GET['rank']) AND !empty($_GET['rank']))
{
	$rank = $_GET['rank'];
	// filter out SQL injection
	if($rank != 'Gamemode')
	{
		// unexpected input detected
		// use default instead
		$rank = 'Gamemode';
	}
}
// set default if no rank provided in URL
else
{
	$rank = 'Gamemode';
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
		$order = 'ASC';
		$nextorder = 'DESC';
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
	$order = 'ASC';
	$nextorder = 'DESC';
}
// query for maps in this server
$Mode_q = @mysqli_query($BF4stats,"
	SELECT Gamemode
	FROM tbl_mapstats
	WHERE Gamemode != ''
	GROUP BY Gamemode
	ORDER BY {$rank} {$order}
");
if(@mysqli_num_rows($Mode_q) == 0)
{
	echo '
	<div class="innercontent"><br/>
	<table width="98%" align="center" border="0">
	<tr><td>
	<center><font class="information">No map stats found for found for this server.</font></center>
	</td></tr>
	</table><br/>
	</div>
	';
}
else
{
	echo '<div class="innercontent">';
	// include playersbydate.php contents
	echo '<br/><center><img src="pchart/playersbydate.php" alt="Average players per day" title="Average players per day" height="300" width="600" /></center>
	<table width="98%" align="center" border="0">
	<tr>
	<th width="5%" style="text-align:left">#</th>
	<th width="16%" style="text-align:left">Map Name</th>
	<th width="16%" style="text-align:left;">Map Code</th>
	<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;maps=1&amp;rank=Gamemode&amp;order=';
	if($rank != 'Gamemode')
	{
		echo 'ASC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Game Mode</span></a></th>
	<th width="15%" style="text-align:left;">Rounds Played</th>
	<th width="16%" style="text-align:left;">Average Players</th>
	<th width="16%" style="text-align:left;">Average Popularity</th>
	</tr>';
	// initialize value
	$count = 0;
	$match = 0;
	$last_mode = 0;
	while($Mode_r = @mysqli_fetch_assoc($Mode_q))
	{
		$Mode = $Mode_r['Gamemode'];
		// query for game modes for each map
		$Map_q = @mysqli_query($BF4stats,"
			SELECT MapName, SUM(NumberofRounds) AS NumberofRounds, AVG(AvgPlayers) AS AveragePlayers, (AVG(AvgPlayers)/AVG(PlayersLeftServer)) AS AVGPop
			FROM tbl_mapstats
			WHERE Gamemode = '{$Mode}'
			AND MapName != ''
			GROUP BY MapName
			ORDER BY NumberofRounds DESC
		");
		if(@mysqli_num_rows($Map_q) != 0)
		{
			$match = 1;
			while($Map_r = @mysqli_fetch_assoc($Map_q))
			{
				$NumberofRounds = $Map_r['NumberofRounds'];
				$MapCode = $Map_r['MapName'];
				// convert map to friendly name
				// first find if this map name is even in the map array
				if(in_array($MapCode,$map_array))
				{
					$MapName = array_search($MapCode,$map_array);
				}
				// this map is missing!
				else
				{
					$MapName = $MapCode;
				}
				// convert mode to friendly name
				if(in_array($Mode,$mode_array))
				{
					$GameMode = array_search($Mode,$mode_array);
				}
				// this mode is missing!
				else
				{
					$GameMode = $Mode;
				}
				$AveragePlayers = round($Map_r['AveragePlayers'],2);
				// don't show average popularity if sample size is small
				if($NumberofRounds <= 4)
				{
					$AveragePopularity = '<font class="information">not enough data</font>';
				}
				else
				{
					$AveragePopularity = round($Map_r['AVGPop'],2);
				}
				$count++;
				// add a space between mode changes
				if($last_mode !== $Mode AND $last_mode !== 0)
				{
					echo '<tr><td width="100%" class="tablecontents" style="text-align: left;" colspan="7">&nbsp;</td></tr>';
					$last_mode = $Mode;
				}
				echo '
				<tr>
				<td width="5%" class="tablecontents" style="text-align: left;"><font class="information">' . $count . ':</font></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><font class="information">' . $MapName . '</font></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><font class="information">' . $MapCode . '</font></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><font class="information">' . $GameMode . '</font></td>
				<td width="15%" class="tablecontents" style="text-align: left;"><font class="information">' . $NumberofRounds . '</font></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><font class="information">' . $AveragePlayers . '</font></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><font class="information">' . $AveragePopularity . '</font></td>
				</tr>
				';
			}
		}
		$last_mode = $Mode;
	}
	// free up map query memory
	@mysqli_free_result($Map_q);
	if($match == 0)
	{
		echo '
		<tr>
		<td width="100%" class="tablecontents" style="text-align: left;" colspan="7"><font class="information">No information found.</font></td>
		</tr>
		';
	}
	echo '
	</table>
	<br/>
	</div>
	';
}
// free up mode query memory
@mysqli_free_result($Mode_q);
echo '
</td></tr>
</table>
</div>
</td></tr>
</table>
';
?>