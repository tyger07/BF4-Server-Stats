<?php
// server stats countries page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// query for countries for map image
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// query for countries
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
		LIMIT 0, 20
	");
}
// or else this is a global stats page
else
{
	// query for countries
	$CountryMap_q = @mysqli_query($BF4stats,"
		SELECT tpd.`CountryCode`, COUNT(tpd.`CountryCode`) AS PlayerCount
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tpd.`GameID` = {$GameID}
		AND tsp.`ServerID` IN ({$valid_ids})
		AND tpd.`CountryCode` != '--'
		AND tpd.`CountryCode` != ''
		GROUP BY tpd.`CountryCode`
		ORDER BY PlayerCount DESC, tps.`Score` DESC, tpd.`CountryCode` ASC
		LIMIT 0, 20
	");
}
// no country stats found
if(@mysqli_num_rows($CountryMap_q) == 0)
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '
		<div class="subsection">
		<div class="headline">No country stats found for this server.</div>
		</div>
		';
	}
	// or else this is a global stats page
	else
	{
		echo '
		<div class="subsection">
		<div class="headline">No country stats found for these servers.</div>
		</div>
		';
	}
}
// found country stats
else
{
	// initialize empty array
	$CountryCodes = array();
	echo '
	<table class="prettytable">
	<tr>
	<td class="tablecontents">
	';
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
				$CountryCodes[] = strtoupper($CountryMap_r['CountryCode']);
			}
			echo '
			var options = {
				colorAxis: {colors: [\'#333333\', \'#640000\']},
				backgroundColor: {fill: \'transparent\'},
				datalessRegionColor: \'transparent\',
				tooltip:  {textStyle: {color: \'#AA0000\'}},
				legend: {textStyle: {color: \'#888\', bold: \'false\', fontSize: 12, auraColor: \'none\'}}
			};
			var geomap = new google.visualization.GeoChart(
			document.getElementById(\'visualization\'));
			geomap.draw(data, options);
		}
		google.setOnLoadCallback(drawVisualization);
	</script>
	<br/>
	<center>
	<div id="visualization" class="embed"></div>
	</center>
	<br/>
	</td>
	</tr>
	</table>
	<br/>
	<div id="tabs" style="min-height: 785px;">
	<ul>
	<li><div class="subscript">1</div><a href="#tabs-1">' . $CountryCodes['0'] . '</a></li>
	';
	$count_tracker = 1;
	// step through the country codes for creating tabs
	foreach($CountryCodes AS $this_CountryCode)
	{
		// first one was already created; skip it
		if($this_CountryCode != $CountryCodes['0'])
		{
			// rename null to dash
			$code_Displayed = $this_CountryCode;
			if($code_Displayed == '')
			{
				$code_Displayed = '--';
			}
			$count_tracker++;
			if(!empty($ServerID))
			{
				echo '<li><div class="subscript">' . $count_tracker . '</div><a href="./common/country-tab.php?sid=' . $ServerID . '&amp;gid=' . $GameID . '&amp;c=' . $this_CountryCode . '">' . $code_Displayed . '</a></li>';
			}
			else
			{
				echo '<li><div class="subscript">' . $count_tracker . '</div><a href="./common/country-tab.php?gid=' . $GameID . '&amp;c=' . $this_CountryCode . '">' . $code_Displayed . '</a></li>';
			}
		}
	}
	echo '
	</ul>
	<div id="tabs-1">
	';
	// show the default tab 1 when user loads page
	// list out the country
	$CountryCode = $CountryCodes['0'];
	$CountryCodeL = strtolower($CountryCode);
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
			$country_img = './images/flags/' . $CountryCodeL . '.png';
		}
	}
	// this country is missing!
	else
	{
		$country_name = $CountryCode;
		$country_img = './images/flags/none.png';
	}
	// query for number of players from this country
	if(!empty($ServerID))
	{
		$CountryCount_q = mysqli_query($BF4stats,"
			SELECT COUNT(tpd.`CountryCode`) AS PlayerCount
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
			AND tpd.`CountryCode` = '{$CountryCodeL}'
			LIMIT 0, 1
		");
	}
	else
	{
		$CountryCount_q = mysqli_query($BF4stats,"
			SELECT COUNT(tpd.`CountryCode`) AS PlayerCount
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` IN ({$valid_ids})
			AND tpd.`CountryCode` = '{$CountryCodeL}'
			LIMIT 0, 1
		");
	}
	$CountryCount_r = @mysqli_fetch_assoc($CountryCount_q);
	$PlayerCount = $CountryCount_r['PlayerCount'];
	$country_count = 0;
	echo '
	<table>
	<tr>
	<th width="33%" style="padding-left: 10px;"><span class="information"><img src="' . $country_img . '" alt="' . $country_name . '"/> ' . $country_name . '</span></th>
	<th width="33%" style="padding-left: 10px;"><span class="information">Country Code: </span>' . $CountryCode . '</th>
	<th width="33%" style="padding-left: 10px;"><span class="information">Player Count: </span>' . $PlayerCount . '</th>
	</tr>
	</table>
	<table class="prettytable">
	<tr>
	<th width="5%" class="countheader">#</th>
	<th width="24%">Player</th>
	<th width="24%"><span class="orderedDESCheader">Score</span></th>
	<th width="24%">Kills</th>
	<th width="24%">Kill / Death</th>
	</tr>
	';
	// show top playes from this country
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		//query top 20 players in this country
		$CountryRank_q = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`CountryCode` = '{$CountryCodeL}'
			AND tpd.`GameID` = {$GameID}
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
	// or else this is a global stats page
	else
	{
		//query top 20 players in this country
		$CountryRank_q = @mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tpd.`CountryCode` = '{$CountryCodeL}'
			AND tpd.`GameID` = {$GameID}
			AND tsp.`ServerID` IN ({$valid_ids})
			GROUP BY tpd.`SoldierName`
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
	// no players found
	// this must be a random database error
	// showing blank
	if(@mysqli_num_rows($CountryRank_q) == 0)
	{
		echo '
		<tr>
		<td width="5%" class="tablecontents">&nbsp;</td>
		<td width="95%" class="tablecontents" colspan="4">No players found!</td>
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
			$Kills = $CountryRank_r['Kills'];
			$KDR = round($CountryRank_r['KDR'],2);
			echo '
			<tr>
			<td width="5%" class="count"><span class="information">' . $country_count . '</span></td>
			';
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				echo '<td width="24%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;sid=' . $ServerID . '&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
			}
			// or else this is a global stats page
			else
			{
				echo '<td width="24%" class="tablecontents"><a href="' . $_SERVER['PHP_SELF'] . '?p=player&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
			}
			echo '
			<td width="24%" class="tablecontents">' . $Score . '</td>
			<td width="24%" class="tablecontents">' . $Kills . '</td>
			<td width="24%" class="tablecontents">' . $KDR . '</td>
			</tr>
			';
		}
	}
	// free up country ranks query memory
	@mysqli_free_result($CountryRank_q);
	echo '
	</table>
	</div>
	</div>
	';
	// free up chart query memory
	@mysqli_free_result($CountryMap_q);
	// free up countries query memory
	@mysqli_free_result($Country_q);
}

?>
