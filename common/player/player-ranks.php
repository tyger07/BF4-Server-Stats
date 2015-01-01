<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

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
	// remove accidental spaces from name input
	$ServerName = mysqli_real_escape_string($BF4stats, $_GET['server']);
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
	rank($ServerID, $valid_ids, $PlayerID, $BF4stats, $GameID);
}
// or else this is a global stats page
else
{
	// get this player's ranks
	// input as: server id, soldier, db, game id
	rank($ServerID, $valid_ids, $PlayerID, $BF4stats, $GameID);
}
echo '
</tr>
</table>
';
?>