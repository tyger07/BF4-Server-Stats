<?php
// BF4 Stats Page by Ty_ger07
// https://forum.myrcon.com/showthread.php?6854

// include required files
require_once("../pchart/class/pData.class.php");
require_once("../pchart/class/pDraw.class.php");
require_once("../pchart/class/pImage.class.php");
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
require_once('../constants.php');
// check if necessary environment exists on this server
if(extension_loaded('gd') && function_exists('gd_info'))
{
	// we will need a server ID from the URL query string!
	// if no data query string is provided, this is an image
	if(!empty($sid) && in_array($sid,$ServerIDs))
	{
		// build graph using pChart API
		$result = @mysqli_query($BF4stats,"
			SELECT SUBSTRING(`TimeMapLoad`, 11, length(`TimeMapLoad`) - 16) AS Hourly, AVG(`MaxPlayers`) AS Average
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
		// did the query return results
		if(@mysqli_num_rows($result) != 0)
		{
			// initialize tracking variable
			$increment = '';
			// loop through query results
			while($row = mysqli_fetch_assoc($result))
			{
				$raw_hour = $row['Hourly'];
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
		}
		// no?
		else
		{
			$increment = 0;
			// add 24 hours of zeroes
			while($increment < 24)
			{
				$hour[] = $increment;
				$average[] = 0;
				$increment++;
			}
		}
		$myData = new pData();
		$myData->addPoints($average,"Serie1");
		$myData->setSerieDescription("Serie1","Average");
		$myData->setSerieOnAxis("Serie1",0);
		$serieSettings = array("R"=>255,"G"=>250,"B"=>200);
		$myData->setPalette("Serie1",$serieSettings);
		$myData->addPoints($hour,"Absissa");
		$myData->setAbscissa("Absissa");
		$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
		$myData->setAxisName(0,"");
		$myData->setAxisUnit(0,"");
		$myPicture = new pImage(160,65,$myData,TRUE);
		$GradientSettings = array("StartR"=>050,"StartG"=>100,"StartB"=>150,"Alpha"=>50,"Levels"=>-100);
		$myPicture->drawGradientArea(0,0,160,80,DIRECTION_VERTICAL,$GradientSettings);
		$myPicture->setShadow(FALSE);
		$myPicture->setGraphArea(14,7,153,60);
		$myPicture->setFontProperties(array("R"=>250,"G"=>250,"B"=>250,"FontName"=>"../pchart/fonts/Forgotte.ttf","FontSize"=>8));
		$max = max($average) + 5;
		if($max <= 0)
		{
			$max = 1;
		}
		if($max > 64)
		{
			$max = 64;
		}
		$min = min($average) - 5;
		if($min < 0)
		{
			$min = 0;
		}
		$Settings = array("Pos"=>SCALE_POS_LEFTRIGHT
		, "Mode"=>SCALE_MODE_MANUAL, "ManualScale"=>array(0=>array("Min"=>$min,"Max"=>$max))
		, "LabelingMethod"=>LABELING_ALL
		, "GridR"=>200, "GridG"=>200, "GridB"=>200, "GridAlpha"=>75
		, "TickR"=>240, "TickG"=>240, "TickB"=>240, "TickAlpha"=>75
		, "LabelRotation"=>0, "LabelSkip"=>1
		, "DrawXLines"=>0
		, "DrawSubTicks"=>1
		, "DrawYLines"=>ALL
		, "SubTickR"=>210, "SubTickG"=>210, "SubTickB"=>210, "SubTickAlpha"=>75
		, "AxisR"=>210, "AxisG"=>210, "AxisB"=>210, "AxisAlpha"=>75);
		$myPicture->drawScale($Settings);
		$Config = "";
		$myPicture->drawSplineChart();
		$myPicture->render("./cache/graph_sid{$sid}.png");
		// graph is done
		// query for server info
		$Basic_q = @mysqli_query($BF4stats,"
			SELECT `mapName`, `Gamemode`, `maxSlots`, `usedSlots`, `ServerName`, `IP_Address`
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
			$ip = $Basic_r['IP_Address'];
			$servername = $Basic_r['ServerName'];
			if(strlen($servername) > 34)
			{
				$servername = substr($servername,0,33);
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
			// copy graph background onto the base background image
			$back = imagecreatefrompng('./images/graph_back.png');
			imagecopy($base, $back, 389, 20, 0, 0, 164, 69);
			// add graph
			$graph = imagecreatefrompng('./cache/graph_sid' . $sid . '.png');
			// copy the graph image onto the background image
			imagecopy($base, $graph, 391, 22, 0, 0, 160, 65);
			// figure out server's location
			// set location default values
			$location = '';
			$fallback = 0;
			// remove port from IP address
			$s_explode = explode(":",$ip);
			$server_ip = $s_explode[0];
			// check if user provided a manual country code override
			if(!empty($cc))
			{
				// set the location to the input override provided
				$location = $cc;
			}
			else
			{
				// try API
				$json = @file_get_contents('http://ip-api.com/json/' . $server_ip);
				$data = @json_decode($json,true);
				$location = $data['countryCode'];
				// if above API failed ...
				// use less accurate method by querying database for players with similar IP address as Server's IP address
				if($location == '')
				{
					// loop through the query removing last character one at a time until a match is found
					// set server location to most similar player's location
					while(@mysqli_num_rows($Location_q) == 0 && strlen($server_ip) > 1)
					{
						// query for server info
						$Location_q = @mysqli_query($BF4stats,"
							SELECT `CountryCode`
							FROM `tbl_playerdata`
							WHERE `IP_Address` LIKE '{$server_ip}%'
							LIMIT 1
						");
						// drop the last character from the server ip for another loop
						$server_ip = substr($server_ip, 0, -1);
						// continuing to do broader and broader search with each pass...
					}
					// set the variable based on results of above query loop
					$Location_r = @mysqli_fetch_assoc($Location_q);
					$location = strtoupper($Location_r['CountryCode']);
					$fallback = 1;
					// add those characters back to $server_ip
					$server_ip = $s_explode[0];
				}
			}
			// compile flag image
			// first find out if this country name is the list of country names
			if(in_array($location,$country_array))
			{
				// compile country flag image
				// if country is null or unknown, use generic image
				if(($location == '') OR ($location == '--'))
				{
					$country_img = '../images/flags/none.png';
				}
				else
				{
					$country_img = '../images/flags/' . strtolower($location) . '.png';	
				}
			}
			// this country is missing!
			else
			{
				$country_img = '../images/flags/none.png';
			}
			// copy country flag onto the base background image
			$flag = imagecreatefrompng($country_img);
			imagecopy($base, $flag, 120, 20, 0, 0, 16, 11);
			// if the country is not certain, present uncertainty
			if($fallback == 1)
			{
				$flag = imagecreatefrompng('./images/uncertain.png');
				imagecopy($base, $flag, 120, 20, 0, 0, 16, 11);
			}
			// add text to image
			imagestring($base, 2, 400, 4, 'Players: Previous 24 Hrs', $yellow);
			imagestring($base, 2, 120, 4, 'Server Name', $yellow);
			imagestring($base, 3, 140, 18, $servername, $light);
			imagestring($base, 2, 240, 32, 'Current Mode', $yellow);
			imagestring($base, 3, 240, 47, $mode_name, $light);
			imagestring($base, 2, 120, 62, 'Players', $yellow);
			imagestring($base, 3, 120, 76, $used_slots . ' / ' . $available_slots, $light);
			imagestring($base, 2, 240, 62, 'Current Map', $yellow);
			imagestring($base, 3, 240, 76, $map_name, $light);
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