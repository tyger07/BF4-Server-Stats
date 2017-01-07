<?php
// BF4 Stats Page by Ty_ger07
// https://forum.myrcon.com/showthread.php?6854

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
// initialize values
$GameID = null;
$ServerIDs = array();
// We need to find the GameID and find all valid ServerIDs for that GameID
// note: WHERE `Name` = 'BF4', change 'BF4' to other game name if you want to try to modify this code to work with another game
$Server_q = @mysqli_query($BF4stats,"
	SELECT tg.`GameID`, ts.`ServerID`
	FROM `tbl_games` tg
	INNER JOIN `tbl_server` ts ON ts.`GameID` = tg.`GameID`
	WHERE tg.`Name` = 'BF4'
	AND (ts.`ConnectionState` IS NULL
	OR ts.`ConnectionState` = 'on')
");
// the server info was found
if(@mysqli_num_rows($Server_q) != 0)
{
	// assign GameID variable and add found server IDs to array 
	while($Server_r = @mysqli_fetch_assoc($Server_q))
	{
		$ServerIDs[] = $Server_r['ServerID'];
		$GameID = $Server_r['GameID'];
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
// is AdKats in this database?
// set a default value
$adkats_available = FALSE;
// query to see if the adkats bans table exists
$AdKats_q  = @mysqli_query($BF4stats,"
	SELECT `TABLE_NAME`
	FROM `INFORMATION_SCHEMA`.`TABLES`
	WHERE `TABLE_SCHEMA` = '" . NAME . "'
	AND `TABLE_NAME` = 'adkats_bans'
");
if(@mysqli_num_rows($AdKats_q) != 0)
{
	$adkats_available = TRUE;
}
?>