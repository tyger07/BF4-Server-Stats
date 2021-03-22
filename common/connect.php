<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// connect to the stats database
$BF4stats = @mysqli_connect(HOST, USER, PASS, NAME, PORT) or die ("<title>BF4 Player Stats - Error</title></head><body><div id='pagebody'><div class='subsection'><div class='headline'>Unable to access stats database. Please notify this website's administrator.</div></div><br/><div class='subsection'><div class='headline'>If you are the administrator, please seek assistance <a href='https://myrcon.net/topic/166-bf4-stats-webpage-for-xpkillers-stats-logger-plugin/' target='_blank'>here</a>.</div></div><br/><div class='subsection'><div class='headline'>Error: " . mysqli_connect_error() . "</div></div></div></body></html>");
// make sure that the database name wasn't left empty
if(NAME)
{
	@mysqli_select_db($BF4stats, NAME) or die ("<title>BF4 Player Stats - Error</title></head><body><div id='pagebody'><div class='subsection'><div class='headline'>Unable to select stats database. Please notify this website's administrator.</div></div><br/><div class='subsection'><div class='headline'>If you are the administrator, please seek assistance <a href='https://myrcon.net/topic/166-bf4-stats-webpage-for-xpkillers-stats-logger-plugin/' target='_blank'>here</a>.</div></div><br/><div class='subsection'><div class='headline'>Error: Database not found at '" . HOST . "'.</div></div></div></body></html>");
}
else
{
	die ("<title>BF4 Player Stats - Error</title></head><body><div id='pagebody'><div class='subsection'><div class='headline'>Unable to select stats database. Please notify this website's administrator.</div></div><br/><div class='subsection'><div class='headline'>If you are the administrator, please seek assistance <a href='https://myrcon.net/topic/166-bf4-stats-webpage-for-xpkillers-stats-logger-plugin/' target='_blank'>here</a>.</div></div><br/><div class='subsection'><div class='headline'>Error: Database '(null)' not found at '" . HOST . "'.</div></div></div></body></html>");
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
	die ("<title>BF4 Player Stats - Error</title></head><body><div id='pagebody'><div class='subsection'><div class='headline'>No 'BF4' servers were found in this database! Please notify this website's administrator.</div></div><br/><div class='subsection'><div class='headline'>If you are the administrator, please seek assistance <a href='https://myrcon.net/topic/166-bf4-stats-webpage-for-xpkillers-stats-logger-plugin/' target='_blank'>here</a>.</div></div></div></body></html>");
}
// merge server IDs array into a variable to use in combined server stats queries later
$valid_ids = join(',',$ServerIDs);

// is AdKats in this database?
// set a default value
$adkats_available = FALSE;

// detect bots
// set a default value
$isbot = FALSE;
// set default client user agent value
$useragent = 'unknown';
// update client's user agent
if(isset($_SERVER["HTTP_USER_AGENT"]))
{
	$useragent = $_SERVER["HTTP_USER_AGENT"];
}
// check for a user agent bot match
if(stripos($useragent, 'search') === false && stripos($useragent, 'seek') === false && stripos($useragent, 'fetch') === false && stripos($useragent, 'archiv') === false && stripos($useragent, 'spide') === false && stripos($useragent, 'validat') === false && stripos($useragent, 'analyze') === false && stripos($useragent, 'crawl') === false && stripos($useragent, 'robot') === false && stripos($useragent, 'track') === false && stripos($useragent, 'generat') === false && stripos($useragent, 'google') === false && stripos($useragent, 'bing') === false && stripos($useragent, 'msnbot') === false && stripos($useragent, 'yahoo') === false && stripos($useragent, 'facebook') === false && stripos($useragent, 'yandex') === false && stripos($useragent, 'alexa') === false)
{
	$isbot = FALSE;
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
}
else
{
	$isbot = TRUE;
	// remove adkats integration information for bots
	$adkats_available = FALSE;
}
?>
