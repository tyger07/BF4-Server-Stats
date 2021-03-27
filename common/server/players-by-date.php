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
	$orange = imagecolorallocate($base, 255, 100, 000);
	if(@mysqli_num_rows($result) != 0)
	{
		$numrows = @mysqli_num_rows($result);
		$top_offset = 40;
		$height = 220;
		$width = 520;
		$x_division = $width / $numrows;
		$x_finish = 50;
		$last_average = 0;
		$loop_count = 0;
		while($row = mysqli_fetch_assoc($result))
		{
			$y_max = $row['Max'];
			$y_max_display = round($y_max, 0);
			$y_division = $height / $y_max;
			$date = date("M d", strtotime($row['Date']));
			$day_average = $height - ($row['Average'] * $y_division) + $top_offset;
			$x_start = $x_finish;
			$x_finish += $x_division;
			if($loop_count > 0)
			{
				imageline($base, $x_start, $last_average, $x_finish, $day_average, $orange);
			}
			else
			{
				imageline($base, $x_start, $day_average, $x_finish, $day_average, $orange);
			}
			imagestring($base, 2, $x_start + 10, $height + 15 + $top_offset, $date, $yellow);
			imageline($base, $x_finish, $height + $top_offset, $x_finish, $height + 10 + $top_offset, $light);
			$last_average = $day_average;
			$loop_count++;
		}
		imagestring($base, 2, 15, $height - ($y_max_display * $y_division) + $top_offset, $y_max_display, $yellow);
		$middle = round(($y_max / 2), 0);
		imagestring($base, 2, 15, $height - ($middle * $y_division) + $top_offset, $middle, $yellow);
		imagestring($base, 2, 15, $height + $top_offset, "0", $yellow);
		imageline($base, 40, $top_offset + 10, 50, $top_offset + 10, $light);
		imageline($base, 40, $height + $top_offset, $width + 50, $height + $top_offset, $light);
		imageline($base, 50, 10 + $top_offset, 50, $height + 10 + $top_offset, $light);
		imagestring($base, 4, 90, 15, 'Average Players per Day on Days with Server Acitivity', $light);
	}
	else
	{
		imagestring($base, 4, 170, 135, 'The query returned no results.', $light);
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