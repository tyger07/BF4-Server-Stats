<?php
// country tab asynchronous for server stats page by Ty_ger07 at http://open-web-community.com/

// include required files
require_once('../config/config.php');
require_once('../common/functions.php');
require_once('../common/connect.php');
require_once('../common/case.php');
require_once('../common/constants.php');

// default variable to null
$ServerID = null;
$GameID = null;
$Code = null;

// get query strings
if(!empty($sid))
{
	$ServerID = $sid;
}
if(!empty($gid))
{
	$GameID = $gid;
}
if(!empty($_GET['c']))
{
	$Code = mysqli_real_escape_string($BF4stats, $_GET['c']);
}

// list out the country
$CountryCode = $Code;
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
<th width="3%" class="countheader">#</th>
<th width="22%">Player</th>
<th width="15%"><span class="orderedDESCheader">Score</span></th>
<th width="15%">Rounds</th>
<th width="15%">Kills</th>
<th width="15%">Deaths</th>
<th width="15%">KDR</th>
</tr>
';
// show top playes from this country
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	//query top 20 players in this country
	$CountryRank_q = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Kills`, tps.`Deaths`, tps.`Rounds`, (tps.`Kills`/tps.`Deaths`) AS KDR
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tsp.`ServerID` = {$ServerID}
		AND tpd.`CountryCode` = '{$CountryCodeL}'
		AND tpd.`GameID` = {$GameID}
		ORDER BY Score DESC, Rounds DESC, tpd.`SoldierName` ASC
		LIMIT 0, 20
	");
}
// or else this is a global stats page
else
{
	//query top 20 players in this country
	$CountryRank_q = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, SUM(tps.`Rounds`) AS Rounds, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tpd.`CountryCode` = '{$CountryCodeL}'
		AND tpd.`GameID` = {$GameID}
		GROUP BY tpd.`SoldierName`
		ORDER BY Score DESC, Rounds DESC, tpd.`SoldierName` ASC
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
	<td width="3%" class="tablecontents">&nbsp;</td>
	<td width="97%" class="tablecontents" colspan="6">No players found!</td>
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
		<td width="3%" class="count"><span class="information">' . $country_count . '</span></td>
		';
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '<td width="22%" class="tablecontents"><a href="./index.php?p=player&amp;sid=' . $ServerID . '&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="22%" class="tablecontents"><a href="./index.php?p=player&amp;pid=' . $PlayerID . '">' . $SoldierName . '</a></td>';
		}
		echo '
		<td width="15%" class="tablecontents">' . $Score . '</td>
		<td width="15%" class="tablecontents">' . $Rounds . '</td>
		<td width="15%" class="tablecontents">' . $Kills . '</td>
		<td width="15%" class="tablecontents">' . $Deaths . '</td>
		<td width="15%" class="tablecontents">' . $KDR . '</td>
		</tr>
		';
	}
}
// free up country ranks query memory
@mysqli_free_result($CountryRank_q);
echo '
</table>
';
	
?>
