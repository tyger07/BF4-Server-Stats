<?php
// database connection page by Ty_ger07 at http://open-web-community.com/

// DON'T EDIT ANYTHING BELOW UNLESS YOU KNOW WHAT YOU ARE DOING

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
// free up memory from server info query
@mysqli_free_result($Server_q);

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
// free up memory from server id query
@mysqli_free_result($ServerID_q);

// merge server IDs array into a variable to use in queries later
$valid_ids = join(',',$ServerIDs);

// deploy database updates if necessary

// check if this table exists
@mysqli_query($BF4stats,"
	CREATE TABLE IF NOT EXISTS `tyger_stats_database_update`
	(`version` TINYINT(2) NOT NULL DEFAULT '0', INDEX (`version`))
	ENGINE = MYISAM
	DEFAULT CHARSET=utf8
	COLLATE=utf8_bin
");
// is it populated?
$rows_q = @mysqli_query($BF4stats,"
	SELECT `version`
	FROM `tyger_stats_database_update`
");
// empty! insert a default row
if(@mysqli_num_rows($rows_q) == 0)
{
	@mysqli_query($BF4stats,"
		INSERT INTO `tyger_stats_database_update`
		(`version`)
		VALUES ('0')
	");
}
// check if version number is current
$current_version_q = @mysqli_query($BF4stats,"
	SELECT `version`
	FROM `tyger_stats_database_update`
");
if(@mysqli_num_rows($current_version_q) != 0)
{
	$version_r = @mysqli_fetch_assoc($current_version_q);
	$version = $version_r['version'];
	
	// if the version is old, do update
	if($version < 1)
	{

		// check to see if old stats rank cache table was made with too small of a SID
		$size_q = @mysqli_query($BF4stats,"
			SELECT `CHARACTER_MAXIMUM_LENGTH`
			FROM `INFORMATION_SCHEMA`.`COLUMNS`
			WHERE `table_name` = 'tyger_stats_rank_cache'
			AND `COLUMN_NAME` = 'SID'
		");
		if(@mysqli_num_rows($size_q) != 0)
		{
			$size_r = @mysqli_fetch_assoc($size_q);
			$size = $size_r['CHARACTER_MAXIMUM_LENGTH'];
			// too small!  fix it
			if($size < 100)
			{
				// empty the table
				@mysqli_query($BF4stats,"
					TRUNCATE TABLE `tyger_stats_rank_cache`
				");
				// optimize the table
				@mysqli_query($BF4stats,"
					OPTIMIZE TABLE `tyger_stats_rank_cache`
				");
				// alter the table
				@mysqli_query($BF4stats,"
					ALTER TABLE `tyger_stats_rank_cache` CHANGE `SID` `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
				");
				// add additional indexes
				@mysqli_query($BF4stats,"
					ALTER TABLE `tyger_stats_rank_cache` DROP INDEX `PlayerID`,
					ADD INDEX `PlayerID` (`PlayerID`, `GID`, `SID`, `category`)
				");
			}
		}
		
		// check to see if old count cache table was made with too small of a SID
		$size_q = @mysqli_query($BF4stats,"
			SELECT `CHARACTER_MAXIMUM_LENGTH`
			FROM `INFORMATION_SCHEMA`.`COLUMNS`
			WHERE `table_name` = 'tyger_stats_count_cache'
			AND `COLUMN_NAME` = 'SID'
		");
		if(@mysqli_num_rows($size_q) != 0)
		{
			$size_r = @mysqli_fetch_assoc($size_q);
			$size = $size_r['CHARACTER_MAXIMUM_LENGTH'];
			// too small!  fix it
			if($size < 100)
			{
				// empty the table
				@mysqli_query($BF4stats,"
					TRUNCATE TABLE `tyger_stats_count_cache`
				");
				// optimize the table
				@mysqli_query($BF4stats,"
					OPTIMIZE TABLE `tyger_stats_count_cache`
				");
				// alter the table
				@mysqli_query($BF4stats,"
					ALTER TABLE `tyger_stats_count_cache` CHANGE `SID` `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
				");
			}
		}
		
		// check to see if old top twenty cache table was made with too small of a SID
		$size_q = @mysqli_query($BF4stats,"
			SELECT `CHARACTER_MAXIMUM_LENGTH`
			FROM `INFORMATION_SCHEMA`.`COLUMNS`
			WHERE `table_name` = 'tyger_stats_top_twenty_cache'
			AND `COLUMN_NAME` = 'SID'
		");
		if(@mysqli_num_rows($size_q) != 0)
		{
			$size_r = @mysqli_fetch_assoc($size_q);
			$size = $size_r['CHARACTER_MAXIMUM_LENGTH'];
			// too small!  fix it
			if($size < 100)
			{
				// empty the table
				@mysqli_query($BF4stats,"
					TRUNCATE TABLE `tyger_stats_top_twenty_cache`
				");
				// optimize the table
				@mysqli_query($BF4stats,"
					OPTIMIZE TABLE `tyger_stats_top_twenty_cache`
				");
				// alter the table
				@mysqli_query($BF4stats,"
					ALTER TABLE `tyger_stats_top_twenty_cache` CHANGE `SID` `SID` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
				");
				// add additional indexes
				@mysqli_query($BF4stats,"
					ALTER TABLE `tyger_stats_top_twenty_cache` DROP INDEX `PlayerID`,
					ADD INDEX `PlayerID` (`PlayerID`, `GID`, `SID`, `SoldierName`)
				");
			}
		}
		
		// update version number to 1
		@mysqli_query($BF4stats,"
			UPDATE `tyger_stats_database_update`
			SET `version` = '1'
		");
	}
	// if the version is old, do update
	if($version < 2)
	{
		// add additional indexes
		@mysqli_query($BF4stats,"
			ALTER TABLE `tyger_stats_rank_cache` DROP INDEX `PlayerID`,
			ADD INDEX `PlayerID` (`PlayerID`, `SID`),
			ADD INDEX `category` (`category`, `PlayerID`)
		");
		
		// add additional indexes
		@mysqli_query($BF4stats,"
			ALTER TABLE `tyger_stats_top_twenty_cache` DROP INDEX `PlayerID`,
			ADD INDEX `PlayerID` (`PlayerID`, `SID`),
			ADD INDEX `SoldierName` (`SoldierName`, `PlayerID`)
		");
		
		// update version number to 2
		@mysqli_query($BF4stats,"
			UPDATE `tyger_stats_database_update`
			SET `version` = '2'
		");
	}
}
?>
