<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../functions.php');
require_once('../case.php');
require_once('../constants.php');
// default variable to null
$ServerID = null;
$Code = null;
// get query strings
if(!empty($sid))
{
	$ServerID = $sid;
}
if(!empty($_GET['c']) && !(is_numeric($_GET['c'])))
{
	$Code = mysqli_real_escape_string($BF4stats, strip_tags($_GET['c']));
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
	// is adkats information available?
	if($adkats_available)
	{
		$CountryCount_q = mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Playtime`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, adk.`ban_status`, sub.`PlayerCount`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
			LEFT JOIN
			(
				SELECT COUNT(tpd.`CountryCode`) AS PlayerCount, tpd.`CountryCode`
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` = {$ServerID}
				AND tpd.`CountryCode` = '{$CountryCodeL}'
				LIMIT 0, 1
			) sub ON sub.`CountryCode` = tpd.`CountryCode`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`CountryCode` = '{$CountryCodeL}'
			AND tpd.`GameID` = {$GameID}
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
	else
	{
		$CountryCount_q = mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Playtime`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, sub.`PlayerCount`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			LEFT JOIN
			(
				SELECT COUNT(tpd.`CountryCode`) AS PlayerCount, tpd.`CountryCode`
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` = {$ServerID}
				AND tpd.`CountryCode` = '{$CountryCodeL}'
				LIMIT 0, 1
			) sub ON sub.`CountryCode` = tpd.`CountryCode`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`CountryCode` = '{$CountryCodeL}'
			AND tpd.`GameID` = {$GameID}
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
}
else
{
	// is adkats information available?
	if($adkats_available)
	{
		$CountryCount_q = mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, adk.`ban_status`, sub.`PlayerCount`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
			LEFT JOIN
			(
				SELECT COUNT(tpd.`CountryCode`) AS PlayerCount, tpd.`CountryCode`
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				AND tpd.`CountryCode` = '{$CountryCodeL}'
				LIMIT 0, 1
			) sub ON sub.`CountryCode` = tpd.`CountryCode`
			WHERE tsp.`ServerID` IN ({$valid_ids})
			AND tpd.`CountryCode` = '{$CountryCodeL}'
			AND tpd.`GameID` = {$GameID}
			GROUP BY tpd.`PlayerID`
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
	else
	{
		$CountryCount_q = mysqli_query($BF4stats,"
			SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, sub.`PlayerCount`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			LEFT JOIN
			(
				SELECT COUNT(tpd.`CountryCode`) AS PlayerCount, tpd.`CountryCode`
				FROM `tbl_playerstats` tps
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				AND tpd.`CountryCode` = '{$CountryCodeL}'
				LIMIT 0, 1
			) sub ON sub.`CountryCode` = tpd.`CountryCode`
			WHERE tsp.`ServerID` IN ({$valid_ids})
			AND tpd.`CountryCode` = '{$CountryCodeL}'
			AND tpd.`GameID` = {$GameID}
			GROUP BY tpd.`PlayerID`
			ORDER BY Score DESC, tpd.`SoldierName` ASC
			LIMIT 0, 20
		");
	}
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
<th width="19%">Player</th>
<th width="19%"><span class="orderedDESCheader">Score</span></th>
<th width="19%">Playtime</th>
<th width="19%">Kills</th>
<th width="19%">Kill / Death</th>
</tr>
';
// set the pointer back to the beginning of the query result array
@mysqli_data_seek($CountryCount_q, 0);
// no players found
// this must be a random database error
// showing blank
if(!$CountryCount_q || @mysqli_num_rows($CountryCount_q) == 0)
{
	echo '
	<tr>
	<td width="5%" class="tablecontents">&nbsp;</td>
	<td width="95%" class="tablecontents" colspan="5">No players found!</td>
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
	while($CountryRank_r = @mysqli_fetch_assoc($CountryCount_q))
	{
		$country_count++;
		$SoldierName = htmlspecialchars(strip_tags($CountryRank_r['SoldierName']));
		$PlayerID = $CountryRank_r['PlayerID'];
		$Score = $CountryRank_r['Score'];
		$Playtime = $CountryRank_r['Playtime'];
		$Playhours = floor($Playtime / 3600);
		$Playminutes = floor(($Playtime / 60) % 60);
		$Playseconds = $Playtime % 60;
		$Playtime = $Playhours . ':' . $Playminutes . ':' . $Playseconds;
		$Kills = $CountryRank_r['Kills'];
		$KDR = round($CountryRank_r['KDR'],2);
		$link = './index.php?';
		if(!empty($ServerID))
		{
			$link .= 'sid=' . $ServerID . '&amp;';
		}
		$link .= 'pid=' . $PlayerID . '&amp;p=player';
		// is this player banned?
		// or have previous ban which was lifted?
		$player_banned = 0;
		$previous_banned = 0;
		if($adkats_available)
		{
			$ban_status = $CountryRank_r['ban_status'];
			if(!is_null($ban_status))
			{
				if($ban_status == 'Active')
				{
					$player_banned = 1;
				}
				elseif($ban_status == 'Expired')
				{
					$previous_banned = 1;
				}
			}
		}
		echo '
		<table class="prettytable" style="margin-top: -2px; position: relative;">
		<tr>
		<td width="5%" class="count">
			<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
				<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
			</div>
			<span class="information">' . $country_count . '</span>
		</td>
		';
		if($player_banned == 1)
		{
			echo '<td width="19%" class="banoutline"><div class="bansubscript">Banned</div>';
		}
		elseif($previous_banned == 1)
		{
			echo '<td width="19%" class="warnoutline"><div class="bansubscript">Warned</div>';
		}
		else
		{
			echo '<td width="19%" class="tablecontents">';
		}
		echo '
		<a href="' . $link . '">' . $SoldierName . '</a></td>
		<td width="19%" class="tablecontents">' . $Score . '</td>
		<td width="19%" class="tablecontents">' . $Playtime . '</td>
		<td width="19%" class="tablecontents">' . $Kills . '</td>
		<td width="19%" class="tablecontents">' . $KDR . '</td>
		</tr>
		</table>
		';
	}
}
?>