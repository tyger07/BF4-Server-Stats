<?php
// server stats display servers page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// initialize values
$now_timestamp = time();
$old = $now_timestamp - 1800;
// go through each detected server ID
foreach($ServerIDs as $this_ServerID)
{
	// get server stats
	$Stats_q = @mysqli_query($BF4stats,"
		SELECT `CountPlayers`, `SumScore`, `SumKills`
		FROM tbl_server_stats
		WHERE `ServerID` = {$this_ServerID}
	");
	if(@mysqli_num_rows($Stats_q) != 0)
	{
		$Stats_r = @mysqli_fetch_assoc($Stats_q);
		$total_players = $Stats_r['CountPlayers'];
		$score = $Stats_r['SumScore'];
		$kills = $Stats_r['SumKills'];
	}
	// some sort of error occured
	else
	{
		$total_players = 'Unknown';
		$score = 'Unknown';
		$kills = 'Unknown';
	}
	// free up stats query memory
	@mysqli_free_result($Stats_q);
	// get current players
	$CurrentPlayers_q = @mysqli_query($BF4stats,"
		SELECT count(`TeamID`) AS count
		FROM tbl_currentplayers
		WHERE `ServerID` = {$this_ServerID}
	");
	if(@mysqli_num_rows($CurrentPlayers_q) != 0)
	{
		$CurrentPlayers_r = @mysqli_fetch_assoc($CurrentPlayers_q);
		$players = $CurrentPlayers_r['count'];
	}
	// some sort of error occured
	else
	{
		$players = 'Unknown';
	}
	// free up stats query memory
	@mysqli_free_result($CurrentPlayers_q);
	// get current map
	$CurrentMap_q = @mysqli_query($BF4stats,"
		SELECT `mapName`, `ServerName`
		FROM tbl_server
		WHERE `ServerID` = {$this_ServerID}
	");
	if(@mysqli_num_rows($CurrentMap_q) != 0)
	{
		$CurrentMap_r = @mysqli_fetch_assoc($CurrentMap_q);
		$map = $CurrentMap_r['mapName'];
		$ServerName = $CurrentMap_r['ServerName'];
		// convert map to friendly name
		// first find if this map name is even in the map array
		if(in_array($map,$map_array))
		{
			$map_name = array_search($map,$map_array);
			// compile map image
			$map_img = './images/maps/' . $map . '.png';
		}
		// this map is missing!
		else
		{
			$map_img = './images/40.png';
			$map_name = $map;
		}
	}
	// some sort of error occured
	else
	{
		$map = 'Unknown';
		$ServerName = 'Unknown';
		$map_img = './images/40.png';
	}
	// free up stats query memory
	@mysqli_free_result($CurrentMap_q);
	// find if there are sessions older than 30 minutes
	// do this to avoid optimizing the table (slow) every page load
	$old_query = @mysqli_query($BF4stats,"
		SELECT `timestamp`
		FROM `ses_{$this_ServerID}_tbl`
		WHERE `timestamp` <= {$old}
	");
	if(@mysqli_num_rows($old_query) != 0)
	{
		// remove sessions older than 30 minutes
		@mysqli_query($BF4stats,"
			DELETE FROM `ses_{$this_ServerID}_tbl`
			WHERE `timestamp` <= {$old}
		");
		@mysqli_query($BF4stats,"
			OPTIMIZE TABLE `ses_{$this_ServerID}_tbl`
		");
	}
	// free up old query memory
	@mysqli_free_result($old_query);
	// count sessions
	$Session_q = @mysqli_query($BF4stats,"
		SELECT count(`IP`) AS count
		FROM ses_{$this_ServerID}_tbl
		WHERE 1
	");
	if(@mysqli_num_rows($Session_q) != 0)
	{
		$Session_r = @mysqli_fetch_assoc($Session_q);
		$sessions = $Session_r['count'];
	}
	// some sort of error occured
	else
	{
		$sessions = 'Unknown';
	}
	// free up stats query memory
	@mysqli_free_result($Session_q);
	echo '
	<tr>
	<td width="100%" style="text-align: left;">
	<br/>
	<div class="mapimage" style="background-image: url(' . $map_img . '); width: 98%;">
	<div class="simplecontent">
	<table width="95%" align="center" border="0">
	<tr>
	<td width="35%">
	<br/><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $this_ServerID . '"><font size="3">' . $ServerName . '</a></font><br/><br/>
	</td>
	<td width="22%">
	<br/><br/><font class="information">Current Players In Server:</font> ' . $players . '<br/><br/>
	</td>
	<td width="22%">
	<br/><br/><font class="information">Current Map:</font> ' . $map_name . '<br/><br/>
	</td>
	<td width="21%">
	<br/><br/><font class="information">Users Viewing Stats:</font> ' . $sessions . '<br/><br/>
	</td>
	</tr>
	<tr>
	<td width="35%">
	<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $this_ServerID . '"><img src="./images/viewstatsbtn.png" alt="view stats"/></a> &nbsp;
	<a href="http://battlelog.battlefield.com/bf4/servers/pc/?filtered=1&amp;expand=0&amp;useAdvanced=1&amp;q=' . preg_replace('/\+/','%2B',$ServerName) . '" target="_blank"><img src="./images/joinbtn.png" alt="join"/></a><br/><br/>
	</td>
	<td width="22%">
	<font class="information">Players Logged:</font> ' . $total_players . '<br/><br/><br/>
	</td>
	<td width="22%">
	<font class="information">Total Score:</font> ' . $score . '<br/><br/><br/>
	</td>
	<td width="21%">
	<font class="information">Total Kills:</font> ' . $kills . '<br/><br/><br/>
	</td>
	</tr>
	</table>
	</div>
	</div>
	</td>
	</tr>
	';
}
?>