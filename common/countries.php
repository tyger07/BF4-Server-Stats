<?php
// server stats countries page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo '
<div class="middlecontent">
<table width="100%" border="0">
<tr><td  class="headline">
';
// if there is a ServerID, this is a server stats page
if(isset($ServerID) AND !is_null($ServerID))
{
	echo '<br/><center><b>Country Stats</b></center><br/>';
}
// or else this is a global stats page
else
{
	echo '<br/><center><b>Global Country Stats</b></center><br/>';
}
echo '
</td></tr>
</table>
<table width="100%" border="0">
<tr><td>
<br/>
';
// if there is a ServerID, this is a server stats page
if(isset($ServerID) AND !is_null($ServerID))
{
	echo '<center>These are the countries players in this server reside in and the stats for the top 10 most common countries.</center>';
}
// or else this is a global stats page
else
{
	echo '<center>These are the countries players from these servers reside in and the stats for the top 10 most common countries.</center>';
}
echo '
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
<th class="headline"><b>Country Stats</b></th>
</tr>
<tr>
<td><br/>
<div class="innercontent">
<table width="100%" border="0">
<tr>
<td>
';
// if there is a ServerID, this is a server stats page
if(isset($ServerID) AND !is_null($ServerID))
{
	// query for countries
	$Country_q = mysqli_query($BF4stats,"
		SELECT tpd.`CountryCode`, COUNT(tpd.`CountryCode`) AS PlayerCount
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tsp.`ServerID` = {$ServerID}
		AND tpd.`GameID` = {$GameID}
		GROUP BY tpd.`CountryCode`
		ORDER BY PlayerCount DESC, tps.`Score` DESC, tpd.`CountryCode` ASC
		LIMIT 10
	");
	$CountryMap_q = @mysqli_query($BF4stats,"
		SELECT tpd.`CountryCode`, COUNT(tpd.`CountryCode`) AS PlayerCount
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tsp.`ServerID` = {$ServerID}
		AND tpd.`GameID` = {$GameID}
		AND tpd.`CountryCode` != '--'
		AND tpd.`CountryCode` != ''
		GROUP BY tpd.`CountryCode`
		ORDER BY PlayerCount DESC, tps.`Score` DESC, tpd.`CountryCode` ASC
		LIMIT 200
	");
}
// or else this is a global stats page
else
{
	// query for countries
	$Country_q = mysqli_query($BF4stats,"
		SELECT tpd.`CountryCode`, COUNT(tpd.`CountryCode`) AS PlayerCount
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tpd.`GameID` = {$GameID}
		GROUP BY tpd.`CountryCode`
		ORDER BY PlayerCount DESC, tps.`Score` DESC, tpd.`CountryCode` ASC
		LIMIT 10
	");
	$CountryMap_q = @mysqli_query($BF4stats,"
		SELECT tpd.`CountryCode`, COUNT(tpd.`CountryCode`) AS PlayerCount
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tpd.`GameID` = {$GameID}
		AND tpd.`CountryCode` != '--'
		AND tpd.`CountryCode` != ''
		GROUP BY tpd.`CountryCode`
		ORDER BY PlayerCount DESC, tps.`Score` DESC, tpd.`CountryCode` ASC
		LIMIT 200
	");
}
// no country stats found
if(@mysqli_num_rows($Country_q) == 0)
{
	echo '
	<div class="innercontent">
	<table width="95%" align="center" border="0">
	<tr><td>
	';
	// if there is a ServerID, this is a server stats page
	if(isset($ServerID) AND !is_null($ServerID))
	{
		echo'<center><font class="information">No country stats found for this server.</font></center>';
	}
	// or else this is a global stats page
	else
	{
		echo'<center><font class="information">No country stats found for these servers.</font></center>';
	}
	echo '
	</td></tr>
	</table>
	</div>
	';
}
// found country stats
else
{
	echo '<div class="innerinnercontent">';
	// initialize values
	$count = 0;
	$mapcount = 0;
	echo '
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load(\'visualization\', \'1\', {packages: [\'geochart\']});
		function drawVisualization()
		{
			var data = new google.visualization.DataTable();
			data.addRows(250);
			data.addColumn(\'string\', \'Country\');
			data.addColumn(\'number\', \'Playercount\');
			';
			while($CountryMap_r = @mysqli_fetch_array($CountryMap_q))
			{
				$CountryCodeMap = strtoupper($CountryMap_r['CountryCode']);
				$PlayerCountMap = $CountryMap_r['PlayerCount'];
				echo '
				data.setValue(' . $mapcount . ', 0, \'' . $CountryCodeMap . '\');
				data.setValue(' . $mapcount . ', 1, ' . $PlayerCountMap . ');
				';
				$mapcount++;	
			}
			echo '
			var options = {
				colorAxis: {colors: [\'#000064\', \'#640000\']},
				backgroundColor: {fill: \'transparent\'},
				datalessRegionColor: \'transparent\',
				tooltip:  {textStyle: {color: \'#AA0000\'}},
				legend: {textStyle: {color: \'#000\', fontSize: 12}}
			};
			var geomap = new google.visualization.GeoChart(
			document.getElementById(\'visualization\'));
			geomap.draw(data, options);
		}
		google.setOnLoadCallback(drawVisualization);
	</script>
	<center>
	<div id="visualization" style="width: 600px; height: 300px;"></div>
	</center>
	<br/>
	';
	// free up chart query memory
	@mysqli_free_result($CountryMap_q);
	// list out the countries
	while($Country_r = @mysqli_fetch_assoc($Country_q))
	{
		$CountryCode = strtoupper($Country_r['CountryCode']);
		// first find out if this country name is the list of country names
		if(in_array($CountryCode,$country_array))
		{
			$country_name = array_search($CountryCode,$country_array);
			// compile country flag image
			// if country is null or unknown, use generic image
			if(($CountryCode == '') OR ($CountryCode == '--'))
			{
				$country_img = './images/flags/none.png';
			}
			else
			{
				$country_img = './images/flags/' . strtolower($CountryCode) . '.png';	
			}
		}
		// this country is missing!
		else
		{
			$country_name = $CountryCode;
			$country_img = './images/flags/none.png';
		}
		$PlayerCount = $Country_r['PlayerCount'];
		$count++;
		echo '
		<div class="innercontent">
		<table width="98%" align="center" border="0">
		<tr>
		<th width="5%" style="text-align: left;">' . $count . '</th>
		<th width="20%" style="text-align: left;"><font class="information"><img src="' . $country_img . '" alt="' . $country_name . '"/> ' . $country_name . '</font></th>
		<th width="15%" style="text-align: left;"><font class="information">Country Code: </font>' . $CountryCode . '</th>
		<th width="60%" style="text-align: left;"><font class="information">Player Count: </font>' . $PlayerCount . '</th>
		</tr>
		</table>
		<div class="innercontent">
		';
		// initialize value
		$country_count = 0;
		// get current rank query details
		if(isset($_GET['rank']) AND !empty($_GET['rank']))
		{
			$rank = $_GET['rank'];
			// filter out SQL injection
			if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Rounds' AND $rank != 'Kills' AND $rank != 'Deaths' AND $rank != 'KDR')
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
		echo '
		<table width="98%" align="center" border="0">
		<tr>
		<td width="1%" style="text-align:left">&nbsp;</td>
		<td width="3%" class="tablecontents" style="text-align:left">#</td>
		';
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="21%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;countries=1&amp;rank=SoldierName&amp;order=';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="21%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalcountries=1&amp;rank=SoldierName&amp;order=';
		}
		if($rank != 'SoldierName')
		{
			echo 'ASC"><span class="orderheader">Player</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Player</span></a></td>';
		}
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;countries=1&amp;rank=Score&amp;order=';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalcountries=1&amp;rank=Score&amp;order=';
		}
		if($rank != 'Score')
		{
			echo 'DESC"><span class="orderheader">Score</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Score</span></a></td>';
		}
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;countries=1&amp;rank=Rounds&amp;order=';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalcountries=1&amp;rank=Rounds&amp;order=';
		}
		if($rank != 'Rounds')
		{
			echo 'DESC"><span class="orderheader">Rounds Played</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Rounds Played</span></a></td>';
		}
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;countries=1&amp;rank=Kills&amp;order=';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalcountries=1&amp;rank=Kills&amp;order=';
		}
		if($rank != 'Kills')
		{
			echo 'DESC"><span class="orderheader">Kills</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Kills</span></a></td>';
		}
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;countries=1&amp;rank=Deaths&amp;order=';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalcountries=1&amp;rank=Deaths&amp;order=';
		}
		if($rank != 'Deaths')
		{
			echo 'DESC"><span class="orderheader">Deaths</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Deaths</span></a></td>';
		}
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;countries=1&amp;rank=KDR&amp;order=';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="15%" class="tablecontents" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalcountries=1&amp;rank=KDR&amp;order=';
		}
		if($rank != 'KDR')
		{
			echo 'DESC"><span class="orderheader">Kill/Death Ratio</span></a></td>';
		}
		else
		{
			echo $nextorder . '"><span class="ordered' . $order . 'header">Kill/Death Ratio</span></a></td>';
		}
		echo '</tr>';
		// if there is a ServerID, this is a server stats page
		if(isset($ServerID) AND !is_null($ServerID))
		{
			//query top 10 players in this country
			$CountryRank_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, tps.`Deaths`, tps.`Rounds`, (tps.`Kills`/tps.`Deaths`) AS KDR
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`CountryCode` = '{$CountryCode}'
				AND tpd.`GameID` = {$GameID}
				ORDER BY {$rank} {$order}
				LIMIT 10
			");
		}
		// or else this is a global stats page
		else
		{
			//query top 10 players in this country
			$CountryRank_q = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, SUM(tps.`Rounds`) AS Rounds, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`CountryCode` = '{$CountryCode}'
				AND tpd.`GameID` = {$GameID}
				GROUP BY tpd.`SoldierName`
				ORDER BY {$rank} {$order}
				LIMIT 10
			");
		}
		// no players found
		// this must be a random database error
		// showing blank
		if(@mysqli_num_rows($CountryRank_q) == 0)
		{
			echo '
			<tr>
			<td width="1%" style="text-align: left;">&nbsp;</td>
			<td width="99%" class="tablecontents" style="text-align: left;" colspan="7">No players found!</td>
			</tr>
			';
		}
		// players found
		else
		{
			while($CountryRank_r = @mysqli_fetch_assoc($CountryRank_q))
			{
				$country_count++;
				$SoldierName = $CountryRank_r['SoldierName'];
				$PlayerID = $CountryRank_r['PlayerID'];
				$Score = $CountryRank_r['Score'];
				$Rounds = $CountryRank_r['Rounds'];
				$Kills = $CountryRank_r['Kills'];
				$Deaths = $CountryRank_r['Deaths'];
				$KDR = round($CountryRank_r['KDR'],2);
				echo '
				<tr>
				<td width="1%" style="text-align: left;">&nbsp;</td>
				<td width="3%" class="tablecontents" style="text-align: left;"><font class="information">' . $country_count . ':</font></td>
				';
				// if there is a ServerID, this is a server stats page
				if(isset($ServerID) AND !is_null($ServerID))
				{
					echo '<td width="21%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;PlayerID=' . $PlayerID . '&amp;search=1">' . $SoldierName . '</a></td>';
				}
				// or else this is a global stats page
				else
				{
					echo '<td width="21%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?globalsearch=1&amp;PlayerID=' . $PlayerID . '">' . $SoldierName . '</a></td>';
				}
				echo '
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Score . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Rounds . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Kills . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $Deaths . '</td>
				<td width="15%" class="tablecontents" style="text-align: left;">' . $KDR . '</td>
				</tr>
				';
			}
			// free up country ranks query memory
			@mysqli_free_result($CountryRank_q);
		}
		echo '
		</table>
		</div>
		</div>
		';
	}
	// free up countries query memory
	@mysqli_free_result($Country_q);
	echo '</div>';
}
echo '
</td></tr>
</table>
</div>
</td></tr>
</table>
<br/>
</div>
</td></tr>
</table>
';
?>