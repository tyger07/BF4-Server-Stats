<?php
// BF4 Stats Page by Ty_ger07
// https://myrcon.net/topic/162-chat-guid-stats-and-mapstats-logger-1003/

// include required files
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
require_once('../constants.php');
require_once('../functions.php');
// check if necessary environment exists on this server
if(extension_loaded('gd') && function_exists('gd_info'))
{
	// we will need a server ID from the URL query string!
	if(!empty($sid) && in_array($sid,$ServerIDs))
	{
		// query for server info
		$Basic_q = @mysqli_query($BF4stats,"
			SELECT `mapName`, `Gamemode`, `maxSlots`, `usedSlots`, `ServerName`
			FROM `tbl_server`
			WHERE `ServerID` = {$sid}
			AND `GameID` = {$GameID}
		");
		// information was found
		if(@mysqli_num_rows($Basic_q) != 0)
		{
			// get basic information
			$Basic_r = @mysqli_fetch_assoc($Basic_q);
			$used_slots = $Basic_r['usedSlots'];
			$available_slots = $Basic_r['maxSlots'];
			$servername = textcleaner($Basic_r['ServerName']);
			if(strlen($servername) > 39)
			{
				$servername = substr($servername,0,38);
				$servername .= '..';
			}
			$mode = $Basic_r['Gamemode'];
			// convert mode to friendly name
			if(in_array($mode,$mode_array))
			{
				$mode_name = array_search($mode,$mode_array);
				if(strlen($mode_name) > 15)
				{
					$mode_name = substr($mode_name,0,14);
					$mode_name .= '..';
				}
			}
			// this mode is missing!
			else
			{
				$mode_name = $mode;
				if(strlen($mode_name) > 15)
				{
					$mode_name = substr($mode_name,0,14);
					$mode_name .= '..';
				}
			}
			$map = $Basic_r['mapName'];
			// start outputting the image
			header('Pragma: public');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			header("Content-type: image/png");
			// base image
			$base = imagecreatefrompng('./images/background.png');
			// color
			$light = imagecolorallocate($base, 255, 255, 255);
			$faded = imagecolorallocate($base, 150, 150, 150);
			$yellow = imagecolorallocate($base, 255, 250, 200);
			$orange = imagecolorallocate($base, 200, 150, 000);
			// copy map background onto the base background image
			$back = imagecreatefrompng('./images/map_back.png');
			imagecopy($base, $back, 6, 6, 0, 0, 104, 60);
			// convert map to friendly name
			// first find if this map name is even in the map array
			if(in_array($map,$map_array))
			{
				$map_name = array_search($map,$map_array);
				if(strlen($map_name) > 13)
				{
					$map_name = substr($map_name,0,12);
					$map_name .= '..';
				}
				$map_img = imagecreatefrompng('../images/maps/' . $map . '.png'); 
				$resize_map = imagecreatetruecolor(100, 56);
				imagecopyresampled($resize_map, $map_img, 0, 0, 0, 0, 100, 56, 200, 113);
			}
			// this map is missing!
			else
			{
				$map_name = $map;
				if(strlen($map_name) > 13)
				{
					$map_name = substr($map_name,0,12);
					$map_name .= '..';
				}
				$map_img = imagecreatefrompng('../images/maps/missing.png');
				$resize_map = imagecreatetruecolor(100, 56);
				imagecopyresampled($resize_map, $map_img, 0, 0, 0, 0, 100, 56, 200, 113);
			}
			// copy the map image onto the background image
			imagecopy($base, $resize_map, 8, 8, 0, 0, 100, 56);
			// BF4 logo
			$logo = imagecreatefrompng('./images/bf4.png');
			// copy the logo image onto the background image
			imagecopy($base, $logo, 8, 70, 0, 0, 100, 19);
			// add text to image
			imagestring($base, 2, 120, 4, 'Server Name', $yellow);
			imagestring($base, 3, 120, 18, $servername, $light);
			imagestring($base, 2, 185, 42, 'Current Mode', $yellow);
			imagestring($base, 3, 185, 57, $mode_name, $light);
			imagestring($base, 2, 120, 42, 'Players', $yellow);
			imagestring($base, 3, 120, 57, $used_slots . ' / ' . $available_slots, $light);
			imagestring($base, 2, 310, 42, 'Current Map', $yellow);
			imagestring($base, 3, 310, 57, $map_name, $light);
			imagestring($base, 2, 440, 4, 'Players in 24 Hrs:', $yellow);
			// compile graph
			// build graph
			$result = @mysqli_query($BF4stats,"
				SELECT SUBSTRING(`TimeMapLoad`, 11, length(`TimeMapLoad`) - 16) AS Hourly, AVG(`MaxPlayers`) AS Average, MAX(`MaxPlayers`) AS Max
				FROM `tbl_mapstats`
				WHERE `ServerID` = {$sid}
				AND SUBSTRING(`TimeMapLoad`, 1, LENGTH(`TimeMapLoad`) - 9) BETWEEN CURDATE() - INTERVAL 24 HOUR AND CURDATE()
				AND `Gamemode` != ''
				AND `MapName` != ''
				GROUP BY Hourly
				ORDER BY `TimeMapLoad` DESC
			");
			// initialize empty arrays
			$hour = array();
			$average = array();
			$y_max = 2;
			// did the query return results
			if(@mysqli_num_rows($result) != 0)
			{
				// initialize tracking variable
				$increment = '';
				// loop through query results
				while($row = mysqli_fetch_assoc($result))
				{
					$raw_hour = $row['Hourly'];
					if($row['Max'] > $y_max)
					{
						$y_max = $row['Max'];
					}
					// add missing hours to fill in hours near the middle and end of the day for which the query found no results
					while($increment > $raw_hour && $increment != '')
					{
						$hour[] = $increment;
						$average[] = 0;
						$increment--;
					}
					// add missing hours to fill in hours at the beginning of the day for which the query found no results
					while($increment < $raw_hour && $increment != '' && $increment > 0)
					{
						$hour[] = $increment;
						$average[] = 0;
						$increment--;
					}
					$hour[] = $row['Hourly'];
					$average[] = $row['Average'];
					$increment = ($raw_hour - 1);
				}
				// query ran out of results to finish the day
				if(count($hour) < 23)
				{
					// get last array element to know where we need to start filling in data
					$last = end($hour);
					while(count($hour) < 23)
					{
						$hour[] = $last;
						$average[] = 0;
						$last--;
					}
				}
				// initialize variables
				$numrows = count($hour);
				$top_offset = 23;
				$height = 60;
				$width = 110;
				$y_max_display = round($y_max, 0);
				$y_division = $height / $y_max;
				$x_division = $width / $numrows;
				$middle = round(($y_max / 2), 0);
				$x_finish = 440;
				$last_average = 0;
				$loop_count = 0;
				// loop through query results
				foreach($hour as $this_hour)
				{
					$this_average = $average[$loop_count];
					$point_average = $height - ($this_average * $y_division) + $top_offset;
					$x_start = $x_finish;
					$x_finish += $x_division;
					if($loop_count > 0)
					{
						imageline($base, $x_start, $last_average, $x_finish, $point_average, $orange);
					}
					else
					{
						imageline($base, $x_start, $point_average, $x_finish, $point_average, $orange);
					}
					$last_average = $point_average;
					$loop_count++;
				}
				imageline($base, 440, $top_offset, 440, $height + $top_offset, $faded);
				if(strlen((string)$y_max_display) > 1)
				{
					imagestring($base, 1, 426, $top_offset - 4, $y_max_display, $light);
				}
				else
				{
					imagestring($base, 1, 430, $top_offset - 4, $y_max_display, $light);
				}
				if(strlen((string)$middle) > 1)
				{
					imagestring($base, 1, 426, $height - ($middle * $y_division) + $top_offset - 4, $middle, $light);
				}
				else
				{
					imagestring($base, 1, 430, $height - ($middle * $y_division) + $top_offset - 4, $middle, $light);
				}
				imagestring($base, 1, 430, $height + $top_offset - 4, "0", $light);
			}
			// no?
			else
			{
				// initialize variables
				$average = 0;
				$y_max = 2;
				$top_offset = 23;
				$height = 60;
				$width = 110;
				$y_max_display = round($y_max, 0);
				$y_division = $height / $y_max;
				$x_division = $width / 24;
				$middle = round(($y_max / 2), 0);
				$x_finish = 440;
				$last_average = 0;
				$loop_count = 0;
				// add 24 hours of zeroes
				while($loop_count < 24)
				{
					$point_average = $height - ($average * $y_division) + $top_offset;
					$x_start = $x_finish;
					$x_finish += $x_division;
					if($loop_count > 0)
					{
						imageline($base, $x_start, $last_average, $x_finish, $point_average, $orange);
					}
					else
					{
						imageline($base, $x_start, $point_average, $x_finish, $point_average, $orange);
					}
					$last_average = $point_average;
					$loop_count++;
				}
				imageline($base, 440, $top_offset, 440, $height + $top_offset, $faded);
				imagestring($base, 1, 430, $height - ($middle * $y_division) + $top_offset - 4, $middle, $light);
				imagestring($base, 1, 430, $height + $top_offset - 4, "0", $light);
				imagestring($base, 1, 430, $top_offset - 4, $y_max_display, $light);
			}
			// compile image and save it in the cache
			$white = imagecolorallocate($base, 255, 255, 255);
			imagecolortransparent($base, $white);
			imagealphablending($base, true);
			imagesavealpha($base, true);
			// find out if user specified for the image to be resized
			// $width = ?w
			// $height = ?h
			if((!empty($_GET['w']) && is_numeric($_GET['w'])) && (!empty($_GET['h']) && is_numeric($_GET['h'])))
			{
				if(!empty($_GET['w']) && is_numeric($_GET['w'])) 
				{
					$width = mysqli_real_escape_string($BF4stats, strip_tags($_GET['w']));
				}
				else
				{
					$width = 560;
				}
				if(!empty($_GET['h']) && is_numeric($_GET['h']))
				{
					$height = mysqli_real_escape_string($BF4stats, strip_tags($_GET['h']));
				}
				else
				{
					$height = 95;
				}
				// compile image and save it in the cache
				$save = './cache/banner_sid' . $sid . '.png';
				imagepng($base, $save);
				imagedestroy($base);
				// load the cached image back in
				$banner_img = imagecreatefrompng('./cache/banner_sid' . $sid . '.png');
				$resize_img = imagecreatetruecolor($width, $height);
				imagecopyresampled($resize_img, $banner_img, 0, 0, 0, 0, $width, $height, 560, 95);
				// start outputting the image
				header('Pragma: public');
				header('Cache-Control: max-age=0');
				header('Expires: 0');
				header("Content-type: image/png");
				// compile image
				imagepng($resize_img);
				imagedestroy($resize_img);
			}
			else
			{
				// compile image
				imagepng($base);
				imagedestroy($base);
			}
		}
		// an error occurred while processing query
		else
		{
			// start outputting the image
			header('Pragma: public');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			header("Content-type: image/png");
			// base image
			$base = imagecreatefrompng('./images/background.png');
			// text color
			$light = imagecolorallocate($base, 255, 255, 255);
			// add text to image
			imagestring($base, 4, 100, 40, 'An error occurred while processing your query.', $light);
			imagealphablending($base, false);
			imagesavealpha($base, true);
			// find out if user specified for the image to be resized
			// $width = ?w
			// $height = ?h
			if((!empty($_GET['w']) && is_numeric($_GET['w'])) && (!empty($_GET['h']) && is_numeric($_GET['h'])))
			{
				if(!empty($_GET['w']) && is_numeric($_GET['w'])) 
				{
					$width = mysqli_real_escape_string($BF4stats, strip_tags($_GET['w']));
				}
				else
				{
					$width = 560;
				}
				if(!empty($_GET['h']) && is_numeric($_GET['h']))
				{
					$height = mysqli_real_escape_string($BF4stats, strip_tags($_GET['h']));
				}
				else
				{
					$height = 95;
				}
				// compile image and save it in the cache
				$save = './cache/banner_sid' . $sid . '.png';
				imagepng($base, $save);
				imagedestroy($base);
				// load the cached image back in
				$banner_img = imagecreatefrompng('./cache/banner_sid' . $sid . '.png');
				$resize_img = imagecreatetruecolor($width, $height);
				imagecopyresampled($resize_img, $banner_img, 0, 0, 0, 0, $width, $height, 560, 95);
				// start outputting the image
				header('Pragma: public');
				header('Cache-Control: max-age=0');
				header('Expires: 0');
				header("Content-type: image/png");
				// compile image
				imagepng($resize_img);
				imagedestroy($resize_img);
			}
			else
			{
				// compile image
				imagepng($base);
				imagedestroy($base);
			}
		}
	}
	// this server id doesn't exist
	elseif(!empty($sid) && !(in_array($sid,$ServerIDs)))
	{
		// start outputting the image
		header('Pragma: public');
		header('Cache-Control: max-age=0');
		header('Expires: 0');
		header("Content-type: image/png");
		// base image
		$base = imagecreatefrompng('./images/background.png');
		// text color
		$light = imagecolorallocate($base, 255, 255, 255);
		// add text to image
		imagestring($base, 4, 130, 40, 'The provided Server ID doesn\'t exist.', $light);
		imagealphablending($base, false);
		imagesavealpha($base, true);
		// find out if user specified for the image to be resized
		// $width = ?w
		// $height = ?h
		if((!empty($_GET['w']) && is_numeric($_GET['w'])) && (!empty($_GET['h']) && is_numeric($_GET['h'])))
		{
			if(!empty($_GET['w']) && is_numeric($_GET['w'])) 
			{
				$width = mysqli_real_escape_string($BF4stats, strip_tags($_GET['w']));
			}
			else
			{
				$width = 560;
			}
			if(!empty($_GET['h']) && is_numeric($_GET['h']))
			{
				$height = mysqli_real_escape_string($BF4stats, strip_tags($_GET['h']));
			}
			else
			{
				$height = 95;
			}
			// compile image and save it in the cache
			$save = './cache/banner_sid' . $sid . '.png';
			imagepng($base, $save);
			imagedestroy($base);
			// load the cached image back in
			$banner_img = imagecreatefrompng('./cache/banner_sid' . $sid . '.png');
			$resize_img = imagecreatetruecolor($width, $height);
			imagecopyresampled($resize_img, $banner_img, 0, 0, 0, 0, $width, $height, 560, 95);
			// start outputting the image
			header('Pragma: public');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			header("Content-type: image/png");
			// compile image
			imagepng($resize_img);
			imagedestroy($resize_img);
		}
		else
		{
			// compile image
			imagepng($base);
			imagedestroy($base);
		}
	}
	// there is no server id number in the url query string
	else
	{
		// start outputting the image
		header('Pragma: public');
		header('Cache-Control: max-age=0');
		header('Expires: 0');
		header("Content-type: image/png");
		// base image
		$base = imagecreatefrompng('./images/background.png');
		// text color
		$light = imagecolorallocate($base, 255, 255, 255);
		// add text to image
		imagestring($base, 4, 200, 40, 'Server ID required.', $light);
		imagealphablending($base, false);
		imagesavealpha($base, true);
		// find out if user specified for the image to be resized
		// $width = ?w
		// $height = ?h
		if((!empty($_GET['w']) && is_numeric($_GET['w'])) && (!empty($_GET['h']) && is_numeric($_GET['h'])))
		{
			if(!empty($_GET['w']) && is_numeric($_GET['w'])) 
			{
				$width = mysqli_real_escape_string($BF4stats, strip_tags($_GET['w']));
			}
			else
			{
				$width = 560;
			}
			if(!empty($_GET['h']) && is_numeric($_GET['h']))
			{
				$height = mysqli_real_escape_string($BF4stats, strip_tags($_GET['h']));
			}
			else
			{
				$height = 95;
			}
			// compile image and save it in the cache
			$save = './cache/banner_sid' . $sid . '.png';
			imagepng($base, $save);
			imagedestroy($base);
			// load the cached image back in
			$banner_img = imagecreatefrompng('./cache/banner_sid' . $sid . '.png');
			$resize_img = imagecreatetruecolor($width, $height);
			imagecopyresampled($resize_img, $banner_img, 0, 0, 0, 0, $width, $height, 560, 95);
			// start outputting the image
			header('Pragma: public');
			header('Cache-Control: max-age=0');
			header('Expires: 0');
			header("Content-type: image/png");
			// compile image
			imagepng($resize_img);
			imagedestroy($resize_img);
		}
		else
		{
			// compile image
			imagepng($base);
			imagedestroy($base);
		}
	}
// php GD extension doesn't exist. show error image
}
else
{
	// start outputting the image
	header("Content-type: image/png");
	echo file_get_contents('./images/error.png');
}
?>