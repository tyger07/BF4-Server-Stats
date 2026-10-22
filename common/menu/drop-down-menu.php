<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// set the default first line in the server select form
// if a server name was already found, use it
if(!empty($ServerName))
{
	$default = $ServerName;
}
// no server name has been established...  is this a combined stats page?
// if there is no server id, but there is a page id, then this must be a combined stats page
elseif(empty($ServerID) && !empty($page))
{
	$default = 'Combined Stats';
}
// there is no page or server id specified
else
{
	$default = 'Select A Server ...';
}
// start the form
echo '
<div class="topmenuitems">
<div id="topmenudropdown">
<form action="' . $_SERVER['PHP_SELF'] . '" method="get">
<input type="hidden" name="p" value="home" />
<select name="sid" onchange="this.form.submit();" class="selectbox">
<option value="null">' . $default . '</option>
';
// go through each detected server ID
foreach($ServerIDs as $this_ServerID)
{
	// skip this server if it is already the $default first line displayed
	if($this_ServerID != $ServerID)
	{
		// get server name
		$ServerName_q = @mysqli_query($BF4stats,"
			SELECT `ServerName`
			FROM `tbl_server`
			WHERE `ServerID` = {$this_ServerID}
			AND `GameID` = {$GameID}
		");
		if(@mysqli_num_rows($ServerName_q) != 0)
		{
			$ServerName_r = @mysqli_fetch_assoc($ServerName_q);
			$Server_Name = $ServerName_r['ServerName'];
		}
		// some sort of error occured
		else
		{
			$Server_Name = 'Unknown';
		}
		echo '
		<option value="' . $this_ServerID . '">' . $Server_Name . '</option>
		';
	}
}
// is there more than one server?
// show combined stats option at bottom of the form if there is more than one server and we aren't already viewing the combined stats page
if(count($ServerIDs) > 1 && !(empty($ServerID) && !empty($page)))
{
	// show combined stats in form
	echo '
	<option value="null">Combined Stats From Servers Above</option>
	';
}
// end the form
echo '
</select>
</form>
</div>
<div id="topmenujoin">
';
// show join button if viewing a server stats page
if(!empty($ServerID))
{
	echo '<a href="' . $battlelog . '" target="_blank"><img src="./common/images/joinbtn.png" style="width: 150px; height: 30px;" alt="join" class="imagebutton"/></a>';
}
echo '
</div>
</div>
';
?>
