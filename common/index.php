<?php
// server stats index page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

echo'
<table width="100%" border="0">
<tr>
<td width="100%" align="center" style="text-align: left;">
';
// change this text if global scoreboard is being scrolled through
if((!isset($_GET['topglobal']) OR empty($_GET['topglobal'])) AND (!isset($_GET['globalsearch']) OR empty($_GET['globalsearch'])) AND (!isset($_GET['globalsuspicious']) OR empty($_GET['globalsuspicious'])) AND (!isset($_GET['globalcountries']) OR empty($_GET['globalcountries'])) AND (!isset($_GET['globalmaps']) OR empty($_GET['globalmaps'])))
{
	echo '<br/><div class="middlecontent"><br/><center>Please select the desired server stats page from ' . $clan_name . '\'s servers listed below:</center><br/></div><br/>';
}
echo '
</td>
</tr>
';
// don't show stats index if global scoreboard is being scrolled through
if((!isset($_GET['topglobal']) OR empty($_GET['topglobal']))  AND (!isset($_GET['globalsearch']) OR empty($_GET['globalsearch'])) AND (!isset($_GET['globalsuspicious']) OR empty($_GET['globalsuspicious'])) AND (!isset($_GET['globalcountries']) OR empty($_GET['globalcountries'])) AND (!isset($_GET['globalmaps']) OR empty($_GET['globalmaps'])))
{
	// include displayservers.php contents
	require_once('./common/displayservers.php');
}
echo '
</table>
<table width="100%" border="0">
<tr>
<td valign="top" align="center">
';
// don't show global top players if global player search is being viewed
if(isset($_GET['topglobal']) AND !empty($_GET['topglobal']))
{
	// include globalhome.php contents
	require_once('./common/globalhome.php');
}
// don't show server totals if global scoreboard is being scrolled through
if((!isset($_GET['topglobal']) OR empty($_GET['topglobal'])) AND (!isset($_GET['globalsearch']) OR empty($_GET['globalsearch'])) AND (!isset($_GET['globalsuspicious']) OR empty($_GET['globalsuspicious'])) AND (!isset($_GET['globalcountries']) OR empty($_GET['globalcountries'])) AND (!isset($_GET['globalmaps']) OR empty($_GET['globalmaps'])))
{
	// include globalserverstats.php contents
	require_once('./common/globalserverstats.php');
}
// show global player stats if selected
if(isset($_GET['globalsearch']) AND !empty($_GET['globalsearch']))
{
	// include globalplayer.php contents
	require_once('./common/globalplayer.php');
}
// show global suspicious players if selected
if(isset($_GET['globalsuspicious']) AND !empty($_GET['globalsuspicious']))
{
	// include globalsuspicious.php contents
	require_once('./common/globalsuspicious.php');
}
// show global country stats if selected
if(isset($_GET['globalcountries']) AND !empty($_GET['globalcountries']))
{
	// include globalcountries.php contents
	require_once('./common/globalcountries.php');
}
// show global map stats if selected
if(isset($_GET['globalmaps']) AND !empty($_GET['globalmaps']))
{
	// include globalmaps.php contents
	require_once('./common/globalmaps.php');
}
echo '
</td>
</tr>
</table>
';
?>