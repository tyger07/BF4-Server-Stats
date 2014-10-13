<?php
// dogtag-tab for server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// first connect to the database
// and include necessary files
include_once('../config/config.php');
include_once('../common/connect.php');
include_once('../common/case.php');

// default variable to null
$ServerID = null;
$GameID = null;
$Code = null;

// get query search string
if(!empty($sid))
{
	$ServerID = $sid;
}
// get query search string
if(!empty($gid))
{
	$GameID = $gid;
}
// get query search string
if(!empty($pid))
{
	$PlayerID = $pid;
}
// get query search string
if(!empty($player))
{
	$SoldierName = $player;
}

// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// find who has killed this player
	$DogTag_q = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName` AS Killer, tpd.`PlayerID` AS KillerID, dt.`Count`
		FROM `tbl_dogtags` dt
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
		INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
		WHERE tpd2.`PlayerID` = {$PlayerID}
		AND tpd2.`GameID` = {$GameID}
		AND tsp.`ServerID` = {$ServerID}
		ORDER BY Count DESC, Killer ASC
	");
}
// or else this is a global stats page
else
{
	// find who has killed this player
	$DogTag_q = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName` AS Killer, tpd.`PlayerID` AS KillerID, SUM(dt.`Count`) AS Count
		FROM `tbl_dogtags` dt
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
		INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
		WHERE tpd2.`PlayerID` = {$PlayerID}
		AND tpd2.`GameID` = {$GameID}
		GROUP BY Killer
		ORDER BY Count DESC, Killer ASC
	");
}
// initialize value
$count = 0;
echo '
<table class="prettytable">
';
// check to see if anyone has got the player's tags
if(@mysqli_num_rows($DogTag_q) != 0)
{
	echo '
	<tr>
	<th width="5%" class="countheader">#</th>
	<th width="47%" style="text-align: left;padding-left: 10px;">Killer</th>
	<th width="48%" style="text-align: left;padding-left: 5px;"><span class="orderedDESCheader">Count</span></th>
	</tr>
	';
	while($DogTag_r = @mysqli_fetch_assoc($DogTag_q))
	{
		$Killer = $DogTag_r['Killer'];
		$KillerID = $DogTag_r['KillerID'];
		$KillCount = $DogTag_r['Count'];
		$count++;
		echo '
		<tr>
		<td width="5%" class="count"><span class="information">' . $count . '</span></td>
		';
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			echo '<td width="47%" class="tablecontents" style="text-align: left;padding-left: 10px;"><a href="./index.php?sid=' . $ServerID . '&amp;pid=' . $KillerID . '&amp;p=player">' . $Killer . '</a></td>';
		}
		// or else this is a global stats page
		else
		{
			echo '<td width="47%" class="tablecontents" style="text-align: left;padding-left: 10px;"><a href="./index.php?p=player&amp;pid=' . $KillerID . '">' . $Killer . '</a></td>';
		}
		echo '
		<td width="48%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $KillCount . '</td>
		</tr>
		';
	}
}
else
{
	echo '
	<tr>
	<td width="100%" class="tablecontents" colspan="3" style="text-align: left;padding-left: 10px;"><div class="headline">No one has gotten ' . $SoldierName . '\'s tags.</div></td>
	</tr>
	';
}
// free up dog tag query memory
@mysqli_free_result($DogTag_q);
echo '</table>';

?>
