<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// include required files
require_once("../pchart/class/pData.class.php");
require_once("../pchart/class/pDraw.class.php");
require_once("../pchart/class/pPie.class.php");
require_once("../pchart/class/pImage.class.php");
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
			SELECT `MapName`, SUM(`NumberofRounds`) AS number
			FROM `tbl_mapstats`
			WHERE `ServerID` = {$sid}
			AND `Gamemode` != ''
			GROUP BY `MapName`
			ORDER BY number DESC
		";
		$result = @mysqli_query($BF4stats, $query);
	}
	// this must be a global stats page
	else
	{
		// merge server IDs array into a variable
		$ids = join(',',$ServerIDs);
		$query = "
			SELECT `MapName`, SUM(`NumberofRounds`) AS number
			FROM `tbl_mapstats`
			WHERE `ServerID` in ({$ids})
			AND `Gamemode` != ''
			GROUP BY `MapName`
			ORDER BY number DESC
		"; 
		$result = @mysqli_query($BF4stats, $query);
	}
	if($result)
	{
		while($row = mysqli_fetch_assoc($result))
		{
			$key = $row['MapName'];
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
			$maps[] = $MapName;
			$number[] = $row['number'];
		}
		/* Create and populate the pData object */
		$MyData = new pData();   
		$MyData->addPoints($number,"ScoreA");  
		$MyData->setSerieDescription("ScoreA","Application A");
		/* Define the absissa serie */
		$MyData->addPoints($maps,"Labels");
		$MyData->setAbscissa("Labels");
		/* Create the pChart object */
		$myPicture = new pImage(600,300,$MyData,TRUE);
		/* Write the picture title */
		$myPicture->setFontProperties(array("FontName"=>"../pchart/fonts/Forgotte.ttf","FontSize"=>12));
		$myPicture->drawText(170,24,"Maps played in percent from greatest to least.",array("R"=>150,"G"=>150,"B"=>150));
		/* Set the default font properties */
		$myPicture->setFontProperties(array("FontName"=>"../pchart/fonts/pf_arma_five.ttf","FontSize"=>6,"R"=>150,"G"=>150,"B"=>150));
		/* Create the pPie object */
		$PieChart = new pPie($myPicture,$MyData);
		/* Draw an AA pie chart */
		$PieChart->draw2DRing(300,150,array("WriteValues"=>TRUE,"ValueR"=>150,"ValueG"=>150,"ValueB"=>150,"Border"=>TRUE));
		/* Set the default font properties */
		$myPicture->setFontProperties(array("FontName"=>"../pchart/fonts/pf_arma_five.ttf","FontSize"=>6,"R"=>150,"G"=>150,"B"=>150));
		/* Write the legend box */
		$myPicture->setShadow(FALSE);
		$PieChart->drawPieLegend(480,10,array("Alpha"=>0));
		/* Render the picture */
		$myPicture->stroke($BrowserExpire=TRUE);
	}
}
// php GD extension doesn't exist. show error image
else
{
	// start outputting the image
	header("Content-type: image/png");
	echo file_get_contents('./images/error.png');
}
?>