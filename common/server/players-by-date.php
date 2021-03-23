<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
// check if necessary environment exists on this server
if(extension_loaded('gd') && function_exists('gd_info'))
{
	// SQL query limit
	$limit = 10;
	// check if a server was provided
	// if so, this is a server stats page
	if(!empty($sid))
	{
		$query  = "
			SELECT SUBSTRING(`TimeMapLoad`, 1, length(`TimeMapLoad`) - 9) AS Date, AVG(`MaxPlayers`) AS Average, MAX(`MaxPlayers`) AS Max
			FROM `tbl_mapstats`
			WHERE `ServerID` = {$sid}
			AND `Gamemode` != ''
			AND `MapName` != ''
			GROUP BY `Date`
			ORDER BY `Date` DESC
			LIMIT {$limit}
		";
		$result = @mysqli_query($BF4stats, $query);
	}
	// this must be a global stats page
	else
	{
		// merge server IDs array into a variable
		$ids = join(',',$ServerIDs);
		$query  = "
			SELECT SUBSTRING(`TimeMapLoad`, 1, length(`TimeMapLoad`) - 9) AS Date, AVG(`MaxPlayers`) AS Average, MAX(`MaxPlayers`) AS Max
			FROM `tbl_mapstats`
			WHERE `ServerID` in ({$ids})
			AND `Gamemode` != ''
			AND `MapName` != ''
			GROUP BY `Date`
			ORDER BY `Date` DESC
			LIMIT {$limit}
		";
		$result = @mysqli_query($BF4stats, $query);
	}
	// initialize timestamp values
	$now_timestamp = time();
	// start outputting the image
	header('Pragma: public');
	header('Cache-Control: max-age=10800');
	header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', $now_timestamp + 10800));
	header("Content-type: image/png");
	// base image
	$base = imagecreatefrompng('./images/background.png');
	// text color
	$light = imagecolorallocate($base, 255, 255, 255);
	$yellow = imagecolorallocate($base, 255, 250, 200);
	$red = imagecolorallocate($base, 255, 000, 000);
	$orange = imagecolorallocate($base, 255, 100, 000);
	$dark = imagecolorallocate($base, 200, 200, 190);
	if(@mysqli_num_rows($result) != 0)
	{
		$numrows = @mysqli_num_rows($result);
		$x_range = 520 / $numrows;
		$x = 50;
		$last_average = 0;
		$i = 0;
		while($row = mysqli_fetch_assoc($result))
		{
			$Max = round($row['Max'], 0);
			$Ticks = 270 / $Max;
			$date = date("M d", strtotime($row['Date']));
			$average = 270 - ($row['Average'] * $Ticks);
			$x_axis = $x;
			$x += $x_range;
			if($i > 0)
			{
				imageline($base, $x_axis, $last_average, $x, $average, $orange);
			}
			else
			{
				imageline($base, $x_axis, $average, $x, $average, $orange);
			}
			imagestring($base, 2, $x_axis, 280, $date, $yellow);
			$last_average = $average;
			$i++;
		}
		imagestring($base, 2, 15, 5, $Max, $yellow);
		$middle = round(($Max / 2), 0);
		imagestring($base, 2, 15, 133, $middle, $yellow);
		imagestring($base, 2, 15, 270, "0", $yellow);
		imageline($base, 40, 280, 580, 280, $light);
		imageline($base, 40, 10, 40, 280, $light);
	}
	else
	{
		imagestring($base, 4, 140, 135, 'The query returned no results.', $light);
	}
	// compile image
	imagealphablending($base, false);
	imagesavealpha($base, true);
	imagepng($base);
	imagedestroy($base);
}
// php GD extension doesn't exist. show error image
else
{
	// start outputting the image
	header("Content-type: image/png");
	echo file_get_contents('./images/error.png');
}
?>