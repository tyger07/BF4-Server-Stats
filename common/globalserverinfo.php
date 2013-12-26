<?php
// server stats global server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// query for server totals
$ServerTotals_q = @mysqli_query($BF4stats,"
	SELECT SUM(`CountPlayers`) AS total_players, SUM(`SumRounds`) AS total_rounds, SUM(`SumPlaytime`) AS total_playtime, SUM(`SumKills`) AS total_kills, SUM(`SumDeaths`) AS total_deaths, SUM(`SumHeadshots`) AS total_headshots
	FROM `tbl_server_stats`
	WHERE 1
");
if(@mysqli_num_rows($ServerTotals_q) != 0)
{
	$ServerTotals_r = @mysqli_fetch_assoc($ServerTotals_q);
	$total_players = $ServerTotals_r['total_players'];
	$total_rounds = $ServerTotals_r['total_rounds'];
	$total_days = round(($ServerTotals_r['total_playtime']/60/60/24),0);
	$total_kills = $ServerTotals_r['total_kills'];
	$total_deaths = $ServerTotals_r['total_deaths'];
	$total_headshots = $ServerTotals_r['total_headshots'];
	echo '
	<br/>
	<br/>
	<div class="middlecontent">
	<br/><center>Or view global stats from all of ' . $clan_name . '\'s servers:</center>
	<br/>
	<div class="shadowcontent">
	<table width="95%" align="center" border="0">
	<tr>
	<td width="35%">
	<br/><font size="3">' . $clan_name . '\'s Global Server Stats</font><br/><br/>
	</td>
	<td width="22%">
	<br/><br/><font class="information">Total Players:</font> ' . $total_players . '<br/><br/>
	</td>
	<td width="22%">
	<br/><br/><font class="information">Total Rounds:</font> ' . $total_rounds . '<br/><br/>
	</td>
	<td width="21%">
	<br/><br/><font class="information">Total Days Played:</font> ' . $total_days . '<br/><br/>
	</td>
	</tr>
	<tr>
	<td width="35%">
	<a href="' . $_SERVER['PHP_SELF'] . '?globalhome=1"><img src="./images/viewstatsbtn.png" alt="view stats" class="imagebutton" /></a><br/><br/>
	</td>
	<td width="22%">
	<font class="information">Total Kills:</font> ' . $total_kills . '<br/><br/><br/>
	</td>
	<td width="22%">
	<font class="information">Total Deaths:</font> ' . $total_deaths . '<br/><br/><br/>
	</td>
	<td width="21%">
	<font class="information">Total Headshots:</font> ' . $total_headshots . '<br/><br/><br/>
	</td>
	</tr>
	</table>
	</div>
	<br/>
	</div>
	';
}
// something went wrong
// display blank data
else
{
	echo '
	<br/>
	<br/><div class="middlecontent"><br/><center>Or view global stats from all of ' . $clan_name . ' servers:</center><br/></div><br/>
	<br/>
	<div class="shadowcontent">
	<table width="95%" align="center" border="0">
	<tr>
	<td width="35%">
	<br/><font size="3">' . $clan_name . ' Global Server Stats</font><br/><br/>
	</td>
	<td width="22%">
	<br/><br/><font class="information">Total Players:</font> Unknown<br/><br/>
	</td>
	<td width="22%">
	<br/><br/><font class="information">Total Rounds:</font> Unknown<br/><br/>
	</td>
	<td width="21%">
	<br/><br/><font class="information">Total Days Played:</font> Unknown<br/><br/>
	</td>
	</tr>
	<tr>
	<td width="35%">
	<a href="' . $_SERVER['PHP_SELF'] . '?globalhome=1"><img src="./images/viewstatsbtn.png" alt="view stats" class="imagebutton" /></a><br/><br/>
	</td>
	<td width="22%">
	<font class="information">Total Kills:</font> Unknown<br/><br/><br/>
	</td>
	<td width="22%">
	<font class="information">Total Deaths:</font> Unknown<br/><br/><br/>
	</td>
	<td width="21%">
	<font class="information">Total Headshots:</font> Unknown<br/><br/><br/>
	</td>
	</tr>
	</table>
	</div>
	';
}
// free up server totals query memory
@mysqli_free_result($ServerTotals_q);
?>