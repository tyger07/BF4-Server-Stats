<?php
// BF4 Stats Page by Ty_ger07
// http://open-web-community.com/

// include required files
require_once("../pchart/class/pData.class.php");
require_once("../pchart/class/pDraw.class.php");
require_once("../pchart/class/pImage.class.php");
require_once('../../config/config.php');
require_once('../connect.php');
require_once('../case.php');
// check if necessary environment exists on this server
if(extension_loaded('gd') && function_exists('gd_info'))
{
	// SQL query limit
	$limit = 50;
	// check if a server was provided
	// if so, this is a server stats page
	if(!empty($sid))
	{
		$query  = "
			SELECT `MinPlayers`, `MaxPlayers`, `AvgPlayers`
			FROM `tbl_mapstats`
			WHERE `ServerID` = {$sid}
			ORDER BY `TimeRoundStarted` DESC
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
			SELECT `MinPlayers`, `MaxPlayers`, `AvgPlayers`
			FROM `tbl_mapstats`
			WHERE `ServerID` in ({$ids})
			ORDER BY `TimeRoundStarted` DESC
			LIMIT {$limit}
		";
		$result = @mysqli_query($BF4stats, $query);
	}
	if($result)
	{
		$i = 1;
		while($row = mysqli_fetch_assoc($result))
		{
			$rounds[$i]	= $i;
			$max[]		= $row['MaxPlayers'];
			$min[]		= $row['MinPlayers'];
			$avg[]		= $row['AvgPlayers'];
			$i++;
		}
	}
	$myData = new pData();
	$myData->addPoints($max,"Serie1");
	$myData->setSerieDescription("Serie1","Max");
	$myData->setSerieOnAxis("Serie1",0);
	$myData->addPoints($min,"Serie2");
	$myData->setSerieDescription("Serie2","Min");
	$myData->setSerieOnAxis("Serie2",0);
	$myData->addPoints($avg,"Serie3");
	$myData->setSerieDescription("Serie3","Avg");
	$myData->setSerieOnAxis("Serie3",0);
	$myData->addPoints($rounds,"Absissa");
	$myData->setAbscissa("Absissa");
	$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
	$myData->setAxisName(0,"Players");
	$myData->setAxisUnit(0,"");
	$myPicture = new pImage(600,300,$myData,TRUE);
	$myPicture->setFontProperties(array("FontName"=>"../pchart/fonts/Forgotte.ttf","FontSize"=>12));
	$TextSettings = array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE
	, "R"=>150, "G"=>150, "B"=>150);
	$myPicture->drawText(297,18,"Minimum, maximum, and average players in last ". $limit ." rounds.",$TextSettings);
	$myPicture->setShadow(FALSE);
	$myPicture->setGraphArea(50,50,576,270);
	$myPicture->setFontProperties(array("R"=>150,"G"=>150,"B"=>150,"FontName"=>"../pchart/fonts/pf_arma_five.ttf","FontSize"=>6));
	$Settings = array("Pos"=>SCALE_POS_LEFTRIGHT
	, "Mode"=>SCALE_MODE_FLOATING
	, "LabelingMethod"=>LABELING_ALL
	, "GridR"=>150, "GridG"=>150, "GridB"=>150, "GridAlpha"=>50, "TickR"=>150, "TickG"=>150, "TickB"=>150, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>0, "DrawSubTicks"=>1, "SubTickR"=>150, "SubTickG"=>150, "SubTickB"=>150, "SubTickAlpha"=>50, "DrawYLines"=>NONE, "AxisR"=>150, "AxisG"=>150,"AxisB"=>150);
	$myPicture->drawScale($Settings);
	$Config = "";
	$myPicture->drawSplineChart();
	$Config = array("FontR"=>150, "FontG"=>150, "FontB"=>150, "FontName"=>"../pchart/fonts/pf_arma_five.ttf", "FontSize"=>6, "Margin"=>6, "Alpha"=>30, "BoxSize"=>5, "Style"=>LEGEND_NOBORDER
	, "Mode"=>LEGEND_HORIZONTAL
	);
	$myPicture->drawLegend(515,12,$Config);
	$myPicture->stroke($BrowserExpire=TRUE);
}
// php GD extension doesn't exist. show error image
else
{
	// start outputting the image
	header("Content-type: image/png");
	echo file_get_contents('./images/error.png');
}
?>