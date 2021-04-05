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
	// color
	$faded = imagecolorallocate($base, 150, 150, 150);
	$yellow = imagecolorallocate($base, 255, 250, 200);
	$orange = imagecolorallocate($base, 200, 150, 000);
	// initialize empty arrays
	$day = array();
	$average = array();
	$y_max = 2;
	if(@mysqli_num_rows($result) != 0)
	{
		// loop through query results
		while($row = mysqli_fetch_assoc($result))
		{
			if($row['Max'] > $y_max)
			{
				$y_max = $row['Max'];
			}
			$day[] = $row['Date'];
			$average[] = $row['Average'];
		}
		// initialize variables
		$numrows = count($day);
		$top_offset = 40;
		$height = 220;
		$width = 520;
		$y_max_display = round($y_max, 0);
		$y_division = $height / $y_max;
		$x_division = $width / $numrows;
		$middle = round(($y_max / 2), 0);
		$x_finish = 50;
		$last_average = 0;
		$loop_count = 0;
		// loop through query results
		foreach($day as $this_day)
		{
			$this_average = $average[$loop_count];
			$date = date("M d", strtotime($this_day));
			$day_average = $height - ($this_average * $y_division) + $top_offset;
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
			imagestring($base, 1, $x_start + 12, $height + 15 + $top_offset, $date, $faded);
			imageline($base, $x_finish, $height + $top_offset, $x_finish, $height + 10 + $top_offset, $faded);
			$last_average = $day_average;
			$loop_count++;
		}
		imagestring($base, 1, 15, $top_offset - 4, $y_max_display, $faded);
		imagestring($base, 1, 15, $height - ($middle * $y_division) + $top_offset - 4, $middle, $faded);
		imagestring($base, 1, 15, $height + $top_offset - 4, "0", $faded);
		imageline($base, 40, $top_offset, 50, $top_offset, $faded);
		imageline($base, 40, $height + $top_offset, $width + 50, $height + $top_offset, $faded);
		imageline($base, 50, $top_offset, 50, $height + 10 + $top_offset, $faded);
		imagestring($base, 2, 140, 15, 'Average Players per Day on Days with Server Acitivity', $faded);
		imagefilledrectangle($base, 527, 20, 532, 25, $orange);
		imagestring($base, 1, 537, 19, 'Average', $faded);
	}
	else
	{
		imagestring($base, 4, 170, 135, 'The query returned no results.', $faded);
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