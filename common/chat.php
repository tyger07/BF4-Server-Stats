<?php
// server stats chat page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo'
<div class="middlecontent">
<table width="100%" border="0">
<tr>
<td class="headline">
<br/>
<center>
<b>Recent Chat Content</b>
</center>
<br/>
</td>
</tr>
</table>
</div>
<br/>
<br/>
<div class="middlecontent">
<table width="100%" border="0">
<tr>
<th class="headline"><b>Chat Results</b></th>
</tr>
<tr>
<td>
<div class="innercontent">
<table width="100%" border="0">
<tr>
<td>
';
// pagination code thanks to: http://www.phpfreaks.com/tutorial/basic-pagination
// find out how many rows are in the table
$TotalRows_q = @mysqli_query($BF4stats,"
	SELECT count(`logDate`) AS count
	FROM tbl_chatlog
	WHERE `ServerID` = {$ServerID}
	AND logMessage != ''
");
$TotalRows_r = @mysqli_fetch_row($TotalRows_q);
$numrows = $TotalRows_r[0];
// number of rows to show per page
$rowsperpage = 25;
// find out total pages
$totalpages = ceil($numrows / $rowsperpage);
// get the current page or set a default
if(isset($_GET['currentpage']) && is_numeric($_GET['currentpage']))
{
	// cast var as int
	$currentpage = (int) $_GET['currentpage'];
}
else
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
if(isset($_GET['rank']) AND !empty($_GET['rank']))
{
	$rank = $_GET['rank'];
	// filter out SQL injection
	if($rank != 'logDate' AND $rank != 'logSoldierName' AND $rank != 'logSubset' AND $rank != 'logMessage')
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
if(isset($_GET['order']) AND !empty($_GET['order']))
{
	$order = $_GET['order'];
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
// get the info from the db 
$Messages_q = @mysqli_query($BF4stats,"
	SELECT `logDate`, `logSoldierName`, TRIM(`logMessage`) AS Message, `logSubset`
	FROM tbl_chatlog
	WHERE `ServerID` = {$ServerID}
	AND logMessage != ''
	ORDER BY {$rank} {$order}, `logDate` DESC LIMIT {$offset}, {$rowsperpage}
");
// offset count
$count = ($currentpage * 25) - 25;
// check if chat rows were found
if(@mysqli_num_rows($Messages_q) != 0)
{
	echo '
	<table width="98%" align="center" border="0" class="prettytable">
	<tr>
	<th width="5%" style="text-align:left">#</th>
	<th width="13%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1&amp;rank=logDate&amp;order=';
	if($rank != 'logDate')
	{
		echo 'DESC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Date</span></a></th>
	<th width="10%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1&amp;rank=logSoldierName&amp;order=';
	if($rank != 'logSoldierName')
	{
		echo 'ASC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Player</span></a></th>
	<th width="7%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1&amp;rank=logSubset&amp;order=';
	if($rank != 'logSubset')
	{
		echo 'ASC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Audience</span></a></th>
	<th width="65%" style="text-align:left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1&amp;rank=logMessage&amp;order=';
	if($rank != 'logMessage')
	{
		echo 'ASC';
	}
	else
	{
		echo $nextorder;
	}
	echo '"><span class="orderheader">Message</span></a></th>
	</tr>';
	// while there are rows to be fetched...
	while($Messages_r = @mysqli_fetch_assoc($Messages_q))
	{
		// get data
		$logDate = $Messages_r['logDate'];
		$logSoldierName = textcleaner($Messages_r['logSoldierName']);
		$logMessage = textcleaner($Messages_r['Message']);
		$logSubset = $Messages_r['logSubset'];
		$count++;
		echo '
		<tr>
		<td width="5%" class="tablecontents" style="text-align: left;"><font class="information">' . $count . ':</font></td>
		<td width="13%" class="tablecontents" style="text-align: left;">' . $logDate . '</td>
		<td width="10%" class="tablecontents" style="text-align: left;"><a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;SoldierName=' . $logSoldierName . '&amp;search=1">' . $logSoldierName . '</a></td>
		<td width="7%" class="tablecontents" style="text-align: left;">' . $logSubset . '</td>
		<td width="65%" class="tablecontents" style="text-align: left;">' . $logMessage . '</td>
		</tr>
		';
	}
}
else
{
	echo '
	<table width="98%" align="center" border="0" class="prettytable">
	<tr>
	<td style="text-align: left;" width="100%" colspan="5"><br/><center><font class="information">No chat content found for this server.</font></center><br/></td>
	</tr>
	';
}
// free up messages query memory
@mysqli_free_result($Messages_q);
// build the pagination links
echo '</table>';
// if no chat was found, don't display pagination links
if(@mysqli_num_rows($TotalRows_q) != 0)
{
	echo '
	<div class="pagination">
	<center>
	';
	// range of num links to show
	$range = 3;
	// if on page 1, don't show back links
	if ($currentpage > 1)
	{
		// show << link to go back to first page
		echo '<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1&amp;currentpage=1&amp;rank=' . $rank . '&amp;order=' . $order . '">&lt;&lt;</a>';
		// get previous page num
		$prevpage = $currentpage - 1;
		// show < link to go back one page
		echo ' <a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1&amp;currentpage=' . $prevpage . '&amp;rank=' . $rank . '&amp;order=' . $order . '">&lt;</a> ';
	}
	// loop to show links to range of pages around current page
	for($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++)
	{
		// if it's a valid page number...
		if (($x > 0) && ($x <= $totalpages))
		{
			// if we're on current page...
			if ($x == $currentpage)
			{
				// 'highlight' it but don't make a link
				echo ' [<font class="information">' . $x . '</font>] ';
			}
			else
			{
				// make it a link
				echo ' <a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1&amp;currentpage=' . $x . '&amp;rank=' . $rank . '&amp;order=' . $order . '">' . $x . '</a> ';
			}
		}
	}
	// if not on last page, show forward links        
	if ($currentpage != $totalpages)
	{
		// get next page
		$nextpage = $currentpage + 1;
		// show > link to go forward one page
		echo ' <a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1&amp;currentpage=' . $nextpage . '&amp;rank=' . $rank . '&amp;order=' . $order . '">&gt;</a> ';
		// show >> link to last page
		echo '<a href="' . $_SERVER['PHP_SELF'] . '?ServerID=' . $ServerID . '&amp;chat=1&amp;currentpage=' . $totalpages . '&amp;rank=' . $rank . '&amp;order=' . $order . '">&gt;&gt;</a>';
	}
	echo '
	</center>
	</div>
	';
}
// end build pagination links and end block
// free up total rows query memory
@mysqli_free_result($TotalRows_q);
echo '
</td>
</tr>
</table>
</div>
</td>
</tr>
</table>
</div>
';
?>