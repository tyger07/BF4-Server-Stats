<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../functions.php');
require_once('../case.php');
// default variable to null
$ServerID = null;
// get value
if(!empty($sid))
{
	$ServerID = $sid;
}
// jquery tabs
echo '
<script type="text/javascript">
$(function()
{
	$( "#tabs" ).tabs(
	{
		beforeLoad: function( event, ui )
		{
			ui.panel.html(
			"<br/><br/><center><img class=\"load\" src=\"./common/images/loading.gif\" alt=\"loading\" /></center><br/><br/>"
			);
			ui.jqXHR.error(function()
			{
				ui.panel.html(
				"<div class=\"subsection\" style=\"margin-top: 2px;\"><div class=\"headline\"><span class=\"information\" style=\"font-size: 14px;\">Error: could not load this tab!</span></div></div>" );
			});
		}
	});
});
</script>
';
// javascript transition wrapper between loading and loaded
echo '
<script type="text/javascript">
$(\'#loading\').hide(0);
$(\'#loaded\').fadeIn("slow");
</script>
';
// continue html output
// query server stats
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	
	$Server_q = @mysqli_query($BF4stats,"
		SELECT `CountPlayers`, `SumKills`, (`SumHeadshots`/`SumKills`) AS AvgHSR, (`SumKills`/`SumDeaths`) AS AvgKDR, `SumRounds`, `SumDeaths`, `AvgScore`, `AvgKills`, `AvgHeadshots`, `AvgDeaths`, `AvgSuicide`, `AvgTKs`
		FROM `tbl_server_stats`
		WHERE `ServerID` = {$ServerID}
	");
}
// or else this is a combined stats page
else
{
	$Server_q = @mysqli_query($BF4stats,"
		SELECT SUM(`SumKills`) AS SumKills, (SUM(`SumHeadshots`)/SUM(`SumKills`)) AS AvgHSR, (SUM(`SumKills`)/SUM(`SumDeaths`)) AS AvgKDR, SUM(`SumRounds`) AS SumRounds, SUM(`SumDeaths`) AS SumDeaths, AVG(`AvgScore`) AS AvgScore, AVG(`AvgKills`) AS AvgKills, AVG(`AvgHeadshots`) AS AvgHeadshots, AVG(`AvgDeaths`) AS AvgDeaths, AVG(`AvgSuicide`) AS AvgSuicide, AVG(`AvgTKs`) AS AvgTKs
		FROM `tbl_server_stats`
		WHERE `ServerID` IN ({$valid_ids})
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
		<li><a href="#tabs-1">Graph / Info</a></li>
		<li><a href="./common/server/server-tab.php?sid=' . $ServerID . '&amp;gid=' . $GameID . '">Banners</a></li>
		</ul>
		';
	}
	echo '
	<div id="tabs-1">
	<div class="subsection" style="margin-top: 2px;">
	';
	$Server_r = @mysqli_fetch_assoc($Server_q);
	// player count is only accurate this way on individual server page
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		$players = $Server_r['CountPlayers'];
	}
	else
	{
		echo '<div style="position: relative;">';
		$players = cache_total_players($ServerID, $valid_ids, $GameID, $BF4stats, $cr);
		echo '</div>';
	}
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
		// show players-by-date.png contents
		echo '<br/><center><img class="embed" src="./common/server/players-by-date.png?sid=' . $ServerID . '" alt="average players per day" title="average players per day" height="300" width="600" /></center><br/>';
	}
	// or else this is a global stats page
	else
	{
		// show players-by-date.png contents
		echo '<br/><center><img class="embed" src="./common/server/players-by-date.png" alt="average players per day" title="average players per day" height="300" width="600" /></center><br/>';
	}
	echo '
	</div>
	<div class="subsection" style="margin-top: 2px;">
	<br/>
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
	<br/>
	</div>
	</div>
	</div>
	';
}
else
{
	echo '
	<div class="subsection">
	<div class="headline">No server stats found for ';
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
?>