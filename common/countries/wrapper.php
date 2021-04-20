<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// query for countries
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
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
// or else this is a combined stats page
else
{
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
if(!$CountryMap_q || @mysqli_num_rows($CountryMap_q) == 0)
{
	echo '
	<div class="subsection">
	<div class="headline">No country stats found for ';
	if(!empty($ServerID))
	{
		echo 'this server.';
	}
	else
	{
		echo 'these servers.';
	}
	echo '
	</div>
	</div>
	';
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
	<script type="text/javascript" src="http';
	// is this an HTTPS server?
	if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
	{
		echo 's';
	}
	echo '://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load(\'visualization\', \'1\', {packages: [\'geochart\']});
		function drawVisualization()
		{
			var data = new google.visualization.DataTable();
			data.addRows(20);
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
	';
	// show loading...
	echo '
	<div id="loading">
	<br/><br/>
	<center><img class="load" src="./common/images/loading.gif" alt="loading" /></center>
	<br/><br/>
	</div>
	';
	// then ajax load content
	echo '
	<div id="loaded" style="display: none;">
	<script type="text/javascript">
	$(\'#loaded\').load("./common/countries/countries.php?gid=' . $GameID;
	if(!empty($ServerID))
	{
		echo '&sid=' . $ServerID;
	}
	echo '&c=' . implode(',', $CountryCodes) . '");
	</script>
	</div>
	';
}
?>
