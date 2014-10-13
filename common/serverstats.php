<?php
// server stats server info page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// query server stats
	$Server_q = @mysqli_query($BF4stats,"
		SELECT `CountPlayers`, `SumKills`, (`SumHeadshots`/`SumKills`) AS AvgHSR, (`SumKills`/`SumDeaths`) AS AvgKDR, `SumRounds`, `SumDeaths`, `AvgScore`, `AvgKills`, `AvgHeadshots`, `AvgDeaths`, `AvgSuicide`, `AvgTKs`
		FROM `tbl_server_stats`
		WHERE `ServerID` = {$ServerID}
	");
}
// or else this is a global stats page
else
{
	// query server stats
	$Server_q = @mysqli_query($BF4stats,"
		SELECT SUM(`CountPlayers`) AS CountPlayers, SUM(`SumKills`) AS SumKills, (SUM(`SumHeadshots`)/SUM(`SumKills`)) AS AvgHSR, (SUM(`SumKills`)/SUM(`SumDeaths`)) AS AvgKDR, SUM(`SumRounds`) AS SumRounds, SUM(`SumDeaths`) AS SumDeaths, AVG(`AvgScore`) AS AvgScore, AVG(`AvgKills`) AS AvgKills, AVG(`AvgHeadshots`) AS AvgHeadshots, AVG(`AvgDeaths`) AS AvgDeaths, AVG(`AvgSuicide`) AS AvgSuicide, AVG(`AvgTKs`) AS AvgTKs
		FROM `tbl_server_stats`
		WHERE 1
	");
}
if(@mysqli_num_rows($Server_q) != 0)
{
	echo '
	<div id="tabs">
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '
		<ul>
		<li><div class="subscript">1</div><a href="#tabs-1">Graphs / Info</a></li>
		<li><div class="subscript">2</div><a href="./common/server-tab.php?sid=' . $ServerID . '&amp;gid=' . $GameID . '">Banner</a></li>
		</ul>
		';
	}
	echo '
	<div id="tabs-1">
	<table class="prettytable">
	<tr>
	<td class="tablecontents">
	';
	$Server_r = @mysqli_fetch_assoc($Server_q);
	$players = round($Server_r['CountPlayers'],2);
	$kills = round($Server_r['SumKills'],2);
	$deaths = round($Server_r['SumDeaths'],2);
	$avgscore = round($Server_r['AvgScore'],2);
	$avgkills = round($Server_r['AvgKills'],2);
	$avgheadshots = round($Server_r['AvgHeadshots'],2);
	$avgdeaths = round($Server_r['AvgDeaths'],2);
	$avgsuicide = round($Server_r['AvgSuicide'],2);
	$avgtks = round($Server_r['AvgTKs'],2);
	$avghsr = round($Server_r['AvgHSR'],2);
	$avgkdr = round($Server_r['AvgKDR'],2);
	$rounds = $Server_r['SumRounds'];
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// include playersbydate.php contents
		echo '<br/><center><img class="embed" src="pchart/playersbydate.php?sid=' . $ServerID . '" alt="average players per day" title="average players per day" height="300" width="600" /></center><br/>';
		// include players.php contents
		echo '<center><img class="embed" src="pchart/players.php?sid=' . $ServerID . '" alt="minimum, maximum and average players" title="minimum, maximum and average players" height="300" width="600" /></center><br/>';
		// include joinsleaves.php contents
		echo '<center><img class="embed" src="pchart/joinsleaves.php?sid=' . $ServerID . '" alt="joins and leaves from server" title="joins and leaves from server" height="300" width="600" /></center><br/>';
	}
	// or else this is a global stats page
	else
	{
		// include playersbydate.php contents
		echo '<br/><center><img class="embed" src="pchart/playersbydate.php" alt="average players per day" title="average players per day" height="300" width="600" /></center><br/>';
		// include players.php contents
		echo '<center><img class="embed" src="pchart/players.php" alt="minimum, maximum and average players" title="minimum, maximum and average players" height="300" width="600" /></center><br/>';
		// include joinsleaves.php contents
		echo '<center><img class="embed" src="pchart/joinsleaves.php" alt="joins and leaves from server" title="joins and leaves from server" height="300" width="600" /></center><br/>';
	}
	echo '
	</td>
	</tr>
	</table>
	<table class="prettytable">
	<tr>
	<td class="tablecontents">
	<table width="90%" align="center" border="0"><tr>
	<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Total Players: </span>' . $players . '<br/><br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Total Kills: </span>' . $kills . '<br/><br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Total Deaths: </span>' . $deaths . '<br/><br/></td>
	</tr><tr>
	<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Total Rounds: </span>' . $rounds . '<br/><br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Average Team Kills: </span>' . $avgtks . '<br/><br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Average Suicides: </span>' . $avgsuicide . '<br/><br/></td>
	</tr><tr>
	<td style="text-align: left;" width="10%">&nbsp;<br/><br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Average Score: </span>' . $avgscore . '<br/><br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Average Kills: </span>' . $avgkills . '<br/><br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Average Deaths: </span>' . $avgdeaths . '<br/><br/></td>
	</tr><tr>
	<td style="text-align: left;" width="10%">&nbsp;<br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Average Headshots: </span>' . $avgheadshots . '<br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Average Kill/Death Ratio: </span>' . $avgkdr . '<br/></td>
	<td style="text-align: left;" width="30%"><span class="information">Average Headshot Ratio: </span>' . $avghsr . '<br/></td>
	</tr></table>
	</td>
	</tr>
	</table>
	</div>
	</div>
	';
}
else
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '
		<div class="subsection">
		<div class="headline">No server stats found for this server.</div>
		</div>
		';
	}
	// or else this is a global stats page
	else
	{
		echo '
		<div class="subsection">
		<div class="headline">No server stats found for these servers.</div>
		</div>
		';
	}
}
// free up server stats query memory
@mysqli_free_result($Server_q);

?>
