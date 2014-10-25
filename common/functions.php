<?php
// functions for server stats page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// function to find player's weapon stats
function Statsout($headingprint, $damagetype, $weapon_array, $PlayerID, $ServerID, $BF4stats)
{
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		// see if this player has used this category's weapons
		$Weapon_q = @mysqli_query($BF4stats,"
			SELECT tws.`Friendlyname`, wa.`Kills`, wa.`Deaths`, wa.`Headshots`, wa.`WeaponID`, (wa.`Headshots`/wa.`Kills`) AS HSR
			FROM `tbl_weapons_stats` wa
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`PlayerID` = {$PlayerID}
			AND tws.`Damagetype` = '{$damagetype}'
			AND wa.`Kills` > 0
			ORDER BY Kills DESC
		");
	}
	// or else this is a global stats page
	else
	{
		// see if this player has used this category's weapons
		$Weapon_q = @mysqli_query($BF4stats,"
			SELECT tws.`Friendlyname`, SUM(wa.`Kills`) AS Kills, SUM(wa.`Deaths`) AS Deaths, SUM(wa.`Headshots`) AS Headshots, wa.`WeaponID`, (SUM(wa.`Headshots`)/SUM(wa.`Kills`)) AS HSR
			FROM `tbl_weapons_stats` wa
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = wa.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			INNER JOIN `tbl_weapons` tws ON tws.`WeaponID` = wa.`WeaponID`
			WHERE tpd.`PlayerID` = {$PlayerID}
			AND tws.`Damagetype` = '{$damagetype}'
			AND wa.`Kills` > 0
			GROUP BY tws.`Friendlyname`
			ORDER BY Kills DESC
		");
	}
	// see if we have any records for this player for this category
	if(@mysqli_num_rows($Weapon_q) != 0)
	{
		echo '
		<table class="prettytable">
		<tr>
		<th width="23%" style="text-align:left;padding-left: 10px;">Weapon Name</th>
		<th width="19%" style="text-align:left;padding-left: 5px;"><span class="orderedDESCheader">Kills</span></th>
		<th width="19%" style="text-align:left;padding-left: 10px;">Deaths</th>
		<th width="19%" style="text-align:left;padding-left: 10px;">Headshots</th>
		<th width="20%" style="text-align:left;padding-left: 10px;">Headshot Ratio</th>
		</tr>
		';
		while($Weapon_r = @mysqli_fetch_assoc($Weapon_q))
		{
			$weapon = $Weapon_r['Friendlyname'];
			// rename 'Death'
			if($weapon == 'Death')
			{
				$weapon = 'Machinery';
			}
			// convert weapon to friendly name
			if(in_array($weapon,$weapon_array))
			{
				$weapon_name = array_search($weapon,$weapon_array);
				$weapon_img = './images/weapons/' . $weapon . '.png';
			}
			// this weapon is missing!
			else
			{
				$weapon_name = preg_replace("/_/"," ",$weapon);
				$weapon_img = './images/weapons/missing.png';
			}
			$kills = $Weapon_r['Kills'];
			$deaths = $Weapon_r['Deaths'];
			$headshots = $Weapon_r['Headshots'];
			$ratio = round(($Weapon_r['HSR']*100),2);
			$weaponID = $Weapon_r['WeaponID'];
			echo '
			<tr>
			<td width="23%" class="tablecontents"  style="text-align: left;"><table width="100%" border="0"><tr><td width="120px"><img src="'. $weapon_img . '" alt="' . $weapon_name . '" /></td><td style="text-align: left;" valign="middle"><font class="information">' . $weapon_name . '</font></td></tr></table></td>
			<td width="19%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $kills . '</td>
			<td width="19%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $deaths . '</td>
			<td width="19%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $headshots . '</td>
			<td width="20%" class="tablecontents" style="text-align: left;padding-left: 10px;">' . $ratio . ' <font class="information">%</font></td>
			</tr>
			';
		}
		// free up weapon query memory
		@mysqli_free_result($Weapon_q);
		echo '
		</table>
		';
	}
}

// rank queries function for player stats page
function rank($ServerID, $PlayerID, $BF4stats, $GameID)
{
	// initialize KDR rank values
	$KDRrank = 0;
	$KDRmatch = 0;
	// rank players by KDR
	$KDR_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`PlayerID`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
			ORDER BY (tps.`Kills`/tps.`Deaths`) DESC, tpd.`SoldierName` ASC
	");
	// loop through the list until this player's ID is found
	if(@mysqli_num_rows($KDR_q) != 0)
	{
		while($KDR_r = @mysqli_fetch_assoc($KDR_q))
		{
			$KDRrank++;
			$ThisID = strtolower($KDR_r['PlayerID']);
			// if player name in rank row matches player of interest
			if($PlayerID == $ThisID)
			{
					$KDRmatch = 1;
					break;
			}
		}
	}
	// free up KDR rank query memory
	@mysqli_free_result($KDR_q);
	
	// initialize HSR rank values
	$HSRrank = 0;
	$HSRmatch = 0;
	// rank players by HSR
	$HSR_q  = @mysqli_query($BF4stats,"
			SELECT tpd.`PlayerID`
			FROM `tbl_playerstats` tps
			INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
			INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
			WHERE tsp.`ServerID` = {$ServerID}
			AND tpd.`GameID` = {$GameID}
			ORDER BY (tps.`Headshots`/tps.`Kills`) DESC, tpd.`SoldierName` ASC
	");
	// loop through the list until this player's ID is found
	if(@mysqli_num_rows($HSR_q) != 0)
	{
		while($HSR_r = @mysqli_fetch_assoc($HSR_q))
		{
			$HSRrank++;
			$ThisID = strtolower($HSR_r['PlayerID']);
			// if player name in rank row matches player of interest
			if($PlayerID == $ThisID)
			{
					$HSRmatch = 1;
					break;
			}
		}
	}
	// free up HSR rank query memory
	@mysqli_free_result($HSR_q);
	
	// look for error in queries above
	if($KDRmatch == 0)
	{
		$KDRrank = 'error';
	}
	if($HSRmatch == 0)
	{
		$HSRrank = 'error';
	}
	
	// get player database ranks
	$Rank_q  = @mysqli_query($BF4stats,"
		SELECT `rankScore`, `rankKills`
		FROM `tbl_playerstats` tps
		INNER JOIN `tbl_server_player` tsp ON tsp.`StatsID` = tps.`StatsID`
		INNER JOIN `tbl_playerdata` tpd ON tsp.`PlayerID` = tpd.`PlayerID`
		WHERE tpd.`PlayerID` = {$PlayerID}
		AND tsp.`ServerID` = {$ServerID}
	");
	// query worked
	if(@mysqli_num_rows($Rank_q) != 0)
	{
		$Rank_r = @mysqli_fetch_assoc($Rank_q);
		$ScoreRank = round($Rank_r['rankScore'],0);
		$KillsRank = round($Rank_r['rankKills'],0);
	}
	// error occured
	else
	{
		$ScoreRank = 'Unknown';
		$KillsRank = 'Unknown';
	}
	// query for player count
	$Server_q = @mysqli_query($BF4stats,"
		SELECT `CountPlayers`
		FROM `tbl_server_stats`
		WHERE `ServerID` = {$ServerID}
	");
	// query worked
	if(@mysqli_num_rows($Server_q) != 0)
	{
		$Server_r = @mysqli_fetch_assoc($Server_q);
		$Players = $Server_r['CountPlayers'];
	}
	// error occured
	else
	{
		$Players = 'Unknown';
	}
	echo '
	<td width="25%" class="tablecontents" style="padding-left: 10px;"><span class="information">Score</span></td>
	<td width="25%" class="tablecontents" style="padding-left: 10px;"><span class="information">Kills</span></td>
	<td width="25%" class="tablecontents" style="padding-left: 10px;"><span class="information">KDR</span></td>
	<td width="25%" class="tablecontents" style="padding-left: 10px;"><span class="information">Headshot Percent</span></td>
	</tr>
	<tr>
	<td width="25%" class="tablecontents" style="padding-left: 10px;"><span class="information">#</span> ' . $ScoreRank . ' <span class="information">of</span> ' . $Players . '</td>
	<td width="25%" class="tablecontents" style="padding-left: 10px;"><span class="information">#</span> ' . $KillsRank . ' <span class="information">of</span> ' . $Players . '</td>
	<td width="25%" class="tablecontents" style="padding-left: 10px;"><span class="information">#</span> ' . $KDRrank . ' <span class="information">of</span> ' . $Players . '</td>
	<td width="25%" class="tablecontents" style="padding-left: 10px;"><span class="information">#</span> ' . $HSRrank . ' <span class="information">of</span> ' . $Players . '</td>
	';
	// free up player rank query memory
	@mysqli_free_result($Rank_q);
	// free up server query memory
	@mysqli_free_result($Server_q);
}

// function to create pagination links
function pagination_links($ServerID,$root,$page,$currentpage,$totalpages,$rank,$order,$query)
{
	echo '<div class="pagination">';
	// reduce pagination width if few page results were found
	if($totalpages == 1)
	{
		echo '<table class="prettytable" style="width: 10%">';
	}
	elseif($totalpages <= 3 && $totalpages >= 2)
	{
		echo '<table class="prettytable" style="width: 30%">';
	}
	else
	{
		echo '<table class="prettytable" style="width: 60%">';
	}
	echo '<tr>';
	// range of number of links to show
	// the range changes at the lowest and highest numbers to make the number of link outputs the same
	// low end
	if($currentpage == 4)
	{
		$range = 4;
	}
	elseif($currentpage == 3)
	{
		$range = 5;
	}
	elseif($currentpage == 2)
	{
		$range = 6;
	}
	elseif($currentpage == 1)
	{
		$range = 7;
	}
	// high end
	elseif($currentpage == ($totalpages - 3))
	{
		$range = 4;
	}
	elseif($currentpage == ($totalpages - 2))
	{
		$range = 5;
	}
	elseif($currentpage == ($totalpages - 1))
	{
		$range = 6;
	}
	elseif($currentpage == $totalpages)
	{
		$range = 7;
	}
	// the default if not at the low or high end
	else
	{
		$range = 3;
	}
	// if on page 1, don't show earlier page links
	if ($currentpage > 1)
	{
		// show first page link to go back to first page
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			if(!empty($query))
			{
				echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=1&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">1</a></td>';
			}
			else
			{
				echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=1&amp;r=' . $rank . '&amp;o=' . $order . '">1</a></td>';
			}
		}
		// or else this is a global stats page
		else
		{
			if(!empty($query))
			{
				echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=1&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">1</a></td>';
			}
			else
			{
				echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=1&amp;r=' . $rank . '&amp;o=' . $order . '">1</a></td>';
			}
		}
		// get previous page number
		$prevpage = $currentpage - 1;
		// show ... as spacer if beyond the first pages
		if (($currentpage - $range) > 3)
		{
			echo ' <td width="9%" class="pagspace">...</td> ';
		}
		// show page 2 instead of ... if the ... would have represented page 2 anyways
		elseif (($currentpage - $range) == 3)
		{
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				if(!empty($query))
				{
					echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=2&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">2</a></td>';
				}
				else
				{
					echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=2&amp;r=' . $rank . '&amp;o=' . $order . '">2</a></td>';
				}
			}
			// or else this is a global stats page
			else
			{
				if(!empty($query))
				{
					echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=2&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">2</a></td>';
				}
				else
				{
					echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=2&amp;r=' . $rank . '&amp;o=' . $order . '">2</a></td>';
				}
			}
		}
	}
	// loop to show links to pages in a range of pages around current page
	for($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++)
	{
		// handle the first and last pages differently
		if ((($x == 1) || ($x == $totalpages)) && ($x == $currentpage))
		{
			// 'highlight' the current page but don't make it a link
			echo ' <td width="9%" class="pagcountselected"><font class="information">' . $x . '</font></td> ';
		}
		// if it's a valid page number... and isn't the first or last page
		if (($x > 1) && ($x < $totalpages))
		{
			// if we're on current page...
			if ($x == $currentpage)
			{
				// 'highlight' the current page but don't make it a link
				echo ' <td width="9%" class="pagcountselected"><font class="information">' . $x . '</font></td> ';
			}
			else
			{
				// make it a link
				// if there is a ServerID, this is a server stats page
				if(!empty($ServerID))
				{
					if(!empty($query))
					{
						echo ' <td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=' . $x . '&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">' . $x . '</a></td> ';
					}
					else
					{
						echo ' <td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=' . $x . '&amp;r=' . $rank . '&amp;o=' . $order . '">' . $x . '</a></td> ';
					}
				}
				// or else this is a global stats page
				else
				{
					if(!empty($query))
					{
						echo ' <td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=' . $x . '&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">' . $x . '</a></td> ';
					}
					else
					{
						echo ' <td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=' . $x . '&amp;r=' . $rank . '&amp;o=' . $order . '">' . $x . '</a></td> ';
					}
				}
			}
		}
	}
	// if not on last page, show forward links        
	if ($currentpage != $totalpages)
	{
		// get next page
		$nextpage = $currentpage + 1;
		// show ... as spacer if before the last pages
		if (($currentpage + $range) < ($totalpages - 2))
		{
			echo ' <td width="9%" class="pagspace">...</td> ';
		}
		// show 2nd-to-last page instead of ... if the ... would have represented 2nd-to-last page anyways
		elseif(($currentpage + $range) == ($totalpages - 2))
		{
			$onelesstotalpages = $totalpages - 1;
			// if there is a ServerID, this is a server stats page
			if(!empty($ServerID))
			{
				if(!empty($query))
				{
					echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=' . $onelesstotalpages . '&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">' . $onelesstotalpages . '</a></td>';
				}
				else
				{
					echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=' . $onelesstotalpages . '&amp;r=' . $rank . '&amp;o=' . $order . '">' . $onelesstotalpages . '</a></td>';
				}
			}
			// or else this is a global stats page
			else
			{
				if(!empty($query))
				{
					echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=' . $onelesstotalpages . '&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">' . $onelesstotalpages . '</a></td>';
				}
				else
				{
					echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=' . $onelesstotalpages . '&amp;r=' . $rank . '&amp;o=' . $order . '">' . $onelesstotalpages . '</a></td>';
				}
			}
		}
		// show last page link
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			if(!empty($query))
			{
				echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=' . $totalpages . '&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">' . $totalpages . '</a></td>';
			}
			else
			{
				echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;sid=' . $ServerID . '&amp;cp=' . $totalpages . '&amp;r=' . $rank . '&amp;o=' . $order . '">' . $totalpages . '</a></td>';
			}
		}
		// or else this is a global stats page
		else
		{
			if(!empty($query))
			{
				echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=' . $totalpages . '&amp;r=' . $rank . '&amp;o=' . $order . '&amp;q=' . $query . '">' . $totalpages . '</a></td>';
			}
			else
			{
				echo '<td width="9%" class="pagcount"><a class="fill-div" href="' . $root . '?p=' . $page . '&amp;cp=' . $totalpages . '&amp;r=' . $rank . '&amp;o=' . $order . '">' . $totalpages . '</a></td>';
			}
		}
	}
	echo '
	</tr>
	</table>
	</div>
	';
}

// function to replace dangerous characters in content
function textcleaner($content)
{
	$content = preg_replace("/&/","&amp;",$content);
	$content = preg_replace("/'/","&#39;",$content);
	$content = preg_replace("/</","&lt;",$content);
	$content = preg_replace("/>/","&gt;",$content);
	return $content;
}

?>
