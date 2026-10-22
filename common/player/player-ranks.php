<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../functions.php');
require_once('../case.php');
// default variables to null
$ServerID = null;
$PlayerID = null;
// get values
if(!empty($sid))
{
	$ServerID = $sid;
}
if(!empty($pid))
{
	$PlayerID = $pid;
}
if(!empty($_GET['server']))
{
	$ServerName = mysqli_real_escape_string($BF4stats, strip_tags($_GET['server']));
	echo '
	<table class="prettytable">
	<tr>
	<td class="tablecontents" colspan="6"><center>Ranks in ' . $ServerName . '</center></td>
	</tr>
	<tr>
	';
}
else
{
	echo '
	<table class="prettytable">
	<tr>
	<td class="tablecontents" colspan="6"><center>Ranks in ' . $clan_name . '\'s Servers</center></td>
	</tr>
	<tr>
	';
}

// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// get this player's ranks
	// input as: server id, soldier, db, game id
	rank($ServerID, $valid_ids, $PlayerID, $BF4stats, $GameID, $cr);
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
	<div style="position: relative;">
	<div id="cache_refresh" style="position: absolute; top: 10px; left: -25px; vertical-align: middle; display: none;">
	<center><a href="' . $refresh_link . '"><img src="./common/images/refresh.png" alt="refresh" /></a></center>
	</div>
	<script type="text/javascript">
	$("#cache_refresh").delay(4000).fadeIn("slow");
	</script>
	</div>
	';
}
// or else this is a global stats page
else
{
	// get this player's ranks
	// input as: server id, soldier, db, game id
	rank($ServerID, $valid_ids, $PlayerID, $BF4stats, $GameID, $cr);
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
	<div style="position: relative;">
	<div id="cache_refresh" style="position: absolute; top: 10px; left: -25px; vertical-align: middle; display: none;">
	<center><a href="' . $refresh_link . '"><img src="./common/images/refresh.png" alt="refresh" /></a></center>
	</div>
	<script type="text/javascript">
	$("#cache_refresh").delay(4000).fadeIn("slow");
	</script>
	</div>
	';
}
echo '
</tr>
</table>
';
?>