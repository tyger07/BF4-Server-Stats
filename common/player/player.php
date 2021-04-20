<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// expand / contract javascript
echo '
<script type="text/javascript">
$(document).ready(function()
{
	$(".expanded").hide();
	$(".collapsed, .expanded").click(function()
	{
		$(this).parent().children(".expanded, .collapsed").toggle();
	});
});
</script>
<script type="text/javascript">
$(document).ready(function()
{
	$(".expanded3").hide();
	$(".collapsed3, .expanded3").click(function()
	{
		$(this).parent().children(".expanded3, .collapsed3").toggle();
	});
});
</script>
';
// jquery tabs
echo '
<script type="text/javascript">
$(function()
{
	$("#tabs, #dogtag_tab").tabs(
	{
		beforeLoad: function( event, ui )
		{
			ui.panel.html(
			"<br/><br/><center><img class=\"load\" src=\"./common/images/loading.gif\" alt=\"loading\" /></center><br/><br/>"
			);
			ui.jqXHR.error(function()
			{
				ui.panel.html(
				"<div class=\"subsection\" style=\"margin-top: 2px;\"><div class=\"headline\"><span class=\"information\" style=\"font-size: 14px;\">Error: This page is not available.</span></div></div>" );
			});
		}
	});
});
</script>
';
// fade in
echo '
<div id="loaded" style="display: none;">
<script type="text/javascript">
$(\'#loaded\').fadeIn("slow");
</script>
';
// no soldiername input from GET
if($SoldierName == null)
{
	echo '
	<div class="subsection">
	<div class="headline">
	';
	// if there was a $playerid input but it was nulled out, let user know it was nulled out because index.php did not find that as a valid player id
	if(!empty($pid) && is_numeric($pid))
	{
		echo 'This player ID does not exist.';
	}
	else
	{
		echo 'Please enter a player name.';
	}
	echo '
	</div>
	</div>
	';
}
// SoldierName from GET has been determined
elseif($SoldierName != null)
{
	// initialize value
	$soldier_found = 0;
	// $playerid is found automatically for this input in index.php
	// if there is no $playerid, no need to do $playerid specific soldier info searches
	if(!empty($PlayerID))
	{
		// get player stats
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			// is adkats information available?
			if($adkats_available)
			{
				$PlayerData_q = @mysqli_query($BF4stats,"
					SELECT tpd.`CountryCode`, tpd.`PlayerID`, tpd.`GlobalRank`, tps.`Suicide`, tps.`Score`, tps.`Playtime`, tps.`Kills`, tps.`Deaths`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR, tps.`TKs`, tps.`Headshots`, tps.`Rounds`, tps.`Killstreak`, tps.`Deathstreak`, tps.`Wins`, tps.`Losses`, (tps.`Wins`/tps.`Losses`) AS WLR, tps.`HighScore`, tps.`FirstSeenOnServer`, tps.`LastSeenOnServer`, adk.`ban_status`, abr.`record_message`
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
					LEFT JOIN `adkats_records_main` abr ON abr.`record_id` = adk.`latest_record_id`
					WHERE tsp.`ServerID` = {$ServerID}
					AND tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
				");
			}
			else
			{
				$PlayerData_q = @mysqli_query($BF4stats,"
					SELECT tpd.`CountryCode`, tpd.`PlayerID`, tpd.`GlobalRank`, tps.`Suicide`, tps.`Score`, tps.`Playtime`, tps.`Kills`, tps.`Deaths`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR, tps.`TKs`, tps.`Headshots`, tps.`Rounds`, tps.`Killstreak`, tps.`Deathstreak`, tps.`Wins`, tps.`Losses`, (tps.`Wins`/tps.`Losses`) AS WLR, tps.`HighScore`, tps.`FirstSeenOnServer`, tps.`LastSeenOnServer`
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE tsp.`ServerID` = {$ServerID}
					AND tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
				");
			}
		}
		// or else this is a combined stats page
		else
		{
			// is adkats information available?
			if($adkats_available)
			{
				$PlayerData_q = @mysqli_query($BF4stats,"
					SELECT tpd.`CountryCode`, tpd.`PlayerID`, tpd.`GlobalRank`, SUM(tps.`Suicide`) AS Suicide, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, SUM(tps.`TKs`) AS TKs, SUM(tps.`Headshots`) AS Headshots, SUM(tps.`Rounds`) AS Rounds, MAX(tps.`Killstreak`) AS Killstreak, MAX(tps.`Deathstreak`) AS Deathstreak, SUM(tps.`Wins`) AS Wins, SUM(tps.`Losses`) AS Losses, (SUM(tps.`Wins`)/SUM(tps.`Losses`)) AS WLR, MAX(tps.`HighScore`) AS HighScore, MIN(tps.`FirstSeenOnServer`) AS FirstSeenOnServer, MAX(tps.`LastSeenOnServer`) AS LastSeenOnServer, adk.`ban_status`, abr.`record_message`
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
					LEFT JOIN `adkats_records_main` abr ON abr.`record_id` = adk.`latest_record_id`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
					GROUP BY tpd.`PlayerID`
				");
			}
			else
			{
				$PlayerData_q = @mysqli_query($BF4stats,"
					SELECT tpd.`CountryCode`, tpd.`PlayerID`, tpd.`GlobalRank`, SUM(tps.`Suicide`) AS Suicide, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, SUM(tps.`Deaths`) AS Deaths, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, SUM(tps.`TKs`) AS TKs, SUM(tps.`Headshots`) AS Headshots, SUM(tps.`Rounds`) AS Rounds, MAX(tps.`Killstreak`) AS Killstreak, MAX(tps.`Deathstreak`) AS Deathstreak, SUM(tps.`Wins`) AS Wins, SUM(tps.`Losses`) AS Losses, (SUM(tps.`Wins`)/SUM(tps.`Losses`)) AS WLR, MAX(tps.`HighScore`) AS HighScore, MIN(tps.`FirstSeenOnServer`) AS FirstSeenOnServer, MAX(tps.`LastSeenOnServer`) AS LastSeenOnServer
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
					GROUP BY tpd.`PlayerID`
				");
			}
		}
		// was a soldier found?
		if(@mysqli_num_rows($PlayerData_q) == 1)
		{
			$soldier_found = 1;
		}
	}
	// if no stats were found for player name, display this
	if($soldier_found == 0)
	{
		echo '
		<div class="subsection">
		<div class="headline">
		<span class="information">No unique player data found for "' . $SoldierName . '" in ';
		if(!empty($ServerID))
		{
			echo 'this server.';
		}
		else
		{
			echo 'these servers.';
		}
		echo '
		</span>
		</div>
		</div>
		';
		// get current rank query details
		if(!empty($rank))
		{
			// filter out SQL injection
			if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Playtime' AND $rank != 'Kills' AND $rank != 'KDR')
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
		if(!empty($order))
		{
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
		// check to see if there are any players who match a similar name
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			// is adkats information available?
			if($adkats_available)
			{
				$PlayerMatch_q = @mysqli_query($BF4stats,"
					SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Playtime`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, adk.`ban_status`
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
					WHERE tsp.`ServerID` = {$ServerID}
					AND tpd.`SoldierName` LIKE '%{$SoldierName}%'
					AND tpd.`GameID` = {$GameID}
					ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
					LIMIT 0, 10
				");
			}
			else
			{
				$PlayerMatch_q = @mysqli_query($BF4stats,"
					SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Playtime`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE tsp.`ServerID` = {$ServerID}
					AND tpd.`SoldierName` LIKE '%{$SoldierName}%'
					AND tpd.`GameID` = {$GameID}
					ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
					LIMIT 0, 10
				");
			}
		}
		// or else this is a combined stats page
		else
		{
			// is adkats information available?
			if($adkats_available)
			{
				$PlayerMatch_q = @mysqli_query($BF4stats,"
					SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, adk.`ban_status`
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
					WHERE tpd.`SoldierName` LIKE '%{$SoldierName}%'
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
					GROUP BY tpd.`SoldierName`
					ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
					LIMIT 0, 10
				");
			}
			else
			{
				$PlayerMatch_q = @mysqli_query($BF4stats,"
					SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR
					FROM `tbl_playerstats` tps
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE tpd.`SoldierName` LIKE '%{$SoldierName}%'
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
					GROUP BY tpd.`SoldierName`
					ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
					LIMIT 0, 10
				");
			}
		}
		// if a similar name was found, display this
		if(@mysqli_num_rows($PlayerMatch_q) != 0)
		{
			echo '
			<div class="subsection" style="margin-top: 2px;">
			<div class="headline">
			Here are some players with names similar to "' . $SoldierName . '".
			</div>
			</div>
			<br/><br/>
			<table class="prettytable">
			<tr>
			<th width="5%" class="countheader">#</th>
			';
			// player column
			pagination_headers('Player',$ServerID,'player','19','r',$rank,'SoldierName','o',$order,'ASC',$nextorder,$currentpage,'',$SoldierName,'');
			// score column
			pagination_headers('Score',$ServerID,'player','19','r',$rank,'Score','o',$order,'DESC',$nextorder,$currentpage,'',$SoldierName,'');
			// playtime column
			pagination_headers('Playtime',$ServerID,'player','19','r',$rank,'Playtime','o',$order,'DESC',$nextorder,$currentpage,'',$SoldierName,'');
			// kills column
			pagination_headers('Kills',$ServerID,'player','19','r',$rank,'Kills','o',$order,'DESC',$nextorder,$currentpage,'',$SoldierName,'');
			// kill / death column
			pagination_headers('Kill / Death',$ServerID,'player','19','r',$rank,'KDR','o',$order,'DESC',$nextorder,$currentpage,'',$SoldierName,'');
			echo '
			</tr>
			</table>
			';
			// initialize value
			$count = 0;
			while($PlayerMatch_r = @mysqli_fetch_assoc($PlayerMatch_q))
			{
				$Soldier_Name = textcleaner($PlayerMatch_r['SoldierName']);
				$Player_ID = $PlayerMatch_r['PlayerID'];
				$Score = $PlayerMatch_r['Score'];
				$Playtime = $PlayerMatch_r['Playtime'];
				$Playhours = floor($Playtime / 3600);
				$Playminutes = floor(($Playtime / 60) % 60);
				$Playseconds = $Playtime % 60;
				$Playtime = $Playhours . ':' . $Playminutes . ':' . $Playseconds;
				$Kills = $PlayerMatch_r['Kills'];
				$KDR = round($PlayerMatch_r['KDR'],2);
				// do the fast count if player name search isn't being done
				// or do fast count if this is a bot
				if(empty($player) || $isbot)
				{
					$count++;
				}
				else
				{
					// include leader-ranks.php contents
					include('./common/leaders/leader-ranks.php');
				}
				$link = './index.php?';
				if(!empty($ServerID))
				{
					$link .= 'sid=' . $ServerID . '&amp;';
				}
				$link .= 'pid=' . $Player_ID . '&amp;p=player';
				// is this player banned?
				// or have previous ban which was lifted?
				$player_banned = 0;
				$previous_banned = 0;
				if($adkats_available)
				{
					$ban_status = $PlayerMatch_r['ban_status'];
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
							<span class="information">' . $count . '</span>
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
						<a href="' . $link . '">' . $Soldier_Name . '</a></td>
						<td width="19%" class="tablecontents">' . $Score . '</td>
						<td width="19%" class="tablecontents">' . $Playtime . '</td>
						<td width="19%" class="tablecontents">' . $Kills . '</td>
						<td width="19%" class="tablecontents">' . $KDR . '</td>
					</tr>
				</table>
				';
			}
			echo '<br/>';
		}
	}
	// this unique player was found
	elseif($soldier_found == 1)
	{
		// get information from the query
		$PlayerData_r = @mysqli_fetch_assoc($PlayerData_q);
		$CountryCode = strtoupper($PlayerData_r['CountryCode']);
		// convert country name to friendly name
		// and compile flag image
		// first find out if this country name is the list of country names
		if(in_array($CountryCode,$country_array))
		{
			$country = array_search($CountryCode,$country_array);
			// compile country flag image
			// if country is null or unknown, use generic image
			if(($CountryCode == '') OR ($CountryCode == '--'))
			{
				$country_img = './common/images/flags/none.png';
			}
			else
			{
				$country_img = './common/images/flags/' . strtolower($CountryCode) . '.png';	
			}
		}
		// this country is missing!
		else
		{
			$country = $CountryCode;
			$country_img = './common/images/flags/none.png';
		}
		// continue getting information from the query
		$Suicides = $PlayerData_r['Suicide'];
		$Score = $PlayerData_r['Score'];
		$Playtime = $PlayerData_r['Playtime'];
		$Playhours = floor($Playtime / 3600);
		$Playminutes = floor(($Playtime / 60) % 60);
		$Playseconds = $Playtime % 60;
		$Playtime = $Playhours . ':' . $Playminutes . ':' . $Playseconds;
		$Kills = $PlayerData_r['Kills'];
		$Deaths = $PlayerData_r['Deaths'];
		$Headshots = $PlayerData_r['Headshots'];
		$HSpercent = round(($PlayerData_r['HSR']*100),2);
		$Rounds = $PlayerData_r['Rounds'];
		$Killstreak = $PlayerData_r['Killstreak'];
		$Deathstreak = $PlayerData_r['Deathstreak'];
		$KDR = round($PlayerData_r['KDR'],2);
		$TKs = $PlayerData_r['TKs'];
		$Wins = $PlayerData_r['Wins'];
		$Losses = $PlayerData_r['Losses'];
		$WLR = round($PlayerData_r['WLR'],2) * 100;
		$HighScore = $PlayerData_r['HighScore'];
		$FirstSeen = date("M d Y", strtotime($PlayerData_r['FirstSeenOnServer']));
		$LastSeen = date("M d Y", strtotime($PlayerData_r['LastSeenOnServer']));
		$PlayerID = $PlayerData_r['PlayerID'];
		$rank = $PlayerData_r['GlobalRank'];
		// filter out the available ranks so we don't try to load a rank image which doesn't exist
		if($rank >= $rank_min && $rank <= $rank_max)
		{
			$rank_img = './common/images/ranks/r' . $rank . '.png';
		}
		else
		{
			$rank_img = './common/images/ranks/missing.png';
		}
		// is this player banned?
		// or have previous ban which was lifted?
		$player_banned = 0;
		$previous_banned = 0;
		$ban_reason = NULL;
		if($adkats_available)
		{
			$ban_status = $PlayerData_r['ban_status'];
			$ban_reason = textcleaner($PlayerData_r['record_message']);
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
		if($player_banned == 1)
		{
			echo '
			<div class="banoutline">
			<div style="position: absolute; color: #990000; margin: 4px;">Banned';
			if(!empty($ban_reason))
			{
				echo '<span style="font-size: 10px;">: ' . $ban_reason . '</span>';
			}
			echo '
			</div>
			<div class="headline">
			' . ucfirst($SoldierName) . '
			</div>
			</div>
			';
		}
		elseif($previous_banned == 1)
		{
			echo '
			<div class="warnoutline">
			<div style="position: absolute; color: #993300; margin: 4px;">Warned';
			if(!empty($ban_reason))
			{
				echo '<span style="font-size: 10px;">: ' . $ban_reason . '</span>';
			}
			echo '
			</div>
			<div class="headline">
			' . ucfirst($SoldierName) . '
			</div>
			</div>
			';
		}
		else
		{
			echo '
			<div class="subsection">
			<div class="headline">
			' . ucfirst($SoldierName) . '
			</div>
			</div>
			';
		}
		// show ranks for player in this server
		// don't show ranks in combined server stats page due to server load concerns
		// don't show to bots
		if(!empty($ServerID) && !($isbot))
		{
			echo '
			<br/>
			<br/>
			<div id="ranks">
			<br/>
			<center><img class="load" src="./common/images/loading.gif" alt="loading" /></center>
			<br/><br/>
			</div>
			';
			// ajax load ranks
			echo '
			<script type="text/javascript">
			$(\'#ranks\').load("./common/player/player-ranks.php?gid=' . $GameID;
			if(!empty($ServerID))
			{
				echo '&sid=' . $ServerID;
			}
			if(!empty($PlayerID))
			{
				echo '&pid=' . $PlayerID;
			}
			if(!empty($ServerName))
			{
				echo '&server=' . urlencode($ServerName);
			}
			if(!empty($cr))
			{
				echo '&cr=' . $cr;
			}
			echo '");
			</script>
			';
		}
		echo '
		<br/>
		<br/>
		<table class="prettytable">
		<tr>
		<td width="15%" class="tablecontents" style="text-align: center;"><img src="' . $rank_img . '" style="height: 95px; width: 95px;" alt="rank ' . $rank . '"/></td>
		<td width="85%" class="tablecontents" colspan="5"><div class="headline">Overview</div></td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Country</th>
		<td width="18%" class="tablecontents"><img src="' . $country_img . '" style="height: 11px; width: 16px;" alt="' . $country . '"/> ' . $country . '<span class="information"> (</span>' . $CountryCode . '<span class="information">)</span></td>
		<th width="15%" style="padding-left: 10px;">First Visit</th>
		<td width="18%" class="tablecontents">' . $FirstSeen . '</td>
		<th width="15%" style="padding-left: 10px;">Last Visit</th>
		<td width="18%" class="tablecontents">' . $LastSeen . '</td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Kills</th>
		<td width="18%" class="tablecontents">' . $Kills . '</td>
		<th width="15%" style="padding-left: 10px;">Deaths</th>
		<td width="18%" class="tablecontents">' . $Deaths . '</td>
		<th width="15%" style="padding-left: 10px;">Kill / Death</th>
		<td width="18%" class="tablecontents">' . $KDR . '</td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Headshots</th>
		<td width="18%" class="tablecontents">' . $Headshots . '</td>
		<th width="15%" style="padding-left: 10px;">Suicides</th>
		<td width="18%" class="tablecontents">' . $Suicides . '</td>
		<th width="15%" style="padding-left: 10px;">Headshot / Kill</th>
		<td width="18%" class="tablecontents">' . $HSpercent . '<span class="information"> %</span></td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Wins</th>
		<td width="18%" class="tablecontents">' . $Wins . '</td>
		<th width="15%" style="padding-left: 10px;">Losses</th>
		<td width="18%" class="tablecontents">' . $Losses . '</td>
		<th width="15%" style="padding-left: 10px;">Wins / Losses</th>
		<td width="18%" class="tablecontents">' . $WLR . '<span class="information"> %</span></td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Kill-streak</th>
		<td width="18%" class="tablecontents">' . $Killstreak . '</td>
		<th width="15%" style="padding-left: 10px;">Death-streak</th>
		<td width="18%" class="tablecontents">' . $Deathstreak . '</td>
		<th width="15%" style="padding-left: 10px;">Team Kills</th>
		<td width="18%" class="tablecontents">' . $TKs . '</td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Total Score</th>
		<td width="18%" class="tablecontents">' . $Score . '</td>
		<th width="15%" style="padding-left: 10px;">High Score</th>
		<td width="18%" class="tablecontents">' . $HighScore . '</td>
		<th width="15%" style="padding-left: 10px;">Rounds Played</th>
		<td width="18%" class="tablecontents">' . $Rounds . '</td>
		</tr>
		<tr>
		<th width="15%" style="padding-left: 10px;">Playtime</th>
		<td width="18%" class="tablecontents">' . $Playtime . '</td>
		<th width="15%" style="padding-left: 10px;">&nbsp;</th>
		<td width="18%" class="tablecontents">&nbsp;</td>
		<th width="15%" style="padding-left: 10px;">&nbsp;</th>
		<td width="18%" class="tablecontents">&nbsp;</td>
		</tr>
		</table>
		<br/>
		';
		// get weapon stats for weapon graph
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$Weapon_q = @mysqli_query($BF4stats,"
				SELECT tws.`Damagetype`, SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				AND (tws.`Damagetype` = 'assaultrifle' OR tws.`Damagetype` = 'lmg' OR tws.`Damagetype` = 'shotgun' OR tws.`Damagetype` = 'smg' OR tws.`Damagetype` = 'sniperrifle' OR tws.`Damagetype` = 'handgun' OR tws.`Damagetype` = 'projectileexplosive' OR tws.`Damagetype` = 'explosive' OR tws.`Damagetype` = 'melee' OR tws.`Damagetype` = 'carbine' OR tws.`Damagetype` = 'dmr' OR tws.`Damagetype` = 'impact')
				GROUP BY tws.`Damagetype`
				ORDER BY Kills DESC
			");
			// do a separate query for vehicles
			$Vehicle_q = @mysqli_query($BF4stats,"
				SELECT SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				AND (tws.`Damagetype` LIKE '%vehicle%' OR tws.`DamageType` = 'none')
				AND (tws.`Friendlyname` <> 'dlSHTR')
				GROUP BY tpd.`PlayerID`
			");
			// initialize values
			$VehicleFound = 0;
			$VehicleAdded = 0;
			if(@mysqli_num_rows($Vehicle_q) == 1)
			{
				$Vehicle_r = @mysqli_fetch_assoc($Vehicle_q);
				$VehicleKills = $Vehicle_r['Kills'];
				$VehicleDeaths = $Vehicle_r['Deaths'];
				$VehicleHS = $Vehicle_r['Headshots'];
				$VehicleHSR = $Vehicle_r['HSR'];
				$VehicleFound = 1;
			}
		}
		// or else this is a combined stats page
		else
		{
			$Weapon_q = @mysqli_query($BF4stats,"
				SELECT tws.`Damagetype`, SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tpd.`PlayerID` = {$PlayerID}
				AND tsp.`ServerID` IN ({$valid_ids})
				AND tpd.`GameID` = {$GameID}
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				AND (tws.`Damagetype` = 'assaultrifle' OR tws.`Damagetype` = 'lmg' OR tws.`Damagetype` = 'shotgun' OR tws.`Damagetype` = 'smg' OR tws.`Damagetype` = 'sniperrifle' OR tws.`Damagetype` = 'handgun' OR tws.`Damagetype` = 'projectileexplosive' OR tws.`Damagetype` = 'explosive' OR tws.`Damagetype` = 'melee' OR tws.`Damagetype` = 'carbine' OR tws.`Damagetype` = 'dmr' OR tws.`Damagetype` = 'impact')
				GROUP BY tws.`Damagetype`
				ORDER BY Kills DESC
			");
			// do a separate query for vehicles
			$Vehicle_q = @mysqli_query($BF4stats,"
				SELECT SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
				FROM `tbl_weapons_stats` wa
				INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
				INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
				WHERE tsp.`ServerID` IN ({$valid_ids})
				AND tpd.`PlayerID` = {$PlayerID}
				AND tpd.`GameID` = {$GameID}
				AND (wa.`Kills` > 0 OR wa.`Deaths` > 0)
				AND (tws.`Damagetype` LIKE '%vehicle%' OR tws.`DamageType` = 'none')
				AND (tws.`Friendlyname` <> 'dlSHTR')
				GROUP BY tpd.`PlayerID`
			");
			// initialize values
			$VehicleFound = 0;
			$VehicleAdded = 0;
			if(@mysqli_num_rows($Vehicle_q) == 1)
			{
				$Vehicle_r = @mysqli_fetch_assoc($Vehicle_q);
				$VehicleKills = $Vehicle_r['Kills'];
				$VehicleDeaths = $Vehicle_r['Deaths'];
				$VehicleHS = $Vehicle_r['Headshots'];
				$VehicleHSR = $Vehicle_r['HSR'];
				$VehicleFound = 1;
			}
		}
		// show weapon graph if there are weapons or vehicles found for this player
		if(@mysqli_num_rows($Weapon_q) != 0 || $VehicleFound == 1)
		{
			// initialize empty array for storing weapons
			$WeaponCodes = array();
			echo '
			<br/>
			<table class="prettytable">
			<tr>
			<td class="tablecontents">
			<script type="text/javascript" src="//www.google.com/jsapi"></script>
			<script type="text/javascript">
				google.load(\'visualization\', \'1\', {packages: [\'corechart\']});
			</script>
			<script type="text/javascript">
				function drawVisualization() {
					// Create and populate the data table.
					var data = google.visualization.arrayToDataTable([
						[\'Weapon\', \'Kills\', \'Deaths\', \'Headshot Ratio\',  \'Headshots\']
						';
						$vmax = 0;
						$hmax = 0;
						// if there are more than just vehicle kills, do the normal weapon output inserting vehicles wherever appropriate
						if(@mysqli_num_rows($Weapon_q) != 0)
						{
							while($Weapon_r = @mysqli_fetch_assoc($Weapon_q))
							{
								$weapon = $Weapon_r['Damagetype'];
								// convert to nice version
								if(in_array($weapon,$cat_array))
								{
									$weapon = array_search($weapon,$cat_array);
								}
								$deaths = $Weapon_r['Deaths'];
								$kills = $Weapon_r['Kills'];						
								$headshots = $Weapon_r['Headshots'];
								$hsr = $Weapon_r['HSR'];
								// set max values higher than max for cropping reasons
								// find the vmax value
								if($deaths > $vmax)
								{
									$vmax = $deaths + 50;
								}
								// find the hmax value
								if($kills > $hmax)
								{
									$hmax = $kills + 50;
								}
								// are there vehicle stats?
								if($VehicleFound == 1)
								{
									// we want to inject the vehicle stats into the correct place
									if($kills > $VehicleKills)
									{
										// add to tabbed display array
										$WeaponCodes[] = $Weapon_r['Damagetype'];
									}
									elseif($kills < $VehicleKills && $VehicleAdded == 0)
									{
										// add vehicle to tabbed display array
										$WeaponCodes[] = 'VehicleCustom';
										echo ',
										[\'Vehicle\', ' . $VehicleKills . ', ' . $VehicleDeaths . ', ' . $VehicleHSR . ',  ' . $VehicleHS . ']
										';
										// add regular to tabbed display array
										$WeaponCodes[] = $Weapon_r['Damagetype'];
										$VehicleAdded = 1;
									}
									else
									{
										// proceed like normal
										$WeaponCodes[] = $Weapon_r['Damagetype'];
									}
								}
								else
								{
									// add to tabbed display array
									$WeaponCodes[] = $Weapon_r['Damagetype'];
								}
								echo ',
								[\'' . $weapon . '\', ' . $kills . ', ' . $deaths . ', ' . $hsr . ',  ' . $headshots . ']
								';
							}
						}
						// or else just process vehicle kills on its own
						elseif($VehicleFound == 1)
						{
							// set max values higher than max for cropping reasons
							$vmax = $VehicleDeaths + 50;
							$hmax = $VehicleKills + 50;
							// add vehicle to tabbed display array
							$WeaponCodes[] = 'VehicleCustom';
							echo ',
							[\'Vehicle\', ' . $VehicleKills . ', ' . $VehicleDeaths . ', ' . $VehicleHSR . ',  ' . $VehicleHS . ']
							';
						}
						echo '
					]);
					var options = {
						title: \'Headshot Ratio\',
						titleTextStyle: {color: \'#888\', bold: \'false\', fontSize: 12, auraColor: \'none\'},
						legend: {textStyle: {color: \'#888\', bold: \'false\', fontSize: 12, auraColor: \'none\'}},
						hAxis: {title: \'Kills\', titleTextStyle: {color: \'#888\', bold: \'false\', fontSize: 12, auraColor: \'none\'}, textStyle:  {color: \'#888\', auraColor: \'none\', fontSize: 8}, minValue: -1, maxValue: ' . $hmax . ', gridlines: {color: \'transparent\'}, baselineColor:  \'#666\'},
						vAxis: {title: \'Deaths\', titleTextStyle: {color: \'#888\', bold: \'false\', fontSize: 12, auraColor: \'none\'}, textStyle:  {color: \'#888\', auraColor: \'none\', fontSize: 8}, minValue: -1, maxValue: ' . $vmax . ', gridlines: {color: \'transparent\'}, baselineColor:  \'#666\'},
						bubble: {textStyle: {color: \'#888\', fontSize: 10, bold: \'false\', auraColor: \'none\'}, stroke: \'transparent\'},
						backgroundColor: \'transparent\',
						chartArea: {left: 20, top: 50, width: "90%", height: "70%"},
						colorAxis: {minValue: 0, colors: [\'#333333\', \'#640000\'], legend: {textStyle: {color: \'#888\'}, position: \'top\'}},
						sizeAxis: {minValue: 0, maxValue: 1, minSize: 15, maxSize: 20},
						tooltip: {textStyle: {color: \'#AA0000\', auraColor: \'none\'}},
						enableInteractivity: \'true\'
					};
					// Create and draw the visualization.
					var chart = new google.visualization.BubbleChart(
					document.getElementById(\'visualization\'));
					chart.draw(data, options);
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
			';
			// display weapon category tabs
			// clean up first tab's name
			// find clean name in array
			if(in_array($WeaponCodes['0'],$cat_array))
			{
				$code_Displayed = array_search($WeaponCodes['0'],$cat_array);
			}
			// not found in array.  use ugly version :(
			else
			{
				$code_Displayed = $WeaponCodes['0'];
			}
			echo '
			<div id="tabs">
			<ul>
			<li><div class="subscript">1</div><a href="#tabs-1">' . $code_Displayed . '</a></li>
			';
			$count_tracker = 1;
			// step through the weapon categories for creating tabs
			foreach($WeaponCodes AS $this_WeaponCode)
			{
				// first one was already created; skip it
				if($this_WeaponCode != $WeaponCodes['0'])
				{
					// clean up name
					// find clean name in array
					if(in_array($this_WeaponCode,$cat_array))
					{
						$tab_code_Displayed = array_search($this_WeaponCode,$cat_array);
					}
					// not found in array.  use ugly version :(
					else
					{
						$tab_code_Displayed = $this_WeaponCode;
					}
					$count_tracker++;
					echo '<li><div class="subscript">' . $count_tracker . '</div><a href="./common/player/weapon-tab.php?sid=' . $ServerID . '&amp;gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;c=' . $this_WeaponCode . '">' . $tab_code_Displayed . '</a></li>';
				}
			}
			echo '
			</ul>
			<div id="tabs-1">
			';
			// get weapon stats for weapon stats list
			Statsout($WeaponCodes['0'], $weapon_array, $PlayerID, $ServerID, $valid_ids, $GameID, $BF4stats, '3');
			echo '</div>
			</div>
			<br/>
			';
		}
		// begin dog tag stats
		// don't show to bots
		if(!($isbot))
		{
			// check to see if the player has gotten anyone's tags
			// query for dog tags this user has collected
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$DogTag_q1 = @mysqli_query($BF4stats,"
					SELECT tpd.`PlayerID`
					FROM `tbl_dogtags` dt
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` = {$ServerID}
					LIMIT 1
				");
			}
			// or else this is a combined stats page
			else
			{
				$DogTag_q1 = @mysqli_query($BF4stats,"
					SELECT tpd.`PlayerID`
					FROM `tbl_dogtags` dt
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
					LIMIT 1
				");
			}
			// or if anyone has gotten his
			// find who has killed this player
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				$DogTag_q2 = @mysqli_query($BF4stats,"
					SELECT tpd.`PlayerID`
					FROM `tbl_dogtags` dt
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`VictimID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` = {$ServerID}
					LIMIT 1
				");
			}
			// or else this is a combined stats page
			else
			{
				$DogTag_q2 = @mysqli_query($BF4stats,"
					SELECT tpd.`PlayerID`
					FROM `tbl_dogtags` dt
					INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`VictimID`
					INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
					WHERE tpd.`PlayerID` = {$PlayerID}
					AND tpd.`GameID` = {$GameID}
					AND tsp.`ServerID` IN ({$valid_ids})
					LIMIT 1
				");
			}
			// only display dogtag block if this player has dogtags or someone has this player's dogtags
			if(@mysqli_num_rows($DogTag_q1) != 0 || @mysqli_num_rows($DogTag_q2) != 0)
			{
				// initialize value
				$count = 0;
				echo '
				<br/>
				<div id="dogtag_tab">
				<ul>
				<li><a href="#dogtag_tab-1">Dog Tags Collected</a></li>
				';
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					echo '<li><a href="./common/player/dogtag-tab.php?gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;sid=' . $ServerID . '&amp;player=' . $SoldierName . '">Dog Tags Surrendered</a></li>';
				}
				// or else this is a global stats page
				else
				{
					echo '<li><a href="./common/player/dogtag-tab.php?gid=' . $GameID . '&amp;pid=' . $PlayerID . '&amp;player=' . $SoldierName . '">Dog Tags Surrendered</a></li>';
				}
				echo '
				</ul>
				<div id="dogtag_tab-1">
				<table class="prettytable">
				';
				// check to see if the player has gotten anyone's tags
				// query for dog tags this user has collected
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					// is adkats information available?
					if($adkats_available)
					{
						$DogTag_q = @mysqli_query($BF4stats,"
							SELECT dt.`Count`, tpd2.`SoldierName` AS Victim, tpd2.`PlayerID` AS VictimID, adk.`ban_status`
							FROM `tbl_dogtags` dt
							INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
							INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
							INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
							LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd2.`PlayerID`
							WHERE tpd.`PlayerID` = {$PlayerID}
							AND tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` = {$ServerID}
							ORDER BY Count DESC, Victim ASC
						");
					}
					else
					{
						$DogTag_q = @mysqli_query($BF4stats,"
							SELECT dt.`Count`, tpd2.`SoldierName` AS Victim, tpd2.`PlayerID` AS VictimID
							FROM `tbl_dogtags` dt
							INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
							INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
							INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
							WHERE tpd.`PlayerID` = {$PlayerID}
							AND tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` = {$ServerID}
							ORDER BY Count DESC, Victim ASC
						");
					}
				}
				// or else this is a combined stats page
				else
				{
					// is adkats information available?
					if($adkats_available)
					{
						$DogTag_q = @mysqli_query($BF4stats,"
							SELECT SUM(dt.`Count`) AS Count, tpd2.`SoldierName` AS Victim, tpd2.`PlayerID` AS VictimID, adk.`ban_status`
							FROM `tbl_dogtags` dt
							INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
							INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
							INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
							LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd2.`PlayerID`
							WHERE tpd.`PlayerID` = {$PlayerID}
							AND tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` IN ({$valid_ids})
							GROUP BY Victim
							ORDER BY Count DESC, Victim ASC
						");
					}
					else
					{
						$DogTag_q = @mysqli_query($BF4stats,"
							SELECT SUM(dt.`Count`) AS Count, tpd2.`SoldierName` AS Victim, tpd2.`PlayerID` AS VictimID
							FROM `tbl_dogtags` dt
							INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
							INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
							INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
							INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
							WHERE tpd.`PlayerID` = {$PlayerID}
							AND tpd.`GameID` = {$GameID}
							AND tsp.`ServerID` IN ({$valid_ids})
							GROUP BY Victim
							ORDER BY Count DESC, Victim ASC
						");
					}
				}
				if(@mysqli_num_rows($DogTag_q) != 0)
				{
					echo '
					<tr>
					<th width="5%" class="countheader">#</th>
					<th width="47%" style="text-align: left;padding-left: 10px;">Victim</th>
					<th width="48%" style="text-align: left;padding-left: 5px;"><span class="orderedDESCheader">Count</span></th>
					</tr>
					';
					while($DogTag_r = @mysqli_fetch_assoc($DogTag_q))
					{
						$Victim = textcleaner($DogTag_r['Victim']);
						$VictimID = $DogTag_r['VictimID'];
						$KillCount = $DogTag_r['Count'];
						$link = './index.php?';
						if(!empty($ServerID))
						{
							$link .= 'sid=' . $ServerID . '&amp;';
						}
						$link .= 'pid=' . $VictimID . '&amp;p=player';
						// is this player banned?
						// or have previous ban which was lifted?
						$player_banned = 0;
						$previous_banned = 0;
						if($adkats_available)
						{
							$ban_status = $DogTag_r['ban_status'];
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
						// show expand/contract if very long
						if($count == 10)
						{
							echo '
							</table>
							<div>
							<span class="expanded">
							<table class="prettytable" style="margin-top: -2px;">
							';
						}
						$count++;
						echo '
						<tr>
							<td width="5%" class="count"><span class="information">' . $count . '</span></td>
							';
							if($player_banned == 1)
							{
								echo '<td width="47%" class="banoutline" style="text-align: left;padding-left: 10px; position: relative;"><div class="bansubscript">Banned</div>';
							}
							elseif($previous_banned == 1)
							{
								echo '<td width="47%" class="warnoutline" style="text-align: left;padding-left: 10px; position: relative;"><div class="bansubscript">Warned</div>';
							}
							else
							{
								echo '<td width="47%" class="tablecontents" style="text-align: left;padding-left: 10px; position: relative;">';
							}
							echo '
								<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
									<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
								</div>
								<a href="' . $link . '">' . $Victim . '</a>
							</td>
							<td width="48%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $KillCount . '</td>
						</tr>
						';
					}
					// finish expand/contract if very long
					if($count > 10)
					{
						$remaining = $count - 10;
						echo '
						</table>
						</span>
						<a href="javascript:void(0)" class="collapsed"><table class="prettytable" style="margin-top: -2px;"><tr><td class="tablecontents" style="text-align: left;padding-left: 15px;"><span class="orderedDESCheader">Show ' . $remaining . ' More</span></td></tr></table></a>
						</div>
						<table>
						<tr>
						<td>
						</td>
						</tr>
						';
					}
				}
				else
				{
					echo '
					<tr>
					<td width="100%" class="tablecontents" colspan="3" style="text-align: left;padding-left: 10px;"><div class="headline">' . $SoldierName . ' has not collected any dog tags.</div></td>
					</tr>
					';
				}
				echo '
				</table>
				</div>
				</div>
				<br/>
				';
			}
		}
		// begin signature images...
		// don't show to bots
		if(!($isbot))
		{
			echo '
			<br/>
			<div class="subsection" style="position: relative;"><div class="headline"><span class="information">Signature images use combined stats from all of ' . $clan_name . '\'s servers.</span></div>
			';
			// check if this player's rank is cached in the database
			// we do this early so that we can insert dummy data now into the database (if necessary) to reduce duplicates later when the slower parallel process is executed
			// (in other words, insert dummy data now quickly, so later the parallel slow execution updates the one dummy data row instead of inserting multiple new data rows in parallel)
			// rank players by score
			// check to see if this rank cache table exists
			@mysqli_query($BF4stats,"
				CREATE TABLE IF NOT EXISTS `tyger_stats_rank_cache`
				(
					`ID` INT UNSIGNED NOT NULL AUTO_INCREMENT,
					`PlayerID` INT(10) UNSIGNED NOT NULL,
					`GID` TINYINT(4) UNSIGNED NOT NULL,
					`SID` VARCHAR(100) NOT NULL,
					`category` VARCHAR(20) NOT NULL,
					`rank` INT(10) UNSIGNED NOT NULL DEFAULT '0',
					`timestamp` INT(11) NOT NULL DEFAULT '0',
					PRIMARY KEY (`ID`),
					UNIQUE `UNIQUE_RankData` (`PlayerID`, `GID`, `SID`, `category`),
					INDEX `PlayerID` (`PlayerID` ASC),
					INDEX `GID` (`GID` ASC),
					INDEX `SID` (`SID` ASC),
					INDEX `category` (`category` ASC),
					INDEX `timestamp` (`timestamp` ASC),
					CONSTRAINT `fk_tyger_stats_rank_cache_PlayerID` FOREIGN KEY (`PlayerID`) REFERENCES `tbl_playerdata`(`PlayerID`) ON DELETE CASCADE ON UPDATE CASCADE,
					CONSTRAINT `fk_tyger_stats_rank_cache_GID` FOREIGN KEY (`GID`) REFERENCES `tbl_games`(`GameID`) ON DELETE CASCADE ON UPDATE CASCADE
				)
				ENGINE=InnoDB
			");
			// initialize timestamp values
			$now_timestamp = time();
			// if cache refresh triggered, refresh cache regardless of last cache time
			if($cr == 1)
			{
				$old = $now_timestamp;
			}
			else
			{
				$old = $now_timestamp - 10800;
			}
			// rank players by score
			// check if score rank is already cached
			$ScoreC_q = @mysqli_query($BF4stats,"
				SELECT `rank`, `timestamp`
				FROM `tyger_stats_rank_cache`
				WHERE `PlayerID` = {$PlayerID}
				AND `category` = 'Score'
				AND `GID` = '{$GameID}'
				AND `SID` = '{$valid_ids}'
				GROUP BY `PlayerID`
			");
			if(@mysqli_num_rows($ScoreC_q) != 0)
			{
				$ScoreC_r = @mysqli_fetch_assoc($ScoreC_q);
				$srank = $ScoreC_r['rank'];
				$timestamp = $ScoreC_r['timestamp'];
				// data old? or incorrect data? show recalculate message
				if(($timestamp <= $old) OR ($srank == 0))
				{
					// we aren't actually doing this now
					// we are just showing the message that it will be done
					echo '
					<div id="cache_fade2" style="position: absolute; top: 1px; left: -150px; display: none;">
					<div class="subsection" style="width: 100px; font-size: 12px;">
					<center>Cache Recreated:<br/>Ranks</center>
					</div>
					</div>
					<script type="text/javascript">
					$("#cache_fade2").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
					</script>
					';
				}
				// show reuse message
				else
				{
					// we aren't actually doing this now
					// we are just showing the message that it will be done
					echo '
					<div id="cache_fade2" style="position: absolute; top: 1px; left: -150px; display: none;">
					<div class="subsection" style="width: 100px; font-size: 12px;">
					<center>Cache Used:<br/>Ranks</center>
					</div>
					</div>
					<script type="text/javascript">
					$("#cache_fade2").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
					</script>
					';
				}
			}
			else
			{
				// show insert message
				echo '
				<div id="cache_fade2" style="position: absolute; top: 1px; left: -150px; display: none;">
				<div class="subsection" style="width: 100px; font-size: 12px;">
				<center>Cache Created:<br/>Ranks</center>
				</div>
				</div>
				<script type="text/javascript">
				$("#cache_fade2").finish().fadeIn("slow").show().delay(2000).fadeOut("slow");
				</script>
				';
				// insert useless dummy data for now
				@mysqli_query($BF4stats,"
					INSERT INTO `tyger_stats_rank_cache`
					(`PlayerID`, `GID`, `SID`, `category`, `rank`, `timestamp`)
					VALUES ('{$PlayerID}', '{$GameID}', '{$valid_ids}', 'Score', '0', '0')
				");
			}
			// cache refresh option
			$refresh_link = './index.php?';
			if(!empty($ServerID))
			{
				$refresh_link .= '&sid=' . $ServerID;
			}
			if(!empty($PlayerID))
			{
				$refresh_link .= '&pid=' . $PlayerID;
			}
			$refresh_link .= '&amp;p=player&amp;cr=1';
			echo '
			<div id="cache_refresh2" style="position: absolute; top: 10px; left: -28px; vertical-align: middle; display: none;">
			<center><a href="' . $refresh_link . '"><img src="./common/images/refresh.png" alt="refresh" /></a></center>
			</div>
			<script type="text/javascript">
			$("#cache_refresh2").delay(4000).fadeIn("slow");
			</script>
			';
			// done with the dummy cache stuff...
			// find current URL info
			// is this an HTTPS server?
			if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443)
			{
				$host = 'https://' . $_SERVER['HTTP_HOST'];
			}
			else
			{
				$host = 'http://' . $_SERVER['HTTP_HOST'];
			}
			$dir = dirname($_SERVER['PHP_SELF']);
			$file = $_SERVER['PHP_SELF'];
			// show signature images
			echo '
			</div>
			<table class="prettytable">
			<tr>
			<td class="tablecontents" style="text-align: center; padding: 20px;" valign="top" width="50%">
			';
			// include signature.php image
			echo '
			<br/><br/>
			<a href="' . $host . $dir . '/common/signature/signaturepid' . $PlayerID . 'fav0.png" target="_blank"><img src="./common/signature/signaturepid' . $PlayerID . 'fav0.png';
			if(!empty($cr))
			{
				echo '?cr=' . $cr;
			}
			echo '" style="height: 100px; width: 400px;" alt="signature" /></a>
			<br/>
			</td>
			<td class="tablecontents" style="text-align: left; padding: 20px;" valign="top" width="50%">
			<span class="information">BBcode:</span>
			<br/><br/>
			Image with rank:
			<br/>
			<table class="prettytable">
			<tr>
			<td class="tablecontents">
			<span style="font-size: 10px;">[URL=' . $host . $file . '?p=player&amp;pid=' . $PlayerID . '][IMG]' . $host . $dir . '/common/signature/signaturepid' . $PlayerID . 'fav0.png[/IMG][/URL]</span>
			</td>
			</tr>
			</table>
			<br/>
			Image with favorite weapon:
			<br/>
			<table class="prettytable">
			<tr>
			<td class="tablecontents">
			<span style="font-size: 10px;">[URL=' . $host . $file . '?p=player&amp;pid=' . $PlayerID . '][IMG]' . $host . $dir . '/common/signature/signaturepid' . $PlayerID . 'fav1.png[/IMG][/URL]</span>
			</td>
			</tr>
			</table>
			</td>
			</tr>
			</table>
			<br/>
			';
		}
	}
}
// this shouldn't happen, but we will make sure
elseif($SoldierName == 'Not Found')
{
	echo '
	<div class="subsection">
	<div class="headline">
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo 'The selected player ID was not found in this server.';
	}
	// or else this is a global stats page
	else
	{
		echo 'The selected player ID was not found in these servers.';
	}
	echo '
	</div>
	</div>
	';
}
// begin external stats block
if($SoldierName != null AND $SoldierName != 'Not Found')
{
	echo '
	<br/>
	<div class="subsection"><div class="headline"><span class="information">External Links for "' . $SoldierName . '"</span></div></div>
	<table class="prettytable">
	<tr>
	<td class="tablecontents" width="50%" style="text-align: center"><span class="information">Battlelog Stats: </span><a href="https://battlelog.battlefield.com/bf4/user/' . $SoldierName . '" target="_blank">www.Battlelog.Battlefield.com</a></td>
	<td class="tablecontents" width="50%" style="text-align: center"><span class="information">BF4DB: </span><a href="https://bf4db.com/player/search?name=' . $SoldierName . '" target="_blank">www.BF4DB.com</a></td>
	</tr>
	</table>
	<br/>
	';
}
echo '
</div>
';
?>
