<?php
// server stats index page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

// don't show this text unless only at the index page
if((!isset($_GET['globalhome']) OR empty($_GET['globalhome'])) AND (!isset($_GET['globalsearch']) OR empty($_GET['globalsearch'])) AND (!isset($_GET['globalsuspicious']) OR empty($_GET['globalsuspicious'])) AND (!isset($_GET['globalcountries']) OR empty($_GET['globalcountries'])) AND (!isset($_GET['globalmaps']) OR empty($_GET['globalmaps'])) AND (!isset($_GET['globalserverstats']) OR empty($_GET['globalserverstats'])) AND (!isset($_GET['globalpotw']) OR empty($_GET['globalpotw'])))
{
	echo '
	<div class="middlecontent">
	<table width="100%" border="0">
	<tr>
	<td width="100%" align="center" style="text-align: left;">
	<br/>
	<center>Please select the desired server stats page from ' . $clan_name . '\'s servers listed below:</center>
	</td>
	</tr>';
	// include displayservers.php contents
	require_once('./common/displayservers.php');
	echo '
	</table>
	<br/>
	</div>
	<table width="100%" border="0">
	<tr>
	<td valign="top" align="center">
	';
	// don't display global server info if there is only one server
	// if there is only one server, it is already "global" to itself
	if(count($ServerIDs) > 1)
	{
		// include globalserverinfo.php contents
		require_once('./common/globalserverinfo.php');
	}
}
// or else a global stats page has been selected
else
{
	echo '
	<table width="100%" border="0">
	<tr>
	<td valign="top" align="center">
	';
	// show global home if selected
	if(isset($_GET['globalhome']) AND !empty($_GET['globalhome']))
	{
		// include home.php contents
		require_once('./common/home.php');
	}
	// show global player stats if selected
	if(isset($_GET['globalsearch']) AND !empty($_GET['globalsearch']))
	{
		// include player.php contents
		require_once('./common/player.php');
	}
	// show global suspicious players if selected
	if(isset($_GET['globalsuspicious']) AND !empty($_GET['globalsuspicious']))
	{
		// include suspicious.php contents
		require_once('./common/suspicious.php');
	}
	// show global country stats if selected
	if(isset($_GET['globalcountries']) AND !empty($_GET['globalcountries']))
	{
		// include countries.php contents
		require_once('./common/countries.php');
	}
	// show global map stats if selected
	if(isset($_GET['globalmaps']) AND !empty($_GET['globalmaps']))
	{
		// include maps.php contents
		require_once('./common/maps.php');
	}
	// show global server stats if selected
	if(isset($_GET['globalserverstats']) AND !empty($_GET['globalserverstats']))
	{
		// include serverstats.php contents
		require_once('./common/serverstats.php');
	}
	// show global players of the week if selected
	if(isset($_GET['globalpotw']) AND !empty($_GET['globalpotw']))
	{
		// include potw.php contents
		require_once('./common/potw.php');
	}
}
echo '
</td>
</tr>
</table>
';
?>