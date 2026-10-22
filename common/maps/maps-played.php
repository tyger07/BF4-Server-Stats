<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../constants.php');
require_once('../case.php');
// check if necessary environment exists on this server
if(extension_loaded('gd') && function_exists('gd_info'))
{
	// check if a server was provided
	// if so, this is a server stats page
	if(!empty($sid))
	{
		$query = "
			SELECT `MapName`, SUM(`NumberofRounds`) AS number, sub.`total`
			FROM `tbl_mapstats`
			LEFT JOIN
			(
				SELECT SUM(`NumberofRounds`) AS total
				FROM `tbl_mapstats`
				WHERE `ServerID` = {$sid}
                		AND `Gamemode` != ''
				LIMIT 0, 1
			) sub ON sub.`total` = `total`
			WHERE `ServerID` = {$sid}
			AND `Gamemode` != ''
			GROUP BY `MapName`
			ORDER BY number DESC
			LIMIT 25
		";
		$result = @mysqli_query($BF4stats, $query);
	}
	// this must be a global stats page
	else
	{
		// merge server IDs array into a variable
		$ids = join(',',$ServerIDs);
		$query = "
			SELECT `MapName`, SUM(`NumberofRounds`) AS number, sub.`total`
			FROM `tbl_mapstats`
			LEFT JOIN
			(
				SELECT SUM(`NumberofRounds`) AS total
				FROM `tbl_mapstats`
				WHERE `ServerID` in ({$ids})
                		AND `Gamemode` != ''
				LIMIT 0, 1
			) sub ON sub.`total` = `total`
			WHERE `ServerID` in ({$ids})
			AND `Gamemode` != ''
			GROUP BY `MapName`
			ORDER BY number DESC
			LIMIT 25
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
	$dark = imagecolorallocate($base, 20, 20, 20);
	$lighttransparent = imagecolorallocatealpha($base, 200, 200, 200, 90);
	$faded = imagecolorallocate($base, 150, 150, 150);
	$yellow = imagecolorallocate($base, 255, 250, 200);
	$orange = imagecolorallocate($base, 200, 150, 000);
	if(@mysqli_num_rows($result) != 0)
	{
		$legend_y_position = 19;
		$loop_count = 0;
		$previous_end = 0;
		$num_rows = @mysqli_num_rows($result);
		$color_steps = round(200 / $num_rows, 0);
		$running_total = 0;
		imagefilledarc($base, 300, 150, 200, 200, 0, 360, $lighttransparent, IMG_ARC_PIE);
		while($row = mysqli_fetch_assoc($result))
		{
			$key = $row['MapName'];
			$number = $row['number'];
			$running_total += $number;
			$total_rounds = $row['total'];
			$fraction = round(($number / $total_rounds * 100), 0);
			$degrees = 360 * ($number / $total_rounds);
			// avoid zero degree bug
			if($degrees < 1)
			{
				$degrees = 1;
			}
			// prevent zero degree bug hacky fix from causing a greater than 360 bug
			if(($previous_end + $degrees) > 360)
			{
				$previous_end = 360 - $degrees;
			}
			$wedge_color = imagecolorallocate($base, 0, abs(-220 + ($loop_count * $color_steps + 20)), abs(-220 + ($loop_count * $color_steps + 20)));
			// convert map to friendly name
			// first find if this map name is even in the map array
			if(in_array($key,$map_array))
			{
				$MapName = array_search($key,$map_array);
			}
			// this map is missing!
			else
			{
				$MapName = $key;
			}
			imagefilledrectangle($base, 457, $legend_y_position + 1, 462, $legend_y_position + 6, $wedge_color);
			imagestring($base, 1, 467, $legend_y_position, $MapName, $faded);
			if($num_rows > 1)
			{
				if(strlen((string)$fraction) == 1)
				{
					imagestring($base, 1, 439, $legend_y_position, " " . $fraction . "%", $faded);
				}
				elseif(strlen((string)$fraction) == 2)
				{
					imagestring($base, 1, 439, $legend_y_position, $fraction . "%", $faded);
				}
				else
				{
					imagestring($base, 1, 434, $legend_y_position, $fraction . "%", $faded);
				}
			}
			else
			{
				imagestring($base, 1, 434, $legend_y_position, $fraction . "%", $faded);
			}
			imagefilledarc($base, 300, 150, 200, 200, $previous_end - 90, $degrees + $previous_end - 90, $wedge_color, IMG_ARC_PIE);
			imagefilledarc($base, 300, 150, 200, 200, $previous_end - 90, $degrees + $previous_end - 90, $dark, IMG_ARC_EDGED | IMG_ARC_NOFILL);
			$legend_y_position += 10;
			$loop_count++;
			$previous_end += $degrees;
		}
		if($running_total < $total_rounds)
		{
			$fraction = round((100 - ($running_total / $total_rounds * 100)), 0);
			imagefilledrectangle($base, 457, $legend_y_position + 1, 462, $legend_y_position + 6, $lighttransparent);
			imagestring($base, 1, 467, $legend_y_position, "Other Maps", $faded);
			if(strlen((string)$fraction) == 1)
			{
				imagestring($base, 1, 439, $legend_y_position, " " . $fraction . "%", $faded);
			}
			elseif(strlen((string)$fraction) == 2)
			{
				imagestring($base, 1, 439, $legend_y_position, $fraction . "%", $faded);
			}
			else
			{
				imagestring($base, 1, 434, $legend_y_position, $fraction . "%", $faded);
			}
		}
		imagestring($base, 2, 250, 15, 'Maps Played Most', $faded);
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
