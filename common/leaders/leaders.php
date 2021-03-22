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
// jquery auto-find players in leaderboard
// don't show to bots
if(!($isbot))
{
	echo '
	<script type="text/javascript">
	$(function()
	{
		$("#soldiers_leaders").autocomplete(
		{
			source: "./common/player/player-search.php?';
			if(!empty($ServerID))
			{
				echo 'sid=' . $ServerID . '&';
			}
			echo 'gid=' . $GameID . '",
			minLength: 3,
			delay: 500,
			select: function( event, ui )
			{
				if(ui.item)
				{
					$(\'#soldiers_leaders\').val(ui.item.value);
				}
				$(\'#ajaxsearch_leaders\').submit();
			}
		});
	});
	</script>
	';
}
// javascript transition wrapper between loading and loaded
echo '
<script type="text/javascript">
$(\'#loading\').hide(0);
$(\'#loaded\').fadeIn("slow");
</script>
';
// continue html output
echo '
<div id="tabs">
<ul>
<li><a href="#tabs-1">Top Players</a></li>
<li><a href="./common/leaders/sessions-tab.php?sid=' . $ServerID . '&amp;gid=' . $GameID . '">Top 20 Players This Week</a></li>
</ul>
<div id="tabs-1">
';
// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// find out how many rows are in the table 
	$TotalRows_q = @mysqli_query($BF4stats,"
		SELECT `CountPlayers`
		FROM `tbl_server_stats`
		WHERE `ServerID` = {$ServerID}
	");
	$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
	$numrows = $TotalRows_r['CountPlayers'];
	$TotalServerPlayers = $numrows;
}
// or else this is a global stats page
else
{
	echo '<div style="position: relative;">';
	$numrows = cache_total_players($ServerID, $valid_ids, $GameID, $BF4stats, $cr);
	$TotalServerPlayers = $numrows;
	echo '</div>';
}
// number of rows to show per page
$rowsperpage = 20;
// find out total pages
$totalpages = ceil($numrows / $rowsperpage);
// set current pagination page to default if none provided
if(empty($currentpage))
{
	// default page num
	$currentpage = 1;
}
// if current page is greater than total pages...
if($currentpage > $totalpages)
{
	// set current page to last page
	$currentpage = $totalpages;
}
// if current page is less than first page...
if($currentpage < 1)
{
	// set current page to first page
	$currentpage = 1;
}
// get current rank query details
if(!empty($rank))
{
	// filter out SQL injection
	if($rank != 'SoldierName' AND $rank != 'Score' AND $rank != 'Playtime' AND $rank != 'Kills' AND $rank != 'KDR' AND $rank != 'HSR')
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
// the offset of the list, based on current page 
$offset = ($currentpage - 1) * $rowsperpage;
// if this is the default page, use cache
if($rank == 'Score' && $order == 'DESC' && $offset == '0' && empty($player))
{
	echo '<div style="position: relative;">';
	$Players_q = cache_top_twenty($ServerID, $valid_ids, $GameID, $BF4stats, $cr, $TotalServerPlayers);
	// cache refresh option
	// only mess with caching if the server isn't small
	if($TotalServerPlayers > 1000)
	{
		$refresh_link = './index.php?';
		if(!empty($ServerID))
		{
			$refresh_link .= '&amp;sid=' . $ServerID;
		}
		if(!empty($page))
		{
			$refresh_link .= '&amp;p=' . $page;
		}
		if(!empty($player))
		{
			$refresh_link .= '&amp;player=' . $player;
		}
		if(!empty($currentpage))
		{
			$refresh_link .= '&amp;cp=' . $currentpage;
		}
		if(!empty($rank))
		{
			$refresh_link .= '&amp;r=' . $rank;
		}
		if(!empty($order))
		{
			$refresh_link .= '&amp;o=' . $order;
		}
		$refresh_link .= '&amp;cr=1';
		echo '
		<div id="cache_refresh" style="position: absolute; top: 10px; left: -25px; vertical-align: middle; display: none;">
		<center><a href="' . $refresh_link . '"><img src="./common/images/refresh.png" alt="refresh" /></a></center>
		</div>
		<script type="text/javascript">
		$("#cache_refresh").delay(4000).fadeIn("slow");
		</script>
		';
	}
	echo '</div>';
}
// if a player name was entered, search for the entered players' position
elseif(!empty($player))
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// is adkats information available?
		if($adkats_available)
		{
			// get the info from the db 
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Playtime`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR, adk.`ban_status`
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`GameID` = {$GameID}
				AND tpd.`SoldierName` LIKE '%{$player}%'
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT 10
			");
		}
		else
		{
			// get the info from the db 
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Playtime`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`GameID` = {$GameID}
				AND tpd.`SoldierName` LIKE '%{$player}%'
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT 10
			");
		}
	}
	// or else this is a global stats page
	else
	{
		// is adkats information available?
		if($adkats_available)
		{
			// get the info from the db 
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, adk.`ban_status`
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				AND tpd.`SoldierName` LIKE '%{$player}%'
				GROUP BY tpd.`PlayerID`
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT 10
			");
		}
		else
		{
			// get the info from the db 
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				AND tpd.`SoldierName` LIKE '%{$player}%'
				GROUP BY tpd.`PlayerID`
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT 10
			");
		}
	}
}
// or else just display every player the normal way
else
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// is adkats information available?
		if($adkats_available)
		{
			// get the info from the db 
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Playtime`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR, adk.`ban_status`
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`GameID` = {$GameID}
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT {$offset}, {$rowsperpage}
			");
		}
		else
		{
			// get the info from the db 
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, tps.`Score`, tps.`Playtime`, tps.`Kills`, (tps.`Kills`/tps.`Deaths`) AS KDR, (tps.`Headshots`/tps.`Kills`) AS HSR
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tsp.`ServerID` = {$ServerID}
				AND tpd.`GameID` = {$GameID}
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT {$offset}, {$rowsperpage}
			");
		}
	}
	// or else this is a global stats page
	else
	{
		// is adkats information available?
		if($adkats_available)
		{
			// get the info from the db 
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR, adk.`ban_status`
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				LEFT JOIN `adkats_bans` adk ON adk.`player_id` = tpd.`PlayerID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`PlayerID`
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT {$offset}, {$rowsperpage}
			");
		}
		else
		{
			// get the info from the db 
			$Players_q  = @mysqli_query($BF4stats,"
				SELECT tpd.`SoldierName`, tpd.`PlayerID`, SUM(tps.`Score`) AS Score, SUM(tps.`Playtime`) AS Playtime, SUM(tps.`Kills`) AS Kills, (SUM(tps.`Kills`)/SUM(tps.`Deaths`)) AS KDR, (SUM(tps.`Headshots`)/SUM(tps.`Kills`)) AS HSR
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tpd.`GameID` = {$GameID}
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`PlayerID`
				ORDER BY {$rank} {$order}, tpd.`SoldierName` {$nextorder}
				LIMIT {$offset}, {$rowsperpage}
			");
		}
	}
}
// offset of player rank count to show on scoreboard
$count = ($currentpage * 20) - 20;
// check if there are rows returned
if(@mysqli_num_rows($Players_q) != 0)
{
	// if this is the default first page, we don't know the ban information yet because the cache doesn't store that information
	// we do this now so that we can query for ban information only once instead of doing it in the while loop 20 times (once for all players now instead of once for each player later)
	if($rank == 'Score' && $order == 'DESC' && $offset == '0')
	{
		if($adkats_available)
		{
			// create an intermediate array to store values in
			$pid_array = array();
			// while there are rows to be fetched...
			while($Players_r = @mysqli_fetch_assoc($Players_q))
			{
				$pid_array[] = $Players_r['PlayerID'];
			}
			// merge the array into a list
			$pid_list = join(',',$pid_array);
			// set the pointer back to the beginning of the player query result array (so that we can loop through it again further down)
			@mysqli_data_seek($Players_q, 0);
			// now query for all banned player information for the pids we just gathered
			$Ban_q  = @mysqli_query($BF4stats,"
				SELECT `player_id`, `ban_status`
				FROM `adkats_bans`
				WHERE `player_id` IN ({$pid_list})
			");
		}
	}
	echo '
	<table class="prettytable">
	<tr>
	<td class="tablecontents">
	<form id="ajaxsearch_leaders" action="./index.php" method="get">
	&nbsp;<span class="information">Search for Player:</span>
	<input type="hidden" name="p" value="leaders" />
	<input type="hidden" name="sid" value="' . $ServerID . '" />
	<input id="soldiers_leaders" type="text" class="inputbox" style="width: 140px;"';
	// try to fill in search box
	if(!empty($player))
	{
		echo 'value="' . $player . '" ';
	}
	echo 'name="player" style="font-size: 12px;"/>
	</form>
	</td>
	</tr>
	</table>
	<table class="prettytable" style="margin-top: -2px;">
	<tr>
	<th width="5%" class="countheader">#</th>
	';
	// player column
	pagination_headers('Player',$ServerID,'leaders','16','r',$rank,'SoldierName','o',$order,'ASC',$nextorder,$currentpage,'',$player,'');
	// score column
	pagination_headers('Score',$ServerID,'leaders','15','r',$rank,'Score','o',$order,'DESC',$nextorder,$currentpage,'',$player,'');
	// playtime column
	pagination_headers('Playtime',$ServerID,'leaders','15','r',$rank,'Playtime','o',$order,'DESC',$nextorder,$currentpage,'',$player,'');
	// kills column
	pagination_headers('Kills',$ServerID,'leaders','15','r',$rank,'Kills','o',$order,'DESC',$nextorder,$currentpage,'',$player,'');
	// kdr column
	pagination_headers('Kill / Death',$ServerID,'leaders','15','r',$rank,'KDR','o',$order,'DESC',$nextorder,$currentpage,'',$player,'');
	// hsr column
	pagination_headers('Headshot / Kill',$ServerID,'leaders','15','r',$rank,'HSR','o',$order,'DESC',$nextorder,$currentpage,'',$player,'');
	echo '
	</tr>
	</table>
	';
	// while there are rows to be fetched...
	while($Players_r = @mysqli_fetch_assoc($Players_q))
	{
		$Score = $Players_r['Score'];
		$Playtime = $Players_r['Playtime'];
		$Playhours = floor($Playtime / 3600);
		$Playminutes = floor(($Playtime / 60) % 60);
		$Playseconds = $Playtime % 60;
		$Playtime = $Playhours . ':' . $Playminutes . ':' . $Playseconds;
		$SoldierName = textcleaner($Players_r['SoldierName']);
		$Soldier_Name = mysqli_real_escape_string($BF4stats, $Players_r['SoldierName']);
		$PlayerID = $Players_r['PlayerID'];
		$Player_ID = mysqli_real_escape_string($BF4stats, $Players_r['PlayerID']);
		$Kills = $Players_r['Kills'];
		$KDR = round($Players_r['KDR'], 2);
		$HSR = round(($Players_r['HSR']*100),2);
		// do the fast count if player name search isn't being done
		// or do fast count if this is a bot
		if(empty($player) || $isbot)
		{
			$count++;
		}
		else
		{
			// include leader-ranks.php contents
			include('./leader-ranks.php');
		}
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
		// if this is the default first page, we had to use the alternate method of finding out ban information because the top 20 cache doesn't contain that information
		if($rank == 'Score' && $order == 'DESC' && $offset == '0')
		{
			if($adkats_available)
			{
				// find if this player has any ban information
				if(@mysqli_num_rows($Ban_q) != 0)
				{
					while($Ban_r = @mysqli_fetch_assoc($Ban_q))
					{
						$Ban_Status = $Ban_r['ban_status'];
						$Ban_pid = $Ban_r['player_id'];
						if($PlayerID == $Ban_pid)
						{
							if($Ban_Status == 'Active')
							{
								$player_banned = 1;
							}
							elseif($Ban_Status == 'Expired')
							{
								$previous_banned = 1;
							}
							break;
						}
					}
					// set the pointer back to the beginning of the ban query result array (so that we can loop through it again)
					@mysqli_data_seek($Ban_q, 0);
				}
			}
		}
		// not the default first page. we figured out the ban status in the query above
		else
		{
			if($adkats_available)
			{
				$ban_status = $Players_r['ban_status'];
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
					echo '<td width="16%" class="banoutline"><div class="bansubscript">Banned</div>';
				}
				elseif($previous_banned == 1)
				{
					echo '<td width="16%" class="warnoutline"><div class="bansubscript">Warned</div>';
				}
				else
				{
					echo '<td width="16%" class="tablecontents">';
				}
				echo '
				<a href="' . $link . '">' . $SoldierName . '</a></td>
				<td width="15%" class="tablecontents">' . $Score . '</td>
				<td width="15%" class="tablecontents">' . $Playtime . '</td>
				<td width="15%" class="tablecontents">' . $Kills . '</td>
				<td width="15%" class="tablecontents">' . $KDR . '</td>
				<td width="15%" class="tablecontents">' . $HSR . '<span class="information"> %</span></td>
			</tr>
		</table>
		';
	}
	if(empty($player))
	{
		// build the pagination links
		pagination_links($ServerID,'./index.php',$page,$currentpage,$totalpages,$rank,$order,'');
	}
}
else
{
	echo '
	<table class="prettytable">
	<tr>
	<td class="tablecontents">
	<form id="ajaxsearch_leaders" action="./index.php" method="get">
	&nbsp;<span class="information">Search for Player:</span>
	<input type="hidden" name="p" value="leaders" />
	<input type="hidden" name="sid" value="' . $ServerID . '" />
	<input id="soldiers_leaders" type="text" class="inputbox" style="width: 140px;"';
	// try to fill in search box
	if(!empty($player))
	{
		echo 'value="' . $player . '" ';
	}
	echo 'name="player" style="font-size: 12px;"/>
	</form>
	</td>
	</tr>
	</table>
	<table class="prettytable" style="margin-top: -2px;">
	<tr>
	<td class="tablecontents">
	<div class="headline">
	No ';
	if(!empty($player))
	{
		echo 'matching ';
	}
	echo 'player stats found for ';
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
	</td>
	</tr>
	</table>
	';
}
echo '
</div>
</div>
';
?>