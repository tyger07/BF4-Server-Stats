<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// include required files
require_once('../../config/config.php');
require_once('../functions.php');
require_once('../connect.php');
require_once('../case.php');
// default variable to null
$ServerID = null;
// get value
if(!empty($sid))
{
	$ServerID = $sid;
}
// initialize value as null
$date_query = null;
// lets see if the query input could possibly be a date/timestamp
if(!empty($query) && !(($timestamp = strtotime($query)) === false))
{
	// convert to compatible timestamp
	$date_query = date("Y-m-d H:i",strtotime($query));
	// find timestamp for now and find the timestamp of the query
	$now = time();
	$compare = strtotime($date_query);
	// find the difference between the two and make sure negative numbers (in the future) are filtered out to 0
	$difference = max(($now - $compare),0);
	// in the future? that doesn't make sense! null out the date query
	if($difference == 0)
	{
		$date_query = null;
	}
	// set low and high filter depending on how far it is from now
	// or depending on the keywords
	else
	{
		// difference is less than an hour ago. show the last hour
		if($difference < 3600)
		{
			$low = date("Y-m-d H:i",($now - 3600));
			$high = date("Y-m-d H:i",$now);
		}
		// query contains 'week' so show a week worth of data
		elseif(stristr($query, 'week') !== FALSE)
		{
			$low = date("Y-m-d",strtotime($query)) . ' 00:01';
			$high = date("Y-m-d",(strtotime($query) + 518400)) . ' 23:59';
		}
		// query contains 'month' so show a month worth of data
		elseif(stristr($query, 'month') !== FALSE)
		{
			$low = date("Y-m-d",strtotime($query)) . ' 00:01';
			$high = date("Y-m-d",(strtotime($query) + 2592000)) . ' 23:59';
		}
		// query contains 'year' so show a year worth of data
		elseif(stristr($query, 'year') !== FALSE)
		{
			$low = date("Y-m-d",strtotime($query)) . ' 00:01';
			$high = date("Y-m-d",(strtotime($query) + 31536000)) . ' 23:59';
		}
		// query contains a specific time of day so filter within 10 minutes of entered time
		elseif(stristr($query, ':') !== FALSE)
		{
			$low = date("Y-m-d H:i",(strtotime($query)) - 300);
			$high = date("Y-m-d H:i",(strtotime($query) + 300));
		}
		// filter within a 1 day by default
		else
		{
			$low = date("Y-m-d",strtotime($query)) . ' 00:01';
			$high = date("Y-m-d",strtotime($query)) . ' 23:59';
		}
		// double check that $high is not in the future
		$compare = strtotime($high);
		$difference = max(($now - $compare),0);
		if($difference == 0)
		{
			$high = date("Y-m-d H:i",$now);
		}
	}
}
// updating text...
// hidden by default until time is reached
echo '
<div id="fadein" style="position: absolute; top: -31px; left: -150px; display: none;">
<div class="subsection" style="width: 100px;">
<center>Updating ...<span style="float:right;"><img class="update" src="./common/images/loading.gif" alt="loading" /></span></center>
</div>
</div>
';
// last updated text...
// shown by default until faded away
echo '
<div id="fadeaway" style="position: absolute; top: -31px; left: -150px;">
<div class="subsection" style="width: 100px;">
<center>Updated <span id="timestamp"></span></center>
</div>
</div>
';
// find out client's current time with javascript
// and fadeaway javascript
// and fadein javascript
echo '
<script type="text/javascript">
var date = new Date();
var hours = date.getHours();
var minutes = date.getMinutes();
if (hours.toString().length == 1)
{
	hours = "0" + hours;
}
if (minutes.toString().length == 1)
{
	minutes = "0" + minutes;
}
document.getElementById("timestamp").innerHTML = hours + \':\' + minutes;
$("#fadeaway").finish().show().delay(2000).fadeOut("slow");
$("#fadein").delay(59000).fadeIn("slow");
</script>
';
// show current search content
if(!empty($query))
{
	echo '
	<div class="subsection" style="margin-top: -2px; margin-bottom: 4px;">
	';
	if(empty($date_query))
	{
		echo '
		Player named: <span class="information">' . $query . '</span>
		<br/>
		Message containing: <span class="information">' . $query . '</span>
		';
	}
	else
	{
		if(!empty($order) && $order == DESC)
		{
			echo '
			Message dated: <span class="information">' . date("H:i F j, Y",strtotime($high)) . '</span> through <span class="information">' . date("H:i F j, Y",strtotime($low)) . '</span>
			';
		}
		else
		{
			echo '
			Message dated: <span class="information">' . date("H:i F j, Y",strtotime($low)) . '</span> through <span class="information">' . date("H:i F j, Y",strtotime($high)) . '</span>
			';
		}
	}
	echo '
	</div>
	';
}
// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
// find out how many rows are in the table
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	if(!empty($query))
	{
		if(!empty($date_query))
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT count(`ID`) AS count
				FROM `tbl_chatlog`
				WHERE `ServerID` = {$ServerID}
				AND `logDate` BETWEEN '{$low}' AND '{$high}'
			");
			$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
			$numrows = $TotalRows_r['count'];
		}
		else
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT count(`ID`) AS count
				FROM `tbl_chatlog`
				WHERE `ServerID` = {$ServerID}
				AND (`logMessage` LIKE '%{$query}%'
				OR `logSoldierName` LIKE '%{$query}%')
			");
			$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
			$numrows = $TotalRows_r['count'];
		}
	}
	else
	{
		echo '<div style="position: relative;">';
		$numrows = cache_total_chat($ServerID, $valid_ids, $GameID, $BF4stats);
		echo '</div>';
	}
}
// or else this is a global stats page
else
{	
	if(!empty($query))
	{
		if(!empty($date_query))
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT count(`ID`) AS count
				FROM `tbl_chatlog`
				WHERE `ServerID` IN ({$valid_ids})
				AND `logDate` BETWEEN '{$low}' AND '{$high}'
			");
			$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
			$numrows = $TotalRows_r['count'];
		}
		else
		{
			$TotalRows_q = @mysqli_query($BF4stats,"
				SELECT count(`ID`) AS count
				FROM `tbl_chatlog`
				WHERE `ServerID` IN ({$valid_ids})
				AND (`logMessage` LIKE '%{$query}%'
				OR `logSoldierName` LIKE '%{$query}%')
			");
			$TotalRows_r = @mysqli_fetch_assoc($TotalRows_q);
			$numrows = $TotalRows_r['count'];
		}
	}
	else
	{
		echo '<div style="position: relative;">';
		$numrows = cache_total_chat($ServerID, $valid_ids, $GameID, $BF4stats);
		echo '</div>';
	}
}
// number of pagination rows to show per page
$rowsperpage = 20;
// find out total pagination pages
$totalpages = ceil($numrows / $rowsperpage);
// set current pagination page to default if none provided
if(empty($currentpage))
{
	// default page num
	$currentpage = 1;
}
// if current pagination page is greater than total pages...
if($currentpage > $totalpages)
{
	// set current pagination page to last page
	$currentpage = $totalpages;
}
// if current pagination page is less than first page...
if($currentpage < 1)
{
	// set current pagination page to first page
	$currentpage = 1;
}
// get current rank query details
if(!empty($rank))
{
	// filter out invalid options
	if($rank != 'logDate' AND $rank != 'logSoldierName' AND $rank != 'Message')
	{
		// unexpected input detected
		// use default instead
		$rank = 'logDate';
	}
}
// set default if no rank provided in URL
else
{
	$rank = 'logDate';
}
// get current order query details
if(!empty($order))
{
	// filter out invalid options
	if($order != 'DESC' AND $order != 'ASC')
	{
		// unexpected input detected
		// use default instead
		$order = 'DESC';
		$nextorder = 'ASC';
	}
	else
	{
		if($order == 'ASC')
		{
			$nextorder = 'DESC';
		}
		else
		{
			$nextorder = 'ASC';
		}
	}
}
// set default if no order provided in URL
else
{
	$order = 'DESC';
	$nextorder = 'ASC';
}
// the pagination offset of the list, based on current page 
$offset = ($currentpage - 1) * $rowsperpage;
// get the info from the db
// if there is a ServerID, this is a server stats page
if(!empty($ServerID))
{
	if(!empty($query))
	{
		if(!empty($date_query))
		{
			$Messages_q = @mysqli_query($BF4stats,"
				SELECT `logDate`, `logSoldierName`, TRIM(`logMessage`) AS Message, `logSubset`
				FROM `tbl_chatlog`
				WHERE `ServerID` = {$ServerID}
				AND `logDate` BETWEEN '{$low}' AND '{$high}'
				ORDER BY {$rank} {$order}, `logDate` DESC
				LIMIT {$offset}, {$rowsperpage}
			");
		}
		else
		{
			$Messages_q = @mysqli_query($BF4stats,"
				SELECT `logDate`, `logSoldierName`, TRIM(`logMessage`) AS Message, `logSubset`
				FROM `tbl_chatlog`
				WHERE `ServerID` = {$ServerID}
				AND (`logMessage` LIKE '%{$query}%'
				OR `logSoldierName` LIKE '%{$query}%')
				ORDER BY {$rank} {$order}, `logDate` DESC
				LIMIT {$offset}, {$rowsperpage}
			");
		}
	}
	else
	{
		$Messages_q = @mysqli_query($BF4stats,"
			SELECT `logDate`, `logSoldierName`, TRIM(`logMessage`) AS Message, `logSubset`
			FROM `tbl_chatlog`
			WHERE `ServerID` = {$ServerID}
			ORDER BY {$rank} {$order}, `logDate` DESC
			LIMIT {$offset}, {$rowsperpage}
		");
	}
}
// or else this is a global stats page
else
{
	if(!empty($query))
	{
		if(!empty($date_query))
		{
			$Messages_q = @mysqli_query($BF4stats,"
				SELECT `logDate`, `logSoldierName`, TRIM(`logMessage`) AS Message, `logSubset`
				FROM `tbl_chatlog`
				WHERE `ServerID` IN ({$valid_ids})
				AND `logDate` BETWEEN '{$low}' AND '{$high}'
				ORDER BY {$rank} {$order}, `logDate` DESC
				LIMIT {$offset}, {$rowsperpage}
			");
		}
		else
		{
			$Messages_q = @mysqli_query($BF4stats,"
				SELECT `logDate`, `logSoldierName`, TRIM(`logMessage`) AS Message, `logSubset`
				FROM `tbl_chatlog`
				WHERE `ServerID` IN ({$valid_ids})
				AND (`logMessage` LIKE '%{$query}%'
				OR `logSoldierName` LIKE '%{$query}%')
				ORDER BY {$rank} {$order}, `logDate` DESC
				LIMIT {$offset}, {$rowsperpage}
			");
		}
	}
	else
	{
		$Messages_q = @mysqli_query($BF4stats,"
			SELECT `logDate`, `logSoldierName`, TRIM(`logMessage`) AS Message, `logSubset`
			FROM `tbl_chatlog`
			WHERE `ServerID` IN ({$valid_ids})
			ORDER BY {$rank} {$order}, `logDate` DESC
			LIMIT {$offset}, {$rowsperpage}
		");
	}
}
// offset pagination count
$count = ($currentpage * 20) - 20;
// check if chat rows were found
if(@mysqli_num_rows($Messages_q) != 0)
{
	echo '
	<table class="prettytable" style="margin-top: -4px;">
	<tr>
	<th width="5%" class="countheader">#</th>
	';
	// date column
	pagination_headers('Date',$ServerID,'chat','15','r',$rank,'logDate','o',$order,'DESC',$nextorder,$currentpage,'','',$query);
	// player column
	pagination_headers('Player',$ServerID,'chat','15','r',$rank,'logSoldierName','o',$order,'ASC',$nextorder,$currentpage,'','',$query);
	// player column
	pagination_headers('Message',$ServerID,'chat','65','r',$rank,'Message','o',$order,'ASC',$nextorder,$currentpage,'','',$query);
	echo '
	</tr>
	</table>
	';
	// while there are rows to be fetched...
	while($Messages_r = @mysqli_fetch_assoc($Messages_q))
	{
		// get data
		$logDate = date("H:i M j, Y", strtotime($Messages_r['logDate']));
		$logSoldierName = textcleaner($Messages_r['logSoldierName']);
		$logMessage = textcleaner($Messages_r['Message']);
		$count++;
		// see if this player has server stats in this server yet
		// if there is a ServerID, this is a server stats page
		if(!empty($ServerID))
		{
			$PlayerID_q = @mysqli_query($BF4stats,"
				SELECT tpd.`PlayerID`
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tpd.`GameID` = {$GameID}
				AND tpd.`SoldierName` = '{$logSoldierName}'
				AND tsp.`ServerID` = {$ServerID}
			");
		}
		// or else this is a global stats page
		else
		{	
			$PlayerID_q = @mysqli_query($BF4stats,"
				SELECT tpd.`PlayerID`
				FROM `tbl_playerdata` tpd
				INNER JOIN `tbl_server_player` tsp ON tsp.`PlayerID` = tpd.`PlayerID`
				INNER JOIN `tbl_playerstats` tps ON tps.`StatsID` = tsp.`StatsID`
				WHERE tpd.`GameID` = {$GameID}
				AND tpd.`SoldierName` = '{$logSoldierName}'
				AND tsp.`ServerID` IN ({$valid_ids})
				GROUP BY tpd.`PlayerID`
			");
		}
		// server stats found for this player in this server
		if(@mysqli_num_rows($PlayerID_q) == 1)
		{
			$PlayerID_r = @mysqli_fetch_assoc($PlayerID_q);
			$PlayerID = $PlayerID_r['PlayerID'];
			$link = './index.php?';
			if(!empty($ServerID))
			{
				$link .= 'sid=' . $ServerID . '&amp;';
			}
			$link .= 'pid=' . $PlayerID . '&amp;p=player';
		}
		// this player needs to finish this round to get server stats in this server
		else
		{
			$PlayerID = null;
		}
		echo '
		<table class="prettytable" style="margin-top: -2px; position: relative;">
		<tr>
		';
		// if this player has stats in this server, provide a link to their stats page
		if(!empty($PlayerID))
		{
			echo '
			<td width="5%" class="count">
			<div style="position: absolute; z-index: 2; width: 100%; height: 100%; top: 0; left: 0; padding: 0px; margin: 0px;">
			<a class="fill-div" style="padding: 0px; margin: 0px;" href="' . $link . '"></a>
			</div>
			<span class="information">' . $count . '</span>
			</td>
			<td width="15%" class="tablecontents">' . $logDate . '</td>
			<td width="15%" class="tablecontents"><a href="' . $link . '">' . $logSoldierName . '</a></td>
			';
		}
		// otherwise just display their name without a link
		else
		{
			echo '
			<td width="5%" class="count"><span class="information">' . $count . '</span></td>
			<td width="15%" class="tablecontents">' . $logDate . '</td>
			<td width="15%" class="tablecontents">' . $logSoldierName . '</td>
			';
		}
		echo '
		<td width="65%" class="tablecontents">' . $logMessage . '</td>
		</tr>
		</table>
		';
	}
	// build the pagination links
	pagination_links($ServerID,'index.php',$page,$currentpage,$totalpages,$rank,$order,$query);
}
else
{
	echo '
	<div class="subsection" style="margin-top: -2px;">
	<div class="headline">
	No relevant chat content found for
	';
	// if there is a ServerID, this is a server stats page
	if(!empty($ServerID))
	{
		echo ' this server.';
	}
	// or else this is a global stats page
	else
	{
		echo ' these servers.';
	}
	echo '
	</div>
	</div>
	';
}
?>