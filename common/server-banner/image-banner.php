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
			$Basic_r = @mysqli_fetch_assoc($Basic_q);
			$used_slots = $Basic_r['usedSlots'];
			$available_slots = $Basic_r['maxSlots'];
			$servername = textcleaner($Basic_r['ServerName']);
			if(strlen($servername) > 54)
			{
				$servername = substr($servername,0,53);
				$servername .= '..';
			}
			$mode = $Basic_r['Gamemode'];
			// convert mode to friendly name
			if(in_array($mode,$mode_array))
			{
				$mode_name = array_search($mode,$mode_array);
				if(strlen($mode_name) > 19)
				{
					$mode_name = substr($mode_name,0,18);
					$mode_name .= '..';
				}
			}
			// this mode is missing!
			else
			{
				$mode_name = $mode;
				if(strlen($mode_name) > 19)
				{
					$mode_name = substr($mode_name,0,18);
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
			// text color
			$light = imagecolorallocate($base, 255, 255, 255);
			$yellow = imagecolorallocate($base, 255, 250, 200);
			// copy map background onto the base background image
			$back = imagecreatefrompng('./images/map_back.png');
			imagecopy($base, $back, 6, 6, 0, 0, 104, 60);
			// convert map to friendly name
			// first find if this map name is even in the map array
			if(in_array($map,$map_array))
			{
				$map_name = array_search($map,$map_array);
				if(strlen($map_name) > 19)
				{
					$map_name = substr($map_name,0,18);
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
				if(strlen($map_name) > 19)
				{
					$map_name = substr($map_name,0,18);
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
			imagestring($base, 2, 190, 42, 'Current Mode', $yellow);
			imagestring($base, 3, 190, 57, $mode_name, $light);
			imagestring($base, 2, 120, 42, 'Players', $yellow);
			imagestring($base, 3, 120, 57, $used_slots . ' / ' . $available_slots, $light);
			imagestring($base, 2, 350, 42, 'Current Map', $yellow);
			imagestring($base, 3, 350, 57, $map_name, $light);
			$white = imagecolorallocate($base, 255, 255, 255);
			imagecolortransparent($base, $white);
			imagealphablending($base, true);
			imagesavealpha($base, true);
			// compile image and save it in the cache
			$save = './cache/banner_sid' . $sid . '.png';
			imagepng($base, $save);
			imagedestroy($base);
			// find out if user specified for the image to be resized
			// $width = ?w
			// $height = ?h
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
			imagealphablending($base, false);
			imagesavealpha($base, true);
			// text color
			$light = imagecolorallocate($base, 255, 255, 255);
			// add text to image
			imagestring($base, 4, 100, 40, 'An error occurred while processing your query.', $light);
			// compile image and save it in the cache
			$save = './cache/banner_sid' . $sid . '.png';
			imagepng($base, $save);
			imagedestroy($base);
			// find out if user specified for the image to be resized
			// $width = ?w
			// $height = ?h
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
		imagealphablending($base, false);
		imagesavealpha($base, true);
		// text color
		$light = imagecolorallocate($base, 255, 255, 255);
		// add text to image
		imagestring($base, 4, 130, 40, 'The provided Server ID doesn\'t exist.', $light);
		// compile image and save it in the cache
		$save = './cache/banner_sid' . $sid . '.png';
		imagepng($base, $save);
		imagedestroy($base);
		// find out if user specified for the image to be resized
		// $width = ?w
		// $height = ?h
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
		imagealphablending($base, false);
		imagesavealpha($base, true);
		// text color
		$light = imagecolorallocate($base, 255, 255, 255);
		// add text to image
		imagestring($base, 4, 200, 40, 'Server ID required.', $light);
		// compile image and save it in the cache
		$save = './cache/banner_sid' . $sid . '.png';
		imagepng($base, $save);
		imagedestroy($base);
		// find out if user specified for the image to be resized
		// $width = ?w
		// $height = ?h
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
// php GD extension doesn't exist. show error image
}
else
{
	// start outputting the image
	header("Content-type: image/png");
	echo file_get_contents('./images/error.png');
}
?>