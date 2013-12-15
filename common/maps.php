<?php
// server stats maps page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo '
<div class="middlecontent">
<table width="100%" border="0">
<tr><td  class="headline">
<br/><center><b>Map Stats</b></center><br/>
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
	if($rank != 'MapCode')
	{
		// unexpected input detected
		// use default instead
		$rankin = 'MapName';
	}
	else
	{
		$rankin = 'MapName';
	}
}
// set default if no rank provided in URL
else
{
	$rank = 'MapCode';
	$rankin = 'MapName';
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
$Map_q = @mysqli_query($BF4stats,"
	SELECT MapName
	FROM tbl_mapstats
	WHERE ServerID = {$ServerID}
	GROUP BY MapName
	ORDER BY {$rankin} {$order}
");
if(@mysqli_num_rows($Map_q) == 0)
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
	$Chart_q = @mysqli_query($BF4stats,"
		SELECT SUBSTRING(TimeMapLoad, 1, length(TimeMapLoad) - 9) AS Date, AVG(MaxPlayers) AS Average
		FROM tbl_mapstats
		WHERE ServerID = {$ServerID}
		GROUP BY Date
		ORDER BY Date DESC LIMIT 7
	");
	if(@mysqli_num_rows($Chart_q) != 0)
	{
		echo'
		<br/>
		<script type="text/javascript" src="//www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load(\'visualization\', \'1\', {packages: [\'imagelinechart\']});
		</script>
		<script type="text/javascript">
			function drawVisualization() {
				// Create and populate the data table.
				var data = google.visualization.arrayToDataTable([
					[\'Date\', \'Average Players\']
					';
					while($Chart_r = @mysqli_fetch_assoc($Chart_q))
					{
						$Date = date("M d", strtotime($Chart_r['Date']));
						$Average = $Chart_r['Average'];
						echo ',
						[\'' . $Date . '\', ' . $Average . ']
						';
					}
					echo '
				]);
				// Create and draw the visualization.
				new google.visualization.ImageLineChart(document.getElementById(\'visualization\')).
				draw(data, {width: 600, height: 300, backgroundColor: \'#00000000\', legend: \'right\', colors: \'#000000\'});
			}
			google.setOnLoadCallback(drawVisualization);
		</script>
		<div id="visualization" style="width: 600px; height: 300px;"></div>
		<br/>
		';
	}
	// free up chart query memory
	@mysqli_free_result($Chart_q);
	echo '
	<table width="98%" align="center" border="0">
	<tr>
	<th width="5%" style="text-align:left">#</th>
	<th width="19%" style="text-align:left">Map Name</th>
	<th width="19%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;maps=1&amp;rank=MapCode&amp;order=';
	if($rank != 'MapCode')
	{
		echo 'ASC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Map Code</span></a></th>
	<th width="19%" style="text-align:left;">Game Mode</th>
	<th width="19%" style="text-align:left;">Rounds Played</th>
	<th width="19%" style="text-align:left;">Average Popularity</th>
	</tr>';
	// initialize value
	$count = 0;
	$match = 0;
	while($Map_r = @mysqli_fetch_assoc($Map_q))
	{
		$MapCode = $Map_r['MapName'];
		// query for game modes for each map
		$GameMode_q = @mysqli_query($BF4stats,"
			SELECT SUM(NumberofRounds) AS NumberofRounds, AVG(AvgPlayers) AS AveragePlayers, AVG(PlayersLeftServer) AS AveragePlayersLeftServer, (AVG(AvgPlayers)/AVG(PlayersLeftServer)) AS AVGPop, Gamemode
			FROM tbl_mapstats
			WHERE ServerID = {$ServerID}
			AND MapName = '{$MapCode}'
			GROUP BY Gamemode
			ORDER BY Gamemode {$order}
		");
		if(@mysqli_num_rows($GameMode_q) != 0)
		{
			$match = 1;
			while($GameMode_r = @mysqli_fetch_assoc($GameMode_q))
			{
				$NumberofRounds = $GameMode_r['NumberofRounds'];
				$MapName = array_search($MapCode,$map_array);
				$GameMode = array_search($GameMode_r['Gamemode'],$mode_array);
				$AveragePlayers = $GameMode_r['AveragePlayers'];
				$AveragePlayersLeftServer = $GameMode_r['AveragePlayersLeftServer'];
				$AveragePopularity = round($GameMode_r['AVGPop'],2);
				// if there is data...
				if($MapName!=null)
				{
					$count++;
					echo '
					<tr>
					<td width="5%" class="tablecontents" style="text-align: left;"><font class="information">' . $count . ':</font></td>
					<td width="19%" class="tablecontents" style="text-align: left;">' . $MapName . '</td>
					<td width="19%" class="tablecontents" style="text-align: left;">' . $MapCode . '</td>
					<td width="19%" class="tablecontents" style="text-align: left;">' . $GameMode . '</td>
					<td width="19%" class="tablecontents" style="text-align: left;">' . $NumberofRounds . '</td>
					<td width="19%" class="tablecontents" style="text-align: left;">' . $AveragePopularity . '</td>
					</tr>
					';
				}
			}
		}
		// free up game mode query memory
		@mysqli_free_result($GameMode_q);
	}
	if($match == 0)
	{
		echo '
		<tr>
		<td width="100%" class="tablecontents" style="text-align: left;" colspan="6"><font class="information">No information found.</font></td>
		</tr>
		';
	}
	echo '
	</table>
	<br/>
	</div>
	';
}
// free up map query memory
@mysqli_free_result($Map_q);
echo '
</td></tr>
</table>
</div>
</td></tr>
</table>
';
?>