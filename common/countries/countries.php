<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../functions.php');
require_once('../case.php');
require_once('../constants.php');
// default variable to null
$ServerID = null;
// get value
if(!empty($sid))
{
	$ServerID = $sid;
}
if(!empty($_GET['c']))
{
	$CountryCodes = explode(',',mysqli_real_escape_string($BF4stats, $_GET['c']));
}
// jquery tabs
echo '
<script type="text/javascript">
$(function()
{
	$( "#tabs, #dogtag_tab" ).tabs(
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
echo '
<br/>
<div id="tabs" style="min-height: 785px;">
<ul>
<li><div class="subscript">1</div><a href="#tabs-1">' . $CountryCodes['0'] . '</a></li>
';
$count_tracker = 1;
// step through the country codes for creating tabs
foreach($CountryCodes AS $this_CountryCode)
{
	// first tab was already created; skip it
	if($this_CountryCode != $CountryCodes['0'])
	{
		// rename null to dash
		$code_Displayed = $this_CountryCode;
		if($code_Displayed == '')
		{
			$code_Displayed = '--';
		}
		$count_tracker++;
		echo '
		<li><div class="subscript">' . $count_tracker . '</div><a href="./common/countries/country-tab.php?';
		if(!empty($ServerID))
		{
			echo 'sid=' . $ServerID . '&amp;';
		}
		echo 'gid=' . $GameID . '&amp;c=' . $this_CountryCode . '">' . $code_Displayed . '</a></li>
		';
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
		$country_img = './common/images/flags/none.png';
	}
	else
	{
		$country_img = './common/images/flags/' . $CountryCodeL . '.png';
	}
}
// this country is missing!
else
{
	$country_name = $CountryCode;
	$country_img = './common/images/flags/none.png';
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
<th width="33%" style="padding-left: 10px;"><span class="information"><img src="' . $country_img . '" style="height: 11px; width: 16px;" alt="' . $country_name . '"/> ' . $country_name . '</span></th>
<th width="33%" style="padding-left: 10px;"><span class="information">Country Code: </span>' . $CountryCode . '</th>
<th width="33%" style="padding-left: 10px;"><span class="information">Player Count: </span>' . $PlayerCount . '</th>
</tr>
</table>
<table class="prettytable" style="margin-top: -2px;">
<tr>
<th width="5%" class="countheader">#</th>
<th width="24%">Player</th>
<th width="24%"><span class="orderedDESCheader">Score</span></th>
<th width="24%">Kills</th>
<th width="24%">Kill / Death</th>
</tr>
';
//query top 20 players in this country
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
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
// or else this is a combined stats page
else
{
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
	</table>
	';
}
// players found
else
{
	echo '
	</table>
	';
	while($CountryRank_r = @mysqli_fetch_assoc($CountryRank_q))
	{
		$country_count++;
		$SoldierName = $CountryRank_r['SoldierName'];
		$PlayerID = $CountryRank_r['PlayerID'];
		$Score = $CountryRank_r['Score'];
		$Kills = $CountryRank_r['Kills'];
		$KDR = round($CountryRank_r['KDR'],2);
		$link = './index.php?';
		if(!empty($ServerID))
		{
			$link .= 'sid=' . $ServerID . '&amp;';
		}
		$link .= 'pid=' . $PlayerID . '&amp;p=player';
		echo '
		<table class="prettytable" style="margin-top: -2px; position: relative;">
		<tr>
		<td width="5%" class="count">
			<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
				<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
			</div>
			<span class="information">' . $country_count . '</span>
		</td>
		<td width="24%" class="tablecontents"><a href="' . $link . '">' . $SoldierName . '</a></td>
		<td width="24%" class="tablecontents">' . $Score . '</td>
		<td width="24%" class="tablecontents">' . $Kills . '</td>
		<td width="24%" class="tablecontents">' . $KDR . '</td>
		</tr>
		</table>
		';
	}
}
echo '
</div>
</div>
';
?>