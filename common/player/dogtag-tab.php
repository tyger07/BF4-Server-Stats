<?php
// BF4 Stats Page by Ty_ger07
// https://forum.myrcon.com/showthread.php?6854

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
// default variable to null
$ServerID = null;
$Code = null;
// get query search string
if(!empty($sid))
{
	$ServerID = $sid;
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
// find who has killed this player
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
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
// or else this is a combined stats page
else
{
	$DogTag_q = @mysqli_query($BF4stats,"
		SELECT tpd.`SoldierName` AS Killer, tpd.`PlayerID` AS KillerID, SUM(dt.`Count`) AS Count
		FROM `tbl_dogtags` dt
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = dt.`KillerID`
		INNER JOIN `tbl_server_player` tsp2 ON tsp2.`StatsID` = dt.`VictimID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		INNER JOIN `tbl_playerdata` tpd2 ON tsp2.`PlayerID` = tpd2.`PlayerID`
		WHERE tpd2.`PlayerID` = {$PlayerID}
		AND tpd2.`GameID` = {$GameID}
		AND tsp.`ServerID` IN ({$valid_ids})
		GROUP BY Killer
		ORDER BY Count DESC, Killer ASC
	");
}
// initialize value
$count = 0;
echo '
<script type="text/javascript">
$(document).ready(function()
{
	$(".expanded2").hide();
	$(".collapsed2, .expanded2").click(function()
	{
		$(this).parent().children(".expanded2, .collapsed2").toggle();
	});
});
</script>
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
		$link = './index.php?';
		if(!empty($ServerID))
		{
			$link .= 'sid=' . $ServerID . '&amp;';
		}
		$link .= 'pid=' . $KillerID . '&amp;p=player';
		// show expand/contract if very long
		if($count == 10)
		{
			echo '
			</table>
			<div>
			<span class="expanded2">
			<table class="prettytable" style="margin-top: -2px;">
			';
		}
		$count++;
		echo '
		<tr>
			<td width="5%" class="count"><span class="information">' . $count . '</span></td>
			<td width="47%" class="tablecontents" style="text-align: left;padding-left: 10px; position: relative;">
				<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
					<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
				</div>
				<a href="' . $link . '">' . $Killer . '</a>
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
		<a href="javascript:void(0)" class="collapsed2"><table class="prettytable" style="margin-top: -2px;"><tr><td class="tablecontents" style="text-align: left;padding-left: 15px;"><span class="orderedDESCheader">Show ' . $remaining . ' More</span></td></tr></table></a>
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
	<td width="100%" class="tablecontents" colspan="3" style="text-align: left;padding-left: 10px;"><div class="headline">No one has gotten ' . $SoldierName . '\'s tags.</div></td>
	</tr>
	';
}
echo '</table>';
?>