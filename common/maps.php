<?php
// server maps stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// get current rank query details
if(!empty($rank))
{
	// filter out SQL injection
	if($rank != 'Gamemode')
	{
		// unexpected input detected
		// use default instead
		$rank = 'Gamemode';
	}
}
// set default if no rank provided in URL
else
{
	$rank = 'Gamemode';
}
// get current order query details
if(!empty($order))
{
	// filter out SQL injection
	if($order != 'DESC' AND $order != 'ASC')
	{
		// unexpected input detected
		// use default instead
		$order = 'ASC';
		$nextorder = 'DESC';
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
	$order = 'ASC';
	$nextorder = 'DESC';
}
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	// query for maps in this server
	$Mode_q = @mysqli_query($BF4stats,"
		SELECT `Gamemode`
		FROM `tbl_mapstats`
		WHERE `ServerID` = {$ServerID}
		AND `Gamemode` != ''
		GROUP BY `Gamemode`
		ORDER BY {$rank} {$order}
	");
}
// or else this is a global stats page
else
{
	// merge server IDs array into a variable
	$ids = join(',',$ServerIDs);
	
	// query for maps in this server
	$Mode_q = @mysqli_query($BF4stats,"
		SELECT `Gamemode`
		FROM `tbl_mapstats`
		WHERE `ServerID` in ({$ids})
		AND `Gamemode` != ''
		GROUP BY `Gamemode`
		ORDER BY {$rank} {$order}
	");
}
if(@mysqli_num_rows($Mode_q) == 0)
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '
		<div class="subsection">
		<div class="headline">No map stats found for this server.</div>
		</div>
		';
	}
	// or else this is a global stats page
	else
	{
		echo '
		<div class="subsection">
		<div class="headline">No map stats found for these servers.</div>
		</div>
		';
	}
}
else
{
	echo '
	<table class="prettytable">
	<tr>
	<td class="tablecontents">
	';
	// include maps.php image contents
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<br/><center><div class="embed"><br/><img src="pchart/maps.php?sid=' . $ServerID . '" alt="maps played" title="maps played" /></div></center><br/>';
	}
	// or else this is a global stats page
	else
	{
		echo '<br/><center><div class="embed"><br/><img src="pchart/maps.php" alt="maps played" title="maps played" /></div></center><br/>';
	}
	echo '
	</td>
	</tr>
	</table>
	<br/>
	<table class="prettytable">
	<tr>
	<td class="tablecontents">
	<center>Average Polularity is calculated as average players in the server for each map divided by average players leaving for each map.  Higher is better.</center>
	</td>
	</tr>
	<tr>
	<td width="100%" style="text-align: left; height: 5px;" colspan="7"></td>
	</tr>
	</table>
	<table class="prettytable" width="98%" align="center" border="0">
	<tr>
	<th width="5%" class="countheader">#</th>
	<th width="16%" style="text-align:left">Map Name</th>
	<th width="16%" style="text-align:left;">Map Code</th>
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo '<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?sid=' . $ServerID . '&amp;p=maps&amp;r=Gamemode&amp;o=';
	}
	// or else this is a global stats page
	else
	{
		echo '<th width="16%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?p=maps&amp;r=Gamemode&amp;o=';
	}
	if($rank != 'Gamemode')
	{
		echo 'ASC"><span class="orderheader">Game Mode</span></a></th>';
	}
	else
	{
		echo $nextorder . '"><span class="ordered' . $order . 'header">Game Mode</span></a></th>';
	}
	echo '
	<th width="15%" style="text-align:left;">Rounds Played</th>
	<th width="16%" style="text-align:left;">Average Players</th>
	<th width="16%" style="text-align:left;">Average Popularity</th>
	</tr>
	<tr>
	<td width="100%" style="text-align: left; height: 5px;" colspan="7"></td>
	</tr>
	';
	// initialize value
	$count = 0;
	$match = 0;
	$last_mode = 0;
	while($Mode_r = @mysqli_fetch_assoc($Mode_q))
	{
		$Mode = $Mode_r['Gamemode'];
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			// query for game modes for each map
			$Map_q = @mysqli_query($BF4stats,"
				SELECT `MapName`, SUM(`NumberofRounds`) AS NumberofRounds, AVG(`AvgPlayers`) AS AveragePlayers, (AVG(`AvgPlayers`)/AVG(`PlayersLeftServer`)) AS AVGPop
				FROM `tbl_mapstats`
				WHERE `ServerID` = {$ServerID}
				AND `Gamemode` = '{$Mode}'
				AND `MapName` != ''
				GROUP BY `MapName`
				ORDER BY NumberofRounds DESC
			");
		}
		// or else this is a global stats page
		else
		{
			// merge server IDs array into a variable
			$ids = join(',',$ServerIDs);
			
			// query for game modes for each map
			$Map_q = @mysqli_query($BF4stats,"
				SELECT `MapName`, SUM(`NumberofRounds`) AS NumberofRounds, AVG(`AvgPlayers`) AS AveragePlayers, (AVG(`AvgPlayers`)/AVG(`PlayersLeftServer`)) AS AVGPop
				FROM `tbl_mapstats`
				WHERE `ServerID` in ({$ids})
				AND `Gamemode` = '{$Mode}'
				AND `MapName` != ''
				GROUP BY `MapName`
				ORDER BY NumberofRounds DESC
			");
		}
		if(@mysqli_num_rows($Map_q) != 0)
		{
			$match = 1;
			while($Map_r = @mysqli_fetch_assoc($Map_q))
			{
				$NumberofRounds = $Map_r['NumberofRounds'];
				$MapCode = $Map_r['MapName'];
				// convert map to friendly name
				// first find if this map name is even in the map array
				if(in_array($MapCode,$map_array))
				{
					$MapName = array_search($MapCode,$map_array);
				}
				// this map is missing!
				else
				{
					$MapName = $MapCode;
				}
				// convert mode to friendly name
				if(in_array($Mode,$mode_array))
				{
					$GameMode = array_search($Mode,$mode_array);
				}
				// this mode is missing!
				else
				{
					$GameMode = $Mode;
				}
				$AveragePlayers = round($Map_r['AveragePlayers'],2);
				// don't show average popularity if sample size is small
				if($NumberofRounds <= 4)
				{
					$AveragePopularity = '<span class="information">not enough data</span>';
				}
				else
				{
					$AveragePopularity = round($Map_r['AVGPop'],2);
				}
				$count++;
				// add a space between mode changes
				if($last_mode !== $Mode AND $last_mode !== 0)
				{
					echo '<tr><td width="100%" style="text-align: left; height: 5px;" colspan="7"></td></tr>';
					$last_mode = $Mode;
				}
				echo '
				<tr>
				<td width="5%" class="count"><span class="information">' . $count . '</span></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><span class="information">' . $MapName . '</span></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><span class="information">' . $MapCode . '</span></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><span class="information">' . $GameMode . '</span></td>
				<td width="15%" class="tablecontents" style="text-align: left;"><span class="information">' . $NumberofRounds . '</span></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><span class="information">' . $AveragePlayers . '</span></td>
				<td width="16%" class="tablecontents" style="text-align: left;"><span class="information">' . $AveragePopularity . '</span></td>
				</tr>
				';
			}
		}
		$last_mode = $Mode;
	}
	// free up map query memory
	@mysqli_free_result($Map_q);
	if($match == 0)
	{
		echo '
		<tr>
		<td width="5%" class="count">&nbsp;</td>
		<td width="95%" class="tablecontents" style="text-align: left;" colspan="6"><span class="information">No information found.</span></td>
		</tr>
		';
	}
	echo '
	</table>
	';
}
// free up mode query memory
@mysqli_free_result($Mode_q);

?>
