<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// connect to the stats database
$BF4stats = @mysqli_connect(HOST, USER, PASS, NAME, PORT) or die ("<title>BF4 Player Stats - Error</title></head><body><br/><br/><center><b>Unable to access stats database. Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/><center>Error: " . mysqli_connect_error() . "</center></body></html>");
// make sure that the database name wasn't left empty
if(NAME)
{
	@mysqli_select_db($BF4stats, NAME) or die ("<title>BF4 Player Stats - Error</title></head><body><br/><br/><center><b>Unable to select stats database. Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/><center>Error: Database not found at '" . HOST . "'.</center></body></html>");
}
else
{
	die ("<title>BF4 Player Stats - Error</title></head><body><br/><br/><center><b>Unable to select stats database. Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/><center>Error: Database '(null)' not found at '" . HOST . "'.</center></body></html>");
}
// initialize value as null
$GameID = null;
// first we need to find the GameID of BF4
// note: WHERE `Name` = 'BF4'
// change 'BF4' to other game name if you want to try to modify this code to work with another game
$Server_q = @mysqli_query($BF4stats,"
	SELECT `GameID`
	FROM `tbl_games`
	WHERE `Name` = 'BF4'
");
// the server info was found
if(@mysqli_num_rows($Server_q) == 1)
{
	$Server_r = @mysqli_fetch_assoc($Server_q);
	$GameID = $Server_r['GameID'];
}
// BF4 not found in this database
else
{
	// display error and die
	die ("<title>BF4 Player Stats - Error</title></head><body><br/><br/><br/><center><b>The game 'BF4' was not found in this database! Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/></body></html>");
}
// find all servers in this database which have the BF4 game id
$ServerID_q = @mysqli_query($BF4stats,"
	SELECT `ServerID`
	FROM `tbl_server`
	WHERE `GameID` = {$GameID}
	AND (`ConnectionState` IS NULL OR `ConnectionState` = 'on')
");
// initialize an empty array for storing server ids in
$ServerIDs = array();
// at least one BF4 server was found
if(@mysqli_num_rows($ServerID_q) != 0)
{
	// add found server IDs to array
	while($ServerID_r = @mysqli_fetch_assoc($ServerID_q))
	{
		$ServerIDs[] = $ServerID_r['ServerID'];
	}
}
// no BF4 servers were found
else
{
	// display error and die
	die ("<title>BF4 Player Stats - Error</title></head><body><br/><br/><br/><center><b>No 'BF4' servers were found in this database! Please notify this website's administrator.</b></center><br/><center>If you are the administrator, please seek assistance <a href='https://forum.myrcon.com/showthread.php?6854-Server-Stats-page-for-XpKiller-s-BF4-Chat-GUID-Stats-and-Mapstats-Logger' target='_blank'>here</a>.</center><br/></body></html>");
}
// merge server IDs array into a variable to use in combined server stats queries later
$valid_ids = join(',',$ServerIDs);
?>